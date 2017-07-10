<?php
require ("../includes/header_start.php");

$cu_qry = $dbconn->query("SELECT * FROM customer");

while($cu = $cu_qry->fetch_assoc()) {
    $dbconn->query("INSERT INTO sales_order (so_num, salesperson, contractor_dealer_code, project, 
     project_addr, project_city, project_state, project_zip, project_landline, project_cell, project_mgr) 
      VALUES ('{$cu['sales_order_num']}', '{$cu['project_manager']}', '{$cu['dealer_code']}', '{$cu['project']}', 
       '{$cu['addr_1']} {$cu['addr_2']}', '{$cu['city']}', '{$cu['state']}', '{$cu['zip']}', '{$cu['pri_phone']}', 
        '{$cu['alt_phone_1']}', '{$cu['project_manager']}')");
}