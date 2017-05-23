<?php
require ("../includes/header_start.php");


$a = $_SESSION['shop_user']['id'];
$t = json_encode($a);

echo $t;