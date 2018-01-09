<?php
require_once ("../../includes/header_start.php");
require_once("../../includes/classes/mail_handler.php");

$mail = new \MailHandler\mail_handler();

//outputPHPErrs();

$action = sanitizeInput($_REQUEST['action']);

switch($action) {
    case 'submit_feedback':
        $task_desc = sanitizeInput($_REQUEST['description']);
        $assignee = sanitizeInput($_REQUEST['assignee']);
        $priority = sanitizeInput($_REQUEST['priority']);

        if($_SESSION['userInfo']['id'] === '16') {
            $submitted_by = $_SESSION['shop_user']['id'];
            $submitted_name = $_SESSION['shop_user']['name'];
        } else {
            $submitted_by = $_SESSION['userInfo']['id'];
            $submitted_name = $_SESSION['userInfo']['name'];
        }

        $notify_qry = $dbconn->query("SELECT * FROM user WHERE id = $assignee");
        $notify = $notify_qry->fetch_assoc();

        if($dbconn->query("INSERT INTO tasks (description, created, last_updated, priority, assigned_to, due_date, submitted_by, resolved)
         VALUES ('$task_desc', UNIX_TIMESTAMP(), null, '$priority', $assignee, null, $submitted_by, FALSE);")) {
            $dbconn->query("INSERT INTO alerts (type, status, message, time_created, time_acknowledged, alert_user, icon, type_id, color) VALUES ('feedback', 'new', 'New feedback submitted by $submitted_name.', UNIX_TIMESTAMP(), null, $assignee, 'icon-bubble', $dbconn->insert_id, 'bg-warning')");

            if(!empty($notify['email']))
                $mail->sendMessage($notify['email'], $_SESSION['userInfo']['email'], 'New Feedback Logged', mail_nl2br($task_desc));

            echo displayToast("success", "Successfully logged feedback.", "Feedback Logged");
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'get_task_list':
        $i = 0;

        $output = array();

        $tasks_qry = $dbconn->query("SELECT tasks.id AS taskID, user.name AS userName, tasks.*, user.* FROM tasks LEFT JOIN user ON tasks.assigned_to = user.id WHERE resolved = FALSE;");

        if($tasks_qry->num_rows > 0) {
            while($task = $tasks_qry->fetch_assoc()) {
                $short_desc = strip_tags(substr($task['description'], 0, 40) . "...");

                if(!empty($task['last_updated'])) {
                    $last_updated = date(DATE_TIME_ABBRV, $task['last_update']);
                } else {
                    $last_updated = "New";
                }

                if(empty($task['name'])) {
                    $name = "<i>Unassigned</i>";
                } else {
                    $name = $task['taskName'];
                }

                if($task['eta'] > 1) {
                    $humanized_eta = "hrs";
                } else {
                    $humanized_eta = "hr";
                }

                $created = date(DATE_TIME_ABBRV, $task['created']);

                $output['data'][$i][] = $task['taskID'];
                $output['data'][$i][] = $task['userName'];
                $output['data'][$i][] = $created;
                $output['data'][$i][] = $short_desc;
                $output['data'][$i][] = $task['priority'];
                $output['data'][$i][] = $task['eta_hrs'] . " $humanized_eta";
                $output['data'][$i][] = $task['pct_completed'] * 100 . "%";
                $output['data'][$i][] = $last_updated;
                $output['data'][$i]['DT_RowId'] = $task['taskID'];

                $i += 1;
            }
        } else {
            $output['data'][0][] = "&nbsp;";
            $output['data'][0][] = "&nbsp;";
            $output['data'][0][] = "None Available";
            $output['data'][0][] = "&nbsp;";
            $output['data'][0][] = "&nbsp;";
            $output['data'][0][] = "&nbsp;";
            $output['data'][0][] = "&nbsp;";
            $output['data'][0][] = "&nbsp;";
        }

        echo json_encode($output);

        break;
    case 'get_task_info':
        $task_id = sanitizeInput($_REQUEST['task_id']);
        $user_opts = null;
        $created_by = null;

        $task_qry = $dbconn->query("SELECT * FROM tasks WHERE id = '$task_id'");

        if($task_qry->num_rows === 1) {
            $task = $task_qry->fetch_assoc();

            $user_qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE ORDER BY name ASC;");

            while($user = $user_qry->fetch_assoc()) {
                $selected = ($user['id'] === $task['assigned_to']) ? "selected" : null;

                $user_opts .= "<option value='{$user['id']}' $selected>{$user['name']}</option>";

                if($user['id'] === $task['submitted_by']) $created_by = $user['name'];
            }

            $perform_qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE ORDER BY name ASC;");

            while($perform = $perform_qry->fetch_assoc()) {
                $per_selected = ($perform['id'] === $task['perform_by']) ? "selected" : null;

                $perform_opts .= "<option value='{$perform['id']}' $per_selected>{$perform['name']}</option>";
            }

            $pct_complete = $task['pct_completed'] * 100;

            switch($task['priority']) {
                case '3 - End of Week':
                    $sel_we = "selected";
                    break;

                case '2 - End of Day':
                    $sel_de = "selected";
                    break;

                case '1 - Immediate':
                    $sel_imm = "selected";
                    break;

                default:
                    $sel_we = "selected";
                    break;
            }

            $task_description = nl2br($task['description']);

            $task_initial_comment_time = date(DATE_TIME_ABBRV, $task['created']);

            $desc_nl = str_ireplace("<br />", "\r\n", $task['description']);

            $notes_desc = "<strong>($created_by $task_initial_comment_time):</strong> $task_description";

            $addl_notes_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'task_reply' AND type_id = $task_id");

            if($addl_notes_qry->num_rows > 0) {
                while($addl_notes = $addl_notes_qry->fetch_assoc()) {
                    $comment_time = date(DATE_TIME_ABBRV, $addl_notes['timestamp']);

                    $notes_desc .= "<br /><br /><strong>({$addl_notes['name']} $comment_time):</strong> {$addl_notes['note']}";
                }
            }

            echo /** @lang HTML */
            <<<HEREDOC
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form name="task_details" id="task_details">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4 class="modal-title" id="modalTaskTitle">Task Details</h4>
                            </div>
                            <div class="modal-body">
                                <div class="task_hide">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table>
                                                <tr>
                                                    <td>Assigned To:</td>
                                                    <td>
                                                        <select name="assigned_to" id="assigned_to" class="form-control">
                                                            $user_opts                                        
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Perform By:</td>
                                                    <td>
                                                        <select name="perform_by" id="perform_by" class="form-control">
                                                            <option value="" selected></option>
                                                            $perform_opts                                        
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Priority:</td>
                                                    <td>
                                                        <select name="priority" id="priority" class="form-control">
                                                            <option value="3 - End of Week" $sel_we>End of Week</option>
                                                            <option value="2 - End of Day" $sel_de>End of Day</option>
                                                            <option value="1 - Immediate" $sel_imm>Immediate</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>ETA</td>
                                                    <td><input type="text" value="{$task['eta_hrs']}" placeholder="Hours" name="eta" id="eta" class="form-control" style="width:85%;float:left;" maxlength="2" /><span style="float:right;line-height:30px;">&nbsp;HRS</span></td>
                                                </tr>
                                                <tr>
                                                    <td>% Complete:</td>
                                                    <td><input type="text" value="$pct_complete" placeholder="Percent" name="pct_completed" id="pct_completed" class="form-control" style="width:90%;float:left;" maxlength="3"><span style="float:right;line-height:30px;">&nbsp;%</span> </td>
                                                </tr>
                                            </table>
            
                                            <div id="task_description" class="task_description">$notes_desc</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row" style="margin-top:15px;">
                                        <div class="col-md-12">
                                            <textarea class="form-control" name="addl_notes" id="addl_notes" style="width:100%;height:100px;"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="split_body" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <textarea class="form-control" name="split-text-1" id="split-text-1" style="width:100%;height:150px;">$desc_nl</textarea>
                                        </div>
                                    </div>
                    
                                    <div class="row" style="margin-top:5px;">
                                        <div class="col-md-1" style="padding-top:3px;"><label for="split_feedback_to_1">Notify: </label></div>
                    
                                        <div class="col-md-4">
                                            <select name="split_feedback_to_1" id="split_feedback_to_1" class="form-control">
                                                <optgroup label="Office">
                                                    <option value="9">Production Administrator</option>
                                                    <option value="14">Shop Foreman</option>
                                                    <option value="7">Robert</option>
                                                    <option value="1">IT</option>
                                                    <option value="10">Engineering</option>
                                                    <option value="8">Accounting</option>
                                                </optgroup>
                    
                                                <optgroup label="Shop">
                                                    <option value="15">Box</option>
                                                    <option value="12">Customs</option>
                                                    <option value="11">Assembly</option>
                                                    <option value="22">Finishing</option>
                                                    <option value="11">Shipping</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-1" style="padding-top:3px;"><label for="split_1_priority">Priority: </label></div>
                                        
                                        <div class="col-md-4">
                                            <select name="split_1_priority" id="split_1_priority" class="form-control">
                                                <option value="3 - End of Week">End of Week</option>
                                                <option value="2 - End of Day">End of Day</option>
                                                <option value="1 - Immediate">Immediate</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row" style="margin-top:20px;">
                                        <div class="col-md-12">
                                            <textarea class="form-control" name="split-text-2" id="split-text-2" style="width:100%;height:150px;">$desc_nl</textarea>
                                        </div>
                                    </div>
                    
                                    <div class="row" style="margin-top:5px;">
                                        <div class="col-md-1" style="padding-top:3px;"><label for="split_feedback_to_2">Notify: </label></div>
                    
                                        <div class="col-md-4">
                                            <select name="split_feedback_to_2" id="split_feedback_to_2" class="form-control">
                                                <optgroup label="Office">
                                                    <option value="9">Production Administrator</option>
                                                    <option value="14">Shop Foreman</option>
                                                    <option value="7">Robert</option>
                                                    <option value="1">IT</option>
                                                    <option value="10">Engineering</option>
                                                    <option value="8">Accounting</option>
                                                </optgroup>
                    
                                                <optgroup label="Shop">
                                                    <option value="15">Box</option>
                                                    <option value="12">Customs</option>
                                                    <option value="11">Assembly</option>
                                                    <option value="22">Finishing</option>
                                                    <option value="11">Shipping</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-1" style="padding-top:3px;"><label for="split_2_priority">Priority: </label></div>
                                        
                                        <div class="col-md-4">
                                            <select name="split_2_priority" id="split_2_priority" class="form-control">
                                                <option value="3 - End of Week">End of Week</option>
                                                <option value="2 - End of Day">End of Day</option>
                                                <option value="1 - Immediate">Immediate</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                                <!--<button type="button" class="btn btn-success-outline waves-effect waves-light" id="create_op_btn" data-taskid="{$task['id']}">Create Op</button>-->
                                <button type="button" class="btn btn-primary-outline waves-effect waves-light" id="split_task_btn" data-taskid="{$task['id']}">Split Task</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light" id="update_task_btn" data-taskid="{$task['id']}">Update Task</button>
                            </div>
                            
                            <input type='hidden' name='split_task_enabled' value='0' id='split_task_enabled' />
                        </form>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
HEREDOC;
        } else {
            echo displayToast("error", "Invalid task ID.", "Invalid Task");
            http_response_code(400);
        }

        break;
    case 'update_task':
        $task_id = sanitizeInput($_REQUEST['task_id']);

        $task_split = (bool)$_REQUEST['split_task_enabled'];

        $task_qry = $dbconn->query("SELECT * FROM tasks WHERE id = $task_id");
        $task = $task_qry->fetch_assoc();

        if($task_split) {
            $split_1_text = sanitizeInput($_REQUEST['s_text_1']);
            $split_1_notify = sanitizeInput($_REQUEST['split_feedback_to_1']);
            $split_1_priority = sanitizeInput($_REQUEST['split_1_priority']);

            $split_2_text = sanitizeInput($_REQUEST['s_text_2']);
            $split_2_notify = sanitizeInput($_REQUEST['split_feedback_to_2']);
            $split_2_priority = sanitizeInput($_REQUEST['split_2_priority']);

            $db_stmt = $dbconn->prepare("INSERT INTO tasks (description, created,  priority, assigned_to, submitted_by, resolved, split_by) VALUES (?, UNIX_TIMESTAMP(), ?, ?, {$task['submitted_by']}, FALSE, {$_SESSION['userInfo']['id']});");

            if(!$db_stmt) dbLogSQLErr($dbconn);

            $db_stmt->bind_param("ssi", $desc, $priority, $assignee);

            try {
                $desc = $split_1_text;
                $priority = $split_1_priority;
                $assignee = $split_1_notify;

                $db_stmt->execute();
            } catch(Exception $e) {
                echo displayToast("error", "Unable to create task 1: $e", "Task 1 Error");
            }

            try {
                $desc = $split_2_text;
                $priority = $split_2_priority;
                $assignee = $split_2_notify;

                $db_stmt->execute();
            } catch(Exception $e) {
                echo displayToast("error", "Unable to create task 2: $e", "Task 2 Error");
            }

            $dbconn->query("UPDATE tasks SET pct_completed = 1, resolved = TRUE, last_updated = UNIX_TIMESTAMP() WHERE id = $task_id");

            $usr_1_qry = $dbconn->query("SELECT email FROM user WHERE id = $split_1_notify");
            $usr_1 = $usr_1_qry->fetch_assoc();

            $usr_2_qry = $dbconn->query("SELECT email FROM user WHERE id = $split_2_notify");
            $usr_2 = $usr_2_qry->fetch_assoc();

            $mail->sendMessage($usr_1['email'], $_SESSION['userInfo']['email'], "Task Split Assigned to You", nl2br($split_1_text));
            $mail->sendMessage($usr_2['email'], $_SESSION['userInfo']['email'], "Task Split Assigned to You", nl2br($split_2_text));

            echo displayToast("success", "Split task successfully.", "Task Split");

            $db_stmt->close();
        } else {
            $assigned_to = sanitizeInput($_REQUEST['assigned_to']);
            $priority = sanitizeInput($_REQUEST['priority']);
            $eta = sanitizeInput($_REQUEST['eta']);
            $perform_by = sanitizeInput($_REQUEST['perform_by']);
            $pct_completed = sanitizeInput($_REQUEST['pct_completed']) / 100;
            $reply_text = sanitizeInput($_REQUEST['addl_notes']);

            $resolved = ((double)$pct_completed === 1.00) ? 1 : 0;

            if($dbconn->query("UPDATE tasks SET last_updated = UNIX_TIMESTAMP(), priority = '$priority', 
                assigned_to = '$assigned_to', resolved = $resolved, pct_completed = '$pct_completed', eta_hrs = '$eta', perform_by = '$perform_by' WHERE id = '$task_id'")) {
                if(!empty($reply_text)) {
                    $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$reply_text', 'task_reply', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, $task_id)");
                }

                echo displayToast("success", "Updated task successfully.", "Task Updated");
                echo "<script>task_table.ajax.reload(null,false);</script>";
            } else {
                dbLogSQLErr($dbconn);
            }
        }

        break;
    case 'create_operation':
        $task_id = sanitizeInput($_REQUEST['task_id']);
        $assigned_to = sanitizeInput($_REQUEST['assigned_to']);
        $priority = sanitizeInput($_REQUEST['priority']);
        $eta = sanitizeInput($_REQUEST['eta']);
        $perform_by = sanitizeInput($_REQUEST['perform_by']);
        $pct_completed = sanitizeInput($_REQUEST['pct_completed']) / 100;
        $reply_text = sanitizeInput($_REQUEST['addl_notes']);

        $resolved = ((double)$pct_completed === 1.00) ? 1 : 0;

        if($dbconn->query("UPDATE tasks SET last_updated = UNIX_TIMESTAMP(), priority = '$priority', 
                assigned_to = '$assigned_to', resolved = $resolved, pct_completed = '$pct_completed', eta_hrs = '$eta', perform_by = '$perform_by' WHERE id = '$task_id'")) {
            if(!empty($reply_text)) {
                $dbconn->query("INSERT INTO notes (note, note_type, timestamp, user, type_id) VALUES ('$reply_text', 'task_reply', UNIX_TIMESTAMP(), {$_SESSION['userInfo']['id']}, $task_id)");
            }

            $notes_desc = null;

            $task_qry = $dbconn->query("SELECT * FROM tasks WHERE id = '$task_id'");
            $task = $task_qry->fetch_assoc();

            $task_description = nl2br($task['description']);

            $task_initial_comment_time = date(DATE_TIME_ABBRV, $task['created']);

            $desc_nl = str_ireplace("<br />", "\r\n", $task['description']);

            $notes_desc = "<strong>($created_by $task_initial_comment_time):</strong> $task_description";

            $addl_notes_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'task_reply' AND type_id = $task_id");

            if($addl_notes_qry->num_rows > 0) {
                while($addl_notes = $addl_notes_qry->fetch_assoc()) {
                    $comment_time = date(DATE_TIME_ABBRV, $addl_notes['timestamp']);

                    $notes_desc .= "<br /><br /><strong>({$addl_notes['name']} $comment_time):</strong> {$addl_notes['note']}";
                }
            }

            $pfm_by_qry = $dbconn->query("SELECT * FROM user WHERE id = '$perform_by'");
            $pfm_by = $pfm_by_qry->fetch_assoc();

            $op_qry = $dbconn->query("SELECT * FROM operations WHERE responsible_dept = '{$pfm_by['default_queue']}' AND job_title = 'Honey Do'");
            $op_info = $op_qry->fetch_assoc();

            $stmt = $dbconn->prepare("INSERT INTO op_queue (room_id, operation_id, active, completed, rework, notes, created) VALUES (?, ?, FALSE, FALSE, FALSE, ?, UNIX_TIMESTAMP())");
            $stmt->bind_param("iis", $task_id, $op_info['id'], $notes_desc);

            $stmt->execute();
            $stmt->close();

            echo displayToast("success", "Created operation based on task.", "Task Converted to Operation");
            echo "<script>task_table.ajax.reload(null,false);</script>";
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    default:
        displayToast("error", "No action specified.", "No action.");
        die();
}