<?php
set_include_path("/home/trustedprogrammer/domains/smc.trustedprogrammer.com/public_html/includes/");
//set_include_path("/home/trustedprogrammer/domains/smc.trustedprogrammer.com/public_html/includes/");

// flip these around when pushing to prod

require ("config.php");

$user_qry = $dbconn->query("SELECT * FROM user WHERE auto_clock = TRUE");

if($user_qry->num_rows > 0) {
    while($user = $user_qry->fetch_assoc()) {
        // Execute auto-logoff in the system
        $timecards = $dbconn->query("SELECT * FROM timecards WHERE time_out IS NULL AND employee = '{$user['id']}'");
        $time_out = mktime(16, 45, 0);

        if($timecards->num_rows > 0) {
            while($card = $timecards->fetch_assoc()) {
                if($card['time_out'] === null) {
                    $dbconn->query("UPDATE timecards SET time_out = $time_out WHERE id = '{$card['id']}'");

                    $desc = json_encode(["Update"=>"Timecards","Time Out"=>$time_out,"ID"=>$card['id']]);

                    $dbconn->query("INSERT INTO log_cron (`desc`, time) VALUES ('$desc', UNIX_TIMESTAMP())");
                }
            }
        }

        $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE active = TRUE AND start_time IS NOT NULL AND active_employees LIKE '%\"{$user['id']}\"%'");

        if($op_queue_qry->num_rows > 0) {
            while($op_queue = $op_queue_qry->fetch_assoc()) {
                $aemp = json_decode($op_queue['active_employees']);

                if(count($aemp) > 1) {
                    $loc = array_search($user['id'], $aemp);
                    unset($aemp[$loc]);
                } else {
                    $aemp = array();
                }

                $active_employees = json_encode(array_values($aemp));

                $active = (empty($aemp)) ? 'FALSE' : 'TRUE';

                $dbconn->query("UPDATE op_queue SET active = $active, active_employees = '$active_employees', partially_completed = TRUE, end_time = UNIX_TIMESTAMP() WHERE id = '{$op_queue['id']}'");

                $changed = ["Active"=>FALSE,"Active Employees"=>$active_employees,"Partially Completed"=>TRUE,"End Time"=>time(),"Auto-Clock"=>TRUE,"ID"=>$op_queue['id']];
                $changed = json_encode($changed);

                $dbconn->query("INSERT INTO log_cron (`desc`, time) VALUES ('$changed', UNIX_TIMESTAMP())");

                $dbconn->query("INSERT INTO op_audit_trail (op_id, shop_id, changed, timestamp) VALUES ('{$op_queue['id']}', NULL, '$changed', UNIX_TIMESTAMP())");
            }
        }
    }
}