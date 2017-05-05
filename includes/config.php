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
// DEV SERVER
/**/
$servername = "localhost";
$username = "devsmc";
$password = "9UnI9Tx721FDBRxiOMmCG3Tv";
$database = "devsmc";
//**/

// PROD SERVER
/*
$servername = "localhost";
$username = "main.smc";
$password = "SmC2017!@#$%&";
$database = "smc";
//**/

// GD PROD SERVER
/*
$servername = "localhost";
$username = "3erp_prod";
$password = "QEdx4VpLp7VG4AxEjqKNm16d";
$database = "prod";
//*/

// connect to the database
$dbconn = new mysqli($servername, $username, $password, $database);

// confirm connected
if($dbconn->connect_error) {
    die("We failed to connect to the database: " . $dbconn->connect_error);
}