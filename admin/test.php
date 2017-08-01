<?php
require ("../includes/header_start.php");

outputPHPErrs();

$self_qry = $dbconn->query("SELECT * FROM user WHERE id = '2'");
$self = $self_qry->fetch_assoc();

if(!empty($self['multi_department'])) {
    $queue_qry_part = "(";

    $departments = json_decode($self['multi_department']);

    foreach($departments as $department) {
        $queue_qry_part .= "operations.responsible_dept = '$department' OR ";
    }

    $queue_qry_part = substr($queue_qry_part, 0, -4);

    $queue_qry_part .= ")";
}

echo $queue_qry_part;