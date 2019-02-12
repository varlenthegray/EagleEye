<?php
require '../../../includes/header_start.php';
require '../php/catalog.php';

//outputPHPErrs();

use catalog\catalog as Catalog;

$cat = new Catalog;

function uploadImage($image, $image_name) {
  if($image['error'] !== 4) {
    $upload = true; // by default, we should upload

    // must be less than 1.5MB
    if ($image['size'] > 1500000) {
      $upload = false;
      http_response_code(400);

      echo displayToast('error', 'Image is too large, must be less than 1.5MB.', 'Image Too Large');
    }

    $file_type_upload = array('image/gif', 'image/png', 'image/jpg', 'image/jpeg');

    if(!in_array($_FILES['image']['type'], $file_type_upload, true)) {
      $upload = false;
      http_response_code(400);

      echo displayToast('error', 'Image must be JPG, PNG, JPEG or GIF.', 'Image Extension Mismatch');
    }

    if($upload) {
      $upload_result = move_uploaded_file($image['tmp_name'], "../images/uploaded/{$image_name}");

      if ((int)$upload_result !== 0 && (int)$upload_result !== 1) {
        echo displayToast('error', 'Unable to upload file. Contact IT.', 'File Not Uploaded');
        echo "<script>console.log('ERROR: $upload_result');</script>";
        return null;
      }

      return "uploaded/{$image_name}";
    }
  }
}

switch($_REQUEST['action']) {
  case 'getItemInfo':
    $id = sanitizeInput($_REQUEST['id']);
    $room_id = sanitizeInput($_REQUEST['room_id']);

    $item_qry = $dbconn->query("SELECT 
      pn.sku, pn.category_id, pn.width, pn.height, pn.depth, pn.id, catalog.name AS catalog, detail.image_path AS image, 
      detail.title, detail.description, pn.sqft, pn.linft, pn.cabinet, pn.addl_markup, pn.fixed_price, pn.kit_id, pn.desc_available,
       detail.image_perspective, detail.image_plan, detail.image_side
    FROM pricing_nomenclature pn
      LEFT JOIN pricing_catalog catalog on pn.catalog_id = catalog.id
      LEFT JOIN pricing_nomenclature_details detail on pn.description_id = detail.id
      LEFT JOIN pricing_categories pc on pn.category_id = pc.id
    WHERE pn.id = $id");

    $price = 'N/A';
    $img = null;

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
      if(!empty($item['image'])) {
        $img = "/html/pricing/images/{$item['image']}";
      } else {
        if(!empty($item['image_perspective'])) {
          $img = "/html/pricing/images/{$item['image_perspective']}";
        } else {
          $img = 'fa fa-magic';
        }
      }

      $item['icon'] = $img;

      $description = null;

      $desc_available = json_decode($item['desc_available']);

      foreach($desc_available AS $desc_get) {
        $desc_qry = $dbconn->query("SELECT * FROM pricing_categories pc RIGHT JOIN pricing_nomenclature_details pnd on pc.description_id = pnd.id WHERE pc.id = $desc_get");
        $desc = $desc_qry->fetch_assoc();

        if(!empty($desc['description'])) {
          $description .= nl2br($desc['description']) . '<hr />';
        }
      }

      if(!empty($item['description'])) {
        $description .= nl2br($item['description']);
      } else {
        $description = rtrim($description, '<hr />');
      }

      $item['description'] = $description;
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

    $pg = [];

    $name = sanitizeInput($_REQUEST['name']);
    $description = trim(sanitizeInput($_REQUEST['description']));
    $sku = sanitizeInput($_REQUEST['sku']);
    $width = sanitizeInput($_REQUEST['width']);
    $height = sanitizeInput($_REQUEST['height']);
    $depth = sanitizeInput($_REQUEST['depth']);
    $default_hinge = sanitizeInput($_REQUEST['default_hinge']);
    $image_type = sanitizeInput($_REQUEST['image_type']);
    $recent_image = sanitizeInput($_REQUEST['recent_image']);
    $drawer_box_count = sanitizeInput($_REQUEST['drawer_box_count']);

    for($i = 1; $i <= 14; $i++) {
      $pg[$i] = sanitizeInput($_REQUEST['pg'. $i]);
    }

    $description_id = sanitizeInput($_REQUEST['image_description_id']);

    $desc_enabled = $_REQUEST['desc_enabled'];

    foreach($desc_enabled AS $key => $value) {
      $desc_enabled[$key] = sanitizeInput($value);
    }

    $desc_enabled = json_encode($desc_enabled);

    $hinge_available = $_REQUEST['hinge_available'];

    foreach($hinge_available AS $key => $value) {
      $hinge_available[$key] = sanitizeInput($value);
    }

    $hinge_available = json_encode($hinge_available);

    if($folder === 'true') {
      $nom_qry = $dbconn->query("SELECT pc.*, pnd.description FROM pricing_categories pc LEFT JOIN pricing_nomenclature_details pnd on pc.description_id = pnd.id WHERE pc.id = $id");
    } else {
      $nom_qry = $dbconn->query("SELECT pn.*, pnd.description, pnd.group_desc, pnd.image_perspective, pnd.image_side, pnd.image_plan, pnd.image_path
      FROM pricing_nomenclature pn LEFT JOIN pricing_nomenclature_details pnd on pn.description_id = pnd.id WHERE pn.id = $id");
    }

    $nom = $nom_qry->fetch_assoc();

    if(!$nom['group_desc'] && !empty($nom['description_id'])) { // if it's not a group description AND there is an existing description
      $image_path = $nom['image_path'];
      $perspective_image = $nom['image_perspective'];
      $plan_image = $nom['image_plan'];
      $side_image = $nom['image_side'];
    }

    switch($image_type) {
      case 'new':
        $perspective_image = uploadImage($_FILES['perspective_image'], "{$sku}_perspective");
        $plan_image = uploadImage($_FILES['plan_image'], "{$sku}_plan");
        $side_image = uploadImage($_FILES['side_image'], "{$sku}_side");
        $wtfever = uploadImage($_FILES['image'], $_FILES['image']['name']);

        break;
      case 'library':
        break;
      default:
        $image_path = $nom['image_path'];
        $perspective_image = $nom['image_perspective'];
        $plan_image = $nom['image_plan'];
        $side_image = $nom['image_side'];

        break;
    }

    if(!empty($nom['description_id']) && !$nom['group_desc']) {
      $dbconn->query("UPDATE pricing_nomenclature_details SET description = '$description', title = '$name', image_path = '$image_path', image_perspective = '$perspective_image',
      image_plan = '$plan_image', image_side = '$side_image'  WHERE id = $description_id");
    } else {
      if(!empty(trim($description))) {
        if($dbconn->query("INSERT INTO pricing_nomenclature_details (description, title, image_path, image_perspective, image_plan, image_side) VALUES ('$description', '$name', 
        '$image_path', '$perspective_image', '$plan_image', '$side_image')")) {
          $description_id = $dbconn->insert_id;
        } else {
          dbLogSQLErr($dbconn);
        }
      }
    }

    if($folder === 'true') {
      if($dbconn->query("UPDATE pricing_categories SET name = '$name', description_id = $description_id WHERE id = $id")) {
        http_response_code(200);
        echo displayToast('success', 'Successfully updated category.', "Updated $name");
      } else {
        http_response_code(400);
        dbLogSQLErr($dbconn);
      }
    } else {
      // now we're working on an item
      if($dbconn->query("UPDATE pricing_nomenclature SET sku = '$sku', width = '$width', height = '$height', depth = '$depth', default_hinge = '$default_hinge',
      hinge = '$hinge_available', description_id = $description_id, drawer_box_count = $drawer_box_count, desc_available = '$desc_enabled' WHERE id = $id")) {
        $price_group_qry = $dbconn->query("SELECT * FROM pricing_price_map WHERE nomenclature_id = $id;");

        if($price_group_qry->num_rows > 0) {
          foreach($pg AS $key => $value) {
            $dbconn->query("UPDATE pricing_price_map SET price = $value WHERE nomenclature_id = $id AND price_group_id = $key");
          }
        } else {
          foreach($pg AS $key => $value) {
            $dbconn->query("INSERT INTO pricing_price_map (price_group_id, nomenclature_id, price) VALUES ($key, $id, $value);");
          }
        }

        http_response_code(200);
        echo displayToast('success', 'Successfully updated item.', "Updated $sku");
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

    $pg = [];

    $name = sanitizeInput($_REQUEST['name']);
    $description = trim(sanitizeInput($_REQUEST['description']));
    $sku = sanitizeInput($_REQUEST['sku']);
    $width = sanitizeInput($_REQUEST['width']);
    $height = sanitizeInput($_REQUEST['height']);
    $depth = sanitizeInput($_REQUEST['depth']);
    $default_hinge = sanitizeInput($_REQUEST['default_hinge']);
    $image_type = sanitizeInput($_REQUEST['image_type']);
    $recent_image = sanitizeInput($_REQUEST['recent_image']);
    $drawer_box_count = sanitizeInput($_REQUEST['drawer_box_count']);

    for($i = 1; $i <= 14; $i++) {
      $pg[$i] = sanitizeInput($_REQUEST['pg'. $i]);
    }

    $hinge_available = $_REQUEST['hinge_available'];

    foreach($hinge_available AS $key => $value) {
      $hinge_available[$key] = sanitizeInput($value);
    }

    $hinge_available = json_encode($hinge_available);

    // if it's a new image, we're going to upload a file
    if($image_type === 'new') {
      //<editor-fold desc="Image Upload">
      $upload = true; // by default, we should upload

      $file_name = basename($_FILES['image']['name']);

      $target_file = "../images/uploaded/$file_name";
      $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

      $img_check = getimagesize($_FILES['image']['tmp_name']);

      if($img_check === false) {
        $upload = false; // it's not an image, we're not uploading
        http_response_code(400);

        echo displayToast('error', 'File attachment is not an image.', 'File Not Image');
      } else { // it is an image
        if(file_exists($target_file)) {
          $upload = false;
          http_response_code(400);

          echo displayToast('error', 'Image name already exists.', 'Image Name Exists');
        }

        // must be less than 1MB
        if ($_FILES['image']['size'] > 1000000) {
          $upload = false;
          http_response_code(400);

          echo displayToast('error', 'Image is too large, must be less than 1MB.', 'Image Too Large');
        }

        if($file_type !== 'jpg' && $file_type !== 'png' && $file_type !== 'jpeg' && $file_type !== 'gif') {
          $upload = false;
          http_response_code(400);

          echo displayToast('error', 'Image must be JPG, PNG, JPEG or GIF.', 'Image Extension Mismatch');
        }

        // confusing, but if we're able to upload AND there's no upload error, don't display anything (we're using the response of "ID" in JQuery)
        if($upload && !move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
          http_response_code(400);
          echo displayToast('error', 'Unable to upload file. Contact IT.', 'File Not Uploaded');
        }
      }

      $image_path = "uploaded/$file_name";
      //</editor-fold>
    } // TODO: Implement recent/library images

    $parentID = null;
    $result = array();

    $dbconn->query("INSERT INTO pricing_nomenclature_details (description, title, image_path) VALUES ('$description', '$name', '$image_path');");
    $description_id = $dbconn->insert_id;

    // time to determine if the description has been created or not
    if($folder === 'true') { // if we're creating a folder
      //<editor-fold desc="Determine what the max sort order is for the category">
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
      //</editor-fold>

      if($dbconn->query("INSERT INTO pricing_categories (catalog_id, description_id, name, parent, enabled, sort_order) VALUES (1, $description_id, '$name', $parentID, 1, $sort_order)")) {
        http_response_code(200);
        echo $dbconn->insert_id;
      } else {
        http_response_code(400);
        dbLogSQLErr($dbconn);
      }
    } else {
      $width = !empty($width) ? $width : 0;
      $height = !empty($height) ? $height : 0;
      $depth = !empty($depth) ? $depth : 0;

      // now we're working on an item
      if($dbconn->query("INSERT INTO pricing_nomenclature (catalog_id, category_id, description_id, sku, width, height, depth, default_hinge, hinge, modification, 
      cabinet, sqft, linft, fixed_price, percent, drawer_box_count) VALUES (1, $id, $description_id, '$sku', $width, $height, $depth, '$default_hinge', '$hinge_available', 
      0, 1, 0, 0, 0, 0, $drawer_box_count)")) {
        $item_id = $dbconn->insert_id;

        foreach($pg AS $key => $value) {
          $dbconn->query("INSERT INTO pricing_price_map (price_group_id, nomenclature_id, price) VALUES ($key, $item_id, $value);");
        }

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
  case 'delete':
    $type = sanitizeInput($_REQUEST['type']);
    $id = sanitizeInput($_REQUEST['key']);

    if($type === 'folder') {
      $cat_qry = $dbconn->query("SELECT * FROM pricing_categories WHERE id = $id");

      if($cat_qry->num_rows > 0) {
        // https://stackoverflow.com/questions/28363893/mysql-select-recursive-get-all-child-with-multiple-level/28366310
        $subcat_qry = $dbconn->query("SELECT GROUP_CONCAT(lv SEPARATOR ',') AS subcategories FROM
        (SELECT @pv:=(SELECT GROUP_CONCAT(id SEPARATOR ',') FROM pricing_categories WHERE parent IN (@pv))
        AS lv FROM pricing_categories JOIN
        (SELECT @pv:=$id)tmp WHERE parent IN (@pv)) a;");

        // get all subcategories separated by commas
        $subcat = $subcat_qry->fetch_assoc();

        // get items related to the current ID
        $item_qry = $dbconn->query("SELECT id FROM pricing_nomenclature WHERE category_id = $id");

        // for all of those items, remove them
        while($item = $item_qry->fetch_assoc()) {
          $dbconn->query("DELETE FROM pricing_nomenclature WHERE id = {$item['id']}");
        }

        // delete the category that has the current ID
        $dbconn->query("DELETE FROM pricing_categories WHERE id = $id");

        // if there are sub-categories
        if(!empty($subcat['subcategories'])) {
          // explode out the delimited list for working with
          $subcat_pending_delete = explode(',', $subcat['subcategories']);

          // for every item in the list
          foreach($subcat_pending_delete AS $cat_id) {
            // delete the category
            $dbconn->query("DELETE FROM pricing_categories WHERE id = $cat_id");

            // find the items that are inside of that category
            $item_qry = $dbconn->query("SELECT id FROM pricing_nomenclature WHERE category_id = $cat_id");

            // for each item inside of that category
            while($item = $item_qry->fetch_assoc()) {
              // delete that item
              $dbconn->query("DELETE FROM pricing_nomenclature WHERE id = {$item['id']}");
            }
          }

          echo displayToast('success', 'Successfully deleted folders and items.', 'Folders and Items Deleted');
        } else {
          echo displayToast('success', 'Successfully deleted folder.', 'Folder Deleted');
        }
      } else {
        echo displayToast('error', 'Category does not exist.', 'Does not exist');
      }
    } else {
      $dbconn->query("DELETE FROM pricing_nomenclature WHERE id = $id");

      echo displayToast('success', 'Item has been deleted.', 'Item Deleted');
    }

    break;
}