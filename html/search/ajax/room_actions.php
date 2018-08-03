<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 8/1/2018
 * Time: 4:31 PM
 */

require '../../../includes/header_start.php';

switch($_REQUEST['action']) {
  case 'add_new_room':
    parse_str($_REQUEST['data'], $data);

    // Data capture
    $room_letter = sanitizeInput($data['room_letter']);
    $room_name = sanitizeInput($data['room_name']);
    $iteration = sanitizeInput($data['iteration']);
    $product_type = sanitizeInput($data['product_type']);
    $order_status = sanitizeInput($data['order_status']);
    $days_to_ship = sanitizeInput($data['days_to_ship']);
    $room_type = sanitizeInput($data['room_type']);
    $so_num = sanitizeInput($data['add_room_so_num']);

    // obtain the default bracket to set this up under
    if($bracket_qry = $dbconn->query("SELECT * FROM default_bracket WHERE product_type = '$product_type' AND room_type = '$room_type'")) {
      $bracket = $bracket_qry->fetch_assoc();

      if($so_qry = $dbconn->query("SELECT id FROM sales_order WHERE so_num = '$so_num'")) {
        $so = $so_qry->fetch_assoc();

        // now it's time to create a brand new room with the above information
        if($dbconn->query("INSERT INTO rooms 
        (so_parent, so_id, room, room_name, iteration, product_type, order_status, days_to_ship, room_type, sales_bracket, sales_published, preproduction_bracket, preproduction_published,
        sample_bracket, sample_published, doordrawer_bracket, doordrawer_published, custom_bracket, custom_published, main_bracket, main_published, shipping_bracket,
        shipping_published, install_bracket, install_bracket_published, pick_materials_bracket, pick_materials_published, edgebanding_bracket, edgebanding_published, 
        individual_bracket_buildout) VALUES ('$so_num', '{$so['id']}', '$room_letter', '$room_name', '$iteration', '$product_type', '$order_status', '$days_to_ship', 
        '$room_type', '{$bracket['sales_bracket']}', '{$bracket['sales_published']}', '{$bracket['preproduction_bracket']}', '{$bracket['preproduction_published']}', 
        '{$bracket['sample_bracket']}', '{$bracket['sample_published']}', '{$bracket['doordrawer_bracket']}', '{$bracket['doordrawer_published']}', 
        '{$bracket['custom_bracket']}', '{$bracket['custom_published']}', '{$bracket['main_bracket']}', '{$bracket['main_published']}', '{$bracket['shipping_bracket']}', 
        '{$bracket['shipping_published']}', '{$bracket['install_bracket']}', '{$bracket['install_bracket_published']}', '{$bracket['pick_materials_bracket']}', 
        '{$bracket['pick_materials_published']}', '{$bracket['edgebanding_bracket']}', '{$bracket['edgebanding_published']}', '{$bracket['bracket']}')")) {
          echo displayToast('success', 'Created room successfully.', 'Room Created');
        } else {
          dbLogSQLErr($dbconn);
        }
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
      echo displayToast('error', 'Unable to find a default bracket.', 'No Default Bracket');
    }

    break;
}