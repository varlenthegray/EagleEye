<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 10/23/2017
 * Time: 10:00 AM
 */

namespace Queue;

class queue {
    function createOpQueue($bracket_pub, $bracket, $operation, $roomid) {
        global $dbconn;

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS QID, op_queue.*, operations.* FROM op_queue LEFT JOIN operations ON op_queue.operation_id = operations.id WHERE room_id = '$roomid' AND published = TRUE AND bracket = '$bracket'");

        // if the bracket is published
        if((bool)$bracket_pub) {
            if($op_queue_qry->num_rows > 0) {
                while($op_queue = $op_queue_qry->fetch_assoc()) {
                    if($op_queue['operation_id'] === $operation && (bool)$op_queue['active']) {
                        // the exact operation is currently active and we cannot take any further action
                        echo displayToast("error", "Operation is active presently inside of $bracket.", "Active Operation");
                        return;
                    } else {
                        // deactivate operations
                        $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['QID']}'");
                    }
                }
            }

            // now that we've cleaned up the operations; it's time to get that operation flowing
            $dbconn->query("INSERT INTO op_queue (room_id, operation_id, active, completed, rework, partially_completed, created) 
              VALUES ('$roomid', '$operation', FALSE, FALSE, FALSE, NULL, UNIX_TIMESTAMP())");
        } else {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['QID']}'");
            }
        }
    }

    function autoRelease($room_qry, $op_bracket, $room_bracket, $room_id) {
        global $dbconn;

        // if the bracket is NOT published
        if(!(bool)$room_qry[$room_bracket . '_published']) {
            // we're going to publish the sample bracket
            $dbconn->query("UPDATE rooms SET {$room_bracket}_published = TRUE WHERE id = $room_id");

            // next, lets make sure there's an operation available in op_queue
            $this->createOpQueue(true, $op_bracket, $room_qry[$room_bracket . '_bracket'], $room_id);

            // now lets alert the user that we've released the next bracket
            echo displayToast("info", "$room_bracket bracket has been released!", "$room_bracket Bracket Released");
        } else {
            echo displayToast("warning", "Unable to release $room_bracket Bracket as it's currently published!", "$room_bracket Bracket Not Released");
        }
    }

    function wc_jobsInQueue() {
        global $dbconn;

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS op_queueID, sales_order.so_num AS op_queueSOParent, 
          rooms.room AS op_queueRoom, op_queue.*, operations.*, rooms.* FROM op_queue 
              JOIN operations ON op_queue.operation_id = operations.id
                JOIN rooms ON op_queue.room_id = rooms.id
                  JOIN sales_order ON rooms.so_parent = sales_order.so_num
                    WHERE active = FALSE AND completed = FALSE AND published = TRUE AND operations.job_title != 'N/A'
                      ORDER BY sales_order.so_num DESC, operations.op_id DESC;");

        $output = array();
        $i = 0;

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                if(!empty($op_queue['assigned_to'])) {
                    $assigned_usrs = json_decode($op_queue['assigned_to']);

                    $name = null;

                    foreach($assigned_usrs as $usr) {
                        $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$usr'");
                        $usr = $usr_qry->fetch_assoc();

                        $name .= $usr['name'] . ", ";
                    }

                    $assignee = substr($name, 0, -2);
                } else {
                    $assignee = "&nbsp;";
                }

                if(substr($op_queue['op_id'], -2) !== '98') {
                    $output['data'][$i][] = "{$op_queue['op_queueSOParent']}{$op_queue['op_queueRoom']}-{$op_queue['iteration']}";
                    $output['data'][$i][] = $op_queue['room_name'];
                    $output['data'][$i][] = "<div class='custom_tooltip'>{$op_queue['responsible_dept']} <span class='tooltiptext'>{$op_queue['bracket']} Bracket</span></div>";
                    $output['data'][$i][] = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                    $output['data'][$i][] = date(DATE_DEFAULT, $op_queue['created']);
                    $output['data'][$i][] = $assignee;
                    $output['data'][$i]['DT_RowId'] = $op_queue['so_parent'];

                    $i += 1;
                }
            }
        } else {
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '--- None Queued ---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
        }

        return $output;
    }

    function wc_recentlyCompleted() {
        global $dbconn;

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS op_queueID, sales_order.so_num AS op_queueSOParent, 
            rooms.room AS op_queueRoom, op_queue.*, operations.*, rooms.*, op_audit_trail.end_time FROM op_queue 
            LEFT JOIN operations ON op_queue.operation_id = operations.id
            LEFT JOIN rooms ON op_queue.room_id = rooms.id
            LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num
            LEFT JOIN op_audit_trail ON op_queue.id = op_audit_trail.op_id
            WHERE active = FALSE AND completed = TRUE AND op_audit_trail.end_time IS NOT NULL
            ORDER BY sales_order.so_num DESC, operations.op_id DESC LIMIT 0,250;");

        $output = array();
        $i = 0;

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $output['data'][$i][] = "{$op_queue['op_queueSOParent']}{$op_queue['op_queueRoom']}-{$op_queue['iteration']}";
                $output['data'][$i][] = $op_queue['room_name'];
                $output['data'][$i][] = $op_queue['bracket'];
                $output['data'][$i][] = $op_queue['op_id'] . ": " . $op_queue['job_title'];

                //$time = Carbon::createFromTimestamp($op_queue['end_time']); // grab the carbon timestamp
                //$output['data'][$i][] = $time->diffForHumans(); // obtain the difference in readable format for humans!
                $output['data'][$i][] = date(DATE_DEFAULT, $op_queue['end_time']); // meh, readable format breaks the completed date

                $i += 1;
            }
        } else {
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '--- Nothing to Show! ---';
            $output['data'][$i][] = '---';
        }

        return $output;
    }

    function wc_activeJobs() {
        global $dbconn;

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS op_queueID, sales_order.so_num AS op_queueSOParent, 
          rooms.room AS op_queueRoom, op_queue.*, operations.*, rooms.* FROM op_queue 
              JOIN operations ON op_queue.operation_id = operations.id
                JOIN rooms ON op_queue.room_id = rooms.id
                  JOIN sales_order ON rooms.so_parent = sales_order.so_num
                    WHERE active = TRUE AND published = TRUE AND (completed = FALSE OR completed IS NULL)
                      ORDER BY sales_order.so_num DESC, operations.op_id DESC;");

        $output = array();
        $i = 0;

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                if((bool)$op_queue['otf_created']) {
                    $tag = 'OTF';
                } else {
                    $tag = "{$op_queue['iteration']}";
                }

                if(!empty($op_queue['subtask'])) {
                    $subtask = " ({$op_queue['subtask']})";
                } else {
                    $subtask = NULL;
                }

                $employees = json_decode($op_queue['active_employees']);
                $active_emp = null;

                foreach($employees as $employee) {
                    $emp_qry = $dbconn->query("SELECT * FROM user WHERE id = $employee");
                    $emp = $emp_qry->fetch_assoc();

                    $active_emp .= $emp['name'] . ", ";
                }

                $active_emp = rtrim($active_emp, ", ");

                $output['data'][$i][] = "{$op_queue['op_queueSOParent']}{$op_queue['op_queueRoom']}-$tag";
                $output['data'][$i][] = $op_queue['room_name'];
                $output['data'][$i][] = $op_queue['bracket'];
                $output['data'][$i][] = $op_queue['op_id'] . ": " . $op_queue['job_title'] . $subtask;
                $output['data'][$i][] = $active_emp;

                // TODO: Fix this so that the start time reflects the last activated time
                $output['data'][$i][] = date(TIME_ONLY, $op_queue['start_time']);

                $i += 1;
            }
        } else {
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '--- None Active ---';
            $output['data'][$i][] = '---';
            $output['data'][$i][] = '---';
        }

        return $output;
    }

    function startOp($id, $operation) {
        global $dbconn;

        $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'");
        $op_info = $op_qry->fetch_assoc();

        $otf = ($op_info['job_title'] === 'On The Fly') ? TRUE : FALSE;

        $subtask = sanitizeInput($_POST['subtask']);
        $notes = sanitizeInput($_POST['notes']);
        $time = date(DATE_TIME_ABBRV);

        $otf_so = sanitizeInput($_REQUEST['otf_so_num']);
        $otf_room = sanitizeInput($_REQUEST['otf_room']);
        $otf_op = sanitizeInput($_REQUEST['otf_op']);
        $otf_notes = sanitizeInput($_REQUEST['otf_notes']);
        $otf_iteration = sanitizeInput($_REQUEST['otf_iteration']);

        $notes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />";

        switch($operation) {
            case '000: Non-Billable':
                $admin_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'"); // grab the normal op info

                if($admin_qry->num_rows > 0) { // if we were able to get the operation
                    $admin_results = $admin_qry->fetch_assoc();

                    if((bool)$admin_results['always_visible']) { // check to confirm this is an always visible op
                        $active[] = $_SESSION['shop_user']['id']; // add individual to the list of active employees

                        $active_employees = json_encode($active); // re-encode it for saving

                        // create the op queue listing to be able to update information
                        $dbconn->query("INSERT INTO op_queue (operation_id, start_time, active, created, active_employees, subtask) VALUES ('{$admin_results['id']}', UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP(), '$active_employees', '$subtask')");

                        $inserted_id = $dbconn->insert_id; // grab the inserted id for audit trail records

                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees), "Subtask"=>$subtask];
                        $final_changes = json_encode($changes);

                        $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, start_time) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");

                        echo displayToast("success", "Successfully started a non-billable operation.", "Started Non-Billable operation");
                    } else {
                        echo displayToast("error", "Unable to properly start this operation (not always visible).", "Error Starting Operation.");
                    }
                } else {
                    dbLogSQLErr($dbconn);
                }

                break;

            case '000: Cabinet Vision':
                $admin_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'"); // grab the normal op info

                if($admin_qry->num_rows > 0) { // if we were able to get the operation
                    $admin_results = $admin_qry->fetch_assoc();

                    if((bool)$admin_results['always_visible']) { // check to confirm this is an always visible op
                        $active[] = $_SESSION['shop_user']['id']; // add individual to the list of active employees

                        $active_employees = json_encode($active); // re-encode it for saving

                        // create the op queue listing to be able to update information
                        $dbconn->query("INSERT INTO op_queue (operation_id, start_time, active, created, active_employees, subtask) VALUES ('{$admin_results['id']}', UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP(), '$active_employees', '$subtask')");

                        $inserted_id = $dbconn->insert_id; // grab the inserted id for audit trail records

                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees), "Subtask"=>$subtask];
                        $final_changes = json_encode($changes);

                        $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, start_time) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");

                        echo displayToast("success", "Successfully started Cabinet Vision.", "Started Cabinet Vision");
                    } else {
                        echo displayToast("error", "Unable to properly start this operation (not always visible).", "Error Starting Operation.");
                    }
                } else {
                    dbLogSQLErr($dbconn);
                }

                break;

            case '000: On The Fly':
                $otf_info = null;

                $otf_notes = "$otf_notes [$time - {$_SESSION['shop_user']['name']} <i>OTF Created</i>]<br />";

                $otf_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = $otf_so AND room = '$otf_room' AND iteration = '$otf_iteration'");

                if($otf_qry->num_rows > 0) {
                    $otf_info = $otf_qry->fetch_assoc();
                }

                // first check to see if anything exists in the op queue with this operation id, room, so# and iteration
                $exists_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$otf_op' AND room_id = '{$otf_info['id']}' AND published = TRUE AND completed = FALSE");

                if($exists_qry->num_rows > 0) { // if the operation already exists
                    echo displayToast("error", "Unable to create On The Fly operation. Already exists.", "Op Exists");
                } else { // this is a brand new operation!
                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$otf_so' AND room = '$otf_room' AND iteration = '$otf_iteration'");

                    if($room_qry->num_rows > 0) {
                        $room = $room_qry->fetch_assoc();

                        $room_id = $room['id'];
                    } else {
                        $room_id = null;
                    }

                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$otf_op'"); // grab the normal op info

                    if($op_qry->num_rows > 0) { // if we were able to get the operation
                        $operation = $op_qry->fetch_assoc();

                        // create the op queue listing to be able to update information
                        $stmt = $dbconn->prepare("INSERT INTO op_queue (room_id, operation_id, start_time, active, created, active_employees, otf_created) 
                            VALUES (?, ?, UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP(), ?, TRUE)");

                        $active[] = $_SESSION['shop_user']['id']; // add individual to the list of active employees

                        $active_employees = json_encode($active); // re-encode it for saving

                        $stmt->bind_param("iis", $room_id, $operation['id'], $active_employees);

                        $stmt->execute();
                        $stmt->close();

                        $inserted_id = $dbconn->insert_id; // grab the inserted id for audit trail records

                        $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees), "Subtask"=>$subtask, "OTF"=>'true'];
                        $final_changes = json_encode($changes);

                        $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())");

                        echo displayToast("success", "Started On The Fly operation.", "Successfully Started OTF");
                    } else {
                        dbLogSQLErr($dbconn);
                    }
                }

                break;

            case '000: Honey Do':
                $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'"); // grab the existing op queue id

                if($qry->num_rows > 0) {  // if we were able to find the operation inside of the queue
                    $results = $qry->fetch_assoc(); // grab the information

                    $changes = null; // our changes are nothing presently

                    $active = json_decode($results['active_employees']); // grab the current list of active employees

                    $active[] = $_SESSION['shop_user']['id']; // add individual to the list of active employees

                    $active_employees = json_encode($active); // re-encode it for saving

                    if($results['start_time'] === null) { // if this op queue item has never been started
                        if($dbconn->query("UPDATE op_queue SET active = TRUE, start_time = UNIX_TIMESTAMP(), active_employees = '$active_employees' WHERE id = '$id'")) {
                            $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                            $final_changes = json_encode($changes);

                            $shop_usr_id = $_SESSION['shop_user']['id'];

                            $stmt = $dbconn->prepare("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, start_time) VALUES (?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
                            $stmt->bind_param("iis", $id, $shop_usr_id, $final_changes);

                            $stmt_result = $stmt->execute();
                            $stmt->close();

                            if($stmt_result)
                                echo displayToast("success", "Successfully started operation.", "Started Operation");
                            else
                                dbLogSQLErr($dbconn);
                        } else {
                            dbLogSQLErr($dbconn);
                        }
                    } else { // if the operation has been started previously
                        $stmt = $dbconn->prepare("UPDATE op_queue SET active = TRUE, active_employees = ? WHERE id = ?");
                        $stmt->bind_param("si", $active_employees, $id);
                        $stmt_result = $stmt->execute();
                        $stmt->close();

                        if($stmt_result) {
                            $changes = ["Active"=>TRUE, "Resumed Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                            $final_changes = json_encode($changes);

                            $stmt = $dbconn->prepare("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, start_time) VALUES (?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
                            $stmt->bind_param("iis", $id, $_SESSION['shop_user']['id'], $final_changes);
                            $stmt_result = $stmt->execute();
                            $stmt->close();

                            if($stmt_result)
                                echo displayToast("success", "Successfully resumed operation.", "Resumed Operation");
                            else
                                dbLogSQLErr($dbconn);
                        } else {
                            dbLogSQLErr($dbconn);
                        }
                    }
                } else { // we were unable to find an operation in the queue that existed
                    dbLogSQLErr($dbconn); // gonna throw an error here...
                }

                break;

            default:
                $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'"); // grab the existing op queue id

                if($qry->num_rows > 0) {  // if we were able to find the operation inside of the queue
                    $results = $qry->fetch_assoc(); // grab the information

                    $changes = null; // our changes are nothing presently

                    $active = json_decode($results['active_employees']); // grab the current list of active employees

                    $active[] = $_SESSION['shop_user']['id']; // add individual to the list of active employees

                    $active_employees = json_encode($active); // re-encode it for saving

                    if($results['start_time'] === null) { // if this op queue item has never been started
                        if($dbconn->query("UPDATE op_queue SET active = TRUE, start_time = UNIX_TIMESTAMP(), active_employees = '$active_employees' WHERE id = '$id'")) {
                            $changes = ["Active"=>TRUE, "Start Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                            $final_changes = json_encode($changes);

                            $stmt = $dbconn->prepare("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, start_time) VALUES (?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
                            $stmt->bind_param("iis", $id, $shop_usr_id, $final_changes);

                            $shop_usr_id = $_SESSION['shop_user']['id'];

                            $stmt_result = $stmt->execute();
                            $stmt->close();

                            if($stmt_result)
                                echo displayToast("success", "Successfully started operation.", "Started Operation");
                            else
                                dbLogSQLErr($dbconn);
                        } else {
                            dbLogSQLErr($dbconn);
                        }
                    } else { // if the operation has been started previously
                        $stmt = $dbconn->prepare("UPDATE op_queue SET active = TRUE, active_employees = ? WHERE id = ?");
                        $stmt->bind_param("si", $active_employees, $id);
                        $stmt_result = $stmt->execute();
                        $stmt->close();

                        if($stmt_result) {
                            $changes = ["Active"=>TRUE, "Resumed Time"=>time(), "Active Employees"=>json_decode($active_employees)];
                            $final_changes = json_encode($changes);

                            $stmt = $dbconn->prepare("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, start_time) VALUES (?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
                            $stmt->bind_param("iis", $id, $_SESSION['shop_user']['id'], $final_changes);
                            $stmt_result = $stmt->execute();
                            $stmt->close();

                            if($stmt_result)
                                echo displayToast("success", "Successfully resumed operation.", "Resumed Operation");
                            else
                                dbLogSQLErr($dbconn);
                        } else {
                            dbLogSQLErr($dbconn);
                        }
                    }
                } else { // we were unable to find an operation in the queue that existed
                    dbLogSQLErr($dbconn); // gonna throw an error here...
                }

                break;
        }
    }

    function pauseOp($id, $notes) {
        global $dbconn;

        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'"); // grab the item from the operation queue
        $op_queue = $op_queue_qry->fetch_assoc();

        $time = date(DATE_TIME_ABBRV); // grab the current time

        $room_id = $op_queue['room_id']; // assign the room ID for use inside of function incrementJob

        $op_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");
        $op_results = $op_qry->fetch_assoc();

        $active_emp = json_decode($op_results['active_employees']);

        if(in_array($_SESSION['shop_user']['id'], $active_emp)) {
            $loc = array_search($_SESSION['shop_user']['id'], $active_emp);
            unset($active_emp[$loc]);
        }

        $active_emp = array_values($active_emp);

        if(count($active_emp) > 0) {
            $active = "TRUE";
        } else {
            $active = "FALSE";
        }

        $active_employees = json_encode($active_emp);

        if($dbconn->query("UPDATE op_queue SET active = $active, partially_completed = TRUE, completed = FALSE, active_employees = '$active_employees' WHERE id = $id")) {
            $changed = ["End time"=>time(), "Active"=>$active, "Partially Completed"=>true, "Active Employees"=>json_decode($active_employees)];
            $changed = json_encode($changed);

            $stmt = $dbconn->prepare("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, end_time) VALUES (?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
            $stmt->bind_param("iis", $id, $_SESSION['shop_user']['id'], $changed);

            if($stmt->execute()) {
                $stmt->close();

                return displayToast("info", "Operation has been marked as partially completed.", "Partially Closed Operation");
            } else
                return dbLogSQLErr($dbconn);
        } else {
            return dbLogSQLErr($dbconn);
        }
    }

    function stopOp($id, $notes, $rw_reqd, $rw_reason, $opnum) {
        global $dbconn;

        if(($_SESSION['last_op_action'] + 5) <= time()) {
            $_SESSION['last_op_action'] = time();

            $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'"); // grab the item from the operation queue
            $op_queue = $op_queue_qry->fetch_assoc();

            $time = date(DATE_TIME_ABBRV); // grab the current time

            $room_id = $op_queue['room_id']; // assign the room ID for use inside of function incrementJob

            $finalnotes = null; // define final notes as null initially

            // figure out if the bracket is published
            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
            $room = $room_qry->fetch_assoc();

            // next, grab the room bracket and blow it to smitherines
            $full_bracket = json_decode($room['individual_bracket_buildout']);

            // now, we find out what the next op is that we're progressing to
            $next_op_pos = ($rw_reqd === 'true') ? array_search($op_queue['operation_id'], $full_bracket) - 1 : array_search($op_queue['operation_id'], $full_bracket) + 1;
            $next_op = $full_bracket[$next_op_pos]; // grab the next op in the "bracket"

            //echo "Next Operation: " . $next_op . "<br />";

            // time to auto-release brackets!
            switch($op_queue['operation_id']) {
                case 1: // 110: Initial Meeting > 205: Sample Door Request
                    $this->autoRelease($room, 'Sample', 'sample', $room_id);

                    break;

                case 109: // 300: SA Review Request for Quote > 205: Sample Door Request
                    $this->autoRelease($room, 'Sample', 'sample', $room_id);

                    break;

                case 28: // 355: Place Orders > 505: Pick List for Box, 605: Pick List for Custom, 410: Door Quote
                    $this->autoRelease($room, 'Main', 'main', $room_id);
                    $this->autoRelease($room, 'Custom', 'custom', $room_id);
                    $this->autoRelease($room, 'Drawer & Doors', 'doordrawer', $room_id);

                    break;

                case 58: // 540: Finishing > 805: Assembly
                    $this->autoRelease($room, 'Shipping', 'shipping', $room_id);

                    break;

                case 67: // 830: Load Inspection > 705: Manage Install
                    $this->autoRelease($room, 'Installation', 'install_bracket', $room_id);

                    break;

                case 177: // 192: Payment of Deposit > 300: SA Review & Quote Prep
                    $this->autoRelease($room, 'Pre-Production', 'preproduction_bracket', $room_id);

                    break;
            }
            // end of auto-release brackets!

            // get the individual op info
            $next_op_info_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$next_op'");
            $next_op_info = $next_op_info_qry->fetch_assoc();

            // get the next operation's bracket
            switch($next_op_info['bracket']) {
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
                case 'Main':
                    $bracket = 'main_bracket';
                    $published = 'main_published';
                    break;
                case 'Shipping':
                    $bracket = 'shipping_bracket';
                    $published = 'shipping_published';
                    break;
                case 'Installation':
                    $bracket = 'install_bracket';
                    $published = 'install_bracket_published';
                    break;
                default:
                    $bracket = 'sales_bracket';
                    $published = 'sales_published';
                    break;
            }

            $cur_op_info_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'");
            $cur_op_info = $cur_op_info_qry->fetch_assoc();

            if(!empty($_FILES['uploadedfile'])) {
                $target_dir = SITE_ROOT . "/attachments/";
                $target_ext = end(explode(".", $_FILES['attachment']['name']));

                if(!file_exists("{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}")) {
                    mkdir("{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}", 0777, true);
                }

                $job_title_fn = str_replace(" ", "_", strtolower($cur_op_info['job_title']));

                $target_file = "{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}/{$cur_op_info['op_id']}-$job_title_fn.{$target_ext}";

                $uploadOK = true;
                $upload_err = '';
                $fileType = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);

                if($fileType !== 'pdf') {
                    $uploadOK = false;
                    $upload_err .= "Incorrect Filetype. PDF only. Received $fileType.";
                }

                if(file_exists($target_file)) {
                    $uploadOK = false;
                    $upload_err .= "File already exists on the server.";
                }
            }

            if($rw_reqd === 'true') { // rework is required
                $stmt = $dbconn->prepare("UPDATE op_queue SET active = FALSE, completed = TRUE, partially_completed = FALSE, rework = TRUE, active_employees = NULL WHERE id = ?");

                $stmt->bind_param("i", $id);

                if($stmt->execute()) {
                    $stmt->close();

                    $changed = ["End time" => time(), "Active" => false, "Completed" => true, "Active Employees" => 'NULL', "Rework" => true]; // set what has changed for audit trail
                    $changed = json_encode($changed); // encode the audit trail for retrieval later

                    // if we're able to insert into the audit trail successfully
                    if ($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, end_time) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())")) {
                        // now we need to deactivate any old ops if bracket is published
                        $bracket_ops = array();

                        // build the bracket operations
                        $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

                        // create an array of all the ops
                        while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
                            // if the operation is not an x98 operation then add it to the array, otherwise exclude it
                            if((int)substr($all_bracket_ops['op_id'], -2) !== 98) {
                                $bracket_ops[] = $all_bracket_ops['id'];
                            }
                        }

                        // grab all operations in the queue for this room that are not OTF
                        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$room_id' AND otf_created = FALSE");

                        // if we were able to find any operations in the queue
                        if($op_queue_qry->num_rows > 0) {
                            // for every operation
                            while($op_queue = $op_queue_qry->fetch_assoc()) {
                                // lets find out if this operation is part of the bracket
                                if(in_array($op_queue['operation_id'], $bracket_ops)) {
                                    // it's part of the bracket, lets unpublish it
                                    $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                                }
                            }
                        }

                        // we've deactivated and/or published all operations related to the queue, now lets create the operation in the queue
                        $ind_op_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$next_op' AND room_id = '$room_id'");

                        // grab the room information for creation of the queued operation
                        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
                        $room = $room_qry->fetch_assoc();

                        // now, create the operation that SHOULD be active
                        $stmt = $dbconn->prepare("INSERT INTO op_queue (room_id, operation_id, active, completed, rework, partially_completed, created) VALUES (?, ?, FALSE, FALSE, TRUE, FALSE, UNIX_TIMESTAMP())");

                        $stmt->bind_param("ii", $room_id, $next_op);

                        if($stmt->execute()) {
                            $stmt->close();

                            $stmt = $dbconn->prepare("UPDATE rooms SET $bracket = ? WHERE id = ?");
                            $stmt->bind_param("ii", $next_op, $room_id);

                            $stmt->execute();
                            $stmt->close();

                            echo displayToast("warning", "Flagged operation for rework!<br /> Moved to {$next_op_info['op_id']}: {$next_op_info['job_title']} in {$next_op_info['responsible_dept']}.", "Operation Scheduled for Rework");
                        } else {
                            dbLogSQLErr($dbconn);
                        }
                    }
                } else {
                    dbLogSQLErr($dbconn);
                }
            } else {
                // if we've successfully communicated the update to the operation and not completing rework
                $stmt = $dbconn->prepare("UPDATE op_queue SET active = FALSE, completed = TRUE, partially_completed = FALSE, rework = FALSE, active_employees = NULL WHERE id = ?");
                $stmt->bind_param("i", $id);

                if($stmt->execute()) {
                    $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Completed"=>true, "Active Employees"=>'[]']; // set what has changed for audit trail
                    $changed = json_encode($changed); // encode the audit trail for retrieval later

                    if(!empty($_FILES['uploadedfile'])) {
                        if(move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                            echo displayToast("success", "Uploaded file successfully.", "File Uploaded");
                        } else {
                            echo displayToast("error", "Unable to upload file. $upload_err", "File Error");
                        }
                    }

                    // if we're able to insert into the audit trail successfully
                    if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp, end_time) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())")) {
                        // find out if the brackets are the same for the next op vs this op
                        if($next_op_info['bracket'] === $cur_op_info['bracket']) {
                            // grab the room and see if the bracket is published
                            $bracket_pub = $room[$published];

                            if((bool)$bracket_pub) { // indeed, bracket is published and we can continue on!
                                /** Goal: Deactivate any ops in the matching bracket **/
                                // now we need to deactivate any old ops if bracket is published
                                $bracket_ops = array();

                                // build the bracket operations
                                $all_bracket_ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE");

                                // create an array of all the ops
                                while($all_bracket_ops = $all_bracket_ops_qry->fetch_assoc()) {
                                    // if the operation is not an x98 operation then add it to the array, otherwise exclude it
                                    if((int)substr($all_bracket_ops['op_id'], -2) !== 98) {
                                        $bracket_ops[] = $all_bracket_ops['id'];
                                    }
                                }

                                // grab all operations in the queue for this room that are not OTF
                                $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$room_id' AND otf_created = FALSE");

                                // if we were able to find any operations in the queue
                                if($op_queue_qry->num_rows > 0) {
                                    // for every operation
                                    while($op_queue = $op_queue_qry->fetch_assoc()) {
                                        // lets find out if this operation is part of the bracket
                                        if(in_array($op_queue['operation_id'], $bracket_ops)) {
                                            // it's part of the bracket, lets unpublish it
                                            $dbconn->query("UPDATE op_queue SET published = FALSE WHERE id = '{$op_queue['id']}'");
                                        }
                                    }
                                }

                                // we've deactivated and/or published all operations related to the queue, now lets create the operation in the queue
                                $ind_op_qry = $dbconn->query("SELECT * FROM op_queue WHERE operation_id = '$next_op' AND room_id = '$room_id'");

                                // grab the room information for creation of the queued operation
                                $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
                                $room = $room_qry->fetch_assoc();

                                // now, create the operation that SHOULD be active
                                $stmt = $dbconn->prepare("INSERT INTO op_queue (room_id, operation_id, active, completed, rework, partially_completed, created) VALUES (?, ?, FALSE, FALSE, FALSE, FALSE, UNIX_TIMESTAMP())");

                                $stmt->bind_param("ii", $room_id, $next_op);

                                if($stmt->execute()) {
                                    $stmt->close();

                                    $stmt = $dbconn->prepare("UPDATE rooms SET $bracket = ? WHERE id = ?");
                                    $stmt->bind_param("ii", $next_op, $room_id);

                                    $stmt->execute();
                                    $stmt->close();

                                    if((int)$cur_op_info['id'] === 140) {
                                        $dbconn->query("UPDATE rooms SET order_status = '$' WHERE id = '$room_id'");
                                    }

                                    echo displayToast("success", "Successfully completed operation.<br /> Moved on to {$next_op_info['op_id']}: {$next_op_info['job_title']} in {$next_op_info['responsible_dept']}.", "Operation Completed");
                                } else {
                                    dbLogSQLErr($dbconn);
                                }
                            } else {
                                echo displayToast("warning", "Bracket is no longer published.", "Bracket Unpublished");
                            }
                        } else {
                            if($opnum !== '000') {
                                echo displayToast("info", "Bracket is now closed.", "Bracket Closed");
                            } else {
                                echo displayToast("success", "Closed out operation.", "Closed Operation");
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
            }
        } else {
            echo displayToast("warning", "Please wait at least 5 seconds between operation updates.", "Please Wait");
        }
    }
}