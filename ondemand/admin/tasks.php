<?php
require_once ("../../includes/header_start.php");

//outputPHPErrs();

$action = sanitizeInput($_REQUEST['action']);

switch($action) {
    case 'submit_feedback':
        $task_desc = sanitizeInput($_REQUEST['description']);

        if($_SESSION['userInfo']['id'] === '16') {
            $submitted_by = $_SESSION['shop_user']['id'];
            $submitted_name = $_SESSION['shop_user']['name'];
        } else {
            $submitted_by = $_SESSION['userInfo']['id'];
            $submitted_name = $_SESSION['userInfo']['name'];
        }

        if($dbconn->query("INSERT INTO tasks (name, description, created, last_updated, priority, assigned_to, due_date, submitted_by, resolved) 
         VALUES ('', '$task_desc', UNIX_TIMESTAMP(), null, 'Low', 1, null, $submitted_by, FALSE);")) {
            $mail_to = "ben@smcm.us";
            $mail_subject = "New Feedback Submitted";
            $mail_message = <<<HEREDOC
<p>A new task has been created in EagleEye by . Here is the contents of the feedback:</p>

<p>$task_desc</p>

<p>Thanks,<br/>
<br/>
Your Automated Task List</p>
HEREDOC;

            // To send HTML mail, the Content-type header must be set
            $mail_headers[] = 'MIME-Version: 1.0';
            $mail_headers[] = 'Content-type: text/html; charset=iso-8859-1';

            // Additional headers
            $mail_headers[] = 'To: Ben <ben@smcm.us>';
            $mail_headers[] = 'Reply-To: Ben <ben@smcm.us>';
            $mail_headers[] = 'X-Mailer: PHP/' . phpversion();;

            $result = mail($mail_to, $mail_subject, $mail_message, implode("\r\n", $mail_headers));

            echo displayToast("success", "Successfully logged feedback.", "Feedback Logged");
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'get_task_list':
        $i = 0;

        $output = array();

        $tasks_qry = $dbconn->query("SELECT tasks.id AS taskID, tasks.name AS taskName, user.name AS userName, tasks.*, user.* FROM tasks LEFT JOIN user ON tasks.assigned_to = user.id WHERE resolved = FALSE ORDER BY created DESC;");

        if($tasks_qry->num_rows > 0) {
            while($task = $tasks_qry->fetch_assoc()) {
                $short_desc = strip_tags(substr($task['description'], 0, 40) . "...");

                if(empty($task['last_update'])) {
                    $last_updated = "New";
                } else {
                    $last_updated = date(DATE_TIME_ABBRV, $task['last_update']);
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
                $output['data'][$i][] = $name;
                $output['data'][$i][] = $short_desc;
                $output['data'][$i][] = $task['userName'];
                $output['data'][$i][] = $created;
                $output['data'][$i][] = $task['priority'];
                $output['data'][$i][] = $task['eta_hrs'] . " $humanized_eta";
                $output['data'][$i][] = $last_updated;
                $output['data'][$i][] = $task['pct_completed'] * 100 . "%";
                $output['data'][$i]['DT_RowId'] = $task['taskID'];

                $i += 1;
            }
        } else {
            $output['data'][0][] = "&nbsp;";
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

            $user_qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE");

            while($user = $user_qry->fetch_assoc()) {
                $selected = ($user['id'] === $task['assigned_to']) ? "selected" : null;

                $user_opts .= "<option value='{$user['id']}' $selected>{$user['name']}</option>";

                if($user['id'] === $task['submitted_by']) $created_by = $user['name'];
            }

            $pct_complete = $task['pct_completed'] * 100;

            switch($task['priority']) {
                case 'Low':
                    $sel_low = "selected";
                    break;

                case 'Moderate':
                    $sel_mod = "selected";
                    break;

                case 'High':
                    $sel_high = "selected";
                    break;

                case 'Immediate':
                    $sel_imm = "selected";
                    break;

                default:
                    $sel_low = "selected";
                    break;
            }

            echo <<<HEREDOC
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form name="task_details" id="task_details">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4 class="modal-title" id="modalTaskTitle"><input type="text" value="{$task['name']}" placeholder="Task Title" maxlength="200" name="task_title" id="task_title" class="form-control" style="width:97%;" /></h4>
                            </div>
                            <div class="modal-body">
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
                                        <td>Priority:</td>
                                        <td>
                                            <select name="priority" id="priority" class="form-control">
                                                <option value="Low" $sel_low>Low</option>
                                                <option value="Moderate" $sel_mod>Moderate</option>
                                                <option value="High" $sel_high>High</option>
                                                <option value="Immediate" $sel_imm>Immediate</option>
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
    
                                <div id="task_description">{$task['description']}</div>
                                
                                <div id="task_created_by">-- $created_by</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light" id="update_task_btn" data-taskid="{$task['id']}">Update Task</button>
                            </div>
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
        $task_title = sanitizeInput($_REQUEST['task_title']);
        $assigned_to = sanitizeInput($_REQUEST['assigned_to']);
        $priority = sanitizeInput($_REQUEST['priority']);
        $eta = sanitizeInput($_REQUEST['eta']);
        $pct_completed = sanitizeInput($_REQUEST['pct_completed']) / 100;

        $resolved = ((double)$pct_completed === 1.00) ? 1 : 0;

        if($dbconn->query("UPDATE tasks SET name = '$task_title', last_updated = NOW(), priority = '$priority', 
          assigned_to = '$assigned_to', resolved = $resolved, pct_completed = '$pct_completed', eta_hrs = '$eta' WHERE id = '$task_id'")) {
            echo displayToast("success", "Updated task successfully.", "Task Updated");
            echo "<script>task_table.ajax.reload(null,false);</script>";
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    default:
        displayToast("error", "No action specified.", "No action.");
        die();
}