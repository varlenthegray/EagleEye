<?php
require '../includes/header_start.php';

switch($_REQUEST['action']) {
    case 'get_dealer_info':
        $dealer = sanitizeInput($_REQUEST['dealer_code']);

        $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE id = '$dealer'");

        if($dealer_qry->num_rows > 0) {
            $dealer_info = $dealer_qry->fetch_assoc();

            echo json_encode($dealer_info);
        }

        break;
}