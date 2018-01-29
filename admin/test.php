<?php
require ("../includes/header_start.php");
require("../includes/classes/mail_handler.php");
require ("../includes/classes/queue.php");

//outputPHPErrs();

/*$excluded_ops = '113,';

$rooms_qry = $dbconn->query("SELECT * FROM rooms WHERE individual_bracket_buildout LIKE '%$excluded_ops%'");

if($rooms_qry->num_rows > 0) {
    while($rooms = $rooms_qry->fetch_assoc()) {
        $first_slice = explode($excluded_ops, $rooms['individual_bracket_buildout']);

        //$middle_slice = "110,111,112,113,114,115,19";

        $final_slice = $first_slice[0] . $first_slice[1];

        $dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$final_slice' WHERE id = '{$rooms['id']}'");

        echo "{$rooms['id']} has been updated to $final_slice.<br />";
    }
}

echo "<h4>Update completed.</h4>";*/

$remove = array('109','166','85','110','113','114','115','140');
$room_qry = $dbconn->query("SELECT * FROM rooms");

while($room = $room_qry->fetch_assoc()) {
    $buildout = json_decode($room['individual_bracket_buildout']);

    foreach($remove as $op) {
        if(in_array($op, $buildout)) {
            $key = array_search($op, $buildout);

            unset($buildout[$key]);
        }

        $dbconn->query("UPDATE op_queue SET published = FALSE WHERE operation_id = $op AND published = TRUE AND room_id = {$room['id']}");
    }

    $final_output = json_encode(array_values($buildout));

    $dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$final_output' WHERE id = {$room['id']}");
}

echo "Completed with removing operations.";