<?php
require ("../../includes/config.php");

if($_REQUEST['action'] === 'start_job') {
    $id = sanitizeInput($_POST['jobID'], $dbconn);

    $qry = $dbconn->query("SELECT id, job_id, qty_requested, operation, part_id FROM jobs WHERE id = '$id' AND NOT active");

    if($qry->num_rows === 1) {
        $output = $qry->fetch_assoc();

        echo json_encode($output);
    }
} elseif($_REQUEST['action'] === 'update_start_job') {
    $id = sanitizeInput($_POST['id'], $dbconn);

    if($dbconn->query("UPDATE jobs SET active = TRUE, started = UNIX_TIMESTAMP() WHERE id = '$id'")) {
        echo "success";
    }
}