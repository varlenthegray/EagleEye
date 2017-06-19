<?php
session_start();

if(!$_SESSION['valid']){
    header("Location: /login.php?pli=true");
}

switch($_SESSION['userInfo']['account_type']) {
    case '6':
        $whitelist = ["/shopfloor/login.php", "/shopfloor/index.php", "/ondemand/shopfloor/job_actions.php", "/ondemand/shopfloor/job_functions.php", "/ondemand/shopfloor/login_actions.php"];

        if(!in_array($_SERVER['SCRIPT_NAME'], $whitelist)) {
            header("Location: /shopfloor/index.php");
        }

        break;
}

require("functions.php"); // require functions first
require("language.php"); // require the language file once
require("config.php"); // require the config file once

// set the timezone for all PHP information
date_default_timezone_set($_SESSION['userInfo']['timezone']);

