<?php
require ("../includes/header_start.php");

outputPHPErrs();

$rooms_qry = $dbconn->query("SELECT * FROM rooms WHERE individual_bracket_buildout LIKE '%86,87,88,19%'");

if($rooms_qry->num_rows > 0) {
    while($rooms = $rooms_qry->fetch_assoc()) {
        $first_slice = explode("86,87,88,19", $rooms['individual_bracket_buildout']);

        $middle_slice = "110,111,112,113,114,115,19";

        $final_slice = $first_slice[0] . $middle_slice . $first_slice[1];

        $dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$final_slice' WHERE id = '{$rooms['id']}'");
    }
}