<?php
require '../../includes/header_start.php';

switch($_REQUEST['action']) {
  case 'getDoorList':
    $door_qry = $dbconn->query("SELECT pm1.price_group_id AS price_group, vs1.value AS door_design, vs1.id AS dd_id, vs2.id AS species_id, vs2.value AS title, 'false' AS icon 
    FROM pricing_price_group_map pm1
      LEFT JOIN vin_schema vs1 ON pm1.door_style_id = vs1.id
      LEFT JOIN vin_schema vs2 ON pm1.species_id = vs2.id
    WHERE vs1.visible = TRUE AND vs2.visible = TRUE ORDER BY vs2.value, price_group_id, vs1.value ASC;");

    while($door = $door_qry->fetch_assoc()) {
      $result[$door['dd_id']]['title'] = $door['door_design'];
      $result[$door['dd_id']]['folder'] = true;
      $result[$door['dd_id']]['children'][] = $door;
    }

    echo json_encode(array_values($result));

    break;
}