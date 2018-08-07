<?php
require_once '../../../includes/header_start.php';

$output = [];

if($cal_qry = $dbconn->query('SELECT * FROM calendar')) {
  while($cal = $cal_qry->fetch_assoc()) {
    $output[] = $cal;
  }
}

echo json_encode($output);