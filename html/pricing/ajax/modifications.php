<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$catalog_id = !empty(sanitizeInput($_REQUEST['catalog'])) ? sanitizeInput($_REQUEST['catalog']) : 2;

// Brandon Christensen 4/14/2018
$result = array();

$parent_qry = $dbconn->prepare("SELECT 
  pc.id AS catID, pn.id AS itemID, name, parent, sort_order, pn.category_id, pn.sku, detail.image_path
FROM pricing_categories pc
  LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id
  LEFT JOIN pricing_nomenclature_details detail on pn.description_id = detail.id
WHERE pc.catalog_id = $catalog_id AND parent = ? AND pc.id BETWEEN 258 AND 280 ORDER BY parent, sort_order, catID ASC");

function makeTree($parent_id) {
  global $parent_qry;

  $ret = array();

  $parent_qry->bind_param('i', $parent_id);
  $parent_qry->bind_result($catID, $itemID, $name, $parent, $sort_order, $item_catID, $sku, $image);
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
      'image_path' => $image
    );
  }

  $parent_qry->free_result();

  $sku_items = array();

  foreach($data as $item) {
    if(!empty($item['sku'])) {
      if(!isset($sku_items[$item['item_catID']])) {
        $object = array('key' => $item['catID'], 'title' => $item['name'], 'folder' => true, 'children' => array());
        $sku_items[$item['item_catID']] = $object;
        $ret[] = &$sku_items[$item['item_catID']];
      }

      $title = $item['sku'];
      $title .= " <span class='actions'>
            <div class='info_container'><i class='fa fa-info-circle primary-color view_item_info' data-id='{$item['itemID']}'></i></div>
          </span>";

      $img = !empty($item['image_path']) ? "/html/pricing/images/{$item['image_path']}" : 'fa fa-magic';

      $sku_items[$item['item_catID']]['children'][] = array('key' => $item['itemID'], 'title' => $title, 'is_item' => true, 'checkbox' => true, 'icon' => $img, 'qty' => 1, 'price' => 0.00);
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