<?php
require ("../includes/header_start.php");

$intro_std = <<<HEREDOC
intro.setOptions({
    steps: [
        {intro: "New features have been released since the last time you have visited!"},
        {intro: "From now on, all new features will be introduced to you through the use of this tutorial."},
        {
            element: document.querySelector('#btn-feedback'),
            intro: "This button will allow you to send feedback directly to the development team for EagleEye.",
            position: "left"
        },
        {
            element: document.querySelector('#nav_tasks'),
            intro: "This navigation option allows you to see all open tasks that have been submitted."
        },
		{intro: "Additionally, the following bugs have been resolved:<ul><li>Scrollbar for Job Queue, Active Jobs and Recently Completed jobs</li><li>Job Completion screen not disappearing for certain tasks</li></ul>"},
		{intro: "As a final note, please <strong>DO NOT</strong> insert a new 'SO' using the 'Add SO' button without refreshing the page first. This also goes with clocking in to a job under 'Individual'.<br><br>This bug will be fixed in the next few releases."},
        {intro: "Please click 'Done' and this will not display again."}]
});
HEREDOC;

$intro_shop = <<<HEREDOC
intro.setOptions({
    steps: [
        {intro: "New features have been released since the last time you have visited!"},
        {intro: "From now on, all new features will be introduced to you through the use of this tutorial."},
        {
            element: document.querySelector('#btn-feedback'),
            intro: "This button will allow you to send feedback directly to the development team for EagleEye. Please feel free to enter feedback into the system.",
            position: "left"
        },
        {intro: "Please click 'Done' and this will not display again."}]
});
HEREDOC;


$intro_std = sanitizeInput($intro_std);
$intro_shop = sanitizeInput($intro_shop);

$admin_flag_qry = $dbconn->query("SELECT * FROM admin_flags WHERE flag = 'db_updated'");

if($admin_flag_qry->num_rows > 0) {
    $admin_flag = $admin_flag_qry->fetch_assoc();

    if(!(bool)$admin_flag['value']) {
        //echo ($dbconn->query("UPDATE user SET intro_code = '$intro_std' WHERE id <> 16;")) ? "Successful with updating intro_code for standard users.<br />" : "<b>Error</b> with updating intro_code for standard users.<br />";
        //echo ($dbconn->query("UPDATE user SET intro_code = '$intro_shop' WHERE id = 16;")) ? "Successful with updating intro_code for shop user.<br />" : "<b>Error</b> with updating intro_code for shop user.<br />";
        echo ($dbconn->query("CREATE TABLE op_inprogress (id INT PRIMARY KEY AUTO_INCREMENT,room_id INT,operation_id INT,active BOOLEAN,rework_num INT,partially_completed BOOLEAN,created INT(20),active_employees VARCHAR(100),subtask VARCHAR(50),otf BOOLEAN,assigned_to INT);")) ? "Successful with creating ops in progress.<br />" : "<b>Error</b> with creating ops in progress.<br />";
        echo ($dbconn->query("CREATE TABLE op_audittrail (id INT PRIMARY KEY AUTO_INCREMENT,room_id INT,operation_id INT,start_time INT(20),end_time INT(20),active BOOLEAN,completed BOOLEAN,rework BOOLEAN,notes TEXT,resumed BOOLEAN,partially_completed BOOLEAN,timestamp INT(20),active_employees VARCHAR(100),subtask VARCHAR(50),otf BOOLEAN,assigned_to INT);")) ? "Successful with creating new op audit trail.<br />" : "<b>Error</b> with creating new op audit trail.<br />";

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