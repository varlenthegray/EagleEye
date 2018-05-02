<?php
require '../../includes/header_start.php';

outputPHPErrs();

$output = array();
$i = 0;

$opl_qry = $dbconn->query('SELECT ou.opl, u.name FROM opl_users ou LEFT JOIN user u on ou.user_id = u.id;');

while($opl = $opl_qry->fetch_assoc()) {
  $output[$i]['title'] = $opl['name'];
  $output[$i]['children'] = $opl['opl'];
  $output[$i]['folder'] = true;

  $i++;

  echo $opl['opl'];
}

