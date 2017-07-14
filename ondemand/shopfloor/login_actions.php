<?php
require_once ("../../includes/header_start.php");

function login($result, $id) {
    global $dbconn;

    $_SESSION['shop_user'] = $result;
    $_SESSION['shop_active'] = true;

    $timecard_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = $id AND time_out IS NULL");

    if($timecard_qry->num_rows === 0) { // if there is no timecard, we have to create one
        $dbconn->query("INSERT INTO timecards (employee, time_in) VALUES ('$id', UNIX_TIMESTAMP())");
        echo "success - clocked in";
    } else {
        echo "success";
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
    case 'logout':
        unset($_SESSION['shop_user']);
        unset($_SESSION['shop_active']);

        header("Location: /shopfloor/login.php");

        break;
    case 'clock_out':
        $qry = $dbconn->query("SELECT * FROM user WHERE id = {$_SESSION['shop_user']['id']}");

        if($qry-> num_rows === 1) {
            $timecard_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '{$_SESSION['shop_user']['id']}' AND time_out IS NULL");

            if($timecard_qry->num_rows === 1) {
                $timecard = $timecard_qry->fetch_assoc();

                if($dbconn->query("UPDATE timecards SET time_out = UNIX_TIMESTAMP() WHERE id = {$timecard['id']}")) {
                    echo "success";
                } else {
                    dbLogSQLErr($dbconn);
                    die();
                }
            } else {
                dbLogSQLErr($dbconn);
                die();
            }
        }

        break;
    default:
        die();
        break;
}