<?php
include_once ("../../includes/header_start.php");
include_once ("../../ondemand/shopfloor/job_functions.php");

switch($_REQUEST['action']) {
    case 'get_op_info':
        $id = sanitizeInput($_REQUEST['opID']);

        $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");

        if($qry->num_rows === 1) {
            $output = $qry->fetch_assoc();

            echo json_encode($output);
        } else {
            $admin_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'");

            if($admin_qry->num_rows === 1) {
                $output = $admin_qry->fetch_assoc();

                echo json_encode($output);
            }
        }

        break;
    case 'update_start_job':
        $id = sanitizeInput($_POST['id']);

        $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");

        if($qry->num_rows > 0) {
            $results = $qry->fetch_assoc();

            $changes = null;

            if($results['start_time'] === null) {
                if($dbconn->query("UPDATE op_queue SET active = TRUE, start_time = UNIX_TIMESTAMP() WHERE id = '$id'")) {
                    $changes = ["Active"=>TRUE, "Start Time"=>time()];
                    $final_changes = json_encode($changes);

                    if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())"))
                        echo "success";
                    else
                        dbLogSQLErr($dbconn);
                } else {
                    dbLogSQLErr($dbconn);
                }
            } else {
                if($dbconn->query("UPDATE op_queue SET active = TRUE, resumed_time = UNIX_TIMESTAMP() WHERE id = '$id'")) {
                    $changes = ["Active"=>TRUE, "Resumed Time"=>time()];
                    $final_changes = json_encode($changes);

                    if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())"))
                        echo "success - resumed";
                    else
                        dbLogSQLErr($dbconn);
                } else {
                    dbLogSQLErr($dbconn);
                }
            }
        } else {
            $admin_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$id'");

            if($admin_qry->num_rows > 0) {
                $admin_results = $admin_qry->fetch_assoc();

                if($admin_results['department'] === 'Admin') {
                    $dbconn->query("INSERT INTO op_queue (operation_id, start_time, active, created) VALUES ('{$admin_results['id']}', UNIX_TIMESTAMP(), TRUE, UNIX_TIMESTAMP())");

                    $inserted_id = $dbconn->insert_id;

                    $changes = ["Active"=>TRUE, "Start Time"=>time()];
                    $final_changes = json_encode($changes);

                    $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$inserted_id', '{$_SESSION['shop_user']['id']}', '$final_changes', UNIX_TIMESTAMP())");

                    echo "success";
                }
            }
        }

        break;
    case 'display_active_jobs':
        activeJobGeneration();

        break;
    case 'display_job_queue':
        $queue = sanitizeInput($_REQUEST['queue']);

        $op_queue_qry = $dbconn->query("SELECT op_queue.id AS queueID, operations.id AS opID, op_queue.*, operations.* FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE active = FALSE AND completed = FALSE AND published = TRUE AND operations.responsible_dept = '$queue';");

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $id = $op_queue['queueID'];
                $sonum = $op_queue['so_parent'] . "-" . $op_queue['room'];
                $department = $op_queue['responsible_dept'];
                $operation = $op_queue['op_id'] . ": " . $op_queue['job_title'];
                $release_date = date(DATE_DEFAULT, $op_queue['created']);
                $op_info = ["id"=>$op_queue['id'], "op_id"=>$op_queue['op_id'], "department"=>$op_queue['department'], "job_title"=>$op_queue['job_title'], "responsible_dept"=>$op_queue['responsible_dept']];
                $op_info_payload = json_encode($op_info);

                echo "<tr class='cursor-hand queue-op-start' data-op-id='$id' data-op-info='$op_info_payload' data-long-op-id='$operation' data-long-part-id='$sonum'>";
                echo "  <td>$sonum</td>";
                echo "  <td>$department</td>";
                echo "  <td>$operation</td>";
                echo "  <td>$release_date</td>";
                echo "  <td>&nbsp;</td>";
                echo "</tr>";
            }
        }

        break;
    case 'update_active_job':
        function incrementJob($operation, $ind_bracket_array) {
            global $dbconn;
            global $room_id;

            if(in_array($operation, $ind_bracket_array)) { // sales bracket
                $next_operation = array_search($operation, $ind_bracket_array) + 1;
                $next_operation = $ind_bracket_array[$next_operation];

                $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$next_operation'");
                $op_result = $op_qry->fetch_assoc();

                switch($op_result['department']) {
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

                    case 'Box':
                        $bracket = 'box_bracket';
                        $published = 'box_published';
                        break;

                    default:
                        $bracket = 'sales_bracket';
                        $published = 'sales_published';
                        break;
                }

                if(!empty($next_operation)) {
                    $dbconn->query("UPDATE rooms SET $bracket = '$next_operation' WHERE id = '$room_id'"); // set the next operation in the bracket for that specific room

                    generateOpQueue($room_id, $published, $bracket, ''); // generate the next operation
                } else {
                    $dbconn->query("UPDATE rooms SET $published = 0 WHERE id = '$room_id'"); // bracket is no longer published
                }
            }
        }

        $id = sanitizeInput($_POST['opID'], $dbconn);
        $notes = sanitizeInput($_POST['notes'], $dbconn);
        $status = sanitizeInput($_POST['status'], $dbconn);
        $qty = sanitizeInput($_POST['qty'], $dbconn);

        $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");
        $results = $qry->fetch_assoc();
        $time = date(DATE_TIME_DEFAULT);

        $room_id = $results['room_id'];

        $notes_qry = $dbconn->query("SELECT notes FROM op_queue WHERE id = '$id'");
        $notes_result = $notes_qry->fetch_assoc();

        $finalnotes = null;

        if(empty($notes_result['notes'])) {
            $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />";
        } else {
            $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />" . $notes_result['notes'];
        }

        if($status === 'Complete') {
            if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', completed = TRUE, partially_completed = FALSE, rework = FALSE WHERE id = $id")) {
                $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Completed"=>true];
                $changed = json_encode($changed);

                if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())")) {
                    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$results['room_id']}'");
                    $room_results = $room_qry->fetch_assoc();

                    $ind_brackets = json_decode($room_results['individual_bracket_buildout']);

                    incrementJob($results['operation_id'], $ind_brackets[0]);
                    incrementJob($results['operation_id'], $ind_brackets[1]);
                    incrementJob($results['operation_id'], $ind_brackets[2]);
                    incrementJob($results['operation_id'], $ind_brackets[3]);
                    incrementJob($results['operation_id'], $ind_brackets[4]);
                    incrementJob($results['operation_id'], $ind_brackets[5]);

                    echo "success";
                } else {
                    dbLogSQLErr($dbconn);
                    die();
                }


            } else {
                dbLogSQLErr($dbconn);
                die();
            }
        } elseif($status === 'Partially Complete') {
            if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', partially_completed = TRUE, completed = FALSE WHERE id = $id")) {
                $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Partially Completed"=>true];
                $changed = json_encode($changed);

                if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())"))
                    echo "success - partial";
                else
                    dbLogSQLErr($dbconn);
            } else {
                dbLogSQLErr($dbconn);
                die();
            }
        } else {
            if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', rework = TRUE, completed = FALSE WHERE id = $id")) {
                $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Rework"=>true];
                $changed = json_encode($changed);

                if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())"))
                    echo "success - partial";
                else
                    dbLogSQLErr($dbconn);
            } else {
                dbLogSQLErr($dbconn);
                die();
            }
        }

        break;
    case 'update_brackets':
        $room = sanitizeInput($_REQUEST['room'], $dbconn);
        $sonum = sanitizeInput($_REQUEST['sonum'], $dbconn);

        // grab the individual bracket
        $indv_bracket_qry = $dbconn->query("SELECT individual_bracket_buildout FROM rooms WHERE so_parent = '$sonum' AND room = '$room'");
        $indv_bracket_results = $indv_bracket_qry->fetch_assoc();

        $op_ids = json_decode($indv_bracket_results['individual_bracket_buildout']);

        $final['ops'] = array();

        foreach($op_ids as $ind_id) {
            $qry = $dbconn->query("SELECT department, job_title, op_id, id FROM operations WHERE id = '$ind_id'");
            $result = $qry->fetch_assoc();

            if(!empty($result))
                $final['ops'][] = $result;
        }


        $qry = $dbconn->query("SELECT sales_published, preproduction_published, sample_published, doordrawer_published, custom_published, box_published FROM rooms WHERE so_parent = '$sonum' AND room = '$room'");
        $result = $qry->fetch_row();

        $final['pub'] = $result;

        echo json_encode($final);

        break;
    case 'save_room':
        $room = sanitizeInput($_POST['room'], $dbconn);
        $room_name = sanitizeInput($_POST['room_name'], $dbconn);
        $product_type = sanitizeInput($_POST['product_type'], $dbconn);
        $remodel_required = sanitizeInput($_POST['remodel_required'], $dbconn);
        $room_notes = sanitizeInput($_POST['room_notes'], $dbconn);
        $sales_bracket = sanitizeInput($_POST['sales_bracket'], $dbconn);
        $pre_prod_bracket = sanitizeInput($_POST['pre_prod_bracket'], $dbconn);
        $sample_bracket = sanitizeInput($_POST['sample_bracket'], $dbconn);
        $door_drawer_bracket = sanitizeInput($_POST['door_drawer_bracket'], $dbconn);
        $custom_bracket = sanitizeInput($_POST['custom_bracket'], $dbconn);
        $box_bracket = sanitizeInput($_POST['box_bracket'], $dbconn);
        $so_num = sanitizeInput($_POST['add_to_sonum'], $dbconn);

        $qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' AND room = '$room'");

        if($qry->num_rows === 1) {
            $update = $dbconn->query("UPDATE rooms SET room_name = '$room_name', product_type = '$product_type', remodel_reqd = '$remodel_required', room_notes = '$room_notes',
              sales_bracket = '$sales_bracket', preproduction_bracket = '$pre_prod_bracket', sample_bracket = '$sample_bracket', doordrawer_bracket = '$door_drawer_bracket',
              custom_bracket = '$custom_bracket', box_bracket = '$box_bracket', sales_bracket_priority = 4, preproduction_bracket_priority = 4, sample_bracket_priority = 4, 
              doordrawer_bracket_priority = 4, custom_bracket_priority = 4, box_bracket_priority = 4 WHERE so_parent = '$so_num' AND room = '$room'");

            if($update) {
                echo "success - update";
            } else {
                dbLogSQLErr($dbconn);
            }
        } else {
            $full_ops_qry = $dbconn->query("SELECT * FROM operations WHERE department != 'Admin'");

            if($full_ops_qry->num_rows > 0) {
                while($ops = $full_ops_qry->fetch_assoc()) {
                    $bracket[] = $ops['id'];
                }
            }

            $bracket = json_encode($bracket);

            $query = $dbconn->query("INSERT INTO rooms (so_parent, room, room_name, product_type, remodel_reqd, room_notes, sales_bracket, 
          preproduction_bracket, sample_bracket, doordrawer_bracket, custom_bracket, box_bracket, sales_bracket_priority, preproduction_bracket_priority, 
          sample_bracket_priority, doordrawer_bracket_priority, custom_bracket_priority, box_bracket_priority, individual_bracket_buildout) 
          VALUES ('$so_num', '$room', '$room_name', '$product_type', '$remodel_required', '$room_notes', '$sales_bracket', '$pre_prod_bracket',
          '$sample_bracket', '$door_drawer_bracket', '$custom_bracket', '$box_bracket', 4, 4, 4, 4, 4, 4, '$bracket')");

            if($query) {
                echo "success";
            } else {
                dbLogSQLErr($dbconn);
            }
        }

        break;
    case 'edit_room':
        $room_id = sanitizeInput($_POST['roomID'], $dbconn);

        $qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");

        if($qry->num_rows === 1) {
            $result = $qry->fetch_assoc();

            generateOpQueue($room_id, 'sales_published', 'sales_bracket', 'Sales');
            generateOpQueue($room_id, 'preproduction_published', 'preproduction_bracket', 'Pre-Production');
            generateOpQueue($room_id, 'sample_published', 'sample_bracket', 'Sample');
            generateOpQueue($room_id, 'doordrawer_published', 'doordrawer_bracket', 'Drawer & Doors');
            generateOpQueue($room_id, 'custom_published', 'custom_bracket', 'Custom');
            generateOpQueue($room_id, 'box_published', 'box_bracket', 'Box');

            echo json_encode($result);
        } else {
            dbLogSQLErr($dbconn);
            die();
        }

        break;
    case 'update_individual_bracket':
        $bracket = sanitizeInput($_POST['payload'], $dbconn);
        $sonum = sanitizeInput($_POST['sonum'], $dbconn);
        $room = sanitizeInput($_POST['room'], $dbconn);
        $published = json_decode($_POST['published']);

        generateOpQueue($room_id, 'sales_published', 'sales_bracket', 'Sales');
        generateOpQueue($room_id, 'preproduction_published', 'preproduction_bracket', 'Pre-Production');
        generateOpQueue($room_id, 'sample_published', 'sample_bracket', 'Sample');
        generateOpQueue($room_id, 'doordrawer_published', 'doordrawer_bracket', 'Drawer & Doors');
        generateOpQueue($room_id, 'custom_published', 'custom_bracket', 'Custom');
        generateOpQueue($room_id, 'box_published', 'box_bracket', 'Box');

        if($dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$bracket', sales_published = '{$published[0]}', preproduction_published = '{$published[1]}', 
          sample_published = '{$published[2]}', doordrawer_published = '{$published[3]}', custom_published = '{$published[4]}', box_published = '{$published[5]}'
          WHERE so_parent = '$sonum' AND room = '$room'")) {
            echo "success";
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'update_in_queue':
        $room_id = sanitizeInput($_POST['roomID']);
        $new_op_id = sanitizeInput($_POST['opID']);

        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'"); // room ID
        $room_results = $room_qry->fetch_assoc();

        $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$new_op_id'"); // new operation ID
        $new_op_results = $op_qry->fetch_assoc();

        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = '$room_id'"); // operation queue

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $this_op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'"); // for each op in op queue for this room
                $this_op_results = $this_op_qry->fetch_assoc(); // get the old op information

                if($this_op_results['responsible_dept'] === $new_op_results['responsible_dept']) { // if the departments are the same
                    if($dbconn->query("UPDATE op_queue SET published = FALSE where ID = '{$this_op_results['id']}'"))
                        echo "success";

                    generateOpQueue($room_id, 'sales_published', 'sales_bracket', 'Sales');
                    generateOpQueue($room_id, 'preproduction_published', 'preproduction_bracket', 'Pre-Production');
                    generateOpQueue($room_id, 'sample_published', 'sample_bracket', 'Sample');
                    generateOpQueue($room_id, 'doordrawer_published', 'doordrawer_bracket', 'Drawer & Doors');
                    generateOpQueue($room_id, 'custom_published', 'custom_bracket', 'Custom');
                    generateOpQueue($room_id, 'box_published', 'box_bracket', 'Box');
                }
            }
        }

        break;
    case 'get_all_ops':
        $qry = $dbconn->query("SELECT id, op_id, department, job_title, responsible_dept FROM operations");

        $ops = null;

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $ops[] = $result;
            }
        }

        $ops_out = json_encode($ops);

        echo $ops_out;

        break;
    case 'manage_bracket':
        $room = sanitizeInput($_REQUEST['room'], $dbconn);
        $sonum = sanitizeInput($_REQUEST['sonum'], $dbconn);

        // grab the individual bracket
        $indv_bracket_qry = $dbconn->query("SELECT individual_bracket_buildout FROM rooms WHERE so_parent = '$sonum' AND room = '$room'");
        $indv_bracket_results = $indv_bracket_qry->fetch_assoc();

        $op_ids = json_decode($indv_bracket_results['individual_bracket_buildout']);

        // grab all operations available
        $all_ops_qry = $dbconn->query("SELECT * FROM operations WHERE department != 'Admin' ORDER BY op_id ASC");

        $output = array();

        if($all_ops_qry->num_rows > 0) {
            while($all_ops = $all_ops_qry->fetch_assoc()) {
                if(in_array($all_ops['id'], $op_ids)) {
                    $output[$all_ops['department']] .= "<option value='{$all_ops['id']}'>{$all_ops['op_id']}-{$all_ops['job_title']}</option>"; // create the operation as "not selected"
                } else {
                    $output[$all_ops['department']] .=  "<option value='{$all_ops['id']}' selected>{$all_ops['op_id']}-{$all_ops['job_title']}</option>"; // create the operation as "selected"
                }
            }
        }

        $qry = $dbconn->query("SELECT sales_published, preproduction_published, sample_published, doordrawer_published, custom_published, box_published FROM rooms WHERE so_parent = '$sonum' AND room = '$room'");
        $result = $qry->fetch_row();

        $output['pub'] = $result;

        echo json_encode($output);

        break;
    default:
        die();

        break;
}