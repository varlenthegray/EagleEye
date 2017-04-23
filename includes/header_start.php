<?php
session_start();

if(!$_SESSION['valid']){
    header("Location: /login.php?pli=true");
}

require_once("language.php"); // require the language file once
require_once("config.php"); // require the config file once

// set the timezone for all PHP information
date_default_timezone_set($_SESSION['userInfo']['timezone']);