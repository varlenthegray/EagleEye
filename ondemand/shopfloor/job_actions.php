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
        $filter = $_POST['filter'];

        if(!empty($filter)) {
            $filter = "";
        }

        queuedJobGeneration();

        break;
    case 'update_active_job':
        $id = sanitizeInput($_POST['opID'], $dbconn);
        $notes = sanitizeInput($_POST['notes'], $dbconn);
        $status = sanitizeInput($_POST['status'], $dbconn);
        $qty = sanitizeInput($_POST['qty'], $dbconn);

        $qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '$id'");
        $results = $qry->fetch_assoc();
        $time = date(DATE_TIME_DEFAULT);

        $notes_qry = $dbconn->query("SELECT notes FROM op_queue WHERE id = '$id'");
        $notes_result = $notes_qry->fetch_assoc();

        $finalnotes = null;

        if(empty($notes_result['notes'])) {
            $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />";
        } else {
            $finalnotes = "$notes [$time - {$_SESSION['shop_user']['name']}]<br />" . $notes_result['notes'];
        }

        switch($status) {
            case 'Complete':
                if($dbconn->query("UPDATE op_queue SET end_time = UNIX_TIMESTAMP(), active = FALSE, notes = '$finalnotes', qty_completed = '$qty', completed = TRUE, partially_completed = FALSE, rework = FALSE WHERE id = $id")) {
                    $changed = ["End time"=>time(), "Active"=>false, "Notes"=>$finalnotes, "Qty Completed"=>$qty, "Completed"=>true];
                    $changed = json_encode($changed);

                    if($dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('$id', '{$_SESSION['shop_user']['id']}', '$changed', UNIX_TIMESTAMP())")) {
                        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '{$results['room_id']}'");
                        $room_results = $room_qry->fetch_assoc();

                        $bracket = json_decode($room_results['individual_bracket_buildout']);

                        ////////////////////////////////

                        echo "success";
                    } else {
                        dbLogSQLErr($dbconn);
                        die();
                    }


                } else {
                    dbLogSQLErr($dbconn);
                    die();
                }

                break;

            case 'Partially Complete':
                break;

            case 'Rework':
                break;
        }

        /*
        function incrementJob($op_queue_array, $ind_bracket_array) {
            global $dbconn;

            if(in_array($op_queue_array['operation_id'], $ind_bracket_array)) { // sales bracket
                $next_operation = array_search($op_queue_array['operation_id'], $ind_bracket_array) + 1;

                if(!empty($next_operation)) {
                    $dbconn->query("UPDATE rooms SET sales_bracket = '{$ind_bracket_array[$next_operation]}' WHERE id = '{$op_queue_array['room_id']}'"); // set the next operation in the bracket

                    generateOpQueue($op_queue_array['room_id'], 'sales_published', 'sales_bracket', '');
                } else {
                    $dbconn->query("UPDATE rooms SET sales_bracket = 0"); // close the bracket
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

                    incrementJob($results, $ind_brackets[0]);
                    incrementJob($results, $ind_brackets[1]);
                    incrementJob($results, $ind_brackets[2]);
                    incrementJob($results, $ind_brackets[3]);
                    incrementJob($results, $ind_brackets[4]);
                    incrementJob($results, $ind_brackets[5]);

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
        }*/

        break;

    case 'update_brackets':
        $room = sanitizeInput($_REQUEST['room'], $dbconn);
        $sonum = sanitizeInput($_REQUEST['sonum'], $dbconn);
        $iteration = sanitizeInput($_REQUEST['iteration'], $dbconn);

        // grab the individual bracket
        $override_qry = $dbconn->query("SELECT individual_bracket_buildout FROM rooms WHERE so_parent = '$sonum' AND room = '$room' AND iteration = '$iteration'");
        $override_desc = $override_qry->fetch_assoc();

        // set the individual bracket description
        $op_ids = $override_desc['individual_bracket_buildout'];
        $op_ids = json_decode($op_ids);

        $final['Sales'] = array();
        $final['Pre-Production'] = array();
        $final['Sample'] = array();
        $final['Drawer & Doors'] = array();
        $final['Custom'] = array();
        $final['Box'] = array();

        foreach($op_ids as $op_bracket) {
            foreach($op_bracket as $ind_id) {
                $qry = $dbconn->query("SELECT department, job_title, op_id, id FROM operations WHERE id = '$ind_id'");
                $result = $qry->fetch_assoc();

                if(!empty($result))
                    $final[$result['department']][] = $result;
            }
        }

        $qry = $dbconn->query("SELECT sales_published, preproduction_published, sample_published, doordrawer_published, custom_published, box_published FROM rooms WHERE so_parent = '$sonum' AND room = '$room' AND iteration = '$iteration'");
        $result = $qry->fetch_row();

        $final['Published'] = $result;

        echo json_encode($final);

        break;

    case 'save_room':
        $room = sanitizeInput($_POST['room'], $dbconn);
        $room_name = sanitizeInput($_POST['room_name'], $dbconn);
        $product_type = sanitizeInput($_POST['product_type'], $dbconn);
        $remodel_required = sanitizeInput($_POST['remodel_required'], $dbconn);
        $room_notes = sanitizeInput($_POST['room_notes'], $dbconn);
        $assigned_bracket = sanitizeInput($_POST['assigned_bracket'], $dbconn);
        $sales_bracket = sanitizeInput($_POST['sales_bracket'], $dbconn);
        $pre_prod_bracket = sanitizeInput($_POST['pre_prod_bracket'], $dbconn);
        $sample_bracket = sanitizeInput($_POST['sample_bracket'], $dbconn);
        $door_drawer_bracket = sanitizeInput($_POST['door_drawer_bracket'], $dbconn);
        $custom_bracket = sanitizeInput($_POST['custom_bracket'], $dbconn);
        $box_bracket = sanitizeInput($_POST['box_bracket'], $dbconn);
        $so_num = sanitizeInput($_POST['add_to_sonum'], $dbconn);
        $iteration = sanitizeInput($_POST['iteration'], $dbconn);

        $qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' AND room = '$room' AND iteration = '$iteration'");

        if($qry->num_rows === 1) {
            $update = $dbconn->query("UPDATE rooms SET iteration = '$iteration', room_name = '$room_name', product_type = '$product_type', remodel_reqd = '$remodel_required', room_notes = '$room_notes',
              assigned_bracket = '$assigned_bracket', sales_bracket = '$sales_bracket', preproduction_bracket = '$pre_prod_bracket', sample_bracket = '$sample_bracket', doordrawer_bracket = '$door_drawer_bracket',
              custom_bracket = '$custom_bracket', box_bracket = '$box_bracket', sales_bracket_priority = 4, preproduction_bracket_priority = 4, sample_bracket_priority = 4, 
              doordrawer_bracket_priority = 4, custom_bracket_priority = 4, box_bracket_priority = 4 WHERE so_parent = '$so_num' AND room = '$room' AND iteration = '$iteration'");

            if($update) {
                echo "success - update";
            } else {
                dbLogSQLErr($dbconn);
            }
        } else {
            $qry = $dbconn->query("SELECT * FROM operations WHERE department != 'Admin'");
            $bracket = array();

            while($result = $qry->fetch_assoc()) {
                $bracket[] = $result['id'];
            }

            $bracket = json_encode($bracket);

            $query = $dbconn->query("INSERT INTO rooms (iteration, so_parent, room, room_name, product_type, remodel_reqd, room_notes, sales_bracket, 
          preproduction_bracket, sample_bracket, doordrawer_bracket, custom_bracket, box_bracket, sales_bracket_priority, preproduction_bracket_priority, 
          sample_bracket_priority, doordrawer_bracket_priority, custom_bracket_priority, box_bracket_priority, individual_bracket_buildout) 
          VALUES ('$iteration', '$so_num', '$room', '$room_name', '$product_type', '$remodel_required', '$room_notes', '$sales_bracket', '$pre_prod_bracket',
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

    default:
        die();

        break;
}