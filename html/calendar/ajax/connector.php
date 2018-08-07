<?php
require_once SITE_ROOT . '/includes/header_start.php';
require_once SITE_ROOT . '/assets/plugins/dhtmlxScheduler/connector/scheduler_connector.php';

$cal_res = mysqli_connect(DB_SERVER_NAME, DB_USERNAME, DB_PASS, DB_DATABASE);

// grab the calendar connection
$dbconn_cal = new SchedulerConnector($cal_res);

// render the table (? Unconfirmed in documentation)
$dbconn_cal->render_table('calendar', 'id', 'start_date,end_date,text');