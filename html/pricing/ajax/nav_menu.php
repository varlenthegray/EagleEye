<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$catalog_id = (!empty(sanitizeInput($_REQUEST['catalog']))) ? sanitizeInput($_REQUEST['catalog']) : 2;

// Brandon Christensen 4/14/2018
$result = array();

$parent_qry = $dbconn->prepare("SELECT 
  pc.id AS catID, pn.id AS itemID, name, parent, sort_order, pn.category_id, pn.sku, detail.image_path
FROM pricing_categories pc
  LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id
  LEFT JOIN pricing_nomenclature_details detail on pn.description_id = detail.id
WHERE pc.catalog_id = $catalog_id AND parent = ? ORDER BY parent, sort_order, catID ASC");

function makeTree($parent_id) {
  global $parent_qry;

  $ret = array();

  $parent_qry->bind_param('i', $parent_id);
  $parent_qry->bind_result($catID, $itemID, $name, $parent, $sort_order, $item_catID, $sku);
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
      'sku' => $sku
    );
  }

  $parent_qry->free_result();

  $sku_items = array();

  foreach($data as $item) {
    if (!empty($item['sku'])) {
      if (!isset($sku_items[$item['item_catID']])) {
        $object = array('key' => $item['catID'], 'title' => $item['name'], 'folder' => true, 'children' => array());
        $sku_items[$item['item_catID']] = $object;
        $ret[] = &$sku_items[$item['item_catID']];
      }

      $title = $item['sku'];
      $title .= " <span class='actions'>
            <div class='info_container'><i class='fa fa-info-circle primary-color view_item_info' data-id='{$item['itemID']}' title='View More Information'></i></div>
            <i class='fa fa-plus-circle success-color add_item_cabinet_list' data-id='{$item['itemID']}' title='Add To Cabinet List'></i>
          </span>";

      $sku_items[$item['item_catID']]['children'][] = array('key' => $item['itemID'], 'title' => $title, 'is_item' => true);
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

$result = makeTree(0);

echo json_encode($result);