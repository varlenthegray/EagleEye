<?php
require '../../../includes/header_start.php';

outputPHPErrs();

//header('Content-Type:text/plain');
//header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
//header('Cache-Control: post-check=0, pre-check=0', false);
//header('Pragma: no-cache');

function fetchCategoryTreeList($parent = 0, $user_tree_array = '') {
  global $dbconn;

  // get all of the categories associated with parent and catalog id = 1
  $sql = "SELECT * FROM pricing_categories WHERE parent = $parent AND catalog_id = 1 ORDER BY id ASC";
  $result = $dbconn->query($sql); // get the results

  // if there were results
  if ($result->num_rows > 0) {
    $user_tree_array[] = '<ul>'; // start the category list

    while ($row = $result->fetch_assoc()) { // for every result
      $user_tree_array[] = '<li class="folder">' . $row['name'] . '</li>'; // create the category names

      $sku_qry = $dbconn->query("SELECT * FROM pricing_nomenclature WHERE category_id = {$row['id']}");

      if($sku_qry->num_rows > 0) {
        $user_tree_array[] = '<ul>';

        while($sku = $sku_qry->fetch_assoc()) {
          $user_tree_array[] = '<li data-item="true">' . $sku['sku'] . '</li>';
        }

        $user_tree_array[] = '</ul>';
      }

      $user_tree_array = fetchCategoryTreeList($row['id'], $user_tree_array); // check for sub-categories and items
    }

    $user_tree_array[] = '</ul>'; // end the category list
  }

  return $user_tree_array; // return the entire result tree
}

$res = fetchCategoryTreeList();

//echo json_encode($res, true);
//print_r($res);

foreach($res AS $i) {
  echo $i;
}