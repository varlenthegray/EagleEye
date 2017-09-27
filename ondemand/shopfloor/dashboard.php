<?php
require '../../includes/header_start.php';
require ("../../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/

use Carbon\Carbon; // prep carbon

switch($_REQUEST['action']) {
    case "display_quotes":
        $output = array();
        $i = 0;

        $quote_qry = $dbconn->query("SELECT * FROM sales_order WHERE order_status = '#'");

        if($quote_qry->num_rows > 0) {
            while($quote = $quote_qry->fetch_assoc()) {
                $prev_room = null;

                $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$quote['so_num']}' ORDER BY room ASC LIMIT 0, 1");
                $room =  $room_qry->fetch_assoc();

                if((bool)$room['sales_published']) {
                    $sales_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room['sales_bracket']}'");
                    $sales_op = $sales_op_qry->fetch_assoc();

                    $sales_op_display = (!empty($sales_op)) ? "{$sales_op['op_id']}: {$sales_op['job_title']}" : "None";
                } else {
                    $sales_op_display = "";
                }

                if((bool)$room['sample_published']) {
                    $sample_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room['sample_bracket']}'");
                    $sample_op = $sample_op_qry->fetch_assoc();

                    $sample_op_display = (!empty($sample_op)) ? "{$sample_op['op_id']}: {$sample_op['job_title']}" : "None";
                } else {
                    $sample_op_display = "";
                }

                $output['data'][$i][] = $quote['so_num'];
                $output['data'][$i][] = "<strong>{$quote['contractor_dealer_code']}_{$quote['project']}</strong>";
                $output['data'][$i][] = null;
                $output['data'][$i][] = null;
                $output['data'][$i]['DT_RowId'] = $quote['so_num'];

                $i += 1;

                $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$quote['so_num']}' ORDER BY room, iteration ASC");

                while($room = $room_qry->fetch_assoc()) {
                    if((bool)$room['sales_published']) {
                        $sales_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room['sales_bracket']}'");
                        $sales_op = $sales_op_qry->fetch_assoc();

                        $sales_op_display = (!empty($sales_op)) ? "{$sales_op['op_id']}: {$sales_op['job_title']}" : "None";
                    } else {
                        $sales_op_display = "";
                    }

                    if((bool)$room['sample_published']) {
                        $sample_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room['sample_bracket']}'");
                        $sample_op = $sample_op_qry->fetch_assoc();

                        $sample_op_display = (!empty($sample_op)) ? "{$sample_op['op_id']}: {$sample_op['job_title']}" : "None";
                    } else {
                        $sample_op_display = "";
                    }

                    if($room['room'] === $prev_room) {
                        $indent = "margin-left:55px";
                        $addl_room_info = substr($room['iteration'], -3, 3);
                    } else {
                        $indent = "margin-left:40px";
                        $addl_room_info = "{$room['room']}{$room['iteration']}";
                    }

                    $prev_room = $room['room'];

                    $output['data'][$i][] = $quote['so_num'];
                    $output['data'][$i][] = "<span style='$indent'>$addl_room_info-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}-{$room['room_name']}</span>";
                    $output['data'][$i][] = $sales_op_display;
                    $output['data'][$i][] = $sample_op_display;
                    $output['data'][$i]['DT_RowId'] = $quote['so_num'];

                    $i += 1;
                }
            }
        }

        echo json_encode($output);

        break;
    case "display_orders":
        $output = array();
        $i = 0;

        $quote_qry = $dbconn->query("SELECT * FROM sales_order WHERE order_status = '$'");

        if($quote_qry->num_rows > 0) {
            while($quote = $quote_qry->fetch_assoc()) {
                $prev_room = null;

                $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$quote['so_num']}' ORDER BY room ASC LIMIT 0, 1");
                $room =  $room_qry->fetch_assoc();

                if((bool)$room['preproduction_published']) {
                    $preprod_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room['preproduction_bracket']}'");
                    $preprod_op = $preprod_op_qry->fetch_assoc();

                    $preprod_op_display = (!empty($preprod_op)) ? "{$preprod_op['op_id']}: {$preprod_op['job_title']}" : "None";
                } else {
                    $preprod_op_display = "";
                }

                if((bool)$room['main_published']) {
                    $main_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room['main_bracket']}'");
                    $main_op = $main_op_qry->fetch_assoc();

                    $main_op_display = (!empty($main_op)) ? "{$main_op['op_id']}: {$main_op['job_title']}" : "None";
                } else {
                    $main_op_display = "";
                }

                $output['data'][$i][] = $quote['so_num'];
                $output['data'][$i][] = "<strong>{$quote['contractor_dealer_code']}_{$quote['project']}</strong>";
                $output['data'][$i][] = null;
                $output['data'][$i][] = null;
                $output['data'][$i]['DT_RowId'] = $quote['so_num'];

                $i += 1;

                $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$quote['so_num']}' ORDER BY room, iteration ASC");

                while($room = $room_qry->fetch_assoc()) {
                    if((bool)$room['preproduction_published']) {
                        $preprod_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room['preproduction_bracket']}'");
                        $preprod_op = $preprod_op_qry->fetch_assoc();

                        $preprod_op_display = (!empty($preprod_op)) ? "{$preprod_op['op_id']}: {$preprod_op['job_title']}" : "None";
                    } else {
                        $preprod_op_display = "";
                    }

                    if((bool)$room['main_published']) {
                        $main_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room['main_bracket']}'");
                        $main_op = $main_op_qry->fetch_assoc();

                        $main_op_display = (!empty($main_op)) ? "{$main_op['op_id']}: {$main_op['job_title']}" : "None";
                    } else {
                        $main_op_display = "";
                    }

                    if($room['room'] === $prev_room) {
                        $indent = "margin-left:60px";
                        $addl_room_info = substr($room['iteration'], -3, 3);
                    } else {
                        $indent = "margin-left:40px";
                        $addl_room_info = "{$room['room']}{$room['iteration']}";
                    }

                    $prev_room = $room['room'];

                    $output['data'][$i][] = $quote['so_num'];
                    $output['data'][$i][] = "<span style='$indent'>$addl_room_info-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}-{$room['room_name']}</span>";
                    $output['data'][$i][] = $preprod_op_display;
                    $output['data'][$i][] = $main_op_display;
                    $output['data'][$i]['DT_RowId'] = $quote['so_num'];

                    $i += 1;
                }
            }
        }

        echo json_encode($output);

        break;

    case "quote_details":
        echo "Hello, my fair world.";

        break;

    case 'display_active_jobs':
        $output = array();
        $i = 0;

        $self_qry = $dbconn->query("SELECT op_queue.id AS opID, op_queue.*, operations.*, rooms.iteration AS rIteration FROM op_queue JOIN operations ON op_queue.operation_id = operations.id LEFT JOIN rooms ON op_queue.room_id = rooms.id WHERE active_employees LIKE '%\"{$_SESSION['shop_user']['id']}\"%' AND active = TRUE");

        if($self_qry->num_rows > 0) {
            while($self = $self_qry->fetch_assoc()) {
                if(!empty($self['subtask'])) {
                    $subtask = " ({$self['subtask']})";
                }

                if($self['job_title'] === 'Non-Billable' || $self['job_title'] === 'On The Fly') {
                    $pause_btn = null;
                    $margin = '12px';
                    $zeroed = true;
                    $notes_btn = null;
                } else {
                    $pause_btn = "<button class='btn waves-effect btn-primary pull-left pause-operation' id='{$self['opID']}'><i class='zmdi zmdi-pause'></i></button>";
                    $notes_btn = "<button class='btn waves-effect btn-primary pull-left op-notes' style='margin-left:4px;' id='{$self['opID']}'><i class='fa fa-sticky-note-o'></i></button>";
                    $margin = '4px';
                    $zeroed = false;
                }

                $operation = $self['op_id'] . ": " . $self['job_title'] . $subtask;
                $start_time = ($self['resumed_time'] === null) ? date(TIME_ONLY, $self['start_time']) : date(TIME_ONLY, $self['resumed_time']);

                $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'product_type' AND value = '{$self['product_type']}'");
                $vin = $vin_qry->fetch_assoc();

                $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$self['room_id']}'");
                $room = $room_qry->fetch_assoc();

                if(!empty($self['so_parent'])) {
                    $so = "{$self['so_parent']}{$self['room']}-{$vin['key']}{$self['rIteration']}";
                } else {
                    $so = "---------";
                }

                if(!empty($room['room_name'])) {
                    $room = $room['room_name'];
                } else {
                    $room = "---------";
                }

                $time = Carbon::createFromTimestamp($self['start_time']); // grab the carbon timestamp

                $output['data'][$i][] = "$pause_btn <button class='btn waves-effect btn-primary pull-left complete-operation' id='{$self['opID']}' style='margin-left:$margin;'><i class='zmdi zmdi-stop'></i></button> $notes_btn";
                $output['data'][$i][] = $so;
                $output['data'][$i][] = $room;
                $output['data'][$i][] = $self['responsible_dept'];
                $output['data'][$i][] = $operation;
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
    case 'display_job_queue':
        $queue = sanitizeInput($_REQUEST['queue']);
        $external_brackets = ['Non-Billable'];

        $output = array();
        $i = 0;

        if($queue === 'self') {
            $queue_qry = $dbconn->query("SELECT * FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");
            $queue_sql = $queue_qry->fetch_assoc();


        }

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS queueID, operations.id AS opID, op_queue.*, operations.*, rooms.* FROM op_queue
              JOIN operations ON op_queue.operation_id = operations.id JOIN rooms ON op_queue.room_id = rooms.id
               WHERE completed = FALSE AND published = TRUE AND operations.responsible_dept = '$queue'
                AND (active_employees NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR active_employees IS NULL) 
                 AND (assigned_to NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR assigned_to IS NULL) ORDER BY op_queue.priority, op_queue.so_parent, op_queue.room ASC;");

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                if($op_queue['rework']) {
                    $rework = "(Rework)";
                } else {
                    $rework = null;
                }

                $sonum = $op_queue['so_parent'] . "-" . $op_queue['room'];
                $operation = "{$op_queue['op_id']}: {$op_queue['job_title']} $rework";
                $release_date = date(DATE_DEFAULT, $op_queue['created']);

                $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'product_type' AND value = '{$op_queue['product_type']}'");
                $vin = $vin_qry->fetch_assoc();

                $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$op_queue['room_id']}'");
                $room = $room_qry->fetch_assoc();

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

                if(empty($op_queue['priority'])) {
                    $pt_weight_qry = $dbconn->query("SELECT * FROM weights WHERE category = 'product_type' AND `column` = '{$room['product_type']}'");
                    $pt_weight = $pt_weight_qry->fetch_assoc();

                    $dts_qry = $dbconn->query("SELECT * FROM weights WHERE category = 'days_to_ship' AND `column` = '{$room['days_to_ship']}'");
                    $dts = $dts_qry->fetch_assoc();

                    $age = (((time() - $op_queue['created']) / 60) / 60) / 24;

                    $priority = ($pt_weight['weight'] * $dts['weight']) * $age;
                } else {
                    $priority = $op_queue['priority'];
                }

                $output['data'][$i][] = "<button class='btn waves-effect btn-primary pull-left start-operation' id='{$op_queue['queueID']}'><i class='zmdi zmdi-play'></i></button>";
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = "{$op_queue['so_parent']}{$op_queue['room']}-{$op_queue['iteration']}";
                $output['data'][$i][] = $room['room_name'];
                $output['data'][$i][] = $operation;
                $output['data'][$i][] = $release_date;
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = $assignee;
                $output['data'][$i][] = $priority;
                $output['data'][$i]['DT_RowId'] = $op_queue['so_parent'];
                $output['data'][$i]['weight'] = $priority;

                $i += 1;
            }
        }

//        $assigned_ops_qry = $dbconn->query("SELECT * FROM op_queue WHERE assigned_to LIKE '%\"{$_SESSION['shop_user']['id']}\"%'");

        $assigned_ops_qry = $dbconn->query("SELECT op_queue.id AS queueID, operations.id AS opID, op_queue.*, operations.*, rooms.* FROM op_queue
              JOIN operations ON op_queue.operation_id = operations.id JOIN rooms ON op_queue.room_id = rooms.id
               WHERE completed = FALSE AND published = TRUE AND assigned_to LIKE '%\"{$_SESSION['shop_user']['id']}\"%' ORDER BY op_queue.so_parent, op_queue.room ASC;");

        if($assigned_ops_qry->num_rows > 0) {
            while($assigned_ops = $assigned_ops_qry->fetch_assoc()) {
                $full_assigned_qry = $dbconn->query("SELECT op_queue.id AS queueID, operations.id AS opID, op_queue.*, operations.*, rooms.* FROM op_queue 
                 JOIN operations ON op_queue.operation_id = operations.id JOIN rooms ON op_queue.room_id = rooms.id WHERE op_queue.id = '{$assigned_ops['queueID']}'");
                $full_assigned_info = $full_assigned_qry->fetch_assoc();

                $assigned_usrs = json_decode($full_assigned_info['assigned_to']);

                $name = null;

                foreach($assigned_usrs as $usr) {
                    $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$usr'");
                    $usr = $usr_qry->fetch_assoc();

                    $name .= $usr['name'] . ", ";
                }

                $assignee = substr($name, 0, -2);

                $sonum = $full_assigned_info['so_parent'] . "-" . $full_assigned_info['room'];
                $operation = $full_assigned_info['op_id'] . ": " . $full_assigned_info['job_title'];
                $release_date = date(DATE_DEFAULT, $full_assigned_info['created']);

                $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'product_type' AND value = '{$full_assigned_info['product_type']}'");
                $vin = $vin_qry->fetch_assoc();

                $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$full_assigned_info['room_id']}'");
                $room = $room_qry->fetch_assoc();

                $output['data'][$i][] = "<button class='btn waves-effect btn-primary pull-left start-operation' id='{$op_queue['queueID']}'><i class='zmdi zmdi-play'></i></button>";
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = "{$full_assigned_info['so_parent']}{$full_assigned_info['room']}-{$vin['key']}{$full_assigned_info['iteration']}";
                $output['data'][$i][] = $room['room_name'];
                $output['data'][$i][] = $operation;
                $output['data'][$i][] = $release_date;
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = $assignee;
                $output['data'][$i]['DT_RowId'] = $full_assigned_info['so_parent'];

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
                $output['data'][$i][] = "&nbsp;";

                $i += 1;
            }
        }

        echo json_encode($output);

        break;

    case 'start_operation':
        $id = sanitizeInput($_REQUEST['id']);
        $operation = sanitizeInput($_REQUEST['operation']);
        $ae[] = $_SESSION['shop_user']['id'];
        $active_employees = json_encode($ae);

        $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'");

        if($op_qry->num_rows > 0) {
            $op_info = $op_qry->fetch_assoc();

            $otf = ($op_info['job_title'] === 'On The Fly') ? TRUE : FALSE;
        }

        $subtask = sanitizeInput($_POST['subtask']);
        $notes = sanitizeInput($_POST['notes']);
        $time = date(DATE_TIME_ABBRV);

        $otf_so = sanitizeInput($_REQUEST['otf_so_num']);
        $otf_room = sanitizeInput($_REQUEST['otf_room']);
        $otf_op = sanitizeInput($_REQUEST['otf_op']);
        $otf_notes = sanitizeInput($_REQUEST['otf_notes']);
        $otf_iteration = sanitizeInput($_REQUEST['otf_iteration']);

        if($otf) {
            $otf_notes = "$otf_notes [$time - {$_SESSION['shop_user']['name']} <i>OTF Created</i>]<br />";
        } else {
            $notes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />";
        }

        if(substr($operation, 0, 3) !== '000') { // if this is not a triple-zero operation
            $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'"); // grab the existing op queue id

            if($qry->num_rows > 0) {  // if we were able to find the operation inside of the queue
                $results = $qry->fetch_assoc(); // grab the information

                $changes = null; // our changes are nothing presently

                $active = json_decode($results['active_employees'], true); // grab the current list of active employees

                $active[] = $_SESSION['shop_user']['id']; // add individual to the list of active employees

                $active_employees = json_encode($active); // re-encode it for saving

                if($results['start_time'] === null) { // if this op queue item has never been started
                    if($dbconn->query("UPDATE op_queue SET active = TRUE, start_time = UNIX_TIMESTAMP(), active_employees = '$active_employees' WHERE id = '$id'")) {
                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                        $final_changes = json_encode($changes);

                        if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())"))
                            echo displayToast("success", "Successfully started operation.", "Started Operation");
                        else
                            dbLogSQLErr($dbconn);
                    } else {
                        dbLogSQLErr($dbconn);
                    }
                } else { // if the operation has been started previously
                    if($dbconn->query("UPDATE op_queue SET active = TRUE, resumed_time = UNIX_TIMESTAMP(), active_employees = '$active_employees' WHERE id = '$id'")) {
                        $changes = ["Active"=>TRUE, "Resumed Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                        $final_changes = json_encode($changes);

                        if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())"))
                            echo displayToast("success", "Successfully resumed operation.", "Resumed Operation");
                        else
                            dbLogSQLErr($dbconn);
                    } else {
                        dbLogSQLErr($dbconn);
                    }
                }
            } else { // we were unable to find an operation in the queue that existed
                dbLogSQLErr($dbconn); // gonna throw an error here...
            }
        } else { // this is a triple-zero op
            if($otf) { // this is an on-the-fly operation
                // first check to see if anything exists in the op queue with this operation id, room, so# and iteration
                $exists_qry = $dbconn->query("SELECT * FROM op_queue WHERE so_parent = '$otf_so' AND room = '$otf_room' AND operation_id = '$otf_op' AND iteration = '$otf_iteration' AND published = TRUE AND completed = FALSE");

                if($exists_qry->num_rows > 0) { // if the operation already exists
                    echo displayToast("error", "Unable to create On The Fly operation. Already exists.", "Op Exists");
                } else { // this is a brand new operation!
                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$otf_so' AND room = '$otf_room' AND iteration = '$otf_iteration'");

                    if($room_qry->num_rows > 0) {
                        $room = $room_qry->fetch_assoc();

                        $room_id = $room['id'];
                    } else {
                        $room_id = null;
                    }

                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$otf_op'"); // grab the normal op info

                    if($op_qry->num_rows > 0) { // if we were able to get the operation
                        $operation = $op_qry->fetch_assoc();

                        // create the op queue listing to be able to update information
                        $dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, iteration, operation_id, start_time, active, created, active_employees, started_by, notes, otf_created) 
                            VALUES ('$room_id', '$otf_so', '$otf_room', '$otf_iteration', '{$operation['id']}', UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP(), '$active_employees', '{$_SESSION['shop_user']['id']}', '$otf_notes', TRUE)");

                        $inserted_id = $dbconn->insert_id; // grab the inserted id for audit trail records

                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees), "Subtask"=>$subtask, "Notes"=>$notes, "OTF"=>'true'];
                        $final_changes = json_encode($changes);

                        $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())");

                        echo displayToast("success", "Started On The Fly operation.", "Successfully Started OTF");
                    } else {
                        dbLogSQLErr($dbconn);
                    }
                }
            } else { // this is not an on-the-fly op
                $admin_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'"); // grab the normal op info

                if($admin_qry->num_rows > 0) { // if we were able to get the operation
                    $admin_results = $admin_qry->fetch_assoc();

                    if((bool)$admin_results['always_visible']) { // check to confirm this is an always visible op
                        // create the op queue listing to be able to update information
                        $dbconn->query("INSERT INTO op_queue (operation_id, start_time, active, created, active_employees, started_by, subtask, notes) VALUES ('{$admin_results['id']}', UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP(), '$active_employees', '{$_SESSION['shop_user']['id']}', '$subtask', '$notes')");

                        $inserted_id = $dbconn->insert_id; // grab the inserted id for audit trail records

                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees), "Subtask"=>$subtask, "Notes"=>$notes];
                        $final_changes = json_encode($changes);

                        $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())");

                        echo displayToast("success", "Successfully started a non-billable operation.", "Started Non-Billable operation");
                    } else {
                        echo displayToast("error", "Unable to properly start this operation (not always visible).", "Error Starting Operation.");
                    }
                } else {
                    dbLogSQLErr($dbconn);
                }
            }
        }

        break;
    case 'get_start_info':
        $id = sanitizeInput($_REQUEST['opID']);
        $op = sanitizeInput($_REQUEST['op']);

        echo "<script>console.log('\"$op\"')</script>";

        if(substr($op, 0, 3) !== '000') { // if not an op in the 000's (always visible op)
            $op_query = $dbconn->query("SELECT * FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE op_queue.id = '$id';");

            if($op_query->num_rows === 1) {
                $op_queue = $op_query->fetch_assoc();

                $operation = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                $current_time = date("g:i A");
                $so_name = $op_queue['so_parent'] . "-" . $op_queue['room'];

                if(!empty($op_queue['start_time'])) {
                    $originally_started = "<p>Originally Started " . date(DATE_TIME_ABBRV, $op_queue['start_time'] . "</p>");
                } else {
                    $originally_started = null;
                }

                $status = (!(bool)$op_queue['rework']) ? "New" : "Rework";
                $notes = (!empty($op_queue['notes']) ? $op_queue['notes'] : "None");

                echo <<<HEREDOC
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="modalStartJobTitle">Start operation $operation for $so_name at <span id="start_job_time">$current_time</span>?</h4>
                        </div>
                        <div class="modal-body">
                            $originally_started
        
                            <p>
                                <!--<span id="start_job_qty">Quantity to Complete: ?</span><br/>-->
                                Operation: <b>$operation</b><br />
                                Sales Order: <b>$so_name</b><br />
                                Status: <b>$status</b>
                            </p>
                            
                            <p>
                                Notes: <b>$notes</b>
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="start_job" data-startid="$id">Start Operation</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
HEREDOC;
            } else {
                http_response_code(400); // send bad request response
                dbLogSQLErr($dbconn);
            }
        } else {
            $op_query = $dbconn->query("SELECT * FROM operations WHERE id = '$id'");

            if($op_query->num_rows === 1) {
                $op_info = $op_query->fetch_assoc();

                if($op !== '000: On The Fly') { // if it's not "on the fly"
                    if(!empty($op_info['sub_tasks'])) {
                        $sub_tasks = json_decode($op_info['sub_tasks']);

                        $operation = $op_info['op_id'] . ": " . $op_info['job_title'];
                        $current_time = date("g:i A");
                        $responsible_dept = $op_info['responsible_dept'];

                        if(!empty($sub_tasks)) {
                            $sub_tasklist = '';

                            foreach($sub_tasks as $task) {
                                $task_id = explode("(", $task);
                                $task_id_readable = str_replace(" ", "_", $task_id['0']);

                                $task_id_readable = strtolower(rtrim($task_id_readable, "_("));
                                $task_id = rtrim($task_id['0'], "(");

                                $sub_tasklist .= "<div class='radio'><input type='radio' class='form-control' id='$task_id_readable' name='nonBillableTask' value='$task_id' /> <label for='$task_id_readable'>$task</label></div>";
                            }
                        } else {
                            $sub_tasklist = null;
                        }

                        $sub_tasklist .= "<div class='radio'><input type='radio' class='form-control' id='other_subtask' name='nonBillableTask' value='Other' /> <label for='other_subtask'>Other</label></div>";

                        echo <<<HEREDOC
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="modalStartJobTitle">Start a non-billable operation at <span id="start_job_time">$current_time</span> as $responsible_dept?</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    $sub_tasklist
                                </div>
                                
                                <div class="col-md-8 has-danger" id="other_notes_section" style="height: 138px; display: none;">
                                    <label for="information">Non-Billable - Other Notes</label>
                                    <textarea class="form-control form-control-danger" id="other_notes_field" name="other_notes" placeholder="Other Notes" style="height: 100%;" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="start_job" data-startid="$id">Start Operation</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
HEREDOC;
                    } else {
                        $operation = $op_info['op_id'] . ": " . $op_info['job_title'];
                        $current_time = date("g:i A");
                        $responsible_dept = $op_info['responsible_dept'];

                        echo <<<HEREDOC
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="modalStartJobTitle">Start $operation at <span id="start_job_time">$current_time</span> as $responsible_dept?</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8" id="notes_section" style="height: 138px;">
                                    <label for="notes_field">Notes</label>
                                    <textarea class="form-control" id="notes_field" name="notes_field" placeholder="Notes regarding this operation." style="height: 90%;"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="start_job" data-startid="$id">Start Operation</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
HEREDOC;
                    }
                } else { // if it is "on the fly"
                    $current_time = date("g:i A");
                    $responsible_dept = $op_info['responsible_dept'];
                    $all_ops = null;

                    $ops_qry = $dbconn->query("SELECT * FROM operations WHERE always_visible = FALSE ORDER BY op_id ASC");

                    while($op = $ops_qry->fetch_assoc()) {
                        $all_ops .= "<option value='{$op['id']}'>{$op['op_id']}: {$op['job_title']}</option>";
                    }

                    echo <<<HEREDOC
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="modalStartJobTitle">Start an "On Thy Fly" operation at <span id="start_job_time">$current_time</span>?</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="otf_so_num">SO#</label>
                                                <input type="text" class="form-control" id="otf_so_num" name="otf_so_num" placeholder="SO Number">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="otf_room">Room</label>
                                                <input type="text" class="form-control" id="otf_room" name="otf_room" placeholder="Room Letter" maxlength="1">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="otf_iteration">Iteration</label>
                                                <input type="text" class="form-control" id="otf_iteration" name="otf_iteration" placeholder="Iteration" maxlength="4" value="1.01">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="otf_operation">Operation</label>
                                                <select class="form-control" id="otf_operation" name="otf_operation">
                                                    $all_ops
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6" style="height: 116px;">
                                    <div class="form-group">
                                        <label for="otf_notes">Notes</label>
                                        <textarea name="otf_notes" id="otf_notes" class="form-control" placeholder="Notes regarding operation." style="height: 86px;"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="start_job" data-startid="$id" data-otf="true">Start Operation</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
HEREDOC;
                }
            } else {
                http_response_code(400); // send bad request response
                dbLogSQLErr($dbconn);
            }
        }

        break;

    case 'get_pause_info':
        $id = sanitizeInput($_REQUEST['opID']);

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS opID, op_queue.*, operations.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE op_queue.id = '$id'");

        if($op_queue_qry->num_rows === 1) {
            $op_queue = $op_queue_qry->fetch_assoc();

            $header = "Partially Complete {$op_queue['so_parent']}{$op_queue['room']}-{$op_queue['iteration']} {$op_queue['op_id']}: {$op_queue['job_title']}";

            echo <<<HEREDOC
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">$header</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class='form-group'>
                                <label for='qtyCompleted'>Quantity Completed</label>
                                <input type='text' class='form-control' style="width:20%;" id='qtyCompleted' name='qtyComplete' placeholder='Requested {$op_queue['qty_requested']}'  data-toggle='tooltip' data-placement='top' value='{$op_queue['qty_requested']}'>
                            </fieldset>
                        
                            <fieldset class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" style="height: 107px" placeholder="Notes and information related to operation."></textarea>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="pause_op" data-id="$id">Update</button>
                </div>
            </div>
        </div>
HEREDOC;

        } else {
            http_response_code(400); // send bad request response
            dbLogSQLErr($dbconn);
        }

        break;
    case 'pause_operation':
        $id = sanitizeInput($_POST['opID'], $dbconn); // operation ID from the queue
        $notes = sanitizeInput($_POST['notes'], $dbconn); // notes to submit to the operation
        $qty = sanitizeInput($_POST['qty']); // quantity completed

        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'"); // grab the item from the operation queue
        $op_queue = $op_queue_qry->fetch_assoc();

        $time = date(DATE_TIME_ABBRV); // grab the current time

        $room_id = $op_queue['room_id']; // assign the room ID for use inside of function incrementJob

        $finalnotes = null; // define final notes as null initially

        if(empty($op_queue['notes'])) { // if no notes exist
            $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />"; // the notes equals the name and the time
        } else { // otherwise notes exist
            $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />" . $op_queue['notes']; // concatenate the notes
        }

        $op_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");
        $op_results = $op_qry->fetch_assoc();
        $active_emp = json_decode($op_results['active_employees']);

        if(in_array($_SESSION['shop_user']['id'], $active_emp)) {
            $loc = array_search($_SESSION['shop_user']['id'], $active_emp);
            unset($active_emp[$loc]);
        }

        if(count($active_emp) > 0) {
            $active = "TRUE";
        } else {
            $active = "FALSE";
        }

        $active_employees = json_encode($active_emp);

        if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = $active, notes = '$finalnotes', qty_completed = '$qty', partially_completed = TRUE, completed = FALSE, active_employees = '$active_employees' WHERE id = $id")) {
            $changed = ["End time"=>time(), "Active"=>$active, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Partially Completed"=>true, "Active Employees"=>json_decode($active_employees)];
            $changed = json_encode($changed);

            if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())"))
                echo displayToast("info", "Operation has been marked as partially completed.", "Partially Closed Operation");
            else
                dbLogSQLErr($dbconn);
        } else {
            dbLogSQLErr($dbconn);
            die();
        }


        break;

    case 'get_stop_info':
        $id = sanitizeInput($_REQUEST['opID']);

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS opID, op_queue.*, operations.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE op_queue.id = '$id'");

        if($op_queue_qry->num_rows === 1) {
            $op_queue = $op_queue_qry->fetch_assoc();

            if($op_queue['op_id'] !== '000') {
                $rework_box = '<div class="col-md-3"><div class="form-group row"><div class="col-sm-12"><div class="checkbox checkbox-primary"><input id="rework_reqd" type="checkbox" value="true" name="rework"><label for="rework_reqd" style="margin-top:21px;">Rework Required</label></div></div></div></div>';
                $rework_codes = "<div class='col-md-3'>
                                    <fieldset class='form-group rework_reason_group' style='display:none;'>
                                        <label for='rework_reason'>Reason for Rework</label>
                                        <select class='form-control' id='rework_reason'>
                                            <optgroup label='Material/Hardware'>
                                                <option value='MW'>MW</option>
                                                <option value='MD'>MD</option>
                                            </optgroup>
                                            <optgroup label='Workmanship'>
                                                <option value='WU'>WU</option>
                                                <option value='WT'>WT</option>
                                            </optgroup>
                                            <optgroup label='Equipment'>
                                                <option value='EB'>EB</option>
                                                <option value='EM'>EM</option>
                                                <option value='ET'>ET</option>
                                            </optgroup>
                                            <optgroup label='Other'>
                                                <option value='OD'>OD</option>
                                                <option value='OA'>OA</option>
                                                <option value='OT'>OT</option>
                                            </optgroup>
                                        </select>
                                    </fieldset>
                                </div>";
                
                $qty_completed = "<div class='col-md-3'>
                                    <fieldset class='form-group'>
                                        <label for='qtyCompleted'>Quantity Completed</label>
                                        <input type='text' class='form-control' id='qtyCompleted' name='qtyComplete' placeholder='Requested {$op_queue['qty_requested']}'  data-toggle='tooltip' data-placement='top' value='{$op_queue['qty_requested']}'>
                                    </fieldset>                                                        
                                </div>";

                $header = "Complete {$op_queue['so_parent']}{$op_queue['room']}-{$op_queue['iteration']} {$op_queue['op_id']}: {$op_queue['job_title']}";
            } else {
                $rework_box = null;
                $rework_codes = null;
                $qty_completed = null;

                $header = "Complete {$op_queue['op_id']}: {$op_queue['job_title']} ({$op_queue['subtask']})";
            }

            echo <<<HEREDOC
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">$header</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                $qty_completed
                                
                                $rework_box
                                
                                $rework_codes
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <fieldset class="form-group">
                                        <label for="notes">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" style="height: 107px" placeholder="Notes and information related to operation."></textarea>
                                    </fieldset>
                                </div>
                            </div>
                        
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="file" name="op_file_attachment" accept="application/pdf">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="complete_op" data-id="$id">Complete</button>
                </div>
            </div>
        </div>
HEREDOC;

        } else {
            http_response_code(400); // send bad request response
            dbLogSQLErr($dbconn);
        }

        break;
    case 'complete_operation':
        $id = sanitizeInput($_POST['opID'], $dbconn); // operation ID from the queue
        $notes = sanitizeInput($_POST['notes'], $dbconn); // notes to submit to the operation
        $qty = sanitizeInput($_POST['qty']); // quantity completed
        $rw_reqd = sanitizeInput($_POST['rework_reqd']); // rework required
        $rw_reason = sanitizeInput($_POST['rework_reason']); // reason for rework
        $opnum = sanitizeInput($_POST['opnum']); // operation number itself

        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'"); // grab the item from the operation queue
        $op_queue = $op_queue_qry->fetch_assoc();

        $time = date(DATE_TIME_ABBRV); // grab the current time

        $room_id = $op_queue['room_id']; // assign the room ID for use inside of function incrementJob

        $finalnotes = null; // define final notes as null initially

        // figure out if the bracket is published
        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
        $room = $room_qry->fetch_assoc();

        // next, grab the room bracket and blow it to smitherines
        $full_bracket = json_decode($room['individual_bracket_buildout']);

        // now, we find out what the next op is that we're progressing to
        $next_op_pos = ($rw_reqd === 'true') ? array_search($op_queue['operation_id'], $full_bracket) - 1 : array_search($op_queue['operation_id'], $full_bracket) + 1;
        $next_op = $full_bracket[$next_op_pos]; // grab the next op in the "bracket"

        // get the individual op info
        $next_op_info_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$next_op'");
        $next_op_info = $next_op_info_qry->fetch_assoc();

        // get the next operation's bracket
        switch($next_op_info['bracket']) {
            case 'Sales':
                $bracket = 'sales_bracket';
                $published = 'sales_published';
                break;
            case 'Pre-Production':
                $bracket = 'preproduction_bracket';
                $published = 'preproduction_published';
                break;
            case 'Sample':
                $bracket = 'sample_bracket';
                $published = 'sample_published';
                break;
            case 'Drawer & Doors':
                $bracket = 'doordrawer_bracket';
                $published = 'doordrawer_published';
                break;
            case 'Custom':
                $bracket = 'custom_bracket';
                $published = 'custom_published';
                break;
            case 'Main':
                $bracket = 'main_bracket';
                $published = 'main_published';
                break;
            case 'Shipping':
                $bracket = 'shipping_bracket';
                $published = 'shipping_published';
                break;
            case 'Installation':
                $bracket = 'install_bracket';
                $published = 'install_bracket_published';
                break;
            default:
                $bracket = 'sales_bracket';
                $published = 'sales_published';
                break;
        }

        $cur_op_info_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'");
        $cur_op_info = $cur_op_info_qry->fetch_assoc();

        if(!empty($_FILES['uploadedfile'])) {
            $target_dir = SITE_ROOT . "/attachments/";
            $target_ext = end(explode(".", $_FILES['attachment']['name']));

            if(!file_exists("{$target_dir}{$op_queue['so_parent']}/{$op_queue['room']}/{$room['iteration']}")) {
                mkdir("{$target_dir}{$op_queue['so_parent']}/{$op_queue['room']}/{$room['iteration']}", 0777, true);
            }

            $job_title_fn = str_replace(" ", "_", strtolower($cur_op_info['job_title']));

            $target_file = "{$target_dir}{$op_queue['so_parent']}/{$op_queue['room']}/{$room['iteration']}/{$cur_op_info['op_id']}-$job_title_fn.{$target_ext}";

            $uploadOK = true;
            $upload_err = '';
            $fileType = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);

            if($fileType !== 'pdf') {
                $uploadOK = false;
                $upload_err .= "Incorrect Filetype. PDF only. Received $fileType.";
            }

            if(file_exists($target_file)) {
                $uploadOK = false;
                $upload_err .= "File already exists on the server.";
            }

            if(empty($op_queue['notes'])) { // if no notes exist
                $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />"; // the notes equals the name and the time
            } else { // otherwise notes exist
                $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />" . $op_queue['notes']; // concatenate the notes
            }
        }

        if($rw_reqd === 'true') { // rework is required
            if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', completed = TRUE, partially_completed = FALSE, rework = TRUE, active_employees = NULL WHERE id = $id")) {
                $changed = ["End time" => time(), "Active" => false, "Notes" => $finalnotes, "Qty Completed" => $qty, "Completed" => true, "Active Employees" => 'NULL', "Rework" => true]; // set what has changed for audit trail
                $changed = json_encode($changed); // encode the audit trail for retrieval later

                // if we're able to insert into the audit trail successfully
                if ($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())")) {
                    // now we need to deactivate any old ops if bracket is published
                    $bracket_ops = array();

                    // build the bracket operations
                    $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

                    // create an array of all the ops
                    while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
                        // if the operation is not an x98 operation then add it to the array, otherwise exclude it
                        if((int)substr($all_bracket_ops['op_id'], -2) !== 98) {
                            $bracket_ops[] = $all_bracket_ops['id'];
                        }
                    }

                    // grab all operations in the queue for this room that are not OTF
                    $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$room_id' AND otf_created = FALSE");

                    // if we were able to find any operations in the queue
                    if($op_queue_qry->num_rows > 0) {
                        // for every operation
                        while($op_queue = $op_queue_qry->fetch_assoc()) {
                            // lets find out if this operation is part of the bracket
                            if(in_array($op_queue['operation_id'], $bracket_ops)) {
                                // it's part of the bracket, lets unpublish it
                                $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                            }
                        }
                    }

                    // we've deactivated and/or published all operations related to the queue, now lets create the operation in the queue
                    $ind_op_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$next_op' AND room_id = '$room_id'");

                    // grab the room information for creation of the queued operation
                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
                    $room = $room_qry->fetch_assoc();

                    // now, create the operation that SHOULD be active
                    if($dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, active, completed, rework, qty_requested, 
                             partially_completed, created, iteration) VALUES ('$room_id', '{$room['so_parent']}', '{$room['room']}', '$next_op', 4, FALSE, FALSE, TRUE, 1, FALSE, 
                              UNIX_TIMESTAMP(), '{$room['iteration']}')")) {
                        $dbconn->query("UPDATE rooms SET $bracket = '$next_op' WHERE id = '$room_id'");

                        echo displayToast("warning", "Flagged operation for rework!<br /> Moved to {$next_op_info['op_id']}: {$next_op_info['job_title']} in {$next_op_info['responsible_dept']}.", "Operation Scheduled for Rework");
                    } else {
                        dbLogSQLErr($dbconn);
                    }
                }
            }
        } else {
            // if we've successfully communicated the update to the operation and not completing rework
            if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', completed = TRUE, partially_completed = FALSE, rework = FALSE, active_employees = '[]' WHERE id = $id")) {
                $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Completed"=>true, "Active Employees"=>'[]']; // set what has changed for audit trail
                $changed = json_encode($changed); // encode the audit trail for retrieval later

                if(!empty($_FILES['uploadedfile'])) {
                    if(move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                        echo displayToast("success", "Uploaded file successfully.", "File Uploaded");
                    } else {
                        echo displayToast("error", "Unable to upload file. $upload_err", "File Error");
                    }
                }

                // if we're able to insert into the audit trail successfully
                if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())")) {
                    // find out if the brackets are the same for the next op vs this op
                    if($next_op_info['bracket'] === $cur_op_info['bracket']) {
                        // grab the room and see if the bracket is published
                        $bracket_pub = $room[$published];

                        if((bool)$bracket_pub) { // indeed, bracket is published and we can continue on!
                            /** Goal: Deactivate any ops in the matching bracket **/
                            // now we need to deactivate any old ops if bracket is published
                            $bracket_ops = array();

                            // build the bracket operations
                            $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

                            // create an array of all the ops
                            while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
                                // if the operation is not an x98 operation then add it to the array, otherwise exclude it
                                if((int)substr($all_bracket_ops['op_id'], -2) !== 98) {
                                    $bracket_ops[] = $all_bracket_ops['id'];
                                }
                            }

                            // grab all operations in the queue for this room that are not OTF
                            $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$room_id' AND otf_created = FALSE");

                            // if we were able to find any operations in the queue
                            if($op_queue_qry->num_rows > 0) {
                                // for every operation
                                while($op_queue = $op_queue_qry->fetch_assoc()) {
                                    // lets find out if this operation is part of the bracket
                                    if(in_array($op_queue['operation_id'], $bracket_ops)) {
                                        // it's part of the bracket, lets unpublish it
                                        $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                                    }
                                }
                            }

                            // we've deactivated and/or published all operations related to the queue, now lets create the operation in the queue
                            $ind_op_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$next_op' AND room_id = '$room_id'");

                            // grab the room information for creation of the queued operation
                            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
                            $room = $room_qry->fetch_assoc();

                            // now, create the operation that SHOULD be active
                            if($dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, active, completed, rework, qty_requested, 
                             partially_completed, created, iteration) VALUES ('$room_id', '{$room['so_parent']}', '{$room['room']}', '$next_op', 4, FALSE, FALSE, FALSE, 1, FALSE, 
                              UNIX_TIMESTAMP(), '{$room['iteration']}')")) {
                                $dbconn->query("UPDATE rooms SET $bracket = '$next_op' WHERE id = '$room_id'");

                                if((int)$cur_op_info['id'] === 140) {
                                    $dbconn->query("UPDATE rooms SET order_status = '$' WHERE id = '$room_id'");
                                }

                                echo displayToast("success", "Successfully completed operation.<br /> Moved on to {$next_op_info['op_id']}: {$next_op_info['job_title']} in {$next_op_info['responsible_dept']}.", "Operation Completed");
                            } else {
                                dbLogSQLErr($dbconn);
                            }
                        } else {
                            echo displayToast("warning", "Bracket is no longer published.", "Bracket Unpublished");
                        }
                    } else {
                        if($opnum !== '000') {
                            echo displayToast("info", "Bracket is now closed.", "Bracket Closed");
                        } else {
                            echo displayToast("success", "Closed out operation.", "Closed Operation");
                        }
                    }
                } else {
                    dbLogSQLErr($dbconn);
                    die();
                }
            } else {
                dbLogSQLErr($dbconn);
                die();
            }
        }

        break;
}