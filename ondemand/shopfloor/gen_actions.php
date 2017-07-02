<?php
require_once ("../../includes/header_start.php");

function createOpQueue($bracket_pub, $bracket, $operation, $roomid) {
    global $dbconn;
    
    // now we need to create the ops and/or activate the appropriate ops based on what's selected (and deactivate any old ones) if bracket is published
    if((bool)$bracket_pub) {
        $ops = array();

        // bracket is published, time to build the bracket operations
        $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

        // create an array of all the ops
        while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
            // if the operation is not an x98 operation then add it to the array, otherwise exclude it
            if((int)substr($all_bracket_ops['op_id'], -2) !== 98) {
                $ops[] = $all_bracket_ops['id'];
            }
        }

        // grab all operations in the queue for this room that are not OTF
        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$roomid' AND otf_created = FALSE");

        // if we were able to find any operations in the queue
        if($op_queue_qry->num_rows > 0) {
            // for every operation
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                // lets find out if this operation is part of the bracket
                if(in_array($op_queue['operation_id'], $ops)) {
                    // it's part of the bracket, is it this operation?
                    if($op_queue['operation_id'] === $operation) {
                        // it is this operation, is it unpublished?
                        if(!(bool)$op_queue['published']) {
                            // publish it, foo!
                            $dbconn->query("UPDATE op_queue SET published = TRUE where id = '{$op_queue['id']}'");
                        }
                    } else {
                        // nope, it's part of the queue but it's not this operation, lets unpublish it!
                        $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                    }
                }
            }
        } else {
            // no operations exist in the queue for this room that are NOT OTF! BLANK SLATE BABY!

            // grab the room information for creation of the queued operation
            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$roomid'");
            $room = $room_qry->fetch_assoc();

            // now, create the operation that SHOULD be active
            $dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, active, completed, rework, qty_requested, 
             partially_completed, created, iteration) VALUES ('$roomid', '{$room['so_parent']}', '{$room['room']}', '$operation', 4, FALSE, FALSE, FALSE, 1, FALSE, 
              UNIX_TIMESTAMP(), '{$room['iteration']}')");
        }

        // we've deactivated and/or published all operations related to the queue, now lets create the operation in the queue if it's not there
        $ind_op_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$operation' AND room_id = '$roomid'");

        if($ind_op_qry->num_rows === 0) {
            // grab the room information for creation of the queued operation
            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$roomid'");
            $room = $room_qry->fetch_assoc();

            // now, create the operation that SHOULD be active
            $dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, active, completed, rework, qty_requested, 
             partially_completed, created, iteration) VALUES ('$roomid', '{$room['so_parent']}', '{$room['room']}', '$operation', 4, FALSE, FALSE, FALSE, 1, FALSE, 
              UNIX_TIMESTAMP(), '{$room['iteration']}')");
        }
    } else {
        // bracket is NOT published, tis time to deactivate ALL operations
        $ops = array();

        // bracket is NOT published, time to build the bracket
        $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

        // create an array of all the ops
        while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
            $ops[] = $all_bracket_ops['id'];
        }

        // time to find all ops in the query that contain this room
        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$roomid' AND completed = FALSE AND published = TRUE AND otf_created = FALSE");

        if($op_queue_qry->num_rows > 0) {
            // if there are operations that are not completed, are published and are not OTF
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                // for all operations in the queue
                if(in_array($op_queue['operation_id'], $ops)) {
                    // if it's a bracket operation deactivate it
                    $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                }
            }
        }
    }
}

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
    case 'save_active_ops':
        $ops = $_REQUEST['active_ops'];
        $roomid = sanitizeInput($_REQUEST['roomid']);

        $sales_op = sanitizeInput($_REQUEST['sales_bracket']);
        $sample_op = sanitizeInput($_REQUEST['sample_bracket']);
        $preprod_op = sanitizeInput($_REQUEST['preproduction_bracket']);
        $doordrawer_op = sanitizeInput($_REQUEST['doordrawer_bracket']);
        $main_op = sanitizeInput($_REQUEST['main_bracket']);
        $custom_op = sanitizeInput($_REQUEST['custom_bracket']);
        $shipping_op = sanitizeInput($_REQUEST['shipping_bracket']);
        $install_op = sanitizeInput($_REQUEST['install_bracket']);

        $sales_pub = (!empty($_REQUEST['sales_published'])) ? sanitizeInput($_REQUEST['sales_published']) : 0;
        $sample_pub = (!empty($_REQUEST['sample_published'])) ? sanitizeInput($_REQUEST['sample_published']) : 0;
        $preprod_pub = (!empty($_REQUEST['preprod_published'])) ? sanitizeInput($_REQUEST['preprod_published']) : 0;
        $doordrawer_pub = (!empty($_REQUEST['doordrawer_published'])) ? sanitizeInput($_REQUEST['doordrawer_published']) : 0;
        $main_pub = (!empty($_REQUEST['main_published'])) ? sanitizeInput($_REQUEST['main_published']) : 0;
        $custom_pub = (!empty($_REQUEST['custom_published'])) ? sanitizeInput($_REQUEST['custom_published']) : 0;
        $shipping_pub = (!empty($_REQUEST['shipping_published'])) ? sanitizeInput($_REQUEST['shipping_published']) : 0;
        $install_pub = (!empty($_REQUEST['install_published'])) ? sanitizeInput($_REQUEST['install_published']) : 0;

        if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$ops' WHERE id = '$roomid'")) {
            $dbconn->query("UPDATE rooms SET sales_bracket = '$sales_op', preproduction_bracket = '$preprod_op', sample_bracket = '$sample_op', doordrawer_bracket = '$doordrawer_op',
             custom_bracket = '$custom_op', main_bracket = '$main_op', shipping_bracket = '$shipping_op', install_bracket = '$install_op', sales_published = '$sales_pub', sample_published = '$sample_pub',
              preproduction_published = '$preprod_pub', doordrawer_published = '$doordrawer_pub', main_published = '$main_pub', custom_published = '$custom_pub', shipping_published = '$shipping_pub',
               install_bracket_published = '$install_pub' WHERE id = '$roomid'");

            createOpQueue($sales_pub, 'Sales', $sales_op, $roomid);
            createOpQueue($sample_pub, 'Sample', $sample_op, $roomid);
            createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $roomid);
            createOpQueue($doordrawer_pub, 'Drawer & Doors', $doordrawer_op, $roomid);
            createOpQueue($main_pub, 'Main', $main_op, $roomid);
            createOpQueue($custom_pub, 'Custom', $custom_op, $roomid);
            createOpQueue($shipping_pub, 'Shipping', $shipping_op, $roomid);
            createOpQueue($install_pub, 'Installation', $install_op, $roomid);

            echo displayToast("success", "Successfully updated the bracket.", "Bracket Updated");
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'save_so':
        $account_type = sanitizeInput($_REQUEST['account_type']);
        $salesperson = sanitizeInput($_REQUEST['salesperson']);
        $contractor = sanitizeInput($_REQUEST['contractor']);
        $project = sanitizeInput($_REQUEST['project']);
        $project_addr = sanitizeInput($_REQUEST['project_addr']);
        $contractor_code = sanitizeInput($_REQUEST['contractor_code']);
        $p_landline = sanitizeInput($_REQUEST['p_landline']);
        $p_city = sanitizeInput($_REQUEST['p_city']);
        $p_state = sanitizeInput($_REQUEST['p_state']);
        $p_zip = sanitizeInput($_REQUEST['p_zip']);
        $contact_1 = sanitizeInput($_REQUEST['contact_1']);
        $cell_1 = sanitizeInput($_REQUEST['cell_1']);
        $business_1 = sanitizeInput($_REQUEST['business_1']);
        $email_1 = sanitizeInput($_REQUEST['email_1']);
        $contact_2 = sanitizeInput($_REQUEST['contact_2']);
        $cell_2 = sanitizeInput($_REQUEST['cell_2']);
        $business_2 = sanitizeInput($_REQUEST['business_2']);
        $email_2 = sanitizeInput($_REQUEST['email_2']);
        $physical_addr = sanitizeInput($_REQUEST['physical_addr']);
        $ph_city = sanitizeInput($_REQUEST['ph_city']);
        $ph_state = sanitizeInput($_REQUEST['ph_state']);
        $ph_zip = sanitizeInput($_REQUEST['ph_zip']);
        $email_address = sanitizeInput($_REQUEST['email_address']);
        $cell_phone = sanitizeInput($_REQUEST['cell_phone']);
        $so_num = sanitizeInput($_REQUEST['so_num']);

        $dbconn->query("UPDATE customer SET account_type = '$account_type', salesperson = '$salesperson', dealer_contractor = '$contractor', project = '$project', addr_1 = '$project_addr',
         dealer_code = '$contractor_code', pri_phone = '$p_landline', city = '$p_city', state = '$p_state', zip = '$p_zip', contact_1 = '$contact_1', contact_1_cell = '$cell_1', contact_1_business_ph = '$business_1',
          contact_1_email = '$email_1', contact_2 = '$contact_2', contact_2_cell = '$cell_2', contact_2_business_ph = '$business_2', contact_2_email = '$email_2', phys_addr = '$physical_addr', phys_city = '$ph_city',
           phys_state = '$ph_state', phys_zip = '$ph_zip', global_email = '$email_address', global_cell = '$cell_phone' WHERE sales_order_num = '$so_num'");

        echo displayToast("success", "Successfully updated Sales Order information.", "Updated Information");

        break;
    case 'add_iteration':
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
            $delivery_date = strtotime($delivery_date);
        }

        $ind_bracket_buildout = array();

        $ind_bracket_qry = $dbconn->query("SELECT * FROM operations WHERE always_visible = FALSE ORDER BY op_id ASC");

        while($ind_bracket = $ind_bracket_qry->fetch_assoc()) {
            $ind_bracket_buildout[] = $ind_bracket['id'];
        }

        $ind_bracket_final = json_encode($ind_bracket_buildout);

        if($dbconn->query("INSERT INTO rooms (so_parent, room, room_name, product_type, sales_bracket, sales_bracket_priority, preproduction_bracket, 
         preproduction_bracket_priority, sample_bracket, sample_bracket_priority, doordrawer_bracket, doordrawer_bracket_priority, custom_bracket, custom_bracket_priority, main_bracket, 
          main_bracket_priority, individual_bracket_buildout, order_status, shipping_bracket, shipping_bracket_priority, install_bracket, install_bracket_priority, delivery_date, iteration) VALUES 
           ('$sonum', '$room', '$room_name', '$product_type', 1, 4, 85, 4, 83, 4, 38, 4, 45, 4, 50, 4, '$ind_bracket_final', '$order_status', 66, 4, 15, 4, '$delivery_date', $iteration);")) {
            if(!empty($notes)) {
                $inserted_id = $dbconn->insert_id;

                if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$inserted_id')")) {
                    echo displayToast("success", "Successfully added iteration.", "Iteration Created");
                } else {
                    dbLogSQLErr($dbconn);
                }
            } else {
                echo displayToast("success", "Successfully added iteration.", "Iteration Created");
            }
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
}