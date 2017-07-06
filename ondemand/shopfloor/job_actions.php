<?php
include_once ("../../includes/header_start.php");
include_once ("../../ondemand/shopfloor/job_functions.php");
require ("../../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/

use Carbon\Carbon; // prep carbon

switch($_REQUEST['action']) {
    case 'get_op_info':
        $id = sanitizeInput($_REQUEST['opID']);
        $op = sanitizeInput($_REQUEST['op']);

        if(!is_numeric(strpos($op, "000: "))) { // if not an op in the 000's (always visible op)
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

                if($id !== '106') { // if it's not "on the fly"
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
                                                <input type="text" class="form-control" id="otf_iteration" name="otf_iteration" placeholder="Iteration" maxlength="4" value="0.01">
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
    case 'get_active_job':
        $id = sanitizeInput($_REQUEST['opID']);

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS opID, op_queue.*, operations.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE op_queue.id = '$id'");

        if($op_queue_qry->num_rows === 1) {
            $op_queue = $op_queue_qry->fetch_assoc();

            if(!empty($op_queue['so_parent']) && !empty($op_queue['room'])) {
                $header = "Update " . $op_queue['so_parent'] . "-" . $op_queue['room'] . " operation " . $op_queue['op_id'] . ": " . $op_queue['job_title'];
                $completion_info = "<div class='col-md-3'>
                                        <fieldset class='form-group'>
                                            <label for='qtyCompleted'>Quantity Completed</label>
                                            <input type='text' class='form-control' id='qtyCompleted' name='qtyComplete' placeholder='Requested {$op_queue['qty_requested']}'  data-toggle='tooltip' data-placement='top' value='{$op_queue['qty_requested']}'>
                                        </fieldset>
                                
                                        <fieldset class='form-group'>
                                            <input type='radio' name='completionCode' id='completion_code1' value='Complete'>
                                            <label for='completion_code1'>Completed</label>
                                            <br />
                                            <input type='radio' name='completionCode' id='completion_code2' value='Partially Complete'>
                                            <label for='completion_code2'>Partially Completed</label>
                                            <br />
                                            <input type='radio' name='completionCode' id='completion_code3' value='Rework'>
                                            <label for='completion_code3'>Rework</label>
                                        </fieldset>
                                        
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
                                            </select>
                                        </fieldset>
                                    </div>";

                $nb = 'false';
            } else {
                $sub_task = json_decode($op_queue['sub_tasks']);

                $sub_task_name = $sub_task[$op_queue['subtask_position']];

                $header = "Update non-billable operation " . $sub_task_name . " - " . $op_queue['subtask'];

                $completion_info = '';
                $nb = 'true';
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
                        $completion_info

                        <div class="col-md-9">
                            <fieldset class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" style="height: 107px" placeholder="Notes and information related to operation."></textarea>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="save_job_update" data-id="$id" data-nb="$nb">Complete</button>
                </div>
            </div>
        </div>
HEREDOC;

        } else {
            http_response_code(400); // send bad request response
            dbLogSQLErr($dbconn);
        }

        break;
    case 'update_start_job':
        $id = sanitizeInput($_POST['id']);
        $operation = $_REQUEST['operation'];
        $ae[] = $_SESSION['shop_user']['id'];
        $active_employees = json_encode($ae);
        $subtask = sanitizeInput($_POST['subtask']);
        $notes = sanitizeInput($_POST['notes']);
        $time = date(DATE_TIME_ABBRV);
        $otf = sanitizeInput($_REQUEST['otf']);
        $otf_so = sanitizeInput($_REQUEST['otf_so_num']);
        $otf_room = sanitizeInput($_REQUEST['otf_room']);
        $otf_op = sanitizeInput($_REQUEST['otf_op']);
        $otf_notes = sanitizeInput($_REQUEST['otf_notes']);
        $otf_iteration = sanitizeInput($_REQUEST['otf_iteration']);

        if($otf === 'yes') {
            $otf_notes = "$otf_notes [$time - {$_SESSION['shop_user']['name']}]<br />";
        } else {
            $notes = "$notes [$time - {$_SESSION['shop_user']['name']} <i>OTF Created</i>]<br />";
        }

        if(!is_numeric(strpos($operation, "000:"))) { // if this is not a triple-zero operation
            $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'"); // grab the existing op queue id

            if($qry->num_rows > 0) {  // if we were able to find the operation inside of the queue
                $results = $qry->fetch_assoc(); // grab the information

                $changes = null; // our changes are nothing presently

                $active = json_decode($results['active_employees']); // grab the current list of active employees

                $active[] = $_SESSION['shop_user']['id']; // add individual to the list of active employees

                $active_employees = json_encode($active); // re-encode it for saving

                if($results['start_time'] === null) { // if this op queue item has never been started
                    if($dbconn->query("UPDATE op_queue SET active = TRUE, start_time = UNIX_TIMESTAMP(), active_employees = '$active_employees' WHERE id = '$id'")) {
                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                        $final_changes = json_encode($changes);

                        if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())"))
                            echo "success";
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
                            echo "success - resumed";
                        else
                            dbLogSQLErr($dbconn);
                    } else {
                        dbLogSQLErr($dbconn);
                    }
                }
            } else { // we were unable to find an operation in the queue that existed
                dbLogSQLErr($dbconn); // gonna throw an error here...

                // i don't think this needs to be here, it shouldn't really ever fall into this catch here...
                /*$admin_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'"); // grab the op information

                if($admin_qry->num_rows > 0) { // if we were able to obtain the op itself
                    $admin_results = $admin_qry->fetch_assoc();

                    if((bool)$admin_results['always_visible']) { // if the op is set to always visible
                        $dbconn->query("INSERT INTO op_queue (operation_id, start_time, active, created, active_employees) VALUES ('{$admin_results['id']}', UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP(), '$active_employees')");

                        $inserted_id = $dbconn->insert_id;

                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                        $final_changes = json_encode($changes);

                        $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())");

                        echo "success";
                    }
                }*/
            }
        } else { // this is a triple-zero op
            if($otf === 'yes') { // this is an on-the-fly operation
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

                        echo "success - otf";
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

                        echo "success";
                    } else {
                        echo displayToast("error", "Unable to properly start this operation (not always visible).", "Error Starting Operation.");
                    }
                } else {
                    dbLogSQLErr($dbconn);
                }
            }
        }

        break;
    case 'display_active_jobs':
        $output = array();
        $i = 0;

        $self_qry = $dbconn->query("SELECT op_queue.id AS opID, op_queue.*, operations.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE active_employees LIKE '%\"{$_SESSION['shop_user']['id']}\"%' AND active = TRUE");

        if($self_qry->num_rows > 0) {
            while($self = $self_qry->fetch_assoc()) {
                $operation = $self['op_id'] . ": " . $self['job_title'];
                $start_time = ($self['resumed_time'] === null) ? date(TIME_ONLY, $self['start_time']) : date(TIME_ONLY, $self['resumed_time']);

                $time = Carbon::createFromTimestamp($self['start_time']); // grab the carbon timestamp

                $output['data'][$i][] = $self['so_parent'];
                $output['data'][$i][] = "{$self['room']}-{$self['iteration']}";
                $output['data'][$i][] = $self['responsible_dept'];
                $output['data'][$i][] = $operation;
                $output['data'][$i][] = $start_time;
                $output['data'][$i][] = $time->diffForHumans(null,true); // obtain the difference in readable format for humans!
                $output['data'][$i]['DT_RowId'] = $self['opID'];

                $i += 1;
            }
        } else {
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

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS queueID, operations.id AS opID, op_queue.*, operations.* 
              FROM op_queue JOIN operations ON op_queue.operation_id = operations.id 
              WHERE completed = FALSE AND published = TRUE AND operations.responsible_dept = '$queue' AND (active_employees NOT LIKE '%\"{$_SESSION['shop_user']['id']}\"%' OR active_employees IS NULL) ORDER BY so_parent, room ASC;");

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $id = $op_queue['queueID'];
                $sonum = $op_queue['so_parent'] . "-" . $op_queue['room'];
                $department = $op_queue['responsible_dept'];
                $operation = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                $release_date = date(DATE_DEFAULT, $op_queue['created']);
                $op_info = ["id"=>$op_queue['id'], "op_id"=>$op_queue['op_id'], "department"=>$op_queue['department'], "job_title"=>$op_queue['job_title'], "responsible_dept"=>$op_queue['responsible_dept'], "always_visible"=>$op_queue['always_visible']];
                $op_info_payload = json_encode($op_info);

                $output['data'][$i][] = $op_queue['so_parent'];
                $output['data'][$i][] = "{$op_queue['room']}-{$op_queue['iteration']}";
                $output['data'][$i][] = $department;
                $output['data'][$i][] = $operation;
                $output['data'][$i][] = $release_date;
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i]['DT_RowId'] = $id;

                $i += 1;
            }
        }

        $op_queue_qry = $dbconn->query("SELECT * FROM operations WHERE always_visible = TRUE AND responsible_dept = '$queue' ORDER BY job_title ASC");

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $id = $op_queue['id'];
                $department = $op_queue['responsible_dept'];
                $operation = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                $release_date = date(DATE_DEFAULT, $op_queue['created']);
                $op_info = ["id"=>$op_queue['id'], "op_id"=>$op_queue['op_id'], "department"=>$op_queue['department'], "job_title"=>$op_queue['job_title'], "responsible_dept"=>$op_queue['responsible_dept'], "always_visible"=>$op_queue['always_visible']];
                $op_info_payload = json_encode($op_info);

                $output['data'][$i][] = "---------";
                $output['data'][$i][] = "---";
                $output['data'][$i][] = $department;
                $output['data'][$i][] = $operation;
                $output['data'][$i][] = "Now";
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i][] = "&nbsp;";
                $output['data'][$i]['DT_RowId'] = $id;

                $i += 1;
            }
        }

        echo json_encode($output);

        break;
    case 'update_active_job':
        $id = sanitizeInput($_POST['opID'], $dbconn); // operation ID from the queue
        $notes = sanitizeInput($_POST['notes'], $dbconn); // notes to submit to the operation
        $status = sanitizeInput($_POST['status'], $dbconn); // status of the operation (complete, partial, rework)
        $qty = sanitizeInput($_POST['qty']); // quantity completed
        $nb = sanitizeInput($_POST['nb']); // non-billable

        if($nb === 'true') { // if it is a non-billable item
            $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");
            $op_queue = $op_queue_qry->fetch_assoc();

            $time = date(DATE_TIME_ABBRV); // grab the current time

            $room_id = $op_queue['room_id']; // assign the room ID for use inside of function incrementJob

            $finalnotes = null; // define final notes as null initially

            if(empty($op_queue['notes'])) { // if no notes exist
                $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />"; // the notes equals the name and the time
            } else { // otherwise notes exist
                $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />" . $op_queue['notes']; // concatenate the notes
            }

            if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', completed = TRUE, partially_completed = FALSE, rework = FALSE, active_employees = '[]' WHERE id = $id")) {
                $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Completed"=>true, "Active Employees"=>'[]']; // set what has changed for audit trail
                $changed = json_encode($changed); // encode the audit trail for retrieval later

                // if we're able to insert into the audit trail successfully
                if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())")) {
                    echo displayToast("success", "Non-billable operation has been completed.", "Non-billable Completed");
                } else {
                    dbLogSQLErr($dbconn);
                    die();
                }
            } else {
                dbLogSQLErr($dbconn);
                die();
            }
        } else { // it's not a nonbillable item
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

            if($status === 'Complete') { // if the item is being marked as completed
                // and if we've successfully communicated the update to the operation
                if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', completed = TRUE, partially_completed = FALSE, rework = FALSE, active_employees = '[]' WHERE id = $id")) {
                    $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Completed"=>true, "Active Employees"=>'[]']; // set what has changed for audit trail
                    $changed = json_encode($changed); // encode the audit trail for retrieval later

                    // if we're able to insert into the audit trail successfully
                    if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())")) {
                        // figure out if the bracket is published
                        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
                        $room = $room_qry->fetch_assoc();

                        // next, grab the individual bracket and blow it to smitherines
                        $full_bracket = json_decode($room['individual_bracket_buildout']);

                        // now, we find out what the next op is that we're progressing to
                        $next_op_pos = array_search($op_queue['operation_id'], $full_bracket) + 1;
                        $next_op = $full_bracket[$next_op_pos]; // grab the next op in the "bracket"

                        // get the individual op info
                        $next_op_info_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$next_op'");
                        $next_op_info = $next_op_info_qry->fetch_assoc();

                        $cur_op_info_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'");
                        $cur_op_info = $cur_op_info_qry->fetch_assoc();

                        // find out if the brackets are the same for the next op vs this op
                        if($next_op_info['bracket'] === $cur_op_info['bracket']) {
                            // brackets match, is the bracket that we're currently in published?
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

                            // grab the room and see if the bracket is published
                            $bracket_pub = $room[$published];

                            if((bool)$bracket_pub) { // indeed, bracket is published and we can continue on!
                                // now we need to create the ops and/or activate the appropriate ops based on what's selected (and deactivate any old ones) if bracket is published
                                $ops = array();

                                // bracket is published, time to build the bracket operations
                                $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

                                // create an array of all the ops
                                // ToDo: Verify this will not cause issues setting x98 operations
                                while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
                                    // if the operation is not an x98 operation then add it to the array, otherwise exclude it
                                    if((int)substr($all_bracket_ops['op_id'], -2) !== 98) {
                                        $ops[] = $all_bracket_ops['id'];
                                    }
                                }

                                // grab all operations in the queue for this room that are not OTF
                                $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$room_id' AND otf_created = FALSE");

                                // if we were able to find any operations in the queue
                                if($op_queue_qry->num_rows > 0) {
                                    // for every operation
                                    while($op_queue = $op_queue_qry->fetch_assoc()) {
                                        // lets find out if this operation is part of the bracket
                                        if(in_array($op_queue['operation_id'], $ops)) {
                                            // it's part of the bracket, is it the next operation?
                                            if($op_queue['operation_id'] === $next_op) {
                                                // it is this operation, is it unpublished?
                                                if(!(bool)$op_queue['published']) {
                                                    // ToDo: Has this been verified that the operation we're republishing is NOT one that has already been completed?
                                                    // publish it, foo!
                                                    $dbconn->query("UPDATE op_queue SET published = TRUE where id = '{$op_queue['id']}'");
                                                }
                                            } else {
                                                echo "Everything in the queue did NOT match this op, so we've deactivated ALL OF IT!";
                                                // nope, it's part of the queue but it's not this operation, lets unpublish it!
                                                $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                                            }
                                        }
                                    }
                                } else {
                                    // no operations exist in the queue for this room that are NOT OTF! BLANK SLATE BABY!

                                    // grab the room information for creation of the queued operation
                                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
                                    $room = $room_qry->fetch_assoc();

                                    // now, create the operation that SHOULD be active
                                    $dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, active, completed, rework, qty_requested, 
                                     partially_completed, created, iteration) VALUES ('$room_id', '{$room['so_parent']}', '{$room['room']}', '$next_op', 4, FALSE, FALSE, FALSE, 1, FALSE, 
                                      UNIX_TIMESTAMP(), '{$room['iteration']}')");
                                }

                                // we've deactivated and/or published all operations related to the queue, now lets create the operation in the queue if it's not there
                                $ind_op_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$next_op' AND room_id = '$room_id'");

                                if($ind_op_qry->num_rows === 0) {
                                    // grab the room information for creation of the queued operation
                                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
                                    $room = $room_qry->fetch_assoc();

                                    // now, create the operation that SHOULD be active
                                    $dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, active, completed, rework, qty_requested, 
                                     partially_completed, created, iteration) VALUES ('$room_id', '{$room['so_parent']}', '{$room['room']}', '$next_op', 4, FALSE, FALSE, FALSE, 1, FALSE, 
                                      UNIX_TIMESTAMP(), '{$room['iteration']}')");

                                    $dbconn->query("UPDATE rooms SET $bracket = '$next_op' WHERE id = '$room_id'");

                                    echo displayToast("success", "Successfully completed operation.<br /> Moved on to {$next_op_info['op_id']}: {$next_op_info['job_title']} in {$next_op_info['responsible_dept']}.", "Operation Completed");
                                } else {
                                    displayToast("error", "Found operations in the queue still active.", "Active Operation Error");
                                }
                            } else {
                                echo displayToast("warning", "Bracket is no longer published.", "Bracket Unpublished");
                            }
                        } else {
                            echo displayToast("info", "Bracket is now closed.", "Bracket Closed");
                        }
                    } else {
                        dbLogSQLErr($dbconn);
                        die();
                    }
                } else {
                    dbLogSQLErr($dbconn);
                    die();
                }
            } elseif($status === 'Partially Complete') {
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
            } else {
                if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', rework = TRUE, completed = FALSE WHERE id = $id")) {
                    $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Rework"=>true];
                    $changed = json_encode($changed);

                    if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())"))
                        echo displayToast("info", "Operation has been sent to the previous department.", "Operation flagged for Rework");
                    else
                        dbLogSQLErr($dbconn);
                } else {
                    dbLogSQLErr($dbconn);
                    die();
                }
            }
        }

        break;
    case 'update_brackets':
        $room = sanitizeInput($_REQUEST['room'], $dbconn);
        $sonum = sanitizeInput($_REQUEST['sonum'], $dbconn);

        // grab the individual bracket
        $indv_bracket_qry = $dbconn->query("SELECT individual_bracket_buildout FROM rooms WHERE so_parent = '$sonum' AND room = '$room'");
        $indv_bracket_results = $indv_bracket_qry->fetch_assoc();

        $op_ids = json_decode($indv_bracket_results['individual_bracket_buildout']);

        $final['ops'] = array();

        foreach($op_ids as $ind_id) {
            $qry = $dbconn->query("SELECT bracket, job_title, op_id, id FROM operations WHERE id = '$ind_id'");
            $result = $qry->fetch_assoc();

            if(!empty($result))
                $final['ops'][] = $result;
        }


        $qry = $dbconn->query("SELECT sales_published, preproduction_published, sample_published, doordrawer_published, custom_published, box_published FROM rooms WHERE so_parent = '$sonum' AND room = '$room'");
        $result = $qry->fetch_row();

        $final['pub'] = $result;

        echo json_encode($final);

        break;
    case 'save_room':
        $room = sanitizeInput($_POST['room'], $dbconn);
        $room_name = sanitizeInput($_POST['room_name'], $dbconn);
        $product_type = sanitizeInput($_POST['product_type'], $dbconn);
        $remodel_required = sanitizeInput($_POST['remodel_required'], $dbconn);
        $room_notes = sanitizeInput($_POST['room_notes'], $dbconn);
        $sales_bracket = sanitizeInput($_POST['sales_bracket'], $dbconn);
        $pre_prod_bracket = sanitizeInput($_POST['pre_prod_bracket'], $dbconn);
        $sample_bracket = sanitizeInput($_POST['sample_bracket'], $dbconn);
        $door_drawer_bracket = sanitizeInput($_POST['door_drawer_bracket'], $dbconn);
        $custom_bracket = sanitizeInput($_POST['custom_bracket'], $dbconn);
        $box_bracket = sanitizeInput($_POST['box_bracket'], $dbconn);
        $so_num = sanitizeInput($_POST['add_to_sonum'], $dbconn);

        $qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' AND room = '$room'");

        if($qry->num_rows === 1) {
            $update = $dbconn->query("UPDATE rooms SET room_name = '$room_name', product_type = '$product_type', remodel_reqd = '$remodel_required', room_notes = '$room_notes',
              sales_bracket = '$sales_bracket', preproduction_bracket = '$pre_prod_bracket', sample_bracket = '$sample_bracket', doordrawer_bracket = '$door_drawer_bracket',
              custom_bracket = '$custom_bracket', main_bracket = b$box_bracketox_bracket, sales_bracket_priority = 4, preproduction_bracket_priority = 4, sample_bracket_priority = 4, 
              doordrawer_bracket_priority = 4, custom_bracket_priority = 4, box_bracket_priority = 4 WHERE so_parent = '$so_num' AND room = '$room'");

            if($update) {
                echo "success - update";
            } else {
                dbLogSQLErr($dbconn);
            }
        } else {
            $full_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket != 'Admin'");

            if($full_ops_qry->num_rows > 0) {
                while($ops = $full_ops_qry->fetch_assoc()) {
                    $bracket[] = $ops['id'];
                }
            }

            $bracket = json_encode($bracket);

            $query = $dbconn->query("INSERT INTO rooms (so_parent, room, room_name, product_type, remodel_reqd, room_notes, sales_bracket, 
          preproduction_bracket, sample_bracket, doordrawer_bracket, custom_bracket, main_bracket, sales_bracket_priority, preproduction_bracket_priority, 
          sample_bracket_priority, doordrawer_bracket_priority, custom_bracket_priority, box_bracket_priority, individual_bracket_buildout) 
          VALUES ('$so_num', '$room', '$room_name', '$product_type', '$remodel_required', '$room_notes', '$sales_bracket', '$pre_prod_bracket',
          '$sample_bracket', '$door_drawer_bracket', '$custom_bracket', b$box_bracketox_bracket, 4, 4, 4, 4, 4, 4, '$bracket')");

            if($query) {
                echo "success";
            } else {
                dbLogSQLErr($dbconn);
            }
        }

        break;
    case 'edit_room':
        $room_id = sanitizeInput($_POST['roomID'], $dbconn);

        $qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");

        if($qry->num_rows === 1) {
            $result = $qry->fetch_assoc();

            generateOpQueue($room_id, 'sales_published', 'sales_bracket', 'Sales');
            generateOpQueue($room_id, 'preproduction_published', 'preproduction_bracket', 'Pre-Production');
            generateOpQueue($room_id, 'sample_published', 'sample_bracket', 'Sample');
            generateOpQueue($room_id, 'doordrawer_published', 'doordrawer_bracket', 'Drawer & Doors');
            generateOpQueue($room_id, 'custom_published', 'custom_bracket', 'Custom');
            generateOpQueue($room_id, 'box_published', 'box_bracket', 'Box');

            echo json_encode($result);
        } else {
            dbLogSQLErr($dbconn);
            die();
        }

        break;
    case 'update_individual_bracket':
        $bracket = sanitizeInput($_POST['payload'], $dbconn);
        $sonum = sanitizeInput($_POST['sonum'], $dbconn);
        $room = sanitizeInput($_POST['room'], $dbconn);
        $published = json_decode($_POST['published']);

        generateOpQueue($room_id, 'sales_published', 'sales_bracket', 'Sales');
        generateOpQueue($room_id, 'preproduction_published', 'preproduction_bracket', 'Pre-Production');
        generateOpQueue($room_id, 'sample_published', 'sample_bracket', 'Sample');
        generateOpQueue($room_id, 'doordrawer_published', 'doordrawer_bracket', 'Drawer & Doors');
        generateOpQueue($room_id, 'custom_published', 'custom_bracket', 'Custom');
        generateOpQueue($room_id, 'box_published', 'box_bracket', 'Box');

        if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$bracket', sales_published = '{$published[0]}', preproduction_published = '{$published[1]}', 
          sample_published = '{$published[2]}', doordrawer_published = '{$published[3]}', custom_published = '{$published[4]}', box_published = '{$published[5]}'
          WHERE so_parent = '$sonum' AND room = '$room'")) {
            echo "success";
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'update_in_queue':
        $room_id = sanitizeInput($_POST['roomID']);
        $new_op_id = sanitizeInput($_POST['opID']);

        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'"); // room ID
        $room_results = $room_qry->fetch_assoc();

        $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$new_op_id'"); // new operation ID
        $new_op_results = $op_qry->fetch_assoc();

        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$room_id'"); // operation queue

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $this_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'"); // for each op in op queue for this room
                $this_op_results = $this_op_qry->fetch_assoc(); // get the old op information

                if($this_op_results['responsible_dept'] === $new_op_results['responsible_dept']) { // if the departments are the same
                    if($dbconn->query("UPDATE op_queue SET published = FALSE where ID = '{$this_op_results['id']}'"))
                        echo "success";

                    generateOpQueue($room_id, 'sales_published', 'sales_bracket', 'Sales');
                    generateOpQueue($room_id, 'preproduction_published', 'preproduction_bracket', 'Pre-Production');
                    generateOpQueue($room_id, 'sample_published', 'sample_bracket', 'Sample');
                    generateOpQueue($room_id, 'doordrawer_published', 'doordrawer_bracket', 'Drawer & Doors');
                    generateOpQueue($room_id, 'custom_published', 'custom_bracket', 'Custom');
                    generateOpQueue($room_id, 'box_published', 'box_bracket', 'Box');
                }
            }
        }

        break;
    case 'get_all_ops':
        $qry = $dbconn->query("SELECT id, op_id, bracket, job_title, responsible_dept FROM operations");

        $ops = null;

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $ops[] = $result;
            }
        }

        $ops_out = json_encode($ops);

        echo $ops_out;

        break;
    case 'manage_bracket':
        $room_id = sanitizeInput($_REQUEST['roomid']);

        // grab the individual bracket
        $indv_bracket_qry = $dbconn->query("SELECT individual_bracket_buildout FROM rooms WHERE id = $room_id");
        $indv_bracket_results = $indv_bracket_qry->fetch_assoc();

        $op_ids = json_decode($indv_bracket_results['individual_bracket_buildout']);

        // grab all operations available
        $all_ops_qry = $dbconn->query("SELECT * FROM operations WHERE always_visible = FALSE ORDER BY op_id ASC");

        $output = array();

        if($all_ops_qry->num_rows > 0) {
            while($all_ops = $all_ops_qry->fetch_assoc()) {
                if(in_array($all_ops['id'], $op_ids)) {
                    $output[$all_ops['department']] .= "<option value='{$all_ops['id']}'>{$all_ops['op_id']}-{$all_ops['job_title']}</option>"; // create the operation as "not selected"
                } else {
                    $output[$all_ops['department']] .=  "<option value='{$all_ops['id']}' selected>{$all_ops['op_id']}-{$all_ops['job_title']}</option>"; // create the operation as "selected"
                }
            }
        }

        $qry = $dbconn->query("SELECT sales_published, preproduction_published, sample_published, doordrawer_published, custom_published, main_published FROM rooms WHERE so_parent = '$sonum' AND room = '$room'");
        $result = $qry->fetch_row();

        $output['pub'] = $result;

        echo json_encode($output);

        break;
    case 'add_me':
        $id = sanitizeInput($_REQUEST['id']);

        $queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");

        if($queue_qry->num_rows === 1) {
            $query = $queue_qry->fetch_assoc();

            $active_ids = json_decode($query['active_employees']);

            if(!in_array($_SESSION['shop_user']['id'], $active_ids)) { // if the shop employee is NOT active on this task
                array_push($active_ids, $_SESSION['shop_user']['id']); // add them as active to the array

                $active_encoded = json_encode($active_ids);

                if($dbconn->query("UPDATE op_queue SET active_employees = '$active_encoded' WHERE id = '$id'")) {
                    $changed = json_encode(["Active Employees"=>$active_encoded]);

                    $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())");

                    echo displayToast("success", "Added {$_SESSION['shop_user']['name']} to {$query['so_parent']}-{$query['room']}.", "Added to room.");
                } else {
                    dbLogSQLErr($dbconn);
                }
            } else {
                echo displayToast("info", "You are already active on this task.", "Already Active");
            }
        } else {
            echo displayToast("error", "Unable to add to task. More than one operation ID exists.", "Error");
        }

        break;
    default:
        die();

        break;
}