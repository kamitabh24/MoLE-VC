<?php
session_start();
$logged_in = isset($_SESSION['user_id']);
$role      = $_SESSION['role'] ?? null;
$user_code = $_SESSION['user_code'] ?? null;

$showHelpTab = isset($_GET['help']) && (bool)$_GET['help'];

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MoLE VC – Schedule meeting</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
  --bg-dark:#020b24;--card-bg:#ffffff;--primary:#2563ff;--primary-soft:#e5edff;
  --border:#dde3f0;--text:#111827;--muted:#6b7280;--danger:#ef4444;
  --radius-lg:18px;--radius-md:12px;--shadow-soft:0 18px 40px rgba(15,23,42,0.18);
  --transition:all .22s ease;--slot-height:48px;
  --font-main:-apple-system,BlinkMacSystemFont,"Segoe UI",system-ui,sans-serif;
}
*,*:before,*:after{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:var(--font-main);
  background:radial-gradient(circle at 10% 20%,#0b1740 0,#020b24 55%,#020617 100%);
  color:#fff;min-height:100vh;display:flex;justify-content:center;align-items:flex-start;padding:24px;
}
.shell{width:100%;max-width:1080px;background:rgba(8,16,40,0.85);border-radius:32px;
  padding:26px 26px 30px;box-shadow:var(--shadow-soft);backdrop-filter:blur(22px);}
.shell-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;}
.brand{display:flex;align-items:center;gap:10px;}
.brand-logo{width:34px;height:34px;border-radius:999px;background:linear-gradient(135deg,#1fe4ff,#3f5efb);}
.brand-text{font-weight:700;font-size:19px;letter-spacing:.03em;}
.top-actions{display:flex;align-items:center;gap:10px;}
.icon-btn{width:38px;height:38px;border-radius:999px;border:none;background:rgba(15,23,42,0.92);
 display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;
 box-shadow:0 0 0 1px rgba(148,163,184,.5);transition:var(--transition);}
.icon-btn:hover{transform:translateY(-1px);box-shadow:0 0 0 1px #fff;}
.headline-title{font-size:24px;font-weight:700;}
.headline-sub{margin-top:4px;color:#cbd5f5;font-size:14px;}
.card{background:var(--card-bg);border-radius:var(--radius-lg);padding:22px;color:var(--text);margin-top:18px;}
.section-label{font-size:13px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:10px;}
.input-row{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:14px;}
.input-group{flex:1;min-width:180px;}
label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--muted);}
input[type="text"],input[type="password"],input[type="date"],select,textarea{
 width:100%;border-radius:var(--radius-md);border:1px solid var(--border);padding:9px 11px;
 font-size:14px;background:#f9fafb;outline:none;transition:var(--transition);}
input:focus,select:focus,textarea:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 1px rgba(37,99,255,.3);}
.primary-btn, .ghost-btn{
 border-radius:var(--radius-md);padding:9px 18px;font-size:14px;display:inline-flex;align-items:center;
 gap:6px;cursor:pointer;transition:var(--transition);border:none;
}
.primary-btn{background:linear-gradient(90deg,#2563ff,#4f46e5);color:#fff;box-shadow:0 12px 25px rgba(37,99,255,.5);}
.primary-btn:hover{transform:translateY(-1px);box-shadow:0 18px 32px rgba(37,99,255,.75);}
.ghost-btn{background:#fff;color:#111827;border:1px solid #d1d5db;}
.ghost-btn:hover{background:#f3f4ff;}
.text-danger{color:var(--danger);font-size:13px;margin-top:4px;}
.text-success{color:#16a34a;font-size:13px;margin-top:4px;}
.booking-layout{display:grid;grid-template-columns:1.4fr 1.3fr;gap:18px;margin-top:12px;}
.date-card{border-radius:var(--radius-md);border:1px solid var(--border);padding:12px;}
.date-row-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px;}
.tz-select{border-radius:999px;border:1px solid var(--border);padding:6px 12px;font-size:12px;color:var(--muted);background:#f9fafb;}
.month-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;margin-bottom:8px;}
.day-strip{display:flex;gap:4px;}
.day-pill{flex:1;border-radius:999px;padding:7px 0 8px;text-align:center;border:1px solid transparent;
 font-size:12px;color:#4b5563;background:#f9fafb;}
.day-pill span{display:block;font-size:11px;color:#9ca3af;}
.slot-column{display:flex;flex-direction:column;gap:10px;}
.slot-grid{display:flex;flex-direction:column;gap:8px;max-height:360px;overflow-y:auto;}
.slot-row{display:flex;gap:10px;}
.slot-btn{
 flex:1;height:var(--slot-height);border-radius:14px;border:1px solid #e5e7eb;background:#ffffff;cursor:pointer;
 padding:0 14px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 3px rgba(148,163,184,.18);
 font-size:13px;color:#111827;transition:var(--transition);}
.slot-btn small{text-transform:uppercase;color:#9ca3af;font-size:11px;}
.slot-btn:hover{transform:translateY(-1px);box-shadow:0 8px 18px rgba(148,163,184,.4);}
.slot-btn.selected{border-color:var(--primary);background:var(--primary-soft);box-shadow:0 8px 22px rgba(37,99,255,.35);}
.slot-btn.selected .slot-time{color:#1d4ed8;}
.slot-btn.full{border-color:var(--danger);background:#fee2e2;color:#b91c1c;cursor:not-allowed;opacity:.95;}
table{width:100%;border-collapse:collapse;margin-top:10px;font-size:13px;}
th,td{padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:left;}
th{background:#f9fafb;font-weight:600;}
.login-center{max-width:420px;margin:40px auto 10px;}

/* Mobile card layout for history tables */
.meeting-card {
  display: none;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px;
  margin-bottom: 12px;
  background: #f9fafb;
}
.meeting-card-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 6px 0;
  border-bottom: 1px solid #e5e7eb;
  font-size: 13px;
}
.meeting-card-row:last-child {
  border-bottom: none;
}
.meeting-card-label {
  font-weight: 600;
  color: #6b7280;
  flex-shrink: 0;
  margin-right: 10px;
}
.meeting-card-value {
  color: #111827;
  text-align: right;
  word-break: break-word;
}
.join-link-btn {
  display: inline-block;
  background: linear-gradient(90deg,#2563ff,#4f46e5);
  color: #fff;
  padding: 6px 14px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 12px;
  font-weight: 600;
  margin-top: 4px;
}

@media(max-width:900px){
  body{padding:12px;}
  .shell{padding:18px 12px 20px;border-radius:24px;}
  .headline-title{font-size:20px;}
  .primary-btn, .ghost-btn{
    padding:11px 18px;
    font-size:14px;
    min-height:44px;
  }
  .card{padding:16px;}
  .booking-layout{grid-template-columns:1fr;}

  .history-table-wrap table {
    display: none;
  }
  
  .meeting-card {
    display: block;
  }

  .table-wrap{
    width:100%;
    overflow-x:hidden;
  }
  .table-wrap table,
  .table-wrap thead,
  .table-wrap tbody,
  .table-wrap th,
  .table-wrap td,
  .table-wrap tr{
    display:block;
  }
  .table-wrap thead{display:none;}
  .table-wrap tr{
    border:1px solid #e5e7eb;
    border-radius:12px;
    margin-bottom:12px;
    padding:12px;
    background:#f9fafb;
  }
  .table-wrap td{
    border:none;
    padding:8px 0;
    font-size:14px;
    display:flex;
    flex-direction:column;
    gap:8px;
  }
  .table-wrap td::before{
    content:attr(data-label);
    display:block;
    font-weight:600;
    color:#6b7280;
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:0.05em;
    margin-bottom:4px;
  }
  
  .table-wrap button.ghost-btn,
  .table-wrap button.primary-btn {
    width:100%;
    padding:12px 18px;
    font-size:14px;
    min-height:44px;
    justify-content:center;
  }
  
  .table-wrap input[type="text"].adm-link {
    width:100%;
    padding:12px;
    font-size:14px;
    min-height:44px;
    margin-bottom:8px;
  }
  
  .table-wrap select.adm-status {
    width:100%;
    padding:12px;
    font-size:14px;
    min-height:44px;
  }
  
  .mobile-btn-group {
    display:flex;
    flex-direction:column;
    gap:8px;
    width:100%;
  }
}
</style>
</head>
<body>
<div class="shell">
  <div class="shell-header">
    <div class="brand">
      <div class="brand-logo"></div><div class="brand-text">MoLE VC</div>
    </div>
    <div class="top-actions">
      <?php if($logged_in): ?>
        <form method="get"><button class="icon-btn" name="logout" value="1">⎋</button></form>
      <?php endif; ?>
    </div>
  </div>

<?php if(!$logged_in): ?>
  <!-- LOGIN -->
  <div class="headline login-center" style="text-align:left;">
    <div class="headline-title">Login to schedule</div>
    <div class="headline-sub">Use your User ID or Email (no OTP).</div>
  </div>
  <div class="card login-center">
    <div id="loginError" class="text-danger" style="display:none;"></div>
    <form id="loginForm">
      <div class="input-group">
        <label>User ID or Email</label>
        <input type="text" name="login_id" required>
      </div>
      <div class="input-group" style="margin-top:10px;">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <div style="margin-top:6px;text-align:right;">
        <a href="index.php?help=1" style="font-size:12px;color:#cbd5f5;text-decoration:underline;">
          Forgot password?
        </a>
      </div>
      <div style="margin-top:10px;display:flex;justify-content:flex-end;">
        <button class="primary-btn" type="submit">Login →</button>
      </div>
    </form>
  </div>

<?php elseif($role === 'admin'): ?>
  <!-- ADMIN PANEL -->
  <div class="headline">
    <div class="headline-title">Admin Panel</div>
    <div class="headline-sub">Manage users, meetings, and help tickets.</div>
  </div>

  <!-- Admin Tabs -->
  <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
    <button type="button" class="primary-btn" id="tabAdminUsers">Users</button>
    <button type="button" class="ghost-btn" id="tabAdminMeetings">Meetings</button>
    <button type="button" class="ghost-btn" id="tabAdminTickets">Tickets</button>
  </div>

  <!-- Users Tab -->
  <div id="cardAdminUsers">
    <div class="card">
      <div class="section-label">Add New User</div>
      <div id="addUserMsg" class="text-danger" style="display:none;"></div>
      <div id="addUserOk" class="text-success" style="display:none;"></div>
      <form id="addUserForm">
        <div class="input-row">
          <div class="input-group">
            <label>User ID</label>
            <input type="text" name="user_id" required>
          </div>
          <div class="input-group">
            <label>Email</label>
            <input type="text" name="email" required>
          </div>
          <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
          </div>
          <div class="input-group">
            <label>Role</label>
            <select name="role">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div style="margin-top:10px;display:flex;justify-content:flex-end;">
          <button class="primary-btn" type="submit">Add User</button>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="section-label">All Users</div>
      <div class="table-wrap">
        <table id="usersTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>User ID</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="usersBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Meetings Tab -->
  <div class="card" id="cardAdminMeetings" style="display:none;">
    <div class="section-label">All Meetings</div>
    <div class="table-wrap">
      <table id="adminMeetings">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Date/Time</th>
            <th>Hall</th>
            <th>Platform</th>
            <th>Chaired By</th>
            <th>Chair Person</th>
            <th>Status</th>
            <th>VC Link / Update</th>
          </tr>
        </thead>
        <tbody id="adminMeetingsBody"></tbody>
      </table>
    </div>
  </div>

  <!-- Tickets Tab -->
  <div class="card" id="cardAdminTickets" style="display:none;">
    <div class="section-label">Help Tickets</div>
    <div class="table-wrap">
      <table id="helpTicketsTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Issue Type</th>
            <th>Message</th>
            <th>Status</th>
            <th>Created</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="helpTicketsBody"></tbody>
      </table>
    </div>
  </div>

<?php else: ?>
  <!-- USER DASHBOARD -->
  <div class="headline">
    <div class="headline-title">Schedule Meeting</div>
    <div class="headline-sub">Welcome, <?=htmlspecialchars($user_code)?> – book MoLE VC slots.</div>
  </div>

  <!-- Tabs: New meeting / My meetings / Help -->
  <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
    <button type="button" class="primary-btn" id="tabNew">New Meeting</button>
    <button type="button" class="ghost-btn" id="tabMy">My Meetings</button>
    <button type="button" class="ghost-btn" id="tabHelp">Help</button>
  </div>

  <!-- New meeting card -->
  <div class="card" id="cardNew">
    <div class="section-label">New Meeting</div>
    <div id="bookMsg" class="text-danger" style="display:none;"></div>
    <div id="bookOk" class="text-success" style="display:none;"></div>

    <form id="bookForm">
      <input type="hidden" name="duration" id="durationInput" value="30">
      <div class="input-row">
        <div class="input-group" style="flex:2;">
          <label>Topic</label>
          <textarea name="topic" rows="2" required></textarea>
        </div>
        <div class="input-group">
          <label>Hall</label>
          <select name="hall" id="hall" required>
            <option value="">Select hall</option>
            <option value="new_sabhaghar">New Sabha Ghar</option>
            <option value="main_community_hall">Main community hall</option>
            <option value="chamber">Chamber</option>
            <option value="online">Online</option>
          </select>
        </div>
        <div class="input-group">
          <label>Platform</label>
          <select name="platform" required>
            <option value="">Select</option>
            <option value="webex">Webex</option>
            <option value="bharatvc">Bharat VC</option>
            <option value="zoom">Zoom</option>
            <option value="other">Others</option>
          </select>
        </div>
        <div class="input-group">
          <label>Duration</label>
          <select id="duration" required>
            <option value="30">30 minutes</option>
            <option value="60">1 hour</option>
            <option value="120">2 hours</option>
            <option value="180">3 hours</option>
            <option value="240">4 hours</option>
          </select>
        </div>
      </div>
      <div class="input-row">
        <div class="input-group">
          <label>Chaired By</label>
          <select name="chaired_by" required>
            <option value="">Select</option>
            <option value="HLEM">HLEM</option>
            <option value="MOS (LE)">MOS (LE)</option>
            <option value="Secretary">Secretary</option>
            <option value="AS">AS</option>
            <option value="AS/FA">AS/FA</option>
            <option value="JS">JS</option>
            <option value="DDG">DDG</option>
            <option value="Director">Director</option>
            <option value="DS">DS</option>
            <option value="US/DD/SO/AD">US/DD/SO/AD</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="input-group" style="flex:2;">
          <label>Chair Person Name</label>
          <input type="text" name="chair_person_name" placeholder="Enter chair person's full name" required>
        </div>
      </div>
      <div class="input-row">
        <div class="input-group" style="max-width:220px;">
          <label>Date</label>
          <input type="date" id="date" name="date" min="<?=date('Y-m-d')?>" required>
        </div>
      </div>

      <div class="booking-layout">
        <div>
          <div class="date-row-header">
            <span style="font-size:13px;color:var(--muted);">Timezone</span>
            <select class="tz-select">
              <option>India – IST</option>
            </select>
          </div>
          <div class="date-card">
            <div class="month-row">
              <div><strong id="weekLabel">Select date above</strong></div>
            </div>
            <div class="day-strip">
              <button type="button" class="day-pill" id="day0">
                <span>Selected</span>
              </button>
            </div>
          </div>
        </div>

        <div class="slot-column">
          <div class="section-label" style="margin-bottom:0;">Time Slots</div>
          <small style="color:var(--muted);margin-bottom:6px;">Max 2 meetings per hall per slot; red = full.</small>
          <input type="hidden" name="slot" id="slotInput" required>
          <div class="slot-grid" id="slotGrid"></div>
        </div>
      </div>

      <div style="margin-top:16px;display:flex;justify-content:flex-end;">
        <button class="primary-btn" type="submit">Continue →</button>
      </div>
    </form>
  </div>

  <!-- My meetings card -->
  <div class="card" id="cardMy" style="display:none;">
    <div class="section-label">My Meetings</div>
    <div style="display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
      <button class="ghost-btn" type="button" onclick="showHistory('current')">Current</button>
      <button class="ghost-btn" type="button" onclick="showHistory('previous')">Previous</button>
      <button class="ghost-btn" type="button" onclick="showHistory('cancelled')">Cancelled</button>
    </div>
    <div id="hist-current"></div>
    <div id="hist-previous" style="display:none;"></div>
    <div id="hist-cancelled" style="display:none;"></div>
  </div>

  <!-- Help card -->
  <div class="card" id="cardHelp" style="display:none;">
    <div class="section-label">Help / Raise Ticket</div>
    <p style="font-size:13px;color:#6b7280;margin-bottom:10px;">
      Select issue type and describe your problem. Support will contact you shortly.
    </p>
    <div id="helpMsg" class="text-danger" style="display:none;"></div>
    <div id="helpOk" class="text-success" style="display:none;"></div>

    <form id="helpForm">
      <div class="input-row">
        <div class="input-group">
          <label>Issue Type</label>
          <select name="issue_type" id="issue_type" required>
            <option value="">Select</option>
            <option value="vc_issue">VC Issue</option>
            <option value="forget_password">Forget Password</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>
      <div class="input-row">
        <div class="input-group">
          <label>Details</label>
          <textarea name="message" rows="3" placeholder="Explain the issue..." required></textarea>
        </div>
      </div>
      <div style="margin-top:12px;display:flex;justify-content:flex-end;">
        <button type="submit" class="primary-btn">Submit Ticket</button>
      </div>
    </form>
  </div>
<?php endif; ?>
</div>

<script>
const loggedIn = <?= $logged_in ? 'true' : 'false' ?>;
const isAdmin  = <?= $role === 'admin' ? 'true' : 'false' ?>;
const showHelpTab = <?= $showHelpTab ? 'true' : 'false' ?>;

// ---------- LOGIN ----------
if (!loggedIn) {
  const lf = document.getElementById('loginForm');
  lf.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(lf);
    fd.append('mode','login');
    const r = await fetch('api.php', {method:'POST', body:fd});
    const d = await r.json();
    const errEl = document.getElementById('loginError');
    if (!r.ok || d.error){
      errEl.style.display='block';
      errEl.textContent = d.error || 'Login failed';
    } else {
      location.reload();
    }
  });
}

// ---------- ADMIN JS ----------
if (loggedIn && isAdmin){
  loadAdminUsers();
  loadAdminMeetings();
  loadHelpTickets();
  
  const addForm = document.getElementById('addUserForm');
  if (addForm){
    addForm.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(addForm);
      fd.append('mode','admin_add_user');
      const r = await fetch('api.php',{method:'POST',body:fd});
      const d = await r.json();
      const err = document.getElementById('addUserMsg');
      const ok  = document.getElementById('addUserOk');
      err.style.display='none'; ok.style.display='none';
      if (!r.ok || d.error){
        err.textContent = d.error || 'Failed to add user';
        err.style.display='block';
      } else {
        ok.textContent = 'User added successfully.';
        ok.style.display='block';
        addForm.reset();
        loadAdminUsers();
      }
    });
  }
  
  // Admin Tab Switching
  const tabAdminUsers = document.getElementById('tabAdminUsers');
  const tabAdminMeetings = document.getElementById('tabAdminMeetings');
  const tabAdminTickets = document.getElementById('tabAdminTickets');
  const cardAdminUsers = document.getElementById('cardAdminUsers');
  const cardAdminMeetings = document.getElementById('cardAdminMeetings');
  const cardAdminTickets = document.getElementById('cardAdminTickets');
  
  function setAdminTabButton(activeBtn){
    [tabAdminUsers, tabAdminMeetings, tabAdminTickets].forEach(btn=>{
      if (!btn) return;
      btn.classList.remove('primary-btn');
      btn.classList.add('ghost-btn');
    });
    if (activeBtn){
      activeBtn.classList.remove('ghost-btn');
      activeBtn.classList.add('primary-btn');
    }
  }
  
  function activateAdminTab(which){
    cardAdminUsers.style.display = (which==='users') ? 'block' : 'none';
    cardAdminMeetings.style.display = (which==='meetings') ? 'block' : 'none';
    cardAdminTickets.style.display = (which==='tickets') ? 'block' : 'none';
    
    if (which==='users') setAdminTabButton(tabAdminUsers);
    if (which==='meetings') setAdminTabButton(tabAdminMeetings);
    if (which==='tickets') setAdminTabButton(tabAdminTickets);
  }
  
  tabAdminUsers.addEventListener('click', ()=>activateAdminTab('users'));
  tabAdminMeetings.addEventListener('click', ()=>activateAdminTab('meetings'));
  tabAdminTickets.addEventListener('click', ()=>activateAdminTab('tickets'));
  
  // Start with users tab
  activateAdminTab('users');
}

async function loadAdminUsers(){
  const body = document.getElementById('usersBody');
  if (!body) return;
  const r = await fetch('api.php?mode=admin_users');
  const d = await r.json();
  if (!r.ok || d.error){
    body.innerHTML = `<tr><td colspan="6" style="color:#b91c1c;font-size:13px;">${d.error || 'Failed to load users'}</td></tr>`;
    return;
  }
  if (!d.length){
    body.innerHTML = `<tr><td colspan="6" style="color:#6b7280;font-size:13px;">No users.</td></tr>`;
    return;
  }
  body.innerHTML = d.map(u=>`
    <tr>
      <td data-label="ID">${u.id}</td>
      <td data-label="User ID">${u.user_id}</td>
      <td data-label="Email">${u.email}</td>
      <td data-label="Role">${u.role}</td>
      <td data-label="Status">${u.status}</td>
      <td data-label="Action">
        ${u.role === 'admin' ? '<span style="color:#9ca3af;">Admin user</span>' :
          `<button class="ghost-btn" style="padding:10px 16px;font-size:13px;min-height:44px;width:100%;"
                   onclick="toggleUser(${u.id})">
             ${u.status === 'active' ? 'Disable User' : 'Enable User'}
           </button>`}
      </td>
    </tr>`).join('');
}

async function toggleUser(id){
  const fd = new FormData();
  fd.append('mode','toggle_user');
  fd.append('uid',id);
  const r = await fetch('api.php',{method:'POST',body:fd});
  const d = await r.json();
  if (!r.ok || d.error){
    alert(d.error || 'Failed to toggle user');
  } else {
    loadAdminUsers();
  }
}

async function loadAdminMeetings(){
  const body = document.getElementById('adminMeetingsBody');
  if (!body) return;
  const r = await fetch('api.php?mode=admin_meetings');
  const d = await r.json();
  if (!r.ok || d.error){
    body.innerHTML = `<tr><td colspan="9" style="color:#b91c1c;font-size:13px;">${d.error || 'Failed to load meetings'}</td></tr>`;
    return;
  }
  if (!d.length){
    body.innerHTML = `<tr><td colspan="9" style="color:#6b7280;font-size:13px;">No meetings yet.</td></tr>`;
    return;
  }
  body.innerHTML = d.map(m=>{
    let hallText = 'New Sabha Ghar';
    if (m.hall === 'main_community_hall') hallText = 'Main community hall';
    else if (m.hall === 'chamber') hallText = 'Chamber';
    else if (m.hall === 'online') hallText = 'Online';
    return `
      <tr>
        <td data-label="ID">${m.id}</td>
        <td data-label="User">${m.user_id}</td>
        <td data-label="Date/Time">${m.start_time}</td>
        <td data-label="Hall">${hallText}</td>
        <td data-label="Platform">${m.platform.toUpperCase()}</td>
        <td data-label="Chaired By">${m.chaired_by || '-'}</td>
        <td data-label="Chair Person">${m.chair_person_name || '-'}</td>
        <td data-label="Status">
          <select data-mid="${m.id}" class="adm-status">
            <option value="current"  ${m.status==='current'?'selected':''}>Current</option>
            <option value="previous" ${m.status==='previous'?'selected':''}>Previous</option>
            <option value="cancelled"${m.status==='cancelled'?'selected':''}>Cancelled</option>
          </select>
        </td>
        <td data-label="VC Link / Update">
          <input type="text" class="adm-link" data-mid="${m.id}"
                 value="${m.meeting_link ? m.meeting_link : ''}"
                 placeholder="https://..." style="margin-bottom:8px;">
          <div class="mobile-btn-group">
            <button class="primary-btn" style="padding:10px 16px;font-size:13px;min-height:44px;"
                    onclick="saveAdminLink(${m.id})">Save Link & Status</button>
            <button class="ghost-btn" style="padding:10px 16px;font-size:13px;min-height:44px;"
                    onclick="endMeetingNow(${m.id})">End Meeting Now</button>
          </div>
        </td>
      </tr>`;
  }).join('');
}

async function loadHelpTickets(){
  const body = document.getElementById('helpTicketsBody');
  if (!body) return;
  const r = await fetch('api.php?mode=help_list');
  const d = await r.json();
  if (!r.ok || d.error){
    body.innerHTML = `<tr><td colspan="7" style="color:#b91c1c;font-size:13px;">${d.error || 'Failed to load tickets'}</td></tr>`;
    return;
  }
  if (!d.length){
    body.innerHTML = `<tr><td colspan="7" style="color:#6b7280;font-size:13px;">No help tickets.</td></tr>`;
    return;
  }
  body.innerHTML = d.map(t=>`
    <tr>
      <td data-label="ID">${t.id}</td>
      <td data-label="User">${t.user_code || 'Guest'}</td>
      <td data-label="Issue Type">${t.issue_type.replace('_', ' ')}</td>
      <td data-label="Message" style="max-width:200px;word-break:break-word;">${t.message}</td>
      <td data-label="Status">${t.status}</td>
      <td data-label="Created">${t.created_at}</td>
      <td data-label="Action">
        ${t.status === 'open' ? 
          `<button class="ghost-btn" style="padding:10px 16px;font-size:13px;min-height:44px;width:100%;"
                   onclick="closeTicket(${t.id})">Close Ticket</button>` : 
          '<span style="color:#16a34a;font-weight:600;">Closed</span>'}
      </td>
    </tr>`).join('');
}

async function closeTicket(id){
  if (!confirm('Close this ticket?')) return;
  const fd = new FormData();
  fd.append('mode','help_close');
  fd.append('ticket_id',id);
  const r = await fetch('api.php',{method:'POST',body:fd});
  const d = await r.json();
  if (!r.ok || d.error){
    alert(d.error || 'Failed to close ticket');
  } else {
    loadHelpTickets();
  }
}

async function saveAdminLink(id){
  const linkEl   = document.querySelector('.adm-link[data-mid="'+id+'"]');
  const statusEl = document.querySelector('.adm-status[data-mid="'+id+'"]');
  const fd = new FormData();
  fd.append('mode','save_link');
  fd.append('meeting_id',id);
  fd.append('meeting_link',linkEl.value);
  fd.append('status',statusEl.value);
  const r = await fetch('api.php',{method:'POST',body:fd});
  const d = await r.json();
  if (!r.ok || d.error){
    alert(d.error || 'Failed to save');
  } else {
    loadAdminMeetings();
  }
}

async function endMeetingNow(id){
  if (!confirm('Mark this meeting as ended (move to previous)?')) return;
  const fd = new FormData();
  fd.append('mode','end_meeting');
  fd.append('meeting_id',id);
  const r = await fetch('api.php',{method:'POST',body:fd});
  const d = await r.json();
  if (!r.ok || d.error){
    alert(d.error || 'Failed to end meeting');
  } else {
    loadAdminMeetings();
  }
}

// ---------- USER JS ----------
function buildStartTimes() {
  const arr = [];
  let minutes = 0;
  while (minutes < 24 * 60) {
    const h24 = Math.floor(minutes / 60);
    const m   = minutes % 60;
    let h12   = h24 % 12;
    if (h12 === 0) h12 = 12;
    const ampm = h24 < 12 ? 'AM' : 'PM';
    const label = `${h12}:${m.toString().padStart(2,'0')} ${ampm}`;
    const hh = h24.toString().padStart(2,'0');
    const mm = m.toString().padStart(2,'0');
    arr.push({label, hhmm: `${hh}:${mm}`});
    minutes += 30;
  }
  return arr;
}
const startTimes = buildStartTimes();
let slots = [];

function buildSlotsForDuration() {
  const durSelect = document.getElementById('duration');
  if (!durSelect) return;
  const durMinutes = parseInt(durSelect.value,10);
  slots = [];
  for (const t of startTimes) {
    const [hStr,mStr] = t.hhmm.split(':');
    let total = parseInt(hStr,10)*60 + parseInt(mStr,10) + durMinutes;
    if (total > 24*60) continue;
    const eh = Math.floor(total/60).toString().padStart(2,'0');
    const em = (total%60).toString().padStart(2,'0');
    const slotValue = `${t.hhmm}-${eh}:${em}`;
    slots.push({value: slotValue, label: t.label});
  }
}

function renderSlots(){
  const grid = document.getElementById('slotGrid');
  if (!grid) return;
  buildSlotsForDuration();
  grid.innerHTML='';
  let row;
  slots.forEach((obj,i)=>{
    const s = obj.value;
    const label = obj.label;
    if(i%2===0){
      row = document.createElement('div');
      row.className='slot-row'; grid.appendChild(row);
    }
    const btn = document.createElement('button');
    btn.type='button'; btn.className='slot-btn'; btn.dataset.slot=s;
    btn.innerHTML = `
      <div>
        <div class="slot-time">${label}</div>
        <small>${parseInt(document.getElementById('duration').value,10)} min</small>
      </div>
      <span>•</span>`;
    btn.onclick = ()=>selectSlot(btn);
    row.appendChild(btn);
  });
  refreshSlotStatus();
}

async function refreshSlotStatus(){
  const grid = document.getElementById('slotGrid');
  const hall = document.getElementById('hall')?.value;
  const date = document.getElementById('date')?.value;
  if (!grid || !hall || !date) return;
  for (const btn of grid.querySelectorAll('.slot-btn')){
    const s = btn.dataset.slot;
    const url = `api.php?mode=slot_status&hall=${encodeURIComponent(hall)}&date=${encodeURIComponent(date)}&slot=${encodeURIComponent(s)}`;
    const r = await fetch(url);
    const d = await r.json();
    if (d.full){
      btn.classList.add('full');
    } else {
      btn.classList.remove('full');
    }
  }
}

function selectSlot(btn){
  if (btn.classList.contains('full')) return;
  document.querySelectorAll('.slot-btn').forEach(b=>b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('slotInput').value = btn.dataset.slot;
}

function setupDateDisplay(){
  const dateInput = document.getElementById('date');
  if (!dateInput) return;
  dateInput.addEventListener('change',()=>{
    const val = dateInput.value;
    if(!val) return;
    document.getElementById('weekLabel').innerText = val;
    document.getElementById('day0').innerHTML = val + '<span>Selected</span>';
    refreshSlotStatus();
  });
}

// USER HELP form handling
const helpForm = document.getElementById('helpForm');
if (helpForm){
  helpForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(helpForm);
    fd.append('mode','help_create');
    const r = await fetch('api.php',{method:'POST',body:fd});
    const d = await r.json();
    const err = document.getElementById('helpMsg');
    const ok  = document.getElementById('helpOk');
    err.style.display='none'; ok.style.display='none';
    if (!r.ok || d.error){
      err.textContent = d.error || 'Failed to submit ticket';
      err.style.display='block';
    } else {
      ok.textContent = 'Ticket submitted. Support will contact you.';
      ok.style.display='block';
      helpForm.reset();
    }
  });
}

// USER init
if (loggedIn && !isAdmin){
  renderSlots();
  setupDateDisplay();
  document.getElementById('hall').addEventListener('change',refreshSlotStatus);
  document.getElementById('duration').addEventListener('change',()=>{
    renderSlots();
  });

  document.getElementById('bookForm').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    document.getElementById('durationInput').value =
      document.getElementById('duration').value;
    fd.append('mode','book');
    const r = await fetch('api.php',{method:'POST',body:fd});
    const d = await r.json();
    const err = document.getElementById('bookMsg');
    const ok  = document.getElementById('bookOk');
    err.style.display='none'; ok.style.display='none';
    if(!r.ok || d.error){
      err.textContent = d.error || 'Booking failed';
      err.style.display='block';
    }else{
      ok.textContent = 'Meeting requested successfully. Admin will add link.';
      ok.style.display='block';
      e.target.reset();
      renderSlots();
      loadHistory();
    }
  });

  loadHistory();
}

// ---------- USER HISTORY ----------
async function loadHistory(){
  const cDiv = document.getElementById('hist-current');
  if (!cDiv) return;
  const r = await fetch('api.php?mode=list_meetings');
  const d = await r.json();
  renderHistTable('current',d.current || []);
  renderHistTable('previous',d.previous || []);
  renderHistTable('cancelled',d.cancelled || []);
}

function renderHistTable(tab,data){
  const container = document.getElementById('hist-'+tab);
  if(!container) return;
  if(!data.length){
    container.innerHTML = `<p style="font-size:13px;color:#6b7280;">No ${tab} meetings.</p>`;
    return;
  }
  
  // Desktop table view
  let tableHtml = '<div class="history-table-wrap"><table><tr><th>When</th><th>Hall</th><th>Platform</th><th>Chaired By</th><th>Chair Person</th><th>Topic</th><th>Link</th></tr>';
  
  // Mobile card view
  let cardHtml = '';
  
  data.forEach(r=>{
    let hallText = 'New Sabha Ghar';
    if (r.hall === 'main_community_hall') hallText = 'Main community hall';
    else if (r.hall === 'chamber') hallText = 'Chamber';
    else if (r.hall === 'online') hallText = 'Online';
    
    const linkDisplay = r.meeting_link ? 
      `<a target="_blank" href="${r.meeting_link}" class="join-link-btn">Join Meeting</a>` : 
      '<span style="color:#6b7280;">Pending</span>';
    
    // Desktop table row
    tableHtml += `<tr>
      <td>${r.start_time}</td>
      <td>${hallText}</td>
      <td>${r.platform.toUpperCase()}</td>
      <td>${r.chaired_by || '-'}</td>
      <td>${r.chair_person_name || '-'}</td>
      <td>${r.topic}</td>
      <td>${linkDisplay}</td>
    </tr>`;
    
    // Mobile card
    cardHtml += `
      <div class="meeting-card">
        <div class="meeting-card-row">
          <span class="meeting-card-label">When:</span>
          <span class="meeting-card-value">${r.start_time}</span>
        </div>
        <div class="meeting-card-row">
          <span class="meeting-card-label">Hall:</span>
          <span class="meeting-card-value">${hallText}</span>
        </div>
        <div class="meeting-card-row">
          <span class="meeting-card-label">Platform:</span>
          <span class="meeting-card-value">${r.platform.toUpperCase()}</span>
        </div>
        <div class="meeting-card-row">
          <span class="meeting-card-label">Chaired By:</span>
          <span class="meeting-card-value">${r.chaired_by || '-'}</span>
        </div>
        <div class="meeting-card-row">
          <span class="meeting-card-label">Chair Person:</span>
          <span class="meeting-card-value">${r.chair_person_name || '-'}</span>
        </div>
        <div class="meeting-card-row">
          <span class="meeting-card-label">Topic:</span>
          <span class="meeting-card-value">${r.topic}</span>
        </div>
        <div class="meeting-card-row">
          <span class="meeting-card-label">Link:</span>
          <span class="meeting-card-value">${linkDisplay}</span>
        </div>
      </div>`;
  });
  
  tableHtml += '</table></div>';
  container.innerHTML = tableHtml + cardHtml;
}

function showHistory(tab){
  ['current','previous','cancelled'].forEach(t=>{
    document.getElementById('hist-'+t).style.display = (t===tab)?'block':'none';
  });
}

// Tabs for New meeting / My meetings / Help
const tabNew  = document.getElementById('tabNew');
const tabMy   = document.getElementById('tabMy');
const tabHelp = document.getElementById('tabHelp');
const cardNew = document.getElementById('cardNew');
const cardMy  = document.getElementById('cardMy');
const cardHelp= document.getElementById('cardHelp');

function setTabButton(activeBtn){
  [tabNew,tabMy,tabHelp].forEach(btn=>{
    if (!btn) return;
    btn.classList.remove('primary-btn');
    btn.classList.add('ghost-btn');
  });
  if (activeBtn){
    activeBtn.classList.remove('ghost-btn');
    activeBtn.classList.add('primary-btn');
  }
}

function activateTab(which){
  if (!cardNew || !cardMy || !cardHelp) return;
  cardNew.style.display  = (which==='new')  ? 'block' : 'none';
  cardMy.style.display   = (which==='my')   ? 'block' : 'none';
  cardHelp.style.display = (which==='help') ? 'block' : 'none';
  if (which==='new')  setTabButton(tabNew);
  if (which==='my')   setTabButton(tabMy);
  if (which==='help') setTabButton(tabHelp);
}

if (tabNew && tabMy && tabHelp){
  tabNew.addEventListener('click',  ()=>activateTab('new'));
  tabMy.addEventListener('click',   ()=>activateTab('my'));
  tabHelp.addEventListener('click', ()=>activateTab('help'));
  // Activate help tab if requested via GET param, otherwise new meeting
  activateTab(<?= $showHelpTab ? "'help'" : "'new'" ?>);
}
</script>
</body>
</html>