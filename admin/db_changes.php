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
        {intro: "Please click 'Done' and this will not display after you log out and log back in."}]
});
HEREDOC;

$intro_shop = <<<HEREDOC
intro.setOptions({
    steps: [
        {intro: "New features have been released since the last time you have visited!"},
        {intro: "From now on, all new features will be introduced to you through the use of this tutorial."},
        {
            element: document.querySelector('#btn-feedback'),
            intro: "This button will allow you to send feedback directly to the development team for EagleEye.",
            position: "left"
        },
        {intro: "Please click 'Done' and this will not display after you log out and log back in."}]
});
HEREDOC;


$admin_flag_qry = $dbconn->query("SELECT * FROM admin_flags WHERE flag = 'db_updated'");

if($admin_flag_qry->num_rows > 0) {
    $admin_flag = $admin_flag_qry->fetch_assoc();

    if(!(bool)$admin_flag['value']) {
        echo ($dbconn->query("CREATE TABLE tasks (id INT PRIMARY KEY AUTO_INCREMENT,name VARCHAR(200),description TEXT,created INT(20),last_updated INT(20),priority VARCHAR(25) DEFAULT 'Low',assigned_to INT,due_date INT(20),submitted_by INT,resolved BOOLEAN,pct_completed DOUBLE DEFAULT 0.00,eta_hrs DOUBLE DEFAULT 1.0);")) ? "Successful with creating tasks table.<br />" : "<b>Error</b> with creating tasks table.<br />";
        echo ($dbconn->query("CREATE TABLE log_tasks (id INT PRIMARY KEY AUTO_INCREMENT,task_id INT,notes TEXT,time_updated INT(20),new_eta DOUBLE,new_priority INT,new_pct_completed DOUBLE,new_due_date INT(20),new_asignee INT,now_resolved BOOLEAN);")) ? "Successful with creating tasks log.<br />" : "<b>Error</b> with creating tasks log.<br />";
        echo ($dbconn->query("ALTER TABLE user ADD intro_code LONGTEXT NULL;")) ? "Successful with altering user adding intro_code.<br />" : "<b>Error</b> with altering user adding intro_code.<br />";
        echo ($dbconn->query("UPDATE user SET intro_code = '$intro_std' WHERE id <> 16;")) ? "Successful with updating intro_code for standard users.<br />" : "<b>Error</b> with updating intro_code for standard users.<br />";
        echo ($dbconn->query("UPDATE user SET intro_code = '$intro_shop' WHERE id = 16;")) ? "Successful with updating intro_code for shop user.<br />" : "<b>Error</b> with updating intro_code for shop user.<br />";

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