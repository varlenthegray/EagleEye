<?php
require '../../../includes/header_start.php';

switch($_REQUEST['action']) {
  case 'getItemInfo':
    $id = sanitizeInput($_REQUEST['id']);

    $item_qry = $dbconn->query("SELECT 
      pn.sku, pn.width, pn.height, pn.depth, pn.id, catalog.name AS catalog
    FROM pricing_nomenclature pn
      LEFT JOIN pricing_catalog catalog on pn.catalog_id = catalog.id
    WHERE pn.id = $id");

    if($item_qry->num_rows === 1) {
      $item = $item_qry->fetch_assoc();

      echo json_encode($item, true);
    }

    break;
  case 'saveCatalog':
    $room_id = sanitizeInput($_REQUEST['room_id']);
    $cab_list = sanitizeInput($_REQUEST['cabinet_list']);
    $catalog_id = sanitizeInput($_REQUEST['catalog_id']);

    $existing_qry = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $room_id");

    if($existing_qry->num_rows > 0) {
      $existing = $existing_qry->fetch_assoc();

      $result = $dbconn->query("UPDATE pricing_cabinet_list SET cabinet_list = '$cab_list', catalog_id = $catalog_id WHERE id = {$existing['id']}");
    } else {
      $result = $dbconn->query("INSERT INTO pricing_cabinet_list (room_id, user_id, catalog_id, cabinet_list) VALUES ($room_id, {$_SESSION['shop_user']['id']}, $catalog_id, '$cab_list')");
    }

    if($result) {
      echo displayToast("success", "Successfully updated the cabinet list.", "Cabinet List Updated");
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
  case 'getCabinetList':
    $room_id = sanitizeInput($_REQUEST['room_id']);

    $cabinet_list = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $room_id");

    if($cabinet_list->num_rows > 0) {
      $cabinet_list = $cabinet_list->fetch_assoc();

      echo $cabinet_list['cabinet_list'];
    } else {
      $children['children'][0]['title'] = "No data";
      $children['children'][0]['key'] = "-1";
      $children['children'][0]['qty'] = 0;
      $children['children'][0]['icon'] = "fa fa-ban";


      echo json_encode($children);
    }

    break;
}