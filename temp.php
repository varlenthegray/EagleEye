<?php
require("includes/header_start.php");

$qry = $dbconn->query("SELECT * FROM rooms");

while($result = $qry->fetch_assoc()) {
    $departmented_bracket = json_decode($result['individual_bracket_buildout']);
    $fullbracket = array();

    foreach($departmented_bracket as $ind_bracket) {
        foreach($ind_bracket as $op) {
            $fullbracket[] = $op;
        }
    }

    $final = json_encode($fullbracket);

    //$dbconn->query("UPDATE rooms SET devsmc.rooms.individual_bracket_buildout = '$final' WHERE id = '{$result['id']}'");
}

//echo password_hash("sweetpea11", PASSWORD_DEFAULT);