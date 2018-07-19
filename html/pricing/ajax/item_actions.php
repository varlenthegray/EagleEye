<?php
require '../../../includes/header_start.php';
require '../php/catalog.php';

//outputPHPErrs();

use catalog\catalog as Catalog;

$cat = new Catalog;

switch($_REQUEST['action']) {
  case 'getItemInfo':
    $id = sanitizeInput($_REQUEST['id']);
    $room_id = sanitizeInput($_REQUEST['room_id']);

    $item_qry = $dbconn->query("SELECT 
      pn.sku, pn.width, pn.height, pn.depth, pn.id, catalog.name AS catalog, detail.image_path AS image, detail.title, detail.description, pn.sqft, pn.linft, pn.cabinet, pn.addl_markup
    FROM pricing_nomenclature pn
      LEFT JOIN pricing_catalog catalog on pn.catalog_id = catalog.id
      LEFT JOIN pricing_nomenclature_details detail on pn.description_id = detail.id
    WHERE pn.id = $id");

    $room_qry = $dbconn->query("SELECT 
      vs1.id AS species_grade_id, vs2.id AS door_design_id
    FROM rooms r 
      LEFT JOIN vin_schema vs1 ON r.species_grade = vs1.key
      LEFT JOIN vin_schema vs2 ON r.door_design = vs2.key
    WHERE r.id = $room_id AND vs1.segment = 'species_grade' AND vs2.segment = 'door_design'");

    $room = $room_qry->fetch_assoc();

    //****************************************************************************
    // Calculate price group
    if($room['door_design_id'] !== '1544' && $room['species_grade_id'] !== '11') {
      $price_group_qry = $dbconn->query("SELECT * FROM pricing_price_group_map WHERE door_style_id = {$room['door_design_id']} AND species_id = {$room['species_grade_id']}");
      $price_group = $price_group_qry->fetch_assoc();
      $price_group = $price_group['price_group_id'];

      if($price_qry = $dbconn->query("SELECT price FROM pricing_price_map map WHERE map.price_group_id = $price_group AND map.nomenclature_id = $id;")) {
        $price = $price_qry->fetch_assoc();

        $price = $price['price'];
      }
    } else {
      $price = 'N/A';
    }
    //****************************************************************************

    if($item_qry->num_rows === 1) {
      $item = $item_qry->fetch_assoc();

      $img = !empty($item['image']) ? "/html/pricing/images/{$item['image']}" : 'fa fa-magic';

      $item['icon'] = $img;

      $item['description'] = nl2br($item['description']);
      $item['price'] = $price;

      echo json_encode($item, true);
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
  case 'submitQuote':
    $room_id = sanitizeInput($_REQUEST['room_id']);
    $cab_list = sanitizeInput($_REQUEST['cabinet_list']);

    $quote_id = $cat->saveCatalog($room_id, $cab_list);

    if($dbconn->query("UPDATE pricing_cabinet_list SET quote_submission = UNIX_TIMESTAMP() WHERE id = $quote_id")) {
      echo displayToast('success', 'Successfully submitted the quote for review!', 'Quote Submitted');
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
}