<?php
require '../includes/header_start.php';
require '../includes/classes/queue.php';

$queue = new \Queue\queue();

function login($result, $id) {
  global $dbconn;

  $_SESSION['shop_user'] = $result;
  $_SESSION['shop_active'] = true;

  $timecard_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = $id AND time_out IS NULL");

  $ip = $_SERVER['REMOTE_ADDR'];

  $dbconn->query("UPDATE user SET last_login = UNIX_TIMESTAMP(), last_ip_address = '$ip' WHERE id = $id");

  if($timecard_qry->num_rows === 0) { // if there is no timecard, we have to create one
    $dbconn->query("INSERT INTO timecards (employee, time_in) VALUES ('$id', UNIX_TIMESTAMP())");
  }

  echo "success";
}

function clockOut($id, $redirect = true) {
  global $dbconn;
  global $queue;

  $qry = $dbconn->query("SELECT * FROM user WHERE id = '$id'");

  if($qry-> num_rows === 1) {
    $user = $qry->fetch_assoc();
    $timecard_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '$id' AND time_out IS NULL");

    if($timecard_qry->num_rows === 1) {
      $timecard = $timecard_qry->fetch_assoc();

      if($dbconn->query("UPDATE timecards SET time_out = UNIX_TIMESTAMP() WHERE id = {$timecard['id']}")) {
        echo $queue->suspendOps($id);

        echo displayToast("success", "Successfully clocked out {$user['name']}.", "Clocked Out");

        if($redirect) {
          echo '<script>setTimeout(function() {window.location.replace("/employees.php");}, 500)</script>';
        }
      } else {
        dbLogSQLErr($dbconn);
        die();
      }
    } else {
      echo displayToast("warning", "User {$user['name']} is not clocked in.", "Not Clocked In");
      die();
    }
  }
}

switch($_REQUEST['action']) {
  case 'login':
    $id = sanitizeInput($_REQUEST['id'], $dbconn);
    $pin = sanitizeInput($_POST['pin'], $dbconn);

    $qry = $dbconn->query("SELECT * FROM user WHERE id = '$id'");

    if($qry->num_rows > 0) {
      $result = $qry->fetch_assoc();

      if($_SESSION['userInfo']['account_type'] <= 4) {
        login($result, $id);
      } else {
        if($result['pin_code'] === $pin) {
          login($result, $id);
        } else {
          die("PIN Failure");
        }
      }
    }

    break;
  case 'clock_out':
    $id = sanitizeInput($_REQUEST['clockout_id']);

    clockOut($id);

    break;
  case 'save_account_ops':
    $id = sanitizeInput($_REQUEST['id']);
    $ops = sanitizeInput($_REQUEST['op_string']);

    $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$id'");
    $usr = $usr_qry->fetch_assoc();

    if($dbconn->query("UPDATE user SET ops_available = '$ops' WHERE id = '$id';")) {
      echo displayToast("success", "Updated user operations for {$usr['name']}.", "Successfully Updated");
    } else {
      echo displayToast("error", "Unable to update user operations!", "Try Again");
    }

    break;

  case 'pin_out':
    $pin = sanitizeInput($_REQUEST['pin']);

    $usr_qry = $dbconn->query("SELECT * FROM user WHERE pin_code = '$pin' AND account_status = TRUE");

    if($usr_qry->num_rows === 1) {
      $usr = $usr_qry->fetch_assoc();

      clockOut($usr['id'], false);
    } elseif($usr_qry->num_rows > 1) {
      echo displayToast("info", "More than 1 individual matches that PIN.", "Multiple PIN");
    } else {
      echo displayToast("error", "Invalid Pin", "Invalid Pin");
    }

    break;

  case 'start_break':
    $queue->startOp(201, 'Break');

    break;

  case 'stop_break':
    $queue->stopOp(201, '', null, null, 'Break');
    break;

  case 'get_break_btn':
    $break_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = 201 AND active = TRUE AND active_employees LIKE '%\"{$_SESSION['shop_user']['id']}\"%'");

    // if they're not currently on break
    if($break_qry->num_rows === 0) {
      $output = array('id' => 201, 'display' => "Start Break");
    } else {
      $break = $break_qry->fetch_assoc();

      $output = array('id' => $break['id'], 'display' => "Stop Break");
    }

    $output_json = json_encode($output, true);

    echo $output_json;

    break;
}