<?php
require_once ("../../includes/header_start.php");

$action = $_REQUEST['action'];

switch($action) {
    case 'report':
        $daterange = sanitizeInput($_REQUEST['dates']); // grab the dates that we're working with
        $user_id = sanitizeInput($_REQUEST['employee']); // figure out who we're trying to get information for

        $user_name = $dbconn->query("SELECT name FROM user WHERE id = '$user_id'"); // grab the user name specifically
        $username = $user_name->fetch_assoc(); // name continued
        $username = $username['name']; // username definition

        echo "<h3>$username</h3><br />"; // employee username

        $dates = explode(" - ", $daterange); // take the dates and blow them up hardcore like... into their separate dates

        $start_date = strtotime($dates[0]); // the first date is the start date
        $end_date = strtotime($dates[1] . "+ 24 hours"); // the second date is the start date + 24 hours
        $requested_days = ($end_date - $start_date) / 86400; // total number of requested days to give me a for loop

        for($i = 0; $i < $requested_days; $i++) { // for each day
            $line_opid = null;

            $start_date_qry = strtotime($dates[0] . " + $i days"); // start date for this query is the start time plus i amount of days
            $end_day_count = $i + 1; // increment the end day by one
            $end_date_qry = strtotime($dates[0] . " + $end_day_count days"); // end date is equal to the amount of END count days calculated above

            if(date('N', $start_date_qry) !== 6 && date('N', $start_date_qry) != 7 && date('N', $end_date_qry) !== 6 && date('N', $end_date_qry) != 7) { // if it's not Saturday or Sunday
                $current_day = date('l, n/j/y', $start_date_qry); // the day we're working with presently (directly related to display)

                $start_limit = strtotime($current_day); // define constraints on the query, this is going to be 5/2/17 MIDNIGHT
                $end_limit = strtotime($current_day . " +24 hours"); // this is going to be 5/3/17 MIDNIGHT

                // echo today's date and the header, we're going to show reports on each day
                echo <<<HEREDOC
                            <h4>$current_day</h4>
                
                            <table class="tablesaw table" width="100%">
                                <thead>
                                <tr>
                                    <th scope="col" data-tablesaw-priority="persist" style="width: 5%;">SO #</th>
                                    <th scope="col" style="width: 10%;">Department</th>
                                    <th scope="col" style="width: 20%;">Operation</th>
                                    <th scope="col" style="width: 40%;">Notes</th>
                                    <th scope="col" style="width: 10%;">Started At</th>
                                    <th scope="col" style="width: 10%;">Ended At</th>
                                    <th scope="col" style="width: 5%;">Rework</th>
                                    <th scope="col" style="width: 5%;">Length Worked</th>
                                </tr>
                                </thead>
                                <tbody id="timecard_audit_table">
HEREDOC;

                // giant query time! WAHOO!
                // get the op audit trail as well as the current operation queue and operations
                $op_audit_qry = $dbconn->query("SELECT op_audit_trail.op_id AS auditOPID, op_audit_trail.*, op_queue.*, operations.* 
              FROM op_audit_trail JOIN op_queue ON op_audit_trail.op_id = op_queue.id JOIN operations ON op_queue.operation_id = operations.id
              WHERE op_audit_trail.shop_id = '$user_id' AND op_queue.start_time > '$start_date_qry' AND op_queue.end_time < '$end_date_qry' ORDER BY op_audit_trail.timestamp ASC LIMIT 0,1");

                if($op_audit_qry->num_rows > 0) { // if there were any results associated with the operation query
                    $current_day = ''; // set the current day to null

                    while($op_audit = $op_audit_qry->fetch_assoc()) { // for each operation audit that we found within the time range
                        // This has to do with shift times which are not currently implemented. We want to determine what shifts are when and deduct time based on that.
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

                        $current_day = date(DATE_DEFAULT, $op_audit['timestamp']); // first, grab the current day we're working with

                        $start_limit = strtotime($current_day); // define constraints on the query, this is going to be START DAY MIDNIGHT
                        $end_limit = strtotime($current_day . " +24 hours"); // this is going to be NEXT DAY MIDNIGHT

                        // now we grab all operations that fit WITHIN that time period
                        $day_qry = $dbconn->query("SELECT * FROM op_audit_trail WHERE op_audit_trail.shop_id = '$user_id' AND op_audit_trail.timestamp >= '$start_limit' AND op_audit_trail.timestamp <= '$end_limit' GROUP BY op_id ORDER BY op_audit_trail.timestamp ASC");

                        if($day_qry->num_rows > 0) { // if there are operations available for that day
                            while($today = $day_qry->fetch_assoc()) { // for each operation for that day
                                $ind_qry = $dbconn->query("SELECT * FROM op_audit_trail WHERE op_audit_trail.op_id = '{$today['op_id']}' AND op_audit_trail.timestamp >= '$start_limit' AND op_audit_trail.timestamp <= '$end_limit' ORDER BY op_audit_trail.timestamp ASC;");

                                $started_at = '';
                                $ended_at = '';
                                $audit_ID = '';
                                $department = '';
                                $so = '';
                                $job_title = '';
                                $rework = 'No';
                                $length_worked = '';
                                $partial_completion = '';
                                $notes = '';

                                while($ind_op_line = $ind_qry->fetch_assoc()) { // for each operation associated with the ID
                                    // grab all important details that we need
                                    $op_line_qry = $dbconn->query("SELECT op_audit_trail.op_id AS auditOPID, op_audit_trail.id AS auditID, op_audit_trail.*, op_queue.*, operations.*
                                        FROM op_audit_trail
                                        JOIN op_queue ON op_audit_trail.op_id = op_queue.id
                                        JOIN operations ON op_queue.operation_id = operations.id
                                        WHERE op_audit_trail.op_id = {$today['op_id']} AND op_audit_trail.timestamp >= '$start_limit' AND op_audit_trail.timestamp <= '$end_limit'
                                        ORDER BY op_audit_trail.timestamp ASC");

                                    while($op_line = $op_line_qry->fetch_assoc()) {
                                        $changed = json_decode($op_line['changed'], true);

                                        // assign the time based on what's been changed
                                        if(!empty($changed['Start Time'])) {
                                            $started_at = $changed['Start Time'];
                                        } elseif(!empty($changed['Resumed Time'])) {
                                            $started_at = $changed['Resumed Time'];
                                        } elseif(!empty($changed['End time'])) {
                                            $ended_at = $changed['End time'];
                                        }

                                        // determine if it's a non-billable task or a normal task
                                        if((bool)$op_line['always_visible']) {
                                            $so = "N/A"; // non-billable SO#
                                            $job_title = "{$op_line['op_id']}: {$op_line['job_title']} - {$op_line['subtask']}"; // non-billable job title
                                        } else {
                                            $so = "{$op_line['so_parent']}-{$op_line['room']}"; // billable so
                                            $job_title = "{$op_line['op_id']}: {$op_line['job_title']}"; // billable title
                                        }

                                        if((bool)$changed['rework']) {
                                            if($rework !== "Yes") {
                                                $rework = "Yes";
                                            }
                                        }

                                        $notes_exploded = explode("<br />", $changed['Notes']);

                                        $notes = $notes_exploded[0];

                                        $audit_ID = $op_line['auditID'];
                                        $department = $op_line['responsible_dept'];
                                    }
                                }

                                $deduction = 0;

                                if($started_at <= $shift['break1_start']) { // it started before break 1 started
                                    if($ended_at >= $shift['break1_end']) { // and it ended AFTER break 1 was over
                                        $deduction += 900; // deduct the full amount of break from it, 15 minutes worth or 900 seconds
                                    } elseif($ended_at >= $shift['break1_start'] && $ended_at <= $shift['break1_end']) { // it ended DURING break...
                                        $deduction += $shift['break1_end'] - $ended_at; // calculate the amount of break to remove
                                    }
                                } elseif($started_at >= $shift['break1_start'] && $started_at <= $shift['break1_end']) {
                                    $deduction += $shift['break1_end'] - $started_at;
                                }

                                if($started_at <= $shift['lunch_start']) { // it started before break 1 started
                                    if($ended_at >= $shift['lunch_end']) { // and it ended AFTER break 1 was over
                                        $deduction += 1800; // deduct the full amount of break from it, 15 minutes worth or 900 seconds
                                    } elseif($ended_at >= $shift['lunch_start'] && $ended_at <= $shift['lunch_end']) { // it ended DURING break...
                                        $deduction += $shift['lunch_end'] - $ended_at; // calculate the amount of break to remove
                                    }
                                } elseif($started_at >= $shift['lunch_start'] && $started_at <= $shift['lunch_end']) { // if it started between lunch
                                    $deduction += $shift['lunch_end'] - $started_at; // remove the mid-range of lunch
                                }

                                if($started_at <= $shift['break2_start']) { // it started before break 1 started
                                    if($ended_at >= $shift['break2_end']) { // and it ended AFTER break 1 was over
                                        $deduction += 900; // deduct the full amount of break from it, 15 minutes worth or 900 seconds
                                    } elseif($ended_at >= $shift['break2_start'] && $ended_at <= $shift['break2_end']) { // it ended DURING break...
                                        $deduction += $shift['break2_end'] - $ended_at; // calculate the amount of break to remove
                                    }
                                } elseif($started_at >= $shift['break2_start'] && $started_at <= $shift['break2_end']) {
                                    $deduction += $shift['break2_end'] - $started_at;
                                }

                                if(!empty($ended_at) && !empty($started_at)) {
                                    $total_worked = ($ended_at - $started_at) - $deduction;

                                    $hours_worked = floor($total_worked / 3600);
                                    $mins_remainder = ($total_worked % 3600);
                                    $mins_readable = floor($mins_remainder / 60);

                                    if(strlen($mins_readable) === 1) {
                                        $mins_readable = "0" . $mins_readable;
                                    }

                                    $length_worked = "$hours_worked:$mins_readable";
                                }

                                $started_at = date(TIME_ONLY, $started_at);
                                $ended_at = date(TIME_ONLY, $ended_at);

                                // display the line information
                                echo "<tr>";
                                echo "<td>$so <!-- $audit_ID --></td>";
                                echo "<td>$department</td>";
                                echo "<td>$job_title</td>";
                                echo "<td>$notes</td>";
                                echo "<td>$started_at</td>";
                                echo "<td>$ended_at</td>";
                                echo "<td>$rework</td>";
                                echo "<td>$length_worked</td>";
                                echo "</tr>";
                            }
                        }


                        echo "<tr>";
                        echo "<td colspan='8' style='height: 4px; background-color: #132882;'></td>";
                        echo "</tr>";
                        echo "<tr>";

                        $clocked_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '$user_id' AND time_in > $start_limit AND time_out < $end_limit");

                        $time_in = '';
                        $time_out = '';

                        if($clocked_qry->num_rows > 0) {
                            while($clocked = $clocked_qry->fetch_assoc()) {
                                $time_in = date(TIME_ONLY, $clocked['time_in']);
                                $time_out = date(TIME_ONLY, $clocked['time_out']);

                                echo "<td colspan='5'>Clocked In: $time_in / Clocked Out: $time_out</td>";

                                $time_in = $clocked['time_in'];
                                $time_out = $clocked['time_out'];
                            }
                        } else {
                            echo "<td colspan='5'>No clock in/out time.</td>";
                        }



                        $total_length_worked = $time_out - $time_in;

                        $total_length_worked_hours = floor($total_length_worked / 3600);
                        $total_length_worked_mins = floor(($total_length_worked % 3600) / 60);

                        $total_length_worked_mins = (strlen($total_length_worked_mins) === 1) ? "0$total_length_worked_mins" : $total_length_worked_mins;

                        echo "<td colspan='3' class='text-md-right'>Total: $total_length_worked_hours:$total_length_worked_mins</td>";
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

                    $clocked_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '$user_id' AND time_in > $start_limit AND time_out < $end_limit");

                    if($clocked_qry->num_rows > 0) {
                        while($clocked = $clocked_qry->fetch_assoc()) {
                            $time_in = date(TIME_ONLY, $clocked['time_in']);
                            $time_out = date(TIME_ONLY, $clocked['time_out']);

                            echo "<tr><td colspan='6'>Clocked In: $time_in / Clocked Out: $time_out</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No clock in/out time.</td></tr>";
                    }
                }

                echo "</tbody>";
                echo "</table>";
            }
        } // end for loop

        break;
    default:
        echo "<tr>";
        echo "  <td colspan='8'>Nothing to report.</td>";
        echo "</tr>";

        break;
}