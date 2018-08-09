<?php
require '../includes/header_start.php';
require '../includes/classes/queue.php';

//outputPHPErrs();

$queue = new \Queue\queue();

switch($_REQUEST['action']) {
    case 'start_operation':
        $id = sanitizeInput($_REQUEST['id']);
        $operation = sanitizeInput($_REQUEST['operation']);

        $queue->startOp($id, $operation);

        break;
    case 'get_start_info':
        $id = sanitizeInput($_REQUEST['opID']);
        $op = sanitizeInput($_REQUEST['op']);

        if(substr($op, 2, 2) !== '00') { // if not an op in the 000's (always visible op)
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
                    case 'TF00: On The Fly':
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
                    case 'HD00: Honey Do':
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
            op_queue.rework, op_queue.active_employees, op_queue.assigned_to, op_queue.urgent, op_queue.subtask FROM op_queue
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

                // TODO: Update notes to be an aggrigate of all notes
                //$notes = (!empty($op_queue['notes']) ? $op_queue['notes'] : "None");

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
}