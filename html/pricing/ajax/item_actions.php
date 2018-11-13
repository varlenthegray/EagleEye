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
      pn.sku, pn.width, pn.height, pn.depth, pn.id, catalog.name AS catalog, detail.image_path AS image, 
      detail.title, detail.description, pn.sqft, pn.linft, pn.cabinet, pn.addl_markup, pn.fixed_price, pn.kit_id
    FROM pricing_nomenclature pn
      LEFT JOIN pricing_catalog catalog on pn.catalog_id = catalog.id
      LEFT JOIN pricing_nomenclature_details detail on pn.description_id = detail.id
    WHERE pn.id = $id");

    $price = 'N/A';

    if($room_qry = $dbconn->query("SELECT vs1.id AS species_grade_id, vs2.id AS door_design_id FROM rooms r LEFT JOIN vin_schema vs1 ON r.species_grade = vs1.key LEFT JOIN vin_schema vs2 ON r.door_design = vs2.key WHERE r.id = $room_id AND vs1.segment = 'species_grade' AND vs2.segment = 'door_design'")) {
      $room = $room_qry->fetch_assoc();

      //****************************************************************************
      // Calculate price group
      if($room['door_design_id'] !== '1544' && $room['species_grade_id'] !== '11') {
        if($price_group_qry = $dbconn->query("SELECT * FROM pricing_price_group_map WHERE door_style_id = {$room['door_design_id']} AND species_id = {$room['species_grade_id']}")) {
          $price_group = $price_group_qry->fetch_assoc();
          $price_group = $price_group['price_group_id'];

          if($price_qry = $dbconn->query("SELECT price FROM pricing_price_map map WHERE map.price_group_id = $price_group AND map.nomenclature_id = $id;")) {
            $price = $price_qry->fetch_assoc();

            $price = $price['price'];
          }
        }
      } else if((bool)$item['fixed_price']) {
        if($price_qry = $dbconn->query("SELECT price FROM pricing_price_map map WHERE map.price_group_id = 1 AND map.nomenclature_id = $id;")) {
          $price = $price_qry->fetch_assoc();

          $price = $price['price'];
        }
      }
      //****************************************************************************
    }

    $item = $item_qry->num_rows === 1 ? $item_qry->fetch_assoc() : null;

    if(!empty($item_qry)) {
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
  case 'updateItem':
    $id = sanitizeInput($_REQUEST['key']);
    $folder = sanitizeInput($_REQUEST['folder']);
    parse_str(sanitizeInput($_REQUEST['update']), $update);

    if($folder === 'true') {
      if($dbconn->query("UPDATE pricing_categories SET name = '{$update['title']}' WHERE id = $id")) {
        http_response_code(200);
        echo displayToast('success', 'Successfully updated category.', "Updated {$update['title']}");
      } else {
        http_response_code(400);
        dbLogSQLErr($dbconn);
      }
    } else {
      // now we're working on an item
      if($dbconn->query("UPDATE pricing_nomenclature SET sku = '{$update['title']}', width = '{$update['width']}', height = '{$update['height']}', depth = '{$update['depth']}' WHERE id = $id")) {
        http_response_code(200);
        echo displayToast('success', 'Successfully updated item.', "Updated {$update['title']}");
      } else {
        http_response_code(400);
        dbLogSQLErr($dbconn);
      }
    }

    break;
  case 'createItem':
    $id = sanitizeInput($_REQUEST['key']);
    $folder = sanitizeInput($_REQUEST['folder']);
    $folderType = sanitizeInput($_REQUEST['folderType']);
    parse_str(sanitizeInput($_REQUEST['update']), $update);
    $parentID = null;

    $result = array();

    if($folder === 'true') { // if we're creating a folder
      $cat_qry = $dbconn->query("SELECT * FROM pricing_categories WHERE id = $id"); // get the current node's information
      $cat = $cat_qry->fetch_assoc(); // current node

      if($folderType === 'alongside') {
        $sort_qry = $dbconn->query("SELECT MAX(sort_order) AS maxSO FROM pricing_categories WHERE parent = {$cat['parent']}"); // current node's parent max sort order
        $sort = $sort_qry->fetch_assoc(); // max sort order for parent

        $parentID = $cat['parent'];
      } elseif($folderType === 'child') {
        $sort_qry = $dbconn->query("SELECT MAX(sort_order) AS maxSO FROM pricing_categories WHERE parent = $id");
        $sort = $sort_qry->fetch_assoc();

        $parentID = $id;
      }

      $sort_order = $sort['maxSO'] + 1; // add one so that it's now at the bottom

      if($dbconn->query("INSERT INTO pricing_categories (catalog_id, name, parent, enabled, sort_order) VALUES (1, '{$update['title']}', $parentID, 1, $sort_order)")) {
        http_response_code(200);
        echo $dbconn->insert_id;
      } else {
        http_response_code(400);
        dbLogSQLErr($dbconn);
      }
    } else {
      $width = !empty($update['width']) ? $update['width'] : 0;
      $height = !empty($update['height']) ? $update['height'] : 0;
      $depth = !empty($update['depth']) ? $update['depth'] : 0;

      // now we're working on an item
      if($dbconn->query("INSERT INTO pricing_nomenclature (catalog_id, category_id, sku, width, height, depth, modification, cabinet, sqft, linft, fixed_price, percent) VALUES 
      (1, $id, '{$update['title']}', $width, $height, $depth, 0, 1, 0, 0, 0, 0)")) {
        http_response_code(200);

        echo $dbconn->insert_id;
      } else {
        http_response_code(400);
        dbLogSQLErr($dbconn);
      }
    }

    break;
  case 'updateCategoryOrder':
    $order = $_REQUEST['newOrder'];
    $order = json_decode($order);
    $new_parent = sanitizeInput($_REQUEST['parent']);
    $current_cat = sanitizeInput($_REQUEST['curCat']);
    $isFolder = sanitizeInput($_REQUEST['isFolder']);

    if($isFolder === 'true') {
      foreach($order AS $key => $line) {
        $key++;
        $dbconn->query("UPDATE pricing_categories SET sort_order = $key WHERE id = $line");
      }

      $dbconn->query("UPDATE pricing_categories SET parent = $new_parent WHERE id = $current_cat");

      echo displayToast('success', 'Successfully updated the category.', 'Category Updated');
    } else {
      foreach($order AS $key => $line) {
        $key++;
        $dbconn->query("UPDATE pricing_nomenclature SET sort_order = $key WHERE id = $line");
      }

      $dbconn->query("UPDATE pricing_nomenclature SET category_id = $new_parent WHERE id = $current_cat");

      echo displayToast('success', 'Successfully updated the item.', 'Item Updated');
    }

    break;
}