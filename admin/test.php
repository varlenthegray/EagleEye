<?php
require ("../includes/header_start.php");

outputPHPErrs();

$excluded_ops = '118,';

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

echo "<h4>Update completed.</h4>";