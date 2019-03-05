<?php
require '../../../includes/header_start.php';
require_once '../../../includes/classes/mail_handler.php';

$mail = new \MailHandler\mail_handler();

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

$pin_count = 0;

function generatePIN(&$pin_count) {
  global $dbconn;

  $generated_pin = substr(str_shuffle('0123456789'), 0, 4);

  $existing_pin_qry = $dbconn->query("SELECT * FROM user WHERE pin_code = '$generated_pin' AND account_status = TRUE");

  if($pin_count <= 100 && $existing_pin_qry->num_rows > 0) {
    $pin_count++;
    generatePIN($pin_count);
  } elseif($pin_count > 100) {
    return false;
  } else {
    return $generated_pin;
  }
}

function generatePW($pin) {
  $passwd = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 3);
  $num = substr(str_shuffle('0123456789'), 0, 1);
  $first_last = mt_rand(0, 1);

  if($first_last === 0) {
    $pass_out = ucwords($passwd) . $num . $pin;
  } else {
    $pass_out = ucwords($passwd) . $pin . $num;
  }

  return $pass_out;
}

switch($_REQUEST['action']) {
  case 'addProject':
    parse_str($_REQUEST['formInfo'], $info);

    foreach($info AS $k => $v) {
      $info[$k] = sanitizeInput($v);
    }

    if($dbconn->query("INSERT INTO sales_order (contact_id, so_num, project_name, project_addr, project_city, project_state, project_zip, project_landline) 
                      VALUES ({$info['contact_id']}, '{$info['project_num']}', '{$info['project_name']}', '{$info['project_addr']}', '{$info['project_city']}', 
                              '{$info['project_state']}', '{$info['project_zip']}', '{$info['project_landline']}');")) {
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

      $dbconn->query("INSERT INTO op_queue (room_id, operation_id, created) VALUES ('$room_id', '{$starting_ops['Sales']}', UNIX_TIMESTAMP())");

      echo displayToast('success', "Successfully created project {$info['project_num']}", 'Project Created');
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
  case 'addNew':
    parse_str($_REQUEST['formInfo'], $info);

    foreach($info AS $key => $r) {
      $info[$key] = sanitizeInput($r);
    }

    // set the fields to blank based on what's being setup
//    if($info['new_type'] === 'Organization') { // if it's an organization, no first/last/title
//      $info['first_name'] = '';
//      $info['last_name'] = '';
//      $info['title'] = '';
//    } else { // otherwise, it's an individual, no org name
//      $info['org_name'] = '';
//    }

    // lets find out if this person's super unique information exists anywhere
    $existing_qry = $dbconn->query("SELECT * FROM contact c WHERE (email = '{$info['email']}' OR primary_phone = '{$info['primary_phone']}') AND (TRIM(email) != '' AND TRIM(primary_phone != ''))");

    if($existing_qry->num_rows > 0) {
      echo displayToast('warning', 'Email or Phone already exists in the system as a contact.', 'Duplicate Contact');
    } else {
      // initial variable definitions set to true (in case they don't run)
      $ins_contact = true; $ins_customer = true; $ins_vendor = true; $ins_emp = true;

      $ins_stmt = $dbconn->prepare("INSERT INTO contact (created_by, unique_id, company_name, first_name, last_name, title, address, city, state, zip, country, email, 
                     primary_phone, secondary_phone, other_phone, fax, creation) 
                     VALUES ({$_SESSION['userInfo']['id']}, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())");

      $ins_stmt->bind_param('sssssssssssssss', $info['identifier'], $info['org_name'], $info['first_name'], $info['last_name'],
                             $info['title'], $info['address'], $info['city'], $info['state'], $info['zip'], $info['country'], $info['email'],
                             $info['primary_phone'], $info['secondary_phone'], $info['other_phone'], $info['fax']);

      $ins_contact = $ins_stmt->execute();

      if($ins_contact) { // if we were able to insert the contact
        $contact_id = $dbconn->insert_id;

        // now it's time to figure out if we need to enter customer information in
        if(!empty($info['add_customer_checked'])) {
          $established_date = strtotime($info['cust_established_date']);

          $cust_stmt = $dbconn->prepare("INSERT INTO contact_customer (contact_id, created_by, established_date, status, `group`, max_commission, salesman_commission_id, 
                              salesman_commission_percent, referral_commission_id, referral_commission_percent, sales_group_commission_id, sales_group_commission_percent, 
                              other_commission_id, other_commission_percent, ship_method, ship_bill_to, ship_account, residential_delivery, ship_address, ship_city, 
                              ship_state, ship_zip, ship_country, billing_type, multiplier, payment_method, payment_terms, federal_id, federal_exempt_reason, created) 
                              VALUES ($contact_id, {$_SESSION['userInfo']['id']}, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())");

          $cust_stmt->bind_param('iiiiiiiiiiiiiisisssssidiisi', $established_date, $info['cust_status'], $info['cust_group'], $info['cust_max_commission'],
            $info['cust_salesman_commission_user'], $info['cust_salesman_commission'], $info['cust_referral_commission_user'], $info['cust_referral_commission'],
            $info['cust_sales_group_commission_user'], $info['cust_sales_group_commission'], $info['cust_other_commission_user'], $info['cust_other_commission'],
            $info['cust_ship_method'], $info['cust_ship_billto'], $info['cust_ship_account_num'], $info['cust_residential_delivery'],
            $info['cust_ship_address'], $info['cust_ship_city'], $info['cust_ship_state'], $info['cust_ship_zip'],
            $info['cust_ship_country'], $info['cust_billing_type'], $info['cust_multiplier'], $info['cust_payment_method'],
            $info['cust_payment_terms'], $info['cust_fed_id'], $info['cust_fed_exempt_reason']);

          $ins_customer = $cust_stmt->execute();

          if(!$ins_customer) {
            dbLogSQLErr($dbconn);
          }
        }

        // now figure out if we're adding vendor information in
        if(!empty($info['add_vendor_check'])) {
          $established_date = strtotime($info['vend_established_date']);

          $vend_stmt = $dbconn->prepare("INSERT INTO contact_vendor (contact_id, created_by, established_date, status, receive_method, receive_country, receive_address, 
                            receive_city, receive_state, receive_zip, payment_terms, federal_id, payment_contact, payment_country, payment_address, payment_city,
                            payment_state, payment_zip, payment_primary_phone, payment_secondary_phone, payment_other_phone, payment_fax, created) 
                            VALUES ($contact_id, {$_SESSION['userInfo']['id']}, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())");

          $vend_stmt->bind_param('iiisssssisssssssssss', $info['vend_established_date'], $info['vend_status'], $info['vend_receive_method'],
            $info['vend_receive_country'], $info['vend_receive_address'], $info['vend_receive_city'], $info['vend_receive_state'],
            $info['vend_receive_zip'], $info['vend_payment_terms'], $info['vend_fed_id'], $info['vend_payment_contact_name'],
            $info['vend_payment_contact_country'], $info['vend_payment_contact_address'], $info['vend_payment_contact_city'],
            $info['vend_payment_contact_state'], $info['vend_payment_contact_zip'], $info['vend_payment_primary_phone'],
            $info['vend_payment_secondary_phone'], $info['vend_payment_other_phone'], $info['vend_payment_fax']);

          $ins_vendor = $vend_stmt->execute();

          if(!$ins_vendor) {
            dbLogSQLErr($dbconn);
          }
        }

        // now, work on employee IF it's an individual
        if($info['new_type'] === 'Individual' && !empty($info['add_employee_check'])) {
          $hire_date = strtotime($info['emp_hire_date']);
          $personal_birthday = strtotime($info['emp_personal_birthday']);

          if($info['generate_pin_pw'] === '1') {
            $pin = generatePIN($pin_count);

            if($pin === false) {
              echo displayToast('error', 'PIN Code unable to generate. Could not find a unique PIN.', 'Unable to generate PIN');
            }

            $pw = generatePW($pin);
          } else {
            $pin = $info['emp_pin'];
            $pw = $info['emp_password'];
          }

          $emp_stmt = $dbconn->prepare("INSERT INTO contact_employee (contact_id, created_by, hire_date, languages, timezone, shift, facility, department, employee_status, user_access, 
                              username, pin, password, pay_schedule, federal_id, personal_country, personal_address, personal_city, personal_state, personal_zip, 
                              personal_email, personal_phone, personal_birthday, emergency_name, emergency_relationship, emergency_country, emergency_address, 
                              emergency_city, emergency_state, emergency_zip, emergency_pri_phone, emergency_secondary_phone, emergency_other_phone, emergency_email, created) 
                              VALUES ($contact_id, {$_SESSION['userInfo']['id']}, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())");

          $emp_stmt->bind_param('issiisiisssissssssssisssssssssss', $hire_date, $info['emp_languages'], $info['emp_timezone'], $info['emp_shift'],
            $info['emp_facility'], $info['emp_department'], $info['emp_status'], $info['emp_user_access'], $info['emp_username'],
            $pin, $pw, $info['emp_pay_schedule'], $info['emp_federal_id'], $info['emp_personal_country'],
            $info['emp_personal_address'], $info['emp_personal_city'], $info['emp_personal_state'], $info['emp_personal_zip'],
            $info['emp_personal_email'], $info['emp_personal_phone'], $personal_birthday, $info['emp_emergency_contact_name'],
            $info['emp_emergency_relationship'], $info['emp_emergency_contact_country'], $info['emp_emergency_contact_address'],
            $info['emp_emergency_contact_city'], $info['emp_emergency_contact_state'], $info['emp_emergency_contact_zip'],
            $info['emp_emergency_primary_phone'], $info['emp_emergency_secondary_phone'], $info['emp_emergency_other_phone'],
            $info['emp_emergency_email']);

          $ins_emp = $emp_stmt->execute();

          if(!$ins_emp) {
            dbLogSQLErr($dbconn);
          } else {
            $msg_body = "Welcome to EagleEye. Your account details are below.<br /><br />Username: {$info['emp_username']}<br />Password: $pw<br />PIN: $pin";

            if(!empty(trim($info['email']))) {
              $send_to = $info['email'];
            } else {
              $send_to = $_SESSION['userInfo']['email'];
            }

            $mail->sendMessage($send_to, $_SESSION['userInfo']['email'], 'New User Account Information', $msg_body, false);
          }
        }

        if($ins_contact && $ins_customer && $ins_vendor && $ins_emp) {
          echo displayToast('success', 'Successfully created contact.', 'Contact Created');
        } else {
          header('error: true');
          echo displayToast('error', 'Unable to create contact, please try again.', 'Contact Error');
        }
      } else {
        dbLogSQLErr($dbconn);
      }
    }

    break;
  case 'updateContact':
    parse_str($_REQUEST['formInfo'], $info);

    foreach($info AS $key => $r) {
      $info[$key] = sanitizeInput($r);
    }

    // set the fields to blank based on what's being setup
//    if($info['contactType'] === 'company') { // if it's an organization, no first/last/title
//      $info['first_name'] = '';
//      $info['last_name'] = '';
//      $info['title'] = '';
//    } else { // otherwise, it's an individual, no org name
//      $info['org_name'] = '';
//    }

    // initial variable definitions set to true (in case they don't run)
    $update_contact = true; $ins_customer = true; $ins_vendor = true; $ins_emp = true;

    $contact_stmt = $dbconn->prepare('UPDATE contact SET company_name = ?, first_name = ?, last_name = ?, title = ?, address = ?, city = ?, state = ?, zip = ?, 
                   country = ?, email = ?, primary_phone = ?, secondary_phone = ?, other_phone = ?, fax = ? WHERE id = ?');

    $contact_stmt->bind_param('ssssssssssssssi', $info['org_name'], $info['first_name'], $info['last_name'], $info['title'], $info['address'],
      $info['city'], $info['state'], $info['zip'], $info['country'], $info['email'], $info['primary_phone'], $info['secondary_phone'], $info['other_phone'],
      $info['fax'], $info['contactID']);

    $update_contact = $contact_stmt->execute();

    if($update_contact) {
      // now it's time to figure out if we need to enter customer information in
      if(!empty($info['add_customer_checked'])) {
        $established_date = strtotime($info['cust_established_date']);

        $cust_exist_qry = $dbconn->query("SELECT * FROM contact_customer WHERE contact_id = {$info['contactID']};");

        if($cust_exist_qry->num_rows > 0) {
          $cust_stmt = $dbconn->prepare('UPDATE contact_customer SET established_date = ?, status = ?, `group` = ?, max_commission = ?, salesman_commission_id = ?, 
                            salesman_commission_percent = ?, referral_commission_id = ?, referral_commission_percent = ?, sales_group_commission_id = ?, 
                            sales_group_commission_percent = ?, other_commission_id = ?, other_commission_percent = ?, ship_method = ?, ship_bill_to = ?, ship_account = ?, 
                            residential_delivery = ?, ship_address = ?, ship_city = ?, ship_state = ?, ship_zip = ?, ship_country = ?, billing_type = ?, multiplier = ?, 
                            payment_method = ?, payment_terms = ?, federal_id = ?, federal_exempt_reason = ? WHERE contact_id = ?');

          $cust_stmt->bind_param('iiiiiiiiiiiiiisisssssidiissi', $established_date, $info['cust_status'], $info['cust_group'], $info['cust_max_commission'],
            $info['cust_salesman_commission_user'], $info['cust_salesman_commission'], $info['cust_referral_commission_user'], $info['cust_referral_commission'],
            $info['cust_sales_group_commission_user'], $info['cust_sales_group_commission'], $info['cust_other_commission_user'], $info['cust_other_commission'],
            $info['cust_ship_method'], $info['cust_ship_billto'], $info['cust_ship_account_num'], $info['cust_residential_delivery'],
            $info['cust_ship_address'], $info['cust_ship_city'], $info['cust_ship_state'], $info['cust_ship_zip'],
            $info['cust_ship_country'], $info['cust_billing_type'], $info['cust_multiplier'], $info['cust_payment_method'],
            $info['cust_payment_terms'], $info['cust_fed_id'], $info['cust_fed_exempt_reason'], $info['contactID']);
        } else {
          $cust_stmt = $dbconn->prepare("INSERT INTO contact_customer (contact_id, created_by, established_date, status, `group`, max_commission, salesman_commission_id, 
                              salesman_commission_percent, referral_commission_id, referral_commission_percent, sales_group_commission_id, sales_group_commission_percent, 
                              other_commission_id, other_commission_percent, ship_method, ship_bill_to, ship_account, residential_delivery, ship_address, ship_city, 
                              ship_state, ship_zip, ship_country, billing_type, multiplier, payment_method, payment_terms, federal_id, federal_exempt_reason, created) 
                              VALUES ({$info['contactID']}, {$_SESSION['userInfo']['id']}, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())");

          $cust_stmt->bind_param('iiiiiiiiiiiiiisisssssidiisi', $established_date, $info['cust_status'], $info['cust_group'], $info['cust_max_commission'],
            $info['cust_salesman_commission_user'], $info['cust_salesman_commission'], $info['cust_referral_commission_user'], $info['cust_referral_commission'],
            $info['cust_sales_group_commission_user'], $info['cust_sales_group_commission'], $info['cust_other_commission_user'], $info['cust_other_commission'],
            $info['cust_ship_method'], $info['cust_ship_billto'], $info['cust_ship_account_num'], $info['cust_residential_delivery'],
            $info['cust_ship_address'], $info['cust_ship_city'], $info['cust_ship_state'], $info['cust_ship_zip'],
            $info['cust_ship_country'], $info['cust_billing_type'], $info['cust_multiplier'], $info['cust_payment_method'],
            $info['cust_payment_terms'], $info['cust_fed_id'], $info['cust_fed_exempt_reason']);
        }

        $ins_customer = $cust_stmt->execute();

        if(!$ins_customer) {
          dbLogSQLErr($dbconn);
        }
      }

      // now figure out if we're adding vendor information in
      if(!empty($info['add_vendor_check'])) {
        $established_date = strtotime($info['vend_established_date']);

        $vendor_exist_qry = $dbconn->query("SELECT * FROM contact_vendor WHERE contact_id = {$info['contactID']}");

        if($vendor_exist_qry->num_rows > 0) {
          $vend_stmt = $dbconn->prepare('UPDATE contact_vendor SET established_date = ?, status = ?, receive_method = ?, receive_country = ?, receive_address = ?, 
                          receive_city = ?, receive_state = ?, receive_zip = ?, payment_terms = ?, federal_id = ?, payment_contact = ?, payment_country = ?, 
                          payment_address = ?, payment_city = ?, payment_state = ?, payment_zip = ?, payment_primary_phone = ?, payment_secondary_phone = ?, 
                          payment_other_phone = ?, payment_fax = ? WHERE contact_id = ?');

          $vend_stmt->bind_param('iiisssssisssssssssssi', $established_date, $info['vend_status'], $info['vend_receive_method'],
            $info['vend_receive_country'], $info['vend_receive_address'], $info['vend_receive_city'], $info['vend_receive_state'],
            $info['vend_receive_zip'], $info['vend_payment_terms'], $info['vend_fed_id'], $info['vend_payment_contact_name'],
            $info['vend_payment_contact_country'], $info['vend_payment_contact_address'], $info['vend_payment_contact_city'],
            $info['vend_payment_contact_state'], $info['vend_payment_contact_zip'], $info['vend_payment_primary_phone'],
            $info['vend_payment_secondary_phone'], $info['vend_payment_other_phone'], $info['vend_payment_fax'], $info['contactID']);
        } else {
          $vend_stmt = $dbconn->prepare("INSERT INTO contact_vendor (contact_id, created_by, established_date, status, receive_method, receive_country, receive_address, 
                            receive_city, receive_state, receive_zip, payment_terms, federal_id, payment_contact, payment_country, payment_address, payment_city,
                            payment_state, payment_zip, payment_primary_phone, payment_secondary_phone, payment_other_phone, payment_fax, created) 
                            VALUES ({$info['contactID']}, {$_SESSION['userInfo']['id']}, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())");

          $vend_stmt->bind_param('iiisssssisssssssssss', $info['vend_established_date'], $info['vend_status'], $info['vend_receive_method'],
            $info['vend_receive_country'], $info['vend_receive_address'], $info['vend_receive_city'], $info['vend_receive_state'],
            $info['vend_receive_zip'], $info['vend_payment_terms'], $info['vend_fed_id'], $info['vend_payment_contact_name'],
            $info['vend_payment_contact_country'], $info['vend_payment_contact_address'], $info['vend_payment_contact_city'],
            $info['vend_payment_contact_state'], $info['vend_payment_contact_zip'], $info['vend_payment_primary_phone'],
            $info['vend_payment_secondary_phone'], $info['vend_payment_other_phone'], $info['vend_payment_fax']);
        }

        $ins_vendor = $vend_stmt->execute();

        if(!$ins_vendor) {
          dbLogSQLErr($dbconn);
        }
      }

      // now, work on employee IF it's an individual
      if($info['contactType'] === 'individual' && !empty($info['add_employee_check'])) {
        $hire_date = strtotime($info['emp_hire_date']);
        $personal_birthday = strtotime($info['emp_personal_birthday']);

        if($info['generate_pin_pw'] === '1') {
          $pin = generatePIN($pin_count);

          if($pin === false) {
            echo displayToast('error', 'PIN Code unable to generate. Could not find a unique PIN.', 'Unable to generate PIN');
          }

          $pw = generatePW($pin);
        } else {
          $pin = $info['emp_pin'];
          $pw = $info['emp_password'];
        }

        $emp_exist_qry = $dbconn->query("SELECT * FROM contact_employee WHERE contact_id = {$info['contactID']}");

        if($emp_exist_qry->num_rows > 0) {
          $emp_stmt = $dbconn->prepare('UPDATE contact_employee SET hire_date = ?, languages = ?, timezone = ?, shift = ?, facility = ?, department = ?, 
                            employee_status = ?, user_access = ?, username = ?, pin = ?, password = ?, pay_schedule = ?, federal_id = ?, personal_country = ?, 
                            personal_address = ?, personal_city = ?, personal_state = ?, personal_zip = ?,  personal_email = ?, personal_phone = ?, personal_birthday = ?, 
                            emergency_name = ?, emergency_relationship = ?, emergency_country = ?, emergency_address = ?,  emergency_city = ?, emergency_state = ?, 
                            emergency_zip = ?, emergency_pri_phone = ?, emergency_secondary_phone = ?, emergency_other_phone = ?, emergency_email = ? WHERE contact_id = ?');

          $emp_stmt->bind_param('issiisiisssissssssssisssssssssssi', $hire_date, $info['emp_languages'], $info['emp_timezone'], $info['emp_shift'],
            $info['emp_facility'], $info['emp_department'], $info['emp_status'], $info['emp_user_access'], $info['emp_username'],
            $pin, $pw, $info['emp_pay_schedule'], $info['emp_federal_id'], $info['emp_personal_country'],
            $info['emp_personal_address'], $info['emp_personal_city'], $info['emp_personal_state'], $info['emp_personal_zip'],
            $info['emp_personal_email'], $info['emp_personal_phone'], $personal_birthday, $info['emp_emergency_contact_name'],
            $info['emp_emergency_relationship'], $info['emp_emergency_contact_country'], $info['emp_emergency_contact_address'],
            $info['emp_emergency_contact_city'], $info['emp_emergency_contact_state'], $info['emp_emergency_contact_zip'],
            $info['emp_emergency_primary_phone'], $info['emp_emergency_secondary_phone'], $info['emp_emergency_other_phone'],
            $info['emp_emergency_email'], $info['contactID']);
        } else {
          $emp_stmt = $dbconn->prepare("INSERT INTO contact_employee (contact_id, created_by, hire_date, languages, timezone, shift, facility, department, employee_status, user_access, 
                              username, pin, password, pay_schedule, federal_id, personal_country, personal_address, personal_city, personal_state, personal_zip, 
                              personal_email, personal_phone, personal_birthday, emergency_name, emergency_relationship, emergency_country, emergency_address, 
                              emergency_city, emergency_state, emergency_zip, emergency_pri_phone, emergency_secondary_phone, emergency_other_phone, emergency_email, created) 
                              VALUES ({$info['contactID']}, {$_SESSION['userInfo']['id']}, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())");

          $emp_stmt->bind_param('issiisiisssissssssssisssssssssss', $hire_date, $info['emp_languages'], $info['emp_timezone'], $info['emp_shift'],
            $info['emp_facility'], $info['emp_department'], $info['emp_status'], $info['emp_user_access'], $info['emp_username'],
            $pin, $pw, $info['emp_pay_schedule'], $info['emp_federal_id'], $info['emp_personal_country'],
            $info['emp_personal_address'], $info['emp_personal_city'], $info['emp_personal_state'], $info['emp_personal_zip'],
            $info['emp_personal_email'], $info['emp_personal_phone'], $personal_birthday, $info['emp_emergency_contact_name'],
            $info['emp_emergency_relationship'], $info['emp_emergency_contact_country'], $info['emp_emergency_contact_address'],
            $info['emp_emergency_contact_city'], $info['emp_emergency_contact_state'], $info['emp_emergency_contact_zip'],
            $info['emp_emergency_primary_phone'], $info['emp_emergency_secondary_phone'], $info['emp_emergency_other_phone'],
            $info['emp_emergency_email']);
        }

        $ins_emp = $emp_stmt->execute();

        if(!$ins_emp) {
          dbLogSQLErr($dbconn);
        } else {
          $msg_body = "EagleEye account details updated below.<br /><br />Username: {$info['emp_username']}<br />Password: $pw<br />PIN: $pin";

          if(!empty(trim($info['email']))) {
            $send_to = $info['email'];
          } else {
            $send_to = $_SESSION['userInfo']['email'];
          }

          $mail->sendMessage($send_to, $_SESSION['userInfo']['email'], 'Updated User Account Information', $msg_body, false);
        }
      }

      if($update_contact && $ins_customer && $ins_vendor && $ins_emp) {
        if(!empty($info['company_notes'])) {
          $followup_date = sanitizeInput($notes['company_followup_date']);
          $followup_individual = sanitizeInput($notes['requested_of']);

          $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$info['company_notes']}', 'contact_note', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '{$info['contactID']}')");

          $note_id = $dbconn->insert_id;

          if(!empty($followup_date) && !empty($followup_individual)) {
            $followup = strtotime($followup_date);

            $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('company_followup', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'Company: {$info['company_name']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $note_id)");
          }
        }

        echo displayToast('success', 'Successfully saved contact.', 'Contact Saved');
      } else {
        header('error: true');
        dbLogSQLErr($dbconn);
      }
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
}