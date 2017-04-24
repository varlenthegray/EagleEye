<?php
require ("../../includes/header_start.php");

switch($_REQUEST['action']) {
    case 'get_job_info':
        $id = sanitizeInput($_POST['jobID'], $dbconn);

        $qry = $dbconn->query("SELECT * FROM jobs WHERE id = '$id'");

        if($qry->num_rows === 1) {
            $output = $qry->fetch_assoc();

            echo json_encode($output);
        }

        break;
    case 'update_start_job':
        $id = sanitizeInput($_POST['id'], $dbconn);

        if($dbconn->query("UPDATE jobs SET active = TRUE, started = UNIX_TIMESTAMP() WHERE id = '$id'")) {
            echo "success";
        }

        break;
    case 'display_active_jobs':
        $qry = $dbconn->query("SELECT * FROM jobs WHERE active AND assigned_to = {$_SESSION['shop_user']['id']}");

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                echo "<tr class='cursor-hand update-active-job' id='job_id_{$result['id']}' data-toggle='modal' data-target='#modalUpdateJob' data-id='{$result['id']}'>";
                echo "  <td>{$result['job_id']}</td>";
                echo "  <td>{$result['operation']}</td>";

                $startTime = date(DATE_TIME_DEFAULT, $result['started']);

                echo "  <td id='startTime' data-toggle='tooltip' data-placement='top' title='$startTime'>
                        <span id='startTime{$result['id']}'></span>
                        <script>
                            $('#startTime{$result['id']}').html(moment({$result['started']} * 1000).fromNow());
                            
                            setInterval(function() {
                                $('#startTime{$result['id']}').html(moment({$result['started']} * 1000).fromNow());
                            }, 1000);
                        </script>
                    </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr>";
            echo "  <td colspan='4'>No active jobs</td>";
            echo "</tr>";
        }

        break;
    case 'display_job_queue':
        $qry = $dbconn->query("SELECT * FROM jobs WHERE NOT active AND assigned_to = {$_SESSION['shop_user']['id']} AND completed IS NULL");

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                echo "<tr class='cursor-hand queue-job-start' id='job_id_{$result['id']}' data-job-id='{$result['id']}'>";
                echo "  <td>{$result['job_id']}</td>";
                echo "  <td>{$result['operation']}</td>";
                echo "  <td>{$result['part_id']}</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr>";
            echo "  <td colspan='3'>No jobs in queue</td>";
            echo "</tr>";
        }

        break;
    case 'update_active_job':
        $id = sanitizeInput($_POST['jobID'], $dbconn);
        $notes = sanitizeInput($_POST['notes'], $dbconn);
        $status = sanitizeInput($_POST['status'], $dbconn);
        $qty = sanitizeInput($_POST['qty'], $dbconn);

        if($dbconn->query("UPDATE jobs SET completed = UNIX_TIMESTAMP(), active = 0, notes = '$notes', qty_completed = '$qty', status = '$status' WHERE id = $id")) {
            echo "success";
        } else
            die();

        break;
    default:
        die();

        break;
}