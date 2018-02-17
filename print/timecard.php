<?php
require '../includes/header_start.php';

//outputPHPErrs();

$employee = sanitizeInput($_REQUEST['employee']);
$start_date = (!empty($_REQUEST['start_date'])) ? sanitizeInput($_REQUEST['start_date']) : time();
$end_date = (!empty($_REQUEST['start_date'])) ? sanitizeInput($_REQUEST['end_date']) : time();

$user_qry = $dbconn->query("SELECT * FROM user WHERE id = $employee");

if($user_qry->num_rows > 0) {
    $user = $user_qry->fetch_assoc();
}
?>

<html>
<head>
    <link href="css/timecard.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

    <link href="/includes/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
</head>

<!--<body onload="printMe()">-->
<body>

<div id="wrapper">
    <div id="pre-header" class="no-print">
        <table>
            <tr>
                <td>
                    <label for="employee_select">Employee: </label>
                    <select name="employee_select" id="employee_select" class="form-control">
                        <?php
                        $emp_qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE ORDER BY name ASC");

                        if($emp_qry->num_rows > 0) {
                            echo "<optgroup label='Active'>";

                            while($employee_select = $emp_qry->fetch_assoc()) {
                                $selected = ($_REQUEST['employee'] === $employee_select['id']) ? "selected" : null;
                                echo "<option value='{$employee_select['id']}' $selected>{$employee_select['name']}</option>";
                            }

                            echo "</optgroup>";
                        }

                        $emp_qry = $dbconn->query("SELECT * FROM user WHERE account_status = FALSE ORDER BY name ASC");

                        if($emp_qry->num_rows > 0) {
                            echo "<optgroup label='Inactive'>";

                            while($employee_select = $emp_qry->fetch_assoc()) {
                                $selected = ($_REQUEST['employee'] === $employee_select['id']) ? "selected" : null;
                                echo "<option value='{$employee_select['id']}' $selected>{$employee_select['name']}</option>";
                            }

                            echo "</optgroup>";
                        }
                        ?>
                    </select>
                </td>
                <td style="width: 20px;">&nbsp;</td>
                <td><label for="from_date">Date: </label><input type="text" name="from_date" id="from_date" value="<?php echo date(DATE_DEFAULT, $_REQUEST['start_date']); ?>" /></td>
                <td><label for="to_date"> to </label><input type="text" name="to_date" id="to_date" value="<?php echo date(DATE_DEFAULT, $_REQUEST['end_date']); ?>" /></td>
            </tr>
        </table>
    </div>

    <div id="header_container">
        <div id="header_left">
            <div id="page_type">
                <table>
                    <tr>
                        <td colspan="2" id="page_type_header">Timecard Report</td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="logo_container">
            <div id="logo"><img src="/assets/images/smc_logo.png" width="170px" /></div>

            <div id="company_info">
                Stone Mountain Cabinetry, Inc.<br />
                206 Vista Blvd<br/>
                Arden, NC 28704<br />
                828.966.9000<br/>
                orders@smcm.us
            </div>
        </div>

        <div id="header_right">
            <div id="page_info">
                <table>
                    <tr>
                        <td width="80px">Employee:</td>
                        <td><?php echo $user['name']; ?></td>
                    </tr>
                    <tr>
                        <td>Date Range:</td>
                        <td><?php echo date(DATE_DEFAULT, $start_date) . " - " . date(DATE_DEFAULT, $end_date); ?></td>
                    </tr>
                    <tr>
                        <td>Printed:</td>
                        <td><?php echo date(DATE_DEFAULT); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="clearfix"></div>
    </div>

    <div id="main_section">
        <table>
            <?php
                $total_days = ($end_date - $start_date) / 86400;
                $current_day = $start_date;
                $prev_op_id = null;
                $audit_id = null;

                $week_total = 0;

                for($i = 0; $i <= $total_days; $i++) {
                    $next_day = (int)$current_day + 86400;

                    if(date("N", $current_day) < 7) { // if it's not sunday
                        echo "<tr><th colspan='6'><h4>" . date("l (" . DATE_DEFAULT . ")", $current_day) . "</h4></th></tr>"; // format the date with DAY (DATE)

                        // grab all data from the audit trail for that day
                        $day_qry = $dbconn->query("SELECT op_audit_trail.op_id AS auditOPID, operations.op_id AS opID, op_audit_trail.id AS oID, op_queue.id AS queueID, 
                             op_queue.*, rooms.*, operations.*, op_audit_trail.* FROM op_audit_trail 
                            LEFT JOIN op_queue ON op_audit_trail.op_id = op_queue.id
                            LEFT JOIN rooms ON op_queue.room_id = rooms.id
                            LEFT JOIN operations ON op_queue.operation_id = operations.id
                            WHERE op_audit_trail.timestamp BETWEEN $current_day AND $next_day AND op_audit_trail.shop_id = $employee AND op_audit_trail.start_time IS NOT NULL
                            ORDER BY op_audit_trail.start_time ASC");

                        echo "<tr><th>SO #</th><th>Department</th><th>Operation</th><th>Started</th><th>Ended</th><th>Length Worked</th></tr>";
                        echo "<tr style='height:4px;' class='excluded_bg'><td></td></tr>";

                        // if there is information for that day
                        if($day_qry->num_rows > 0) {
                            while($line = $day_qry->fetch_assoc()) { // grab that information and begin working through it
                                if($audit_id != $line['auditOPID']) { // if the current line does not match the previous line
                                    $started = null; // we're starting with fresh start time
                                    $ended = null; // fresh end time
                                    $notes = null;

                                    // ordering that operation by start time null first, then non-null causing things to be out of order
                                    $start_end_qry = $dbconn->query("SELECT * FROM op_audit_trail WHERE op_id = {$line['op_id']} AND timestamp BETWEEN $current_day AND $next_day ORDER BY start_time ASC");

                                    // for each operation that's related to that audit id
                                    while($start_end = $start_end_qry->fetch_assoc()) {
                                        $changed_array = json_decode($start_end['changed'], true);

                                        if(!empty($changed_array['Start Time'])) {
                                            $started = $changed_array['Start Time'];
                                        } elseif(!empty($changed_array['Resumed Time'])) {
                                            $started = $changed_array['Resumed Time'];
                                        }

                                        if(!empty($changed_array['End time'])) {
                                            $ended = $changed_array['End time'];
                                        }
                                    }

                                    if($line['opID'] === '000') {
                                        $addl_op = "({$line['subtask']})";
                                    } else {
                                        $addl_op = null;
                                    }

                                    if(!empty($line['so_parent'])) {
                                        $so = "{$line['so_parent']}{$line['room']}-{$line['iteration']}";
                                        $notes = null;
                                    } else {
                                        $so = "Non-Billable";

                                        $notes_qry = $dbconn->query("SELECT * FROM notes WHERE note_type = 'op_note' AND type_id = '{$line['queueID']}'");

                                        if($notes_qry->num_rows > 0) {
                                            while($note_result = $notes_qry->fetch_assoc()) {
                                                $notes .= "<br />{$note_result['note']}";
                                            }
                                        }
                                    }

                                    $shift['break1_start'] = strtotime(date(DATE_DEFAULT, $line['timestamp']) . " 9:15AM");
                                    $shift['break1_end'] = strtotime(date(DATE_DEFAULT, $line['timestamp']) . " 9:30AM");

                                    $shift['lunch_start'] = strtotime(date(DATE_DEFAULT, $line['timestamp']) . " 11:30AM");
                                    $shift['lunch_end'] = strtotime(date(DATE_DEFAULT, $line['timestamp']) . " 12PM");

                                    $shift['break2_start'] = strtotime(date(DATE_DEFAULT, $line['timestamp']) . " 2:15PM");
                                    $shift['break2_end'] = strtotime(date(DATE_DEFAULT, $line['timestamp']) . " 2:30PM");

                                    $shift['fri_end'] = strtotime(date(DATE_DEFAULT, $line['timestamp']) . " 12:15PM");

                                    $deduction = 0;

                                    if($started <= $shift['break1_start']) { // it started before break 1 started
                                        if($ended >= $shift['break1_end']) { // and it ended AFTER break 1 was over
                                            $deduction += 900; // deduct the full amount of break from it, 15 minutes worth or 900 seconds
                                        } elseif($ended >= $shift['break1_start'] && $ended <= $shift['break1_end']) { // it ended DURING break...
                                            $deduction += $shift['break1_end'] - $ended; // calculate the amount of break to remove
                                        }
                                    } elseif($started >= $shift['break1_start'] && $started <= $shift['break1_end']) {
                                        $deduction += $shift['break1_end'] - $started;
                                    }

                                    if($started <= $shift['lunch_start']) { // it started before break 1 started
                                        if($ended >= $shift['lunch_end']) { // and it ended AFTER break 1 was over
                                            $deduction += 1800; // deduct the full amount of break from it, 15 minutes worth or 900 seconds
                                        } elseif($ended >= $shift['lunch_start'] && $ended <= $shift['lunch_end']) { // it ended DURING break...
                                            $deduction += $shift['lunch_end'] - $ended; // calculate the amount of break to remove
                                        }
                                    } elseif($started >= $shift['lunch_start'] && $started <= $shift['lunch_end']) { // if it started between lunch
                                        $deduction += $shift['lunch_end'] - $started; // remove the mid-range of lunch
                                    }

                                    if($started <= $shift['break2_start']) { // it started before break 1 started
                                        if($ended >= $shift['break2_end']) { // and it ended AFTER break 1 was over
                                            $deduction += 900; // deduct the full amount of break from it, 15 minutes worth or 900 seconds
                                        } elseif($ended >= $shift['break2_start'] && $ended <= $shift['break2_end']) { // it ended DURING break...
                                            $deduction += $shift['break2_end'] - $ended; // calculate the amount of break to remove
                                        }
                                    } elseif($started >= $shift['break2_start'] && $started <= $shift['break2_end']) {
                                        $deduction += $shift['break2_end'] - $started;
                                    }

                                    if(!empty($ended) && !empty($started)) {
                                        if(($ended - $started) > $deduction) {
                                            $total_worked = ($ended - $started) - $deduction;
                                        } else {
                                            $total_worked = 0;
                                        }

                                        $hours_worked = floor($total_worked / 3600);
                                        $mins_remainder = ($total_worked % 3600);
                                        $mins_readable = floor($mins_remainder / 60);

                                        if(strlen($mins_readable) === 1) {
                                            $mins_readable = "0" . $mins_readable;
                                        }

                                        $length_worked = "$hours_worked:$mins_readable";
                                    } else {
                                        $length_worked = "0:00";
                                    }

                                    $started_readable = date(TIME_ONLY, $started);
                                    $ended_readable = (!empty($ended)) ? date(TIME_ONLY, $ended) : "N/A";

                                    echo "<tr>";
                                    echo "<td>$so</td>";
                                    echo "<td>{$line['responsible_dept']}</td>";
                                    echo "<td>{$line['opID']}: {$line['job_title']} $addl_op $notes</td>";
                                    echo "<td>$started_readable</td>";
                                    echo "<td>$ended_readable</td>";
                                    echo "<td>$length_worked</td>";
                                    echo "</tr>";

                                    $audit_id = $line['auditOPID'];
                                }
                            }

                            $clocked_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '$employee' AND time_in BETWEEN $current_day AND $next_day ORDER BY time_in ASC");

                            $time_in = '';
                            $time_out = '';

                            if($clocked_qry->num_rows > 0) {
                                while($clocked = $clocked_qry->fetch_assoc()) {
                                    $time_in = $clocked['time_in'];
                                    $time_out = $clocked['time_out'];

                                    $time_in_human = date(TIME_ONLY, $clocked['time_in']);
                                    $time_out_human = (!empty($clocked['time_out'])) ? date(TIME_ONLY, $clocked['time_out']) : "N/A";
                                }
                            }


                            if($time_out_human !== 'N/A') {
                                $total_length_worked = $time_out - $time_in;

                                if($total_length_worked >= 35100) {
                                    // deduct 1 hour for 2 15 min breaks and 1 30 min lunch
                                    $total_length_worked -= 3600;
                                } elseif($total_length_worked <= 26100 && $total_length_worked >= 20700) {
                                    // deduct 45 minutes for 1 15 min break and 1 30 min lunch
                                    $total_length_worked -= 2700;
                                } elseif($total_length_worked <= 20700 && $total_length_worked >= 9000) {
                                    $total_length_worked -= 1800;
                                }

                                $week_total += $total_length_worked;

                                $total_length_worked_hours = floor($total_length_worked / 3600);
                                $total_length_worked_mins = floor(($total_length_worked % 3600) / 60);

                                $total_length_worked_mins = (strlen($total_length_worked_mins) === 1) ? "0$total_length_worked_mins" : $total_length_worked_mins;

                                $length_worked_output = "$total_length_worked_hours:$total_length_worked_mins";
                            } else {
                                $length_worked_output = "N/A";
                            }

                            echo "<tr class='excluded_bg' style='height:4px;'><td colspan='6'></td></tr>";
                            echo "<tr class='excluded_bg'><td colspan='5'>Clocked In: $time_in_human / Clocked Out: $time_out_human</td><td>$length_worked_output</td></tr>";
                        } else {
                            echo "<tr class='excluded_bg'><td colspan='6'><h4>Nothing to report</h4></td></tr>";

                            $timecard_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '$employee' AND time_in BETWEEN $current_day AND $next_day");

                            if($timecard_qry->num_rows > 0) {
                                echo "<tr class='excluded_bg' style='height:4px;'><td colspan='6'></td></tr>";

                                while($timecard = $timecard_qry->fetch_assoc()) {
                                    $time_in = $timecard['time_in'];
                                    $time_out = $timecard['time_out'];

                                    $time_in_human = date(TIME_ONLY, $time_in);
                                    $time_out_human = (!empty($timecard['time_out'])) ? date(TIME_ONLY, $timecard['time_out']) : "N/A";

                                    if($time_out_human !== 'N/A') {
                                        $total_length_worked = $time_out - $time_in;

                                        if($total_length_worked >= 35100) {
                                            // deduct 1 hour for 2 15 min breaks and 1 30 min lunch
                                            $total_length_worked -= 3600;
                                        } elseif($total_length_worked <= 26100 && $total_length_worked >= 20700) {
                                            // deduct 45 minutes for 1 15 min break and 1 30 min lunch
                                            $total_length_worked -= 2700;
                                        } elseif($total_length_worked <= 20700 && $total_length_worked >= 9000) {
                                            $total_length_worked -= 1800;
                                        }

                                        $week_total += $total_length_worked;

                                        $total_length_worked_hours = floor($total_length_worked / 3600);
                                        $total_length_worked_mins = floor(($total_length_worked % 3600) / 60);

                                        $total_length_worked_mins = (strlen($total_length_worked_mins) === 1) ? "0$total_length_worked_mins" : $total_length_worked_mins;

                                        $length_worked_output = "$total_length_worked_hours:$total_length_worked_mins";
                                    } else {
                                        $length_worked_output = "N/A";
                                    }

                                    echo "<tr class='excluded_bg'><td colspan='5'>Clocked In: $time_in_human / Clocked Out: $time_out_human</td><td>$length_worked_output</td></tr>";
                                }
                            }

                            $total_length_worked = '0:00';
                        }

                        echo "<tr class='excluded_bg' style='height:4px;'><td colspan='6'></td></tr>";
                    }

                    $current_day = $next_day;
                }

                echo "<tr class='excluded_bg' style='height:4px;'><td colspan='6'></td></tr>";
                echo "<tr class='excluded_bg' style='height:4px;'><td colspan='6'></td></tr>";

                $week_total_hours = floor($week_total / 3600);
                $week_total_mins = floor(($week_total % 3600) / 60);

                $week_total_mins = (strlen($week_total_mins) === 1) ? "0$week_total_mins" : $week_total_mins;

                echo "<tr class='excluded_bg' style='height:4px;'><td colspan='6' style='text-align:right;'>Week Total: $week_total_hours:$week_total_mins</td></tr>";
            ?>
        </table>
    </div>
</div>

<!-- JQuery & JQuery UI -->
<script src="/assets/js/jquery.min.js"></script>
<script src="/includes/js/jquery-ui.min.js"></script>

<script>
    $(function() {
        var sDate = "<?php echo $_REQUEST['start_date']; ?>";
        var eDate = "<?php echo $_REQUEST['end_date']; ?>";
        var emp = "<?php echo $_REQUEST['employee']; ?>";

        $("#employee_select").change(function() {
            emp = $(this).find(":selected").val();
            window.location.replace("timecard.php?start_date=" + sDate + "&end_date=" + eDate + "&employee=" + emp);
        });

        $("#from_date").datepicker({
            <?php
                $setDate = date("n/j/Y", $_REQUEST['start_date']);
                if(!empty($_REQUEST['start_date'])) echo "defaultDate: '$setDate'";
            ?>
        }).change(function() {
            sDate = Date.parse($("#from_date").val())/1000;

            window.location.replace("timecard.php?start_date=" + sDate + "&end_date=" + eDate + "&employee=" + emp);
        });

        $("#to_date").datepicker({
            <?php
            $setDate = date("n/j/Y", $_REQUEST['end_date']);
            if(!empty($_REQUEST['end_date'])) echo "defaultDate: '$setDate'";
            ?>
        }).change(function() {
            eDate = Date.parse($("#to_date").val())/1000;
            window.location.replace("timecard.php?start_date=" + sDate + "&end_date=" + eDate + "&employee=" + emp);
        });
    });
</script>

</body>
</html>