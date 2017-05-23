<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 3/22/2017
 * Time: 3:33 PM
 */

// Display a PHP toast
function displayToast($type, $message, $subject) {
    return <<<HEREDOC
<script type="text/javascript">
    displayToast("$type", "$message", "$subject");
</script>
HEREDOC;
}

// Log an SQL error
function dbLogSQLErr($db, $toast = true) {
    $sqlerror = $db->real_escape_string($db->error);

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

function outputPHPErrs() {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}