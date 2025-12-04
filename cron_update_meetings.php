// cron_update_meetings.php
<?php
$conn = new mysqli("localhost","root","","mole_vc");
$conn->query("
  UPDATE meetings 
  SET status='previous'
  WHERE status='current' 
    AND end_time < (NOW() - INTERVAL 24 HOUR)
");
