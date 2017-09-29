<?php
require ("../includes/header_start.php");

$start_qry = $dbconn->query("SELECT * FROM op_audit_trail");

while($start = $start_qry->fetch_assoc()) {
    $changed = json_decode($start['changed'], true);

    if(!empty($changed['Start Time']))
        $dbconn->query("UPDATE op_audit_trail SET start_time = '{$changed['Start Time']}' WHERE id = '{$start['id']}'");
}

$resumed_qry = $dbconn->query("SELECT * FROM op_audit_trail");

while($resumed = $resumed_qry->fetch_assoc()) {
    $changed = json_decode($resumed['changed'], true);

    if(!empty($changed['Resumed Time']))
        $dbconn->query("UPDATE op_audit_trail SET start_time = '{$changed['Resumed Time']}' WHERE id = '{$resumed['id']}'");
}

$ended_qry = $dbconn->query("SELECT * FROM op_audit_trail");

while($ended = $ended_qry->fetch_assoc()) {
    $changed = json_decode($ended['changed'], true);

    if(!empty($changed['End time']))
        $dbconn->query("UPDATE op_audit_trail SET end_time = '{$changed['End time']}' WHERE id = '{$ended['id']}'");
}

echo "Migration complete.";