<?php
require '../../../includes/header_start.php';

outputPHPErrs();


$categories = array();


function get_categories() {
  global $dbconn;

  $query = "select * from pricing_categories";
  $result = $dbconn->query($query);

  if(!$result) {
    return false;
  }else {
    $result = db_result_to_array($result);
    return $result;
  }
}

function get_child_cats($parent_id) {
  global $dbconn;

  $query = "select * from pricing_categories where parent = '".$parent_id."'";

  $result = $dbconn->query($query);

  if(!$result) {
    return false;
  }else {
    $result = db_result_to_array($result);
    return $result;
  }

}

function db_result_to_array($result) {
  $res_array = array();

  for($count = 0; $row = $result->fetch_assoc(); $count++) {
    $res_array[$count] = $row;
  }

  return $res_array;
}


if(is_array($categories)) {
  echo "<ul>";

  foreach($categories as $row) {
    if(!$row['parent_id']) {
      $childs = get_child_cats($parent_id);

      echo "<li>".$row['cat_name'];

      if($row['parent_id']) {
        echo "<ul>";

        foreach($childs as $row) {
          echo "<li>".$row['cat_name']."</li>";
        }

        echo "</ul>";
      }
      echo "</li>";
    }

  }
  echo "</ul>";
}



/*$category_qry = $dbconn->query("SELECT
  pc.id AS catID, pn.id AS itemID, name, parent, sort_order, pn.category_id, pn.sku
FROM pricing_categories pc
  LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id 
WHERE pc.catalog_id = 1 ORDER BY parent, sort_order, catID ASC");

$cat_array = array();
$item_array = array();
$output = null;
$prev_cat = null;

// what do i want:
// all parent categories grouped together, 0 is the top level, 1 is the next level under 0
// $category[$parent][$sort_order] = $array_values;
// then for each category[$parent] sort by $sort_order, inside of each $sort_order grab the values, return them as an associate array under the parent

// if there are items and/or categories in the database
if($category_qry->num_rows > 0) {
  while($category = $category_qry->fetch_assoc()) {
    if($category['catID'] !== $prev_cat) { // if the previous category ID doesn't match this id
      $item_array = array(); // reset the array (because we're starting over with a new category)
      $prev_cat = $category['catID']; // update the previous category with the current category
    }

    $item_array[] = array('key' => $category['itemID'], 'title' => $category['sku']); // add items in a big array

    if(!empty($category['itemID'])) {
      $output = array('key' => $category['catID'], 'title' => $category['name'], 'children' => $item_array);
    } else {
      $output = array('key' => $category['catID'], 'title' => $category['name']);
    }

    //$cat_array[$category['parent']] = array('key' => $category['catID'], 'title' => $category['name']);

    $cat_array[$category['parent']][] = $output; // formalize the final array
  }
}

/*$new_array = array(); // semi-functional but doesn't nest

foreach($cat_array AS $sort_order) {
  foreach($sort_order AS $item) {
    $new_array[] = $item;
  }
}*/



//var_dump($new_array);
//echo json_encode($new_array);
//echo json_encode(array_values($cat_array));



// BRANDON ATTEMPT 1
/*$result = array();

function makeTree($parent_id) {
  global $dbconn;

  $parent_qry = $dbconn->prepare("SELECT
  pc.id AS catID, pn.id AS itemID, name, parent, sort_order, pn.category_id, pn.sku
FROM pricing_categories pc
  LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id
WHERE pc.catalog_id = 1 AND parent = ? ORDER BY parent, sort_order, catID ASC");

  $ret = array();

  $parent_qry->bind_param('i', $parent_id);
  $parent_qry->execute();
  $parent_qry->store_result();
  $parent_qry->bind_result($catID, $itemID, $name, $parent, $sort_order, $item_catID, $sku);

  $item = null;

  if($parent_qry->num_rows !== 0) {
    $parent_qry->fetch();

    if(!empty($sku)) {
      $item = true;
      $ret = array('key' => $catID, 'title' => $name, 'children' => array());
    } else {
      $item = false;
    }

    do {
      if($item === false) {
        $object = array('key' => $catID, 'title' => $name, 'childrenCount' => $parent_qry->num_rows, 'item' => $item);

        $children = makeTree($catID);

        if(!empty($children)) {
          $object['children'] = $children;
        }

        $ret[] = $object;
      } else {
        $object = array('key' => $catID, 'title' => $sku, 'item' => $item);

        $ret['children'][] = $object;
      }
    } while($parent_qry->fetch());
  }

  $parent_qry->free_result();
  $parent_qry->close();

  return $ret;
}

$result = makeTree(0);

echo json_encode($result);*/
















/*$output = null; // output variable is nothing right now

function makeTree($parent, $categories) { // this is to help loop through itself and go through as many nests as needed
  if(isset($categories[$parent])) { // if we're starting with a parent (this starts at 0, then recurses down)
    $output = '<ul>'; // we're putting a new main level in
    ksort($categories[$parent]); // sort the keys in that parent array

    foreach ($categories[$parent] as $category) { // for each item in that parent array as individual categories
      $output .= "<li class='ws-wrap'>{$category['name']}"; // create the category name and list it out
      $output .= makeTree($category['id'], $categories); // then, recurse through the categories again

      if(!empty($category['items'])) { // if there are items
        $output .= '<ul>'; // add this as the next level

        foreach($category['items'] AS $item) { // for every item
          $output .= "<li class='ws-wrap'>{$item['name']}</li>"; // list it out
        }
        $output .= '</ul>'; // terminate the item list for that category
      }
      $output .= '</li>'; // terminate the list item for the sub-category
    }

    $output .= '</ul>'; // terminate the list item for the main category (this could be sub-categories too, thanks to recursing)

    return $output; // return the gigantic output!
  }
}

$output = makeTree(0, $cat_array); // make the UL tree with the big array of all variables*/


/*$category_qry = $dbconn->query("SELECT
  pc.id AS catID, pn.id AS itemID, name, parent, sort_order, pn.category_id, pn.sku
FROM pricing_categories pc
  LEFT JOIN pricing_nomenclature pn on pc.id = pn.category_id
WHERE pc.catalog_id = 1 ORDER BY parent, sort_order, catID ASC");

$cat_array = array();
$item_array = array();
$output = null;
$prev_cat = null;

// if there are items and/or categories in the database
if($category_qry->num_rows > 0) {
  while($category = $category_qry->fetch_assoc()) {
    if($category['catID'] !== $prev_cat) { // if the previous category ID doesn't match this id
      $item_array = array(); // reset the array (because we're starting over with a new category)
      $prev_cat = $category['catID']; // update the previous category with the current category
    }

    $item_array[] = array('id' => $category['itemID'], 'name' => $category['sku']); // add items in a big array

    $cat_array[$category['parent']][$category['sort_order']] = array('id' => $category['catID'], 'name' => $category['name'], 'items' => $item_array); // formalize the final array
  }
}

$output = null; // output variable is nothing right now

function makeTree($parent, $categories) { // this is to help loop through itself and go through as many nests as needed
  if(isset($categories[$parent])) { // if we're starting with a parent (this starts at 0, then recurses down)
    $output = '<ul>'; // we're putting a new main level in
    ksort($categories[$parent]); // sort the keys in that parent array

    foreach ($categories[$parent] as $category) { // for each item in that parent array as individual categories
      $output .= "<li class='ws-wrap'>{$category['name']}"; // create the category name and list it out
      $output .= makeTree($category['id'], $categories); // then, recurse through the categories again

      if(!empty($category['items'])) { // if there are items
        $output .= '<ul>'; // add this as the next level

        foreach($category['items'] AS $item) { // for every item
          $output .= "<li class='ws-wrap'>{$item['name']}</li>"; // list it out
        }
        $output .= '</ul>'; // terminate the item list for that category
      }
      $output .= '</li>'; // terminate the list item for the sub-category
    }

    $output .= '</ul>'; // terminate the list item for the main category (this could be sub-categories too, thanks to recursing)

    return $output; // return the gigantic output!
  }
}

$output = makeTree(0, $cat_array); // make the UL tree with the big array of all variables*/

//echo json_encode($output);

//echo json_encode($cat_array, true);

//var_dump($cat_array);
//echo json_encode($final_out);
//var_dump($final_out);