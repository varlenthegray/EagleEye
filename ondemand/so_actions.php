<?php
require '../includes/header_start.php';

switch($_REQUEST['action']) {
    case 'add_customer':
        /** Global information */
        $so_num = sanitizeInput($_REQUEST['so_num']);

        /** Retail information */
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
        $contractor_business = sanitizeInput($_REQUEST['contractor_business_num']);
        $contractor_cell = sanitizeInput($_REQUEST['contractor_cell_num']);
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

        if ($dbconn->query("INSERT INTO sales_order (so_num, dealer_code, project_name, project_addr, project_city, project_state, project_zip, 
          project_landline, name_1, cell_1, business_1, email_1, name_2, cell_2, business_2, email_2, secondary_addr, secondary_city, secondary_state, 
            secondary_zip, secondary_landline, contractor_name, contractor_business, contractor_cell, contractor_email, project_mgr, project_mgr_cell, 
              project_mgr_email, bill_to, billing_contact, billing_landline, billing_cell, billing_addr, billing_city, billing_state, billing_zip, 
                billing_account, billing_routing, billing_cc_num, billing_cc_exp, billing_cc_ccv, contractor_zip, contractor_state, contractor_city, 
                  contractor_addr) VALUES ('$so_num', '$dealer_code', '$project_name', '$project_addr', '$project_city', '$project_state', '$project_zip',
                    '$project_landline', '$name_1', '$cell_1', '$business_1', '$email_1', '$name_2', '$cell_2', '$business_2', '$email_2', '$secondary_addr',
                      '$secondary_city', '$secondary_state', '$secondary_zip', '$secondary_landline', '$contractor_name', '$contractor_business', '$contractor_cell',
                        '$contractor_email', '$project_mgr', '$project_mgr_cell', '$project_mgr_email', '$bill_to', '$billing_contact', '$billing_landline',
                          '$billing_cell', '$billing_addr', '$billing_city', '$billing_state', '$billing_zip', '$billing_account', '$billing_routing',
                            '$billing_cc_num', '$billing_cc_exp', '$billing_cc_ccv', '$contractor_zip', '$contractor_state', '$contractor_city', '$contractor_addr')")) {

            $op_qry = $dbconn->query("SELECT * FROM operations WHERE op_id != '000' AND job_title NOT LIKE '%N/A%' ORDER BY op_id;");

            $ind_bracket = array();

            $starting_ops = array();

            while ($op = $op_qry->fetch_assoc()) {
                if (empty($starting_ops[$op['bracket']])) {
                    $starting_ops[$op['bracket']] = $op['id'];
                }

                $ind_bracket[] = $op['id'];
            }

            $ind_bracket_final = json_encode($ind_bracket);

            $dbconn->query("INSERT INTO rooms (so_parent, room, room_name, product_type, individual_bracket_buildout, order_status, sales_bracket, sample_bracket,
              preproduction_bracket, doordrawer_bracket, main_bracket, custom_bracket, install_bracket, shipping_bracket, sales_published) 
                VALUES ('$so_num', 'A', 'Auto-Generated: Intake', 'C', '$ind_bracket_final', '#', '{$starting_ops['Sales']}', '{$starting_ops['Sample']}', 
                '{$starting_ops['Pre-Production']}', '{$starting_ops['Drawer & Doors']}', '{$starting_ops['Main']}', '{$starting_ops['Custom']}', 
                  '{$starting_ops['Installation']}', '{$starting_ops['Shipping']}', TRUE);");

            $room_id = $dbconn->insert_id;

            $dbconn->query("INSERT INTO op_queue (room_id, operation_id, notes, created) VALUES ('$room_id', '{$starting_ops['Sales']}', 'Auto-generated.', UNIX_TIMESTAMP())");

            echo displayToast("success", "Successfully created new SO.", "New SO Created");
        } else {
            dbLogSQLErr($dbconn);
        }

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
    case 'get_so_list':
        $output = array();
        $i = 0;

        $so_qry = $dbconn->query("SELECT sales_order.*, dealers.dealer_name, dealers.contact FROM sales_order LEFT JOIN dealers ON sales_order.dealer_code = dealers.dealer_id");

        if($so_qry->num_rows > 0) {
            while($so = $so_qry->fetch_assoc()) {
                $output['data'][$i][] = $so['so_num'];
                $output['data'][$i][] = $so['project_name'];
                $output['data'][$i][] = $so['contact'];
                $output['data'][$i][] = "{$so['dealer_name']}: {$so['contact']}";
                $output['data'][$i]['DT_RowId'] = $so['so_num'];

                $i += 1;
            }
        } else {
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "---------";
            $output['data'][$i][] = "---------";
            $output['data'][$i][] = "No SO's to list";
            $output['data'][$i][] = "";
            $output['data'][$i][] = "";
            $output['data'][$i][] = "";

            $i += 1;
        }

        echo json_encode($output);

        break;
    case 'reply_inquiry':
        $reply_id = sanitizeInput($_REQUEST['id']);
        $reply_text = sanitizeInput($_REQUEST['reply']);

        if(!empty($reply_text)) {
            if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$reply_text', 'inquiry_reply', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$reply_id')")) {
                echo displayToast("success", "Replied to inquiry.", "Inquiry Replied");
            } else {
                dbLogSQLErr($dbconn);
            }
        } else {
            echo displayToast("error", "No message to reply with.", "Unable to reply to Inquiry");
        }

        break;
    case 'get_sales_list':
        $output = array();
        $i = 0;

        $prev_so = null;
        $prev_room = null;
        $prev_sequence = null;

        $so_qry = $dbconn->query("SELECT sales_order.*, rooms.*, dealers.* FROM sales_order 
          LEFT JOIN rooms ON sales_order.so_num = rooms.so_parent LEFT JOIN dealers ON sales_order.dealer_code = dealers.dealer_id 
            ORDER BY so_num ASC, room ASC, iteration ASC;");

        if($so_qry->num_rows > 0) {
            while($so = $so_qry->fetch_assoc()) {
                $contact = (!empty($so['contact'])) ? "{$so['dealer_id']}: {$so['contact']} ({$so['dealer_name']})" : "<span style='color: #FF0000 !important;'>A00: None Assigned</span>";

                switch($so['order_status']) {
                    case ')':
                        $order_status = "Lost";
                        break;

                    case '#':
                        $order_status = "Quote";
                        break;

                    case '$':
                        $order_status = "Job";
                        break;

                    case '(':
                        $order_status = "Completed";
                        break;

                    default:
                        $order_status = "<span style='color: #FF0000 !important;'>None</span>";
                        break;
                }

                if($prev_so !== $so['so_num']) {
                    $output['data'][$i][] = "<strong>{$so['so_num']}</strong>";
                    $output['data'][$i][] = "<strong>{$so['project_name']}</strong>";
                    $output['data'][$i][] = $contact;
                    $output['data'][$i][] = "&nbsp;";
                    $output['data'][$i][] = "{$so['dealer_id']}";
                    $output['data'][$i]['DT_RowId'] = $so['so_num'];

                    $i++;

                    $room_iteration = (!empty($so['room']) && !empty($so['iteration'])) ? "{$so['room']}{$so['iteration']}" : "<span style='color: #FF0000 !important;'>None</span>";
                    $room_name = (!empty($so['room_name'])) ? "{$so['room_name']}" : "<span style='color: #FF0000 !important;'>None</span>";

                    $output['data'][$i][] = "<span style='padding-left:10px;'>$room_iteration</span>";
                    $output['data'][$i][] = "<span style='padding-left:20px;'>$room_name</span>";
                    $output['data'][$i][] = $contact;
                    $output['data'][$i][] = $order_status;
                    $output['data'][$i][] = "{$so['dealer_id']}";
                    $output['data'][$i]['DT_RowId'] = $so['so_num'];

                    $prev_room = $so['room'];
                    $prev_so = $so['so_num'];
                } else {
                    $cur_sequence = substr($so['iteration'], 0, 1);

                    if($cur_sequence !== $prev_sequence) {
                        $iteration = $so['iteration'];
                        $prev_sequence = $cur_sequence;
                    } else {
                        $iteration = substr($so['iteration'], -1, 3);
                    }

                    $room_def = "<span style='padding-left:9px;'>$iteration</span>";

                    $output['data'][$i][] = "<span style='padding-left:10px;'>$room_def</span>";
                    $output['data'][$i][] = "<span style='padding-left:20px;'>{$so['room_name']}</span>";
                    $output['data'][$i][] = $contact;
                    $output['data'][$i][] = $order_status;
                    $output['data'][$i][] = "{$so['dealer_id']}";
                    $output['data'][$i]['DT_RowId'] = $so['so_num'];
                }

                $i++;
            }
        } else {
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "No SO's to list";
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "---------";

            $i++;
        }


        echo json_encode($output);

        break;
}