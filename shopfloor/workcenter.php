<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<!-- Date & Clock -->
<link href="/assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>

<div class="row">
    <div class="col-md-4">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h4>Jobs in Queue</h4>

                    <table class="tablesaw table m-b-0" data-tablesaw-sortable>
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Job ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Part ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Operation</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Assigned To</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // first grab the department from the system  for the user
                        $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");

                        $display_no_jobs = 0;

                        // for each job in the queue
                        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE active = false AND completed = false");

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

                                    // generate the part ID
                                    $part_id = strtoupper($room['so_parent'] . $room['room'] . "-" . $so_result['dealer_code'] . "_" . $room['room_name']);

                                    // generate the operation ID
                                    $op_id = strtoupper($ind_op['op_id'] . $room['room'] . "_" . $so_result['dealer_code'] . '_' . $ind_op['department']);

                                    echo "<tr class='cursor-hand queue-op-start' data-op-id='{$op_queue['id']}' data-op-info='$operation_payload'
                                            data-long-op-id='$op_id' data-long-part-id='$part_id'>";
                                    echo "  <td>$part_id</td>";
                                    echo "  <td>$op_id</td>";
                                    echo "  <td>{$ind_op['job_title']}</td>"; // the operation title itself, easy!
                                    echo "  <td>{$ind_op['responsible_dept']}</td>";
                                    echo "</tr>";

                                    $display_no_jobs -= 1;
                                }
                            }
                        }

                        if($display_no_jobs === 7) {
                            echo "<tr><td colspan='4'>No active jobs</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h4>Active Jobs</h4>

                    <table class="tablesaw table m-b-0" data-tablesaw-sortable>
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Job ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Part ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="3">Started</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Operation</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Assigned To</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
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

                                        // generate the part ID
                                        $part_id = strtoupper($room['so_parent'] . $room['room'] . "-" . $so_result['dealer_code'] . "_" . $room['room_name']);

                                        // generate the operation ID
                                        $op_id = strtoupper($ind_op['op_id'] . $room['room'] . "_" . $so_result['dealer_code'] . '_' . $ind_op['department']);

                                        echo "<tr class='cursor-hand queue-op-start' data-op-id='{$op_queue['id']}' data-op-info='$operation_payload'
                                            data-long-op-id='$op_id' data-long-part-id='$part_id'>";
                                        echo "  <td>$part_id</td>";
                                        echo "  <td>$op_id</td>";
                                        echo "  <td id='{$op_queue['start_time']}'></td>";
                                        echo "  <td>{$ind_op['job_title']}</td>"; // the operation title itself, easy!
                                        echo "  <td>{$ind_op['responsible_dept']}</td>";
                                        echo "</tr>";

                                        echo "<script>$('#{$op_queue['start_time']}').text(moment({$op_queue['start_time']} * 1000).fromNow())</script>";

                                        $display_no_jobs -= 1;
                                    }
                                }
                            } else {
                                echo "<tr><td colspan='4'>No active jobs</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h4>Recently Completed Jobs</h4>

                    <table class="tablesaw table m-b-0" data-tablesaw-sortable>
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Job ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Part ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="3">Completed</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Operation</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Assigned To</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // first grab the department from the system  for the user
                        $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}' LIMIT 0,50");

                        $display_no_jobs = 0;

                        // for each job in the queue
                        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE completed = TRUE");

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

                                    // generate the part ID
                                    $part_id = strtoupper($room['so_parent'] . $room['room'] . "-" . $so_result['dealer_code'] . "_" . $room['room_name']);

                                    // generate the operation ID
                                    $op_id = strtoupper($ind_op['op_id'] . $room['room'] . "_" . $so_result['dealer_code'] . '_' . $ind_op['department']);

                                    echo "<tr class='cursor-hand queue-op-start' data-op-id='{$op_queue['id']}' data-op-info='$operation_payload'
                                            data-long-op-id='$op_id' data-long-part-id='$part_id'>";
                                    echo "  <td>$part_id</td>";
                                    echo "  <td>$op_id</td>";
                                    echo "  <td id='{$op_queue['end_time']}'></td>";
                                    echo "  <td>{$ind_op['job_title']}</td>"; // the operation title itself, easy!
                                    echo "  <td>{$ind_op['responsible_dept']}</td>";
                                    echo "</tr>";

                                    echo "<script>$('#{$op_queue['end_time']}').text(moment({$op_queue['end_time']} * 1000).fromNow())</script>";
                                }
                            }
                        } else {
                            echo "<tr><td colspan='4'>No recently completed jobs</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- modal -->
    <div id="viewJobDetails" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewJobDetailsLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="modalJobDetailsTitle">Job <span id="modalJobDetailsInnerTitle"></span> information</h4>
                </div>
                <div class="modal-body">
                    <p>
                        <span id="start_job_originally_started">Originally Started: ?</span>
                    </p>

                    <div class="row">
                        <div class="col-md-8">
                            <table>
                                <tr class="form-group">
                                    <td><label for="qty_requested">Quantity Requested</label></td>
                                    <td><input type="text" class="form-control" id="qty_requested" name="qty_requested" placeholder="Qty"></td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="qty_completed">Quantity Completed</label></td>
                                    <td><input type="text" class="form-control" id="qty_completed" name="qty_completed" placeholder="Qty"></td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="operation">Operation</label></td>
                                    <td>
                                        <select id="operation" name="operation" class="form-control">
                                            <?php
                                                $qry = $dbconn->query("SELECT * FROM operations");

                                                if($qry->num_rows > 0) {
                                                    $tmpResult = $qry->fetch_assoc();
                                                    $optgroup = $tmpResult['department'];

                                                    echo "<optgroup label='$optgroup'>";

                                                    $qry = $dbconn->query("SELECT * FROM operations");

                                                    while($result = $qry->fetch_assoc()) {
                                                        if($result['department'] !== $optgroup) {
                                                            $optgroup = $result['department'];
                                                            echo "</optgroup>";
                                                            echo "<optgroup label='$optgroup'>";
                                                        }

                                                        echo "<option value='{$result['id']}'>{$result['op_id']} - {$result['job_title']}</option>";
                                                    }

                                                    echo "</optgroup>";
                                                }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="part_id">Part ID</label></td>
                                    <td><input type="text" class="form-control" id="part_id" name="part_id" placeholder="Part ID"></td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="status">Status</label></td>
                                    <td>
                                        <select id="status" name="status" class="form-control">
                                            <option value="New">New</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Rework">Rework</option>
                                            <option value="Partially Completed">Partially Completed</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="department">Department</label></td>
                                    <td>
                                        <select name="department" id="department" class="form-control">
                                        <?php
                                            $qry = $dbconn->query("SELECT DISTINCT department FROM operations");

                                            if($qry->num_rows > 0) {
                                                while($result = $qry->fetch_assoc()) {
                                                    echo "<option value='{$result['department']}'>{$result['department']}</option>";
                                                }
                                            }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="assigned_to">Assigned To</label></td>
                                    <td>
                                        <select name="assigned_to" id="assigned_to" class="form-control">
                                        <?php
                                            $qry = $dbconn->query("SELECT name FROM user");

                                            if($qry->num_rows > 0) {
                                                while($result = $qry->fetch_assoc()) {
                                                    echo "<option value='{$result['name']}'>{$result['name']}</option>";
                                                }
                                            }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="job_started">Started At</label></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="job_started" name="job_started" placeholder="Date Started">
                                            <span class="input-group-addon bg-custom b-0">
                                                <i class="icon-calender"></i>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="job_completed">Completed At</label></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="job_completed" name="job_completed" placeholder="Date Completed">
                                            <span class="input-group-addon bg-custom b-0">
                                                <i class="icon-calender"></i>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="form-group">
                                    <td><label for="assigned_bracket">Assigned Bracket</label></td>
                                    <td>
                                        <select name="assigned_bracket" id="assigned_bracket" class="form-control">
                                        <?php
                                            $qry = $dbconn->query("SELECT * FROM brackets");

                                            if($qry->num_rows > 0) {
                                                while($result = $qry->fetch_assoc()) {
                                                    echo "<option value='{$result['id']}'>{$result['bracket_name']}</option>";
                                                }
                                            }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <span id="view_job_notes">Notes: ???</span><br />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="update_job" data-startid="?">Update Job</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
</div>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Date & Clock picker -->
<script src="/assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>


<!-- Loading page content -->
<script src="/ondemand/js/page_content_functions.js"></script>

<script>
    $("#job_started").datetimepicker({
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    });

    $("#job_completed").datetimepicker({
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    });
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>