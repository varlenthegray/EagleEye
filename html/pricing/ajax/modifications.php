<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

// Brandon Christensen 4/14/2018
$result = array();

$itemID = sanitizeInput($_REQUEST['itemID']);
$price_group = sanitizeInput($_REQUEST['priceGroup']);
$mods_accepted = array();

if(!empty($itemID)) {
  $mod_type_qry = $dbconn->query("SELECT mod_type FROM pricing_nomenclature WHERE id = $itemID;");
  $mod_type = $mod_type_qry->fetch_assoc();

  $mod_type = '`' . $mod_type['mod_type'] . '`';

  $item_mod_qry = $dbconn->query("SELECT pn.id FROM pricing_modification_details pmd
    LEFT JOIN pricing_nomenclature pn on pmd.nomenclature_id = pn.id
  WHERE $mod_type IS TRUE;");

  if($item_mod_qry->num_rows > 0) {
    while($item_mod = $item_mod_qry->fetch_assoc()) {
      $mods_accepted[] = $item_mod['id'];
    }
  }
}

$parent_qry = $dbconn->prepare('SELECT
  pc.id AS catID, pn.id AS itemID, name, parent, pc.sort_order, pn.category_id, pn.sku, detail.image_path, detail.title, pn.addl_info, pn.sqft, pn.linft, pn.cabinet, pn.addl_markup, pn.percent
FROM pricing_categories pc
  LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id
  LEFT JOIN pricing_nomenclature_details detail on pn.description_id = detail.id
WHERE parent = ? AND pc.id BETWEEN 15000 AND 16000 ORDER BY parent, pc.sort_order, catID, pn.sku ASC');

function makeTree($parent_id) {
  global $parent_qry;
  global $mods_accepted;
  global $dbconn;
  global $price_group;

  $ret = array();

  $parent_qry->bind_param('i', $parent_id);
  $parent_qry->bind_result($catID, $itemID, $name, $parent, $sort_order, $item_catID, $sku, $image, $sku_title, $addl_info, $sqft, $linft, $cabinet, $addl_markup, $pct_markup);
  $parent_qry->execute();
  $parent_qry->store_result();

  $data = array();

  while ($parent_qry->fetch()) {
    $data[] = array(
      'catID' => $catID,
      'itemID' => $itemID,
      'name' => $name,
      'parent' => $parent,
      'sort_order' => $sort_order,
      'item_catID' => $item_catID,
      'sku' => $sku,
      'image_path' => $image,
      'sku_title' => $sku_title,
      'addl_info' => $addl_info,
      'sqft' => $sqft,
      'linft' => $linft,
      'cabinet' => $cabinet,
      'addlMarkup' => $addl_markup,
      'percentMarkup' => $pct_markup
    );
  }

  $parent_qry->free_result();

  $sku_items = array();

  foreach($data as $item) {
    if(!empty($item['sku'])) {
      if(in_array($item['itemID'], $mods_accepted, false)) {
        if (!isset($sku_items[$item['item_catID']])) {
          $object = array('key' => $item['catID'], 'title' => $item['name'], 'folder' => true, 'children' => array());
          $sku_items[$item['item_catID']] = $object;
          $ret[] = &$sku_items[$item['item_catID']];
        }

        $info = "<span class='actions'><div class='info_container'><i class='fa fa-info-circle primary-color view_item_info' data-id='{$item['itemID']}'></i></div></span>";

        $img = !empty($item['image_path']) ? "/html/pricing/images/{$item['image_path']}" : 'fa fa-magic';

        $price_qry = $dbconn->query("SELECT * FROM pricing_price_map WHERE price_group_id = $price_group AND nomenclature_id = {$item['itemID']}");

        if($price_qry->num_rows > 0) {
          $price = $price_qry->fetch_assoc();

          $sku_items[$item['item_catID']]['children'][] = array(
            'key' => $item['itemID'],
            'title' => $item['sku'],
            'description' => $item['sku_title'],
            'info' => $info,
            'is_item' => true,
            'checkbox' => true,
            'icon' => $img,
            'qty' => 1,
            'price' => $price['price'],
            'addl_info' => $item['addl_info'],
            'sqft' => $item['sqft'],
            'linft' => $item['linft'],
            'cabinet' => $item['cabinet'],
            'addlMarkup' => $item['addlMarkup'],
            'itemID' => $item['itemID'],
            'percentMarkup' => $item['percentMarkup']);
        } else {
          $sku_items[$item['item_catID']]['children'][] = array(
            'key' => $item['itemID'],
            'title' => $item['sku'],
            'description' => $item['sku_title'],
            'info' => $info,
            'is_item' => true,
            'checkbox' => true,
            'icon' => $img,
            'qty' => 1,
            'price' => 0.00,
            'addl_info' => $item['addl_info'],
            'sqft' => $item['sqft'],
            'linft' => $item['linft'],
            'cabinet' => $item['cabinet'],
            'addlMarkup' => $item['addlMarkup'],
            'itemID' => $item['itemID'],
            'percentMarkup' => $item['percentMarkup']);
        }
      }
    } else {
      $object = array('key' => $item['catID'], 'folder' => true, 'title' => $item['name']);

      $children = makeTree($item['catID']);

      if(!empty($children)) {
        $object['children'] = $children;
      }

      $ret[] = $object;
    }
  }

  return $ret;
}

$result = makeTree(15000);

echo json_encode($result);