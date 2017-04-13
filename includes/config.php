<?php
/**
 * Created with the intent to configure the application.
 * User: Ben
 * Date: 3/17/2017
 * Time: 10:13 PM
 */
require_once ("functions.php");

// initial MySQL configuration
$servername = "localhost";
$username = "main.smc";
$password = "SmC2017!@#$%&";
$database = "smc";

// connect to the database
$dbconn = new mysqli($servername, $username, $password, $database);

// confirm connected
if($dbconn->connect_error) {
    die("We failed to connect to the database: " . $dbconn->connect_error);
}