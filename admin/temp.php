<?php
require '../includes/header_start.php';

outputPHPErrs();

switch($_REQUEST['action']) {
  case 'companyID':
    $so_qry = $dbconn->query('SELECT id, LEFT(dealer_code, 3) AS dealer_parent FROM sales_order');

    while($so = $so_qry->fetch_assoc()) {
      $dealer_qry = $dbconn->query("SELECT contact FROM dealers WHERE dealer_id = '{$so['dealer_parent']}'");
      $dealer = $dealer_qry->fetch_assoc();

      $company_qry = $dbconn->query("SELECT id FROM contact_company WHERE name = '{$dealer['contact']}'");
      $company = $company_qry->fetch_assoc();

      $dbconn->query("UPDATE sales_order SET company_id = {$company['id']} WHERE id = {$so['id']}");

      echo "<h1>SO ID: {$so['id']} updated.</h1><hr />";
    }

    break;

  case 'assignPM':
    $so_qry = $dbconn->query('SELECT d.id AS dID, so.id AS soID, so.so_num FROM sales_order so LEFT JOIN dealers d ON so.dealer_code = d.dealer_id WHERE dealer_code IS NOT NULL AND char_length(dealer_code) > 3');

    while($so = $so_qry->fetch_assoc()) {
      $contact_qry = $dbconn->query("SELECT id FROM contact WHERE dealer_id = {$so['dID']}");
      $contact = $contact_qry->fetch_assoc();

      $dbconn->query("INSERT INTO contact_associations (type_id, contact_id, assigned_by, created_on, associated_as) VALUES ({$so['soID']}, {$contact['id']}, 1, UNIX_TIMESTAMP(), 'Project Manager');");

      echo "<h1>SO Num {$so['so_num']} updated</h1><hr />";
    }

    break;
}

