<?php
require '../../includes/header_start.php';
require ("../../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/
require("../../includes/classes/queue.php");

//outputPHPErrs();

use Carbon\Carbon; // prep carbon

$queue = new Queue\queue();

switch($_REQUEST['action']) {
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
    case 'display_active_jobs':
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
    case 'display_job_queue':
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

    case 'start_operation':
        $id = sanitizeInput($_REQUEST['id']);
        $operation = sanitizeInput($_REQUEST['operation']);

        $queue->startOp($id, $operation);

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

                switch($op) {
                    case '000: On The Fly':
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

                        break;

                    case '000: Honey Do':
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

                        break;
                    default:
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

                        break;
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
        $id = sanitizeInput($_REQUEST['opID'], $dbconn); // operation ID from the queue
        $notes = sanitizeInput($_REQUEST['notes'], $dbconn); // notes to submit to the operation

        echo $queue->pauseOp($id, $notes);

        break;

    case 'get_stop_info':
        $id = sanitizeInput($_REQUEST['opID']);

        $op_queue_qry = $dbconn->query("SELECT op_queue.id, op_queue.created, operations.op_id, operations.job_title, rooms.room, rooms.so_parent, rooms.room_name, rooms.iteration,
            op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.priority, op_queue.subtask FROM op_queue
              LEFT JOIN operations ON op_queue.operation_id = operations.id
                LEFT JOIN rooms ON op_queue.room_id = rooms.id
                  WHERE op_queue.id = '$id';");

        if($op_queue_qry->num_rows === 1) {
            $op_queue = $op_queue_qry->fetch_assoc();

            if($op_queue['op_id'] !== '000') {
                $rework_box = '<div class="col-md-3"><div class="form-group row"><div class="col-sm-12"><div class="checkbox checkbox-primary"><input id="rework_reqd" type="checkbox" value="true" name="rework"><label for="rework_reqd">Rework Required</label></div></div></div></div>';
                $rework_select = "<div class='col-md-3 rework_reason_group' style='display:none;'>
                                        <select class='form-control' id='rework_reason'>
                                            <optgroup label='Material/Hardware'>
                                                <option value='Material Workmanship'>Material Workmanship</option>
                                                <option value='Material Defective'>Material Defective</option>
                                            </optgroup>
                                            <optgroup label='Workmanship'>
                                                <option value='Workmanship Unacceptable'>Workmanship Unacceptable</option>
                                                <option value='Workmanship Training'>Workmanship Training</option>
                                            </optgroup>
                                            <optgroup label='Equipment'>
                                                <option value='Equipment Failure'>Equipment Failure</option>
                                                <option value='Equipment Maintenance'>Equipment Maintenance</option>
                                                <option value='Equipment Training'>Equipment Training</option>
                                            </optgroup>
                                            <optgroup label='Other'>
                                                <option value='Documentation'>Documentation</option>
                                                <option value='Accuracy'>Accuracy</option>
                                                <option value='Training'>Training</option>
                                            </optgroup>
                                        </select>
                                    </div>";
                $rework_codes = "<div class='col-md-3'><fieldset class='form-group rework_reason_group' style='display:none;'><label for='rework_reason'>Reason for Rework</label></fieldset></div> $rework_select";

                
//                $qty_completed = "<div class='col-md-3'>
//                                    <fieldset class='form-group'>
//                                        <label for='qtyCompleted'>Quantity Completed</label>
//                                        <input type='text' class='form-control' id='qtyCompleted' name='qtyComplete' placeholder='Requested {$op_queue['qty_requested']}'  data-toggle='tooltip' data-placement='top' value='{$op_queue['qty_requested']}'>
//                                    </fieldset>
//                                </div>";

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
                                <div class="col-md-12">
                                    $rework_box
                                    
                                    $rework_codes
                                </div>
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

        $queue->stopOp($id, $notes, $rw_reqd, $rw_reason, $opnum);

        break;
}