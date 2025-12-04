<?php
session_start();

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'mole_vc';
$DB_PORT = 3307;

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error"=>"DB connection failed"]); exit;
}

header('Content-Type: application/json');

// ---------- HELPERS ----------
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["error"=>"login_required"]); exit;
    }
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role']==='admin';
}

function json_ok($data){ echo json_encode($data); exit; }

// ---------- ROUTER ----------
$mode = $_REQUEST['mode'] ?? '';

switch ($mode) {
    // ===== LOGIN =====
    case 'login':
        $id_or_email = trim($_POST['login_id'] ?? '');
        $rawpass = $_POST['password'] ?? '';
        
        if ($id_or_email === '' || $rawpass === '') {
            http_response_code(400);
            json_ok(["error"=>"missing_fields"]);
        }
        
        $pass = md5($rawpass);
        $stmt = $conn->prepare(
            "SELECT id,user_id,email,role,status
             FROM users
             WHERE (user_id=? OR email=?) AND password=? LIMIT 1"
        );
        $stmt->bind_param("sss",$id_or_email,$id_or_email,$pass);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if (!$row = $res->fetch_assoc()) {
            http_response_code(401);
            json_ok(["error"=>"invalid_credentials"]);
        }
        
        if ($row['status']!=='active') {
            http_response_code(403);
            json_ok(["error"=>"user_disabled"]);
        }
        
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_code'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];
        json_ok([
            "success"=>true,
            "user"=>[
                "id"=>$row['id'],
                "user_id"=>$row['user_id'],
                "email"=>$row['email'],
                "role"=>$row['role']
            ]
        ]);
        break;

    // ===== LOGOUT =====
    case 'logout':
        session_destroy();
        json_ok(["success"=>true]);
        break;

    // ===== BOOK MEETING (USER) =====
    case 'book':
        require_login();
        if (is_admin()) {
            http_response_code(403);
            json_ok(["error"=>"admin_cannot_book"]);
        }
        
        $topic = trim($_POST['topic'] ?? '');
        $date = $_POST['date'] ?? '';
        $hall = $_POST['hall'] ?? '';
        $platform = $_POST['platform'] ?? '';
        $slot = $_POST['slot'] ?? '';
        
        if ($topic==='' || $date==='' || $hall==='' || $platform==='' || $slot==='') {
            http_response_code(400);
            json_ok(["error"=>"missing_fields"]);
        }
        
        if (!in_array($hall,['new_sabhaghar','main_community_hall','chamber','online'],true) ||
            !in_array($platform,['webex','bharatvc','zoom','other'],true)) {
            http_response_code(400);
            json_ok(["error"=>"invalid_hall_or_platform"]);
        }
        
        if (!preg_match('/^\d{2}:\d{2}-\d{2}:\d{2}$/',$slot)) {
            http_response_code(400);
            json_ok(["error"=>"invalid_slot_format"]);
        }
        
        list($s,$e) = explode('-',$slot);
        $start = "$date $s:00";
        $end = "$date $e:00";
        
        // max 2 concurrent per hall
        $q = $conn->prepare("
            SELECT COUNT(*) AS c FROM meetings
            WHERE hall=? AND status='current'
            AND ((start_time <= ? AND end_time > ?)
            OR (start_time < ? AND end_time >= ?)
            OR (start_time >= ? AND end_time <= ?))
        ");
        $q->bind_param("sssssss",$hall,$start,$start,$end,$end,$start,$end);
        $q->execute();
        $cRow = $q->get_result()->fetch_assoc();
        
        if ($cRow['c'] >= 2) {
            http_response_code(409);
            json_ok(["error"=>"slot_full"]);
        }
        
        $stmt = $conn->prepare("
            INSERT INTO meetings (user_id,topic,hall,platform,start_time,end_time,status)
            VALUES (?,?,?,?,?,?, 'current')
        ");
        $stmt->bind_param("isssss",$_SESSION['user_id'],$topic,$hall,$platform,$start,$end);
        $stmt->execute();
        json_ok(["success"=>true,"meeting_id"=>$stmt->insert_id]);
        break;

    // ===== SLOT STATUS =====
    case 'slot_status':
        $hall = $_GET['hall'] ?? '';
        $date = $_GET['date'] ?? '';
        $slot = $_GET['slot'] ?? '';
        
        if ($hall===''||$date===''||$slot==='') {
            http_response_code(400);
            json_ok(["error"=>"missing"]);
        }
        
        if (!preg_match('/^\d{2}:\d{2}-\d{2}:\d{2}$/',$slot)) {
            http_response_code(400);
            json_ok(["error"=>"bad_slot"]);
        }
        
        list($s,$e) = explode('-',$slot);
        $start = "$date $s:00";
        $end = "$date $e:00";
        
        $q = $conn->prepare("
            SELECT COUNT(*) AS c FROM meetings
            WHERE hall=? AND status='current'
            AND ((start_time <= ? AND end_time > ?)
            OR (start_time < ? AND end_time >= ?)
            OR (start_time >= ? AND end_time <= ?))
        ");
        $q->bind_param("sssssss",$hall,$start,$start,$end,$end,$start,$end);
        $q->execute();
        $cRow = $q->get_result()->fetch_assoc();
        $c = (int)$cRow['c'];
        json_ok(["count"=>$c,"full"=>$c>=2]);
        break;

    // ===== USER HISTORY =====
    case 'list_meetings':
        require_login();
        $uid = $_SESSION['user_id'];
        $res = $conn->query("
            SELECT id,topic,hall,platform,start_time,end_time,status,meeting_link
            FROM meetings
            WHERE user_id=$uid
            ORDER BY start_time DESC
        ");
        $out = ["current"=>[],"previous"=>[],"cancelled"=>[]];
        while($row=$res->fetch_assoc()){
            $out[$row['status']][]=$row;
        }
        json_ok($out);
        break;

    // ===== ADMIN: ALL MEETINGS =====
    case 'admin_meetings':
        require_login();
        if (!is_admin()){ http_response_code(403); json_ok(["error"=>"admin_only"]); }
        $res = $conn->query("
            SELECT m.*,u.user_id,u.email
            FROM meetings m
            JOIN users u ON m.user_id=u.id
            ORDER BY m.start_time DESC
        ");
        $all=[];
        while($row=$res->fetch_assoc()) $all[]=$row;
        json_ok($all);
        break;

    // ===== ADMIN: ALL USERS =====
    case 'admin_users':
        require_login();
        if (!is_admin()){ http_response_code(403); json_ok(["error"=>"admin_only"]); }
        $res = $conn->query("
            SELECT id,user_id,email,role,status
            FROM users
            ORDER BY id ASC
        ");
        $rows=[];
        while($row=$res->fetch_assoc()) $rows[]=$row;
        json_ok($rows);
        break;

    // ===== ADMIN: SAVE LINK / STATUS =====
    case 'save_link':
        require_login();
        if (!is_admin()){ http_response_code(403); json_ok(["error"=>"admin_only"]); }
        $id = (int)($_POST['meeting_id'] ?? 0);
        $link = trim($_POST['meeting_link'] ?? '');
        $st = $_POST['status'] ?? 'current';
        
        if (!in_array($st,['current','previous','cancelled'],true)) {
            http_response_code(400); json_ok(["error"=>"bad_status"]);
        }
        
        $stmt = $conn->prepare("UPDATE meetings SET meeting_link=?, status=? WHERE id=?");
        $stmt->bind_param("ssi",$link,$st,$id);
        $stmt->execute();
        json_ok(["success"=>true]);
        break;

    // ===== ADMIN: TOGGLE USER =====
    case 'toggle_user':
        require_login();
        if (!is_admin()){ http_response_code(403); json_ok(["error"=>"admin_only"]); }
        $uid = (int)($_POST['uid'] ?? 0);
        $conn->query("UPDATE users
            SET status=IF(status='active','disabled','active')
            WHERE id=$uid");
        json_ok(["success"=>true]);
        break;

    // ===== ADMIN: END MEETING =====
    case 'end_meeting':
        require_login();
        if (!is_admin()){ http_response_code(403); json_ok(["error"=>"admin_only"]); }
        $id = (int)($_POST['meeting_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE meetings SET status='previous' WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        json_ok(["success"=>true]);
        break;

    // ===== ADMIN: ADD USER =====
    case 'admin_add_user':
        require_login();
        if (!is_admin()){ http_response_code(403); json_ok(["error"=>"admin_only"]); }
        $user_id = trim($_POST['user_id'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        if ($user_id==='' || $email==='' || $pass==='') {
            http_response_code(400); json_ok(["error"=>"missing_fields"]);
        }
        
        if (!in_array($role,['user','admin'],true)) $role = 'user';
        $hash = md5($pass);
        $stmt = $conn->prepare(
            "INSERT INTO users (user_id,email,password,role,status)
             VALUES (?,?,?,?, 'active')"
        );
        $stmt->bind_param("ssss",$user_id,$email,$hash,$role);
        if (!$stmt->execute()) {
            http_response_code(400);
            json_ok(["error"=>"could_not_insert"]);
        }
        json_ok(["success"=>true,"id"=>$stmt->insert_id]);
        break;

    // ===== HELP TICKETS: CREATE (user or guest) =====
    case 'help_create':
        $issue_type = $_POST['issue_type'] ?? '';
        $message = trim($_POST['message'] ?? '');
        
        if (!in_array($issue_type, ['vc_issue', 'forget_password', 'other'], true) || $message === '') {
            http_response_code(400);
            json_ok(["error" => "missing_or_invalid"]);
        }
        
        $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        $stmt = $conn->prepare("
            INSERT INTO help_tickets (user_id, issue_type, message, status) 
            VALUES (?, ?, ?, 'open')
        ");
        $stmt->bind_param("iss", $uid, $issue_type, $message);
        $stmt->execute();
        json_ok(["success" => true, "ticket_id" => $stmt->insert_id]);
        break;

    // ===== HELP TICKETS: ADMIN LIST =====
    case 'help_list':
        require_login();
        if (!is_admin()) { 
            http_response_code(403); 
            json_ok(["error" => "admin_only"]); 
        }
        $res = $conn->query("
            SELECT t.*, u.user_id AS user_code, u.email 
            FROM help_tickets t 
            LEFT JOIN users u ON t.user_id = u.id 
            ORDER BY t.created_at DESC
        ");
        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        json_ok($rows);
        break;

    // ===== HELP TICKETS: ADMIN CLOSE =====
    case 'help_close':
        require_login();
        if (!is_admin()) { 
            http_response_code(403); 
            json_ok(["error" => "admin_only"]); 
        }
        $tid = (int)($_POST['ticket_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE help_tickets SET status='closed' WHERE id=?");
        $stmt->bind_param("i", $tid);
        $stmt->execute();
        json_ok(["success" => true]);
        break;

    default:
        http_response_code(400);
        json_ok(["error"=>"unknown_mode"]);
}
?>
