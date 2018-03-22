<?php
require '../includes/header_start.php';

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

  $qry = $dbconn->query("SELECT * FROM user WHERE id = '$id'");

  if($qry-> num_rows === 1) {
    $user = $qry->fetch_assoc();
    $timecard_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '$id' AND time_out IS NULL");

    if($timecard_qry->num_rows === 1) {
      $timecard = $timecard_qry->fetch_assoc();

      if($dbconn->query("UPDATE timecards SET time_out = UNIX_TIMESTAMP() WHERE id = {$timecard['id']}")) {
        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE active = TRUE AND active_employees LIKE '%\"$id\"%'");

        if($op_queue_qry->num_rows > 0) {
          while($op_queue = $op_queue_qry->fetch_assoc()) {
            $aemp = json_decode($op_queue['active_employees']);

            if(count($aemp) > 1) {
              $loc = array_search($user['id'], $aemp);
              unset($aemp[$loc]);
            } else {
              $aemp = array();
            }

            $active_employees = json_encode(array_values($aemp));

            $active = (empty($aemp)) ? 'FALSE' : 'TRUE';

            $dbconn->query("UPDATE op_queue SET active = $active, active_employees = '$active_employees', partially_completed = TRUE WHERE id = '{$op_queue['id']}'");

            $changed = ["Active"=>FALSE,"Active Employees"=>$active_employees,"Partially Completed"=>TRUE,"ID"=>$op_queue['id']];
            $changed = json_encode($changed);

            $dbconn->query("INSERT INTO log_cron (`desc`, time) VALUES ('$changed', UNIX_TIMESTAMP())");

            $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, end_time) VALUES ('{$op_queue['id']}', NULL, '$changed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
          }
        }

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
    $id = sanitizeInput($_REQUEST['user_id']);

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
}