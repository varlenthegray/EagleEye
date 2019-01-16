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
$vin_qry = $dbconn->query("SELECT * FROM vin_schema ORDER BY FIELD(`value`, 'Custom/Other', 'TBD', 'N/A', 'Completed', 'Job', 'Quote', 'Lost') DESC, segment, `key` ASC");

while($vin = $vin_qry->fetch_assoc()) {
  $vin_schema[$vin['segment']][$vin['key']] = $vin['value'];
}

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

if($room_qry = $dbconn->query("SELECT id, so_parent, room, iteration, product_type, order_status, days_to_ship, room_name, sales_marketing_bracket, sales_marketing_published,
      shop_bracket, preproduction_bracket, preproduction_published, press_bracket, press_published, paint_bracket, paint_published, custom_bracket, 
      custom_published, shipping_bracket, shipping_published, assembly_published, assembly_bracket, welding_bracket, welding_published
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
    $output[$i]['order_status'] = $vin_schema['order_status']['$'];
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
    $output[$i]['sales_marketing_bracket'] = !empty($operations[$room['sales_marketing_bracket']]) ? $operations[$room['sales_marketing_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['shop_bracket'] = !empty($operations[$room['shop_bracket']]) ? $operations[$room['shop_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['preproduction_bracket'] = !empty($operations[$room['preproduction_bracket']]) ? $operations[$room['preproduction_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['press_bracket'] = !empty($operations[$room['press_bracket']]) ? $operations[$room['press_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['paint_bracket'] = !empty($operations[$room['paint_bracket']]) ? $operations[$room['paint_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['custom_bracket'] = !empty($operations[$room['custom_bracket']]) ? $operations[$room['custom_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['shipping_bracket'] = !empty($operations[$room['shipping_bracket']]) ? $operations[$room['shipping_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['assembly_bracket'] = !empty($operations[$room['assembly_bracket']]) ? $operations[$room['assembly_bracket']] : array('job_title' => 'Unassigned');
    $output[$i]['welding_bracket'] = !empty($operations[$room['welding_bracket']]) ? $operations[$room['welding_bracket']] : array('job_title' => 'Unassigned');
    //</editor-fold>

    //<editor-fold desc="Determining if published or not">
    $output[$i]['sales_marketing_bracket']['published'] = (bool)$room['sales_marketing_published'];
    $output[$i]['shop_bracket']['published'] = (bool)$room['sample_published'];
    $output[$i]['preproduction_bracket']['published'] = (bool)$room['preproduction_published'];
    $output[$i]['press_bracket']['published'] = (bool)$room['press_published'];
    $output[$i]['paint_bracket']['published'] = (bool)$room['paint_published'];
    $output[$i]['custom_bracket']['published'] = (bool)$room['custom_published'];
    $output[$i]['shipping_bracket']['published'] = (bool)$room['shipping_published'];
    $output[$i]['assembly_bracket']['published'] = (bool)$room['assembly_published'];
    $output[$i]['welding_bracket']['published'] = (bool)$room['welding_published'];
    //</editor-fold>

    $i++;
  }
}

if($strip) {
  echo strip_tags(json_encode($output));
} else {
  echo json_encode($output);
}
