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
define('DB_USERNAME', '3erp_stratis');
define('DB_PASS', 'MWwYWn6eohulDSxL');
define('DB_DATABASE', '3erp_stratis');

if(file_exists('C:/Users/Ben')) {
  define('SITE_ROOT', 'C:/Users/Ben/OneDrive/SMCM/Eagle Eye/stratis'); // Surface Site
  define('SERVER_TYPE', 'local');
} elseif(file_exists('C:/Users/subz3')) {
  define ('SITE_ROOT', 'C:/Users/subz3/OneDrive/SMCM/Eagle Eye/stratis'); // Desktop site
  define('SERVER_TYPE', 'local');
} else {
  define('SITE_ROOT', '/home/threeerp/domains/stratis.3erp.us/public_html/'); // Prod Site
  define('SERVER_TYPE', 'prod');
}

// connect to the database
$dbconn = new mysqli(DB_SERVER_NAME, DB_USERNAME, DB_PASS, DB_DATABASE);

$dbconn->set_charset('utf8');

// confirm connected
if($dbconn->connect_error) {
  die('We failed to connect to the database: ' . $dbconn->connect_error);
}