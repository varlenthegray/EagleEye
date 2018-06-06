<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

// Brandon Christensen 4/14/2018
$result = array();

$itemID = sanitizeInput($_REQUEST['itemID']);
$mods_accepted = array();

if(!empty($itemID)) {
  $mod_type_qry = $dbconn->query("SELECT mod_type FROM pricing_nomenclature WHERE id = $itemID;");
  $mod_type = $mod_type_qry->fetch_assoc();

  $mod_type = $mod_type['mod_type'];

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
  pc.id AS catID, pn.id AS itemID, name, parent, sort_order, pn.category_id, pn.sku, detail.image_path, detail.title
FROM pricing_categories pc
  LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id
  LEFT JOIN pricing_nomenclature_details detail on pn.description_id = detail.id
WHERE  parent = ? AND pc.id BETWEEN 258 AND 280 ORDER BY parent, sort_order, catID ASC');

function makeTree($parent_id) {
  global $parent_qry;
  global $mods_accepted;

  $ret = array();

  $parent_qry->bind_param('i', $parent_id);
  $parent_qry->bind_result($catID, $itemID, $name, $parent, $sort_order, $item_catID, $sku, $image, $sku_title);
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
      'sku_title' => $sku_title
    );
  }

  $parent_qry->free_result();

  $sku_items = array();

  foreach($data as $item) {
    if(!empty($item['sku'])) {
      if(in_array($item['itemID'], $mods_accepted, false)) {
//      if(1===1) {
        if (!isset($sku_items[$item['item_catID']])) {
          $object = array('key' => $item['catID'], 'title' => $item['name'], 'folder' => true, 'children' => array());
          $sku_items[$item['item_catID']] = $object;
          $ret[] = &$sku_items[$item['item_catID']];
        }

        $title = "{$item['sku']} - {$item['sku_title']}";
        $title .= " <span class='actions'>
          <div class='info_container'><i class='fa fa-info-circle primary-color view_item_info' data-id='{$item['itemID']}'></i></div>
        </span>";

        $img = !empty($item['image_path']) ? "/html/pricing/images/{$item['image_path']}" : 'fa fa-magic';

        $sku_items[$item['item_catID']]['children'][] = array('key' => $item['itemID'], 'title' => $title, 'is_item' => true, 'checkbox' => true, 'icon' => $img, 'qty' => 1, 'price' => 0.00);
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

$result = makeTree(258);

echo json_encode($result);