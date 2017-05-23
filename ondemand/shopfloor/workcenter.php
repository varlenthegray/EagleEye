<?php
require ("../../includes/header_start.php");

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
        // first grab the department from the system  for the user
        $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");

        $display_no_jobs = 0;

        // for each job in the queue
        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE active = false AND completed = false AND published = TRUE");

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $ind_op_qry = $dbconn->query("SELECT id, op_id, department, job_title, responsible_dept FROM operations WHERE id = '{$op_queue['operation_id']}'");

                if($ind_op_qry->num_rows > 0) {
                    $ind_op = $ind_op_qry->fetch_assoc();

                    // we do this job and should display it
                    // gather room information
                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$op_queue['room_id']}'");
                    $room = $room_qry->fetch_assoc();

                    $operation_payload = json_encode($ind_op); // encode the operation so that we can send it to the desired function

                    // we need the customer query for the dealer code
                    $so_qry = $dbconn->query("SELECT * FROM customer WHERE sales_order_num = '{$room['so_parent']}'");
                    $so_result = $so_qry->fetch_assoc();

                    // generate the SO ID
                    $so_id = strtoupper($room['so_parent'] . "-" . $room['room']);

                    $released = date(DATE_DEFAULT, $op_queue['created']);

                    echo "<tr class='cursor-hand wc-edit-queue' data-op-id='{$op_queue['id']}' data-op-info='$operation_payload'
                                            data-long-op-id='{$ind_op['responsible_dept']}' data-long-part-id='$so_id'>";
                    echo "  <td>$so_id</td>";
                    echo "  <td>{$ind_op['responsible_dept']}</td>";
                    echo "  <td>{$ind_op['op_id']}: {$ind_op['job_title']}</td>"; // the operation title itself, easy!
                    echo "  <td>$released</td>";
                    echo "</tr>";

                    $display_no_jobs -= 1;
                }
            }
        }

        if($display_no_jobs === 7) {
            echo "<tr><td colspan='4'>No active jobs</td></tr>";
        }

        break;
    case 'display_recently_completed':
        // first grab the department from the system  for the user
        $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");

        $display_no_jobs = 0;

        // for each job in the queue
        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE completed = TRUE ORDER BY end_time DESC LIMIT 0,30");

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $ind_op_qry = $dbconn->query("SELECT id, op_id, department, job_title, responsible_dept FROM operations WHERE id = '{$op_queue['operation_id']}'");

                if($ind_op_qry->num_rows > 0) {
                    $ind_op = $ind_op_qry->fetch_assoc();

                    // we do this job and should display it
                    // gather room information
                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$op_queue['room_id']}'");
                    $room = $room_qry->fetch_assoc();

                    $operation_payload = json_encode($ind_op); // encode the operation so that we can send it to the desired function

                    // we need the customer query for the dealer code
                    $so_qry = $dbconn->query("SELECT * FROM customer WHERE sales_order_num = '{$room['so_parent']}'");
                    $so_result = $so_qry->fetch_assoc();

                    // generate the SO ID
                    $so_id = strtoupper($room['so_parent'] . "-" . $room['room']);

                    echo "<tr class='cursor-hand queue-op-start' data-op-id='{$op_queue['id']}' data-op-info='$operation_payload'
                                            data-long-op-id='{$ind_op['responsible_dept']}' data-long-part-id='$so_id'>";
                    echo "  <td>$so_id</td>";
                    echo "  <td>{$ind_op['responsible_dept']}</td>";
                    echo "  <td>{$ind_op['op_id']}: {$ind_op['job_title']}</td>"; // the operation title itself, easy!
                    echo "  <td id='{$op_queue['start_time']}'></td>";
                    echo "</tr>";

                    echo "<script>$('#{$op_queue['start_time']}').text(moment({$op_queue['start_time']} * 1000).fromNow())</script>";
                }
            }
        } else {
            echo "<tr><td colspan='4'>No recently completed jobs</td></tr>";
        }

        break;
    case 'display_active_jobs':
        // first grab the department from the system  for the user
        $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");

        $display_no_jobs = 0;

        // for each job in the queue
        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE active = TRUE AND completed = false");

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $ind_op_qry = $dbconn->query("SELECT id, op_id, department, job_title, responsible_dept FROM operations WHERE id = '{$op_queue['operation_id']}'");

                if($ind_op_qry->num_rows > 0) {
                    $ind_op = $ind_op_qry->fetch_assoc();

                    // we do this job and should display it
                    // gather room information
                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$op_queue['room_id']}'");
                    $room = $room_qry->fetch_assoc();

                    $operation_payload = json_encode($ind_op); // encode the operation so that we can send it to the desired function

                    // we need the customer query for the dealer code
                    $so_qry = $dbconn->query("SELECT * FROM customer WHERE sales_order_num = '{$room['so_parent']}'");
                    $so_result = $so_qry->fetch_assoc();

                    // generate the SO ID
                    $so_id = strtoupper($room['so_parent'] . "-" . $room['room']);

                    // obtain the list of individuals working
                    $active_emp = json_decode($op_queue['active_employees']);
                    $emp_list = '';

                    foreach($active_emp as $emp) {
                        $name_qry = $dbconn->query("SELECT name FROM user WHERE id = '$emp'");
                        $name = $name_qry->fetch_assoc();

                        $emp_list .= "{$name['name']}, ";
                    }

                    $emp_list = rtrim($emp_list, ", ");

                    echo "<tr class='cursor-hand queue-op-start' data-op-id='{$op_queue['id']}' data-op-info='$operation_payload'
                                            data-long-op-id='{$ind_op['responsible_dept']}' data-long-part-id='$so_id'>";
                    echo "  <td>$so_id</td>";
                    echo "  <td>{$ind_op['responsible_dept']}</td>";
                    echo "  <td>{$ind_op['op_id']}: {$ind_op['job_title']}</td>"; // the operation title itself, easy!
                    echo "  <td>$emp_list</td>";
                    echo "  <td id='{$op_queue['start_time']}'></td>";
                    echo "</tr>";

                    echo "<script>$('#{$op_queue['start_time']}').text(moment({$op_queue['start_time']} * 1000).fromNow())</script>";

                    $display_no_jobs -= 1;
                }
            }
        } else {
            echo "<tr><td colspan='4'>No active jobs</td></tr>";
        }

        break;
    default:
        die();
        break;
}