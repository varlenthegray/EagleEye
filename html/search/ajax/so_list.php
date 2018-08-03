<?php
require_once '../../../includes/header_start.php';

//outputPHPErrs();

$find = sanitizeInput($_REQUEST['find']);
$output = [];

if((bool)$_SESSION['userInfo']['dealer']) {
  $dealer_filter = "AND dealer_code LIKE '{$_SESSION['userInfo']['dealer_code']}%'";
} else {
  $dealer_filter = null;
}

if($find_qry = $dbconn->query("SELECT
  so.id AS soID, so.so_num, so.project_name, d.dealer_id, d.dealer_name, d.contact
FROM sales_order so 
  LEFT JOIN dealers d ON so.dealer_code = d.dealer_id
WHERE 
(so_num LIKE '%$find%' OR LOWER(dealer_code) LIKE LOWER('%$find%') OR LOWER(project_name) LIKE LOWER('%$find%') OR LOWER(project_mgr) LIKE LOWER('%$find%') 
OR LOWER(name_1) LIKE LOWER('%$find%') OR LOWER(name_2) LIKE LOWER('%$find%'))
$dealer_filter ORDER BY so_num DESC")) {
  while($find = $find_qry->fetch_assoc()) {
    $output[] = $find;
  }
}

echo json_encode($output);