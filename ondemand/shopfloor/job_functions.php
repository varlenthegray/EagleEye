<?php
include_once("../../includes/header_start.php");

function activeJobGeneration() {
    global $dbconn;

    $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");
    $result = $qry->fetch_assoc();

    // gather the departments up in an array
    $department = json_decode($result['department']);

    // for each job in the queue, lets find out if we work with it on this employee
    $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE active = TRUE");

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
                    $part_id = strtoupper($room['so_parent'] . "-" .  $room['room']);

                    // generate the operation ID
                    $op_id = strtoupper($ind_op['op_id'] . $room['room'] . "_" . $so_result['dealer_code'] . '_' . $ind_op['department']);

                    $release_date = date('n/j/y', $op_queue['created']);

                    echo "<tr class='cursor-hand update-active-job' data-op-id='{$op_queue['id']}' data-op-info='$operation_payload'
                                                    data-long-op-id='$op_id' data-long-part-id='$part_id'>";
                    echo "  <td>$part_id</td>";
                    echo "  <td>{$ind_op['responsible_dept']}</td>";
                    echo "  <td>{$ind_op['op_id']}: {$ind_op['job_title']}</td>"; // the operation title itself, easy!
                    echo "  <td>$release_date</td>";
                    echo "  <td></td>";
                    echo "</tr>";

                    echo "<script>$('#{$op_queue['start_time']}').html(moment({$op_queue['start_time']} * 1000).fromNow());</script>";
                }
            }
        }
    } else {
        echo "<tr>";
        echo "  <td colspan='4'>No active jobs in {$result['responsible_dept']}</td>";
        echo "</tr>";
    }
}

function generateOpQueue($room_id, $published_table, $op_bracket, $bracket_name) {
    global $dbconn;

    $qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'"); // select the room associated with the operations

    if($qry->num_rows === 1) { // if we have a room
        $result = $qry->fetch_assoc(); // log the room information in to result

        if ((bool)$result[$published_table] === true) { // if the bracket is published for that room
            $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$result[$op_bracket]}'"); // select the operation associated with that bracket

            if($op_qry->num_rows === 1) { // if the operation is successful
                $op_result = $op_qry->fetch_assoc(); // the operation is logged

                if($op_result['department'] !== 'Admin') {
                    // find out if there is already an operation in the queue for the room and the operation id matches it
                    $existing_q_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$room_id' AND operation_id = '{$op_result['id']}'");

                    // if there is not a match in the system
                    if ($existing_q_qry->num_rows === 0) {
                        // create a new operation in the system
                        if (!$dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, start_time, end_priority, end_time,
                                active, completed, rework, notes, qty_requested, qty_completed, qty_rework, created, published) VALUES ('{$result['id']}', '{$result['so_parent']}', '{$result['room']}', 
                                '{$op_result['id']}', '4', null, null, null, false, false, false, null, 1, null, null, UNIX_TIMESTAMP(), 1)")) { // end IF statement
                            dbLogSQLErr($dbconn);
                        }
                    } else { // otherwise there is a match
                        $op_queue = $existing_q_qry->fetch_assoc(); // log the operation queue specifically
                        $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'"); // find the operation directly related to that queue
                        $op = $op_qry->fetch_assoc(); // log that information

                        if($bracket_name === $op['department']) { // if the bracket names match
                            if((bool)$result[$published_table] === FALSE) { // if the bracket is NOT published, set it to not published
                                $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                            } else { // if the bracket IS published, set it to published
                                $dbconn->query("UPDATE op_queue SET published = TRUE WHERE id = '{$op_queue['id']}'");
                            }
                        }

                    }
                }
            }
        } else { // if the bracket is not published
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