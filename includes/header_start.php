<?php
session_start();

$error_display = null;

$nologin[] = '/display/finishing/production.php';
$nologin[] = '/out.php';
$nologin[] = '/ondemand/account_actions.php';

if(!$_SESSION['valid'] && !in_array($_SERVER['SCRIPT_NAME'], $nologin, true)) {
    header('Location: /login.php?pli=true');
}

if(empty($_SESSION['shop_user']) && $_SERVER['SCRIPT_NAME'] !== '/employees.php' && $_SERVER['SCRIPT_NAME'] !== '/ondemand/account_actions.php' && !in_array($_SERVER['SCRIPT_NAME'], $nologin, true)) {
    header('Location: /employees.php');
} else {
    switch($_SESSION['userInfo']['account_type']) {
        case '6':
            $whitelist = ['/main.php', '/html/dashboard.php', '/html/employees.php', '/employees.php', '/ondemand/op_actions.php', '/ondemand/account_actions.php', '/ondemand/so_actions.php', '/ondemand/display_actions.php', '/html/view_notes.php', '/ondemand/admin/tasks.php'];

            if(!in_array($_SERVER['SCRIPT_NAME'], $whitelist, true)) {
                header('Location: /main.php');
                $error_display = displayToast('error', 'Unable to access page.', 'Access Denied');
            }

            break;
    }
}

require 'language.php'; // require the language file once
require 'config.php'; // require the config file once
require 'classes/bouncer.php'; // require the bouncer
require SITE_ROOT . '/assets/plugins/flowroute/vendor/autoload.php'; // flowroute and SMS
require SITE_ROOT . '/assets/plugins/flowroute/src/Configuration.php'; // more flowroute and SMS
require 'functions.php'; // require functions first

// set the timezone for all PHP information
date_default_timezone_set($_SESSION['userInfo']['timezone']);

$bouncer = new \Bouncer\bouncer();

use FlowrouteNumbersAndMessagingLib\Models;

// Flowroute API details
$username = '46449714';
$password = 'aQlpTe9RgTVWfDL6jw0BEKSo3r4hPfxN';

// API interface
$flowroute = new FlowrouteNumbersAndMessagingLib\FlowrouteNumbersAndMessagingClient($username, $password);