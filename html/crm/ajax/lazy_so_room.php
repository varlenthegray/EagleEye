<?php
require '../../../includes/header_start.php';

// TODO: Add CRM to search

$search = sanitizeInput(strtolower($_REQUEST['search']));

$qry = $dbconn->query("SELECT
    so.id AS soID,
    r.id as rID,
    d.id as dID,
    d.dealer_name,
    so.so_num,
    so.project_name,
    r.room,
    r.iteration,
    r.room_name
  FROM sales_order so
    LEFT JOIN dealers d ON so.dealer_code = d.dealer_id
    LEFT JOIN rooms r on so.id = r.so_id
  WHERE
    LOWER(d.dealer_name) LIKE '%$search%' 
    OR LOWER(d.dealer_id) LIKE '%$search%' 
    OR LOWER(d.email) LIKE '%$search%' 
    OR LOWER(so.so_num) LIKE '%$search%' 
    OR LOWER(so.project_name) LIKE '%$search%' 
    OR LOWER(so.email_1) LIKE '%$search%'
  ORDER BY d.dealer_name, so.so_num, r.room, r.iteration ASC;");

$output = [];
$i = 0;

if($qry->num_rows > 0) {
  while($result = $qry->fetch_assoc()) {
//    $output[$i]['title'] = "<strong>{$result['so_num']} - {$result['project_name']}</strong>";
    $output[$i]['title'] = "{$result['so_num']} - {$result['project_name']}";
    $output[$i]['soID'] = $result['soID'];
    $output[$i]['rID'] = $result['rID'];

    $i++;
  }
}

if(!empty($output)) {
  echo json_encode($output);
} else {
  echo "Nothing found for $search";
}