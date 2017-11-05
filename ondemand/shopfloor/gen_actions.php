<?php
require_once ("../../includes/header_start.php");

function createOpQueue($bracket_pub, $bracket, $operation, $roomid) {
    global $dbconn;

    $op_queue_qry = $dbconn->query("SELECT op_queue.id AS QID, op_queue.*, operations.* FROM op_queue LEFT JOIN operations ON op_queue.operation_id = operations.id WHERE room_id = '$roomid' AND published = TRUE AND bracket = '$bracket'");

    // if the bracket is published
    if((bool)$bracket_pub) {
        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                if($op_queue['operation_id'] === $operation && (bool)$op_queue['active']) {
                    // the exact operation is currently active and we cannot take any further action
                    echo displayToast("error", "Operation is active presently inside of $bracket.", "Active Operation");
                    return;
                } else {
                    // deactivate operations
                    $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['QID']}'");
                }
            }
        }

        // grab the entire room's information
        $dbinfo_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$roomid'");
        $dbinfo = $dbinfo_qry->fetch_assoc();

        // now that we've cleaned up the operations; it's time to get that operation flowing
        $dbconn->query("INSERT INTO op_queue (room_id, operation_id, start_time, end_time, active, 
         completed, rework, notes, resumed_time, partially_completed, created) VALUES ('$roomid', 
          '$operation', NULL, NULL, FALSE, FALSE, FALSE, NULL, NULL, NULL, UNIX_TIMESTAMP())");
    } else {
        while($op_queue = $op_queue_qry->fetch_assoc()) {
            $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['QID']}'");
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
        $delivery_date = sanitizeInput($_REQUEST['delivery_date']);
        $product_type = sanitizeInput($_REQUEST['product_type']);
        $iteration = sanitizeInput($_REQUEST['iteration']);
        $order_status = sanitizeInput(html_entity_decode($_REQUEST['order_status']));
        $days_to_ship = sanitizeInput($_REQUEST['days_to_ship']);
        $room_name = sanitizeInput($_REQUEST['room_name']);
        $notes = sanitizeInput($_REQUEST['room_notes']);
        $room_id = sanitizeInput($_REQUEST['roomid']);

        if(empty($delivery_date)) {
            $delivery_date = null;
        } elseif(!empty($delivery_date)) {
             $delivery_date = ",delivery_date = " . strtotime($delivery_date) . "";
         }

        if($dbconn->query("UPDATE rooms SET product_type = '$product_type', order_status = '$order_status', days_to_ship = '$days_to_ship', room_name = '$room_name' $delivery_date  WHERE id = $room_id")) {
            if(!empty($notes)) {
                if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')")) {
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

        $so_num = sanitizeInput($_REQUEST['vin_so_num_' . $room_id]);
        $room = sanitizeInput($_REQUEST['vin_room_' . $room_id]);
        $iteration = sanitizeInput($_REQUEST['vin_iteration_' . $room_id]);

        $species_grade = sanitizeInput($_REQUEST['species_grade_' . $room_id]);
        $construction_method = sanitizeInput($_REQUEST['construction_method_' . $room_id]);
        $door_design = sanitizeInput($_REQUEST['door_design_' . $room_id]);
        $panel_raise_door = sanitizeInput($_REQUEST['panel_raise_door_' . $room_id]);
        $panel_raise_sd = sanitizeInput($_REQUEST['panel_raise_sd_' . $room_id]);
        $panel_raise_td = sanitizeInput($_REQUEST['panel_raise_td_' . $room_id]);
        $edge_profile = sanitizeInput($_REQUEST['edge_profile_' . $room_id]);
        $framing_bead = sanitizeInput($_REQUEST['framing_bead_' . $room_id]);
        $framing_options = sanitizeInput($_REQUEST['framing_options_' . $room_id]);
        $style_rail_width = sanitizeInput($_REQUEST['style_rail_width_' . $room_id]);
        $finish_code = sanitizeInput($_REQUEST['finish_code_' . $room_id]);
        $sheen = sanitizeInput($_REQUEST['sheen_' . $room_id]);
        $glaze = sanitizeInput($_REQUEST['glaze_' . $room_id]);
        $glaze_technique = sanitizeInput($_REQUEST['glaze_technique_' . $room_id]);
        $antiquing = sanitizeInput($_REQUEST['antiquing_' . $room_id]);
        $worn_edges = sanitizeInput($_REQUEST['worn_edges_' . $room_id]);
        $distress_level = sanitizeInput($_REQUEST['distress_level_' . $room_id]);
        $carcass_exterior_species = sanitizeInput($_REQUEST['carcass_exterior_species_' . $room_id]);
        $carcass_exterior_finish_code = sanitizeInput($_REQUEST['carcass_exterior_finish_code_' . $room_id]);
        $carcass_exterior_glaze_color = sanitizeInput($_REQUEST['carcass_exterior_glaze_color_' . $room_id]);
        $carcass_exterior_glaze_technique = sanitizeInput($_REQUEST['carcass_exterior_glaze_technique_' . $room_id]);
        $carcass_interior_species = sanitizeInput($_REQUEST['carcass_interior_species_' . $room_id]);
        $carcass_interior_finish_code = sanitizeInput($_REQUEST['carcass_interior_finish_code_' . $room_id]);
        $carcass_interior_glaze_color = sanitizeInput($_REQUEST['carcass_interior_glaze_color_' . $room_id]);
        $carcass_interior_glaze_technique = sanitizeInput($_REQUEST['carcass_interior_glaze_technique_' . $room_id]);
        $drawer_boxes = sanitizeInput($_REQUEST['drawer_boxes_' . $room_id]);

        $notes = sanitizeInput($_REQUEST['notes_' . $room_id]);
        $vin_final = sanitizeInput($_REQUEST['vin_code_' . $room_id]);

        $sample_block_ordered = sanitizeInput($_REQUEST['sample_block_' . $room_id]);
        $door_only_ordered = sanitizeInput($_REQUEST['door_only_' . $room_id]);
        $door_drawer_ordered = sanitizeInput($_REQUEST['door_drawer_' . $room_id]);
        $inset_square_ordered = sanitizeInput($_REQUEST['inset_square_' . $room_id]);
        $inset_beaded_ordered = sanitizeInput($_REQUEST['inset_beaded_' . $room_id]);

        if(!empty($sample_block_ordered) || !empty($door_only_ordered) || !empty($door_drawer_ordered) || !empty($inset_square_ordered) || !empty($inset_beaded_ordered)) {
            $now = time();

            $sample_ordered_date = ", sample_ordered_date = '$now'";
        } else {
            $sample_ordered_date = null;
        }

        if($dbconn->query("UPDATE rooms SET species_grade = '$species_grade', construction_method = '$construction_method', door_design = '$door_design', 
         panel_raise_door = '$panel_raise_door', panel_raise_sd = '$panel_raise_sd', panel_raise_td = '$panel_raise_td', edge_profile = '$edge_profile', 
          framing_bead = '$framing_bead', framing_options = '$framing_options', style_rail_width = '$style_rail_width',
           finish_code = '$finish_code', sheen = '$sheen', glaze = '$glaze', glaze_technique = '$glaze_technique', antiquing = '$antiquing', 
            worn_edges = '$worn_edges', distress_level = '$distress_level', carcass_exterior_species = '$carcass_exterior_species', 
             carcass_exterior_finish_code = '$carcass_exterior_finish_code', 
              carcass_exterior_glaze_color = '$carcass_exterior_glaze_color', carcass_exterior_glaze_technique = '$carcass_exterior_glaze_technique', 
               carcass_interior_species = '$carcass_interior_species',
                carcass_interior_finish_code = '$carcass_interior_finish_code', carcass_interior_glaze_color = '$carcass_interior_glaze_color', 
                 carcass_interior_glaze_technique = '$carcass_interior_glaze_technique', drawer_boxes = '$drawer_boxes', vin_notes = '$notes',
                  vin_code = '$vin_final', sample_block_ordered = '$sample_block_ordered', door_only_ordered = '$door_only_ordered', door_drawer_ordered = '$door_drawer_ordered',
                   inset_square_ordered = '$inset_square_ordered', inset_beaded_ordered = '$inset_beaded_ordered' $sample_ordered_date WHERE id = '$room_id'")) {
            echo displayToast("success", "VIN has been updated for SO $so_num room $room iteration $iteration.", "VIN Updated");
        } else {
            dbLogSQLErr($dbconn);
        }

        $ops = $_REQUEST['active_ops'];

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

        if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$ops' WHERE id = '$room_id'")) {
            $dbconn->query("UPDATE rooms SET sales_bracket = '$sales_op', preproduction_bracket = '$preprod_op', sample_bracket = '$sample_op', doordrawer_bracket = '$doordrawer_op',
             custom_bracket = '$custom_op', main_bracket = '$main_op', shipping_bracket = '$shipping_op', install_bracket = '$install_op', sales_published = '$sales_pub', sample_published = '$sample_pub',
              preproduction_published = '$preprod_pub', doordrawer_published = '$doordrawer_pub', main_published = '$main_pub', custom_published = '$custom_pub', shipping_published = '$shipping_pub',
               install_bracket_published = '$install_pub' WHERE id = '$room_id'");

            createOpQueue($sales_pub, 'Sales', $sales_op, $room_id);
            createOpQueue($sample_pub, 'Sample', $sample_op, $room_id);
            createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $room_id);
            createOpQueue($doordrawer_pub, 'Drawer & Doors', $doordrawer_op, $room_id);
            createOpQueue($main_pub, 'Main', $main_op, $room_id);
            createOpQueue($custom_pub, 'Custom', $custom_op, $room_id);
            createOpQueue($shipping_pub, 'Shipping', $shipping_op, $room_id);
            createOpQueue($install_pub, 'Installation', $install_op, $room_id);

            echo displayToast("success", "All operations have been refreshed and the bracket has been updated.", "Updated & Refreshed");
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'save_active_ops':
        $ops = $_REQUEST['active_ops'];
        $roomid = sanitizeInput($_REQUEST['roomid']);

        //$active_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$roomid' AND active = TRUE;");

        //if($active_qry->num_rows === 0) {
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

                echo displayToast("success", "All operations have been refreshed and the bracket has been updated.", "Updated & Refreshed");
            } else {
                dbLogSQLErr($dbconn);
            }
//        } else {
//            echo displayToast("error", "Unable to update brackets, there is an active operation for this room.", "Active Operation");
//        }

        break;
    case 'save_so':
        $note_id = null;
        $inquiry_id = null;

        $so_num = sanitizeInput($_REQUEST['so_num']);

        $dealer_code = sanitizeInput($_REQUEST['dealer_code']);

        $project_name = sanitizeInput($_REQUEST['project_name']);
        $project_addr = sanitizeInput($_REQUEST['project_addr']);
        $project_city = sanitizeInput($_REQUEST['project_city']);
        $project_state = sanitizeInput($_REQUEST['project_state']);
        $project_zip = sanitizeInput($_REQUEST['project_zip']);
        $project_landline = sanitizeInput($_REQUEST['project_landline']);

        $name_1 = sanitizeInput($_REQUEST['name_1']);
        $cell_1 = sanitizeInput($_REQUEST['cell_1']);
        $business_1 = sanitizeInput($_REQUEST['business_1']);
        $email_1 = sanitizeInput($_REQUEST['email_1']);

        $name_2 = sanitizeInput($_REQUEST['name_2']);
        $cell_2 = sanitizeInput($_REQUEST['cell_2']);
        $business_2 = sanitizeInput($_REQUEST['business_2']);
        $email_2 = sanitizeInput($_REQUEST['email_2']);

        $secondary_addr = sanitizeInput($_REQUEST['secondary_addr']);
        $secondary_landline = sanitizeInput($_REQUEST['secondary_landline']);
        $secondary_city = sanitizeInput($_REQUEST['secondary_city']);
        $secondary_state = sanitizeInput($_REQUEST['secondary_state']);
        $secondary_zip = sanitizeInput($_REQUEST['secondary_zip']);

        $contractor_name = sanitizeInput($_REQUEST['contractor_name']);
        $contractor_business = sanitizeInput($_REQUEST['contractor_business']);
        $contractor_cell = sanitizeInput($_REQUEST['contractor_cell']);
        $contractor_addr = sanitizeInput($_REQUEST['contractor_addr']);
        $contractor_city = sanitizeInput($_REQUEST['contractor_city']);
        $contractor_state = sanitizeInput($_REQUEST['contractor_state']);
        $contractor_zip = sanitizeInput($_REQUEST['contractor_zip']);
        $contractor_email = sanitizeInput($_REQUEST['contractor_email']);
        $project_mgr = sanitizeInput($_REQUEST['project_mgr']);
        $project_mgr_cell = sanitizeInput($_REQUEST['project_mgr_cell']);
        $project_mgr_email = sanitizeInput($_REQUEST['project_mgr_email']);

        $bill_to = sanitizeInput($_REQUEST['bill_to']);
        $billing_contact = sanitizeInput($_REQUEST['billing_contact']);
        $billing_landline = sanitizeInput($_REQUEST['billing_landline']);
        $billing_cell = sanitizeInput($_REQUEST['billing_cell']);
        $billing_addr = sanitizeInput($_REQUEST['billing_addr']);
        $billing_city = sanitizeInput($_REQUEST['billing_city']);
        $billing_state = sanitizeInput($_REQUEST['billing_state']);
        $billing_zip = sanitizeInput($_REQUEST['billing_zip']);
        $billing_account = sanitizeInput($_REQUEST['billing_account']);
        $billing_routing = sanitizeInput($_REQUEST['billing_routing']);
        $billing_cc_num = sanitizeInput($_REQUEST['billing_cc_num']);
        $billing_cc_exp = sanitizeInput($_REQUEST['billing_cc_exp']);
        $billing_cc_ccv = sanitizeInput($_REQUEST['billing_cc_ccv']);

        $inquiry = sanitizeInput($_REQUEST['inquiry']);

        $so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '$so_num'");
        $so = $so_qry->fetch_assoc();

        $followup_date = sanitizeInput($_REQUEST['inquiry_followup_date']);
        $followup_individual = sanitizeInput($_REQUEST['inquiry_requested_of']);

        if(!empty($inquiry)) {
            $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$inquiry', 'so_inquiry', UNIX_TIMESTAMP(), '{$_SESSION['userInfo']['id']}', '{$so['id']}')");
            $inquiry_id = $dbconn->insert_id;
        }

        if(!empty($followup_date)) {
            $followup = strtotime($followup_date);

            $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('so_inquiry', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'SO# $so_num, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $inquiry_id)");
        }

        if($dbconn->query("UPDATE sales_order SET dealer_code = '$dealer_code', project_name = '$project_name', project_addr = '$project_addr', project_city = '$project_city',
          project_state = '$project_state', project_zip = '$project_zip', project_landline = '$project_landline', name_1 = '$name_1', cell_1 = '$cell_1', business_1 = '$business_1', 
            email_1 = '$email_1', name_2 = '$name_2', cell_2 = '$cell_2', business_2 = '$business_2', email_2 = '$email_2', secondary_addr = '$secondary_addr', secondary_landline = '$secondary_landline',
              secondary_city = '$secondary_city', secondary_state = '$secondary_state', secondary_zip = '$secondary_zip', contractor_name = '$contractor_name', contractor_business = '$contractor_business',
                contractor_cell = '$contractor_cell', contractor_addr = '$contractor_addr', contractor_city = '$contractor_city', contractor_state = '$contractor_state', contractor_zip = '$contractor_zip',
                  contractor_email = '$contractor_email', project_mgr = '$project_mgr', project_mgr_cell = '$project_mgr_cell', project_mgr_email = '$project_mgr_email', bill_to = '$bill_to',
                    billing_contact = '$billing_contact', billing_landline = '$billing_landline', billing_cell = '$billing_cell', billing_addr = '$billing_addr', billing_city = '$billing_city',
                      billing_state = '$billing_state', billing_zip = '$billing_zip', billing_account = '$billing_account', billing_routing = '$billing_routing', billing_cc_num = '$billing_cc_num',
                        billing_cc_exp = '$billing_cc_exp', billing_cc_ccv = '$billing_cc_ccv' WHERE so_num = '$so_num'")) {
            echo displayToast("success", "Successfully updated Sales Order information for $so_num.", "Updated Information");
        } else {
            dbLogSQLErr($dbconn);
        }

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
          main_bracket_priority, individual_bracket_buildout, order_status, shipping_bracket, shipping_bracket_priority, install_bracket, install_bracket_priority, delivery_date, iteration, days_to_ship) VALUES 
           ('$sonum', '$room', '$room_name', '$product_type', 1, 4, 85, 4, 83, 4, 38, 4, 45, 4, 50, 4, '$ind_bracket_final', '$order_status', 66, 4, 15, 4, '$delivery_date', $iteration, '$days_to_ship');")) {
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

    /** Deprecated */
    case 'update_VIN':
        $room_id = sanitizeInput($_REQUEST['room_id']);

        $so_num = sanitizeInput($_REQUEST['vin_so_num_' . $room_id]);
        $room = sanitizeInput($_REQUEST['vin_room_' . $room_id]);
        $iteration = sanitizeInput($_REQUEST['vin_iteration_' . $room_id]);

        $species_grade = sanitizeInput($_REQUEST['species_grade_' . $room_id]);
        $construction_method = sanitizeInput($_REQUEST['construction_method_' . $room_id]);
        $door_design = sanitizeInput($_REQUEST['door_design_' . $room_id]);
        $panel_raise_door = sanitizeInput($_REQUEST['panel_raise_door_' . $room_id]);
        $panel_raise_sd = sanitizeInput($_REQUEST['panel_raise_sd_' . $room_id]);
        $panel_raise_td = sanitizeInput($_REQUEST['panel_raise_td_' . $room_id]);
        $edge_profile = sanitizeInput($_REQUEST['edge_profile_' . $room_id]);
        $framing_bead = sanitizeInput($_REQUEST['framing_bead_' . $room_id]);
        $framing_options = sanitizeInput($_REQUEST['framing_options_' . $room_id]);
        $style_rail_width = sanitizeInput($_REQUEST['style_rail_width_' . $room_id]);
        $finish_type = sanitizeInput($_REQUEST['finish_type_' . $room_id]);
        $finish_code = sanitizeInput($_REQUEST['finish_code_' . $room_id]);
        $sheen = sanitizeInput($_REQUEST['sheen_' . $room_id]);
        $glaze = sanitizeInput($_REQUEST['glaze_' . $room_id]);
        $glaze_technique = sanitizeInput($_REQUEST['glaze_technique_' . $room_id]);
        $antiquing = sanitizeInput($_REQUEST['antiquing_' . $room_id]);
        $worn_edges = sanitizeInput($_REQUEST['worn_edges_' . $room_id]);
        $distress_level = sanitizeInput($_REQUEST['distress_level_' . $room_id]);
        $carcass_exterior_species = sanitizeInput($_REQUEST['carcass_exterior_species_' . $room_id]);
        $carcass_exterior_finish_type = sanitizeInput($_REQUEST['carcass_exterior_finish_type_' . $room_id]);
        $carcass_exterior_finish_code = sanitizeInput($_REQUEST['carcass_exterior_finish_code_' . $room_id]);
        $carcass_exterior_glaze_color = sanitizeInput($_REQUEST['carcass_exterior_glaze_color_' . $room_id]);
        $carcass_exterior_glaze_technique = sanitizeInput($_REQUEST['carcass_exterior_glaze_technique_' . $room_id]);
        $carcass_interior_species = sanitizeInput($_REQUEST['carcass_interior_species_' . $room_id]);
        $carcass_interior_finish_type = sanitizeInput($_REQUEST['carcass_interior_finish_type_' . $room_id]);
        $carcass_interior_finish_code = sanitizeInput($_REQUEST['carcass_interior_finish_code_' . $room_id]);
        $carcass_interior_glaze_color = sanitizeInput($_REQUEST['carcass_interior_glaze_color_' . $room_id]);
        $carcass_interior_glaze_technique = sanitizeInput($_REQUEST['carcass_interior_glaze_technique_' . $room_id]);
        $drawer_boxes = sanitizeInput($_REQUEST['drawer_boxes_' . $room_id]);

        $notes = sanitizeInput($_REQUEST['notes_' . $room_id]);
        $vin_final = sanitizeInput($_REQUEST['vin_code_' . $room_id]);

        $sample_block_ordered = sanitizeInput($_REQUEST['sample_block_' . $room_id]);
        $door_only_ordered = sanitizeInput($_REQUEST['door_only_' . $room_id]);
        $door_drawer_ordered = sanitizeInput($_REQUEST['door_drawer_' . $room_id]);
        $inset_square_ordered = sanitizeInput($_REQUEST['inset_square_' . $room_id]);
        $inset_beaded_ordered = sanitizeInput($_REQUEST['inset_beaded_' . $room_id]);

        if(!empty($sample_block_ordered) || !empty($door_only_ordered) || !empty($door_drawer_ordered) || !empty($inset_square_ordered) || !empty($inset_beaded_ordered)) {
            $now = time();

            $sample_ordered_date = ", sample_ordered_date = '$now'";
        } else {
            $sample_ordered_date = null;
        }

        if($dbconn->query("UPDATE rooms SET species_grade = '$species_grade', construction_method = '$construction_method', door_design = '$door_design', 
         panel_raise_door = '$panel_raise_door', panel_raise_sd = '$panel_raise_sd', panel_raise_td = '$panel_raise_td', edge_profile = '$edge_profile', 
          framing_bead = '$framing_bead', framing_options = '$framing_options', style_rail_width = '$style_rail_width', finish_type = '$finish_type', 
           finish_code = '$finish_code', sheen = '$sheen', glaze = '$glaze', glaze_technique = '$glaze_technique', antiquing = '$antiquing', 
            worn_edges = '$worn_edges', distress_level = '$distress_level', carcass_exterior_species = '$carcass_exterior_species', 
             carcass_exterior_finish_type = '$carcass_exterior_finish_type', carcass_exterior_finish_code = '$carcass_exterior_finish_code', 
              carcass_exterior_glaze_color = '$carcass_exterior_glaze_color', carcass_exterior_glaze_technique = '$carcass_exterior_glaze_technique', 
               carcass_interior_species = '$carcass_interior_species', carcass_interior_finish_type = '$carcass_interior_finish_type', 
                carcass_interior_finish_code = '$carcass_interior_finish_code', carcass_interior_glaze_color = '$carcass_interior_glaze_color', 
                 carcass_interior_glaze_technique = '$carcass_interior_glaze_technique', drawer_boxes = '$drawer_boxes', vin_notes = '$notes',
                  vin_code = '$vin_final', sample_block_ordered = '$sample_block_ordered', door_only_ordered = '$door_only_ordered', door_drawer_ordered = '$door_drawer_ordered',
                   inset_square_ordered = '$inset_square_ordered', inset_beaded_ordered = '$inset_beaded_ordered' $sample_ordered_date WHERE id = '$room_id'")) {
            echo displayToast("success", "VIN has been updated for SO $so_num room $room iteration $iteration.", "VIN Updated");
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'reply_inquiry':
        $reply_id = sanitizeInput($_REQUEST['id']);
        $reply_text = sanitizeInput($_REQUEST['reply']);

        if(!empty($reply_text)) {
            if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$reply_text', 'inquiry_reply', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$reply_id')")) {
                echo displayToast("success", "Replied to inquiry.", "Inquiry Replied");
            } else {
                echo dbLogSQLErr($dbconn);
            }
        } else {
            echo displayToast("error", "No message to reply with.", "Unable to reply to Inquiry");
        }

        break;
}