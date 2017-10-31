<?php
require("../../includes/header_start.php");
require("../../includes/classes/queue.php");

$queue = new Queue\queue();

$action = $_REQUEST['action'];

switch($action) {
    case 'display_jiq':
        $jiq = $queue->wc_jobsInQueue();

        echo json_encode($jiq);

        break;
    case 'display_recently_completed':
        $recently_completed = $queue->wc_recentlyCompleted();

        echo json_encode($recently_completed);

        break;
    case 'display_active_jobs':
        $active = $queue->wc_activeJobs();

        echo json_encode($active);

        break;
    default:
        die();
        break;
}