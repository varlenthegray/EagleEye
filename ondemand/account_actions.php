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

        $qry = $dbconn->query("SELECT * FROM user WHERE id = $id");

        if($qry-> num_rows === 1) {
            $user = $qry->fetch_assoc();
            $timecard_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '$id' AND time_out IS NULL");

            if($timecard_qry->num_rows === 1) {
                $timecard = $timecard_qry->fetch_assoc();

                if($dbconn->query("UPDATE timecards SET time_out = UNIX_TIMESTAMP() WHERE id = {$timecard['id']}")) {
                    echo displayToast("success", "Successfully clocked out {$user['name']}.", "Clocked Out");

                    echo '<script>setTimeout(function() {window.location.replace("/employees.php");}, 500)</script>';
                } else {
                    dbLogSQLErr($dbconn);
                    die();
                }
            } else {
                echo displayToast("warning", "User {$user['name']} is not clocked in.", "Not Clocked In");
                die();
            }
        }

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
}