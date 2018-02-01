<?php
session_start();

$error_display = null;

if(!$_SESSION['valid'] && $_SERVER['SCRIPT_NAME'] !== "/display/finishing/production.php"){
    header("Location: /login.php?pli=true");
}

if(empty($_SESSION['shop_user']) && $_SERVER['SCRIPT_NAME'] !== "/employees.php" && $_SERVER['SCRIPT_NAME'] !== "/ondemand/account_actions.php" && $_SERVER['SCRIPT_NAME'] !== "/display/finishing/production.php") {
    header("Location: /employees.php");
} else {
    switch($_SESSION['userInfo']['account_type']) {
        case '6':
            $whitelist = ["/index.php", "/html/dashboard.php", "/html/employees.php", "/employees.php", "/ondemand/op_actions.php", "/ondemand/account_actions.php", "/ondemand/so_actions.php", "/ondemand/display_actions.php", "/html/view_notes.php", "/ondemand/admin/tasks.php"];

            if(!in_array($_SERVER['SCRIPT_NAME'], $whitelist)) {
                header("Location: /index.php");
                $error_display = displayToast("error", "Unable to access page.", "Access Denied");
            }

            break;
    }
}

require("functions.php"); // require functions first
require("language.php"); // require the language file once
require("config.php"); // require the config file once

// set the timezone for all PHP information
date_default_timezone_set($_SESSION['userInfo']['timezone']);