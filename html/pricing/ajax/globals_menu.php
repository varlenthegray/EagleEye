<?php
require '../../../includes/header_start.php';

$vin_schema = array();

$vin_qry = $dbconn->query("SELECT * FROM vin_schema
WHERE visible = TRUE AND (segment = 'construction_method' OR segment = 'species_grade' OR segment = 'carcass_material' OR segment = 'door_design' OR 
  segment = 'panel_raise' OR segment = 'style_rail_width' OR segment = 'edge_profile' OR segment = 'framing_bead' OR segment = 'framing_options' OR 
  segment = 'drawer_boxes' OR segment = 'drawer_guide' OR segment = 'finish_code' OR segment = 'sheen' OR segment = 'glaze' OR segment = 'glaze_technique' OR 
  segment = 'antiquing' OR segment = 'worn_edges' OR segment = 'distress_level' OR segment = 'green_gard')
ORDER BY FIELD(segment, 'construction_method', 'species_grade', 'carcass_material', 'door_design', 'panel_raise', 'style_rail_width', 'edge_profile', 'framing_bead',
  'framing_options', 'drawer_boxes', 'drawer_guide', 'finish_code', 'sheen', 'glaze', 'glaze_technique', 'antiquing', 'worn_edges', 'distress_level', 'green_gard'), `group`, value ASC");

while($vin = $vin_qry->fetch_assoc()) {
  $vin_schema[$vin['segment']][] = $vin;
}

$result = array();
$disable_btns = $_REQUEST['disable_btns'];
$prev_group = null;
$prev_key = null;
$i = 1;
$k = 0;
$l = 0;

foreach($vin_schema AS $key => $item) {
  foreach($item AS $newVal) {
    $title = $newVal['value'];

    $result[$key]['key'] = $newVal['id'];
    $result[$key]['folder'] = true;
    $result[$key]['title'] = $newVal['human_segment'];
    $result[$key]['sort_order'] = $i;

    if(!$disable_btns) { // if we're not disabling the buttons
      $title .= " <span class='actions'>
            <div class='info_container'><i class='fa fa-info-circle primary-color view_item_info' data-id='{$item['itemID']}'></i></div>
            <i class='fa fa-plus-circle success-color add_item_cabinet_list' data-id='{$item['itemID']}' title='Add To Cabinet List'></i>
          </span>"; // output the title
    }

    $object = array('key' => $newVal['id'], 'icon' => 'fa fa-globe', 'title' => strip_tags($title));

    // TODO: gotta make newVal[group] = to array_vals, somehow

    if($prev_key !== $key) {
      $prev_key = $key;
      $k++;
      $l = 0;
      $prev_group = $newVal['group'];
    } else if($prev_group !== $newVal['group']) {
      $prev_group = $newVal['group'];
      $l++;
    }

    $result[$key]['children'][$l]['key'] = $newVal['id'];
    $result[$key]['children'][$l]['folder'] = true;
    $result[$key]['children'][$l]['title'] = $newVal['group'];

    $result[$key]['children'][$l]['children'][] = $object;
  }

  $i++;
}

$result = array_values($result);

echo json_encode($result);