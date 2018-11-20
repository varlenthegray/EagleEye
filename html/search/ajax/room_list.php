<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 8/2/2018
 * Time: 4:47 PM
 */
require_once '../../../includes/header_start.php';

//outputPHPErrs();

$soID = sanitizeInput($_REQUEST['so_id']);
$output = [];
$operations = [];
$i = 0;

$op_qry = $dbconn->query('SELECT * FROM operations');

while($op = $op_qry->fetch_assoc()) {
  $operations[$op['id']] = $op;
}

if($room_qry = $dbconn->query("SELECT id, room, iteration, product_type, order_status, days_to_ship, room_name, sales_bracket, sample_bracket, preproduction_bracket,
doordrawer_bracket, main_bracket, custom_bracket, shipping_bracket, install_bracket, pick_materials_bracket, edgebanding_bracket
 FROM rooms WHERE so_parent = '$soID'")) {
  while($room = $room_qry->fetch_assoc()) {
    $output[$i]['id'] = $room['id'];
    $output[$i]['room'] = $room['room'];
    $output[$i]['iteration'] = $room['iteration'];
    $output[$i]['product_type'] = $room['product_type'];
    $output[$i]['order_status'] = $room['order_status'];
    $output[$i]['days_to_ship'] = $room['days_to_ship'];
    $output[$i]['room_name'] = $room['room_name'];


    $output[$i]['sales_bracket'] = !empty($operations[$room['sales_bracket']]) ? $operations[$room['sales_bracket']] : '';
    $output[$i]['sample_bracket'] = !empty($operations[$room['sample_bracket']]) ? $operations[$room['sample_bracket']] : '';
    $output[$i]['preproduction_bracket'] = !empty($operations[$room['preproduction_bracket']]) ? $operations[$room['preproduction_bracket']] : '';
    $output[$i]['doordrawer_bracket'] = !empty($operations[$room['doordrawer_bracket']]) ? $operations[$room['doordrawer_bracket']] : '';
    $output[$i]['main_bracket'] = !empty($operations[$room['main_bracket']]) ? $operations[$room['main_bracket']] : '';
    $output[$i]['custom_bracket'] = !empty($operations[$room['custom_bracket']]) ? $operations[$room['custom_bracket']] : '';
    $output[$i]['shipping_bracket'] = !empty($operations[$room['shipping_bracket']]) ? $operations[$room['shipping_bracket']] : '';
    $output[$i]['install_bracket'] = !empty($operations[$room['install_bracket']]) ? $operations[$room['install_bracket']] : '';
    $output[$i]['pick_materials_bracket'] = !empty($operations[$room['pick_materials_bracket']]) ? $operations[$room['pick_materials_bracket']] : '';
    $output[$i]['edgebanding_bracket'] = !empty($operations[$room['edgebanding_bracket']]) ? $operations[$room['edgebanding_bracket']] : '';

    $i++;
  }
}

echo json_encode($output);