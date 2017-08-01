<?php
require_once ("../../includes/header_start.php");

$find = sanitizeInput($_REQUEST['term']);
$search = sanitizeInput($_REQUEST['search']);

switch($search) {
    case 'so_num':
        $qry = $dbconn->query("SELECT so_num FROM sales_order WHERE so_num LIKE '%$find%' LIMIT 0,50");

        $results = array();

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $results[] = $result['so_num'];
            }
        }

        echo json_encode($results);

        break;
    case 'room':
        $so_num = sanitizeInput($_REQUEST['so_num']);

        $qry = $dbconn->query("SELECT DISTINCT(room) FROM rooms WHERE so_parent = '$so_num'");

        if($qry->num_rows > 0) {
            while($room = $qry->fetch_assoc()) {
                echo "<option value='{$room['room']}'>{$room['room']}</option>";
            }
        }

        break;
    case 'iteration':
        $so_num = sanitizeInput($_REQUEST['so_num']);
        $room = sanitizeInput($_REQUEST['room']);

        $qry = $dbconn->query("SELECT DISTINCT(iteration) FROM rooms WHERE so_parent = '$so_num' AND room = '$room'");

        if($qry->num_rows > 0) {
            while($room = $qry->fetch_assoc()) {
                echo "<option value='{$room['iteration']}'>{$room['iteration']}</option>";
            }
        }

        break;
}

