<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 6/26/2018
 * Time: 1:24 PM
 */
require '../../../includes/header_start.php';
require '../../../includes/classes/mail_handler.php';

//outputPHPErrs();

$vin_qry = $dbconn->query("SELECT * FROM vin_schema ORDER BY segment ASC, case `group` when 'Custom' then 1 when 'Other' then 2 else 3 end, `group` ASC,
 FIELD(`value`, 'Custom', 'Other', 'No', 'None') DESC");

while($vin = $vin_qry->fetch_assoc()) {
  $vin_schema[$vin['segment']][] = $vin;
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

function displayVINOpts($segment, $db_col = null, $id = null) {
  global $vin_schema;
  global $room;

  // assigns SEGMENT = VIN Schema column (panel_raise) of which there may be multiple pulled from VIN SCHEMA, DB_COL of which there is only one (panel_raise_sd, stored in ROOMS table)
  $dblookup = !empty($db_col) ? $db_col : $segment;
  $addl_id = !empty($id) ? "id = '$id'" : null; // for duplicate values (panel_raise vs panel_raise_sd)
  $options = null;
  $option_grid = null;

  $prev_header = null;
  $section_head = null;

  $selected = '';

  foreach($vin_schema[$segment] as $value) {
    if(((string)$value['key'] === (string)$room[$dblookup]) && empty($selected)) {
      $selected = $value['value'];
      $selected_img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;
      $sel_key = $value['key'];
    }

    if((bool)$value['visible']) {
      $img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;

      if ($value['group'] !== $prev_header) {
        $section_head = "<div class='header'>{$value['group']}</div>";
        $prev_header = $value['group'];
      } else {
        $section_head = null;
      }

      if(!empty($value['subitems'])) {
        $options .= "$section_head <div class='sub_option_header' data-value='{$value['key']}'>{$value['value']} $img</div>";

        $subitems = json_decode($value['subitems']);
        $option_grid .= "$section_head <div class='grid_element' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img";

        foreach($subitems as $key => $item) {
          $options .= "<div class='option sub_option' data-value='{$key}' data-addl-info='{$value['value']}'>{$item}</div>";
          $option_grid .= "<div class='option sub_option' data-value='{$key}'>{$item}</div>";
        }

        $option_grid .= "</div>";
      } else {
        $options .= "$section_head <div class='option' data-value='{$value['key']}'>{$value['value']} $img</div>";

        $option_grid .= "$section_head <div class='grid_element option' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img</div>";
      }
    }
  }

  $selected = empty($selected) ? 'Not Selected Yet' : $selected;

  $output = "<div class='custom_dropdown' $addl_id>";
  $output .= "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  $output .= "<div class='dropdown_options' data-for='$dblookup'>";
  $output .= "<div class='option_list'>$options</div>";
  $output .= "<div class='option_grid'>$option_grid</div>";
  $output .= "</div><input type='hidden' value='$sel_key' id='{$dblookup}' name='{$dblookup}' /><div class='clearfix'></div></div>";

  return $output;
}

function displayFinishOpts($segment, $db_col = null, $id = null) {
  global $vin_schema;
  global $room;

  // assigns SEGMENT = VIN Schema column (panel_raise) of which there may be multiple pulled from VIN SCHEMA, DB_COL of which there is only one (panel_raise_sd, stored in ROOMS table)
  $dblookup = !empty($db_col) ? $db_col : $segment;
  $addl_id = !empty($id) ? $id : $dblookup; // for duplicate values (panel_raise vs panel_raise_sd)
  $options = null;
  $option_grid = null;

  $prev_header = null;
  $section_head = null;

  $selected = '';

  foreach($vin_schema[$segment] as $value) {
    if(((string)$value['key'] === (string)$room[$dblookup]) && empty($selected)) {
      $selected = $value['value'];
      $selected_img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;
      $sel_key = $value['key'];
    }

    if((bool)$value['visible']) {
      $img = (!empty($value['image'])) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;

      if ($value['group'] !== $prev_header) {
        $section_head = "<div class='header'>{$value['group']}</div>";
        $prev_header = $value['group'];
      } else {
        $section_head = null;
      }

      $options .= "$section_head <div class='option' data-value='{$value['key']}' data-display-text=\"{$value['value']}\">{$value['value']} $img</div>";

      if(!empty($value['imagemap_coords']) && stristr($value['imagemap_coords'], '[')) {
        $multimap = json_decode($value['imagemap_coords']);

        foreach($multimap AS $map) {
          $option_grid .= "<area shape='rect' class='option sub_option' style='display:none;' coords='$map' href='#' onclick='return false;' data-value='{$value['key']}' data-display-text=\"{$value['value']}\" />";
        }
      } else {
        $option_grid .= "<area shape='rect' class='option sub_option' style='display:none;' coords='{$value['imagemap_coords']}' onclick='return false;' href='#' data-value='{$value['key']}' data-display-text=\"{$value['value']}\" />";
      }
    }
  }

  $selected = empty($selected) ? 'Not Selected Yet' : $selected;

  $option_grid = "<img src='/assets/images/sample_display.jpg' width='778' height='800' border='0' usemap='#{$dblookup}_map' style='max-width:800px;max-height:800px;' /><map name='{$dblookup}_map' class='grid_element'>$option_grid</map>";

  echo "<div class='custom_dropdown'>";
  echo "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  echo "<div class='dropdown_options' data-for='$dblookup'>";
  echo "<div class='option_list'>$options</div>";
  echo "<div class='option_grid'>$option_grid</div>";
  echo "</div><input type='hidden' value='$sel_key' id='{$dblookup}' name='{$dblookup}' /><div class='clearfix'></div></div>";

  /*echo "<div class='custom_dropdown' $addl_id>";
  echo "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  echo "<div class='dropdown_options' data-for='$dblookup'>";
  echo "<div class='option_list'>$options</div>";
  echo "<div class='option_grid'>$options_grid</div>";
  echo "</div><input type='hidden' value='$selected' id='$dblookup' name='$dblookup' /><div class='clearfix'></div></div>";*/
}

function displayBracketOpsMgmt($bracket, $room, $individual_bracket) {
  global $dbconn;

  $bracket_def = null;

  switch($bracket) {
    case 'Sales':
      $bracket_def = 'sales_bracket';
      break;

    case 'Sample':
      $bracket_def = 'sample_bracket';
      break;

    case 'Pre-Production':
      $bracket_def = 'preproduction_bracket';
      break;

    case 'Drawer & Doors':
      $bracket_def = 'doordrawer_bracket';
      break;

    case 'Main':
      $bracket_def = 'main_bracket';
      break;

    case 'Custom':
      $bracket_def = 'custom_bracket';
      break;

    case 'Shipping':
      $bracket_def = 'shipping_bracket';
      break;

    case 'Installation':
      $bracket_def = 'install_bracket';
      break;

    case 'Pick & Materials':
      $bracket_def = 'pick_materials_bracket';
      break;

    case 'Edge Banding':
      $bracket_def = 'edgebanding_bracket';
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

switch($_REQUEST['action']) {
  case 'update_room':
    parse_str($_REQUEST['editInfo'], $editInfo);

//    $room_id = sanitizeInput($editInfo['roomid']);

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
                echo displayToast("warning", "Finishing/Sample Note already exists. Please refresh your page and try again.", "Delivery Note Exists");
              }
            }

            $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('Finishing/Sample Note: $notes', 'room_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$room_id')");

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

      $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = $followup_individual");

      if($usr = $usr_qry->fetch_assoc()) {
        $msg_notes = nl2br($notes);
        $msg_notes = str_replace('  ', '&nbsp;&nbsp;', $msg_notes);

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

    //////

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
        carcass_interior_glaze_technique = '$carcass_interior_glaze_technique', drawer_boxes = '$drawer_boxes', drawer_box_mount = '$drawer_box_mount', vin_code = '$vin_final', 
        sample_block_ordered = '$sample_block_ordered', door_only_ordered = '$door_only_ordered', door_drawer_ordered = '$door_drawer_ordered',
        inset_square_ordered = '$inset_square_ordered', inset_beaded_ordered = '$inset_beaded_ordered', custom_vin_info = '$custom_vals' $sample_ordered_date WHERE id = '$room_id'")) {
      echo displayToast('success', "VIN has been updated for SO {$editInfo['sonum']} room {$editInfo['room']} iteration $iteration.", 'VIN Updated');
    } else {
      dbLogSQLErr($dbconn);
    }

    $ops = $_REQUEST['active_ops'];

    $sales_op = empty(sanitizeInput($editInfo['sales_bracket'])) ? 0 : sanitizeInput($editInfo['sales_bracket']);
    $sample_op = empty(sanitizeInput($editInfo['sample_bracket'])) ? 0 : sanitizeInput($editInfo['sample_bracket']);
    $preprod_op = empty(sanitizeInput($editInfo['preproduction_bracket'])) ? 0 : sanitizeInput($editInfo['preproduction_bracket']);
    $doordrawer_op = empty(sanitizeInput($editInfo['doordrawer_bracket'])) ? 0 : sanitizeInput($editInfo['doordrawer_bracket']);
    $main_op = empty(sanitizeInput($editInfo['main_bracket'])) ? 0 : sanitizeInput($editInfo['main_bracket']);
    $custom_op = empty(sanitizeInput($editInfo['custom_bracket'])) ? 0 : sanitizeInput($editInfo['custom_bracket']);
    $shipping_op = empty(sanitizeInput($editInfo['shipping_bracket'])) ? 0 : sanitizeInput($editInfo['shipping_bracket']);
    $install_op = empty(sanitizeInput($editInfo['install_bracket'])) ? 0 : sanitizeInput($editInfo['install_bracket']);
    $pickmat_op = empty(sanitizeInput($editInfo['pick_materials_bracket'])) ? 0 : sanitizeInput($editInfo['pick_materials_bracket']);
    $edgebanding_op = empty(sanitizeInput($editInfo['edgebanding_bracket'])) ? 0 : sanitizeInput($editInfo['edgebanding_bracket']);

    $changed[] = whatChanged($sales_op, $room_info['sales_bracket'], 'Sales Bracket', false, false, true);
    $changed[] = whatChanged($sample_op, $room_info['sample_bracket'], 'Sample Bracket', false, false, true);
    $changed[] = whatChanged($preprod_op, $room_info['preproduction_bracket'], 'Pre-Production Bracket', false, false, true);
    $changed[] = whatChanged($doordrawer_op, $room_info['doordrawer_bracket'], 'Door/Drawer Bracket', false, false, true);
    $changed[] = whatChanged($main_op, $room_info['main_bracket'], 'Main Bracket', false, false, true);
    $changed[] = whatChanged($custom_op, $room_info['custom_bracket'], 'Custom Bracket', false, false, true);
    $changed[] = whatChanged($shipping_op, $room_info['shipping_bracket'], 'Shipping Bracket', false, false, true);
    $changed[] = whatChanged($install_op, $room_info['install_bracket'], 'Install Bracket', false, false, true);
    $changed[] = whatChanged($pickmat_op, $room_info['pick_materials_bracket'], 'Pick & Materials Bracket', false, false, true);
    $changed[] = whatChanged($edgebanding_op, $room_info['edgebanding_bracket'], 'Edge Banding Bracket', false, false, true);

    $sales_pub = !empty($editInfo['sales_published']) ? sanitizeInput($editInfo['sales_published']) : 0;
    $sample_pub = !empty($editInfo['sample_published']) ? sanitizeInput($editInfo['sample_published']) : 0;
    $preprod_pub = !empty($editInfo['preprod_published']) ? sanitizeInput($editInfo['preprod_published']) : 0;
    $doordrawer_pub = !empty($editInfo['doordrawer_published']) ? sanitizeInput($editInfo['doordrawer_published']) : 0;
    $main_pub = !empty($editInfo['main_published']) ? sanitizeInput($editInfo['main_published']) : 0;
    $custom_pub = !empty($editInfo['custom_published']) ? sanitizeInput($editInfo['custom_published']) : 0;
    $shipping_pub = !empty($editInfo['shipping_published']) ? sanitizeInput($editInfo['shipping_published']) : 0;
    $install_pub = !empty($editInfo['install_published']) ? sanitizeInput($editInfo['install_published']) : 0;
    $pickmat_pub = !empty($editInfo['pick_materials_published']) ? sanitizeInput($editInfo['pickmat_published']) : 0;
    $edgebanding_pub = !empty($editInfo['edgebanding_published']) ? sanitizeInput($editInfo['edgebanding_published']) : 0;


    $changed[] = whatChanged($sales_pub, $room_info['sales_published'], 'Sales Bracket', false, true);
    $changed[] = whatChanged($sample_pub, $room_info['sample_published'], 'Sample Bracket', false, true);
    $changed[] = whatChanged($preprod_pub, $room_info['preproduction_published'], 'Pre-Production Bracket', false, true);
    $changed[] = whatChanged($doordrawer_pub, $room_info['doordrawer_published'], 'Door/Drawer Bracket', false, true);
    $changed[] = whatChanged($main_pub, $room_info['main_published'], 'Main Bracket', false, true);
    $changed[] = whatChanged($custom_pub, $room_info['custom_published'], 'Custom Bracket', false, true);
    $changed[] = whatChanged($shipping_pub, $room_info['shipping_published'], 'Shipping Bracke', false, true);
    $changed[] = whatChanged($install_pub, $room_info['install_bracket_published'], 'Install Bracket', false, true);
    $changed[] = whatChanged($pickmat_pub, $room_info['pick_materials_published'], 'Pick & Materials Bracket', false, true);
    $changed[] = whatChanged($edgebanding_pub, $room_info['edgebanding_published'], 'Edgebanding Bracket', false, true);
    $changed[] = whatChanged($ops, $room_info['individual_bracket_buildout'], 'Active Bracket Operations');

    if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$ops' WHERE id = '$room_id'")) {
      $update_result = $dbconn->query("UPDATE rooms SET sales_bracket = '$sales_op', preproduction_bracket = '$preprod_op', sample_bracket = '$sample_op', doordrawer_bracket = '$doordrawer_op', edgebanding_bracket = '$edgebanding_op',
    custom_bracket = '$custom_op', main_bracket = '$main_op', shipping_bracket = '$shipping_op', install_bracket = '$install_op', sales_published = '$sales_pub', sample_published = '$sample_pub',
    preproduction_published = '$preprod_pub', doordrawer_published = '$doordrawer_pub', main_published = '$main_pub', edgebanding_published = '$edgebanding_pub', custom_published = '$custom_pub', shipping_published = '$shipping_pub',
    install_bracket_published = '$install_pub', pick_materials_bracket = '$pickmat_op', pick_materials_published = '$pickmat_pub', payment_deposit = '$deposit_received',
    payment_final = '$final_payment', payment_del_ptl = '$ptl_del' WHERE id = '$room_id'");

      createOpQueue($sales_pub, 'Sales', $sales_op, $room_id);
      createOpQueue($sample_pub, 'Sample', $sample_op, $room_id);
      createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $room_id);
      createOpQueue($doordrawer_pub, 'Drawer & Doors', $doordrawer_op, $room_id);
      createOpQueue($main_pub, 'Main', $main_op, $room_id);
      createOpQueue($custom_pub, 'Custom', $custom_op, $room_id);
      createOpQueue($shipping_pub, 'Shipping', $shipping_op, $room_id);
      createOpQueue($install_pub, 'Installation', $install_op, $room_id);
      createOpQueue($pickmat_pub, 'Pick & Materials', $pickmat_op, $room_id);
      createOpQueue($edgebanding_pub, 'Edge Banding', $edgebanding_op, $room_id);

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
  case 'modalGlobals':
    $room_id = sanitizeInput($_REQUEST['roomID']);

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
    $room = $room_qry->fetch_assoc();

    // run functions for heredoc output
    $product_type = displayVINOpts('product_type', null, 'dropdown_p_type');
    $order_status = displayVINOpts('order_status');
    $days_to_ship = displayVINOpts('days_to_ship');
    // end of function run for heredoc

    // days to ship calculation info
    switch($room['days_to_ship']) {
      case 'G':
        $dd_class = 'job-color-green';
        break;

      case 'Y':
        $dd_class = 'job-color-yellow';
        break;

      case 'N':
        $dd_class = 'job-color-orange';
        break;

      case 'R':
        $dd_class = 'job-color-red';
        break;

      default:
        $dd_class = 'job-color-gray';
        break;
    }

    $dd_value = !empty($room['delivery_date']) ? date('m/d/Y', $room['delivery_date']) : '';
    // end days to ship calculation info

    echo <<<HEREDOC
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title">Global Room Details</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <form id="modalGlobalData" action="#">
                <table style="width:50%;margin:0 auto;">
                  <colgroup>
                    <col width="30%">
                    <col width="70%">
                  </colgroup>
                  <tr>
                    <td><label for="room">Room</label></td>
                    <td>
                      <input type="text" class="form-control" id="room_letter" name="room_letter" placeholder="Room" value="{$room['room']}" style="float:left;width:15%;" readonly>
                      <input type="text" class="form-control" id="room_name" name="room_name" placeholder="Room Name" value="{$room['room_name']}" style="float:left;width:82.5%;margin-left:5px;">
                    </td>
                  </tr>
                  <tr>
                    <td><label for="iteration">Iteration</label></td>
                    <td><input type="text" class="form-control" id="iteration" name="iteration" placeholder="Iteration" value="{$room['iteration']}" readonly></td>
                  </tr>
                  <tr>
                    <td><label for="product_type">Product Type</label></td>
                    <td>$product_type</td>
                  </tr>
                  <tr>
                    <td><label for="order_status">Order Status</label></td>
                    <td>$order_status</td>
                  </tr>
                  <tr>
                    <td><label for="days_to_ship">Days to Ship</label></td>
                    <td>$days_to_ship</td>
                  </tr>
                  <tr>
                    <td><label for="delivery_date">Delivery Date</label></td>
                    <td>
                      <div class="input-group">
                        <input type="text" class="form-control delivery_date $dd_class" name="delivery_date" placeholder="Delivery Date" value="$dd_value">
                        <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                      </div>
                    </td>
                  </tr>
                </table>
                
                <input type="hidden" id="roomID" name="roomID" value="$room_id" />
              </form>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary waves-effect waves-light" id="modalGlobalsUpdate">Update</button>
        </div>
      </div>
    </div>
HEREDOC;


    break;
  case 'updateGlobals':
    parse_str($_REQUEST['globalInfo'], $globalInfo);

    $room_id = sanitizeInput($globalInfo['roomID']);
    $room_letter = sanitizeInput($globalInfo['room_letter']);
    $room_name = sanitizeInput($globalInfo['room_name']);
    $iteration = (double)sanitizeInput($globalInfo['iteration']);
    $product_type = sanitizeInput($globalInfo['product_type']);
    $order_status = sanitizeInput($globalInfo['order_status']);
    $days_to_ship = sanitizeInput($globalInfo['days_to_ship']);
    $delivery_date = $globalInfo['delivery_date'];

    if(empty($delivery_date)) { // this entire thing is stupid but it resolves the del date being blank
      $del_date_unix = 'null'; // this was stupid, i had to set this to a null string and remove quotes from SQL statement
    } else {
      $del_date_unix = strtotime($delivery_date); // this was stupid, i had to configure this to string
    }

    if(!empty($room_id)) {
      if($dbconn->query("UPDATE rooms SET room = '$room_letter', room_name = '$room_name', iteration = '$iteration', product_type = '$product_type', order_status = '$order_status', days_to_ship = '$days_to_ship', delivery_date = '$del_date_unix' WHERE id = $room_id;")) {
        echo displayToast('success', "Successfully updated the global information for room $room_name.", 'Room Updated');
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
      echo displayToast('error', 'Unable to update the information. Please reload the page and try again.', 'Unable to Update');
    }

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
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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

    $sales_published = (bool)$room['sales_published'] ? 'checked' : NULL;
    $sample_published = (bool)$room['sample_published'] ? 'checked' : NULL;

    $sales_bracket = displayBracketOpsMgmt('Sales', $room, $individual_bracket);
    $sample_bracket = displayBracketOpsMgmt('Sample', $room, $individual_bracket);

    $preprod_published = (bool)$room['preproduction_published'] ? 'checked' : NULL;
    $doordrawer_published = (bool)$room['doordrawer_published'] ? 'checked' : NULL;

    $preprod_bracket = displayBracketOpsMgmt('Pre-Production', $room, $individual_bracket);
    $doordrawer_bracket = displayBracketOpsMgmt('Drawer & Doors', $room, $individual_bracket);

    $main_published = (bool)$room['main_published'] ? 'checked' : NULL;
    $edgebanding_published = (bool)$room['edgebanding_published'] ? 'checked' : NULL;

    $main_bracket = displayBracketOpsMgmt('Main', $room, $individual_bracket);
    $edgebanding_bracket = displayBracketOpsMgmt('Edge Banding', $room, $individual_bracket);

    $custom_published = (bool)$room['custom_published'] ? 'checked' : NULL;
    $shipping_published = (bool)$room['shipping_published'] ? 'checked' : NULL;

    $custom_bracket = displayBracketOpsMgmt('Custom', $room, $individual_bracket);
    $shipping_bracket = displayBracketOpsMgmt('Shipping', $room, $individual_bracket);

    $install_published = (bool)$room['install_bracket_published'] ? 'checked' : NULL;
    $pickmat_published = (bool)$room['pick_materials_published'] ? 'checked' : NULL;

    $install_bracket = displayBracketOpsMgmt('Installation', $room, $individual_bracket);
    $pickmat_bracket = displayBracketOpsMgmt('Pick & Materials', $room, $individual_bracket);


    echo /** @lang HTML */
    <<<HEREDOC
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
                        <div class="col-md-8"><h5><label for="sales_bracket">Sales Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sales_published" value="1" id="sales_published" $sales_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                    <td style="background-color:#eceeef;"></td>
                    <td style="width: 49.8%;" class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="sample_bracket">Sample Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sample_published" value="1" id="sample_published" $sample_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom">
                      $sales_bracket
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-bottom">
                      $sample_bracket
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
                        <div class="col-md-8"><h5><label for="door_drawer_bracket">Door/Drawer Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="doordrawer_published" value="1" id="doordrawer_published" $doordrawer_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom">
                      $preprod_bracket
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-bottom">
                      $doordrawer_bracket
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="main_bracket">Main Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="main_published" value="1" id="main_published" $main_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="edgebanding_bracket">Edge Banding Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="edgebanding_published" value="1" id="edgebanding_bracket" $edgebanding_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom">
                      $main_bracket
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-bottom">
                      $edgebanding_bracket
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
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="install_bracket">Install Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="install_published" value="1" id="install_published" $install_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-top">
                      <div class="row bracket-header-custom">
                        <div class="col-md-8"><h5><label for="pickmat_bracket">Pick & Materials Bracket</label></h5></div>
                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="pickmat_published" value="1" id="pickmat_bracket" $pickmat_published> <span class="c-indicator"></span> Published</label> </div>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="bracket-border-bottom">
                      $install_bracket
                    </td>
                    <td style="background-color: #eceeef;">&nbsp;</td>
                    <td class="bracket-border-bottom">
                      $pickmat_bracket
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
    $sales_op = empty(sanitizeInput($bracket_info['sales_bracket'])) ? 0 : sanitizeInput($bracket_info['sales_bracket']);
    $sample_op = empty(sanitizeInput($bracket_info['sample_bracket'])) ? 0 : sanitizeInput($bracket_info['sample_bracket']);
    $preprod_op = empty(sanitizeInput($bracket_info['preproduction_bracket'])) ? 0 : sanitizeInput($bracket_info['preproduction_bracket']);
    $doordrawer_op = empty(sanitizeInput($bracket_info['doordrawer_bracket'])) ? 0 : sanitizeInput($bracket_info['doordrawer_bracket']);
    $main_op = empty(sanitizeInput($bracket_info['main_bracket'])) ? 0 : sanitizeInput($bracket_info['main_bracket']);
    $custom_op = empty(sanitizeInput($bracket_info['custom_bracket'])) ? 0 : sanitizeInput($bracket_info['custom_bracket']);
    $shipping_op = empty(sanitizeInput($bracket_info['shipping_bracket'])) ? 0 : sanitizeInput($bracket_info['shipping_bracket']);
    $install_op = empty(sanitizeInput($bracket_info['install_bracket'])) ? 0 : sanitizeInput($bracket_info['install_bracket']);
    $pickmat_op = empty(sanitizeInput($bracket_info['pick_materials_bracket'])) ? 0 : sanitizeInput($bracket_info['pick_materials_bracket']);
    $edgebanding_op = empty(sanitizeInput($bracket_info['edgebanding_bracket'])) ? 0 : sanitizeInput($bracket_info['edgebanding_bracket']);
    // end of grabbing the current operation for each bracket

    // determine if any of the above (current bracket operation) has changed
    $changed[] = whatChanged($sales_op, $room['sales_bracket'], 'Sales Bracket', false, false, true);
    $changed[] = whatChanged($sample_op, $room['sample_bracket'], 'Sample Bracket', false, false, true);
    $changed[] = whatChanged($preprod_op, $room['preproduction_bracket'], 'Pre-Production Bracket', false, false, true);
    $changed[] = whatChanged($doordrawer_op, $room['doordrawer_bracket'], 'Door/Drawer Bracket', false, false, true);
    $changed[] = whatChanged($main_op, $room['main_bracket'], 'Main Bracket', false, false, true);
    $changed[] = whatChanged($custom_op, $room['custom_bracket'], 'Custom Bracket', false, false, true);
    $changed[] = whatChanged($shipping_op, $room['shipping_bracket'], 'Shipping Bracket', false, false, true);
    $changed[] = whatChanged($install_op, $room['install_bracket'], 'Install Bracket', false, false, true);
    $changed[] = whatChanged($pickmat_op, $room['pick_materials_bracket'], 'Pick & Materials Bracket', false, false, true);
    $changed[] = whatChanged($edgebanding_op, $room['edgebanding_bracket'], 'Edge Banding Bracket', false, false, true);
    // end of the current bracket operation changes

    // grab the status of each bracket publish
    $sales_pub = !empty($bracket_info['sales_published']) ? sanitizeInput($bracket_info['sales_published']) : 0;
    $sample_pub = !empty($bracket_info['sample_published']) ? sanitizeInput($bracket_info['sample_published']) : 0;
    $preprod_pub = !empty($bracket_info['preprod_published']) ? sanitizeInput($bracket_info['preprod_published']) : 0;
    $doordrawer_pub = !empty($bracket_info['doordrawer_published']) ? sanitizeInput($bracket_info['doordrawer_published']) : 0;
    $main_pub = !empty($bracket_info['main_published']) ? sanitizeInput($bracket_info['main_published']) : 0;
    $custom_pub = !empty($bracket_info['custom_published']) ? sanitizeInput($bracket_info['custom_published']) : 0;
    $shipping_pub = !empty($bracket_info['shipping_published']) ? sanitizeInput($bracket_info['shipping_published']) : 0;
    $install_pub = !empty($bracket_info['install_published']) ? sanitizeInput($bracket_info['install_published']) : 0;
    $pickmat_pub = !empty($bracket_info['pick_materials_published']) ? sanitizeInput($bracket_info['pickmat_published']) : 0;
    $edgebanding_pub = !empty($bracket_info['edgebanding_published']) ? sanitizeInput($bracket_info['edgebanding_published']) : 0;
    // end of the status of each bracket publish

    // record the changes for the status of each bracket publish
    $changed[] = whatChanged($sales_pub, $room['sales_published'], 'Sales Bracket', false, true);
    $changed[] = whatChanged($sample_pub, $room['sample_published'], 'Sample Bracket', false, true);
    $changed[] = whatChanged($preprod_pub, $room['preproduction_published'], 'Pre-Production Bracket', false, true);
    $changed[] = whatChanged($doordrawer_pub, $room['doordrawer_published'], 'Door/Drawer Bracket', false, true);
    $changed[] = whatChanged($main_pub, $room['main_published'], 'Main Bracket', false, true);
    $changed[] = whatChanged($custom_pub, $room['custom_published'], 'Custom Bracket', false, true);
    $changed[] = whatChanged($shipping_pub, $room['shipping_published'], 'Shipping Bracke', false, true);
    $changed[] = whatChanged($install_pub, $room['install_bracket_published'], 'Install Bracket', false, true);
    $changed[] = whatChanged($pickmat_pub, $room['pick_materials_published'], 'Pick & Materials Bracket', false, true);
    $changed[] = whatChanged($edgebanding_pub, $room['edgebanding_published'], 'Edgebanding Bracket', false, true);
    $changed[] = whatChanged($ops, $room['individual_bracket_buildout'], 'Active Bracket Operations');
    // end of recording the changes for the status of each bracket publish

    if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$ops' WHERE id = '$room_id'")) {
      if($dbconn->query("UPDATE rooms SET sales_bracket = '$sales_op', preproduction_bracket = '$preprod_op', sample_bracket = '$sample_op', doordrawer_bracket = '$doordrawer_op', edgebanding_bracket = '$edgebanding_op',
      custom_bracket = '$custom_op', main_bracket = '$main_op', shipping_bracket = '$shipping_op', install_bracket = '$install_op', sales_published = '$sales_pub', sample_published = '$sample_pub',
      preproduction_published = '$preprod_pub', doordrawer_published = '$doordrawer_pub', main_published = '$main_pub', edgebanding_published = '$edgebanding_pub', custom_published = '$custom_pub', shipping_published = '$shipping_pub',
      install_bracket_published = '$install_pub', pick_materials_bracket = '$pickmat_op', pick_materials_published = '$pickmat_pub' WHERE id = '$room_id'")) {
        createOpQueue($sales_pub, 'Sales', $sales_op, $room_id);
        createOpQueue($sample_pub, 'Sample', $sample_op, $room_id);
        createOpQueue($preprod_pub, 'Pre-Production', $preprod_op, $room_id);
        createOpQueue($doordrawer_pub, 'Drawer & Doors', $doordrawer_op, $room_id);
        createOpQueue($main_pub, 'Main', $main_op, $room_id);
        createOpQueue($custom_pub, 'Custom', $custom_op, $room_id);
        createOpQueue($shipping_pub, 'Shipping', $shipping_op, $room_id);
        createOpQueue($install_pub, 'Installation', $install_op, $room_id);
        createOpQueue($pickmat_pub, 'Pick & Materials', $pickmat_op, $room_id);
        createOpQueue($edgebanding_pub, 'Edge Banding', $edgebanding_op, $room_id);

        echo displayToast('success', 'The bracket and operations have been updated.', 'Brackets & Operations Updated');
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
}