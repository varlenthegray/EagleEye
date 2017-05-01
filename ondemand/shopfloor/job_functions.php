<?php
include_once("../../includes/header_start.php");

function activeJobGeneration() {
    global $dbconn;

    $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");
    $result = $qry->fetch_assoc();

    // gather the departments up in an array
    $department = json_decode($result['department']);

    // for each job in the queue, lets find out if we work with it on this employee
    $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE active");

    if($op_queue_qry->num_rows > 0) {
        while($op_queue = $op_queue_qry->fetch_assoc()) {
            $ind_op_qry = $dbconn->query("SELECT id, op_id, department, job_title, responsible_dept FROM operations WHERE id = '{$op_queue['operation_id']}'");

            if($ind_op_qry->num_rows > 0) {
                $ind_op = $ind_op_qry->fetch_assoc();

                if(in_array($ind_op['responsible_dept'], $department)) {
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

                    echo "<tr class='cursor-hand update-active-job' data-op-id='{$op_queue['id']}' data-op-info='$operation_payload'
                                                    data-long-op-id='$op_id' data-long-part-id='$part_id'>";
                    echo "  <td>$op_id</td>";
                    echo "  <td>{$ind_op['job_title']}</td>";
                    echo "  <td id='{$op_queue['start_time']}'></td>";
                    echo "</tr>";

                    echo "<script>$('#{$op_queue['start_time']}').html(moment({$op_queue['start_time']} * 1000).fromNow());</script>";
                }
            }
        }
    } else {
        echo "<tr>";
        echo "  <td colspan='4'>No active jobs</td>";
        echo "</tr>";
    }
}

function queuedJobGeneration() {
    global $dbconn;

    // first grab the department from the system  for the user
    $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");
    $result = $qry->fetch_assoc();

    // gather the departments up in an array
    $department = json_decode($result['department']);

    $display_no_jobs = 0;

    $admin_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Admin'");

    if($admin_qry->num_rows > 0) {
        while($admin_task = $admin_qry->fetch_assoc()) {
            $operation_payload = json_encode($admin_task);

            echo "<tr class='cursor-hand queue-op-start' data-op-id='{$admin_task['id']}' data-op-info='$operation_payload'
                                                data-long-op-id='' data-long-part-id=''>";

            $part_id = strtoupper($admin_task['op_id'] . "-" . $admin_task['job_title']);

            echo "  <td>$part_id</td>";
            echo "  <td>$part_id</td>";
            echo "  <td>{$admin_task['job_title']}</td>"; // the operation title itself, easy!
            echo "</tr>";
        }
    }

    // for each job in the queue, lets find out if we work with it on this employee
    $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE active = false AND completed = false AND published = TRUE");

    if($op_queue_qry->num_rows > 0) {
        while($op_queue = $op_queue_qry->fetch_assoc()) {
            $ind_op_qry = $dbconn->query("SELECT id, op_id, department, job_title, responsible_dept FROM operations WHERE id = '{$op_queue['operation_id']}'");

            if($ind_op_qry->num_rows > 0) {
                $ind_op = $ind_op_qry->fetch_assoc();

                if(in_array($ind_op['responsible_dept'], $department)) {
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
                    echo "</tr>";

                    $display_no_jobs -= 1;
                } else {
                    $display_no_jobs += 1;
                }
            }
        }
    }

    if($display_no_jobs === 7) {
        echo "<tr><td colspan='4'>No active jobs</td></tr>";
    }
}

function generateOpQueue($room_id, $published_table, $op_bracket, $bracket_name) {
    global $dbconn;

    $qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");

    if($qry->num_rows === 1) {
        $result = $qry->fetch_assoc();

        if ((bool)$result[$published_table] === true) {
            $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$result[$op_bracket]}'");

            if($op_qry->num_rows === 1) {
                $op_result = $op_qry->fetch_assoc();

                $existing_q_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '{$result['id']}' AND room = '{$result['room']}' AND operation_id = '{$op_result['id']}'");

                if ($existing_q_qry->num_rows === 0) {
                    if (!$dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, start_time, end_priority, end_time,
                                active, completed, rework, notes, qty_requested, qty_completed, qty_rework, created, published) VALUES ('{$result['id']}', '{$result['so_parent']}', '{$result['room']}', 
                                '{$op_result['id']}', '4', null, null, null, false, false, false, null, 1, null, null, UNIX_TIMESTAMP(), 1)")
                    ){ // end IF statement
                        dbLogSQLErr($dbconn);
                    }
                } else {
                    $op_queue = $existing_q_qry->fetch_assoc();
                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'");
                    $op = $op_qry->fetch_assoc();

                    if($bracket_name === $op['department']) {
                        if((bool)$result[$published_table] === FALSE) {
                            dbLogDebug("Setting published to FALSE for op_queue ID " . $op_queue['id']);
                            $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                        } else {
                            $dbconn->query("UPDATE op_queue SET published = TRUE WHERE id = '{$op_queue['id']}'");
                        }
                    }

                }
            }
        } else {
            $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$result[$op_bracket]}'");

            if($op_qry->num_rows === 1) {
                $op_result = $op_qry->fetch_assoc();

                $existing_q_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '{$result['id']}' AND room = '{$result['room']}' AND operation_id = '{$op_result['id']}'");

                if ($existing_q_qry->num_rows === 1) {
                    $op_queue = $existing_q_qry->fetch_assoc();
                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'");
                    $op = $op_qry->fetch_assoc();

                    if($bracket_name === $op['department']) {
                        if((bool)$result[$published_table] === FALSE) {
                            dbLogDebug("Setting published to FALSE for op_queue ID " . $op_queue['id']);
                            $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                        }
                    } else {
                    }
                } else {
                }
            }
        }
    }
}