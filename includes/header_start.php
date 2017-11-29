<?php
session_start();

$error_display = null;

if(!$_SESSION['valid']){
    header("Location: /login.php?pli=true");
}

switch($_SESSION['userInfo']['account_type']) {
    case '6':
        $whitelist = ["/index.php", "/html/dashboard.php", "/html/employees.php", "/ondemand/op_actions.php", "/ondemand/account_actions.php", "/ondemand/so_actions.php", "/ondemand/display_actions.php", "/html/view_notes.php", "/ondemand/admin/tasks.php"];

        if(!in_array($_SERVER['SCRIPT_NAME'], $whitelist)) {
            header("Location: /index.php");
            $error_display = displayToast("error", "Unable to access page.", "Access Denied");
        }

        break;
}

require("functions.php"); // require functions first
require("language.php"); // require the language file once
require("config.php"); // require the config file once

// set the timezone for all PHP information
date_default_timezone_set($_SESSION['userInfo']['timezone']);

spl_autoload_register(function ($class) {
    include SITE_ROOT . '/includes/classes/' . $class . '.php';
});