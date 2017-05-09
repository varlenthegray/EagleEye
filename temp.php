<?php
require_once ("includes/header_start.php");

$rooms = $dbconn->query("SELECT * FROM rooms");

while($ind_room = $rooms->fetch_assoc()) {
    $ind_brackets = json_decode($ind_room['individual_bracket_buildout']);
    $single_string = array();

    foreach($ind_brackets as $single_bracket) {
        foreach($single_bracket as $operation) {
            $single_string[] = $operation;
        }
    }

    $commit = json_encode($single_string);

    //$dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$commit' WHERE id = {$ind_room['id']}");
}
