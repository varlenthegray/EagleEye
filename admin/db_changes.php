<?php
require ("../includes/header_start.php");

$admin_flag_qry = $dbconn->query("SELECT * FROM admin_flags WHERE flag = 'db_updated'");

if($admin_flag_qry->num_rows > 0) {
    $admin_flag = $admin_flag_qry->fetch_assoc();

    if(!(bool)$admin_flag['value']) {
        echo ($dbconn->query("UPDATE rooms SET iteration = iteration + 1;")) ? "Successful with updating iteration to +1.<br />" : "<b>Error</b> with updating iteration to +1.<br />";
        echo ($dbconn->query("ALTER TABLE op_queue ADD assigned_time DOUBLE NULL;")) ? "Successful with altering op_queue adding assigned_time setting default to 72 (hours).<br />" : "<b>Error</b> with altering op_queue adding assigned_time setting default to 72 (hours).<br />";

        echo "<h1>Database prepared.</h1>";

        $dbconn->query("UPDATE admin_flags SET value = TRUE WHERE flag = 'db_updated'");
    } else {
        echo "<h1>Database indicates already updated.</h1>";
    }
} else {
    echo ($dbconn->query("CREATE TABLE admin_flags (id INT PRIMARY KEY AUTO_INCREMENT,flag VARCHAR(50),value BOOLEAN);")) ? "Successful with creating admin_flags table.<br />" : "<b>Error</b> with creating admin_flags table.<br />";
    echo ($dbconn->query("INSERT INTO admin_flags (flag, value) VALUES ('db_updated', FALSE);")) ? "Successful with setting database as updated.<br />" : "<b>Error</b> with setting database as updated.<br />";

    echo "<h1>First run with 'Admin Flags' - please re-run script.</h1>";
}