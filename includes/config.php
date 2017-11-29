<?php
/**
 * Created with the intent to configure the application.
 * User: Ben
 * Date: 3/17/2017
 * Time: 10:13 PM
 */
// initial MySQL configuration
/*************************************************************
 * FILE IS EXCLUDED FROM AUTO-UPLOAD IN PHPSTORM TO PROD SERVER!
 *************************************************************/
$server = explode(".", $_SERVER['HTTP_HOST']);

// determination for type of server we're on and what connection to throw
switch($server[0]) {
    case 'dev':
        $servername = "localhost";
        $username = "threeerp";
        $password = "8h294et9hVaLvp0K*s!&";
        $database = "3erp_dev";

        define('SITE_ROOT', '/home/threeerp/domains/dev.3erp.us/public_html'); // Current Dev Site

        break;

    case 'd2':
        $servername = "localhost";
        $username = "threeerp";
        $password = "8h294et9hVaLvp0K*s!&";
        $database = "3erp_dev";

        define('SITE_ROOT', '/home/threeerp/domains/d2.3erp.us/public_html'); // New Dev Site

        break;

    default:
        $servername = "localhost";
        $username = "threeerp";
        $password = "8h294et9hVaLvp0K*s!&";
        $database = "3erp";

        define('SITE_ROOT', '/home/threeerp/public_html'); // Prod Site

        break;
}

// connect to the database
$dbconn = new mysqli($servername, $username, $password, $database);

$dbconn->set_charset('utf8');

// confirm connected
if($dbconn->connect_error) {
    die("We failed to connect to the database: " . $dbconn->connect_error);
}