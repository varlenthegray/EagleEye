<?php
require '../includes/header_start.php';

outputPHPErrs();

$cc_qry = $dbconn->query('SELECT * FROM contact_company');

while($cc = $cc_qry->fetch_assoc()) {
  $con_qry = $dbconn->query("SELECT * FROM contact WHERE first_name IS NULL AND last_name IS NULL AND company_name = '{$cc['name']}' LIMIT 0,1;");
  $conID = $con_qry->fetch_assoc();
  $conID = $conID['id'];

  if(!$so_qry = $dbconn->query("UPDATE sales_order SET contact_id = $conID WHERE company_id = {$cc['id']};")) {
    echo $dbconn->error;
  }
}

echo '<h2>Completed</h2>';

