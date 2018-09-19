<?php
require '../../../includes/header_start.php';

$dealer_qry = $dbconn->query("SELECT 
  d.id, d.dealer_name 
FROM dealers d
  LEFT JOIN sales_order so ON d.dealer_id = so.dealer_code
WHERE 
  LOWER(d.dealer_name) LIKE '%$search%' 
  OR LOWER(d.dealer_id) LIKE '%$search%' 
  OR LOWER(d.email) LIKE '%$search%' 
  OR LOWER(so.so_num) LIKE '%$search%' 
  OR LOWER(so.project_name) LIKE '%$search%' 
  OR LOWER(so.email_1) LIKE '%$search%'
ORDER BY d.dealer_name ASC;");

$output = [];
$i = 0;

if($dealer_qry->num_rows > 0) {
  while($dealer = $dealer_qry->fetch_assoc()) {
    $output[$i]['title'] = $dealer['dealer_name'];
    $output[$i]['search_term'] = $search;
    $output[$i]['key'] = $dealer['id'];
    $output[$i]['folder'] = 'true';
    $output[$i]['lazy'] = 'true';
  }
}

if(!empty($output)) {
  echo json_encode($output);
} else {
  echo "Unable to find anything to output for $search.";
}