<?php
require '../includes/header_start.php';

outputPHPErrs();

$nom_qry = $dbconn->query('SELECT * FROM pricing_nomenclature WHERE catalog_id = 1;');

while($nom = $nom_qry->fetch_assoc()) {
  $cat_array = [];

  if(!empty($nom['category_id'])) {
    $cat_tree_qry = $dbconn->query("SELECT T2.id, T2.name, T2.description_id, T2.enabled_desc FROM (
    SELECT @r AS _id, (SELECT @r := parent FROM pricing_categories WHERE id = _id) AS parent_id, @l := @l + 1 AS lvl
    FROM (SELECT @r := {$nom['category_id']}, @l := 0) vars, pricing_categories h WHERE @r <> 0 AND catalog_id = 1) T1
    JOIN pricing_categories T2 ON T1._id = T2.id ORDER BY T1.lvl DESC;");

    while($cat_tree = $cat_tree_qry->fetch_assoc()) {
      $cat_array[] = $cat_tree['id'];
    }

    $cats = json_encode($cat_array);

    $dbconn->query("UPDATE pricing_nomenclature SET desc_available = '$cats' WHERE id = {$nom['id']}");
  }
}

echo "<h2>Completed</h2>";

