<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 8/2/2018
 * Time: 4:47 PM
 */
require_once '../../../includes/header_start.php';

$strip = (bool)$_REQUEST['strip']; // if we're trying to view in a browser with JSON data, remove HTML info

//outputPHPErrs();

// obtain the VIN database table and commit to memory for this query (MAJOR reduction in DB query count)
$vin_schema = getVINSchema();

$soID = sanitizeInput($_REQUEST['so_id']); // get the SO ID
$output = []; // final output
$operations = []; // operation information
$i = 0; // increment for displaying in order

// get all operations
$op_qry = $dbconn->query('SELECT * FROM operations');

while($op = $op_qry->fetch_assoc()) {
  $operations[$op['id']] = $op;
}

$prev_room = null;
$prev_sequence = null;

if($room_qry = $dbconn->query("SELECT id, so_parent, room, iteration, product_type, order_status, days_to_ship, room_name, sales_bracket, sales_published,
      sample_bracket, preproduction_bracket, preproduction_published, doordrawer_bracket, doordrawer_published, main_bracket, main_published, custom_bracket, 
      custom_published, shipping_bracket, shipping_published, install_bracket_published, install_bracket, pick_materials_bracket, pick_materials_published, 
      edgebanding_bracket, edgebanding_published
    FROM rooms WHERE so_parent = '$soID' ORDER BY room, iteration ASC")) {
  while($room = $room_qry->fetch_assoc()) {
    $iteration = number_format($room['iteration'], 2);

    //<editor-fold desc="Button Definitions">
    $edit_btn = "<button class=\"btn waves-effect btn-primary edit_room\" id=\"{$room['id']}\" data-sonum=\"{$room['so_parent']}\"><i class=\"zmdi zmdi-edit\"></i></button>";
    $attachment_btn = "<button class=\"btn waves-effect btn_secondary disabled\" id=\"show_attachments_room_{$room['id']}\"><i class=\"zmdi zmdi-attachment-alt\"></i></button>";
    $sequence_btn = "<button class=\"btn btn-primary-outline waves-effect add_iteration\" data-roomid=\"{$room['id']}\" data-sonum=\"{$room['so_parent']}\" data-addto=\"sequence\" data-iteration=\"{$room['iteration']}\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"Add additional sequence\" style=\"font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;visibility:visible;\"> S +1</button>";
    $sequence_btn_hidden = "<button class=\"btn btn-primary-outline waves-effect add_iteration\" data-roomid=\"{$room['id']}\" data-sonum=\"{$room['so_parent']}\" data-addto=\"sequence\" data-iteration=\"{$room['iteration']}\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"Add additional sequence\" style=\"font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;visibility:hidden;\"> S +1</button>";
    $iteration_btn = "<button class=\"btn btn-primary-outline waves-effect add_iteration\" data-roomid=\"{$room['id']}\" data-sonum=\"{$room['so_parent']}\" data-addto=\"iteration\" data-iteration=\"{$room['iteration']}\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"Add additional iteration\" style=\"font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;visibility:visible;\"> I +.01</button>";
    $iteration_btn_hidden = "<button class=\"btn btn-primary-outline waves-effect add_iteration\" data-roomid=\"{$room['id']}\" data-sonum=\"{$room['so_parent']}\" data-addto=\"iteration\" data-iteration=\"{$room['iteration']}\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"Add additional iteration\" style=\"font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;visibility:hidden;\"> I +.01</button>";
    //</editor-fold>

    //<editor-fold desc="Room details">
    $output[$i]['id'] = $room['id'];
    $output[$i]['room'] = $room['room'];
    $output[$i]['iteration'] = $iteration;
    $output[$i]['product_type'] = $room['product_type'];
    $output[$i]['order_status'] = $vin_schema['order_status'][$room['order_status']];
    $output[$i]['days_to_ship'] = $room['days_to_ship'];
    $output[$i]['room_name'] = $room['room_name'];
    //</editor-fold>

    $seq_it = explode('.', $iteration);

    //<editor-fold desc="Remove duplication of display data and determine buttons to display">
    if($prev_room !== $room['room']) {
      $prev_room = $room['room'];
      $prev_sequence = $seq_it[0];
      $output[$i]['room_details'] = "{$room['room']}{$room['iteration']}: {$room['room_name']}";
      $output[$i]['room_actions'] = "$edit_btn $attachment_btn $sequence_btn $iteration_btn";
    } else {
      if($prev_sequence !== $seq_it[0]) {
        $prev_sequence = $seq_it[0];
        $output[$i]['room_details'] = "&nbsp;&nbsp;{$room['iteration']}: {$room['room_name']}";
        $output[$i]['room_actions'] = "$edit_btn $attachment_btn $sequence_btn_hidden $iteration_btn";
      } else {
        $output[$i]['room_details'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.{$seq_it[1]}: {$room['room_name']}";
        $output[$i]['room_actions'] = "$edit_btn $attachment_btn $sequence_btn_hidden $iteration_btn_hidden";
      }
    }
    //</editor-fold>

    //<editor-fold desc="Bracket information">
    $output[$i]['sales_bracket'] = !empty($operations[$room['sales_bracket']]) ? $operations[$room['sales_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['sample_bracket'] = !empty($operations[$room['sample_bracket']]) ? $operations[$room['sample_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['preproduction_bracket'] = !empty($operations[$room['preproduction_bracket']]) ? $operations[$room['preproduction_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['doordrawer_bracket'] = !empty($operations[$room['doordrawer_bracket']]) ? $operations[$room['doordrawer_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['main_bracket'] = !empty($operations[$room['main_bracket']]) ? $operations[$room['main_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['custom_bracket'] = !empty($operations[$room['custom_bracket']]) ? $operations[$room['custom_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['shipping_bracket'] = !empty($operations[$room['shipping_bracket']]) ? $operations[$room['shipping_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['install_bracket'] = !empty($operations[$room['install_bracket']]) ? $operations[$room['install_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['pick_materials_bracket'] = !empty($operations[$room['pick_materials_bracket']]) ? $operations[$room['pick_materials_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['edgebanding_bracket'] = !empty($operations[$room['edgebanding_bracket']]) ? $operations[$room['edgebanding_bracket']] : array('job_title' => 'Unassigned');
    //</editor-fold>

    //<editor-fold desc="Determining if published or not">
    $output[$i]['sales_bracket']['published'] = (bool)$room['sales_published'];
    $output[$i]['sample_bracket']['published'] = (bool)$room['sample_published'];
    $output[$i]['preproduction_bracket']['published'] = (bool)$room['preproduction_published'];
    $output[$i]['doordrawer_bracket']['published'] = (bool)$room['doordrawer_published'];
    $output[$i]['main_bracket']['published'] = (bool)$room['main_published'];
    $output[$i]['custom_bracket']['published'] = (bool)$room['custom_published'];
    $output[$i]['shipping_bracket']['published'] = (bool)$room['shipping_published'];
    $output[$i]['install_bracket']['published'] = (bool)$room['install_bracket_published'];
    $output[$i]['pick_materials_bracket']['published'] = (bool)$room['pick_materials_published'];
    $output[$i]['edgebanding_bracket']['published'] = (bool)$room['edgebanding_published'];
    //</editor-fold>

    $i++;
  }
}

if($strip) {
  echo strip_tags(json_encode($output));
} else {
  echo json_encode($output);
}
