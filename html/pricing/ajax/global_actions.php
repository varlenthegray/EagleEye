<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 6/26/2018
 * Time: 1:24 PM
 */
require '../../../includes/header_start.php';
require '../../../includes/classes/mail_handler.php';
require '../php/catalog.php';

//outputPHPErrs();

use catalog\catalog as Catalog;

$mail = new \MailHandler\mail_handler();

$vin_schema = getVINSchema();

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

    $str = $updated;
    $old = (bool)$old;
    $new = (bool)$new;
  } else {
    $str = "Updated $updated";
  }

  if(!empty($new)) {
    return ($old !== $new) ? "$title $str" : null;
  }
}

function displayBracketOpsMgmt($bracket, $room, $individual_bracket) {
  global $dbconn;

  $bracket_def = null;

  switch($bracket) {
    case 'Sales/Marketing':
      $bracket_def = 'sales_marketing_bracket';
      break;

    case 'Shop':
      $bracket_def = 'shop_bracket';
      break;

    case 'Pre-Production':
      $bracket_def = 'preproduction_bracket';
      break;

    case 'Press':
      $bracket_def = 'press_bracket';
      break;

    case 'Paint':
      $bracket_def = 'paint_bracket';
      break;

    case 'Custom':
      $bracket_def = 'custom_bracket';
      break;

    case 'Shipping':
      $bracket_def = 'shipping_bracket';
      break;

    case 'Assembly':
      $bracket_def = 'assembly_bracket';
      break;

    case 'Welding':
      $bracket_def = 'welding_bracket';
      break;

    default:
      $bracket_def = null;
  }

  $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE ORDER BY op_id ASC");

  $left_info = '';
  $right_info = '';

  while($op = $op_qry->fetch_assoc()) {
    $op_room_id = "op_{$op['id']}_room_{$room['id']}";

    if($op['op_id'] === '102' || $op['op_id'] === '103' || $op['responsible_dept'] === 'Accounting') {
      $color = 'red';
    } else {
      $color = '#132882';
    }

    if(in_array($op['id'], $individual_bracket, false)) {
      if($op['id'] === $room[$bracket_def]) {
        $selected = "checked='checked'";
      } else {
        $selected = '';
      }

      $left_info .= <<<HEREDOC
        <li class="active_ops_{$room['id']}" data-opnum="{$op['op_id']}" data-opid="{$op['id']}" data-roomid="{$room['id']}" style="clear:both;">
          <input type="radio" name="$bracket_def" id="$op_room_id" value="{$op['id']}" $selected>
          <label for="$op_room_id" style="color:$color;">{$op['job_title']}</label>
          <span class="pull-right cursor-hand text-md-center deactivate_op" data-opid="{$op['id']}" data-roomid="{$room['id']}" data-soid="{$room['so_parent']}"> <i class="fa fa-arrow-circle-right" style="width: 18px;"></i> </button>
        </li>
HEREDOC;
    } else {
      $right_info .= <<<HEREDOC
        <li class="inactive_ops_{$room['id']}" data-opnum="{$op['op_id']}" data-opid="{$op['id']}" data-roomid="{$room['id']}" style="clear:both;">
          <span class="pull-left cursor-hand activate_op" style="height:18px;width:18px;" data-opid="{$op['id']}" data-roomid="{$room['id']}" data-soid="{$room['so_parent']}"> <i class="fa fa-arrow-circle-left pull-left" style="margin:5px;"></i></span>
          <span style="color:$color;">{$op['job_title']}</span>
        </li>
HEREDOC;
    }
  }

  $room_bracket = "{$room['id']}_$bracket_def";

  return <<<HEREDOC
  <div class="col-md-12">
    <div class="row">
      <div class="col-md-6 custom_ul" style="border-right: 2px solid #000;">
        <h3 class="text-md-center">Active</h3>
        <ul class="radio" class="activeops_{$room['id']}" id="activeops_$room_bracket" data-bracket="$bracket_def">
          $left_info
        </ul>
      </div>

      <div class="col-md-6 custom_ul">
        <h3 class="text-md-center">Inactive</h3>
        <ul style="padding: 0;" class="inactiveops_{$room['id']}" id="inactiveops_$room_bracket" data-bracket="$bracket_def">
          $right_info
        </ul>
      </div>
    </div>
  </div>
HEREDOC;
}

function createOpQueue($bracket_pub, $bracket, $operation, $roomid) {
  global $dbconn;

  $op_queue_qry = $dbconn->query("SELECT op_queue.id AS QID, op_queue.*, operations.* FROM op_queue LEFT JOIN operations ON op_queue.operation_id = operations.id WHERE room_id = '$roomid' AND published = TRUE AND bracket = '$bracket'");

  // if the bracket is published
  if((bool)$bracket_pub) {
    if($op_queue_qry->num_rows > 0) {
      while($op_queue = $op_queue_qry->fetch_assoc()) {
        if($op_queue['operation_id'] === $operation && (bool)$op_queue['active']) {
          // the exact operation is currently active and we cannot take any further action
          echo displayToast('error', "Operation is active presently inside of $bracket.", 'Active Operation');
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

function updateAuditLog($note, $room_id) {
  global $dbconn;

  $user = $_SESSION['userInfo']['id'];

  $stmt = $dbconn->prepare("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES (?, 'room_note_log', UNIX_TIMESTAMP(), ?, ?);");
  $stmt->bind_param('sii', $note, $user, $room_id);
  $stmt->execute();
  $stmt->close();
}

switch($_REQUEST['action']) {
  case 'saveBatchNotes':
    //<editor-fold desc="Get the initial variable information">
    $room_id = sanitizeInput($_REQUEST['room_id']);

    parse_str($_REQUEST['notes'], $notes);
    //</editor-fold>

    //<editor-fold desc="Get the room information">
    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $room = $room_qry->fetch_assoc();
    //</editor-fold>

    //<editor-fold desc="Room Notes">
    if(!empty($notes['batch_note_input'])) {
      $batch_followup_date = sanitizeInput($notes['batch_inquiry_followup_date']);
      $batch_followup_individual = sanitizeInput($notes['batch_inquiry_requested_of']);

      $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$notes['batch_note_input']}', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

      $note_id = $dbconn->insert_id;

      if(!empty($batch_followup_date) && !empty($batch_followup_individual)) {
        $followup = strtotime($batch_followup_date);

        $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('batch_followup', UNIX_TIMESTAMP(), '$batch_followup_individual', '{$_SESSION['userInfo']['id']}', 'Room: {$room['so_parent']}{$room['room']}-{$room['iteration']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $note_id)");
      }
    }
    //</editor-fold>

    //<editor-fold desc="SO Notes">
    if(!empty($notes['project_note_input'])) {
      $so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '{$room['so_parent']}'");
      $so = $so_qry->fetch_assoc();

      $project_followup_date = sanitizeInput($notes['project_followup_date']);
      $project_followup_individual = sanitizeInput($notes['project_followup_requested_of']);

      $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$notes['project_note_input']}', 'so_inquiry', UNIX_TIMESTAMP(), '{$_SESSION['userInfo']['id']}', '{$so['id']}')");
      $note_id = $dbconn->insert_id;

      if(!empty($project_followup_date) && !empty($project_followup_individual)) {
        $followup = strtotime($project_followup_date);

        $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('so_inquiry', UNIX_TIMESTAMP(), '$project_followup_individual', '{$_SESSION['userInfo']['id']}', 'SO# {$room['so_parent']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $note_id)");
      }
    }
    //</editor-fold>

    //<editor-fold desc="Display a toast if the notes are not empty">
    if(!empty($notes['project_note_input']) || !empty($notes['batch_note_input'])) {
      echo displayToast('success', 'Room notes inserted successfully.', 'Room Notes Inserted');
    }
    //</editor-fold>

    break;
  case 'saveBatchDetails':
    //<editor-fold desc="Get the initial variable information">
    $room_id = sanitizeInput($_REQUEST['room_id']);

    parse_str($_REQUEST['formData'], $formData);
    //</editor-fold>

    //<editor-fold desc="Get the room information">
    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $room = $room_qry->fetch_assoc();
    //</editor-fold>

    //<editor-fold desc="Variable assignment">
    //<editor-fold desc="Batch info itself">
    $room_name = sanitizeInput($formData['room_name']);
    $product_type = sanitizeInput($formData['product_type']);
    $leadtime = sanitizeInput($formData['days_to_ship']);
    $order_status = sanitizeInput($formData['order_status']);
    $ship_via = sanitizeInput($formData['ship_via']);
    $ship_to_name = sanitizeInput($formData['ship_to_name']);
    $ship_to_address = sanitizeInput($formData['ship_to_address']);
    $ship_to_city = sanitizeInput($formData['ship_to_city']);
    $ship_to_state = sanitizeInput($formData['ship_to_state']);
    $ship_to_zip = sanitizeInput($formData['ship_to_zip']);
    $multi_room_ship = !empty($formData['multi_room_ship']) ? sanitizeInput($formData['multi_room_ship']) : 0;
    $payment_method = sanitizeInput($formData['payment_method']);
    $seen_approved = !empty($formData['seen_approved']) ? sanitizeInput($formData['seen_approved']) : 0;
    $unseen_approved = !empty($formData['unseen_approved']) ? sanitizeInput($formData['unseen_approved']) : 0;
    $requested_sample = !empty($formData['requested_sample']) ? sanitizeInput($formData['requested_sample']) : 0;
    $sample_reference = sanitizeInput($formData['sample_reference']);
    //</editor-fold>

    //<editor-fold desc="Signature details">
    $signature = sanitizeInput($formData['signature']);
    $sig_ip = $_SERVER['REMOTE_ADDR'];
    $sig_time = time();
    //</editor-fold>

    //<editor-fold desc="Delivery Notes">
    $delivery_notes = sanitizeInput($formData['delivery_notes']);
    $delivery_notes_id = sanitizeInput($formData['delivery_notes_id']);
    //</editor-fold>

    //<editor-fold desc="Accounting checkboxes">
    $deposit_received = !empty($formData['deposit_received']) ? (bool)$formData['deposit_received'] : 0;
    $ptl_del = !empty($formData['ptl_del']) ? (bool)$formData['ptl_del'] : 0;
    $final_payment = !empty($formData['final_payment']) ? (bool)$formData['final_payment'] : 0;
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="What's changed">
    //<editor-fold desc="Batch info itself">
    $changed[] = whatChanged($room_name, $room['room_name'], 'Room Name');
    $changed[] = whatChanged($product_type, $room['product_type'], 'Product Type');
    $changed[] = whatChanged($leadtime, $room['days_to_ship'], 'Lead Time');
    $changed[] = whatChanged($ship_via, $room['ship_via'], 'Ship Via');
    $changed[] = whatChanged($ship_to_name, $room['ship_to_name'], 'Ship To Name');
    $changed[] = whatChanged($ship_to_address, $room['ship_to_address'], 'Ship To Address');
    $changed[] = whatChanged($ship_to_city, $room['ship_to_city'], 'Ship To City');
    $changed[] = whatChanged($ship_to_state, $room['ship_to_state'], 'Ship To State');
    $changed[] = whatChanged($ship_to_zip, $room['ship_to_zip'], 'Ship To Zip');
    $changed[] = whatChanged($multi_room_ship, $room['multi_room_ship'], 'Multi-room Shipping');
    $changed[] = whatChanged($payment_method, $room['payment_method'], 'Payment Method');
    $changed[] = whatChanged($seen_approved, $room['seen_approved'], 'Shop seen/approved');
    $changed[] = whatChanged($unseen_approved, $room['unseen_approved'], 'Shop unseen/approved');
    $changed[] = whatChanged($requested_sample, $room['requested_sample'], 'Shop Requested');
    $changed[] = whatChanged($sample_reference, $room['sample_reference'], 'Shop Reference');
    //</editor-fold>

    //<editor-fold desc="Signature">
    $changed[] = whatChanged($signature, $room['signature'], 'Signature');
    //</editor-fold>

    //<editor-fold desc="Delivery Notes">
    $changed[] = whatChanged($delivery_notes, $room['delivery_notes'], 'Delivery Notes');
    //</editor-fold>

    //<editor-fold desc="Accounting checkbox change record">
    $changed[] = whatChanged($deposit_received, $room_info['payment_deposit'], 'Deposit Payment');
    $changed[] = whatChanged($final_payment, $room_info['payment_final'], 'Final Payment');
    $changed[] = whatChanged($ptl_del, $room_info['payment_del_ptl'], 'Prior to Loading/Delivery Payment');
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="Database Update">
    //<editor-fold desc="DB: Delivery Notes">
    if(!empty($formData['delivery_notes_id'])) {
      $dbconn->query("UPDATE notes SET note = '$delivery_notes', timestamp = UNIX_TIMESTAMP() WHERE id = '$delivery_notes_id'");

      $changed[] = 'Delivery Notes Updated';
    } else if(!empty($formData['delivery_notes'])) {
      if ($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_delivery'")->num_rows === 0) {
        $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$delivery_notes', 'room_note_delivery', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

        $changed[] = 'Delivery Notes Created';
      } else {
        echo displayToast('warning', 'Delivery Notes already exist. Please refresh your page and try again.', 'Delivery Notes Exist');
      }
    }
    //</editor-fold>

    //<editor-fold desc="DB: Update Room">
    $update_room = $dbconn->query("UPDATE rooms SET
      room_name = '$room_name', 
      product_type = '$product_type', 
      days_to_ship = '$leadtime', 
      order_status = '$order_status',
      ship_via = '$ship_via', 
      ship_name = '$ship_to_name', 
      ship_address = '$ship_to_address', 
      ship_city = '$ship_to_city',
      ship_state = '$ship_to_state', 
      ship_zip = '$ship_to_zip', 
      multi_room_ship = $multi_room_ship,
      payment_method = '$payment_method',
      sample_seen_approved = $seen_approved, 
      sample_unseen_approved = $unseen_approved, 
      sample_requested = $requested_sample,
      sample_reference = '$sample_reference', 
      esig = '$signature',
      esig_ip = '$sig_ip',
      esig_time = $sig_time,
      payment_deposit = $deposit_received, 
      payment_del_ptl = $ptl_del,
      payment_final = $final_payment
    WHERE id = '$room_id'");

    //<editor-fold desc="Output based on DB update">
    if($update_room) {
      echo displayToast('success', 'Room updated successfully.', 'Room Updated');
    } else {
      dbLogSQLErr($dbconn);
    }
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="DB: Insert What's Changed">
    if(!empty(array_values(array_filter($changed)))) {
      $c_note = '<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
      $c_note .= implode(', ', array_values(array_filter($changed)));

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
    //</editor-fold>
    //</editor-fold>

    break;
  case 'saveCabinetDetails':
    //<editor-fold desc="Get the initial variable information">
    $room_id = sanitizeInput($_REQUEST['room_id']);
    $custom_vals = $_REQUEST['customVals']; // custom fields in VIN sheet

    parse_str($_REQUEST['formData'], $formData);
    //</editor-fold>

    //<editor-fold desc="Get the room information">
    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $room = $room_qry->fetch_assoc();
    //</editor-fold>

    //<editor-fold desc="Variable assignment">
    $construction_method = sanitizeInput($formData['construction_method']);
    $species_grade = sanitizeInput($formData['species_grade']);
    $carcass_material = sanitizeInput($formData['carcass_material']);
    $door_design = sanitizeInput($formData['door_design']);
    $panel_raise_door = sanitizeInput($formData['panel_raise_door']);
    $panel_raise_sd = sanitizeInput($formData['panel_raise_sd']);
    $panel_raise_td = sanitizeInput($formData['panel_raise_td']);
    $style_rail_width = sanitizeInput($formData['style_rail_width']);
    $edge_profile = sanitizeInput($formData['edge_profile']);
    $framing_bead = sanitizeInput($formData['framing_bead']);
    $framing_options = sanitizeInput($formData['framing_options']);
    $drawer_boxes = sanitizeInput($formData['drawer_boxes']);
    $drawer_guide = sanitizeInput($formData['drawer_guide']);
    $finish_code = sanitizeInput($formData['finish_code']);
    $sheen = sanitizeInput($formData['sheen']);
    $glaze = sanitizeInput($formData['glaze']);
    $glaze_technique = sanitizeInput($formData['glaze_technique']);
    $antiquing = sanitizeInput($formData['antiquing']);
    $worn_edges = sanitizeInput($formData['worn_edges']);
    $distress_level = sanitizeInput($formData['distress_level']);
    $green_gard = sanitizeInput($formData['green_gard']);
    //</editor-fold>

    //<editor-fold desc="What's Changed">
    $changed[] = whatChanged($construction_method, $room_info['construction_method'], 'Construction Method');
    $changed[] = whatChanged($species_grade, $room_info['species_grade'], 'Species/Grade');
    $changed[] = whatChanged($carcass_material, $room_info['carcass_material'], 'Carcass Material');
    $changed[] = whatChanged($door_design, $room_info['door_design'], 'Door Design');
    $changed[] = whatChanged($panel_raise_door, $room_info['panel_raise_door'], 'Panel Raise (Door)');
    $changed[] = whatChanged($panel_raise_sd, $room_info['panel_raise_sd'], 'Panel Raise Shoot Drawer');
    $changed[] = whatChanged($panel_raise_td, $room_info['panel_raise_td'], 'Panel Raise Tall Drawer');
    $changed[] = whatChanged($style_rail_width, $room_info['style_rail_width'], 'Style/Rail Width');
    $changed[] = whatChanged($edge_profile, $room_info['edge_profile'], 'Edge Profile');
    $changed[] = whatChanged($framing_bead, $room_info['framing_bead'], 'Framing Bead');
    $changed[] = whatChanged($framing_options, $room_info['framing_options'], 'Framing Options');
    $changed[] = whatChanged($drawer_boxes, $room_info['drawer_boxes'], 'Drawer Boxes');
    $changed[] = whatChanged($drawer_guide, $room_info['drawer_guide'], 'Drawer Guide');
    $changed[] = whatChanged($finish_code, $room_info['finish_code'], 'Finish Code');
    $changed[] = whatChanged($sheen, $room_info['sheen'], 'Sheen');
    $changed[] = whatChanged($glaze, $room_info['glaze'], 'Glaze');
    $changed[] = whatChanged($glaze_technique, $room_info['glaze_technique'], 'Glaze Technique');
    $changed[] = whatChanged($antiquing, $room_info['antiquing'], 'Antiquing');
    $changed[] = whatChanged($worn_edges, $room_info['worn_edges'], 'Worn Edges');
    $changed[] = whatChanged($distress_level, $room_info['distress_level'], 'Distress Level');
    $changed[] = whatChanged($green_gard, $room_info['green_gard'], 'Green Gard');
    //</editor-fold>

    //<editor-fold desc="Database Update">
    //<editor-fold desc="DB: Notes">
    //<editor-fold desc="DB: Design notes">
    if(!empty($formData['design_notes_id'])) {
      $dbconn->query("UPDATE notes SET note = '$room_note_design', timestamp = UNIX_TIMESTAMP() WHERE id = '$room_note_design_id'");

      $changed[] = 'Design Notes Updated';
    } else if(!empty($formData['room_note_design'])) {
      if($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_design'")->num_rows === 0) {
        $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$room_note_design', 'room_note_design', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

        $changed[] = 'Design Notes Created';
      } else {
        echo displayToast('warning', 'Design Note already exists. Please refresh your page and try again.', 'Design Note Exists');
      }
    }
    //</editor-fold>

    //<editor-fold desc="DB: Finishing Notes">
    if(!empty($formData['fin_sample_notes_id'])) {
      if($dbconn->query("UPDATE notes SET note = '$fin_sample_notes', timestamp = UNIX_TIMESTAMP() WHERE id = '$fin_sample_notes_id'")) {
        $changed[] = 'Finishing/Shop Notes Updated';
      } else {
        dbLogSQLErr($dbconn);
      }
    } else if(!empty($formData['fin_sample_notes'])) {
      if($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_fin_sample'")->num_rows === 0) {
        $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$fin_sample_notes', 'room_note_fin_sample', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

        $changed[] = 'Finishing/Shop Notes Created';
      } else {
        echo displayToast('warning', 'Finishing/Shop Note already exists. Please refresh your page and try again.', 'Finishing/Shop Note Exists');
      }
    }
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="DB: Update Cabinet Details for room">
    if($dbconn->query("UPDATE rooms SET 
        construction_method = '$construction_method', 
        species_grade = '$species_grade', 
        carcass_material = '$carcass_material', 
        door_design = '$door_design', 
        panel_raise_door = '$panel_raise_door', 
        panel_raise_sd = '$panel_raise_sd', 
        panel_raise_td = '$panel_raise_td', 
        style_rail_width = '$style_rail_width', 
        edge_profile = '$edge_profile', 
        framing_bead = '$framing_bead', 
        framing_options = '$framing_options', 
        drawer_boxes = '$drawer_boxes', 
        drawer_guide = '$drawer_guide', 
        finish_code = '$finish_code', 
        sheen = '$sheen', 
        glaze = '$glaze', 
        glaze_technique = '$glaze_technique', 
        antiquing = '$antiquing', 
        worn_edges = '$worn_edges', 
        distress_level = '$distress_level', 
        green_gard = '$green_gard', 
        custom_vin_info = '$custom_vals'
      WHERE id = '$room_id'")) {
      echo displayToast('success', 'Room updated successfully.', 'Room Updated');
    } else {
      dbLogSQLErr($dbconn);
    }
    //</editor-fold>

    //<editor-fold desc="DB: Insert What's Changed">
    if(!empty(array_values(array_filter($changed)))) {
      $c_note = '<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
      $c_note .= implode(', ', array_values(array_filter($changed)));

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
    //</editor-fold>
    //</editor-fold>

    break;
  case 'saveItemList':
    $room_id = sanitizeInput($_REQUEST['room_id']);
    $cat = new Catalog;

    //<editor-fold desc="Saving the Cabinet List">
    $cab_list = sanitizeInput($_REQUEST['cabinet_list']);
    $cat->saveCatalog($room_id, $cab_list);
    //</editor-fold>

    echo displayToast('success', 'Successfully saved the item list.', 'Item List Saved');

    break;
  case 'roomSave':
    //<editor-fold desc="Initial Setup, variable capture">
    $cat = new Catalog;

    parse_str($_REQUEST['formData'], $info); // general form information
    parse_str($_REQUEST['cabinet_specifications'], $cabinet_specifications); // global: cabinet specifications
    parse_str($_REQUEST['accounting_notes'], $accounting_notes); // accounting and notes

    $custom_vals = $_REQUEST['customVals']; // custom fields in VIN sheet
    $room_id = sanitizeInput($_REQUEST['room_id']);
    $keyPath = json_decode($_REQUEST['keys']); // the ID's of company/project/batch

    foreach($info AS $k => $i) {
      $info[$k] = sanitizeInput($i);
    }

    foreach($keyPath AS $k => $i) {
      $keyPath[$k] = sanitizeInput($i);
    }

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $room_info = $room_qry->fetch_assoc();

    $deposit_received = !empty($info['deposit_received']) ? (bool)$info['deposit_received'] : 0;
    $final_payment = !empty($info['final_payment']) ? (bool)$info['final_payment'] : 0;
    $ptl_del = !empty($info['ptl_del']) ? (bool)$info['ptl_del'] : 0;

    $changed[] = whatChanged($deposit_received, $room_info['payment_deposit'], 'Deposit Payment');
    $changed[] = whatChanged($final_payment, $room_info['payment_final'], 'Final Payment');
    $changed[] = whatChanged($ptl_del, $room_info['payment_del_ptl'], 'Prior to Loading/Delivery Payment');
    $changed[] = !empty($notes) ? 'Notes added' : null;
    //</editor-fold>

    //<editor-fold desc="Capture VIN Info">
    $species_grade = sanitizeInput($info['species_grade']);
    $construction_method = sanitizeInput($info['construction_method']);
    $carcass_material = sanitizeInput($info['carcass_material']);
    $door_design = sanitizeInput($info['door_design']);
    $panel_raise_door = sanitizeInput($info['panel_raise_door']);
    $panel_raise_sd = sanitizeInput($info['panel_raise_sd']);
    $panel_raise_td = sanitizeInput($info['panel_raise_td']);
    $style_rail_width = sanitizeInput($info['style_rail_width']);
    $edge_profile = sanitizeInput($info['edge_profile']);
    $framing_bead = sanitizeInput($info['framing_bead']);
    $framing_options = sanitizeInput($info['framing_options']);
    $drawer_boxes = sanitizeInput($info['drawer_boxes']);
    $drawer_guide = sanitizeInput($info['drawer_guide']);
    $finish_code = sanitizeInput($info['finish_code']);
    $sheen = sanitizeInput($info['sheen']);
    $glaze = sanitizeInput($info['glaze']);
    $glaze_technique = sanitizeInput($info['glaze_technique']);
    $antiquing = sanitizeInput($info['antiquing']);
    $worn_edges = sanitizeInput($info['worn_edges']);
    $distress_level = sanitizeInput($info['distress_level']);
    $green_gard = sanitizeInput($info['green_gard']);
    //</editor-fold>

    //<editor-fold desc="Capture Global Info">
    $room_name = sanitizeInput($info['room_name']);
    $product_type = sanitizeInput($info['product_type']);
    $dealer_po = sanitizeInput($info['dealer_po']);
    $ship_via = sanitizeInput($info['ship_via']);
    $ship_to_name = sanitizeInput($info['ship_to_name']);
    $ship_to_address = sanitizeInput($info['ship_to_address']);
    $ship_to_city = sanitizeInput($info['ship_to_city']);
    $ship_to_state = sanitizeInput($info['ship_to_state']);
    $ship_to_zip = sanitizeInput($info['ship_to_zip']);
    $shipping_cost = sanitizeInput($info['shipping_cost']); // wtf is this?
    $shipping_cubes = !empty($info['shipping_cubes']) ? sanitizeInput($info['shipping_cubes']) : 0;
    $payment_method = sanitizeInput($info['payment_method']);
    $leadtime = sanitizeInput($info['days_to_ship']);
    $order_status = sanitizeInput($info['order_status']);
    $seen_approved = sanitizeInput($info['seen_approved']);
    $unseen_approved = sanitizeInput($info['unseen_approved']);
    $requested_sample = sanitizeInput($info['requested_sample']);
    $sample_reference = sanitizeInput($info['sample_reference']);
    $multi_room_ship = sanitizeInput($info['multi_room_ship']);
    $jobsite_delivery = sanitizeInput($info['jobsite_delivery']);
    //</editor-fold>

    $seen_approved = empty($seen_approved) ? 0 : 1;
    $unseen_approved = empty($unseen_approved) ? 0 : 1;
    $requested_sample = empty($requested_sample) ? 0 : 1;
    $multi_room_ship = empty($multi_room_ship) ? 0 : 1;
    $jobsite_delivery = empty($jobsite_delivery) ? 0 : 1;

    //<editor-fold desc="What's Changed">
    $changed[] = whatChanged($species_grade, $room_info['species_grade'], 'Species/Grade');
    $changed[] = whatChanged($construction_method, $room_info['construction_method'], 'Construction Method');
    $changed[] = whatChanged($carcass_material, $room_info['carcass_material'], 'Carcass Material');
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
    $changed[] = whatChanged($green_gard, $room_info['green_gard'], 'Green Gard');
    $changed[] = whatChanged($antiquing, $room_info['antiquing'], 'Antiquing');
    $changed[] = whatChanged($worn_edges, $room_info['worn_edges'], 'Worn Edges');
    $changed[] = whatChanged($distress_level, $room_info['distress_level'], 'Distress Level');
    $changed[] = whatChanged($drawer_box_mount, $room_info['drawer_box_mount'], 'Drawer Box Mount');
    $changed[] = whatChanged($drawer_boxes, $room_info['drawer_boxes'], 'Drawer Boxes');
    $changed[] = whatChanged($drawer_guide, $room_info['drawer_guide'], 'Drawer Guide');
    $changed[] = whatChanged($dealer_po, $room_info['dealer_po'], 'Dealer PO');
    $changed[] = whatChanged($room_name, $room_info['room_name'], 'Room Name');
    $changed[] = whatChanged($order_status, $room_info['order_status'], 'Order Status');
    $changed[] = whatChanged($seen_approved, $room_info['seen_approved'], 'Shop seen/approved');
    $changed[] = whatChanged($unseen_approved, $room_info['unseen_approved'], 'Shop unseen/approved');
    $changed[] = whatChanged($requested_sample, $room_info['requested_sample'], 'Shop Requested');
    $changed[] = whatChanged($sample_reference, $room_info['sample_reference'], 'Shop Reference');
    $changed[] = whatChanged($multi_room_ship, $room_info['multi_room_ship'], 'Multi-room Shipping');
    $changed[] = whatChanged($jobsite_delivery, $room_info['jobsite_delivery'], 'Jobsite Delivery');
    //</editor-fold>

    //<editor-fold desc="Variable assignment">
    //<editor-fold desc="Signature details">
    $signature = sanitizeInput($info['signature']);
    $sig_ip = $_SERVER['REMOTE_ADDR'];
    $sig_time = time();
    //</editor-fold>

    //<editor-fold desc="Accounting checkboxes">
    $deposit_received = !empty($info['deposit_received']) ? (bool)$info['deposit_received'] : 0;
    $ptl_del = !empty($info['ptl_del']) ? (bool)$info['ptl_del'] : 0;
    $final_payment = !empty($info['final_payment']) ? (bool)$info['final_payment'] : 0;
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="What's changed">
    //<editor-fold desc="Batch info itself">
    $changed[] = whatChanged($product_type, $room['product_type'], 'Product Type');
    $changed[] = whatChanged($leadtime, $room['days_to_ship'], 'Lead Time');
    $changed[] = whatChanged($ship_via, $room['ship_via'], 'Ship Via');
    $changed[] = whatChanged($ship_to_name, $room['ship_to_name'], 'Ship To Name');
    $changed[] = whatChanged($ship_to_address, $room['ship_to_address'], 'Ship To Address');
    $changed[] = whatChanged($ship_to_city, $room['ship_to_city'], 'Ship To City');
    $changed[] = whatChanged($ship_to_state, $room['ship_to_state'], 'Ship To State');
    $changed[] = whatChanged($ship_to_zip, $room['ship_to_zip'], 'Ship To Zip');
    $changed[] = whatChanged($payment_method, $room['payment_method'], 'Payment Method');
    //</editor-fold>

    //<editor-fold desc="Signature">
    $changed[] = whatChanged($signature, $room['signature'], 'Signature');
    //</editor-fold>

    //<editor-fold desc="Delivery Notes">
    $changed[] = whatChanged($delivery_notes, $room['delivery_notes'], 'Delivery Notes');
    //</editor-fold>

    //<editor-fold desc="Accounting checkbox change record">
    $changed[] = whatChanged($deposit_received, $room_info['payment_deposit'], 'Deposit Payment');
    $changed[] = whatChanged($final_payment, $room_info['payment_final'], 'Final Payment');
    $changed[] = whatChanged($ptl_del, $room_info['payment_del_ptl'], 'Prior to Loading/Delivery Payment');
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="DB: Notes">
    //<editor-fold desc="Room Notes">
    $room_note_design = sanitizeInput($info['room_note_design']);
    $room_note_design_id = sanitizeInput($info['design_notes_id']);

    $fin_sample_notes = sanitizeInput($info['fin_sample_notes']);
    $fin_sample_notes_id = sanitizeInput($info['fin_sample_notes_id']);

    $delivery_notes = sanitizeInput($info['delivery_notes']);
    $delivery_notes_id = sanitizeInput($info['delivery_notes_id']);

    if(!empty($info['delivery_notes_id'])) {
      $dbconn->query("UPDATE notes SET note = '$delivery_notes', timestamp = UNIX_TIMESTAMP() WHERE id = '$delivery_notes_id'");

      $changed[] = 'Delivery Notes Updated';
    } else if(!empty($info['delivery_notes'])) {
      if ($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_delivery'")->num_rows === 0) {
        $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$delivery_notes', 'room_note_delivery', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

        $changed[] = 'Delivery Notes Created';
      } else {
        echo displayToast('warning', 'Delivery Note already exists. Please refresh your page and try again.', 'Delivery Note Exists');
      }
    }

    if(!empty($info['design_notes_id'])) {
      $dbconn->query("UPDATE notes SET note = '$room_note_design', timestamp = UNIX_TIMESTAMP() WHERE id = '$room_note_design_id'");

      $changed[] = 'Design Notes Updated';
    } else if(!empty($info['room_note_design'])) {
      if($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_design'")->num_rows === 0) {
        $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$room_note_design', 'room_note_design', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

        $changed[] = 'Design Notes Created';
      } else {
        echo displayToast('warning', 'Design Note already exists. Please refresh your page and try again.', 'Design Note Exists');
      }
    }

    if(!empty($info['fin_sample_notes_id'])) {
      if($dbconn->query("UPDATE notes SET note = '$fin_sample_notes', timestamp = UNIX_TIMESTAMP() WHERE id = '$fin_sample_notes_id'")) {
        $changed[] = 'Finishing/Shop Notes Updated';
      } else {
        dbLogSQLErr($dbconn);
      }
    } else if(!empty($info['fin_sample_notes'])) {
      if($dbconn->query("SELECT notes.* FROM notes LEFT JOIN rooms ON notes.type_id = rooms.id WHERE type_id = '{$room_info['id']}' AND note_type = 'room_note_fin_sample'")->num_rows === 0) {
        $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$fin_sample_notes', 'room_note_fin_sample', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

        $changed[] = 'Finishing/Shop Notes Created';
      } else {
        echo displayToast('warning', 'Finishing/Shop Note already exists. Please refresh your page and try again.', 'Finishing/Shop Note Exists');
      }
    }
    //</editor-fold>

    //<editor-fold desc="SO Notes">
    if(!empty($info['company_notes'])) {
      $followup_date = $info['company_followup_date'];
      $followup_individual = $info['requested_of'];

      if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$info['company_notes']}', 'company_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '{$keyPath[0]}')")) {
        $note_id = $dbconn->insert_id;

        if(!empty($followup_date) && !empty($followup_individual)) {
          $followup = strtotime($followup_date);

          $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('company_followup', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'Company: {$info['company_name']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $note_id)");
        }
      } else {
        dbLogSQLErr($dbconn);
      }
    }

    if(!empty($info['project_notes'])) {
      $followup_date = $info['project_followup_date'];
      $followup_individual = $info['project_requested_of'];

      if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$info['project_notes']}', 'so_inquiry', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '{$keyPath[1]}')")) {
        $note_id = $dbconn->insert_id;

        if(!empty($followup_date) && !empty($followup_individual)) {
          $followup = strtotime($followup_date);

          $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('so_inquiry', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'Company: {$info['company_name']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $note_id)");
        }
      } else {
        dbLogSQLErr($dbconn);
      }
    }

    if(!empty($info['batch_notes'])) {
      $followup_date = $info['batch_followup_date'];
      $followup_individual = $info['batch_requested_of'];

      if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$info['batch_notes']}', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '{$keyPath[2]}')")) {
        $note_id = $dbconn->insert_id;

        if(!empty($followup_date) && !empty($followup_individual)) {
          $followup = strtotime($followup_date);

          $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('room_note', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'Company: {$info['company_name']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $note_id)");
        }
      } else {
        dbLogSQLErr($dbconn);
      }
    }

    $so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = {$room_info['so_parent']}");
    $so = $so_qry->fetch_assoc();

    if(!empty($accounting_notes['inquiry'])) {
      $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$accounting_notes['inquiry']}', 'so_inquiry', UNIX_TIMESTAMP(), '{$_SESSION['userInfo']['id']}', '{$so['id']}')");
      $inquiry_id = $dbconn->insert_id;
    }

    if(!empty($followup_date)) {
      $followup = strtotime($followup_date);

      $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('so_inquiry', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'SO# {$room_info['so_parent']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $inquiry_id)");
    }
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="DB: Followup & Inquiry">
    if(!empty($followup_date) && !empty($followup_individual)) {
      $followup = strtotime($followup_date);

      $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('room_inquiry_reply', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'SO# {$cabinet_specifications['sonum']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $inquiry_id)");

      $followup_time = date(DATE_TIME_ABBRV, $followup);

      $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = $followup_individual");

      if($usr = $usr_qry->fetch_assoc()) {
        $msg_notes = nl2br($notes);
        $msg_notes = str_replace('  ', '&nbsp;&nbsp;', $msg_notes);

        $message = <<<HEREDOC
<strong>Followup Time: $followup_time</strong>

<h5 style="margin:2px 0;padding:0;">Notes:</h5>
$msg_notes -- {$_SESSION['userInfo']['name']}
HEREDOC;

        $subject = "{$room_info['so_parent']}{$room_info['room']}-{$room_info['iteration']}";

        $mail->sendMessage($usr['email'], $_SESSION['userInfo']['email'], $subject, $message, true);

        $dbconn->query("INSERT INTO tasks (description, created, last_updated, priority, assigned_to, due_date, submitted_by, resolved)
        VALUES ('$subject - $message', UNIX_TIMESTAMP(), null, '3 - End of Week', $followup_individual, null, {$_SESSION['userInfo']['id']}, FALSE);");
      }
    } elseif((empty($followup_date) && !empty($followup_individual)) || (!empty($followup_date) && empty($followup_individual))) {
      echo displayToast('warning', 'Unable to set a followup as there is a missing individual or date.', 'No Followup Set');
    }
    //</editor-fold>

    //<editor-fold desc="DB: Update Cabinet Details for room">
    $update_room_qry = $dbconn->prepare("UPDATE rooms SET 
      construction_method = ?, species_grade = ?, carcass_material = ?, door_design = ?, panel_raise_door = ?, panel_raise_sd = ?, panel_raise_td = ?, 
      style_rail_width = ?, edge_profile = ?, framing_bead = ?, framing_options = ?, drawer_boxes = ?, drawer_guide = ?, finish_code = ?, sheen = ?, 
      glaze = ?, glaze_technique = ?, antiquing = ?, worn_edges = ?, distress_level = ?, green_gard = ?, custom_vin_info = ?, room_name = ?, 
      product_type = ?, days_to_ship = ?, order_status = ?, ship_via = ?, ship_name = ?, ship_address = ?, ship_city = ?, ship_state = ?, 
      ship_zip = ?, multi_room_ship = ?, payment_method = ?, sample_seen_approved = ?, sample_unseen_approved = ?, sample_requested = ?,
      sample_reference = ?, esig = ?, esig_ip = ?, esig_time = ?, payment_deposit = ?, payment_del_ptl = ?, payment_final = ?, ship_cubes = ?,
      jobsite_delivery = ?
    WHERE id = $room_id");

    $update_room_qry->bind_param('ssssssssssssssssssssssssssisssssiiiiisssiiiiii', $construction_method, $species_grade, $carcass_material,
      $door_design, $panel_raise_door, $panel_raise_sd, $panel_raise_td, $style_rail_width, $edge_profile, $framing_bead, $framing_options, $drawer_boxes,
      $drawer_guide, $finish_code, $sheen, $glaze, $glaze_technique, $antiquing, $worn_edges, $distress_level, $green_gard, $custom_vals, $room_name,
      $product_type, $leadtime, $order_status, $ship_via, $ship_to_name, $ship_to_address, $ship_to_city, $ship_to_state, $ship_to_zip, $multi_room_ship,
      $payment_method, $seen_approved, $unseen_approved, $requested_sample, $sample_reference, $signature, $sig_ip, $sig_time, $deposit_received, $ptl_del,
      $final_payment, $shipping_cubes, $jobsite_delivery);

    if($update_room_qry->execute()) {
      echo displayToast('success', 'Room updated successfully.', 'Room Updated');
      $update_room_qry->close();
    } else {
      dbLogSQLErr($dbconn);
    }
    //</editor-fold>

    //<editor-fold desc="DB: What's Changed">
    if(!empty(array_values(array_filter($changed)))) {
      $c_note = '<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
      $c_note .= implode(', ', array_values(array_filter($changed)));

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
    //</editor-fold>

    //<editor-fold desc="Saving the Cabinet List">
    $cab_list = sanitizeInput($_REQUEST['cabinet_list']);
    $cat->saveCatalog($room_id, $cab_list);
    //</editor-fold>

    break;
  case 'modalApplianceWS':
    $room_id = sanitizeInput($_REQUEST['roomID']);

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $room = $room_qry->fetch_assoc();

    $worksheet_qry = $dbconn->query("SELECT w.*, s.*, v.value AS construction_method, w.id AS worksheetID FROM appliance_worksheets w LEFT JOIN appliance_specs s ON w.spec = s.id LEFT JOIN vin_schema v ON v.`key` = s.const_method WHERE w.room = $room_id AND v.segment = 'construction_method'");

    $existing_ws = null;

    if($worksheet_qry->num_rows > 0) {
      while($worksheet = $worksheet_qry->fetch_assoc()) {
        $existing_ws .= "<tr><td><a class='load_app_worksheet' id='{$worksheet['worksheetID']}' href='#'>{$worksheet['name']} ({$worksheet['construction_method']})</a></td></tr>";
      }
    } else {
      $existing_ws = '<tr><td><strong>No worksheets saved.</strong></td></tr>';
    }

    echo <<<HEREDOC
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">??</button>
          <h4 class="modal-title">Appliance Worksheets - {$room['room']}{$room['iteration']}</h4>
        </div>
        <div class="modal-body">
        <div class="row">
          <div class="col-md-2">
            <table width="100%">
              <tr><th class="text-md-center">Saved Sheets</th></tr>
              $existing_ws
            </table>
          </div>
            
            <div class="col-md-10 sheet_data"></div>
        </div>
        
          <script>
            $.post("/html/search/appliance_ws_info.php?room_id=" + active_room_id + "&id=1", function(data) {
              $(".sheet_data").html(data);
            });
          </script>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary waves-effect waves-light" id="modalAppWSSave">Save</button>
        </div>
      </div>
    </div>
HEREDOC;
    break;
  case 'modalBracketMgmt':
    $room_id = sanitizeInput($_REQUEST['roomID']);

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id' ORDER BY room, iteration ASC;");
    $room = $room_qry->fetch_assoc();

    $individual_bracket = json_decode($room['individual_bracket_buildout']);

    $sales_marketing_published = (bool)$room['sales_marketing_published'] ? 'checked' : NULL;
    $shop_published = (bool)$room['shop_published'] ? 'checked' : NULL;

    $sales_marketing_bracket = displayBracketOpsMgmt('Sales/Marketing', $room, $individual_bracket);
    $shop_bracket = displayBracketOpsMgmt('Shop', $room, $individual_bracket);

    $preprod_published = (bool)$room['preproduction_published'] ? 'checked' : NULL;
    $press_published = (bool)$room['press_published'] ? 'checked' : NULL;

    $preprod_bracket = displayBracketOpsMgmt('Pre-Production', $room, $individual_bracket);
    $press_bracket = displayBracketOpsMgmt('Press', $room, $individual_bracket);

    $paint_published = (bool)$room['paint_published'] ? 'checked' : NULL;
    $welding_published = (bool)$room['welding_published'] ? 'checked' : NULL;

    $paint_bracket = displayBracketOpsMgmt('Paint', $room, $individual_bracket);
    $welding_bracket = displayBracketOpsMgmt('Welding', $room, $individual_bracket);

    $custom_published = (bool)$room['custom_published'] ? 'checked' : NULL;
    $shipping_published = (bool)$room['shipping_published'] ? 'checked' : NULL;

    $custom_bracket = displayBracketOpsMgmt('Custom', $room, $individual_bracket);
    $shipping_bracket = displayBracketOpsMgmt('Shipping', $room, $individual_bracket);

    $assembly_published = (bool)$room['assembly_published'] ? 'checked' : NULL;

    $assembly_bracket = displayBracketOpsMgmt('Assembly', $room, $individual_bracket);

    echo /** @lang HTML */
    <<<HEREDOC
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">??</button>
          <h4 class="modal-title">Manage Brackets - {$room['room']}{$room['iteration']}</h4>
        </div>
        <div class="modal-body">
          <!--<div class="sticky" style="top:0;background-color:#FFF;width:100%;z-index:999;">
            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary waves-effect waves-light" id="modalAppWSSave">Save</button>
          </div>-->
        
          <div class="row">
            <div class="col-md-12">
              <form id="bracketAdjustments" action="#">
                <table width="100%" class="bracket-adjustment-table">
                  <tr>
                    <td style="width: 49.8%;" class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="sales_marketing_bracket">Sales/Marketing Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sales_marketing_published" value="1" id="sales_marketing_published" $sales_marketing_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                    <td style="background-color:#eceeef;"></td>
                    <td style="width: 49.8%;" class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="shop_bracket">Shop Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="shop_published" value="1" id="shop_published" $shop_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom">
                      $sales_marketing_bracket
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-bottom">
                      $shop_bracket
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="pre_prod_bracket">Pre-production Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="preprod_published" value="1" id="pre_prod_published" $preprod_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="door_drawer_bracket">Press</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="press_published" value="1" id="press_published" $press_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom">
                      $preprod_bracket
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-bottom">
                      $press_bracket
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="paint_bracket">Paint Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="paint_published" value="1" id="paint_published" $paint_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="welding_bracket">Welding Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="welding_published" value="1" id="welding_bracket" $welding_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom">
                      $paint_bracket
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-bottom">
                      $welding_bracket
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="custom_bracket">Custom Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="custom_published" value="1" id="custom_published" $custom_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="shipping_bracket">Shipping Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="shipping_published" value="1" id="shipping_published" $shipping_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom">
                      $custom_bracket
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-bottom">
                      $shipping_bracket
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-top" colspan="3">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="assembly_bracket">Assembly Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="assembly_published" value="1" id="assembly_published" $assembly_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom" colspan="3">
                      $assembly_bracket
                    </td>
                  </tr>
                </table>
              </form>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary waves-effect waves-light" id="modalBracketSave">Save</button>
        </div>
      </div>
    </div>
HEREDOC;

    break;
  case 'updateBracket':
    parse_str($_REQUEST['bracket_status'], $bracket_info);
    $ops = $_REQUEST['active_ops'];
    $room_id = sanitizeInput($_REQUEST['roomID']);

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id' ORDER BY room, iteration ASC;");
    $room = $room_qry->fetch_assoc();

    // grab the operation that each bracket is on
    $sales_marketing_op = empty(sanitizeInput($bracket_info['sales_marketing_bracket'])) ? 0 : sanitizeInput($bracket_info['sales_marketing_bracket']);
    $shop_op = empty(sanitizeInput($bracket_info['shop_bracket'])) ? 0 : sanitizeInput($bracket_info['shop_bracket']);
    $preprod_op = empty(sanitizeInput($bracket_info['preproduction_bracket'])) ? 0 : sanitizeInput($bracket_info['preproduction_bracket']);
    $press_op = empty(sanitizeInput($bracket_info['press_bracket'])) ? 0 : sanitizeInput($bracket_info['press_bracket']);
    $paint_op = empty(sanitizeInput($bracket_info['paint_bracket'])) ? 0 : sanitizeInput($bracket_info['paint_bracket']);
    $custom_op = empty(sanitizeInput($bracket_info['custom_bracket'])) ? 0 : sanitizeInput($bracket_info['custom_bracket']);
    $shipping_op = empty(sanitizeInput($bracket_info['shipping_bracket'])) ? 0 : sanitizeInput($bracket_info['shipping_bracket']);
    $assembly_op = empty(sanitizeInput($bracket_info['assembly_bracket'])) ? 0 : sanitizeInput($bracket_info['assembly_bracket']);
    $welding_op = empty(sanitizeInput($bracket_info['welding_bracket'])) ? 0 : sanitizeInput($bracket_info['welding_bracket']);
    // end of grabbing the current operation for each bracket

    // determine if any of the above (current bracket operation) has changed
    $changed[] = whatChanged($sales_marketing_op, $room['sales_marketing_bracket'], 'Sales/Marketing Bracket', false, false, true);
    $changed[] = whatChanged($shop_op, $room['shop_bracket'], 'Shop Bracket', false, false, true);
    $changed[] = whatChanged($preprod_op, $room['preproduction_bracket'], 'Pre-Production Bracket', false, false, true);
    $changed[] = whatChanged($press_op, $room['press_bracket'], 'Press', false, false, true);
    $changed[] = whatChanged($paint_op, $room['paint_bracket'], 'Paint Bracket', false, false, true);
    $changed[] = whatChanged($custom_op, $room['custom_bracket'], 'Custom Bracket', false, false, true);
    $changed[] = whatChanged($shipping_op, $room['shipping_bracket'], 'Shipping Bracket', false, false, true);
    $changed[] = whatChanged($assembly_op, $room['assembly_bracket'], 'Assembly Bracket', false, false, true);
    $changed[] = whatChanged($welding_op, $room['welding_bracket'], 'Welding Bracket', false, false, true);
    // end of the current bracket operation changes

    // grab the status of each bracket publish
    $sales_marketing_pub = !empty($bracket_info['sales_marketing_published']) ? sanitizeInput($bracket_info['sales_marketing_published']) : 0;
    $shop_pub = !empty($bracket_info['shop_published']) ? sanitizeInput($bracket_info['shop_published']) : 0;
    $preprod_pub = !empty($bracket_info['preprod_published']) ? sanitizeInput($bracket_info['preprod_published']) : 0;
    $press_pub = !empty($bracket_info['press_published']) ? sanitizeInput($bracket_info['press_published']) : 0;
    $paint_pub = !empty($bracket_info['paint_published']) ? sanitizeInput($bracket_info['paint_published']) : 0;
    $custom_pub = !empty($bracket_info['custom_published']) ? sanitizeInput($bracket_info['custom_published']) : 0;
    $shipping_pub = !empty($bracket_info['shipping_published']) ? sanitizeInput($bracket_info['shipping_published']) : 0;
    $assembly_pub = !empty($bracket_info['assembly_published']) ? sanitizeInput($bracket_info['assembly_published']) : 0;
    $welding_pub = !empty($bracket_info['welding_published']) ? sanitizeInput($bracket_info['welding_published']) : 0;
    // end of the status of each bracket publish

    // record the changes for the status of each bracket publish
    $changed[] = whatChanged($sales_marketing_pub, $room['sales_marketing_published'], 'Sales/Marketing Bracket', false, true);
    $changed[] = whatChanged($shop_pub, $room['shop_published'], 'Shop Bracket', false, true);
    $changed[] = whatChanged($preprod_pub, $room['preproduction_published'], 'Pre-Production Bracket', false, true);
    $changed[] = whatChanged($press_pub, $room['press_published'], 'Press', false, true);
    $changed[] = whatChanged($paint_pub, $room['paint_published'], 'Paint Bracket', false, true);
    $changed[] = whatChanged($custom_pub, $room['custom_published'], 'Custom Bracket', false, true);
    $changed[] = whatChanged($shipping_pub, $room['shipping_published'], 'Shipping Bracke', false, true);
    $changed[] = whatChanged($assembly_pub, $room['assembly_published'], 'Assembly Bracket', false, true);
    $changed[] = whatChanged($welding_pub, $room['welding_published'], 'Edgebanding Bracket', false, true);
    $changed[] = whatChanged($ops, $room['individual_bracket_buildout'], 'Active Bracket Operations');
    // end of recording the changes for the status of each bracket publish

    if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$ops' WHERE id = '$room_id'")) {
      if($dbconn->query("UPDATE rooms SET sales_marketing_bracket = '$sales_marketing_op', preproduction_bracket = '$preprod_op', shop_bracket = '$shop_op', press_bracket = '$press_op', welding_bracket = '$welding_op',
      custom_bracket = '$custom_op', paint_bracket = '$paint_op', shipping_bracket = '$shipping_op', assembly_bracket = '$assembly_op', sales_marketing_published = '$sales_marketing_pub', shop_published = '$shop_pub',
      preproduction_published = '$preprod_pub', press_published = '$press_pub', paint_published = '$paint_pub', welding_published = '$welding_pub', custom_published = '$custom_pub', shipping_published = '$shipping_pub',
      assembly_published = '$assembly_pub' WHERE id = '$room_id'")) {
        createOpQueue($sales_marketing_pub, 'Sales/Marketing', $sales_marketing_op, $room_id);
        createOpQueue($shop_pub, 'Shop', $shop_op, $room_id);
        createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $room_id);
        createOpQueue($press_pub, 'Press', $press_op, $room_id);
        createOpQueue($paint_pub, 'Paint', $paint_op, $room_id);
        createOpQueue($custom_pub, 'Custom', $custom_op, $room_id);
        createOpQueue($shipping_pub, 'Shipping', $shipping_op, $room_id);
        createOpQueue($assembly_pub, 'Assembly', $assembly_op, $room_id);
        createOpQueue($welding_pub, 'Welding', $welding_op, $room_id);

        if(!empty(array_values(array_filter($changed)))) {
          $c_note = '<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
          $c_note .= implode(', ', array_values(array_filter($changed)));

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

        echo displayToast('success', 'The bracket and operations have been updated.', 'Brackets & Operations Updated');
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
  case 'calcShipDate':
    $days_to_ship = sanitizeInput($_REQUEST['days_to_ship']);
    $roomID = sanitizeInput($_REQUEST['room_id']);

    $date_int['ship_date'] = calcDelDate($days_to_ship);
    $date_int['del_date'] = strtotime(date(DATE_DEFAULT, $date_int['ship_date']) . ' + 1 day');

    $date['ship_date'] = date(DATE_DEFAULT, $date_int['ship_date']);
    $date['del_date'] = date(DATE_DEFAULT, $date_int['del_date']);

    $dbconn->query("UPDATE rooms SET ship_date = {$date_int['ship_date']}, delivery_date = {$date_int['del_date']}, days_to_ship = '$days_to_ship' WHERE id = $roomID");

    echo json_encode($date, TRUE);
    break;
  case 'termsSign':
    $room_id = sanitizeInput($_REQUEST['room_id']);

    $signature = sanitizeInput($_REQUEST['sig']);
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = time();

    if(!empty(trim($signature))) {
      if($dbconn->query("UPDATE rooms SET esig = '$signature', esig_ip = '$ip', esig_time = '$time' WHERE id = $room_id AND esig IS NULL")) {
        echo displayToast('success', 'Successfully signed this quote.', 'Quote Signed');
      } else {
        echo displayToast('error', 'Unable to sign quote. Please contact your SMCM representative.', 'Quote Error');
      }
    } else {
      echo displayToast('error', 'Signature cannot be empty.', 'Empty Signature');
    }


    break;
  case 'getPriceGroup':
    $speciesGrade = sanitizeInput($_REQUEST['speciesGrade']);
    $doorDesign = sanitizeInput($_REQUEST['doorDesign']);

    $doorDesignID = null;
    $speciesGradeID = null;

    foreach($vin_schema['species_grade'] AS $line) {
      if($line['key'] === $speciesGrade) {
        $speciesGradeID = $line['id'];
        break;
      }
    }

    foreach($vin_schema['door_design'] AS $line) {
      if($line['key'] === $doorDesign) {
        $doorDesignID = $line['id'];
        break;
      }
    }

    if($doorDesignID !== '1544' && $speciesGradeID !== '11') {
      $price_group_qry = $dbconn->query("SELECT * FROM pricing_price_group_map WHERE door_style_id = $doorDesignID AND species_id = $speciesGradeID");

      // error resolution
      if($price_group_qry->num_rows === 1) {
        $price_group = $price_group_qry->fetch_assoc();
        $price_group = $price_group['price_group_id'];
      } else {
        echo 'Unknown';
      }
    } else {
      $price_group = 'Unknown';
    }

    echo $price_group;

    break;
  case 'modalOverrideShipping':
    $room_id = sanitizeInput($_REQUEST['roomID']);

    echo <<<HEREDOC
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">??</button>
          <h4 class="modal-title">Override Shipping Date</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <form id="modalShippingOverrideData" action="#">
                <table style="width:50%;margin:0 auto;">
                  <colgroup>
                    <col width="30%">
                    <col width="70%">
                  </colgroup>
                  <tr>
                    <td>New Date:</td>
                    <td><input type="text" class="form-control" name="new_date" placeholder="Date (Any Format)"></td>
                  </tr>
                </table>
                
                <input type="hidden" id="roomID" name="roomID" value="$room_id" />
              </form>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary waves-effect waves-light" id="modalShippingUpdate">Update</button>
        </div>
      </div>
    </div>
HEREDOC;

    break;
  case 'overrideShipping':
    $room_id = sanitizeInput($_REQUEST['roomID']);
    parse_str($_REQUEST['info'], $info);

    $newShip = strtotime($info['new_date']);
    $newDel = strtotime(date(DATE_DEFAULT, $newShip) . ' + 1 weekday');

    $dbconn->query("UPDATE rooms SET ship_date = $newShip, delivery_date = $newDel WHERE id = $room_id");

    $return['ship_date'] = date(DATE_DEFAULT, $newShip);
    $return['del_date'] = date(DATE_DEFAULT, $newDel);

    updateAuditLog("Manually set shipping date to $newShip", $room_id);

    echo json_encode($return);

    break;
  case 'modalCopyRoom':
    $room_id = sanitizeInput($_REQUEST['roomID']);

    $cur_room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $cur_room = $cur_room_qry->fetch_assoc();

    $other_rooms_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = {$cur_room['so_parent']} ORDER BY room, iteration ASC");

    $select = '<select name="new_room">';

    while($other_rooms = $other_rooms_qry->fetch_assoc()) {
      if($other_rooms['id'] !== $room_id) {
        $select .= "<option value='{$other_rooms['id']}'>{$other_rooms['room']}-{$other_rooms['iteration']}</option>";
      }
    }

    $select .= '</select>';

    echo <<<HEREDOC
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">??</button>
          <h4 class="modal-title">Copy Room Data</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <form id="modalCopyRoomData" action="#">
                <table style="width:50%;margin:0 auto;">
                  <colgroup>
                    <col width="30%">
                    <col width="70%">
                  </colgroup>
                  <tr>
                    <td>Copy To Room:</td>
                    <td>$select</td>
                  </tr>
                </table>
                
                <input type="hidden" id="roomID" name="roomID" value="$room_id" />
              </form>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary waves-effect waves-light" id="modalCopyRoom">Copy</button>
        </div>
      </div>
    </div>
HEREDOC;

    break;
  case 'copyRoomInfo':
    parse_str($_REQUEST['formInfo'], $info);

    $initial_room = sanitizeInput($info['roomID']);
    $copy_to_room = sanitizeInput($info['new_room']);

    $from_room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $initial_room");
    $from_room = $from_room_qry->fetch_assoc();

    $from_room_list_qry = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $initial_room");
    $from_room_list = $from_room_list_qry->fetch_assoc();

    $copy_to_list_qry = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $copy_to_room");

    $ship_date = (int)$from_room['ship_date'];
    $delivery_date = (int)$from_room['delivery_date'];
    $multi_room_ship = (int)$from_room['multi_room_ship'];
    $ext_carcass_same = (int)$from_room['ext_carcass_same'];
    $int_carcass_same = (int)$from_room['int_carcass_same'];
    $sample_block_ordered = (int)$from_room['sample_block_ordered'];
    $door_only_ordered = (int)$from_room['door_only_ordered'];
    $door_drawer_ordered = (int)$from_room['door_drawer_ordered'];
    $inset_square_ordered = (int)$from_room['inset_square_ordered'];
    $inset_beaded_ordered = (int)$from_room['inset_beaded_ordered'];
    $sample_ordered_date = (int)$from_room['sample_ordered_date'];
    $dealer_created = (int)$from_room['dealer_created'];
    $quote_submission = (int)$from_room['quote_submission'];
    $const_method_iteration = (int)$from_room['const_method_iteration'];
    $rework_iteration = (int)$from_room['rework_iteration'];
    $add_on_iteration = (int)$from_room['add_on_iteration'];
    $warranty_iteration = (int)$from_room['warranty_iteration'];
    $payment_deposit = (int)$from_room['payment_deposit'];
    $payment_final = (int)$from_room['payment_final'];
    $payment_del_ptl = (int)$from_room['payment_del_ptl'];

    if($dbconn->query("UPDATE rooms SET 
      `product_type` = '{$from_room['product_type']}',
      `room_type` = '{$from_room['room_type']}',
      `individual_bracket_buildout` = '{$from_room['individual_bracket_buildout']}',
      `order_status` = '{$from_room['order_status']}',
      `dealer_status` = '{$from_room['dealer_status']}',
      `days_to_ship` = '{$from_room['days_to_ship']}',
      `ship_via` = '{$from_room['ship_via']}',
      `ship_to` = '{$from_room['ship_to']}',
      `payee` = '{$from_room['payee']}',
      `payment_method` = '{$from_room['payment_method']}',
      `installation` = '{$from_room['installation']}',
      `ship_date` = $ship_date,
      `delivery_date` = $delivery_date,
      `multi_room_ship` = $multi_room_ship,
      `species_grade` = '{$from_room['species_grade']}',
      `construction_method` = '{$from_room['construction_method']}',
      `carcass_material` = '{$from_room['carcass_material']}',
      `door_design` = '{$from_room['door_design']}',
      `panel_raise_door` = '{$from_room['panel_raise_door']}',
      `panel_raise_sd` = '{$from_room['panel_raise_sd']}',
      `panel_raise_td` = '{$from_room['panel_raise_td']}',
      `edge_profile` = '{$from_room['edge_profile']}',
      `framing_bead` = '{$from_room['framing_bead']}',
      `framing_options` = '{$from_room['framing_options']}',
      `style_rail_width` = '{$from_room['style_rail_width']}',
      `finish_code` = '{$from_room['finish_code']}',
      `sheen` = '{$from_room['sheen']}',
      `glaze` = '{$from_room['glaze']}',
      `glaze_technique` = '{$from_room['glaze_technique']}',
      `antiquing` = '{$from_room['antiquing']}',
      `worn_edges` = '{$from_room['worn_edges']}',
      `distress_level` = '{$from_room['distress_level']}',
      `green_gard` = '{$from_room['green_gard']}',
      `ext_carcass_same` = $ext_carcass_same,
      `carcass_exterior_species` = '{$from_room['carcass_exterior_species']}',
      `carcass_exterior_finish_code` = '{$from_room['carcass_exterior_finish_code']}',
      `carcass_exterior_glaze_color` = '{$from_room['carcass_exterior_glaze_color']}',
      `carcass_exterior_glaze_technique` = '{$from_room['carcass_exterior_glaze_technique']}',
      `int_carcass_same` = $int_carcass_same,
      `carcass_interior_species` = '{$from_room['carcass_interior_species']}',
      `carcass_interior_finish_code` = '{$from_room['carcass_interior_finish_code']}',
      `carcass_interior_glaze_color` = '{$from_room['carcass_interior_glaze_color']}',
      `carcass_interior_glaze_technique` = '{$from_room['carcass_interior_glaze_technique']}',
      `drawer_boxes` = '{$from_room['drawer_boxes']}',
      `drawer_box_mount` = '{$from_room['drawer_box_mount']}',
      `drawer_guide` = '{$from_room['drawer_guide']}',
      `vin_code` = '{$from_room['vin_code']}',
      `sample_block_ordered` = $sample_block_ordered,
      `door_only_ordered` = $door_only_ordered,
      `door_drawer_ordered` = $door_drawer_ordered,
      `inset_square_ordered` = $inset_square_ordered,
      `inset_beaded_ordered` = $inset_beaded_ordered,
      `sample_ordered_date` = $sample_ordered_date,
      `vin_ship_via` = '{$from_room['vin_ship_via']}',
      `ship_site` = '{$from_room['ship_site']}',
      `delivery_note` = '{$from_room['delivery_note']}',
      `global_note` = '{$from_room['global_note']}',
      `fin_sample_note` = '{$from_room['fin_sample_note']}',
      `const_method_iteration` = $const_method_iteration,
      `rework_iteration` = $rework_iteration,
      `add_on_iteration` = $add_on_iteration,
      `warranty_iteration` = $warranty_iteration,
      `payment_deposit` = $payment_deposit,
      `payment_final` = $payment_final,
      `payment_del_ptl` = $payment_del_ptl,
      `dealer_pw` = '{$from_room['dealer_pw']}',
      `dealer_created` = $dealer_created,
      `quote_submission` = $quote_submission,
      `custom_vin_info` = '{$from_room['custom_vin_info']}',
      `ship_address` = '{$from_room['ship_address']}',
      `ship_city` = '{$from_room['ship_city']}',
      `ship_cubes` = {$from_room['ship_cubes']},
      `ship_name` = '{$from_room['ship_name']}',
      `ship_state` = '{$from_room['ship_state']}',
      `ship_zip` = '{$from_room['ship_zip']}'
    WHERE id = $copy_to_room")) {
      echo displayToast('success', 'Successfully copied the room information.', 'Room Info Copied');
    } else {
      dbLogSQLErr($dbconn);
    }

    if($copy_to_list_qry->num_rows === 1) {
      $copy_to_list = $copy_to_list_qry->fetch_assoc();

      $stmt = $dbconn->prepare('UPDATE pricing_cabinet_list SET cabinet_list = ? WHERE id = ?');
      $stmt->bind_param('si', $from_room_list['cabinet_list'], $copy_to_list['id']);

//      if($dbconn->query("UPDATE pricing_cabinet_list SET cabinet_list = '$fromList' WHERE id = {$copy_to_list['id']}")) {
      if($stmt->execute()) {
        echo displayToast('success', 'Successfully updated the cabinet list.', 'Cabinet List Updated');
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
//      if($dbconn->query("INSERT INTO pricing_cabinet_list (room_id, user_id, catalog_id, cabinet_list) VALUES ({$copy_to_room}, {$_SESSION['userInfo']['id']}, 1, '$fromList')")) {
      $stmt = $dbconn->prepare('INSERT INTO pricing_cabinet_list (room_id, user_id, catalog_id, cabinet_list) VALUES (?, ?, 1, ?)');
      $stmt->bind_param('iis', $copy_to_room, $_SESSION['userInfo']['id'], $from_room_list['cabinet_list']);

      if($stmt->execute()) {
        echo displayToast('success', 'Successfully created the cabinet list.', 'Cabinet List Created');
      } else {
        dbLogSQLErr($dbconn);
      }
    }

    break;
  case 'modalOverrideShipCost':
    $room_id = sanitizeInput($_REQUEST['roomID']);

    echo <<<HEREDOC
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">??</button>
          <h4 class="modal-title">Override Shipping Cost</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <form id="modalShippingOverrideCost" action="#">
                <table style="width:50%;margin:0 auto;">
                  <colgroup>
                    <col width="30%">
                    <col width="70%">
                  </colgroup>
                  <tr>
                    <td>New Cost:</td>
                    <td><input type="text" class="form-control" name="new_cost" placeholder="Cost"></td>
                  </tr>
                </table>
                
                <input type="hidden" id="roomID" name="roomID" value="$room_id" />
              </form>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary waves-effect waves-light" id="modalShippingCostOverride">Override</button>
        </div>
      </div>
    </div>
HEREDOC;

    break;
  case 'shipCostOverride':
    $room_id = sanitizeInput($_REQUEST['roomID']);
    parse_str($_REQUEST['info'], $info);

    $cost = preg_replace('/[^0-9.]/', '', $info['new_cost']);

    if($dbconn->query("UPDATE rooms SET ship_cost = $cost WHERE id = $room_id;")) {
      http_response_code(200);

      updateAuditLog("Overrode shipping cost to $cost", $room_id);

      echo $cost;
    } else {
      http_response_code(400);
      dbLogSQLErr($dbconn);
    }

    break;
  case 'overrideProductionLock':
    $room_id = sanitizeInput($_REQUEST['roomID']);

    updateAuditLog('Over-rode production lock.', $room_id);

    break;
}