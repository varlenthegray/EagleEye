<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 3/22/2017
 * Time: 3:33 PM
 */
// Display a PHP toast
function Toast($type, $message, $subject) {
    return <<<HEREDOC
<script type="text/javascript">
    displayToast("$type", "$message", "$subject");
</script>
HEREDOC;
}

// Log an SQL error
function dbLogSQLErr($db, $pt = false) {
    $sqlerror = $db->error;
    if($qry = $db->query('INSERT INTO log (message, time, ref_page, type) VALUES ("' . $sqlerror . '", NOW(), "' . $_SERVER['REQUEST_URI'] . '", 1)')) {
        $id = $db->insert_id;

        if($pt)
            echo "<E> A severe error has been logged. Please report error code $id to IT.";
        else
            echo Toast("error", "A severe error has been logged. Please report error code $id to IT.", "Error");
    } else {
        echo "Suffered FATAL ERROR: '" . $db->error . "'~~";
        die();
    }
}

// Sanitize input field
function sanitizeInput($input, $db) {
    return trim($db->real_escape_string($input));
}
