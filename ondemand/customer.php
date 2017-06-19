<?php
require "../includes/header_start.php";

switch($_GET['action']) {
    case 'add_new':
        $org_name = sanitizeInput($_POST['new_org_name'], $dbconn);
        $addr1 = sanitizeInput($_POST['new_addr_1'], $dbconn);
        $addr2 = sanitizeInput($_POST['new_addr_2'], $dbconn);
        $city = sanitizeInput($_POST['new_city'], $dbconn);
        $state = sanitizeInput($_POST['new_state'], $dbconn);
        $zip = sanitizeInput($_POST['new_zip'], $dbconn);
        $pri_phone = sanitizeInput($_POST['new_phone1'], $dbconn);
        $alt_phone1 = sanitizeInput($_POST['new_phone2'], $dbconn);
        $alt_phone2 = sanitizeInput($_POST['new_phone3'], $dbconn);
        $pri_email = sanitizeInput($_POST['new_email1'], $dbconn);
        $alt_email = sanitizeInput($_POST['new_email2'], $dbconn);
        $so_num = sanitizeInput($_POST['new_so_num'], $dbconn);
        $dealer_code = sanitizeInput($_POST['new_dealer_code'], $dbconn);
        $project = sanitizeInput($_POST['new_project_name'], $dbconn);
        $dealer_contractor = sanitizeInput($_POST['new_dealer_contractor'], $dbconn);
        $project_mgr = sanitizeInput($_POST['new_project_manager'], $dbconn);
        $account_type = sanitizeInput($_POST['new_account_type'], $dbconn);

        $existing_so = $dbconn->query("SELECT * FROM customer WHERE sales_order_num = '$so_num'");

        if($existing_so->num_rows > 0) {
            displayToast("error", "SO already exists. Please insert a different SO number.", "SO Already Exists");
        } else {
            if($dbconn->query("INSERT INTO customer (sales_order_num, dealer_code, org_name, addr_1, addr_2, city, state, zip, pri_phone, alt_phone_1, alt_phone_2, pri_email, alt_email, project, project_manager, account_type) 
          VALUES ('$so_num', '$dealer_code', '$org_name', '$addr1', '$addr2', '$city', '$state', '$zip', '$pri_phone', '$alt_phone1', '$alt_phone2', '$pri_email', '$alt_email', '$project', '$project_mgr', '$account_type')"))
                echo "success";
            else
                dbLogSQLErr($dbconn);
        }
        
        break;
    case 'update_customer':
        $sales_order_num = sanitizeInput($_POST['sales_order_num'], $dbconn);
        $project = sanitizeInput($_POST['project'], $dbconn);
        $dealer_contractor = sanitizeInput($_POST['dealer_contractor'], $dbconn);
        $project_mgr = sanitizeInput($_POST['project_manager'], $dbconn);
        $account_type = sanitizeInput($_POST['account_type'], $dbconn);
        $org_name = sanitizeInput($_POST['org_name'], $dbconn);
        $addr1 = sanitizeInput($_POST['addr_1'], $dbconn);
        $addr2 = sanitizeInput($_POST['addr_2'], $dbconn);
        $city = sanitizeInput($_POST['city'], $dbconn);
        $state = sanitizeInput($_POST['state'], $dbconn);
        $zip = sanitizeInput($_POST['zip'], $dbconn);
        $pri_phone = sanitizeInput($_POST['pri_phone'], $dbconn);
        $alt_phone1 = sanitizeInput($_POST['alt_phone1'], $dbconn);
        $alt_phone2 = sanitizeInput($_POST['alt_phone2'], $dbconn);
        $pri_email = sanitizeInput($_POST['pri_email'], $dbconn);
        $alt_email = sanitizeInput($_POST['alt_email'], $dbconn);
        $so_num = sanitizeInput($_POST['so_num'], $dbconn);
        $dealer_code = sanitizeInput($_POST['dealer_code'], $dbconn);
        $record_id = sanitizeInput($_POST['record_id'], $dbconn);

        $update = $dbconn->query("UPDATE customer SET sales_order_num = '$sales_order_num', project = '$project', dealer_contractor = '$dealer_contractor', 
          project_manager = '$project_mgr', dealer_code = '$dealer_code', account_type = '$account_type', org_name = '$org_name', addr_1 = '$addr1', addr_2 = '$addr2',
          city = '$city', state = '$state', zip = '$zip', pri_phone = '$pri_phone', alt_phone_1 = '$alt_phone1', alt_phone_2 = '$alt_phone2', pri_email = '$pri_email', alt_email = '$alt_email'
          WHERE id = $record_id");

        if($update) {
            echo "success";
        } else {
            dbLogSQLErr($dbconn);
        }
        
        break;
    default:
        die();
        break;
}