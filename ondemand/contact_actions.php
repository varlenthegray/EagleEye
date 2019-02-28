<?php
require '../includes/header_start.php';
require '../includes/classes/mail_handler.php';
//require '../includes/functions.php';

//outputPHPErrs();

$mail = new \MailHandler\mail_handler();

function dbOrQry($post_field, $col) {
  return !empty($post_field) ? " AND LOWER($col) LIKE LOWER('%$post_field%')" : null;
}

switch($_REQUEST['action']) {
  case 'save_contact':
    parse_str($_REQUEST['formInfo'], $info);

    foreach($info AS $key => $value) {
      $info[$key] = sanitizeInput($value);
    }

    $sql_search = null;

    if(!empty(trim($info['email']))) {
      $sql_search .= "OR LOWER(email) = 'LOWER('{$info['email']}')'";
    }

    if(!empty(trim($info['cell']))) {
      $sql_search .= " OR cell = '{$info['cell']}'";
    }

    $existing_qry = $dbconn->query("SELECT * FROM contact WHERE 
                            (LOWER(first_name) = LOWER('{$info['first_name']}') AND LOWER(last_name) = LOWER('{$info['last_name']}')) $sql_search");

    if($existing_qry->num_rows === 0) {
      if(!empty(trim($info['first_name'])) && !empty(trim($info['last_name']))) {
        $insert_success = $dbconn->query("INSERT INTO contact (created_by, first_name, last_name, email, cell, line_2, shipping_addr, shipping_city, shipping_state, 
                     shipping_zip, billing_addr, billing_city, billing_state, billing_zip, creation) 
                     VALUES ({$_SESSION['userInfo']['id']}, '{$info['first_name']}', '{$info['last_name']}', '{$info['email']}', '{$info['cell']}',
                             '{$info['phone_2']}', '{$info['shipping_addr']}', '{$info['shipping_city']}', '{$info['shipping_state']}', '{$info['shipping_zip']}', 
                             '{$info['billing_addr']}', '{$info['billing_city']}', '{$info['billing_state']}', '{$info['billing_zip']}', UNIX_TIMESTAMP())");

        if($insert_success) {
          echo displayToast('success', 'Successfully created contact.', 'Contact Created');
        } else {
          dbLogSQLErr($dbconn);
        }
      } else {
        echo displayToast('error', 'Contact must have both a first name and a last initial.', 'Unable to Create');
      }
    } else {
      echo displayToast('error', 'Unable to create contact. One already exists.', 'Contact Exists');
    }

    break;
  case 'update_contact':
    $errors = null;

    $id = sanitizeInput($_REQUEST['id']);

    $type = sanitizeInput($_REQUEST['contact_type']);
    $dealer_code = sanitizeInput($_REQUEST['dealer_code']);
    $first_name = sanitizeInput($_REQUEST['first_name']);
    $last_name = sanitizeInput($_REQUEST['last_name']);
    $company_name = sanitizeInput($_REQUEST['company_name']);
    $email = sanitizeInput($_REQUEST['email']);
    $cell = sanitizeInput($_REQUEST['cell']);
    $phone_2 = sanitizeInput($_REQUEST['phone_2']);
    $shipping_first_name = sanitizeInput($_REQUEST['shipping_first_name']);
    $shipping_last_name = sanitizeInput($_REQUEST['shipping_last_name']);
    $shipping_addr = sanitizeInput($_REQUEST['shipping_addr']);
    $shipping_city = sanitizeInput($_REQUEST['shipping_city']);
    $shipping_state = sanitizeInput($_REQUEST['shipping_state']);
    $shipping_zip = sanitizeInput($_REQUEST['shipping_zip']);
    $billing_first_name = sanitizeInput($_REQUEST['billing_first_name']);
    $billing_last_name = sanitizeInput($_REQUEST['billing_last_name']);
    $billing_addr = sanitizeInput($_REQUEST['billing_addr']);
    $billing_city = sanitizeInput($_REQUEST['billing_city']);
    $billing_state = sanitizeInput($_REQUEST['billing_state']);
    $billing_zip = sanitizeInput($_REQUEST['billing_zip']);

    $search_multiple = dbOrQry($first_name, 'first_name');
    $search_multiple .= dbOrQry($last_name, 'last_name');
    $search_multiple .= dbOrQry($company_name, 'company_name');
    $search_multiple .= dbOrQry($phone_2, 'cell');
    $search_multiple .= dbOrQry($email, 'email');

    $search_multiple = ltrim($search_multiple, ' AND ');

    if((int)$type === 2) {
      // TODO: This is broken; it doesn't select the right dealer code or ID
      $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '%$dealer_code%'");

      if($dealer_qry->num_rows === 0) {
        $dbconn->query("INSERT INTO dealers (dealer_id, multiplier, ship_zone) VALUES ('$dealer_code', '0.419', 'A')");

        $dealer_id = $dbconn->insert_id;
      } else {
        $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '%$dealer_code%'");
        $dealer = $dealer_qry->fetch_assoc();

        $dealer_id = $dealer['id'];
      }
    } else {
      $dealer_id = 'NULL';
    }

    if($dbconn->query("UPDATE contact SET type = $type, company_name = '$company_name', first_name = '$first_name', last_name = '$last_name', email = '$email',
        cell = '$cell', line_2 = '$phone_2', shipping_first_name = '$shipping_first_name', shipping_last_name = '$shipping_last_name', shipping_addr = '$shipping_addr',
        shipping_city = '$shipping_city', shipping_state = '$shipping_state', shipping_zip = '$shipping_zip', billing_first_name = '$billing_first_name',
        billing_last_name = '$billing_last_name', billing_addr = '$billing_addr', billing_city = '$billing_city', billing_state = '$billing_state', billing_zip = '$billing_zip', 
        dealer_id = $dealer_id WHERE id = $id")) {
      echo displayToast("success", "Updated contact $first_name $last_name", "Updated Contact");
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
  case 'delete_contact':
    $id = sanitizeInput($_REQUEST['id']);

    if($bouncer->validate('delete_contact')) {
      if($dbconn->query("DELETE FROM contact WHERE id = '$id'")) {
        echo displayToast("success", "Successfully removed contact.", "Contact Removed");
      } else {
        echo displayToast("error", "Unable to remove contact. Verify that no projects have this contact.", "Unable to Remove Contact");
      }
    } else {
      echo displayToast("error", "You are unable to delete contacts.", "Unable to Delete");
    }

    break;

  // adds a contact to a project/so
  case 'add_contact_project':
    $con_id = sanitizeInput($_REQUEST['contact_id']);
    $type_id = sanitizeInput($_REQUEST['type_id']);
    $type = sanitizeInput($_REQUEST['type']);

    parse_str($_REQUEST['formInfo'], $info);

    $contact_role = !empty($info['custom_association']) ? sanitizeInput(ucwords($info['custom_contact_role'])) : sanitizeInput($info['contact_role']);

    if(!empty($con_id)) {
      if($type === 'organization') {
        $assoc_qry = $dbconn->query("SELECT * FROM contact_to_contact ctc WHERE ctc.contact_from = $type_id AND ctc.contact_to = $con_id AND associated_as = '$contact_role'");
      } elseif($type === 'sales order') {
        $assoc_qry = $dbconn->query("SELECT * FROM contact_to_sales_order ctso WHERE sales_order_id = $type_id AND contact_id = $con_id AND associated_as = '$contact_role'");
      }

      if($assoc_qry->num_rows === 0) {
        $contact_qry = $dbconn->query("SELECT * FROM contact WHERE id = $con_id");
        $contact = $contact_qry->fetch_assoc();

        $contact['associated_as'] = $contact_role;

        if($type === 'organization') {
          $dbconn->query("INSERT INTO contact_to_contact (created_by, contact_from, contact_to, associated_as, created_on) 
                        VALUES ({$_SESSION['userInfo']['id']}, $type_id, $con_id, '$contact_role', UNIX_TIMESTAMP())");
        } elseif($type === 'sales order') {
          $dbconn->query("INSERT INTO contact_to_sales_order (created_by, contact_id, sales_order_id, associated_as, created_on) 
                        VALUES ({$_SESSION['userInfo']['id']}, $con_id, $type_id, '$contact_role', UNIX_TIMESTAMP())");
        }

        $contact['uID'] = $dbconn->insert_id;

        if(empty($dbconn->error)) {
          echo json_encode($contact);
        } else {
          http_response_code(400);
          dbLogSQLErr($dbconn);
        }
      } else {
        http_response_code(401);

        echo displayToast('info', 'Contact has already been assigned to project.', 'Contact Already Assigned');
      }
    } else {
      http_response_code(402);

      echo displayToast('error', 'Unable to find contact information. Please refresh and try again.', 'Unable to Find Contact');
    }

    break;
  case 'remove_contact_project':
    // TODO: Remove duplication between remove and add contact
    $id = sanitizeInput($_REQUEST['id']);
    $type = sanitizeInput($_REQUEST['type']); // TODO: TOTALLY CONFUSED, THIS IS PROJECT ONE TIME AND SALES ORDER ANOTHER!

    if(!empty($id)) {
      if($type === 'contact') {
        $del = $dbconn->query("DELETE FROM contact_to_contact WHERE id = $id");
      } elseif($type === 'project') {
        $del = $dbconn->query("DELETE FROM contact_to_sales_order WHERE id = $id");
      }

      if($del) {
        echo displayToast('success', 'Successfully removed contact from project.', 'Contact Removed');
      } else {
        http_response_code(401);
        dbLogSQLErr($dbconn);
      }
    } else {
      http_response_code(400);

      echo displayToast('error', 'Unable to identify contact to remove. Please refresh and try again.', 'Unable to Find Contact');
    }


    break;
}