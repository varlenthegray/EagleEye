<?php
require '../includes/header_start.php';

outputPHPErrs();

$global_qry = $dbconn->query("SELECT * FROM batch_global bg LEFT JOIN batch_category bc on bg.category_id = bc.id WHERE bg.enabled = TRUE AND bc.enabled = TRUE AND bg.show_as = 'checkbox' ORDER BY category_id ASC;");

$prev_cat = null;

while($global = $global_qry->fetch_assoc()) {
  if($global['category_id'] !== $prev_cat) {
    $prev_cat = $global['category_id'];


  }
}