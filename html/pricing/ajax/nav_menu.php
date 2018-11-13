<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$disable_btns = empty($_REQUEST['disable_btns']) ? false : true;

// Brandon Christensen 4/14/2018
$result = array();

// get categories based on parent
$parent_qry = $dbconn->prepare('SELECT
  pc.id AS catID, pn.id AS itemID, name, parent, pc.sort_order, pn.category_id, pn.sku, detail.image_path, pc.catalog_id AS catalog_id, pn.catalog_id AS itemCatalogID, 
  pn.percent, pn.sort_order AS nomenclature_sort
FROM pricing_categories pc
  LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id
  LEFT JOIN pricing_nomenclature_details detail on pn.description_id = detail.id
WHERE parent = ? AND pc.catalog_id = 1 ORDER BY pc.catalog_id, parent, pc.sort_order, nomenclature_sort, pn.sku, catID ASC;');

function makeTree($parent_id) {
  global $disable_btns;
  global $parent_qry;

  $ret = array();

  $parent_qry->bind_param('i', $parent_id);
  $parent_qry->bind_result($catID, $itemID, $name, $parent, $sort_order, $item_catID, $sku, $image, $catCatalogID, $itemCatalogID, $pctMarkup, $nomSort);
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
      'catCatalogID' => $catCatalogID,
      'itemCatalogID' => $itemCatalogID,
      'percentMarkup' => $pctMarkup,
      'nomenclatureSort' => $nomSort
    );
  }

  $parent_qry->free_result();

  $sku_items = array();

  foreach($data as $item) { // for every query result as item
    if(!empty($item['sku'])) { // if it has a SKU
      if(!isset($sku_items[$item['item_catID']])) { // and we have not created a category ID for this item
        $object = array('key' => $item['catID'], 'title' => $item['name'], 'folder' => true, 'sort_order' => $item['sort_order'], 'children' => array()); // create the folder
        $sku_items[$item['item_catID']] = $object; // add the folder to the items of the SKU
        $ret[] = &$sku_items[$item['item_catID']]; // refer to the value of SKU items for that category ID that was just added
      }

      if($item['itemCatalogID'] === 1) { // as long as this is catalog 1
        $title = $item['sku']; // the title = the SKU

        if(!$disable_btns) { // if we're not disabling the buttons
          $title .= " <span class='actions'>
            <div class='info_container'><i class='fa fa-info-circle primary-color view_item_info' data-id='{$item['itemID']}'></i></div>
            <i class='fa fa-plus-circle success-color add_item_cabinet_list' data-id='{$item['itemID']}' title='Add To Cabinet List'></i>
          </span>"; // output the title
        }

        // if the image isn't empty, update the image
        $img = !empty($item['image_path']) ? "/html/pricing/images/{$item['image_path']}" : 'fa fa-magic';

        // add this item to the children of that array
        $sku_items[$item['item_catID']]['children'][] = array('key' => $item['itemID'],'icon' => $img, 'title' => $title, 'is_item' => true, 'qty' => 1, 'nomSort' => $nomSort);
      }
    } else { // otherwise we're creating folders
      $object = array('key' => $item['catID'], 'folder' => true, 'title' => $item['name'], 'sort_order' => $item['sort_order']); // create the folder object

      $children = makeTree($item['catID']); // check for sub-folders and items

      if(!empty($children)) { // if there were sub-folders or items
        $object['children'] = $children; // we're adding them to the children of this object
      }

      $ret[] = $object; // return the folder structure
    }
  }

  return $ret;
}

$result = makeTree(0);

echo json_encode($result);