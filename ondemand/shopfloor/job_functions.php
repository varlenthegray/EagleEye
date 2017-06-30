<?php
include_once("../../includes/header_start.php");

function generateOpQueue($room_id, $published_table, $op_bracket, $bracket_name) {
    global $dbconn;

    $qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'"); // select the room associated with the operations

    if($qry->num_rows === 1) { // if we have a room
        $result = $qry->fetch_assoc(); // log the room information in to result

        if ((bool)$result[$published_table] === true) { // if the bracket is published for that room
            $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$result[$op_bracket]}'"); // select the operation associated with that bracket

            if($op_qry->num_rows === 1) { // if the operation is successful
                $op_result = $op_qry->fetch_assoc(); // the operation is logged

                if($op_result['job_title'] !== 'Non-Billable') {
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

                        if($op_qry->num_rows > 0) {
                            $op = $op_qry->fetch_assoc(); // log that information

                            if($bracket_name === $op['department']) { // if the bracket names match
                                if((bool)$result[$published_table] === FALSE) { // if the bracket is NOT published, set it to not published
                                    $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                                } else { // if the bracket IS published, set it to published
                                    $dbconn->query("UPDATE op_queue SET published = TRUE WHERE id = '{$op_queue['id']}'");
                                }
                            } else {
                                dbLogDebug("I give up, the brackets don't match.");
                            }
                        } else {
                            dbLogDebug("Unable to find operation {$op_queue['operation_id']}");
                        }

                        dbLogDebug("Made it through the code without an issue!");
                    }
                }
            }
        } else { // if the bracket is not published
            dbLogDebug("Looks like it's the end of the road buddy, the bracket isn't published!");
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