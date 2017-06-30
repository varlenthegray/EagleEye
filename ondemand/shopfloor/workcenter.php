<?php
require ("../../includes/header_start.php");
require("../../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/

use Carbon\Carbon; // prep carbon

$action = $_REQUEST['action'];

switch($action) {
    case 'view_job_in_queue':
        $id = sanitizeInput($_POST['id']);

        $op_qry = $dbconn->query("SELECT op_queue.id AS queueID, op_queue.*, operations.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE op_queue.id = '$id'");

        if($op_qry->num_rows > 0) {
            $op = $op_qry->fetch_assoc();

            $header = "Job Management: ". $op['so_parent'] . "-" . $op['room'] . " (" . $op['op_id'] . ": " . $op['job_title'] . ")";

            echo <<<HEREDOC
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">$header</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-9">
                            <fieldset class="form-group">
                                <input type="checkbox" name="published" id="published" checked />
                                <label for="published">Published</label>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="wc-jiq-update" data-id="$id">Update</button>
                </div>
            </div>
        </div>
HEREDOC;
        } else {
            http_response_code(400); // send bad request response
            dbLogSQLErr($dbconn);
        }

        break;
    case 'update_queued_job':
        $id = sanitizeInput($_POST['id']);

        $op_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");

        if($op_qry->num_rows > 0) {
            $op = $op_qry->fetch_assoc();

            if((bool)$op['published']) {
                $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '$id'");

                $changed = json_encode(["Published"=>"False"]);

                $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('{$op['id']}', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())");

                echo displayToast("success", "This operation has been unpublished.", "Unpublished Operation");
            } else {
                echo displayToast("warning", "This operation has already been marked as not published.", "Not Published Presently");
            }
        } else {
            http_response_code(400); // send bad request response
            dbLogSQLErr($dbconn);
        }

        break;
    case 'display_jiq':
        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS op_queueID, op_queue.*, operations.*, rooms.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id JOIN rooms ON op_queue.room_id = rooms.id JOIN customer ON op_queue.so_parent = customer.sales_order_num WHERE active = FALSE AND completed = FALSE AND published = TRUE;");

        $output = array();
        $i = 0;

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $output['data'][$i][] = $op_queue['so_parent'] . "-" . $op_queue['room'];
                $output['data'][$i][] = $op_queue['bracket'];
                $output['data'][$i][] = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                $output['data'][$i][] = date(DATE_DEFAULT, $op_queue['created']);
                $output['data'][$i]['DT_RowId'] = $op_queue['op_queueID'];

                $i += 1;
            }
        } else {
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '--- None Queued ---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
        }

        echo json_encode($output);

        break;
    case 'display_recently_completed':
        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS op_queueID, op_queue.*, operations.*, rooms.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id JOIN rooms ON op_queue.room_id = rooms.id JOIN customer ON op_queue.so_parent = customer.sales_order_num WHERE active = FALSE AND completed = TRUE AND published = TRUE ORDER BY end_time DESC;");

        $output = array();
        $i = 0;

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $output['data'][$i][] = $op_queue['so_parent'] . "-" . $op_queue['room'];
                $output['data'][$i][] = $op_queue['bracket'];
                $output['data'][$i][] = $op_queue['op_id'] . ": " . $op_queue['job_title'];

                $time = Carbon::createFromTimestamp($op_queue['end_time']); // grab the carbon timestamp

                $output['data'][$i][] = $time->diffForHumans(); // obtain the difference in readable format for humans!

                $i += 1;
            }
        } else {
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '--- Nothing to Show! ---';
            $output['data'][$i][] = '---';
        }

        echo json_encode($output);

        break;
    case 'display_active_jobs':
        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS op_queueID, op_queue.*, operations.*, rooms.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id LEFT JOIN rooms ON op_queue.room_id = rooms.id LEFT JOIN customer ON op_queue.so_parent = customer.sales_order_num WHERE active = TRUE AND published = TRUE AND (completed = FALSE OR completed IS NULL);");

        $output = array();
        $i = 0;

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $output['data'][$i][] = $op_queue['so_parent'] . "-" . $op_queue['room'];
                $output['data'][$i][] = $op_queue['bracket'];

                if(!empty($op_queue['subtask'])) {
                    $subtask = " ({$op_queue['subtask']})";
                } else {
                    $subtask = NULL;
                }

                $output['data'][$i][] = $op_queue['op_id'] . ": " . $op_queue['job_title'] . $subtask;

                $employees = json_decode($op_queue['active_employees']);
                $active_emp = null;

                foreach($employees as $employee) {
                    $emp_qry = $dbconn->query("SELECT * FROM user WHERE id = $employee");
                    $emp = $emp_qry->fetch_assoc();

                    $active_emp .= $emp['name'] . ", ";
                }

                $active_emp = rtrim($active_emp, ", ");

                $output['data'][$i][] = $active_emp;

                if($op_queue['resumed_time'] !== null) {
                    $start_resume_time = date(TIME_ONLY, $op_queue['resumed_time']);
                } else {
                    $start_resume_time = date(TIME_ONLY, $op_queue['start_time']);
                }

                $output['data'][$i][] = $start_resume_time;

                $i += 1;
            }
        } else {
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '--- None Active ---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
        }

        echo json_encode($output);

        break;
    default:
        die();
        break;
}