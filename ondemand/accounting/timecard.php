<?php
require_once ("../../includes/header_start.php");

$action = $_REQUEST['action'];

switch($action) {
    case 'report':
        $daterange = sanitizeInput($_REQUEST['dates']);
        $user_id = sanitizeInput($_REQUEST['employee']);

        $user_name = $dbconn->query("SELECT name FROM user WHERE id = '$user_id'");
        $username = $user_name->fetch_assoc();
        $username = $username['name'];

        echo "<h3>$username</h3><br />";

        $dates = explode(" - ", $daterange);

        $start_date = strtotime($dates[0]);
        $end_date = strtotime($dates[1] . "+ 24 hours");
        $requested_days = ($end_date - $start_date) / 86400;

        for($i = 0; $i < $requested_days; $i++) { // for each day
            $start_date_qry = strtotime($dates[0] . " + $i days");

            $end_day_count = $i + 1;
            $end_date_qry = strtotime($dates[0] . " + $end_day_count days");

            $op_audit_qry = $dbconn->query("SELECT op_audit_trail.op_id AS auditOPID, op_audit_trail.*, op_queue.*, operations.* 
              FROM op_audit_trail JOIN op_queue ON op_audit_trail.op_id = op_queue.id JOIN operations ON op_queue.operation_id = operations.id
              WHERE op_audit_trail.shop_id = '$user_id' AND op_queue.start_time > '$start_date_qry' AND op_queue.end_time < '$end_date_qry' ORDER BY op_audit_trail.timestamp ASC LIMIT 0,1");

            $current_day = date('l, n/j/y', $start_date_qry);

            // echo today's date and the header, we're going to show reports on each day
            echo <<<HEREDOC
                            <h4>$current_day</h4>
                
                            <table class="tablesaw table">
                                <thead>
                                <tr>
                                    <th scope="col" data-tablesaw-priority="persist" style="width: 5%;">SO #</th>
                                    <th scope="col" style="width: 10%;">Department</th>
                                    <th scope="col" style="width: 30%;">Operation</th>
                                    <th scope="col" style="width: 10%;">Started At</th>
                                    <th scope="col" style="width: 10%;">Ended At</th>
                                    <th scope="col" style="width: 5%;">Rework</th>
                                    <th scope="col" style="width: 5%;">Length Worked</th>
                                    <th scope="col" style="width: 5%;">Partial Completion</th>
                                </tr>
                                </thead>
                                <tbody id="timecard_audit_table">
HEREDOC;

            if($op_audit_qry->num_rows > 0) {
                $current_day = '';

                while($op_audit = $op_audit_qry->fetch_assoc()) {
                    $shift['day_start'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 6AM");
                    $shift['day_end'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 4:30PM");

                    $shift['lunch_start'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 11:30AM");
                    $shift['lunch_end'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 12PM");

                    $shift['break1_start'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 9:15AM");
                    $shift['break1_end'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 9:30AM");

                    $shift['break2_start'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 1:15PM");
                    $shift['break2_end'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 1:30PM");

                    $shift['next_day_start'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " tomorrow 6AM");
                    $shift['next_day_end'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " tomorrow 4:30PM");

                    $shift['fri_end'] = strtotime(date(DATE_DEFAULT, $op_audit['timestamp']) . " 12:15PM");

                    //if(date('l', $op_audit['timestamp']) !== 'Friday') { // monday through sunday excluding friday
                        // first, grab the current day we're working with
                        $current_day = date(DATE_DEFAULT, $op_audit['timestamp']); // 5/2/17 for instance

                        $start_limit = strtotime($current_day); // define constraints on the query, this is going to be 5/2/17 MIDNIGHT
                        $end_limit = strtotime($current_day . " +24 hours"); // this is going to be 5/3/17 MIDNIGHT

                        // now we grab all operations that fit WITHIN that time period
                        $day_qry = $dbconn->query("SELECT op_audit_trail.op_id AS auditOPID, op_audit_trail.*, op_queue.*, operations.* 
                      FROM op_audit_trail 
                      JOIN op_queue ON op_audit_trail.op_id = op_queue.id 
                      JOIN operations ON op_queue.operation_id = operations.id
                      WHERE op_audit_trail.shop_id = '$user_id' AND op_queue.start_time >= '$start_limit' AND op_queue.end_time <= '$end_limit' 
                      ORDER BY op_audit_trail.timestamp ASC");

                        if($day_qry->num_rows > 0) { // there are operations available for that day
                            $current_op_id = '';
                            $previous_op_id = '';
                            $total_length_worked = 0;

                            while($today = $day_qry->fetch_assoc()) { // for each operation lets group them together
                                $current_op_id = $today['auditOPID']; // store the current operation that we're working with

                                if($current_op_id !== $previous_op_id) { // if the current operation doesn't match the one we just worked on
                                    $previous_op_id = $current_op_id; // this is a new "line" in the log and we need to update the previous op

                                    $started = date(TIME_ONLY, $today['start_time']);
                                    $ended = date(TIME_ONLY, $today['end_time']);

                                    $rework = ((bool)$today['rework']) ? "Yes" : "No";

                                    $changed_info = json_decode($today['changed'], true);

                                    if(!empty($changed_info['Notes'])) {
                                        $notes = "<tr><td colspan='7'>{$changed_info['Notes']}</td></tr>";
                                    }

                                    if(!empty($changed_info['Resumed Time'])) {
                                        $resumed_at = date(DATE_TIME_ABBRV, $changed_info['Resumed Time']);
                                    } else {
                                        $resumed_at = null;
                                    }

                                    $partial_compl = ((bool)$changed_info['Partially Completed']) ? "Yes" : "No";

                                    $hours_worked = floor(($today['end_time'] - $today['start_time']) / 3600);

                                    $total_length_worked += $today['end_time'] - $today['start_time'];

                                    $mins_remainder = (($today['end_time'] - $today['start_time']) % 3600);

                                    $mins_readable = floor($mins_remainder / 60);

                                    if(strlen($mins_readable) === 1) {
                                        $mins_readable = "0" . $mins_readable;
                                    }

                                    $length_worked = "$hours_worked:$mins_readable";

                                    echo "<tr>";
                                    echo "  <td class='tablesaw-cell-persist'>{$today['so_parent']}-{$today['room']}</td>";
                                    echo "  <td>{$today['responsible_dept']}</td>";
                                    echo "  <td>{$today['op_id']}: {$today['job_title']}</td>";
                                    echo "  <td>$started</td>";
                                    echo "  <td>$ended</td>";
                                    echo "  <td>$rework</td>";
                                    echo "  <td>$length_worked</td>";
                                    echo "  <td>$partial_compl</td>";
                                    echo "</tr>";


                                } else { // this is the next line "finished" with notes
                                    $changed_info = json_decode($today['changed'], true);

                                    if(!empty($changed_info['Notes'])) {
                                        $notes = "{$changed_info['Notes']}";
                                    }

                                    if(!empty($changed_info['Resumed Time'])) {
                                        $resumed_at = date(DATE_TIME_ABBRV, $changed_info['Resumed Time']);
                                    } else {
                                        $resumed_at = null;
                                    }

                                    echo "<tr>";
                                    echo "  <td>&nbsp;</td>";
                                    echo "  <td>&nbsp;</td>";
                                    echo "  <td colspan='5'>$notes</td>";
                                    echo "</tr>";
                                }
                            }
                        }


                        echo "<tr>";
                        echo "<td colspan='8' style='height: 4px; background-color: #132882;'></td>";
                        echo "</tr>";
                        echo "<tr>";
                        echo "<td colspan='6'>&nbsp;</td>";

                        $total_length_worked_hours = floor($total_length_worked / 3600);
                        $total_length_worked_mins = floor(($total_length_worked % 3600) / 60);

                        $total_length_worked_mins = (strlen($total_length_worked_mins) === 1) ? "0$total_length_worked_mins" : $total_length_worked_mins;

                        echo "<td>Total: $total_length_worked_hours:$total_length_worked_mins</td>";
                        echo "<td>&nbsp;</td>";
                        echo "</tr>";
                    //} // else it's friday!
                }
            }  else { // they didn't work at all, show nothing
                echo "<tr>";
                echo "<td colspan='8'>No work logged for this day.</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td colspan='8' style='height: 4px; background-color: #132882;'></td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
        } // end for loop

        break;
    default:
        echo "<tr>";
        echo "  <td colspan='7'>Nothing to report.</td>";
        echo "</tr>";

        break;
}