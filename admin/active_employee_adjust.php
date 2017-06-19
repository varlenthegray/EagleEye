<?php
require ("../includes/header_start.php");

function array_search_partial($arr, $keyword) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
}

$audit_trail_qry = $dbconn->query("SELECT * FROM op_audit_trail");

while($audit_trail = $audit_trail_qry->fetch_assoc()) {/*
    $changed = ltrim($audit_trail['changed'], "{");
    $changed = rtrim($changed, "}");

    $ch_array1 = explode(",", $changed);

    $active_position = array_search_partial($ch_array1, "Active Employees");

    if(!empty($active_position)) {
        $active_emp = explode(":", $ch_array1[$active_position]);

        $final = ltrim($active_emp[1], "\"");
        $final = rtrim($final, "\"");

        $active_emp[1] = $final;

        $active_emp = implode(":", $active_emp);

        $ch_array1[$active_position] = $active_emp;
    }

    $changed_array = implode(",", $ch_array1);

    $changed_array = "{" . $changed_array . "}";

    $new_changed = str_replace('$active_employees', '[]', $audit_trail['changed']);

    $dbconn->query("UPDATE op_audit_trail SET changed = '$changed_array', changed = '$new_changed' WHERE id = '{$audit_trail['id']}'");*/
}

echo "<h1>Completed.</h1>";

