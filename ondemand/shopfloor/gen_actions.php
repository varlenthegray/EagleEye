<?php
require_once ("../../includes/header_start.php");

switch($_REQUEST['action']) {
    case 'insert_new_room':
        $dealer_code = sanitizeInput($_REQUEST['dealer_code']);
        $account_type = sanitizeInput($_REQUEST['account_type']);
        $dealer = sanitizeInput($_REQUEST['dealer']);
        $contact = sanitizeInput($_REQUEST['contact']);
        $phone_number = sanitizeInput($_REQUEST['phone_number']);
        $email = sanitizeInput($_REQUEST['email']);
        $salesperson = sanitizeInput($_REQUEST['salesperson']);
        $shipping_addr = sanitizeInput($_REQUEST['shipping_addr']);
        $city = sanitizeInput($_REQUEST['city']);
        $p_state = sanitizeInput($_REQUEST['p_state']);
        $zip = sanitizeInput($_REQUEST['zip']);
        $delivery_date = sanitizeInput($_REQUEST['delivery_date']);
        $room = sanitizeInput($_REQUEST['room']);
        $product_type = sanitizeInput($_REQUEST['product_type']);
        $iteration = sanitizeInput($_REQUEST['iteration']);
        $order_status = sanitizeInput($_REQUEST['order_status']);
        $days_to_ship = sanitizeInput($_REQUEST['days_to_ship']);
        $room_name = sanitizeInput($_REQUEST['room_name']);
        $notes = sanitizeInput($_REQUEST['room_notes']);
        $sonum = sanitizeInput($_REQUEST['sonum']);

        $delivery_date = strtotime($delivery_date);

        $bracket_buildout = array();

        $bracket_buildout_qry = $dbconn->query("SELECT * FROM operations WHERE always_visible = FALSE");

        while($operation = $bracket_buildout_qry->fetch_assoc()) {
            $bracket_buildout[] = $operation['id'];
        }

        $ind_bracket = json_encode($bracket_buildout);

        $isthere_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$sonum' AND room = '$room' AND iteration = '$iteration'");

        if($isthere_qry->num_rows === 0) {
            if($dbconn->query("INSERT INTO rooms (so_parent, room, room_name, product_type, remodel_reqd, room_notes, sales_bracket, 
              sales_bracket_priority, preproduction_bracket, preproduction_bracket_priority, sample_bracket, sample_bracket_priority, 
              doordrawer_bracket, doordrawer_bracket_priority, custom_bracket, custom_bracket_priority, main_bracket, main_bracket_priority, 
              individual_bracket_buildout, order_status, shipping_bracket, shipping_bracket_priority, install_bracket, install_bracket_priority, delivery_date) 
              VALUES ('$sonum','$room','$room_name','$product_type','0','$notes','1','4','85','4','83','4','38','4','45','4','50','4','$ind_bracket','$order_status','66',
              '4','15','4','$delivery_date')")) {
                echo displayToast("success", "Addeded room successfully.", "Room Added");
            } else {
                var_dump(http_response_code(400));
            }
        } else {
            echo displayToast("error", "This room has already been created with this iteration.", "Unable to Save");
        }

        break;
    case 'calc_del_date':
        $days_to_ship = sanitizeInput($_REQUEST['days_to_ship']);

        echo calcDelDate($days_to_ship);
        break;
    case 'get_dealer_info':
        $dealer = sanitizeInput($_REQUEST['dealer_code']);

        $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE id = '$dealer'");

        if($dealer_qry->num_rows > 0) {
            $dealer_info = $dealer_qry->fetch_assoc();

            echo json_encode($dealer_info);
        }

        break;
    case 'update_room':
        $dealer_code = sanitizeInput($_REQUEST['dealer_code']);
        $account_type = sanitizeInput($_REQUEST['account_type']);
        $dealer = sanitizeInput($_REQUEST['dealer']);
        $contact = sanitizeInput($_REQUEST['contact']);
        $phone_number = sanitizeInput($_REQUEST['phone_number']);
        $email = sanitizeInput($_REQUEST['email']);
        $salesperson = sanitizeInput($_REQUEST['salesperson']);
        $shipping_addr = sanitizeInput($_REQUEST['shipping_addr']);
        $city = sanitizeInput($_REQUEST['city']);
        $p_state = sanitizeInput($_REQUEST['p_state']);
        $zip = sanitizeInput($_REQUEST['zip']);
        $delivery_date = sanitizeInput($_REQUEST['delivery_date']);
        $room = sanitizeInput($_REQUEST['room']);
        $product_type = sanitizeInput($_REQUEST['product_type']);
        $iteration = sanitizeInput($_REQUEST['iteration']);
        $order_status = sanitizeInput($_REQUEST['order_status']);
        $days_to_ship = sanitizeInput($_REQUEST['days_to_ship']);
        $room_name = sanitizeInput($_REQUEST['room_name']);
        $notes = sanitizeInput($_REQUEST['room_notes']);
        $sonum = sanitizeInput($_REQUEST['sonum']);
        $room = sanitizeInput($_REQUEST['room']);
        $roomid = sanitizeInput($_REQUEST['roomid']);

        if(empty($delivery_date)) {
            $delivery_date = '';
        } elseif(!empty($delivery_date)) {
             $delivery_date = ",delivery_date = '" . strtotime($delivery_date) . "'";
         }

        if($dbconn->query("UPDATE rooms SET product_type = '$product_type', order_status = '$order_status', days_to_ship = '$days_to_ship', room_name = '$room_name' $delivery_date WHERE id = $roomid")) {
            if(!empty($notes)) {
                if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$roomid')")) {
                    echo displayToast("success", "Successfully updated the room.", "Room Updated");
                } else {
                    dbLogSQLErr($dbconn);
                }
            } else {
                echo displayToast("success", "Successfully updated the room.", "Room Updated");
            }
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
}