<?php
session_start();

if(!$_SESSION['valid']){
    header("Location: /login.php?pli=true");
}

require_once("language.php"); // require the language file once
require_once("config.php"); // require the config file once