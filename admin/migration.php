<?php
require ("../includes/header_start.php");

$queue_qry = $dbconn->query("SELECT op_queue.*, operations.job_title FROM op_queue LEFT JOIN operations ON op_queue.operation_id = operations.id");

while($queue = $queue_qry->fetch_assoc()) {
    $stmt = $dbconn->prepare("INSERT INTO queue (id, type, type_id, operation, status, created, last_updated, published, active_emp, subtask, otf, priority, assigned_to) VALUES 
        (?, 'room', ?, ?, ?, ?, NULL, ?, ?, ?, ?, NULL, NULL)");

    if((bool)$queue['active']) {
        $status = 'Active';
    } elseif((bool)$queue['completed']) {
        $status = 'Completed';
    } elseif((bool)$queue['rework']) {
        $status = 'Rework';
    } elseif((bool)$queue['partially_completed']) {
        $status = 'Partially Completed';
    } else {
        if(!(bool)$queue['otf_created']) {
            $status = 'New';
        }
    }

    if($queue['job_title'] === 'Non-Billable') {
        $published = FALSE;
    } else {
        $published = $queue['published'];
    }

    $stmt->bind_param('iiisiissi', $queue['id'], $queue['room_id'], $queue['operation_id'], $status, $queue['created'], $published, $queue['active_employees'], $queue['subtask'], $queue['otf_created']);

    $stmt->execute();
}

$stmt->close();

echo "Migration complete.";