<?php
require '../includes/header_start.php';

function whatChanged($new, $old, $title, $date = false, $bool = false) {
  global $dbconn;

  if($date) {
    /** @var string $c_del_date Converts the delivery date to a string */
    $updated = date(DATE_TIME_ABBRV, strtotime($new));
    $new = strtotime($new);

    $new = (int)$new;
    $old = (int)$old;
  } else {
    if($title === 'Sales Bracket' || $title === 'Sample Bracket' || $title === 'Pre-Production Bracket' || $title === 'Door/Drawer Bracket' || $title === 'Main Bracket' ||
      $title === 'Custom Bracket' || $title === 'Shipping Bracket' || $title === 'Install Bracket') {

      $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = $new");
      $op = $op_qry->fetch_assoc();

      $updated = $op['job_title'];
    } elseif($title === 'Active Bracket Operations') {
      $updated = null;
    } else {
      $updated = "to $new";
    }
  }

  if($bool) {
    $updated = ($new === 0) ? 'Unpublished' : 'Published';

    $str = "$updated";
    $old = (bool)$old;
    $new = (bool)$new;
  } else {
    $str = "Updated $updated";
  }

  return ($old !== $new) ? "$title $str" : null;
}

switch($_REQUEST['action']) {
  case 'add_customer':
    /** Global information */
    $so_num = sanitizeInput($_REQUEST['so_num']);
    
    parse_str($_REQUEST['cu_data'], $cuData);

    $debug_out = json_encode($cuData, true);

    /** Retail information */
    $dealer_code = sanitizeInput($cuData['dealer_code']);

    $project_name = sanitizeInput($cuData['project_name']);
    $project_addr = sanitizeInput($cuData['project_addr']);
    $project_city = sanitizeInput($cuData['project_city']);
    $project_state = sanitizeInput($cuData['project_state']);
    $project_zip = sanitizeInput($cuData['project_zip']);
    $project_landline = sanitizeInput($cuData['project_landline']);

    $name_1 = sanitizeInput($cuData['name_1']);
    $cell_1 = sanitizeInput($cuData['cell_1']);
    $business_1 = sanitizeInput($cuData['business_1']);
    $email_1 = sanitizeInput($cuData['email_1']);

    $name_2 = sanitizeInput($cuData['name_2']);
    $cell_2 = sanitizeInput($cuData['cell_2']);
    $business_2 = sanitizeInput($cuData['business_2']);
    $email_2 = sanitizeInput($cuData['email_2']);

    $secondary_addr = sanitizeInput($cuData['secondary_addr']);
    $secondary_landline = sanitizeInput($cuData['secondary_landline']);
    $secondary_city = sanitizeInput($cuData['secondary_city']);
    $secondary_state = sanitizeInput($cuData['secondary_state']);
    $secondary_zip = sanitizeInput($cuData['secondary_zip']);

    $contractor_name = sanitizeInput($cuData['contractor_name']);
    $contractor_business = sanitizeInput($cuData['contractor_business_num']);
    $contractor_cell = sanitizeInput($cuData['contractor_cell_num']);
    $contractor_addr = sanitizeInput($cuData['contractor_addr']);
    $contractor_city = sanitizeInput($cuData['contractor_city']);
    $contractor_state = sanitizeInput($cuData['contractor_state']);
    $contractor_zip = sanitizeInput($cuData['contractor_zip']);
    $contractor_email = sanitizeInput($cuData['contractor_email']);

    $project_mgr = sanitizeInput($cuData['project_mgr']);
    $project_mgr_cell = sanitizeInput($cuData['project_mgr_cell']);
    $project_mgr_email = sanitizeInput($cuData['project_mgr_email']);

    $bill_to = sanitizeInput($cuData['bill_to']);
    $billing_contact = sanitizeInput($cuData['billing_contact']);
    $billing_landline = sanitizeInput($cuData['billing_landline']);
    $billing_cell = sanitizeInput($cuData['billing_cell']);
    $billing_addr = sanitizeInput($cuData['billing_addr']);
    $billing_city = sanitizeInput($cuData['billing_city']);
    $billing_state = sanitizeInput($cuData['billing_state']);
    $billing_zip = sanitizeInput($cuData['billing_zip']);

    $billing_account = sanitizeInput($cuData['billing_account']);
    $billing_routing = sanitizeInput($cuData['billing_routing']);
    $billing_cc_num = sanitizeInput($cuData['billing_cc_num']);
    $billing_cc_exp = sanitizeInput($cuData['billing_cc_exp']);
    $billing_cc_ccv = sanitizeInput($cuData['billing_cc_ccv']);

    $designer_id = sanitizeInput($cuData['designer']);

    if ($dbconn->query("INSERT INTO sales_order (so_num, dealer_code, project_name, project_addr, project_city, project_state, project_zip, 
    project_landline, name_1, cell_1, business_1, email_1, name_2, cell_2, business_2, email_2, secondary_addr, secondary_city, secondary_state, 
    secondary_zip, secondary_landline, contractor_name, contractor_business, contractor_cell, contractor_email, project_mgr, project_mgr_cell, 
    project_mgr_email, bill_to, billing_contact, billing_landline, billing_cell, billing_addr, billing_city, billing_state, billing_zip, 
    billing_account, billing_routing, billing_cc_num, billing_cc_exp, billing_cc_ccv, contractor_zip, contractor_state, contractor_city, 
    contractor_addr) VALUES ('$so_num', '$dealer_code', '$project_name', '$project_addr', '$project_city', '$project_state', '$project_zip',
    '$project_landline', '$name_1', '$cell_1', '$business_1', '$email_1', '$name_2', '$cell_2', '$business_2', '$email_2', '$secondary_addr',
    '$secondary_city', '$secondary_state', '$secondary_zip', '$secondary_landline', '$contractor_name', '$contractor_business', '$contractor_cell',
    '$contractor_email', '$project_mgr', '$project_mgr_cell', '$project_mgr_email', '$bill_to', '$billing_contact', '$billing_landline',
    '$billing_cell', '$billing_addr', '$billing_city', '$billing_state', '$billing_zip', '$billing_account', '$billing_routing',
    '$billing_cc_num', '$billing_cc_exp', '$billing_cc_ccv', '$contractor_zip', '$contractor_state', '$contractor_city', '$contractor_addr')")) {

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

      $dbconn->query("INSERT INTO rooms (so_parent, room, room_name, product_type, individual_bracket_buildout, order_status, sales_bracket, sample_bracket,
      preproduction_bracket, doordrawer_bracket, main_bracket, custom_bracket, install_bracket, shipping_bracket, sales_published) 
      VALUES ('$so_num', 'A', 'Intake (Auto-Generated)', 'C', '$ind_bracket_final', '#', '{$starting_ops['Sales']}', '{$starting_ops['Sample']}', 
      '{$starting_ops['Pre-Production']}', '{$starting_ops['Drawer & Doors']}', '{$starting_ops['Main']}', '{$starting_ops['Custom']}', 
      '{$starting_ops['Installation']}', '{$starting_ops['Shipping']}', TRUE);");

      $room_id = $dbconn->insert_id;

      $dbconn->query("INSERT INTO op_queue (room_id, operation_id, notes, created) VALUES ('$room_id', '{$starting_ops['Sales']}', 'Auto-generated.', UNIX_TIMESTAMP())");

      echo displayToast('success', 'Successfully created new project.', 'New Project Created');
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
  case 'save_so':
    $note_id = null;
    $inquiry_id = null;

    $so_id = sanitizeInput($_REQUEST['so_num']);

    parse_str($_REQUEST['formInfo'], $info);

    foreach($info AS $k => $v) {
      $info[$k] = sanitizeInput($v);
    }

    $so_qry = $dbconn->query("SELECT * FROM sales_order WHERE id = $so_id");
    $so = $so_qry->fetch_assoc();

    $so_num = $so['so_num'];

    $changed[] = whatChanged($info['project_name'], $so['project_name'], 'Project Name');
    $changed[] = whatChanged($info['project_addr'], $so['project_addr'], 'Project Address');
    $changed[] = whatChanged($info['project_city'], $so['project_city'], 'Project City');
    $changed[] = whatChanged($info['project_state'], $so['project_state'], 'Project State');
    $changed[] = whatChanged($info['project_zip'], $so['project_zip'], 'Project Zip');
    $changed[] = whatChanged($info['project_landline'], $so['project_landline'], 'Project Landline');

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

    if(!empty($info['project_notes'])) {
      $followup_date = sanitizeInput($notes['project_followup_date']);
      $followup_individual = sanitizeInput($notes['project_requested_of']);

      $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('{$info['project_notes']}', 'so_inquiry', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '{$so['id']}')");

      $note_id = $dbconn->insert_id;

      if(!empty($followup_date) && !empty($followup_individual)) {
        $followup = strtotime($followup_date);

        $dbconn->query("INSERT INTO cal_followup (type, timestamp, user_to, user_from, notes, followup_time, type_id) VALUES ('so_inquiry', UNIX_TIMESTAMP(), '$followup_individual', '{$_SESSION['userInfo']['id']}', 'Company: {$info['company_name']}, Inquiry by: {$_SESSION['userInfo']['name']}', $followup, $note_id)");
      }
    }

    if($dbconn->query("UPDATE sales_order SET project_name = '{$info['project_name']}', project_addr = '{$info['project_addr']}', project_city = '{$info['project_city']}',
    project_state = '{$info['project_state']}', project_zip = '{$info['project_zip']}', project_landline = '{$info['project_landline']}' WHERE so_num = '$so_num'")) {
      if(!empty(array_values(array_filter($changed)))) {
        $c_note = '<strong>UPDATE PERFORMED</strong><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $c_note .= implode(', ', array_values(array_filter($changed)));

        $stmt = $dbconn->prepare("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES (?, 'so_note_log', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, ?);");
        $stmt->bind_param('si', $c_note, $so['id']);
        $stmt->execute();
        $stmt->close();
      }

      echo displayToast('success', "Successfully updated Sales Order information for $so_num.", 'Updated Information');
    } else {
      dbLogSQLErr($dbconn);
    }

    break;
  case 'get_so_list':
    $output = array();
    $i = 0;

    if((bool)$_SESSION['userInfo']['dealer']) {
      $dealer_where = "WHERE d.dealer_id LIKE '{$_SESSION['userInfo']['dealer_code']}%'";
    } else {
      $dealer_where = null;
    }

    //$so_qry = $dbconn->query("SELECT sales_order.*, dealers.dealer_name, dealers.contact FROM sales_order LEFT JOIN dealers ON sales_order.dealer_code = dealers.dealer_id $dealer_where");
    $so_qry = $dbconn->query("SELECT so.*, c.company_name, c.first_name, c.last_name FROM sales_order so LEFT JOIN dealers d ON so.dealer_code = d.dealer_id LEFT JOIN contact c ON d.id = c.dealer_id $dealer_where");

    if($so_qry->num_rows > 0) {
      while($so = $so_qry->fetch_assoc()) {
        $output['data'][$i][] = $so['so_num'];
        $output['data'][$i][] = $so['project_name'];
        $output['data'][$i][] = $so['contact'];
        $output['data'][$i][] = "{$so['company_name']}: {$so['first_name']} {$so['last_name']}";
        $output['data'][$i]['DT_RowId'] = $so['so_num'];

        $i += 1;
      }
    } else {
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "---------";
      $output['data'][$i][] = "---------";
      $output['data'][$i][] = "No SO's to list";
      $output['data'][$i][] = "";
      $output['data'][$i][] = "";
      $output['data'][$i][] = "";

      $i += 1;
    }

    echo json_encode($output);

    break;
  case 'reply_inquiry':
    $reply_id = sanitizeInput($_REQUEST['id']);
    $reply_text = sanitizeInput($_REQUEST['reply']);

    if(!empty($reply_text)) {
      if($dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$reply_text', 'inquiry_reply', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, '$reply_id')")) {
        echo displayToast("success", "Replied to inquiry.", "Inquiry Replied");
      } else {
        dbLogSQLErr($dbconn);
      }
    } else {
      echo displayToast("error", "No message to reply with.", "Unable to reply to Inquiry");
    }

    break;
  case 'get_sales_list':
    $output = array();
    $i = 0;

    $hidden_enabled = (bool)$_REQUEST['hidden'];

    $prev_so = null;
    $prev_room = null;
    $prev_seq = null;

    if((bool)$_SESSION['userInfo']['dealer']) {
      $dealer_where = "WHERE dealers.dealer_id LIKE '{$_SESSION['userInfo']['dealer_code']}%'";
    } else {
      $dealer_where = null;
    }

    $so_qry = $dbconn->query("SELECT so.id AS sID, so.*, r.id AS rID, d.dealer_id AS dealerIDCode, r.*, d.*, c.* FROM sales_order so
        LEFT JOIN rooms r ON so.so_num = r.so_parent LEFT JOIN dealers d ON so.dealer_code = d.dealer_id
        LEFT JOIN contact c ON d.id = c.dealer_id $dealer_where ORDER BY so_num ASC, room ASC, iteration ASC;");

    $usr_qry = $dbconn->query("SELECT hide_sales_list_values FROM user WHERE id = {$_SESSION['userInfo']['id']};");
    $usr = $usr_qry->fetch_assoc();
    $hidden = json_decode($usr['hide_sales_list_values']);

    if($so_qry->num_rows > 0) {
      while($so = $so_qry->fetch_assoc()) {
        if(in_array($so['rID'], $hidden)) {
          // if it's hidden, tell the system it's showing the hidden button
          $btn_classes = 'btn-primary-outline sales_list_hidden';
          $btn_icon = 'zmdi-eye';
        } else {
          $btn_classes = 'btn-primary sales_list_visible';
          $btn_icon = 'zmdi-eye-off';
        }

        if(!in_array($so['rID'], $hidden) || $hidden_enabled) {
          $contact = (!empty($so['dealerIDCode'])) ? "{$so['dealerIDCode']}: {$so['first_name']} {$so['last_name']} ({$so['company_name']})" : "<span style='color: #FF0000 !important;'>A00: None Assigned</span>";

          switch($so['order_status']) {
            case '-':
              $order_status = "Lost";
              break;

            case '#':
              $order_status = "Quote";
              break;

            case '$':
              $order_status = "Job";
              break;

            case '+':
              $order_status = "Completed";
              break;


            case 'A':
              $order_status = "Add-on";
              break;

            case 'W':
              $order_status = "Warranty";
              break;

            default:
              $order_status = "<span style='color: #FF0000 !important;'>None</span>";
              break;
          }

          if($prev_so !== $so['so_num']) {
            $output['data'][$i][] = "";
            $output['data'][$i][] = "<strong>{$so['so_num']}</strong>";
            $output['data'][$i][] = "<strong>{$so['project_name']}</strong>";
            $output['data'][$i][] = $contact;
            $output['data'][$i][] = "&nbsp;";
            $output['data'][$i][] = "{$so['dealer_id']}";
            $output['data'][$i][] = "{$so['sID']}";
            $output['data'][$i]['DT_RowId'] = $so['so_num'];

            $i++;

            $room_iteration = (!empty($so['room']) && !empty($so['iteration'])) ? "{$so['room']}{$so['iteration']}" : "<span style='color: #FF0000 !important;'>None</span>";
            $room_name = (!empty($so['room_name'])) ? "{$so['room_name']}" : "<span style='color: #FF0000 !important;'>None</span>";

            $iteration = explode(".", number_format($so['iteration'], 2));
            $prev_seq = $iteration[0];

            $output['data'][$i][] = "<button class='$btn_classes' data-identifier='{$so['rID']}'><i class='zmdi $btn_icon'></i></button>";
            $output['data'][$i][] = "<span style='padding-left:20px;'>$room_iteration</span>";
            $output['data'][$i][] = "<span style='padding-left:20px;'>$room_name</span>";
            $output['data'][$i][] = $contact;
            $output['data'][$i][] = $order_status;
            $output['data'][$i][] = "{$so['dealer_id']}";
            $output['data'][$i][] = "{$so['rID']}";
            $output['data'][$i]['DT_RowId'] = $so['so_num'];

            $prev_room = $so['room'];
            $prev_so = $so['so_num'];
          } else {
            $iteration = explode(".", number_format($so['iteration'], 2));

            if($prev_room !== $so['room']) {
              $prev_room = $so['room'];
              $room_def = "{$so['room']}{$so['iteration']}";
            } else {
              if($iteration[0] !== $prev_seq) {
                $prev_seq = $iteration[0];
                $final_iteration = $so['iteration'];
                $final_padding = '8';
              } else {
                $final_iteration = ".{$iteration[1]}";
                $final_padding = '15';
              }

              $room_def = "<span style='padding-left:{$final_padding}px;'>$final_iteration</span>";
            }

            $output['data'][$i][] = "<button class='$btn_classes' data-identifier='{$so['rID']}'><i class='zmdi $btn_icon'></i></button>";
            $output['data'][$i][] = "<span style='padding-left:20px;'>$room_def</span>";
            $output['data'][$i][] = "<span style='padding-left:20px;'>{$so['room_name']}</span>";
            $output['data'][$i][] = $contact;
            $output['data'][$i][] = $order_status;
            $output['data'][$i][] = "{$so['dealer_id']}";
            $output['data'][$i][] = "{$so['rID']}";
            $output['data'][$i]['DT_RowId'] = $so['so_num'];
          }

          $i++;
        }
      }
    } else {
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "No SO's to list";
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "---------";

      $i++;
    }


    echo json_encode($output);

    break;
  case 'generate_code':
    $key = md5(microtime());
    $so = sanitizeInput($_REQUEST['so_num']);

    $dbconn->query("UPDATE sales_order SET access_code = '$key' WHERE so_num = '$so'");

    echo $key;

    break;
}