<?php
require '../includes/header_start.php';

//outputPHPErrs();

$vin_qry = $dbconn->query("SELECT * FROM vin_schema 
ORDER BY segment ASC, case `group` when 'Custom' then 1 when 'Other' then 2 else 3 end, `group` ASC,
 FIELD(`value`, 'Custom', 'Other', 'No', 'None') DESC,
 FIELD(`key`, 'B78') DESC");

while($vin = $vin_qry->fetch_assoc()) {
  $vin_schema[$vin['segment']][] = $vin;
}

print_r($vin_schema);