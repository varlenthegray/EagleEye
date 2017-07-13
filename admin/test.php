<?php
require ("../includes/header_start.php");

function createOpQueue($bracket_pub, $bracket, $operation, $roomid) {
    global $dbconn;

    // now we need to create the ops and/or activate the appropriate ops based on what's selected (and deactivate any old ones) if bracket is published
    if((bool)$bracket_pub) {
        $ops = array();

        // bracket is published, time to build the bracket operations
        $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

        // create an array of all the ops
        while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
            // if the operation is not an x98 operation then add it to the array, otherwise exclude it
            if((int)substr($all_bracket_ops['op_id'], -2) !== 98) {
                $ops[] = $all_bracket_ops['id'];
            }
        }

        // grab all operations in the queue for this room that are not OTF
        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$roomid' AND otf_created = FALSE");

        // if we were able to find any operations in the queue
        if($op_queue_qry->num_rows > 0) {
            // for every operation
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                // lets find out if this operation is part of the bracket
                if(in_array($op_queue['operation_id'], $ops)) {
                    // it's part of the bracket, is it this operation?
                    if($op_queue['operation_id'] === $operation) {
                        // it is this operation, is it unpublished?
                        if(!(bool)$op_queue['published']) {
                            // publish it, foo!
                            $dbconn->query("UPDATE op_queue SET published = TRUE where id = '{$op_queue['id']}'");
                        }
                    } else {
                        // nope, it's part of the queue but it's not this operation, lets unpublish it!
                        $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                    }
                }
            }
        } else {
            // no operations exist in the queue for this room that are NOT OTF! BLANK SLATE BABY!

            // grab the room information for creation of the queued operation
            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$roomid'");
            $room = $room_qry->fetch_assoc();

            // now, create the operation that SHOULD be active
            $dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, active, completed, rework, qty_requested, 
             partially_completed, created, iteration) VALUES ('$roomid', '{$room['so_parent']}', '{$room['room']}', '$operation', 4, FALSE, FALSE, FALSE, 1, FALSE, 
              UNIX_TIMESTAMP(), '{$room['iteration']}')");
        }

        // we've deactivated and/or published all operations related to the queue, now lets create the operation in the queue if it's not there
        $ind_op_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$operation' AND room_id = '$roomid'");

        if($ind_op_qry->num_rows === 0) {
            // grab the room information for creation of the queued operation
            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$roomid'");
            $room = $room_qry->fetch_assoc();

            // now, create the operation that SHOULD be active
            $dbconn->query("INSERT INTO op_queue (room_id, so_parent, room, operation_id, start_priority, active, completed, rework, qty_requested, 
             partially_completed, created, iteration) VALUES ('$roomid', '{$room['so_parent']}', '{$room['room']}', '$operation', 4, FALSE, FALSE, FALSE, 1, FALSE, 
              UNIX_TIMESTAMP(), '{$room['iteration']}')");
        }
    } else {
        // bracket is NOT published, tis time to deactivate ALL operations
        $ops = array();

        // bracket is NOT published, time to build the bracket
        $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

        // create an array of all the ops
        while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
            $ops[] = $all_bracket_ops['id'];
        }

        // time to find all ops in the query that contain this room
        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$roomid' AND completed = FALSE AND published = TRUE AND otf_created = FALSE");

        if($op_queue_qry->num_rows > 0) {
            // if there are operations that are not completed, are published and are not OTF
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                // for all operations in the queue
                if(in_array($op_queue['operation_id'], $ops)) {
                    // if it's a bracket operation deactivate it
                    $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                }
            }
        }
    }
}

function checkPub($bracket, $published) {
    global $dbconn;

    $room_qry = $dbconn->query("SELECT * FROM rooms");

    while($room = $room_qry->fetch_assoc()) {
        if((bool)$room[$published]) {
            $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room[$bracket]}'");
            $op = $op_qry->fetch_assoc();

            if(substr($op['op_id'], -2) !== '98') {
                // is it in the operation queue?
                $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '{$room['id']}' AND operation_id = '{$op['op_id']}' AND active = TRUE AND published = TRUE");

                if($op_queue_qry->num_rows === 0) {
                    // create it
                    createOpQueue($published, $bracket, $op['id'], $room['id']);

                    echo "Create operation {$op['op_id']} on {$room['so_parent']}.<br/>";
                }
            }
        }
    }
}


checkPub('sales_bracket', 'sales_published');
checkPub('preproduction_bracket', 'preproduction_published');
checkPub('sample_bracket', 'sample_published');
checkPub('doordrawer_bracket', 'doordrawer_published');
checkPub('custom_bracket', 'custom_published');
checkPub('main_bracket', 'main_published');
checkPub('shipping_bracket', 'shipping_published');
checkPub('install_bracket', 'install_bracket_published');