<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 3/22/2017
 * Time: 3:33 PM
 */

use FlowrouteNumbersAndMessagingLib\Models;

// Display a PHP toast
function displayToast($type, $message, $subject) {
    return <<<HEREDOC
<script type="text/javascript">
    displayToast("$type", "$message", "$subject");
</script>
HEREDOC;
}

// Log an SQL error
function dbLogSQLErr($db, $toast = true, $err_override = null) {
    $sqlerror = (!empty($err_override)) ? $sqlerror = $err_override : $sqlerror = sanitizeInput($db->error);

    if($qry = $db->query("INSERT INTO log_error (message, time, ref_page, type) VALUES ('$sqlerror', NOW(), '{$_SERVER['REQUEST_URI']}', 1)")) {
        $id = $db->insert_id;

        if(!$toast)
            echo "<E> A severe error has been logged. Please report error code $id to IT.";
        else
            echo displayToast("error", "A severe error has been logged. Please report error code $id to IT.", "Error");
    } else {
        echo "Suffered FATAL ERROR: ~~'" . $db->error . "'~~";
        die();
    }
}

// Sanitize input field
function sanitizeInput($input, $db = '') {
    global $dbconn;

    return trim($dbconn->real_escape_string($input));
}

// log a debug code
function dbLogDebug($code) {
    global $dbconn;
    $dbconn->query("INSERT INTO log_debug (time, message) VALUES (NOW(), '$code')");
}

// display error codes on page
function outputPHPErrs() {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

/** Calculates the delivery date AND counts for holidays AND weekends */
function calcDelDate($days_to_ship) {
    global $dbconn;

    switch($days_to_ship) {
        case 'G':
            $target_date = strtotime('+26 weekdays');

            break;
        case 'Y':
            $target_date = strtotime('+19 weekdays');

            break;
        case 'N':
            $target_date = strtotime('+13 weekdays');

            break;
        case 'R':
            $target_date = strtotime('+6 weekdays');

            break;
        default:
            $target_date = strtotime('+26 weekdays');

            break;
    }

    $holiday_count = 0;
    $holiday = [];

    $hol_qry = $dbconn->query("SELECT * FROM cal_holidays");

    while($hol_res = $hol_qry->fetch_assoc()) {
        $holiday[] = $hol_res['unix_time'];
    }

    // take the target date and determine if there are any holidays that fall between now and then
    foreach($holiday as $day) {
        if($target_date > $day && time() < $day) {
            // it falls on a holiday
            $holiday_count += 1;
        }
    }

    $target_date_formatted = date('m/d/y', $target_date);

    $final_date = strtotime("$target_date_formatted + $holiday_count days");

    if(date("N", $final_date) >= 6) {
        $final_date = strtotime(date("m/d/y", $final_date) . " next monday");
    }

    return date("m/d/Y", $final_date);
}

function mail_nl2br($string) {
    //return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    return str_replace('\\n', '<br />', $string);
}

function sendText($to, $message) {
    global $flowroute;

    $msg = new Models\Message();

    $msg->from = '18285755727';
    $msg->to = $to;
    $msg->body = $message;

    $messages = $flowroute->getMessages();
    $result = $messages->createSendAMessage($msg);
}