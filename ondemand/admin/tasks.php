<?php
require_once ("../../includes/header_start.php");

$action = sanitizeInput($_REQUEST['action']);

switch($action) {
    case 'submit_feedback':
        $task_desc = sanitizeInput($_REQUEST['description']);

        if($_SESSION['userInfo']['id'] === 16) {
            $submitted_by = $_SESSION['shop_user']['id'];
        } else {
            $submitted_by = $_SESSION['userInfo']['id'];
        }

        if($dbconn->query("INSERT INTO tasks (name, description, created, last_updated, priority, assigned_to, due_date, submitted_by, resolved) 
         VALUES ('', '$task_desc', UNIX_TIMESTAMP(), null, 'Low', 1, null, $submitted_by, FALSE);")) {
            echo displayToast("success", "Successfully logged feedback.", "Feedback Logged");
        } else {
            dbLogSQLErr($dbconn);
        }

        break;
    case 'get_task_list':
        $i = 0;

        $tasks_qry = $dbconn->query("SELECT tasks.id AS taskID, tasks.name AS taskName, user.name AS userName, tasks.*, user.* FROM tasks LEFT JOIN user ON tasks.assigned_to = user.id WHERE resolved = FALSE ORDER BY created DESC;");

        if($tasks_qry->num_rows > 0) {
            while($task = $tasks_qry->fetch_assoc()) {
                $short_desc = strip_tags(substr($task['description'], 0, 40) . "...");

                if(empty($task['last_update'])) {
                    $last_updated = "New";
                } else {
                    $last_updated = date(DATE_TIME_ABBRV, $task['last_update']);
                }

                if(empty($task['name'])) {
                    $name = "<i>Unassigned</i>";
                } else {
                    $name = $task['taskName'];
                }

                if($task['eta'] > 1) {
                    $humanized_eta = "hrs";
                } else {
                    $humanized_eta = "hr";
                }

                $created = date(DATE_TIME_ABBRV, $task['created']);

                $output['data'][$i][] = $task['taskID'];
                $output['data'][$i][] = $name;
                $output['data'][$i][] = $short_desc;
                $output['data'][$i][] = $task['userName'];
                $output['data'][$i][] = $created;
                $output['data'][$i][] = $task['priority'];
                $output['data'][$i][] = $task['eta_hrs'] . " $humanized_eta";
                $output['data'][$i][] = $last_updated;
                $output['data'][$i][] = $task['pct_completed'] * 100 . "%";
                $output['data'][$i]['DT_RowId'] = $task['id'];

                $i += 1;
            }
        }

        echo json_encode($output);

        break;
    default:
        displayToast("error", "No action specified.", "No action.");
        die();
}