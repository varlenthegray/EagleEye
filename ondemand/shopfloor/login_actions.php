<?php
require_once ("../../includes/header_start.php");

if($_REQUEST['action'] === 'login') {
    $id = sanitizeInput($_REQUEST['id'], $dbconn);
    $pin = sanitizeInput($_POST['pin'], $dbconn);

    $qry = $dbconn->query("SELECT * FROM user WHERE id = '$id'");

    if($qry->num_rows > 0) {
        $result = $qry->fetch_assoc();

        if($result['pin_code'] === $pin) {
            $_SESSION['shop_user'] = $result;
            $_SESSION['shop_active'] = true;

            echo "success";
        } else {
            die("PIN Failure");
        }
    }
} elseif($_REQUEST['action'] === 'logout') {
    unset($_SESSION['shop_user']);
    unset($_SESSION['shop_active']);
}