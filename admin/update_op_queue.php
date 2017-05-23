<?php
require_once ("../includes/header_start.php");

if($_GET['action'] === 'create_cols') {
    $dbconn->query("ALTER TABLE devsmc.op_queue ADD active_employees VARCHAR(255) NULL");
    $dbconn->query("ALTER TABLE devsmc.op_queue ADD started_by INT(11) NULL");
    $dbconn->query("ALTER TABLE devsmc.op_queue ADD completed_by INT(11) NULL");
}

$op_queue_qry = $dbconn->query("SELECT * FROM op_queue");

if($op_queue_qry->num_rows > 0) {
    while($op_queue = $op_queue_qry->fetch_assoc()) {
        if($op_queue['started_by'] === null) {
            echo "Starting update for ID: {$op_queue['id']}<br />";

            $op_audit_qry = $dbconn->query("SELECT * FROM op_audit_trail WHERE op_id = '{$op_queue['id']}'");

            if($op_audit_qry->num_rows > 0) {
                $last_edited = '';

                while($op_audit = $op_audit_qry->fetch_assoc()) {
                    echo "Found audit query ID: {$op_audit['id']}<br/>";

                    if($last_edited !== $op_audit['shop_id']) {
                        echo "Updated last edited by ID to: {$op_audit['shop_id']}<br />";
                        $last_edited = $op_audit['shop_id'];
                    }
                }

                $user_qry = $dbconn->query("SELECT * FROM user WHERE id = '$last_edited'");
                $user = $user_qry->fetch_assoc();

                echo "Located user information: {$user['name']}<br/>";

                echo "Presuming the following:<br/> --- Started by is the same as completed by<br /> --- Active Employees is presently one<br />";

                if($op_queue['end_time'] !== null) {
                    echo "Found an end time for this job! End time: " . date(DATE_TIME_ABBRV, $op_queue['end_time']) . "<br />";

                    $dbconn->query("UPDATE op_queue SET started_by = '$last_edited', completed_by = '$last_edited' WHERE id = '{$op_queue['id']}'");

                    echo "Updated operation {$op_queue['id']} with Started By as $last_edited, Completed By as $last_edited, and no active employees.<br />";
                } elseif($op_queue['start_time'] !== null) {
                    echo "Did not find an end time for this job but I found a start time! Start time: " . date(DATE_TIME_ABBRV, $op_queue['start_time']) . "<br />";
                    echo "Since there is a start time but no end time, we're updating the started by and the active employee list...<br />";

                    $active_emp = json_encode($last_edited);

                    $dbconn->query("UPDATE op_queue SET started_by = '$last_edited', active_employees = '$active_emp' WHERE id = '{$op_queue['id']}'");

                    echo "Updated operation {$op_queue['id']} with Started By as $last_edited and active employees as $active_emp.<br />";
                } else {
                    echo "Did not find a start or end time for this job, leaving it unassigned and not updating any information!<br />";
                }
            } else {
                echo "Unable to locate any audit trail for operation - no action taken<br/>";
            }
        } else {
            echo "Skipping update for ID: {$op_queue['id']} as it's already set<br />";
        }

        echo "<hr />";
    }
}