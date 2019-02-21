<?php
require '../includes/header_start.php';
require '../includes/classes/mail_handler.php';
//require '../includes/functions.php';

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
    $dbconn->query("INSERT INTO op_queue (room_id, operation_id, start_time, active, completed, rework, partially_completed, created) VALUES ('$roomid', '$operation', NULL, FALSE, FALSE, FALSE, NULL, UNIX_TIMESTAMP())");
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
    parse_str($_REQUEST['editInfo'], $editInfo);

    $so_num = sanitizeInput($editInfo['sonum']);
    $room = sanitizeInput($editInfo['room']);
    $room_id = sanitizeInput($editInfo['roomid']);

    $delivery_date = sanitizeInput($editInfo['delivery_date']);
    $product_type = sanitizeInput($editInfo['product_type']);
    $iteration = sanitizeInput($editInfo['iteration']);
    $order_status = sanitizeInput(html_entity_decode($editInfo['order_status']));
    $days_to_ship = sanitizeInput($editInfo['days_to_ship']);
    $room_name = sanitizeInput($editInfo['room_name']);
    $notes = sanitizeInput($editInfo['room_inquiry']);
    $followup_date = sanitizeInput($editInfo['room_inquiry_followup_date']);
    $followup_individual = sanitizeInput($editInfo['room_inquiry_requested_of']);
    $inquiry_id = null;

    $species_grade = sanitizeInput($editInfo['species_grade']);
    $construction_method = sanitizeInput($editInfo['construction_method']);
    $door_design = sanitizeInput($editInfo['door_design']);
    $panel_raise_door = sanitizeInput($editInfo['panel_raise_door']);
    $panel_raise_sd = sanitizeInput($editInfo['panel_raise_sd']);
    $panel_raise_td = sanitizeInput($editInfo['panel_raise_td']);
    $edge_profile = sanitizeInput($editInfo['edge_profile']);
    $framing_bead = sanitizeInput($editInfo['framing_bead']);
    $framing_options = sanitizeInput($editInfo['framing_options']);
    $style_rail_width = sanitizeInput($editInfo['style_rail_width']);
    $finish_code = sanitizeInput($editInfo['finish_code']);
    $sheen = sanitizeInput($editInfo['sheen']);
    $glaze = sanitizeInput($editInfo['glaze']);
    $glaze_technique = sanitizeInput($editInfo['glaze_technique']);
    $antiquing = sanitizeInput($editInfo['antiquing']);
    $worn_edges = sanitizeInput($editInfo['worn_edges']);
    $distress_level = sanitizeInput($editInfo['distress_level']);
    $carcass_exterior_species = sanitizeInput($editInfo['carcass_exterior_species']);
    $carcass_exterior_finish_code = sanitizeInput($editInfo['carcass_exterior_finish_code']);
    $carcass_exterior_glaze_color = sanitizeInput($editInfo['carcass_exterior_glaze_color']);
    $carcass_exterior_glaze_technique = sanitizeInput($editInfo['carcass_exterior_glaze_technique']);
    $carcass_interior_species = sanitizeInput($editInfo['carcass_interior_species']);
    $carcass_interior_finish_code = sanitizeInput($editInfo['carcass_interior_finish_code']);
    $carcass_interior_glaze_color = sanitizeInput($editInfo['carcass_interior_glaze_color']);
    $carcass_interior_glaze_technique = sanitizeInput($editInfo['carcass_interior_glaze_technique']);
    $drawer_boxes = sanitizeInput($editInfo['drawer_boxes']);

    $vin_final = sanitizeInput($editInfo['vin_code_' . $room_id]);

    $sample_block_ordered = sanitizeInput($editInfo['sample_block_' . $room_id]);
    $door_only_ordered = sanitizeInput($editInfo['door_only_' . $room_id]);
    $door_drawer_ordered = sanitizeInput($editInfo['door_drawer_' . $room_id]);
    $inset_square_ordered = sanitizeInput($editInfo['inset_square_' . $room_id]);
    $inset_beaded_ordered = sanitizeInput($editInfo['inset_beaded_' . $room_id]);

    $ops = $_REQUEST['active_ops'];

    $sales_marketing_op = sanitizeInput($editInfo['sales_marketing_bracket']);
    $shop_op = sanitizeInput($editInfo['shop_bracket']);
    $preprod_op = sanitizeInput($editInfo['preproduction_bracket']);
    $press_op = sanitizeInput($editInfo['press_bracket']);
    $paint_op = sanitizeInput($editInfo['paint_bracket']);
    $custom_op = sanitizeInput($editInfo['custom_bracket']);
    $shipping_op = sanitizeInput($editInfo['shipping_bracket']);
    $assembly_op = sanitizeInput($editInfo['assembly_bracket']);

    $sales_marketing_pub = !empty($editInfo['sales_marketing_published']) ? sanitizeInput($editInfo['sales_marketing_published']) : 0;
    $sample_pub = !empty($editInfo['sample_published']) ? sanitizeInput($editInfo['sample_published']) : 0;
    $preprod_pub = !empty($editInfo['preprod_published']) ? sanitizeInput($editInfo['preprod_published']) : 0;
    $press_pub = !empty($editInfo['press_published']) ? sanitizeInput($editInfo['press_published']) : 0;
    $paint_pub = !empty($editInfo['paint_published']) ? sanitizeInput($editInfo['paint_published']) : 0;
    $custom_pub = !empty($editInfo['custom_published']) ? sanitizeInput($editInfo['custom_published']) : 0;
    $shipping_pub = !empty($editInfo['shipping_published']) ? sanitizeInput($editInfo['shipping_published']) : 0;
    $assembly_pub = !empty($editInfo['assembly_published']) ? sanitizeInput($editInfo['assembly_published']) : 0;

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' AND room = '$room' AND iteration = '$iteration'");

    if($room_qry->num_rows === 0) {
      if(empty($delivery_date)) { // this entire thing is stupid but it resolves the del date being blank
        $del_date_unix = 'null'; // this was stupid, i had to set this to a null string and remove quotes from SQL statement
      } else {
        $del_date_unix = "'" . strtotime($delivery_date) . "'"; // this was stupid, i had to configure this to string
      }

      // first, create the room itself
      if($dbconn->query("INSERT INTO rooms (so_parent, room, iteration, room_name, product_type, sales_marketing_bracket, preproduction_bracket, shop_bracket, 
            press_bracket, custom_bracket, paint_bracket, individual_bracket_buildout, order_status, shipping_bracket, assembly_bracket, delivery_date,
            sales_marketing_published, preproduction_published, sample_published, press_published, custom_published, paint_published, shipping_published,
            assembly_published) VALUES  ('$so_num', '$room', '$iteration', '$room_name', '$product_type', '$sales_marketing_op', '$preprod_op', '$shop_op', 
            '$press_op', '$custom_op', '$paint_op', '$ops', '$order_status', '$shipping_op', '$assembly_op', $del_date_unix, '$sales_marketing_pub', 
            '$preprod_pub', '$sample_pub', '$press_pub', '$custom_pub', '$paint_pub', '$shipping_pub', '$assembly_pub')")) {
        $new_room_id = $dbconn->insert_id;
        createOpQueue($sales_marketing_pub, 'Sales/Marketing', $sales_marketing_op, $new_room_id);
        createOpQueue($sample_pub, 'Shop', $shop_op, $new_room_id);
        createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $new_room_id);
        createOpQueue($press_pub, 'Press', $press_op, $new_room_id);
        createOpQueue($paint_pub, 'Paint', $paint_op, $new_room_id);
        createOpQueue($custom_pub, 'Custom', $custom_op, $new_room_id);
        createOpQueue($shipping_pub, 'Shipping', $shipping_op, $new_room_id);
        createOpQueue($assembly_pub, 'Assembly', $assembly_op, $new_room_id);

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

            $mail->sendMessage($usr['email'], $_SESSION['userInfo']['email'], "New Inquiry: {$so_num}{$room}{$iteration}", $message, true);
          }
        }

        $c_note = '<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $c_note .= 'Created room.';

        $stmt = $dbconn->prepare("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES (?, 'room_note_log', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, ?);");
        $stmt->bind_param('si', $c_note, $room_id);
        $stmt->execute();
        $stmt->close();

        echo displayToast('success', 'Room added successfully.', 'Room Added');
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
      echo displayToast('warning', 'Room already exists within the system.', 'Room Exists');
    }

    break;
  case 'update_room':
    parse_str($_REQUEST['editInfo'], $editInfo);

    $room_id = sanitizeInput($editInfo['roomid']);

    $delivery_date = sanitizeInput($editInfo['delivery_date']);
    $product_type = sanitizeInput($editInfo['product_type']);
    $iteration = sanitizeInput($editInfo['iteration']);
    $order_status = sanitizeInput(html_entity_decode($editInfo['order_status']));
    $dealer_status = sanitizeInput(html_entity_decode($editInfo['dealer_status']));
    $days_to_ship = sanitizeInput($editInfo['days_to_ship']);
    $room_name = sanitizeInput($editInfo['room_name']);

    $notes = sanitizeInput($editInfo['room_notes']);
    $note_type = sanitizeInput($editInfo['note_type']);
    $note_id = sanitizeInput($editInfo['note_id']);

    $deposit_received = !empty($editInfo['deposit_received']) ? (bool)$editInfo['deposit_received'] : 0;
    $final_payment = !empty($editInfo['final_payment']) ? (bool)$editInfo['final_payment'] : 0;
    $ptl_del = !empty($editInfo['ptl_del']) ? (bool)$editInfo['ptl_del'] : 0;

    $followup_date = sanitizeInput($editInfo['room_inquiry_followup_date']);
    $followup_individual = sanitizeInput($editInfo['room_inquiry_requested_of']);
    $inquiry_id = null;

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $room_info = $room_qry->fetch_assoc();

    $changed[] = whatChanged($delivery_date, $room_info['delivery_date'], 'Delivery Date', true);
    $changed[] = whatChanged($product_type, $room_info['product_type'], 'Product Type');
    $changed[] = whatChanged($iteration, $room_info['iteration'], 'Iteration');
    $changed[] = whatChanged($order_status, $room_info['order_status'], 'Order Status');
    $changed[] = whatChanged($dealer_status, $room_info['dealer_status'], 'Dealer Status');
    $changed[] = whatChanged($days_to_ship, $room_info['days_to_ship'], 'Days to Ship');
    $changed[] = whatChanged($room_name, $room_info['room_name'], 'Room Name');
    $changed[] = whatChanged($deposit_received, $room_info['payment_deposit'], 'Deposit Payment');
    $changed[] = whatChanged($final_payment, $room_info['payment_final'], 'Final Payment');
    $changed[] = whatChanged($ptl_del, $room_info['payment_del_ptl'], 'Prior to Loading/Delivery Payment');
    $changed[] = !empty($notes) ? 'Notes added' : null;

    if(empty($delivery_date)) {
      $delivery_date = null;
    } elseif(!empty($delivery_date)) {
      $delivery_date = ',delivery_date = ' . strtotime($delivery_date);
    }

    if($dbconn->query("UPDATE rooms SET product_type = '$product_type', order_status = '$order_status', dealer_status = '$dealer_status', days_to_ship = '$days_to_ship', room_name = '$room_name' $delivery_date  WHERE id = $room_id")) {
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

            $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('Delivery Note: $notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

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

            $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('Global Note: $notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

            break;

          case 'fin_sample_note':
            if(!empty($note_id)) {
              $dbconn->query("UPDATE notes SET note = '$notes', timestamp = UNIX_TIMESTAMP() WHERE id = '$note_id'");
            } else {
              if($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_fin_sample'")->num_rows === 0) {
                $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$notes', 'room_note_fin_sample', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");
              } else {
                echo displayToast("warning", "Finishing/Shop Note already exists. Please refresh your page and try again.", "Delivery Note Exists");
              }
            }

            $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('Finishing/Shop Note: $notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

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

      $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('room_inquiry_reply', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'SO# {$editInfo['sonum']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $inquiry_id)");

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

        $mail->sendMessage($usr['email'], $_SESSION['userInfo']['email'], "New Inquiry: {$editInfo['sonum']}{$editInfo['room']}{$iteration}", $message, true);
      }
    } elseif((empty($followup_date) && !empty($followup_individual)) || (!empty($followup_date) && empty($followup_individual))) {
      echo displayToast("warning", "Unable to set a followup as there is a missing individual or date.", "No Followup Set");
    }

    $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = $followup_individual");

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $room = $room_qry->fetch_assoc();

    $species_grade = sanitizeInput($editInfo['species_grade']);
    $construction_method = sanitizeInput($editInfo['construction_method']);
    $door_design = sanitizeInput($editInfo['door_design']);
    $panel_raise_door = sanitizeInput($editInfo['panel_raise_door']);
    $panel_raise_sd = sanitizeInput($editInfo['panel_raise_sd']);
    $panel_raise_td = sanitizeInput($editInfo['panel_raise_td']);
    $edge_profile = sanitizeInput($editInfo['edge_profile']);
    $framing_bead = sanitizeInput($editInfo['framing_bead']);
    $framing_options = sanitizeInput($editInfo['framing_options']);
    $style_rail_width = sanitizeInput($editInfo['style_rail_width']);
    $finish_code = sanitizeInput($editInfo['finish_code']);
    $sheen = sanitizeInput($editInfo['sheen']);
    $glaze = sanitizeInput($editInfo['glaze']);
    $glaze_technique = sanitizeInput($editInfo['glaze_technique']);
    $antiquing = sanitizeInput($editInfo['antiquing']);
    $worn_edges = sanitizeInput($editInfo['worn_edges']);
    $distress_level = sanitizeInput($editInfo['distress_level']);
    $carcass_exterior_species = sanitizeInput($editInfo['carcass_exterior_species']);
    $carcass_exterior_finish_code = sanitizeInput($editInfo['carcass_exterior_finish_code']);
    $carcass_exterior_glaze_color = sanitizeInput($editInfo['carcass_exterior_glaze_color']);
    $carcass_exterior_glaze_technique = sanitizeInput($editInfo['carcass_exterior_glaze_technique']);
    $carcass_interior_species = sanitizeInput($editInfo['carcass_interior_species']);
    $carcass_interior_finish_code = sanitizeInput($editInfo['carcass_interior_finish_code']);
    $carcass_interior_glaze_color = sanitizeInput($editInfo['carcass_interior_glaze_color']);
    $carcass_interior_glaze_technique = sanitizeInput($editInfo['carcass_interior_glaze_technique']);
    $drawer_box_mount = sanitizeInput($editInfo['drawer_box_mount']);
    $drawer_boxes = sanitizeInput($editInfo['drawer_boxes']);

    $custom_vals = $_REQUEST['customVals']; // custom fields in VIN sheet

    $vin_final = sanitizeInput($editInfo['vin_code_' . $room_id]);

    $sample_block_ordered = sanitizeInput($editInfo['sample_block_' . $room_id]);
    $door_only_ordered = sanitizeInput($editInfo['door_only_' . $room_id]);
    $door_drawer_ordered = sanitizeInput($editInfo['door_drawer_' . $room_id]);
    $inset_square_ordered = sanitizeInput($editInfo['inset_square_' . $room_id]);
    $inset_beaded_ordered = sanitizeInput($editInfo['inset_beaded_' . $room_id]);

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
      $changed[] = whatChanged($drawer_box_mount, $room_info['drawer_box_mount'], 'Drawer Box Mount');
      $changed[] = whatChanged($drawer_boxes, $room_info['drawer_boxes'], 'Drawer Boxes');

      $changed[] = whatChanged($sample_block_ordered, $room_info['sample_block_ordered'], 'Shop Block Ordered');
      $changed[] = whatChanged($door_only_ordered, $room_info['door_only_ordered'], 'Door Only Ordered');
      $changed[] = whatChanged($door_drawer_ordered, $room_info['door_drawer_ordered'], 'Press Ordered');
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
        carcass_interior_glaze_technique = '$carcass_interior_glaze_technique', drawer_boxes = '$drawer_boxes', drawer_box_mount = '$drawer_box_mount', vin_code = '$vin_final', 
        sample_block_ordered = '$sample_block_ordered', door_only_ordered = '$door_only_ordered', door_drawer_ordered = '$door_drawer_ordered',
        inset_square_ordered = '$inset_square_ordered', inset_beaded_ordered = '$inset_beaded_ordered', custom_vin_info = '$custom_vals' $sample_ordered_date WHERE id = '$room_id'")) {
      echo displayToast('success', "VIN has been updated for SO {$editInfo['sonum']} room {$editInfo['room']} iteration $iteration.", 'VIN Updated');
    } else {
      dbLogSQLErr($dbconn);
    }

    $ops = $_REQUEST['active_ops'];

    $sales_marketing_op = empty(sanitizeInput($editInfo['sales_marketing_bracket'])) ? 0 : sanitizeInput($editInfo['sales_marketing_bracket']);
    $shop_op = empty(sanitizeInput($editInfo['shop_bracket'])) ? 0 : sanitizeInput($editInfo['shop_bracket']);
    $preprod_op = empty(sanitizeInput($editInfo['preproduction_bracket'])) ? 0 : sanitizeInput($editInfo['preproduction_bracket']);
    $press_op = empty(sanitizeInput($editInfo['press_bracket'])) ? 0 : sanitizeInput($editInfo['press_bracket']);
    $paint_op = empty(sanitizeInput($editInfo['paint_bracket'])) ? 0 : sanitizeInput($editInfo['paint_bracket']);
    $custom_op = empty(sanitizeInput($editInfo['custom_bracket'])) ? 0 : sanitizeInput($editInfo['custom_bracket']);
    $shipping_op = empty(sanitizeInput($editInfo['shipping_bracket'])) ? 0 : sanitizeInput($editInfo['shipping_bracket']);
    $assembly_op = empty(sanitizeInput($editInfo['assembly_bracket'])) ? 0 : sanitizeInput($editInfo['assembly_bracket']);
    $welding_op = empty(sanitizeInput($editInfo['welding_bracket'])) ? 0 : sanitizeInput($editInfo['welding_bracket']);

    $changed[] = whatChanged($sales_marketing_op, $room_info['sales_marketing_bracket'], 'Sales/Marketing Bracket', false, false, true);
    $changed[] = whatChanged($shop_op, $room_info['shop_bracket'], 'Shop Bracket', false, false, true);
    $changed[] = whatChanged($preprod_op, $room_info['preproduction_bracket'], 'Pre-Production Bracket', false, false, true);
    $changed[] = whatChanged($press_op, $room_info['press_bracket'], 'Press Bracket', false, false, true);
    $changed[] = whatChanged($paint_op, $room_info['paint_bracket'], 'Paint Bracket', false, false, true);
    $changed[] = whatChanged($custom_op, $room_info['custom_bracket'], 'Custom Bracket', false, false, true);
    $changed[] = whatChanged($shipping_op, $room_info['shipping_bracket'], 'Shipping Bracket', false, false, true);
    $changed[] = whatChanged($assembly_op, $room_info['assembly_bracket'], 'Install Bracket', false, false, true);
    $changed[] = whatChanged($welding_op, $room_info['welding_bracket'], 'Welding Bracket', false, false, true);

    $sales_marketing_pub = !empty($editInfo['sales_marketing_published']) ? sanitizeInput($editInfo['sales_marketing_published']) : 0;
    $sample_pub = !empty($editInfo['sample_published']) ? sanitizeInput($editInfo['sample_published']) : 0;
    $preprod_pub = !empty($editInfo['preprod_published']) ? sanitizeInput($editInfo['preprod_published']) : 0;
    $press_pub = !empty($editInfo['press_published']) ? sanitizeInput($editInfo['press_published']) : 0;
    $paint_pub = !empty($editInfo['paint_published']) ? sanitizeInput($editInfo['paint_published']) : 0;
    $custom_pub = !empty($editInfo['custom_published']) ? sanitizeInput($editInfo['custom_published']) : 0;
    $shipping_pub = !empty($editInfo['shipping_published']) ? sanitizeInput($editInfo['shipping_published']) : 0;
    $assembly_pub = !empty($editInfo['assembly_published']) ? sanitizeInput($editInfo['assembly_published']) : 0;
    $welding_pub = !empty($editInfo['welding_published']) ? sanitizeInput($editInfo['welding_published']) : 0;


    $changed[] = whatChanged($sales_marketing_pub, $room_info['sales_marketing_published'], 'Sales/Marketing Bracket', false, true);
    $changed[] = whatChanged($sample_pub, $room_info['sample_published'], 'Shop Bracket', false, true);
    $changed[] = whatChanged($preprod_pub, $room_info['preproduction_published'], 'Pre-Production Bracket', false, true);
    $changed[] = whatChanged($press_pub, $room_info['press_published'], 'Press Bracket', false, true);
    $changed[] = whatChanged($paint_pub, $room_info['paint_published'], 'Paint Bracket', false, true);
    $changed[] = whatChanged($custom_pub, $room_info['custom_published'], 'Custom Bracket', false, true);
    $changed[] = whatChanged($shipping_pub, $room_info['shipping_published'], 'Shipping Bracke', false, true);
    $changed[] = whatChanged($assembly_pub, $room_info['assembly_published'], 'Install Bracket', false, true);
    $changed[] = whatChanged($welding_pub, $room_info['welding_published'], 'Edgebanding Bracket', false, true);
    $changed[] = whatChanged($ops, $room_info['individual_bracket_buildout'], 'Active Bracket Operations');

    if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$ops' WHERE id = '$room_id'")) {
      $update_result = $dbconn->query("UPDATE rooms SET sales_marketing_bracket = '$sales_marketing_op', preproduction_bracket = '$preprod_op', shop_bracket = '$shop_op', press_bracket = '$press_op', welding_bracket = '$welding_op',
    custom_bracket = '$custom_op', paint_bracket = '$paint_op', shipping_bracket = '$shipping_op', assembly_bracket = '$assembly_op', sales_marketing_published = '$sales_marketing_pub', sample_published = '$sample_pub',
    preproduction_published = '$preprod_pub', press_published = '$press_pub', paint_published = '$paint_pub', welding_published = '$welding_pub', custom_published = '$custom_pub', shipping_published = '$shipping_pub',
    assembly_published = '$assembly_pub', payment_deposit = '$deposit_received',
    payment_final = '$final_payment', payment_del_ptl = '$ptl_del' WHERE id = '$room_id'");

      createOpQueue($sales_marketing_pub, 'Sales/Marketing', $sales_marketing_op, $room_id);
      createOpQueue($sample_pub, 'Shop', $shop_op, $room_id);
      createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $room_id);
      createOpQueue($press_pub, 'Press', $press_op, $room_id);
      createOpQueue($paint_pub, 'Paint', $paint_op, $room_id);
      createOpQueue($custom_pub, 'Custom', $custom_op, $room_id);
      createOpQueue($shipping_pub, 'Shipping', $shipping_op, $room_id);
      createOpQueue($assembly_pub, 'Assembly', $assembly_op, $room_id);
      createOpQueue($welding_pub, 'Welding', $welding_op, $room_id);

      echo displayToast('success', 'All operations have been refreshed and the bracket has been updated.', 'Updated & Refreshed');
    } else {
      dbLogSQLErr($dbconn);
    }


    if(!empty(array_values(array_filter($changed)))) {
      $c_note = '<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
      $c_note .= implode(", ", array_values(array_filter($changed)));

      if(empty($_SESSION['userInfo'])) {
        $user = 36;
      } else {
        $user = $_SESSION['userInfo']['id'];
      }

      $stmt = $dbconn->prepare("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES (?, 'room_note_log', UNIX_TIMESTAMP(), $user, ?);");
      $stmt->bind_param('si', $c_note, $room_id);
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
  case 'upload_attachment':
    $room_id = sanitizeInput($_REQUEST['roomid']);

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
    $room = $room_qry->fetch_assoc();

    // filter out empty attachments
    $files = array_filter($_FILES['room_attachments']['name']);
    $file_count = count($files);

    if($file_count > 0) {
      for($i = 0; $i < $file_count; $i++) {
        $target_dir = SITE_ROOT . "/attachments/";
        $target_ext = end(explode(".", $files[$i]));
        $target_file = "{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}/{$files[$i]}";

        $uploadOK = true;
        $upload_err = '';

        $file_size = filesize($_FILES['room_attachments']['tmp_name'][$i]) / 1048576;

        $allowed_extensions = explode(",", FILE_TYPES);
        $ext = ".$target_ext";

        if($file_size > 15) {
          $uploadOK = false;
          $upload_err .= "File Size is greater than 15MB. Please use a smaller file.";
        }

        if(!in_array($ext, $allowed_extensions)) {
          $uploadOK = false;
          $upload_err .= "Incorrect Filetype. You can upload " . FILE_TYPES . ". Received $target_ext.";
        }

        if(file_exists($target_file)) {
          $uploadOK = false;
          $upload_err .= "File already exists on the server.";
        }

        if($uploadOK) {
          if(!file_exists("{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}")) {
            mkdir("{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}", 0777, true);
          }

          if(move_uploaded_file($_FILES['room_attachments']['tmp_name'][$i], $target_file)) {
            echo displayToast("success", "File Uploaded Successfully: {$files[$i]}", "Successful Upload");
          } else {
            echo displayToast("error", "Unable to upload due to system error.", "System Error");
          }
        } else {
          echo displayToast("error", $upload_err, "Unable to Upload");
        }
      }
    } else {
      echo displayToast("warning", "No files detected, unable to upload.", "No Files Attached");
    }

    break;
  case 'save_app_worksheet':
    $room = sanitizeInput($_REQUEST['room']);
    $notes = sanitizeInput($_REQUEST['notes']);
    $sheet = sanitizeInput($_REQUEST['sheet_type']);

    $spec = json_encode($_REQUEST['spec'], JSON_UNESCAPED_SLASHES);

    $worksheet_qry = $dbconn->query("SELECT w.*, s.name FROM appliance_worksheets w LEFT JOIN appliance_specs s ON w.spec = s.id WHERE room = $room AND spec = $sheet");

    if($worksheet_qry->num_rows === 0) {
      $stmt = $dbconn->prepare("INSERT INTO appliance_worksheets (room, spec, `values`, notes) VALUES (?, ?, ?, ?);");
      $stmt->bind_param('iiss', $room, $sheet, $spec, $notes);

      if($stmt->execute()) {
        echo $dbconn->insert_id;
      } else {
        dbLogSQLErr($dbconn);
        echo "false";
      }

      $stmt->close();
    } else {
      $worksheet = $worksheet_qry->fetch_assoc();

      $stmt = $dbconn->prepare("UPDATE appliance_worksheets SET `values` = ?, notes = ? WHERE id = {$worksheet['id']}");
      $stmt->bind_param('ss', $spec, $notes);

      if($stmt->execute()) {
        echo $worksheet['id'];
      } else {
        dbLogSQLErr($dbconn);
        echo "false";
      }
    }

    break;
  case 'load_app_worksheet':
    $id = sanitizeInput($_REQUEST['id']);

    $worksheet_qry = $dbconn->query("SELECT * FROM appliance_worksheets WHERE id = $id");

    if($worksheet_qry->num_rows > 0) {
      $worksheet = $worksheet_qry->fetch_assoc();

      echo json_encode($worksheet, JSON_UNESCAPED_SLASHES);
    }

    break;
  case 'save_coversheet':
    break;

  case 'submit_quote':
    $room_id = sanitizeInput($_REQUEST['roomid']);

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");

    if($room_qry->num_rows > 0) {
      $room = $room_qry->fetch_assoc();

      $so_num = $room['so_parent'];
      $room_letter = $room['room'];
      $iteration = $room['iteration'];

      $dbconn->query("UPDATE rooms SET quote_submission = UNIX_TIMESTAMP() WHERE id = '$room_id'");

      $message = "A new quote has been submitted through EagleEye.";

      $mail->sendMessage("orders@smcm.us", $_SESSION['userInfo']['email'], "New Quote Submission: {$so_num}{$room_letter}-{$iteration}", $message, false);

      echo displayToast("success", "Submitted quote for review.", "Quote Submitted");
    } else {
      echo displayToast("error", "Unable to find room, please refresh and try again.", "Room Error");
    }

    break;
}