<?php
require ("../includes/header_start.php");

$category_qry = $dbconn->query("SELECT 
          pc.id AS catID, pn.id AS itemID, name, parent, sort_order, pn.category_id, pn.sku
        FROM pricing_categories pc
          LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id 
        WHERE pc.catalog_id = 1 ORDER BY parent, sort_order, catID ASC");

$cat_array = array();
$item_array = array();
$output = null;
$prev_cat = null;
$item_sort_id = 0;

if($category_qry->num_rows > 0) {
  while($category = $category_qry->fetch_assoc()) {
    if($prev_cat !== $category['catID']) {
      $item_sort_id = 0;
      $item_array = array();
      $prev_cat = $category['catID'];
    } else {
      $item_array[$item_sort_id] = array('id' => $category['itemID'], 'name' => $category['sku']);
      $item_sort_id++;
    }

    $cat_array[$category['parent']][$category['sort_order']] = array('id' => $category['catID'], 'name' => $category['name'], 'items' => $item_array);
  }
}

$output = null;

function makeTree($parent, $categories) {
  if(isset($categories[$parent])) {
    $output = '<ul>';
    ksort($categories[$parent]);

    foreach ($categories[$parent] as $category) {
      $output .= "<li class='ws-wrap'>{$category['name']}";
      $output .= makeTree($category['id'], $categories);

      if(!empty($category['items'])) {
        $output .= '<ul>';

        foreach($category['items'] AS $item) {
          $output .= "<li class='ws-wrap'>{$item['name']}</li>";
        }
        $output .= '</ul>';
      }
      $output .= '</li>';
    }

    $output .= '</ul>';

    return $output;
  }
}

echo makeTree(0, $cat_array);