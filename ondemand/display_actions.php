<?php
require '../includes/header_start.php';
require ("../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/

//outputPHPErrs();

use Carbon\Carbon; // prep carbon

switch($_REQUEST['action']) {
    /** Dashboard */
    case "display_quotes":
        $output = array();
        $i = 0;

        $so_qry = $dbconn->query("SELECT * FROM sales_order ORDER BY so_num DESC");

        while($so = $so_qry->fetch_assoc()) {
            $prev_room = null;
            $prev_seq = null;

            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = {$so['so_num']} AND order_status = '#' ORDER BY room, iteration ASC");

            if($room_qry->num_rows > 0) {
                $output['data'][$i][] = $so['so_num'];
                $output['data'][$i][] = "<strong>{$so['dealer_code']}_{$so['project_name']}</strong>";
                $output['data'][$i][] = null;
                $output['data'][$i][] = null;
                $output['data'][$i]['DT_RowId'] = $so['so_num'];

                $i += 1;

                while($room = $room_qry->fetch_assoc()) {
                    switch($room['order_status']) {
                        case '$':
                            $order_status = '[Job (Deposit Received)]';
                            break;

                        case '#':
                            $order_status = '[Quote (No Deposit)]';
                            break;

                        case '(':
                            $order_status = '[Completed]';
                            break;

                        case ')':
                            $order_status = '[Lost]';
                            break;
                    }

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

                    if($room['room'] === $prev_room && substr($room['iteration'], 0, 1) === $prev_seq) {
                        $indent = "margin-left:55px";
                        $addl_room_info = substr($room['iteration'], -3, 3);
                    } else {
                        $indent = "margin-left:40px";
                        $addl_room_info = "{$room['room']}{$room['iteration']}";
                    }

                    $prev_room = $room['room'];
                    $prev_seq = substr($room['iteration'], 0, 1);

                    $output['data'][$i][] = $room['so_parent'];
                    $output['data'][$i][] = "<span style='$indent'>$addl_room_info-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}-{$room['room_name']}</span>";
                    $output['data'][$i][] = $sales_op_display;
                    $output['data'][$i][] = $sample_op_display;
                    $output['data'][$i]['DT_RowId'] = $room['so_parent'];

                    $i += 1;
                }
            }
        }

        echo json_encode($output);

        break;
    case "display_orders":
        $output = array();
        $i = 0;

        $so_qry = $dbconn->query("SELECT * FROM sales_order ORDER BY so_num DESC");

        while($so = $so_qry->fetch_assoc()) {
            $prev_room = null;
            $prev_seq = null;

            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = {$so['so_num']} AND order_status = '$' ORDER BY room, iteration ASC");

            if($room_qry->num_rows > 0) {
                $output['data'][$i][] = $so['so_num'];
                $output['data'][$i][] = "<strong>{$so['dealer_code']}_{$so['project_name']}</strong>";
                $output['data'][$i][] = null;
                $output['data'][$i][] = null;
                $output['data'][$i]['DT_RowId'] = $so['so_num'];

                $i += 1;

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

                    if($room['room'] === $prev_room && substr($room['iteration'], 0, 1) === $prev_seq) {
                        $indent = "margin-left:55px";
                        $addl_room_info = substr($room['iteration'], -3, 3);
                    } else {
                        $indent = "margin-left:40px";
                        $addl_room_info = "{$room['room']}{$room['iteration']}";
                    }

                    $prev_room = $room['room'];
                    $prev_seq = substr($room['iteration'], 0, 1);

                    $output['data'][$i][] = $room['so_parent'];
                    $output['data'][$i][] = "<span style='$indent'>$addl_room_info-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}-{$room['room_name']}</span>";
                    $output['data'][$i][] = $sales_op_display;
                    $output['data'][$i][] = $sample_op_display;
                    $output['data'][$i]['DT_RowId'] = $room['so_parent'];

                    $i += 1;
                }
            }
        }

        echo json_encode($output);

        break;
    case 'display_ind_active_jobs':
        $output = array();
        $i = 0;

        $self_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, rooms.room, rooms.so_parent, rooms.room_name, rooms.iteration,
            op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.priority, op_queue.subtask, operations.responsible_dept, op_queue.start_time, op_queue.room_id FROM op_queue
              LEFT JOIN operations ON op_queue.operation_id = operations.id
                LEFT JOIN rooms ON op_queue.room_id = rooms.id
                  WHERE active_employees LIKE '%\"{$_SESSION['shop_user']['id']}\"%' AND active = TRUE;");

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
                    $pause_btn = "<button class='btn waves-effect btn-primary pull-left pause-operation' id='{$self['id']}'><i class='zmdi zmdi-pause'></i></button>";
                    $notes_btn = "<button class='btn waves-effect btn-primary pull-left op-notes' style='margin-left:4px;' id='{$self['id']}'><i class='fa fa-sticky-note-o'></i></button>";
                    $margin = '4px';
                    $zeroed = false;
                }

                $start_time = ($self['resumed_time'] === null) ? date(TIME_ONLY, $self['start_time']) : date(TIME_ONLY, $self['resumed_time']);

                if($self['job_title'] === 'Non-Billable' || $self['job_title'] === 'On The Fly') {
                    $so = "---------";
                    $room = "---------";
                } elseif($self['job_title'] === 'Honey Do') {
                    $so = $self['room_id'];
                    $room = "---------";
                } else {
                    $so = "{$self['so_parent']}{$self['room']}-{$self['iteration']}";
                    $room = $self['room_name'];
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
        $queue = sanitizeInput($_REQUEST['queue']);

        $output = array();
        $i = 0;

        $hd_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, op_queue.rework, op_queue.active_employees, 
          op_queue.assigned_to, op_queue.priority, op_queue.room_id FROM op_queue
              JOIN operations ON op_queue.operation_id = operations.id
                  WHERE completed = FALSE AND published = TRUE AND operations.responsible_dept = '$queue'
                    AND (active_employees NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR active_employees IS NULL) 
                      AND (op_queue.assigned_to NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR op_queue.assigned_to IS NULL)
                        AND operations.job_title = 'Honey Do';");

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
                $output['data'][$i][] = "&nbsp;";

                $i += 1;
            }
        }

        $op_queue_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, rooms.room, rooms.so_parent, rooms.room_name, rooms.iteration,
            op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.priority, rooms.product_type, rooms.days_to_ship FROM op_queue
              JOIN operations ON op_queue.operation_id = operations.id
                JOIN rooms ON op_queue.room_id = rooms.id
                  WHERE completed = FALSE AND published = TRUE AND operations.responsible_dept = '$queue'
                    AND (active_employees NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR active_employees IS NULL) 
                      AND (assigned_to NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR assigned_to IS NULL)
                        AND operations.job_title != 'Honey Do' ORDER BY op_queue.priority ASC;");

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

                if(empty($op_queue['priority'])) {
                    $pt_weight_qry = $dbconn->query("SELECT * FROM weights WHERE category = 'product_type' AND `column` = '{$op_queue['product_type']}'");
                    $pt_weight = $pt_weight_qry->fetch_assoc();

                    $dts_qry = $dbconn->query("SELECT * FROM weights WHERE category = 'days_to_ship' AND `column` = '{$op_queue['days_to_ship']}'");
                    $dts = $dts_qry->fetch_assoc();

                    $age = (((time() - $op_queue['created']) / 60) / 60) / 24;

                    $priority = ($pt_weight['weight'] * $dts['weight']) * $age;
                } else {
                    $priority = $op_queue['priority'];
                }

                $output['data'][$i][] = "<button class='btn waves-effect btn-primary pull-left start-operation' id='{$op_queue['id']}'><i class='zmdi zmdi-play'></i></button>";
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = "{$op_queue['so_parent']}{$op_queue['room']}-{$op_queue['iteration']}";
                $output['data'][$i][] = $op_queue['room_name'];
                $output['data'][$i][] = "{$op_queue['op_id']}: {$op_queue['job_title']} $rework";
                $output['data'][$i][] = $release_date;
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = $assignee;
                $output['data'][$i][] = $priority;
                $output['data'][$i]['DT_RowId'] = $op_queue['so_parent'];
                $output['data'][$i]['weight'] = $priority;

                $i += 1;
            }
        }

        $assigned_ops_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, rooms.room, rooms.so_parent, rooms.room_name, rooms.iteration,
            op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.priority FROM op_queue
              JOIN operations ON op_queue.operation_id = operations.id
                JOIN rooms ON op_queue.room_id = rooms.id
                  WHERE completed = FALSE AND published = TRUE AND assigned_to LIKE '%\"{$_SESSION['shop_user']['id']}\"%' ORDER BY op_queue.priority ASC;");

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
                $output['data'][$i][] = $room['room_name'];
                $output['data'][$i][] = "{$assigned_ops['op_id']}: {$assigned_ops['job_title']}";
                $output['data'][$i][] = $release_date;
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = $assignee;
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
                $output['data'][$i][] = "&nbsp;";

                $i += 1;
            }
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
}