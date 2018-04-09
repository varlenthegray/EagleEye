<?php
require '../includes/header_start.php';
require ("../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/
require ("../includes/classes/queue.php");

//outputPHPErrs();

use Carbon\Carbon; // prep carbon

$queue = new \Queue\queue();

if((bool)$_SESSION['userInfo']['dealer']) {
  $dealer = DEALER;

  $dealer_filter = "AND dealer_code LIKE '$dealer%'";
} else {
  $dealer_filter = null;
}

function displayOrderQuote ($type) {
  global $dbconn;
  global $dealer_filter;

  $output = array();
  $i = 0;

  $prev_so = null;
  $prev_room = null;
  $prev_seq = null;

  $room_qry = $dbconn->query("SELECT * FROM sales_order so LEFT JOIN rooms r ON so.so_num = r.so_parent WHERE r.order_status = '$type' $dealer_filter ORDER BY so_num, room, iteration ASC;");

  if($room_qry->num_rows > 0) {
    while($room = $room_qry->fetch_assoc()) {
      if($type === '#') {
        $bracket1 = 'sales_bracket';
        $bracket2 = 'sample_bracket';
      } else {
        $bracket1 = 'preproduction_bracket';
        $bracket2 = 'main_bracket';
      }

      if((bool)$room['sales_published']) {
        $sales_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room[$bracket1]}'");
        $sales_op = $sales_op_qry->fetch_assoc();

        if((bool)$_SESSION['userInfo']['dealer'] && $type === '$') {
          $sales_op_display = 'In Production';
        } else {
          $sales_op_display = (!empty($sales_op)) ? "{$sales_op['op_id']}: {$sales_op['job_title']}" : "None";
        }
      } else {
        $sales_op_display = "";
      }

      if((bool)$room['sample_published']) {
        $sample_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room[$bracket2]}'");
        $sample_op = $sample_op_qry->fetch_assoc();

        if((bool)$_SESSION['userInfo']['dealer'] && $type === '$') {
          $sales_op_display = 'In Production';
        } else {
          $sample_op_display = (!empty($sample_op)) ? "{$sample_op['op_id']}: {$sample_op['job_title']}" : "None";
        }
      } else {
        $sample_op_display = "";
      }

      $iteration = explode(".", number_format($room['iteration'], 2));

      if($room['room'] === $prev_room && $room['so_num'] === $prev_so && $iteration[0] === $prev_seq) {
        $indent = "margin-left:55px";
        $addl_room_info = ".{$iteration[1]}";
      } else {
        $indent = "margin-left:40px";
        $addl_room_info = "{$room['room']}{$room['iteration']}";
      }

      $rowID = (empty($room['so_num'])) ? $room['project_name'] : $rowID = $room['so_num'];

      if($prev_so !== $room['so_num']) {
        $output['data'][$i][] = $room['so_num'];
        $output['data'][$i][] = "<strong>{$room['dealer_code']}_{$room['project_name']}</strong>";
        $output['data'][$i][] = null;
        $output['data'][$i][] = null;
        $output['data'][$i]['DT_RowId'] = $rowID;

        $i += 1;
      }

      $output['data'][$i][] = $room['so_parent'];
      $output['data'][$i][] = "<span style='$indent'>$addl_room_info-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}-{$room['room_name']}</span>";
      $output['data'][$i][] = $sales_op_display;
      $output['data'][$i][] = $sample_op_display;
      $output['data'][$i]['DT_RowId'] = $rowID;

      $prev_room = $room['room'];
      $prev_seq = $iteration[0];
      $prev_so = $room['so_num'];

      $i += 1;
    }
  } else {
    $output['data'][$i][] = "";
    $output['data'][$i][] = "No rooms found.";
    $output['data'][$i][] = "";
    $output['data'][$i][] = "";
  }


  return $output;
}

switch($_REQUEST['action']) {
  /** Dashboard */
  case "display_quotes":
    echo json_encode(displayOrderQuote('#'));

    break;
  case "display_orders":
    echo json_encode(displayOrderQuote('$'));

    break;
  case 'display_ind_active_jobs':
    $output = array();
    $i = 0;

    $self_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, rooms.room, rooms.so_parent, rooms.room_name, rooms.iteration,
    op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.urgent, op_queue.subtask, operations.responsible_dept, op_queue.start_time, op_queue.room_id, rooms.order_status
    FROM op_queue
    LEFT JOIN operations ON op_queue.operation_id = operations.id
    LEFT JOIN rooms ON op_queue.room_id = rooms.id
    WHERE active_employees LIKE '%\"{$_SESSION['shop_user']['id']}\"%' AND active = TRUE;");

    if($self_qry->num_rows > 0) {
      while($self = $self_qry->fetch_assoc()) {
        if(!empty($self['subtask'])) {
          $subtask = " ({$self['subtask']})";
        }

        if($self['job_title'] === 'Non-Billable' || $self['job_title'] === 'On The Fly'|| $self['job_title'] === 'Break') {
          $pause_btn = null;
          $margin = '12px';
          $zeroed = true;
          $notes_btn = null;
        } else {
          $pause_btn = "<button class='btn waves-effect btn-primary pull-left pause-operation' id='{$self['id']}'><i class='zmdi zmdi-pause'></i></button>";
          $notes_btn = "<button class='btn waves-effect btn-primary pull-left op-notes' style='margin-left:4px;' id='{$self['id']}'><i class='fa fa-sticky-note-o'></i></button>";
          $margin = '4px';
          $zeroed = false;
        }

        $start_time = ($self['resumed_time'] === null) ? date(TIME_ONLY, $self['start_time']) : date(TIME_ONLY, $self['resumed_time']);

        if($self['job_title'] === 'Non-Billable' || $self['job_title'] === 'On The Fly' || $self['job_title'] === 'Break') {
          $so = "---------";
          $room = "---------";
        } elseif($self['job_title'] === 'Honey Do') {
          $so = $self['room_id'];
          $room = "---------";
        } else {
          $so = "{$self['so_parent']}{$self['room']}-{$self['iteration']}";

          switch($self['order_status']) {
            case 'A':
              $status = '<strong>Add-on</strong>';
              break;

            case 'W':
              $status = '<strong>Warranty</strong>';
              break;

            default:
              $status = null;
              break;
          }

          $room = "{$self['room_name']}<span class='pull-right'>$status</span>";
        }

        $time = Carbon::createFromTimestamp($self['start_time']); // grab the carbon timestamp

        $output['data'][$i][] = "$pause_btn <button class='btn waves-effect btn-primary pull-left complete-operation' id='{$self['id']}' style='margin-left:$margin;'><i class='zmdi zmdi-stop'></i></button> $notes_btn";
        $output['data'][$i][] = $so;
        $output['data'][$i][] = $room;
        $output['data'][$i][] = $self['responsible_dept'];
        $output['data'][$i][] = "{$self['op_id']}: {$self['job_title']} $subtask";
        $output['data'][$i][] = $start_time;
        $output['data'][$i][] = $time->diffForHumans(null,true); // obtain the difference in readable format for humans!
        $output['data'][$i]['DT_RowId'] = (!$zeroed) ?  $self['so_parent'] : null;

        $i += 1;
      }
    } else {
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "---------";
      $output['data'][$i][] = "---------";
      $output['data'][$i][] = "No current active operations";
      $output['data'][$i][] = "";
      $output['data'][$i][] = "";
      $output['data'][$i][] = "";

      $i += 1;
    }

    echo json_encode($output);

    break;
  case 'display_ind_job_queue':
    $queue = urldecode(sanitizeInput($_REQUEST['queue']));

    $output = array();
    $i = 0;

    if($queue === 'self') {
      $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '{$_SESSION['userInfo']['id']}'");
      $usr = $usr_qry->fetch_assoc();

      $usr_ops = json_decode($usr['ops_available']);

      if(!empty($usr_ops)) {
        $orderby = "FIELD(operations.id,";
        $filter = 'AND (';

        foreach($usr_ops AS $op) {
          $orderby .= "$op,";
          $filter .= "operations.id = $op OR ";
        }

        $orderby = rtrim($orderby, ",");
        $orderby .= ")";

        $filter = rtrim($filter, " OR ");
        $filter .= ")";

        $usr_ops_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, rooms.room, rooms.so_parent, rooms.room_name, rooms.iteration,
                op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.urgent, rooms.product_type, rooms.days_to_ship, rooms.order_status FROM op_queue
                JOIN operations ON op_queue.operation_id = operations.id JOIN rooms ON op_queue.room_id = rooms.id WHERE completed = FALSE AND published = TRUE 
                AND (active_employees NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR active_employees IS NULL) AND operations.job_title != 'Honey Do' $filter 
                ORDER BY op_queue.urgent, $orderby, FIELD(rooms.days_to_ship,'R','N','Y','G'), op_queue.created ASC;");

        while($usr_ops = $usr_ops_qry->fetch_assoc()) {
          if($usr_ops['rework']) {
            $rework = "(Rework)";
          } else {
            $rework = null;
          }

          $release_date = date(DATE_DEFAULT, $usr_ops['created']);

          if(!empty($usr_ops['assigned_to'])) {
            $assigned_usrs = json_decode($usr_ops['assigned_to']);

            $name = null;

            foreach($assigned_usrs as $usr) {
              $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$usr'");
              $usr = $usr_qry->fetch_assoc();

              $name .= $usr['name'] . ", ";
            }

            $assignee = substr($name, 0, -2);
          } else {
            $assignee = "&nbsp;";
          }

          // TODO: Update urgent code so that it selects that first in the SQL query

//                    if(empty($usr_ops['urgent'])) {
//                        $pt_weight_qry = $dbconn->query("SELECT * FROM weights WHERE category = 'product_type' AND `column` = '{$usr_ops['product_type']}'");
//                        $pt_weight = $pt_weight_qry->fetch_assoc();
//
//                        $dts_qry = $dbconn->query("SELECT * FROM weights WHERE category = 'days_to_ship' AND `column` = '{$usr_ops['days_to_ship']}'");
//                        $dts = $dts_qry->fetch_assoc();
//
//                        $age = (((time() - $usr_ops['created']) / 60) / 60) / 24;
//
//                        $priority = ($pt_weight['weight'] * $dts['weight']) * $age;
//                    }

          $output['data'][$i][] = "<button class='btn waves-effect btn-primary pull-left start-operation' id='{$usr_ops['id']}'><i class='zmdi zmdi-play'></i></button>";
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i][] = "{$usr_ops['so_parent']}{$usr_ops['room']}-{$usr_ops['iteration']}";
          $output['data'][$i][] = "{$usr_ops['room_name']}";
          $output['data'][$i][] = "{$usr_ops['op_id']}: {$usr_ops['job_title']} $rework";
          $output['data'][$i][] = $release_date;
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i]['DT_RowId'] = $usr_ops['so_parent'];
//                    $output['data'][$i]['weight'] = $priority;

          $i += 1;
        }
      }
    } else {
      // honey-do operations (deprecated)
      /*
      $hd_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, op_queue.rework, op_queue.active_employees,
      op_queue.assigned_to, op_queue.urgent, op_queue.room_id FROM op_queue JOIN operations ON op_queue.operation_id = operations.id
      WHERE completed = FALSE AND published = TRUE AND operations.responsible_dept = '$queue' AND (active_employees NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR active_employees IS NULL)
      AND (op_queue.assigned_to NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR op_queue.assigned_to IS NULL) AND operations.job_title = 'Honey Do';");

      if($hd_qry->num_rows > 0) {
          while($hd = $hd_qry->fetch_assoc()) {
              if($hd['rework']) {
                  $rework = "(Rework)";
              } else {
                  $rework = null;
              }

              $release_date = date(DATE_DEFAULT, $hd['created']);

              $output['data'][$i][] = "<button class='btn waves-effect btn-primary pull-left start-operation' id='{$hd['id']}'><i class='zmdi zmdi-play'></i></button>";
              $output['data'][$i][] = "&nbsp;";
              $output['data'][$i][] = $hd['room_id'];
              $output['data'][$i][] = "---------";
              $output['data'][$i][] = "{$hd['op_id']}: {$hd['job_title']} $rework";
              $output['data'][$i][] = $release_date;
              $output['data'][$i][] = "&nbsp;";
              $output['data'][$i][] = "&nbsp;";

              $i += 1;
          }
      }*/

      $op_queue_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, rooms.room, rooms.so_parent, rooms.room_name, rooms.iteration,
            op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.urgent, rooms.product_type, rooms.days_to_ship, rooms.order_status FROM op_queue
            JOIN operations ON op_queue.operation_id = operations.id JOIN rooms ON op_queue.room_id = rooms.id
            WHERE completed = FALSE AND published = TRUE AND operations.responsible_dept = '$queue' AND (active_employees NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR active_employees IS NULL) 
            AND (assigned_to NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR assigned_to IS NULL) AND operations.job_title != 'Honey Do' ORDER BY op_queue.urgent ASC;");

      if($op_queue_qry->num_rows > 0) {
        while($op_queue = $op_queue_qry->fetch_assoc()) {
          if($op_queue['rework']) {
            $rework = "(Rework)";
          } else {
            $rework = null;
          }

          $release_date = date(DATE_DEFAULT, $op_queue['created']);

          if(!empty($op_queue['assigned_to'])) {
            $assigned_usrs = json_decode($op_queue['assigned_to']);

            $name = null;

            foreach($assigned_usrs as $usr) {
              $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$usr'");
              $usr = $usr_qry->fetch_assoc();

              $name .= $usr['name'] . ", ";
            }

            $assignee = substr($name, 0, -2);
          } else {
            $assignee = "&nbsp;";
          }

          // TODO: Update urgent code so that it selects that first in the SQL query

          if(empty($op_queue['urgent'])) {
            $pt_weight_qry = $dbconn->query("SELECT * FROM weights WHERE category = 'product_type' AND `column` = '{$op_queue['product_type']}'");
            $pt_weight = $pt_weight_qry->fetch_assoc();

            $dts_qry = $dbconn->query("SELECT * FROM weights WHERE category = 'days_to_ship' AND `column` = '{$op_queue['days_to_ship']}'");
            $dts = $dts_qry->fetch_assoc();

            $age = (((time() - $op_queue['created']) / 60) / 60) / 24;

            $priority = ($pt_weight['weight'] * $dts['weight']) * $age;
          }

          $output['data'][$i][] = "<button class='btn waves-effect btn-primary pull-left start-operation' id='{$op_queue['id']}'><i class='zmdi zmdi-play'></i></button>";
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i][] = "{$op_queue['so_parent']}{$op_queue['room']}-{$op_queue['iteration']}";
          $output['data'][$i][] = "{$op_queue['room_name']}";
          $output['data'][$i][] = "{$op_queue['op_id']}: {$op_queue['job_title']} $rework";
          $output['data'][$i][] = $release_date;
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i][] = $priority;
          $output['data'][$i]['DT_RowId'] = $op_queue['so_parent'];
          $output['data'][$i]['weight'] = $priority;

          $i += 1;
        }
      }

      $assigned_ops_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, rooms.room, rooms.so_parent, rooms.room_name, rooms.iteration,
            op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.urgent, rooms.order_status FROM op_queue JOIN operations ON op_queue.operation_id = operations.id
            JOIN rooms ON op_queue.room_id = rooms.id WHERE completed = FALSE AND published = TRUE AND assigned_to LIKE '%\"{$_SESSION['shop_user']['id']}\"%' ORDER BY op_queue.urgent ASC;");

      if($assigned_ops_qry->num_rows > 0) {
        while($assigned_ops = $assigned_ops_qry->fetch_assoc()) {
          $assigned_usrs = json_decode($assigned_ops['assigned_to']);

          $name = null;

          foreach($assigned_usrs as $usr) {
            $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$usr'");
            $usr = $usr_qry->fetch_assoc();

            $name .= $usr['name'] . ", ";
          }

          $assignee = substr($name, 0, -2);
          $release_date = date(DATE_DEFAULT, $assigned_ops['created']);

          $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'product_type' AND value = '{$assigned_ops['product_type']}'");
          $vin = $vin_qry->fetch_assoc();

          $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$assigned_ops['room_id']}'");
          $room = $room_qry->fetch_assoc();

          $output['data'][$i][] = "<button class='btn waves-effect btn-primary pull-left start-operation' id='{$assigned_ops['id']}'><i class='zmdi zmdi-play'></i></button>";
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i][] = "{$assigned_ops['so_parent']}{$assigned_ops['room']}-{$vin['key']}{$assigned_ops['iteration']}";
          $output['data'][$i][] = "{$room['room_name']}";
          $output['data'][$i][] = "{$assigned_ops['op_id']}: {$assigned_ops['job_title']}";
          $output['data'][$i][] = $release_date;
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i]['DT_RowId'] = $assigned_ops['so_parent'];

          $i += 1;
        }
      }

      $op_queue_qry = $dbconn->query("SELECT * FROM operations WHERE always_visible = TRUE AND responsible_dept = '$queue' ORDER BY job_title ASC");

      if($op_queue_qry->num_rows > 0) {
        while($op_queue = $op_queue_qry->fetch_assoc()) {
          $id = $op_queue['id'];
          $operation = $op_queue['op_id'] . ": " . $op_queue['job_title'];
          $release_date = date(DATE_DEFAULT, $op_queue['created']);
          $op_info = ["id"=>$op_queue['id'], "op_id"=>$op_queue['op_id'], "department"=>$op_queue['department'], "job_title"=>$op_queue['job_title'], "responsible_dept"=>$op_queue['responsible_dept'], "always_visible"=>$op_queue['always_visible']];
          $op_info_payload = json_encode($op_info);

          $output['data'][$i][] = "<button class='btn waves-effect btn-primary pull-left start-operation' id='$id'><i class='zmdi zmdi-play'></i></button>";
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i][] = "---------";
          $output['data'][$i][] = "---------";
          $output['data'][$i][] = $operation;
          $output['data'][$i][] = "Now";
          $output['data'][$i][] = "&nbsp;";
          $output['data'][$i][] = "&nbsp;";

          $i += 1;
        }
      }
    }

    if(empty($output)) {
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "---------";
      $output['data'][$i][] = "No operations found.";
      $output['data'][$i][] = "---------";
      $output['data'][$i][] = "Never";
      $output['data'][$i][] = "&nbsp;";
      $output['data'][$i][] = "---------";
    }

    echo json_encode($output);

    break;

  /** Workcenter */
  case 'display_full_job_queue':
    $jiq = $queue->wc_jobsInQueue();

    echo json_encode($jiq);

    break;
  case 'display_full_recently_completed':
    $recently_completed = $queue->wc_recentlyCompleted();

    echo json_encode($recently_completed);

    break;
  case 'display_full_active_jobs':
    $active = $queue->wc_activeJobs();

    echo json_encode($active);

    break;

  /** Sales List */
  case 'hide_sales_list_id':
    $id = sanitizeInput($_REQUEST['id']);

    $hidden_qry = $dbconn->query("SELECT hide_sales_list_values FROM user WHERE id = {$_SESSION['userInfo']['id']}");

    if($hidden_qry->num_rows > 0) {
      $hidden = $hidden_qry->fetch_assoc();
      $hidden = $hidden['hide_sales_list_values'];

      if (!empty($hidden)) {
        // there's an existing hidden array
        $hidden_values = json_decode($hidden);

        if(!in_array($id, $hidden_values)) {
          $hidden_values[] = $id;
        }

        $hidden_values = json_encode($hidden_values);

        $dbconn->query("UPDATE user SET hide_sales_list_values = '$hidden_values' WHERE id = {$_SESSION['userInfo']['id']}");
      } else {
        // there are no existing hidden array values
        $id_array[] = $id;

        $hidden_values = json_encode($id_array);

        $dbconn->query("UPDATE user SET hide_sales_list_values = '$hidden_values' WHERE id = {$_SESSION['userInfo']['id']}");
      }
    }

    break;
  case 'show_sales_list_id':
    $id = sanitizeInput($_REQUEST['id']);

    $hidden_qry = $dbconn->query("SELECT hide_sales_list_values FROM user WHERE id = {$_SESSION['userInfo']['id']}");

    if($hidden_qry->num_rows > 0) {
      $hidden = $hidden_qry->fetch_assoc();
      $hidden = $hidden['hide_sales_list_values'];

      if(!empty($hidden)) {
        // there's an existing hidden array
        $hidden_values = json_decode($hidden);

        if(in_array($id, $hidden_values)) {
          $loc = array_search($id, $hidden_values);

          unset($hidden_values[$loc]);

          $hidden_values = array_values($hidden_values);
        }

        $hidden_values = json_encode($hidden_values);

        $dbconn->query("UPDATE user SET hide_sales_list_values = '$hidden_values' WHERE id = {$_SESSION['userInfo']['id']}");
      }
    }

    break;

  /** Engineering Cardlist */
  case 'update_eng_order':
    $items = sanitizeInput($_REQUEST['items']);
    $type = sanitizeInput($_REQUEST['type']);

    $eng_order_qry = $dbconn->query("SELECT * FROM eng_report WHERE user_id = '{$_SESSION['userInfo']['id']}'");

    if($eng_order_qry->num_rows === 1) {
      $eng_order = $eng_order_qry->fetch_assoc();

      $dbconn->query("UPDATE eng_report SET `{$type}_sort` = '$items' WHERE id = '{$eng_order['id']}'");

      echo "UPDATE eng_report SET `{$type}_sort` = '$items' WHERE id = '{$eng_order['id']}";
    } else {
      $dbconn->query("INSERT INTO eng_report (user_id, {$type}_sort) VALUES ('{$_SESSION['userInfo']['id']}', '$items');");
    }

    break;
  case 'hide_eng_card':
    $room_id = sanitizeInput($_REQUEST['room_id']);
    $type = sanitizeInput($_REQUEST['type']);

    $eng_report_qry = $dbconn->query("SELECT * FROM eng_report WHERE user_id = '{$_SESSION['userInfo']['id']}'");

    if($eng_report_qry->num_rows === 1) {
      $eng_report = $eng_report_qry->fetch_assoc();

      if(!empty($eng_report[$type . '_invisible'])) {
        $hidden_cards = json_decode($eng_report[$type . '_invisible']);

        $hidden_cards[] = $room_id;
      } else {
        $hidden_cards[] = $room_id;
      }

      $hidden_cards = json_encode($hidden_cards);

      $dbconn->query("UPDATE eng_report SET {$type}_invisible = '$hidden_cards' WHERE user_id = '{$_SESSION['userInfo']['id']}'");
    } else {
      $hidden_cards[] = $room_id;

      $hidden_cards = json_encode($hidden_cards);

      $dbconn->query("INSERT INTO eng_report (user_id, {$type}_invisible) VALUES ('{$_SESSION['userInfo']['id']}', '$hidden_cards')");
    }

    break;
  case 'show_eng_card':
    $room_id = sanitizeInput($_REQUEST['room_id']);
    $type = sanitizeInput($_REQUEST['type']);

    $eng_report_qry = $dbconn->query("SELECT * FROM eng_report WHERE user_id = '{$_SESSION['userInfo']['id']}'");

    if($eng_report_qry->num_rows === 1) {
      $eng_report = $eng_report_qry->fetch_assoc();

      if(!empty($eng_report[$type . '_invisible'])) {
        $hidden_cards = json_decode($eng_report[$type . '_invisible']);

        $array_location = array_search($room_id, $hidden_cards); // find the index of the card to remove

        unset($hidden_cards[$array_location]); // remove the card from hidden view

        $hidden_cards = array_values($hidden_cards); // reset the index of the array

        $hidden_cards = json_encode($hidden_cards);
      } else {
        $hidden_cards = "";
      }

      $dbconn->query("UPDATE eng_report SET {$type}_invisible = '$hidden_cards' WHERE user_id = '{$_SESSION['userInfo']['id']}'");
    } else {
      echo displayToast("error", "No cards to unhide.", "Unable to Unhide");
    }

    break;

  /** VIN Image display */
  case 'vin_image_ref':
    $type = sanitizeInput($_REQUEST['type']);
    $vinID = sanitizeInput($_REQUEST['vinID']);

    $qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$type' AND `key` = '$vinID'");

    if($qry->num_rows > 0) {
      $result = $qry->fetch_assoc();

      echo (!empty($result['image'])) ? "<img src='/assets/images/vin/{$result['image']}'>" : null;
    }

    break;
}