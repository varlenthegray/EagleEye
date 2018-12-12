<?php
require '../includes/header_start.php';
require '../includes/classes/mail_handler.php';
//require '../includes/functions.php';

outputPHPErrs();

$mail = new \MailHandler\mail_handler();

function dbOrQry($post_field, $col) {
  return (!empty($post_field)) ? " AND LOWER($col) LIKE LOWER('%$post_field%')" : null;
}

switch($_REQUEST['action']) {
  case 'save_contact':
    $errors = null;
    $dealer_entry_id = 'NULL';

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

    $insert_SQL = "INSERT INTO contact (type, created_by, company_name, first_name, last_name, email, cell, line_2, shipping_first_name, shipping_last_name,
        shipping_addr, shipping_city, shipping_state, shipping_zip, billing_first_name, billing_last_name, billing_addr, billing_city, billing_state, billing_zip, creation, dealer_id) 
        VALUES ('$type', '{$_SESSION['userInfo']['id']}', '$company_name', '$first_name', '$last_name', '$email', '$cell', '$phone_2', '$shipping_first_name', '$shipping_last_name', '$shipping_addr',
        '$shipping_city', '$shipping_state', '$shipping_zip', '$billing_first_name', '$billing_last_name', '$billing_addr', '$billing_city', '$billing_state',
        '$billing_zip', UNIX_TIMESTAMP(), $dealer_entry_id)";

    if(!empty($search_multiple)) {
      $personal_filter = ((int)$type === 8) ? "AND created_by = '{$_SESSION['userInfo']['id']}'" : null;

      $contact_qry = $dbconn->query("SELECT * FROM contact WHERE ($search_multiple) AND type = '$type' $personal_filter");

      if($contact_qry->num_rows === 0) {
        if(!empty($dealer_code)) {
          $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '%$dealer_code%'");

          if($dealer_qry->num_rows > 0) {
            echo displayToast("error", "Dealer code already exists in the system.", "Dealer Exists");
          } else {
            if($dbconn->query($insert_SQL)) {
              $contact_id = $dbconn->insert_id;

              $dbconn->query("INSERT INTO dealers (dealer_id, multiplier, contact_id) VALUES ('$dealer_code', '0.419', $contact_id");

              $dealer_entry_id = $dbconn->insert_id;

              $dbconn->query("UPDATE contact SET dealer_id = $dealer_entry_id");

              echo displayToast("success", "Successfully created dealer.", "Dealer Created");
            }
          }
        } else {
          if($dbconn->query($insert_SQL)) {
            echo displayToast("success", "Successfully created contact.", "Contact Created");
          } else {
            dbLogSQLErr($dbconn);
          }
        }
      } else {
        echo displayToast("error", "Contact already exists in the system.", "Contact Exists");
      }
    } else {
      echo displayToast("error", "Unable to create empty contact.", "Unable to Create");
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
    $so_id = sanitizeInput($_REQUEST['so']);

    parse_str($_REQUEST['formInfo'], $info);

    $contact_role = !empty($info['custom_association']) ? sanitizeInput($info['custom_contact_role']) : sanitizeInput($info['contact_role']);

    $so_qry = $dbconn->query("SELECT * FROM sales_order WHERE id = $so_id");

    if($so_qry->num_rows === 1) {
      $so = $so_qry->fetch_assoc();

      if(!empty($con_id)) {
        $so_contact_qry = $dbconn->query("SELECT * FROM contact_associations WHERE contact_id = '$con_id' AND so_id = {$so['id']}");

        if($so_contact_qry->num_rows === 0) {
          if($dbconn->query("INSERT INTO contact_associations (so_id, contact_id, assigned_by, created_on, associated_as) VALUES ('{$so['id']}', '$con_id', {$_SESSION['userInfo']['id']}, UNIX_TIMESTAMP(), '$contact_role')")) {
            $contact_qry = $dbconn->query("SELECT c.*, a.associated_as FROM contact c LEFT JOIN contact_associations a on c.id = a.contact_id WHERE c.id = $con_id");
            $contact = $contact_qry->fetch_assoc();

            echo json_encode($contact);
          } else {
            http_response_code(400);

            dbLogSQLErr($dbconn);
          }
        } else {
          http_response_code(400);

          echo displayToast('info', 'Contact has already been assigned to project.', 'Contact Already Assigned');
        }
      } else {
        http_response_code(400);

        echo displayToast('error', 'Unable to find contact information. Please refresh and try again.', 'Unable to Find Contact');
      }
    } else {
      http_response_code(400);

      echo displayToast('error', 'Unable to properly identify SO number. Please refresh and try again.', 'Unable to Obtain SO');
    }

    break;
  case 'remove_contact_project':
    // TODO: Remove duplication between remove and add contact
    $con_id = sanitizeInput($_REQUEST['contact_id']);
    $so_id = sanitizeInput($_REQUEST['so']);

    $so_qry = $dbconn->query("SELECT * FROM sales_order WHERE id = $so_id");

    if($so_qry->num_rows === 1) {
      $so = $so_qry->fetch_assoc();

      if(!empty($con_id)) {
        $so_contact_qry = $dbconn->query("SELECT * FROM contact_associations WHERE contact_id = '$con_id' AND so_id = '{$so['id']}'");

        if($so_contact_qry->num_rows === 1) {
          $so_contact = $so_contact_qry->fetch_assoc();

          $dbconn->query("DELETE FROM contact_associations WHERE id = '{$so_contact['id']}'");

          echo displayToast('success', 'Successfully removed contact from project.', 'Contact Removed');
        } else {
          http_response_code(400);

          echo displayToast('info', 'Contact has already been deleted from project.', 'Contact Already Removed');
        }
      } else {
        http_response_code(400);

        echo displayToast('error', 'Unable to identify contact to remove. Please refresh and try again.', 'Unable to Find Contact');
      }
    } else {
      http_response_code(400);

      displayToast('error', 'Unable to properly identify SO number. Please refresh and try again.', 'Unable to Obtain SO');
    }

    break;
}