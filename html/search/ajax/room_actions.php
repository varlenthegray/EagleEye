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
    $so_num = sanitizeInput($data['add_room_so_num']);

    // obtain the default bracket to set this up under
    // @FIXME: This has been reduced to just product type
    if($bracket_qry = $dbconn->query("SELECT * FROM default_bracket WHERE product_type = '$product_type'")) {
      $bracket = $bracket_qry->fetch_assoc();

      if($so_qry = $dbconn->query("SELECT id FROM sales_order WHERE so_num = '$so_num'")) {
        $so = $so_qry->fetch_assoc();

        // now it's time to create a brand new room with the above information
        if($dbconn->query("INSERT INTO rooms 
        (so_parent, so_id, room, room_name, iteration, product_type, order_status, days_to_ship, sales_marketing_bracket, sales_marketing_published, preproduction_bracket, preproduction_published,
        shop_bracket, shop_published, press_bracket, press_published, custom_bracket, custom_published, paint_bracket, paint_published, shipping_bracket,
        shipping_published, assembly_bracket, assembly_published, welding_bracket, welding_published, 
        individual_bracket_buildout) VALUES ('$so_num', '{$so['id']}', '$room_letter', '$room_name', '$iteration', '$product_type', '$order_status', '$days_to_ship', 
        '{$bracket['sales_marketing_bracket']}', '{$bracket['sales_marketing_published']}', '{$bracket['preproduction_bracket']}', '{$bracket['preproduction_published']}', 
        '{$bracket['shop_bracket']}', '{$bracket['shop_published']}', '{$bracket['press_bracket']}', '{$bracket['press_published']}', 
        '{$bracket['custom_bracket']}', '{$bracket['custom_published']}', '{$bracket['paint_bracket']}', '{$bracket['paint_published']}', '{$bracket['shipping_bracket']}', 
        '{$bracket['shipping_published']}', '{$bracket['assembly_bracket']}', '{$bracket['assembly_published']}', 
        '{$bracket['welding_bracket']}', '{$bracket['welding_published']}', '{$bracket['bracket']}')")) {
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