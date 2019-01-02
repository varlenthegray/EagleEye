<?php
require '../includes/header_start.php';

outputPHPErrs();

$id = sanitizeInput($_REQUEST['id']);

$cat_qry = $dbconn->query("SELECT T2.id, T2.name, T2.description_id FROM (
SELECT @r AS _id, 
      (SELECT @r := parent FROM pricing_categories WHERE id = _id) AS parent_id, 
      @l := @l + 1 AS lvl
FROM (SELECT @r := $id, @l := 0) vars, pricing_categories h WHERE @r <> 0) 
  T1 JOIN pricing_categories T2 ON T1._id = T2.id 
ORDER BY T1.lvl DESC");

while($cat = $cat_qry->fetch_assoc()) {
  if(!empty($cat['description_id'])) {
    $desc_id = $dbconn->query("SELECT * FROM pricing_nomenclature_details WHERE id = {$cat['description_id']}");
    $desc = $desc_id->fetch_assoc();
  } else {
    $desc['description'] = '';
  }

  echo "<textarea>{$desc['description']}</textarea>";
}
