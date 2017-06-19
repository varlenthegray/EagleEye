<?php
include_once ("../../includes/header_start.php");
include_once ("../../ondemand/shopfloor/job_functions.php");

switch($_REQUEST['action']) {
    case 'get_op_info':
        $id = sanitizeInput($_REQUEST['opID']);
        $opInfo = $_REQUEST['opInfo'];

        if(!(bool)$opInfo['always_visible']) {
            $op_query = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");

            if($op_query->num_rows === 1) {
                $op_queue = $op_query->fetch_assoc();

                $operation = $opInfo['op_id'] . ": " . $opInfo['job_title'];
                $current_time = date("g:i A");
                $so_name = $op_queue['so_parent'] . "-" . $op_queue['room'];

                if(!empty($op_queue['start_time'])) {
                    $originally_started = "<p>Originally Started " . date(DATE_TIME_ABBRV, $op_queue['start_time'] . "</p>");
                } else {
                    $originally_started = "Unknown";
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
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="start_job" data-startid="$id">Start Job</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
HEREDOC;
            } else {
                http_response_code(400); // send bad request response
                dbLogSQLErr($dbconn);
            }
        } else {
            $op_query = $dbconn->query("SELECT * FROM operations WHERE id = '{$opInfo['id']}'");

            if($op_query->num_rows === 1) {
                $op_info = $op_query->fetch_assoc();

                if(!empty($op_info['sub_tasks'])) {
                    $sub_tasks = json_decode($op_info['sub_tasks']);

                    $operation = $opInfo['op_id'] . ": " . $opInfo['job_title'];
                    $current_time = date("g:i A");
                    $responsible_dept = $opInfo['responsible_dept'];

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
                            <button type="button" class="btn btn-success waves-effect waves-light pull-left" id="add_me" data-startid="$id">Add Me</button>
                            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary waves-effect waves-light" id="start_job" data-startid="$id">Start Operation</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
HEREDOC;
                } else {
                    $operation = $opInfo['op_id'] . ": " . $opInfo['job_title'];
                    $current_time = date("g:i A");
                    $responsible_dept = $opInfo['responsible_dept'];

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
                    <button type="button" class="btn btn-success waves-effect waves-light pull-left" id="add_me" data-taskid="$id">Add Me</button>
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
        $opInfo = $_REQUEST['opInfo'];
        $ae[] = $_SESSION['shop_user']['id'];
        $active_employees = json_encode($ae);
        $subtask = sanitizeInput($_POST['subtask']);
        $notes = sanitizeInput($_POST['notes']);
        $time = date(DATE_TIME_ABBRV);

        $notes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />";

        if(!(bool)$opInfo['always_visible']) {
            $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");

            if($qry->num_rows > 0) {
                $results = $qry->fetch_assoc();

                $changes = null;

                if($results['start_time'] === null) {
                    $active = json_decode($results['active_employees']);

                    if(!empty($active)) {
                        $active_employees = json_encode(array_push($active, $_SESSION['shop_user']['id']));
                    }

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
                } else {
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
            } else {
                $admin_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'");

                if($admin_qry->num_rows > 0) {
                    $admin_results = $admin_qry->fetch_assoc();

                    if((bool)$admin_results['always_visible']) {
                        $dbconn->query("INSERT INTO op_queue (operation_id, start_time, active, created, active_employees) VALUES ('{$admin_results['id']}', UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP(), '$active_employees')");

                        $inserted_id = $dbconn->insert_id;

                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                        $final_changes = json_encode($changes);

                        $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())");

                        echo "success";
                    }
                }
            }
        } else {
            $admin_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'");

            if($admin_qry->num_rows > 0) {
                $admin_results = $admin_qry->fetch_assoc();

                if((bool)$admin_results['always_visible']) {
                    $dbconn->query("INSERT INTO op_queue (operation_id, start_time, active, created, active_employees, started_by, subtask, notes) VALUES ('{$admin_results['id']}', UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP(), '$active_employees', '{$_SESSION['shop_user']['id']}', '$subtask', '$notes')");

                    $inserted_id = $dbconn->insert_id;

                    $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees), "Subtask"=>$subtask, "Notes"=>$notes];
                    $final_changes = json_encode($changes);

                    $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())");

                    echo "success";
                }
            }
        }

        break;
    case 'display_active_jobs':
        $filter = sanitizeInput($_REQUEST['view']);

        if($filter === 'self') {
            $self_qry = $dbconn->query("SELECT op_queue.id AS opID, op_queue.*, operations.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE active_employees LIKE '%\"{$_SESSION['shop_user']['id']}\"%' AND active = TRUE");

            if($self_qry->num_rows > 0) {
                while($self = $self_qry->fetch_assoc()) {
                    $so_id = $self['so_parent'] . "-" . $self['room'];
                    $operation = $self['op_id'] . ": " . $self['job_title'];
                    $start_time = ($self['resumed_time'] === null) ? date(TIME_ONLY, $self['start_time']) : date(TIME_ONLY, $self['resumed_time']);
                    $active_time = ($self['resumed_time'] === null) ? "$('#start_{$self['start_time']}').text(moment({$self['start_time']} * 1000).fromNow(true))" : "$('#start_{$self['start_time']}').text(moment({$self['resumed_time']} * 1000).fromNow(true))";

                    echo "<tr class='cursor-hand update-active-job' data-op-id='{$self['opID']}'>";
                    echo "  <td>$so_id</td>";
                    echo "  <td>{$self['responsible_dept']}</td>";
                    echo "  <td>$operation</td>"; // the operation title itself, easy!
                    echo "  <td>$start_time</td>";
                    echo "  <td id='start_{$self['start_time']}'></td>";
                    echo "<script>$active_time</script>";
                    echo "</tr>";
                }
            } else {
                echo "<tr>";
                echo "  <td colspan='5'>No active operations for you individually.</td>";
                echo "</tr>";
            }
        } else {
            $dept_qry = $dbconn->query("SELECT op_queue.id AS opID, op_queue.*, operations.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE operations.responsible_dept = '$filter' AND active = TRUE");

            if($dept_qry->num_rows > 0) {
                while($dept = $dept_qry->fetch_assoc()) {
                    $so_id = $dept['so_parent'] . "-" . $dept['room'];
                    $operation = $dept['op_id'] . ": " . $dept['job_title'];
                    $release_date = date('n/j/y', $dept['created']);

                    echo "<tr class='cursor-hand update-active-job' data-op-id='{$dept['opID']}'>";
                    echo "  <td>$so_id</td>";
                    echo "  <td>{$dept['responsible_dept']}</td>";
                    echo "  <td>$operation</td>"; // the operation title itself, easy!
                    echo "  <td>$release_date</td>";
                    echo "  <td></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr>";
                echo "  <td colspan='5'>No active operations for $filter.</td>";
                echo "</tr>";
            }
        }

        break;
    case 'display_job_queue':
        $queue = sanitizeInput($_REQUEST['queue']);
        $external_brackets = ['Non-Billable'];

        if(in_array($queue, $external_brackets)) {
            $op_queue_qry = $dbconn->query("SELECT * FROM operations WHERE always_visible = TRUE AND bracket = 'Non-Billable'");

            if($op_queue_qry->num_rows > 0) {
                while($op_queue = $op_queue_qry->fetch_assoc()) {
                    $id = $op_queue['id'];
                    $department = $op_queue['responsible_dept'];
                    $operation = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                    $release_date = date(DATE_DEFAULT, $op_queue['created']);
                    $op_info = ["id"=>$op_queue['id'], "op_id"=>$op_queue['op_id'], "department"=>$op_queue['department'], "job_title"=>$op_queue['job_title'], "responsible_dept"=>$op_queue['responsible_dept'], "always_visible"=>$op_queue['always_visible']];
                    $op_info_payload = json_encode($op_info);

                    echo "<tr class='cursor-hand queue-op-start' data-op-id='$id' data-op-info='$op_info_payload' data-long-op-id='$operation' data-long-part-id='$sonum'>";
                    echo "  <td>Non-Billable</td>";
                    echo "  <td>$department</td>";
                    echo "  <td>$operation</td>";
                    echo "  <td>Now</td>";
                    echo "  <td>&nbsp;</td>";
                    echo "</tr>";
                }
            }
        } else {
            $op_queue_qry = $dbconn->query("SELECT op_queue.id AS queueID, operations.id AS opID, op_queue.*, operations.* 
          FROM op_queue JOIN operations ON op_queue.operation_id = operations.id 
          WHERE (active = FALSE AND completed = FALSE AND published = TRUE AND operations.responsible_dept = '$queue');");

            if($op_queue_qry->num_rows > 0) {
                while($op_queue = $op_queue_qry->fetch_assoc()) {
                    $id = $op_queue['queueID'];
                    $sonum = $op_queue['so_parent'] . "-" . $op_queue['room'];
                    $department = $op_queue['responsible_dept'];
                    $operation = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                    $release_date = date(DATE_DEFAULT, $op_queue['created']);
                    $op_info = ["id"=>$op_queue['id'], "op_id"=>$op_queue['op_id'], "department"=>$op_queue['department'], "job_title"=>$op_queue['job_title'], "responsible_dept"=>$op_queue['responsible_dept'], "always_visible"=>$op_queue['always_visible']];
                    $op_info_payload = json_encode($op_info);

                    echo "<tr class='cursor-hand queue-op-start' data-op-id='$id' data-op-info='$op_info_payload' data-long-op-id='$operation' data-long-part-id='$sonum'>";
                    echo "  <td>$sonum</td>";
                    echo "  <td>$department</td>";
                    echo "  <td>$operation</td>";
                    echo "  <td>$release_date</td>";
                    echo "  <td>&nbsp;</td>";
                    echo "</tr>";
                }
            }

            $op_queue_qry = $dbconn->query("SELECT * FROM operations WHERE always_visible = TRUE AND responsible_dept = '$queue' AND bracket != 'Non-Billable'");

            if($op_queue_qry->num_rows > 0) {
                while($op_queue = $op_queue_qry->fetch_assoc()) {
                    $id = $op_queue['id'];
                    $department = $op_queue['responsible_dept'];
                    $operation = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                    $release_date = date(DATE_DEFAULT, $op_queue['created']);
                    $op_info = ["id"=>$op_queue['id'], "op_id"=>$op_queue['op_id'], "department"=>$op_queue['department'], "job_title"=>$op_queue['job_title'], "responsible_dept"=>$op_queue['responsible_dept'], "always_visible"=>$op_queue['always_visible']];
                    $op_info_payload = json_encode($op_info);

                    echo "<tr class='cursor-hand queue-op-start' data-op-id='$id' data-op-info='$op_info_payload' data-long-op-id='$operation' data-long-part-id='$sonum'>";
                    echo "  <td>---------</td>";
                    echo "  <td>$department</td>";
                    echo "  <td>$operation</td>";
                    echo "  <td>Now</td>";
                    echo "  <td>&nbsp;</td>";
                    echo "</tr>";
                }
            }
        }

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

            if($status === 'Complete') { // if the item is being marked as completed
                // and if we've successfully communicated the update to the operation
                if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', completed = TRUE, partially_completed = FALSE, rework = FALSE, active_employees = '[]' WHERE id = $id")) {
                    $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Completed"=>true, "Active Employees"=>'[]']; // set what has changed for audit trail
                    $changed = json_encode($changed); // encode the audit trail for retrieval later

                    // if we're able to insert into the audit trail successfully
                    if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())")) {
                        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$op_queue['room_id']}'"); // find the room associated with this operation
                        $room_results = $room_qry->fetch_assoc(); // information on the room itself

                        $ind_bracket = json_decode($room_results['individual_bracket_buildout']); // decode the JSON string containing the operations

                        $loc = array_search($op_queue['operation_id'], $ind_bracket) + 1; // find the next operation in the chain

                        $next_operation = $ind_bracket[$loc]; // obtain that operation ID itself

                        // determine if we've done this operation already and it's closing the bracket or not
                        $comp_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$next_operation' AND room_id = '{$op_queue['room_id']}'");

                        $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$next_operation'");
                        $operation = $op_qry->fetch_assoc();

                        switch($operation['department']) {
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

                            case 'Box':
                                $bracket = 'box_bracket';
                                $published = 'box_published';
                                break;

                            default:
                                $bracket = 'sales_bracket';
                                $published = 'sales_published';
                                break;
                        }

                        if($comp_qry->num_rows > 0) {
                            // we've already done this operation, it's closing time for this section
                            $dbconn->query("UPDATE rooms SET $bracket = '0' WHERE id = '{$room_results['id']}");

                            echo displayToast("info", "Notice: the bracket has ended.", "Bracket Ended");
                        } else {
                            // time to find out if the bracket is published
                            if((bool)$room_results[$published] === true) {
                                // bracket IS published
                                if($dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, start_time, end_priority, end_time, active, 
                                  completed, rework, notes, qty_requested, qty_completed, qty_rework, resumed_time, resumed_priority, partially_completed, created, active_employees, 
                                  started_by, completed_by, subtask) VALUES ('{$op_queue['room_id']}', '{$room_results['so_parent']}', '{$room_results['room']}', '$next_operation',
                                  4, NULL, NULL, NULL, FALSE, FALSE, FALSE, NULL, 1, NULL, NULL, NULL, NULL, 0, UNIX_TIMESTAMP(), NULL, NULL, NULL, NULL)")) {
                                        echo displayToast("success", "Operation has been closed.", "Operation closed");
                                    } else {
                                        dbLogSQLErr($dbconn);
                                    }
                            } else { // bracket is NOT published
                                echo displayToast("info", "Notice: The next bracket is not published yet.", "Notice");
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