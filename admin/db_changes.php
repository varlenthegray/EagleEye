<?php
require ("../includes/header_start.php");

$admin_flag_qry = $dbconn->query("SELECT * FROM admin_flags WHERE flag = 'db_updated'");

if($admin_flag_qry->num_rows > 0) {
    $admin_flag = $admin_flag_qry->fetch_assoc();

    if(!(bool)$admin_flag['value']) {
        echo ($dbconn->query("CREATE TABLE rework_codes (id INT PRIMARY KEY AUTO_INCREMENT,code VARCHAR(15),value VARCHAR(100),deptartment TEXT);")) ? "Successful with create rework_codes table.<br />" : "<b>Error</b> with create rework_codes table.<br />";
        echo ($dbconn->query("INSERT INTO `rework_codes` (`id`, `code`, `value`, `deptartment`) VALUES (NULL, 'MW', 'Material Used Incorrectly', '[\"Box\",\"Finishing\",\"Custom\",\"Assembly\"]'), (NULL, 'MD', 'Material Defective', '[\"Box\",\"Finishing\",\"Custom\",\"Assembly\"]'), (NULL, 'WU', 'Workmanship Unacceptable (Not Knowledge Related)', '[\"Box\",\"Finishing\",\"Custom\",\"Assembly\"]'), (NULL, 'WT', 'Workmanship Training', '[\"Box\",\"Finishing\",\"Custom\",\"Assembly\"]'), (NULL, 'EB', 'Equipment Broken', '[\"Box\",\"Finishing\",\"Custom\",\"Assembly\"]'), (NULL, 'EM', 'Equipment Maintenance Failure', '[\"Box\",\"Finishing\",\"Custom\",\"Assembly\"]'), (NULL, 'ET', 'Equipment Training', '[\"Box\",\"Finishing\",\"Custom\",\"Assembly\"]'), (NULL, 'DI', 'Documentation Incorrect', '[\"Project Manager\",\"Design\",\"Sales Administrator\",\"Engineering\",\"Shop Foreman\",\"Production Administrator\",\"Accounting\"]'), (NULL, 'GA', 'General Accuracy', '[\"Project Manager\",\"Design\",\"Sales Administrator\",\"Engineering\",\"Shop Foreman\",\"Production Administrator\",\"Accounting\"]'), (NULL, 'GT', 'General Training', '[\"Project Manager\",\"Design\",\"Sales Administrator\",\"Engineering\",\"Shop Foreman\",\"Production Administrator\",\"Accounting\"]');")) ? "Successful with inserting rework_codes data.<br />" : "<b>Error</b> with inserting rework_codes data.<br />";
        echo ($dbconn->query("CREATE TABLE rework_log (id INT PRIMARY KEY AUTO_INCREMENT,code_id INT,reporting_uid INT,previous_uid INT,notes INT,cur_oid INT,new_oid INT,timestamp INT(15));")) ? "Successful with creating rework_log table.<br />" : "<b>Error</b> with creating rework_log table.<br />";

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