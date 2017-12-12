<?php
require '../includes/header_start.php';
require '../includes/classes/mail_handler.php';

//outputPHPErrs();

$mail = new \MailHandler\mail_handler();

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

function whatChanged($new, $old, $title, $date = false, $bool = false, $bracket_chng = false) {
    global $dbconn;

    if($date) {
        /** @var string $c_del_date Converts the delivery date to a string */
        $updated = date(DATE_TIME_ABBRV, strtotime($new));
        $new = strtotime($new);

        $new = (int)$new;
        $old = (int)$old;
    } else {
        if($bracket_chng) {
            $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = $new");

            if($op_qry->num_rows > 0) {
                $op = $op_qry->fetch_assoc();

                $updated = "to {$op['job_title']}";
            }
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

    if(!empty($new)) {
        return ($old !== $new) ? "$title $str" : null;
    }
}

switch($_REQUEST['action']) {
    case 'create_room':
        $so_num = sanitizeInput($_REQUEST['sonum']);
        $room = sanitizeInput($_REQUEST['room']);
        $room_id = sanitizeInput($_REQUEST['roomid']);

        $delivery_date = sanitizeInput($_REQUEST['delivery_date']);
        $product_type = sanitizeInput($_REQUEST['product_type']);
        $iteration = sanitizeInput($_REQUEST['iteration']);
        $order_status = sanitizeInput(html_entity_decode($_REQUEST['order_status']));
        $days_to_ship = sanitizeInput($_REQUEST['days_to_ship']);
        $room_name = sanitizeInput($_REQUEST['room_name']);
        $notes = sanitizeInput($_REQUEST['room_inquiry']);
        $followup_date = sanitizeInput($_REQUEST['room_inquiry_followup_date']);
        $followup_individual = sanitizeInput($_REQUEST['room_inquiry_requested_of']);
        $inquiry_id = null;

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

        $vin_final = sanitizeInput($_REQUEST['vin_code_' . $room_id]);

        $sample_block_ordered = sanitizeInput($_REQUEST['sample_block_' . $room_id]);
        $door_only_ordered = sanitizeInput($_REQUEST['door_only_' . $room_id]);
        $door_drawer_ordered = sanitizeInput($_REQUEST['door_drawer_' . $room_id]);
        $inset_square_ordered = sanitizeInput($_REQUEST['inset_square_' . $room_id]);
        $inset_beaded_ordered = sanitizeInput($_REQUEST['inset_beaded_' . $room_id]);

        $ops = $_REQUEST['active_ops'];

        $sales_op = sanitizeInput($_REQUEST['sales_bracket']);
        $sample_op = sanitizeInput($_REQUEST['sample_bracket']);
        $preprod_op = sanitizeInput($_REQUEST['preproduction_bracket']);
        $doordrawer_op = sanitizeInput($_REQUEST['doordrawer_bracket']);
        $main_op = sanitizeInput($_REQUEST['main_bracket']);
        $custom_op = sanitizeInput($_REQUEST['custom_bracket']);
        $shipping_op = sanitizeInput($_REQUEST['shipping_bracket']);
        $install_op = sanitizeInput($_REQUEST['install_bracket']);
        $pickmat_op = sanitizeInput($_REQUEST['pick_materials_bracket']);

        $sales_pub = (!empty($_REQUEST['sales_published'])) ? sanitizeInput($_REQUEST['sales_published']) : 0;
        $sample_pub = (!empty($_REQUEST['sample_published'])) ? sanitizeInput($_REQUEST['sample_published']) : 0;
        $preprod_pub = (!empty($_REQUEST['preprod_published'])) ? sanitizeInput($_REQUEST['preprod_published']) : 0;
        $doordrawer_pub = (!empty($_REQUEST['doordrawer_published'])) ? sanitizeInput($_REQUEST['doordrawer_published']) : 0;
        $main_pub = (!empty($_REQUEST['main_published'])) ? sanitizeInput($_REQUEST['main_published']) : 0;
        $custom_pub = (!empty($_REQUEST['custom_published'])) ? sanitizeInput($_REQUEST['custom_published']) : 0;
        $shipping_pub = (!empty($_REQUEST['shipping_published'])) ? sanitizeInput($_REQUEST['shipping_published']) : 0;
        $install_pub = (!empty($_REQUEST['install_published'])) ? sanitizeInput($_REQUEST['install_published']) : 0;
        $pickmat_pub = (!empty($_REQUEST['pickmat_published'])) ? sanitizeInput($_REQUEST['pickmat_published']) : 0;

        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' AND room = '$room' AND iteration = '$iteration'");

        if($room_qry->num_rows === 0) {
            $del_date_unix = strtotime($delivery_date);

            // first, create the room itself
            if($dbconn->query("INSERT INTO rooms (so_parent, room, iteration, room_name, product_type, sales_bracket, preproduction_bracket, sample_bracket, 
            doordrawer_bracket, custom_bracket, main_bracket, individual_bracket_buildout, order_status, shipping_bracket, install_bracket, delivery_date,
            sales_published, preproduction_published, sample_published, doordrawer_published, custom_published, main_published, shipping_published,
            install_bracket_published, pick_materials_published) VALUES  ('$so_num', '$room', '$iteration', '$room_name', '$product_type', '$sales_op', '$preprod_op', '$sample_op', 
            '$doordrawer_op', '$custom_op', '$main_op', '$ops', '$order_status', '$shipping_op', '$install_op', '$del_date_unix', '$sales_pub', 
            '$preprod_pub', '$sample_pub', '$doordrawer_pub', '$custom_pub', '$main_pub', '$shipping_pub', '$install_pub', '$pickmat_pub')")) {
                $new_room_id = $dbconn->insert_id;

                createOpQueue($sales_pub, 'Sales', $sales_op, $room_id);
                createOpQueue($sample_pub, 'Sample', $sample_op, $room_id);
                createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $room_id);
                createOpQueue($doordrawer_pub, 'Drawer & Doors', $doordrawer_op, $room_id);
                createOpQueue($main_pub, 'Main', $main_op, $room_id);
                createOpQueue($custom_pub, 'Custom', $custom_op, $room_id);
                createOpQueue($shipping_pub, 'Shipping', $shipping_op, $room_id);
                createOpQueue($install_pub, 'Installation', $install_op, $room_id);
                createOpQueue($pickmat_pub, 'Pick & Materials', $pickmat_op, $room_id);

                if(!empty($sample_block_ordered) || !empty($door_only_ordered) || !empty($door_drawer_ordered) || !empty($inset_square_ordered) || !empty($inset_beaded_ordered)) {
                    $now = time();

                    $sample_ordered_date = ", sample_ordered_date = '$now'";
                } else {
                    $sample_ordered_date = null;
                }

                if(!empty($vin_final)) {
                    $dbconn->query("UPDATE rooms SET species_grade = '$species_grade', construction_method = '$construction_method', door_design = '$door_design', 
                    panel_raise_door = '$panel_raise_door', panel_raise_sd = '$panel_raise_sd', panel_raise_td = '$panel_raise_td', edge_profile = '$edge_profile', 
                    framing_bead = '$framing_bead', framing_options = '$framing_options', style_rail_width = '$style_rail_width',
                    finish_code = '$finish_code', sheen = '$sheen', glaze = '$glaze', glaze_technique = '$glaze_technique', antiquing = '$antiquing', 
                    worn_edges = '$worn_edges', distress_level = '$distress_level', carcass_exterior_species = '$carcass_exterior_species', 
                    carcass_exterior_finish_code = '$carcass_exterior_finish_code', carcass_exterior_glaze_color = '$carcass_exterior_glaze_color', 
                    carcass_exterior_glaze_technique = '$carcass_exterior_glaze_technique', carcass_interior_species = '$carcass_interior_species',
                    carcass_interior_finish_code = '$carcass_interior_finish_code', carcass_interior_glaze_color = '$carcass_interior_glaze_color', 
                    carcass_interior_glaze_technique = '$carcass_interior_glaze_technique', drawer_boxes = '$drawer_boxes',
                    vin_code = '$vin_final', sample_block_ordered = '$sample_block_ordered', door_only_ordered = '$door_only_ordered', door_drawer_ordered = '$door_drawer_ordered',
                    inset_square_ordered = '$inset_square_ordered', inset_beaded_ordered = '$inset_beaded_ordered' $sample_ordered_date WHERE id = '$new_room_id'");
                }

                $delivery_date = (empty($delivery_date)) ? null : ",delivery_date = " . strtotime($delivery_date);

                if(!empty($notes)) {
                    if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$new_room_id')")) {
                        $inquiry_id = $dbconn->insert_id;
                    } else {
                        dbLogSQLErr($dbconn);
                    }
                }

                if(!empty($followup_date)) {
                    $followup = strtotime($followup_date);

                    $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('room_inquiry_reply', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'SO# $so_num, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $inquiry_id)");
                }

                $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = $followup_individual");

                if(!empty($followup_individual)) {
                    $followup_time = date(DATE_TIME_ABBRV, $followup);

                    if($usr = $usr_qry->fetch_assoc()) {
                        $msg_notes = nl2br($notes);
                        $msg_notes = str_replace(" ", "&nbsp;", $msg_notes);

                        $message = <<<HEREDOC
A new inquiry has been sent in for this room and requires your feedback.<br />
<br />
<h5>Followup Time: $followup_time</h5>

<h3>Inquiry:</h3>

$msg_notes -- {$_SESSION['userInfo']['name']}
HEREDOC;

                        $mail->sendMessage($usr['email'], $_SESSION['userInfo']['email'], "New Inquiry: {$so_num}{$room}{$iteration}", $message);
                    }
                }

                $c_note = "<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                $c_note .= "Created room.";

                $stmt = $dbconn->prepare("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES (?, 'room_note_log', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, ?);");
                $stmt->bind_param("si", $c_note, $room_id);
                $stmt->execute();
                $stmt->close();

                echo displayToast("success", "Room added successfully.", "Room Added");
            } else {
                dbLogSQLErr($dbconn);
            }
        } else {
            echo displayToast("warning", "Room already exists within the system.", "Room Exists");
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
        $note_type = sanitizeInput($_REQUEST['note_type']);
        $note_id = sanitizeInput($_REQUEST['note_id']);

        $room_id = sanitizeInput($_REQUEST['roomid']);
        $followup_date = sanitizeInput($_REQUEST['room_inquiry_followup_date']);
        $followup_individual = sanitizeInput($_REQUEST['room_inquiry_requested_of']);
        $inquiry_id = null;

        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
        $room_info = $room_qry->fetch_assoc();

        $changed[] = whatChanged($delivery_date, $room_info['delivery_date'], 'Delivery Date', true);
        $changed[] = whatChanged($product_type, $room_info['product_type'], 'Product Type');
        $changed[] = whatChanged($iteration, $room_info['iteration'], 'Iteration');
        $changed[] = whatChanged($order_status, $room_info['order_status'], 'Order Status');
        $changed[] = whatChanged($days_to_ship, $room_info['days_to_ship'], 'Days to Ship');
        $changed[] = whatChanged($room_name, $room_info['room_name'], 'Room Name');
        $changed[] = (!empty($notes)) ? "Notes added" : null;

        if(empty($delivery_date)) {
            $delivery_date = null;
        } elseif(!empty($delivery_date)) {
            $delivery_date = ",delivery_date = " . strtotime($delivery_date) . "";
        }

        if($dbconn->query("UPDATE rooms SET product_type = '$product_type', order_status = '$order_status', days_to_ship = '$days_to_ship', room_name = '$room_name' $delivery_date  WHERE id = $room_id")) {
            if(!empty($notes)) {
                switch($note_type) {
                    case 'room_note':
                        $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

                        break;

                    case 'delivery_note':
                        if(!empty($note_id)) {
                            $dbconn->query("UPDATE notes SET note = '$notes', timestamp = UNIX_TIMESTAMP() WHERE id = '$note_id'");
                        } else {
                            if($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_delivery'")->num_rows === 0) {
                                $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note_delivery', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");
                            } else {
                                echo displayToast("warning", "Delivery Note already exists. Please refresh your page and try again.", "Delivery Note Exists");
                            }
                        }

                        break;

                    case 'global_note':
                        if(!empty($note_id)) {
                            $dbconn->query("UPDATE notes SET note = '$notes', timestamp = UNIX_TIMESTAMP() WHERE id = '$note_id'");
                        } else {
                            if($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_global'")->num_rows === 0) {
                                $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note_global', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");
                            } else {
                                echo displayToast("warning", "Global Note already exists. Please refresh your page and try again.", "Delivery Note Exists");
                            }
                        }

                        break;

                    case 'fin_sample_note':
                        if(!empty($note_id)) {
                            $dbconn->query("UPDATE notes SET note = '$notes', timestamp = UNIX_TIMESTAMP() WHERE id = '$note_id'");
                        } else {
                            if($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_fin_sample'")->num_rows === 0) {
                                $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note_fin_sample', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");
                            } else {
                                echo displayToast("warning", "Finishing/Sample Note already exists. Please refresh your page and try again.", "Delivery Note Exists");
                            }
                        }

                        break;

                    default:
                        $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

                        break;
                }

                $inquiry_id = $dbconn->insert_id;

                echo displayToast("success", "Successfully updated the room with the notes attached.", "Room Updated with Notes");
            } else {
                echo displayToast("success", "Successfully updated the room.", "Room Updated");
            }
        } else {
            dbLogSQLErr($dbconn);
        }

        if(!empty($followup_date) && !empty($followup_individual)) {
            $followup = strtotime($followup_date);

            $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('room_inquiry_reply', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'SO# $so_num, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $inquiry_id)");

            $followup_time = date(DATE_TIME_ABBRV, $followup);

            if($usr = $usr_qry->fetch_assoc()) {
                $msg_notes = nl2br($notes);
                $msg_notes = str_replace(" ", "&nbsp;", $msg_notes);

                $message = <<<HEREDOC
A new inquiry has been sent in for this room and requires your feedback.<br />
<br />
<h5>Followup Time: $followup_time</h5>

<h3>Inquiry:</h3>

$msg_notes -- {$_SESSION['userInfo']['name']}
HEREDOC;

                $mail->sendMessage($usr['email'], $_SESSION['userInfo']['email'], "New Inquiry: {$room['so_parent']}{$room['room']}{$room['iteration']}", $message);
            }
        } elseif((empty($followup_date) && !empty($followup_individual)) || (!empty($followup_date) && empty($followup_individual))) {
            echo displayToast("warning", "Unable to set a followup as there is a missing individual or date.", "No Followup Set");
        }

        $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = $followup_individual");

        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
        $room = $room_qry->fetch_assoc();

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

        $vin_final = sanitizeInput($_REQUEST['vin_code_' . $room_id]);

        $sample_block_ordered = sanitizeInput($_REQUEST['sample_block_' . $room_id]);
        $door_only_ordered = sanitizeInput($_REQUEST['door_only_' . $room_id]);
        $door_drawer_ordered = sanitizeInput($_REQUEST['door_drawer_' . $room_id]);
        $inset_square_ordered = sanitizeInput($_REQUEST['inset_square_' . $room_id]);
        $inset_beaded_ordered = sanitizeInput($_REQUEST['inset_beaded_' . $room_id]);

        if(!empty($room_info['vin_code'])) {
            $changed[] = whatChanged($species_grade, $room_info['species_grade'], 'Species/Grade');
            $changed[] = whatChanged($construction_method, $room_info['construction_method'], 'Construction Method');
            $changed[] = whatChanged($door_design, $room_info['door_design'], 'Door Design');
            $changed[] = whatChanged($panel_raise_door, $room_info['panel_raise_door'], 'Panel Raise (Door)');
            $changed[] = whatChanged($panel_raise_sd, $room_info['panel_raise_sd'], 'Panel Raise Shoot Drawer');
            $changed[] = whatChanged($panel_raise_td, $room_info['panel_raise_td'], 'Panel Raise Tall Drawer');
            $changed[] = whatChanged($edge_profile, $room_info['edge_profile'], 'Edge Profile');
            $changed[] = whatChanged($framing_bead, $room_info['framing_bead'], 'Framing Bead');
            $changed[] = whatChanged($framing_options, $room_info['framing_options'], 'Framing Options');
            $changed[] = whatChanged($style_rail_width, $room_info['style_rail_width'], 'Style/Rail Width');
            $changed[] = whatChanged($finish_code, $room_info['finish_code'], 'Finish Code');
            $changed[] = whatChanged($sheen, $room_info['sheen'], 'Sheen');
            $changed[] = whatChanged($glaze, $room_info['glaze'], 'Glaze');
            $changed[] = whatChanged($glaze_technique, $room_info['glaze_technique'], 'Glaze Technique');
            $changed[] = whatChanged($antiquing, $room_info['antiquing'], 'Antiquing');
            $changed[] = whatChanged($worn_edges, $room_info['worn_edges'], 'Worn Edges');
            $changed[] = whatChanged($distress_level, $room_info['distress_level'], 'Distress Level');
            $changed[] = whatChanged($carcass_exterior_species, $room_info['carcass_exterior_species'], 'Carcass Exterior Species');
            $changed[] = whatChanged($carcass_exterior_finish_code, $room_info['carcass_exterior_finish_code'], 'Carcass Exterior Finish Code');
            $changed[] = whatChanged($carcass_exterior_glaze_color, $room_info['carcass_exterior_glaze_color'], 'Carcass Exterior Glaze Color');
            $changed[] = whatChanged($carcass_exterior_glaze_technique, $room_info['carcass_exterior_glaze_technique'], 'Carcass Exterior Glaze Technique');
            $changed[] = whatChanged($carcass_interior_species, $room_info['carcass_interior_species'], 'Carcass Interior Species');
            $changed[] = whatChanged($carcass_interior_finish_code, $room_info['carcass_interior_finish_code'], 'Carcass Interior Finish Code');
            $changed[] = whatChanged($carcass_interior_glaze_color, $room_info['carcass_interior_glaze_color'], 'Carcass Interior Glaze Color');
            $changed[] = whatChanged($carcass_interior_glaze_technique, $room_info['carcass_interior_glaze_technique'], 'Carcass Interior Glaze Technique');
            $changed[] = whatChanged($drawer_boxes, $room_info['drawer_boxes'], 'Drawer Boxes');

            $changed[] = whatChanged($sample_block_ordered, $room_info['sample_block_ordered'], 'Sample Block Ordered');
            $changed[] = whatChanged($door_only_ordered, $room_info['door_only_ordered'], 'Door Only Ordered');
            $changed[] = whatChanged($door_drawer_ordered, $room_info['door_drawer_ordered'], 'Door/Drawer Ordered');
            $changed[] = whatChanged($inset_square_ordered, $room_info['inset_square_ordered'], 'Inset Square Ordered');
            $changed[] = whatChanged($inset_beaded_ordered, $room_info['inset_beaded_ordered'], 'Inset Beaded Ordered');
        } else {
            if(!empty($vin_final)) {
                if($vin_final !== $room_info['vin_code']) {
                    $changed[] = "Established VIN values";
                }
            }
        }

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
             carcass_exterior_finish_code = '$carcass_exterior_finish_code', carcass_exterior_glaze_color = '$carcass_exterior_glaze_color', 
              carcass_exterior_glaze_technique = '$carcass_exterior_glaze_technique', carcass_interior_species = '$carcass_interior_species',
                carcass_interior_finish_code = '$carcass_interior_finish_code', carcass_interior_glaze_color = '$carcass_interior_glaze_color', 
                 carcass_interior_glaze_technique = '$carcass_interior_glaze_technique', drawer_boxes = '$drawer_boxes', vin_code = '$vin_final', 
                  sample_block_ordered = '$sample_block_ordered', door_only_ordered = '$door_only_ordered', door_drawer_ordered = '$door_drawer_ordered',
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
        $pickmat_op = sanitizeInput($_REQUEST['pick_materials_bracket']);

        $changed[] = whatChanged($sales_op, $room_info['sales_bracket'], 'Sales Bracket', false, false, true);
        $changed[] = whatChanged($sample_op, $room_info['sample_bracket'], 'Sample Bracket', false, false, true);
        $changed[] = whatChanged($preprod_op, $room_info['preproduction_bracket'], 'Pre-Production Bracket', false, false, true);
        $changed[] = whatChanged($doordrawer_op, $room_info['doordrawer_bracket'], 'Door/Drawer Bracket', false, false, true);
        $changed[] = whatChanged($main_op, $room_info['main_bracket'], 'Main Bracket', false, false, true);
        $changed[] = whatChanged($custom_op, $room_info['custom_bracket'], 'Custom Bracket', false, false, true);
        $changed[] = whatChanged($shipping_op, $room_info['shipping_bracket'], 'Shipping Bracket', false, false, true);
        $changed[] = whatChanged($install_op, $room_info['install_bracket'], 'Install Bracket', false, false, true);
        $changed[] = whatChanged($pickmat_op, $room_info['pick_materials_bracket'], 'Pick & Materials Bracket', false, false, true);

        $sales_pub = (!empty($_REQUEST['sales_published'])) ? sanitizeInput($_REQUEST['sales_published']) : 0;
        $sample_pub = (!empty($_REQUEST['sample_published'])) ? sanitizeInput($_REQUEST['sample_published']) : 0;
        $preprod_pub = (!empty($_REQUEST['preprod_published'])) ? sanitizeInput($_REQUEST['preprod_published']) : 0;
        $doordrawer_pub = (!empty($_REQUEST['doordrawer_published'])) ? sanitizeInput($_REQUEST['doordrawer_published']) : 0;
        $main_pub = (!empty($_REQUEST['main_published'])) ? sanitizeInput($_REQUEST['main_published']) : 0;
        $custom_pub = (!empty($_REQUEST['custom_published'])) ? sanitizeInput($_REQUEST['custom_published']) : 0;
        $shipping_pub = (!empty($_REQUEST['shipping_published'])) ? sanitizeInput($_REQUEST['shipping_published']) : 0;
        $install_pub = (!empty($_REQUEST['install_published'])) ? sanitizeInput($_REQUEST['install_published']) : 0;
        $pickmat_pub = (!empty($_REQUEST['pick_materials_published'])) ? sanitizeInput($_REQUEST['pickmat_published']) : 0;


        $changed[] = whatChanged($sales_pub, $room_info['sales_published'], 'Sales Bracket', false, true);
        $changed[] = whatChanged($sample_pub, $room_info['sample_published'], 'Sample Bracket', false, true);
        $changed[] = whatChanged($preprod_pub, $room_info['preproduction_published'], 'Pre-Production Bracket', false, true);
        $changed[] = whatChanged($doordrawer_pub, $room_info['doordrawer_published'], 'Door/Drawer Bracket', false, true);
        $changed[] = whatChanged($main_pub, $room_info['main_published'], 'Main Bracket', false, true);
        $changed[] = whatChanged($custom_pub, $room_info['custom_published'], 'Custom Bracket', false, true);
        $changed[] = whatChanged($shipping_pub, $room_info['shipping_published'], 'Shipping Bracke', false, true);
        $changed[] = whatChanged($install_pub, $room_info['install_bracket_published'], 'Install Bracket', false, true);
        $changed[] = whatChanged($pickmat_pub, $room_info['pick_materials_published'], 'Pick & Materials Bracket', false, true);
        $changed[] = whatChanged($ops, $room_info['individual_bracket_buildout'], 'Active Bracket Operations');

        if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$ops' WHERE id = '$room_id'")) {
            $dbconn->query("UPDATE rooms SET sales_bracket = '$sales_op', preproduction_bracket = '$preprod_op', sample_bracket = '$sample_op', doordrawer_bracket = '$doordrawer_op',
             custom_bracket = '$custom_op', main_bracket = '$main_op', shipping_bracket = '$shipping_op', install_bracket = '$install_op', sales_published = '$sales_pub', sample_published = '$sample_pub',
              preproduction_published = '$preprod_pub', doordrawer_published = '$doordrawer_pub', main_published = '$main_pub', custom_published = '$custom_pub', shipping_published = '$shipping_pub',
               install_bracket_published = '$install_pub', pick_materials_bracket = '$pickmat_op', pick_materials_published = '$pickmat_pub' WHERE id = '$room_id'");

            createOpQueue($sales_pub, 'Sales', $sales_op, $room_id);
            createOpQueue($sample_pub, 'Sample', $sample_op, $room_id);
            createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $room_id);
            createOpQueue($doordrawer_pub, 'Drawer & Doors', $doordrawer_op, $room_id);
            createOpQueue($main_pub, 'Main', $main_op, $room_id);
            createOpQueue($custom_pub, 'Custom', $custom_op, $room_id);
            createOpQueue($shipping_pub, 'Shipping', $shipping_op, $room_id);
            createOpQueue($install_pub, 'Installation', $install_op, $room_id);
            createOpQueue($pickmat_pub, 'Pick & Materials', $pickmat_op, $room_id);

            echo displayToast("success", "All operations have been refreshed and the bracket has been updated.", "Updated & Refreshed");
        } else {
            dbLogSQLErr($dbconn);
        }

        if(!empty(array_values(array_filter($changed)))) {
            $c_note = "<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $c_note .= implode(", ", array_values(array_filter($changed)));

            $stmt = $dbconn->prepare("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES (?, 'room_note_log', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, ?);");
            $stmt->bind_param("si", $c_note, $room_id);
            $stmt->execute();
            $stmt->close();
        }

        break;
    case 'add_iteration':


        break;
    case 'calc_del_date':
        $days_to_ship = sanitizeInput($_REQUEST['days_to_ship']);

        echo calcDelDate($days_to_ship);
        break;
    case 'copy_vin':
        $from_room = sanitizeInput($_REQUEST['copy_from']);
        $to_room = sanitizeInput($_REQUEST['copy_to']);

        $from_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$from_room'");
        $from = $from_qry->fetch_assoc();

        $to_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$to_room'");
        $to = $to_qry->fetch_assoc();

        $dbconn->query("UPDATE rooms SET species_grade = '{$from['species_grade']}', construction_method = '{$from['construction_method']}', door_design = '{$from['door_design']}',
        panel_raise_door = '{$from['panel_raise_door']}', panel_raise_sd = '{$from['panel_raise_sd']}', panel_raise_td = '{$from['panel_raise_td']}', edge_profile = '{$from['edge_profile']}',
        framing_bead = '{$from['framing_bead']}', framing_options = '{$from['framing_options']}', style_rail_width = '{$from['style_rail_width']}', finish_code = '{$from['finish_code']}', 
        sheen = '{$from['sheen']}', glaze = '{$from['glaze']}', glaze_technique = '{$from['glaze_technique']}', antiquing = '{$from['antiquing']}', worn_edges = '{$from['worn_edges']}', 
        distress_level = '{$from['distress_level']}', carcass_exterior_species = '{$from['carcass_exterior_species']}', carcass_exterior_finish_code = '{$from['carcass_exterior_finish_code']}', 
        carcass_exterior_glaze_color = '{$from['carcass_exterior_glaze_color']}', carcass_exterior_glaze_technique = '{$from['carcass_exterior_glaze_technique']}', 
        carcass_interior_species = '{$from['carcass_interior_species']}', carcass_interior_finish_code = '{$from['carcass_interior_finish_code']}',
        carcass_interior_glaze_color = '{$from['carcass_interior_glaze_color']}', carcass_interior_glaze_technique = '{$from['carcass_interior_glaze_technique']}', drawer_boxes = '{$from['drawer_boxes']}' WHERE id = '$to_room'");

        echo displayToast("success", "VIN Data copied from {$from['room']}{$from['iteration']} to {$to['room']}{$to['iteration']}", "Copied VIN");

        break;
}