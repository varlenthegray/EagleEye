<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

function createBlankRoom($projectNum) {
  global $dbconn;

  $op_qry = $dbconn->query("SELECT * FROM operations WHERE op_id != '000' AND job_title NOT LIKE '%N/A%' ORDER BY op_id;");

  $ind_bracket = array();

  $starting_ops = array();

  while ($op = $op_qry->fetch_assoc()) {
    if (empty($starting_ops[$op['bracket']])) {
      $starting_ops[$op['bracket']] = $op['id'];
    }

    $ind_bracket[] = $op['id'];
  }

  $ind_bracket_final = json_encode($ind_bracket);

  $dbconn->query("INSERT INTO rooms (so_parent, room, room_name, product_type, individual_bracket_buildout, order_status, sales_marketing_bracket, shop_bracket,
  preproduction_bracket, press_bracket, paint_bracket, welding_bracket, custom_bracket, assembly_bracket, shipping_bracket, sales_marketing_published) 
  VALUES ('$projectNum', 'A', 'Intake (Auto-Generated)', 'C', '$ind_bracket_final', '#', '{$starting_ops['Sales/Marketing']}', '{$starting_ops['Shop']}', 
  '{$starting_ops['Pre-Production']}', '{$starting_ops['Press']}', '{$starting_ops['Paint']}', '{$starting_ops['Welding']}', '{$starting_ops['Custom']}', 
  '{$starting_ops['Assembly']}' , '{$starting_ops['Shipping']}', TRUE);");

  return $dbconn->insert_id;
}

switch($_REQUEST['action']) {
  case 'getCompanyList':
    $company_out['data'] = [];

    if($company_sql = $dbconn->query('SELECT id, name, phone_number, annual_revenue, industry, created FROM crm_company')) {
      while($company = $company_sql->fetch_assoc()) {
        $company['last_contact'] = 'Never';
        $company['created'] = date(DATE_TIME_ABBRV, $company['created']);
        $company['annual_revenue'] = '$' . number_format( $company['annual_revenue']) . '.00';

        $company_out['data'][] = $company;
      }

      echo json_encode($company_out);
    } else {
      echo 'No records found.';
    }

    break;
  case 'saveCompany':
    parse_str($_REQUEST['formInfo'], $info);

    foreach($info AS $key => $r) {
      $info[$key] = sanitizeInput($r);
    }

    if($dbconn->query("UPDATE contact_company SET name = '{$info['company_name']}', address = '{$info['company_address']}', city = '{$info['company_city']}', state = '{$info['company_state']}', zip = '{$info['company_zip']}',
    email = '{$info['company_email']}', landline = '{$info['company_landline']}', payment_processor = '{$info['company_payment_processor']}', shipping_address = '{$info['company_ship_addr']}',
    shipping_city = '{$info['company_ship_city']}', shipping_state = '{$info['company_ship_state']}', shipping_zip = '{$info['company_ship_zip']}', billing_address = '{$info['company_billing_addr']}',
    billing_city = '{$info['company_billing_city']}', billing_state = '{$info['company_billing_state']}', billing_zip = '{$info['company_billing_zip']}' WHERE id = {$info['company_id']}")) {
      echo displayToast('success', 'Successfully updated company.', 'Company Updated');
    } else {
      dbLogSQLErr($dbconn);
    }

    if(!empty($info['company_notes'])) {
      $followup_date = sanitizeInput($notes['company_followup_date']);
      $followup_individual = sanitizeInput($notes['requested_of']);

      $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$info['company_notes']}', 'company_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '{$info['company_id']}')");

      $note_id = $dbconn->insert_id;

      if(!empty($followup_date) && !empty($followup_individual)) {
        $followup = strtotime($followup_date);

        $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('company_followup', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'Company: {$info['company_name']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $note_id)");
      }
    }

    break;
  case 'newCompany':
    parse_str($_REQUEST['formInfo'], $info);

    foreach($info AS $key => $r) {
      $info[$key] = sanitizeInput($r);
    }

    $qry = $dbconn->query("SELECT DISTINCT so_num FROM sales_order WHERE so_num REGEXP '^[0-9]+$' ORDER BY so_num DESC LIMIT 0,1");
    $result = $qry->fetch_assoc();

    $next_so = $result['so_num'] + 1;

    if(!empty($info['company_name'])) {
      if($dbconn->query("INSERT INTO contact_company (name, address, city, state, zip, email, landline, payment_processor, shipping_address, shipping_city, shipping_state,
      shipping_zip, billing_address, billing_city, billing_state, billing_zip) VALUES ('{$info['company_name']}', '{$info['company_addr']}', '{$info['company_city']}', 
      '{$info['company_state']}', '{$info['company_zip']}', '{$info['company_email']}', '{$info['company_landline']}', '{$info['company_payment_processor']}', 
      '{$info['company_shipping_addr']}', '{$info['company_shipping_city']}', '{$info['company_shipping_state']}', '{$info['company_shipping_zip']}', '{$info['company_billing_addr']}', 
      '{$info['company_billing_city']}', '{$info['company_billing_state']}', '{$info['company_billing_zip']}');")) {
        $insert_id = $dbconn->insert_id;

        $dbconn->query("INSERT INTO sales_order (company_id, so_num, project_name) VALUES ($insert_id, '$next_so', 'New Project');");

        createBlankRoom($next_so);

        echo displayToast('success', 'Successfully created company.', 'Company Created');
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
      echo displayToast('error', 'Please fill in company name.', 'Company Name Required');
    }


    break;
  case 'addProject':
    parse_str($_REQUEST['formInfo'], $info);

    foreach($info AS $k => $v) {
      $info[$k] = sanitizeInput($v);
    }

    if($dbconn->query("INSERT INTO sales_order (company_id, so_num, project_name, project_addr, project_city, project_state, project_zip, project_landline) VALUES ({$info['company_id']}, '{$info['project_num']}', '{$info['project_name']}', '{$info['project_addr']}', '{$info['project_city']}', '{$info['project_state']}', '{$info['project_zip']}', '{$info['project_landline']}');")) {
      $op_qry = $dbconn->query("SELECT * FROM operations WHERE op_id != '000' AND job_title NOT LIKE '%N/A%' ORDER BY op_id;");

      $ind_bracket = array();
      $starting_ops = array();

      while ($op = $op_qry->fetch_assoc()) {
        if (empty($starting_ops[$op['bracket']])) {
          $starting_ops[$op['bracket']] = $op['id'];
        }

        $ind_bracket[] = $op['id'];
      }

      $room_id = createBlankRoom($info['project_num']);

      $dbconn->query("INSERT INTO op_queue (room_id, operation_id, created) VALUES ('$room_id', '{$starting_ops['Sales/Marketing']}', UNIX_TIMESTAMP())");

      echo displayToast('success', "Successfully created project {$info['project_num']}", 'Project Created');
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
}