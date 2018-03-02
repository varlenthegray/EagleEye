<?php
require '../includes/header_start.php';

function whatChanged($new, $old, $title, $date = false, $bool = false) {
    global $dbconn;

    if($date) {
        /** @var string $c_del_date Converts the delivery date to a string */
        $updated = date(DATE_TIME_ABBRV, strtotime($new));
        $new = strtotime($new);

        $new = (int)$new;
        $old = (int)$old;
    } else {
        if($title === 'Sales Bracket' || $title === 'Sample Bracket' || $title === 'Pre-Production Bracket' || $title === 'Door/Drawer Bracket' || $title === 'Main Bracket' ||
            $title === 'Custom Bracket' || $title === 'Shipping Bracket' || $title === 'Install Bracket') {

            $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = $new");
            $op = $op_qry->fetch_assoc();

            $updated = $op['job_title'];
        } elseif($title === 'Active Bracket Operations') {
            $updated = null;
        } else {
            $updated = "to $new";
        }
    }

    if($bool) {
        $updated = ($new === 0) ? 'Unpublished' : 'Published';

        $str = "$updated";
        $old = (bool)$old;
        $new = (bool)$new;
    } else {
        $str = "Updated $updated";
    }

    return ($old !== $new) ? "$title $str" : null;
}

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

        $changed[] = whatChanged($so_num, $so['so_num'], 'SO Number');
        $changed[] = whatChanged($dealer_code, $so['dealer_code'], 'Dealer Code');
        $changed[] = whatChanged($project_name, $so['project_name'], 'Project Name');
        $changed[] = whatChanged($project_addr, $so['project_addr'], 'Project Address');
        $changed[] = whatChanged($project_city, $so['project_city'], 'Project City');
        $changed[] = whatChanged($project_state, $so['project_state'], 'Project State');
        $changed[] = whatChanged($project_zip, $so['project_zip'], 'Project Zip');
        $changed[] = whatChanged($project_landline, $so['project_landline'], 'Project Landline');
        $changed[] = whatChanged($name_1, $so['name_1'], 'Name 1');
        $changed[] = whatChanged($cell_1, $so['cell_1'], 'Cell 1');
        $changed[] = whatChanged($business_1, $so['business_1'], 'Secondary Phone 1');
        $changed[] = whatChanged($email_1, $so['email_1'], 'Email 1');
        $changed[] = whatChanged($name_2, $so['name_2'], 'Name 2');
        $changed[] = whatChanged($cell_2, $so['cell_2'], 'Cell 2');
        $changed[] = whatChanged($business_2, $so['business_2'], 'Secondary Phone 2');
        $changed[] = whatChanged($email_2, $so['email_2'], 'Email 2');
        $changed[] = whatChanged($secondary_addr, $so['secondary_addr'], 'Secondary Address');
        $changed[] = whatChanged($secondary_landline, $so['secondary_landline'], 'Secondary Landline');
        $changed[] = whatChanged($secondary_city, $so['secondary_city'], 'Secondary City');
        $changed[] = whatChanged($secondary_state, $so['secondary_state'], 'Secondary State');
        $changed[] = whatChanged($secondary_zip, $so['secondary_zip'], 'Secondary Zip');
        $changed[] = whatChanged($contractor_name, $so['contractor_name'], 'Contractor Name');
        $changed[] = whatChanged($contractor_business, $so['contractor_business'], 'Contractor Business');
        $changed[] = whatChanged($contractor_cell, $so['contractor_cell'], 'Contractor Cell');
        $changed[] = whatChanged($contractor_addr, $so['contractor_addr'], 'Contractor Address');
        $changed[] = whatChanged($contractor_city, $so['contractor_city'], 'Contractor City');
        $changed[] = whatChanged($contractor_state, $so['contractor_state'], 'Contractor State');
        $changed[] = whatChanged($contractor_zip, $so['contractor_zip'], 'Contractor Zip');
        $changed[] = whatChanged($contractor_email, $so['contractor_email'], 'Contractor Email');
        $changed[] = whatChanged($project_mgr, $so['project_mgr'], 'Project Manager');
        $changed[] = whatChanged($project_mgr_cell, $so['project_mgr_cell'], 'Project Manager Cell');
        $changed[] = whatChanged($project_mgr_email, $so['project_mgr_email'], 'Project Manager Email');
        $changed[] = whatChanged($bill_to, $so['bill_to'], 'Bill To');
        $changed[] = whatChanged($billing_contact, $so['billing_contact'], 'Billing Contact');
        $changed[] = whatChanged($billing_landline, $so['billing_landline'], 'Billing Landline');
        $changed[] = whatChanged($billing_cell, $so['billing_cell'], 'Billing Cell');
        $changed[] = whatChanged($billing_addr, $so['billing_addr'], 'Billing Address');
        $changed[] = whatChanged($billing_city, $so['billing_city'], 'Billing City');
        $changed[] = whatChanged($billing_state, $so['billing_state'], 'Billing State');
        $changed[] = whatChanged($billing_zip, $so['billing_zip'], 'Billing Zip');
        $changed[] = whatChanged($billing_account, $so['billing_account'], 'Billing Account');
        $changed[] = whatChanged($billing_routing, $so['billing_routing'], 'Billing Routing');
        $changed[] = whatChanged($billing_cc_num, $so['billing_cc_num'], 'Billing CC Number');
        $changed[] = whatChanged($billing_cc_exp, $so['billing_cc_exp'], 'Billing CC Expiration');
        $changed[] = whatChanged($billing_cc_ccv, $so['billing_cc_ccv'], 'Billing CC CCV');

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
            if(!empty(array_values(array_filter($changed)))) {
                $c_note = "<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                $c_note .= implode(", ", array_values(array_filter($changed)));

                $stmt = $dbconn->prepare("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES (?, 'so_note_log', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, ?);");
                $stmt->bind_param("si", $c_note, $so['id']);
                $stmt->execute();
                $stmt->close();
            }

            echo displayToast("success", "Successfully updated Sales Order information for $so_num.", "Updated Information");
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'get_so_list':
        $output = array();
        $i = 0;

        if((bool)$_SESSION['userInfo']['dealer']) {
            $dealer = DEALER;

            $dealer_where = "WHERE dealers.dealer_id LIKE '$dealer%'";
        } else {
            $dealer_where = null;
        }

        $so_qry = $dbconn->query("SELECT sales_order.*, dealers.dealer_name, dealers.contact FROM sales_order LEFT JOIN dealers ON sales_order.dealer_code = dealers.dealer_id $dealer_where");

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

        $hidden_enabled = (bool)$_REQUEST['hidden'];

        $prev_so = null;
        $prev_room = null;
        $prev_seq = null;

        if((bool)$_SESSION['userInfo']['dealer']) {
            $dealer = DEALER;

            $dealer_where = "WHERE dealers.dealer_id LIKE '$dealer%'";
        } else {
            $dealer_where = null;
        }

        $so_qry = $dbconn->query("SELECT sales_order.id AS sID, sales_order.*, rooms.id AS rID, rooms.*, dealers.* FROM sales_order 
          LEFT JOIN rooms ON sales_order.so_num = rooms.so_parent LEFT JOIN dealers ON sales_order.dealer_code = dealers.dealer_id $dealer_where 
            ORDER BY so_num ASC, room ASC, iteration ASC;");

        $usr_qry = $dbconn->query("SELECT hide_sales_list_values FROM user WHERE id = {$_SESSION['userInfo']['id']};");
        $usr = $usr_qry->fetch_assoc();
        $hidden = json_decode($usr['hide_sales_list_values']);

        if($so_qry->num_rows > 0) {
            while($so = $so_qry->fetch_assoc()) {
                if(in_array($so['rID'], $hidden)) {
                    // if it's hidden, tell the system it's showing the hidden button
                    $btn_classes = 'btn-primary-outline sales_list_hidden';
                    $btn_icon = 'zmdi-eye';
                } else {
                    $btn_classes = 'btn-primary sales_list_visible';
                    $btn_icon = 'zmdi-eye-off';
                }

                if(!in_array($so['rID'], $hidden) || $hidden_enabled) {
                    $contact = (!empty($so['contact'])) ? "{$so['dealer_id']}: {$so['contact']} ({$so['dealer_name']})" : "<span style='color: #FF0000 !important;'>A00: None Assigned</span>";

                    switch($so['order_status']) {
                        case '-':
                            $order_status = "Lost";
                            break;

                        case '#':
                            $order_status = "Quote";
                            break;

                        case '$':
                            $order_status = "Job";
                            break;

                        case '+':
                            $order_status = "Completed";
                            break;


                        case 'A':
                            $order_status = "Add-on";
                            break;

                        case 'W':
                            $order_status = "Warranty";
                            break;

                        default:
                            $order_status = "<span style='color: #FF0000 !important;'>None</span>";
                            break;
                    }

                    if($prev_so !== $so['so_num']) {
                        $output['data'][$i][] = "";
                        $output['data'][$i][] = "<strong>{$so['so_num']}</strong>";
                        $output['data'][$i][] = "<strong>{$so['project_name']}</strong>";
                        $output['data'][$i][] = $contact;
                        $output['data'][$i][] = "&nbsp;";
                        $output['data'][$i][] = "{$so['dealer_id']}";
                        $output['data'][$i][] = "{$so['sID']}";
                        $output['data'][$i]['DT_RowId'] = $so['so_num'];

                        $i++;

                        $room_iteration = (!empty($so['room']) && !empty($so['iteration'])) ? "{$so['room']}{$so['iteration']}" : "<span style='color: #FF0000 !important;'>None</span>";
                        $room_name = (!empty($so['room_name'])) ? "{$so['room_name']}" : "<span style='color: #FF0000 !important;'>None</span>";

                        $iteration = explode(".", number_format($so['iteration'], 2));
                        $prev_seq = $iteration[0];

                        $output['data'][$i][] = "<button class='$btn_classes' data-identifier='{$so['rID']}'><i class='zmdi $btn_icon'></i></button>";
                        $output['data'][$i][] = "<span style='padding-left:20px;'>$room_iteration</span>";
                        $output['data'][$i][] = "<span style='padding-left:20px;'>$room_name</span>";
                        $output['data'][$i][] = $contact;
                        $output['data'][$i][] = $order_status;
                        $output['data'][$i][] = "{$so['dealer_id']}";
                        $output['data'][$i][] = "{$so['rID']}";
                        $output['data'][$i]['DT_RowId'] = $so['so_num'];

                        $prev_room = $so['room'];
                        $prev_so = $so['so_num'];
                    } else {
                        $iteration = explode(".", number_format($so['iteration'], 2));

                        if($prev_room !== $so['room']) {
                            $prev_room = $so['room'];
                            $room_def = "{$so['room']}{$so['iteration']}";
                        } else {
                            if($iteration[0] !== $prev_seq) {
                                $prev_seq = $iteration[0];
                                $final_iteration = $so['iteration'];
                                $final_padding = '8';
                            } else {
                                $final_iteration = ".{$iteration[1]}";
                                $final_padding = '15';
                            }

                            $room_def = "<span style='padding-left:{$final_padding}px;'>$final_iteration</span>";
                        }

                        $output['data'][$i][] = "<button class='$btn_classes' data-identifier='{$so['rID']}'><i class='zmdi $btn_icon'></i></button>";
                        $output['data'][$i][] = "<span style='padding-left:20px;'>$room_def</span>";
                        $output['data'][$i][] = "<span style='padding-left:20px;'>{$so['room_name']}</span>";
                        $output['data'][$i][] = $contact;
                        $output['data'][$i][] = $order_status;
                        $output['data'][$i][] = "{$so['dealer_id']}";
                        $output['data'][$i][] = "{$so['rID']}";
                        $output['data'][$i]['DT_RowId'] = $so['so_num'];
                    }

                    $i++;
                }
            }
        } else {
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "No SO's to list";
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "---------";

            $i++;
        }


        echo json_encode($output);

        break;
    case 'generate_code':
        $key = md5(microtime());
        $so = sanitizeInput($_REQUEST['so_num']);

        $dbconn->query("UPDATE sales_order SET access_code = '$key' WHERE so_num = '$so'");

        echo $key;

        break;
}