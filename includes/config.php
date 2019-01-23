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

// possible development paths
$available_paths[] = ['path' => 'C:/Users/Ben/OneDrive/SMCM/Eagle Eye/SMCDev', 'db' => '3erp_dev'];
$available_paths[] = ['path' => 'C:/Users/subz3/OneDrive/SMCM/Eagle Eye/SMCDev', 'db' => '3erp_dev'];
$available_paths[] = ['path' => '/home/dev/public_html', 'db' => '3erp_dev'];
$available_paths[] = ['path' => 'C:/wamp64/www_eagleeye', 'db' => '3erp_dev'];

// live server path
$available_paths[] = ['path' => '/home/threeerp/public_html', 'db' => '3erp'];

foreach($available_paths AS $ind_path) {
  if(file_exists($ind_path['path'])) {
    define('SITE_ROOT', $ind_path['path']);
    define('DB_DATABASE', $ind_path['db']);
  }
}

// connect to the database
$dbconn = new mysqli(DB_SERVER_NAME, DB_USERNAME, DB_PASS, DB_DATABASE);

$dbconn->set_charset('utf8');

// confirm connected
if($dbconn->connect_error) {
  die('We failed to connect to the database: ' . $dbconn->connect_error);
}