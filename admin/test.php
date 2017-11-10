<?php
require ("../includes/header_start.php");
require("../includes/classes/mail_handler.php");

outputPHPErrs();

$op_qry = $dbconn->query("SELECT * FROM operations WHERE op_id != '000' AND job_title NOT LIKE '%N/A%' ORDER BY op_id;");

$ind_bracket = array();

$starting_ops = array();

while($op = $op_qry->fetch_assoc()) {
    if(empty($starting_ops[$op['bracket']])) {
        $starting_ops[$op['bracket']] = $op['id'];
    }

    $ind_bracket[] = $op['id'];
}

$ind_bracket_final = json_encode($ind_bracket);

echo "Bracket Final: $ind_bracket_final<br />Individual Starting Bracket: ";
print_r($starting_ops);