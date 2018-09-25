<?php
/**
 * Created with the intent to configure the application.
 * User: Ben
 * Date: 3/17/2017
 * Time: 10:13 PM
 */
// initial MySQL configuration
$server = explode('.', $_SERVER['HTTP_HOST']);

define('DB_SERVER_NAME', 'localhost');
define('DB_USERNAME', 'threeerp');
define('DB_PASS', '8h294et9hVaLvp0K*s!&');

// determination for type of server we're on and what connection to throw
switch($server[0]) {
  case 'dev':
    define('DB_DATABASE', '3erp_dev');
    define('SITE_ROOT', '/home/dev/public_html'); // Current Dev Site

    break;

  case 'eagleeye':
//    define('DB_DATABASE', '3erp_old_pricing');
    define('DB_DATABASE', '3erp_dev');

    if(file_exists('C:/Users/Ben')) {
      define('SITE_ROOT', 'C:/Users/Ben/OneDrive/SMCM/Eagle Eye/SMCDev'); // Server Site
    } else {
      define ('SITE_ROOT', 'C:/Users/subz3/OneDrive/SMCM/Eagle Eye/SMCDev'); // Desktop site
    }

    break;

  default:
    define('DB_DATABASE', '3erp');
    define('SITE_ROOT', '/home/threeerp/public_html'); // Prod Site

    break;
}

// connect to the database
$dbconn = new mysqli(DB_SERVER_NAME, DB_USERNAME, DB_PASS, DB_DATABASE);

$dbconn->set_charset('utf8');

// confirm connected
if($dbconn->connect_error) {
  die('We failed to connect to the database: ' . $dbconn->connect_error);
}