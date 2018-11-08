<?php
require '../includes/header_start.php';

outputPHPErrs();

$subitems = [];

$item_qry = $dbconn->query('SELECT * FROM pricing_nomenclature WHERE id = 60931');
$item = $item_qry->fetch_assoc();

if(!empty($item['kit_id'])) {
  if($kit_qry = $dbconn->query("SELECT * FROM pricing_kit WHERE kit_id = {$item['kit_id']}")) {
    while($kit = $kit_qry->fetch_assoc()) {
      if($si_qry = $dbconn->query("SELECT * FROM pricing_nomenclature WHERE id = {$kit['nomenclature_id']}")) {
        while($subitem = $si_qry->fetch_assoc()) {
          $subitems[] = $subitem;
        }
      }
    }
  }
}

$subitems[] = $item;

echo json_encode($subitems, true);