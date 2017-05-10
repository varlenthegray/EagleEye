<?php
require_once ("../../includes/header_start.php");

$action = $_REQUEST['action'];

switch($action) {
    case 'report':
        $daterange = sanitizeInput($_REQUEST['dates']);
        $user_id = sanitizeInput($_REQUEST['employee']);

        $dates = explode(" - ", $daterange);

        $start_date = strtotime($dates[0]);
        $end_date = strtotime($dates[1] . "+ 24 hours");

        $op_audit_qry = $dbconn->query("SELECT op_audit_trail.op_id AS auditOPID, op_audit_trail.*, op_queue.*, operations.* 
          FROM op_audit_trail JOIN op_queue ON op_audit_trail.op_id = op_queue.id JOIN operations ON op_queue.operation_id = operations.id
          WHERE op_audit_trail.shop_id = '$user_id' AND op_queue.start_time >= '$start_date' AND op_queue.end_time <= '$end_date' ORDER BY op_audit_trail.timestamp ASC");

        if($op_audit_qry->num_rows > 0) {
            $current_date = '';
            $partial_compl = '';

            while($op_audit = $op_audit_qry->fetch_assoc()) {
                $resumed_at = '';
                if(date('j', $op_audit['timestamp']) !== $current_date) {
                    $current_date = date('j', $op_audit['timestamp']);

                    $formatted_date = date(DATE_DEFAULT, $op_audit['timestamp']);

                    echo "</tbody>";
                    echo "</table>";

                    echo <<<HEREDOC
                        <h4>$formatted_date</h4>
            
                        <table class="tablesaw table">
                            <thead>
                            <tr>
                                <th scope="col" data-tablesaw-priority="persist" style="width: 5%;">SO #</th>
                                <th scope="col" style="width: 10%;">Department</th>
                                <th scope="col" style="width: 30%;">Operation</th>
                                <th scope="col" style="width: 10%;">Started At</th>
                                <th scope="col" style="width: 10%;">Ended At</th>
                                <th scope="col" style="width: 10%;">Resumed At</th>
                                <th scope="col" style="width: 5%;">Rework</th>
                                <th scope="col" style="width: 5%;">Length Worked</th>
                                <th scope="col" style="width: 5%;">Partial Completion</th>
                            </tr>
                            </thead>
                            <tbody id="timecard_audit_table">
HEREDOC;
                }

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

                if($op_audit['end_time'] > $shift['day_end']) { // if the end time is greater than today's end time
                    // we need to remove X amount of labor hours from the total, when did they start this operation next?
                    $ind_op_qry = $dbconn->query("SELECT * FROM op_audit_trail WHERE op_id = {$op_audit['auditOPID']} AND shop_id = '$user_id' AND timestamp >= '{$shift['day_end']}' AND timestamp <= '{$shift['next_day_end']}'");

                    if($ind_op_qry->num_rows === 1) {
                        while($ind_op = $ind_op_qry->fetch_assoc()) {
                            echo $ind_op['timestamp'] . "<br />";
                        }
                    } else {
                        echo "More than 1 found!";
                    }
                }

                $released = date(DATE_TIME_ABBRV, $op_audit['created']);
                $started = date(DATE_TIME_ABBRV, $op_audit['start_time']);
                $ended = date(DATE_TIME_ABBRV, $op_audit['end_time']);

                $rework = ((bool)$op_audit['rework']) ? "Yes" : "No";

                $changed_info = json_decode($op_audit['changed'], true);

                if(!empty($changed_info['Notes'])) {
                    $notes = "<tr><td colspan='7'>{$changed_info['Notes']}</td></tr>";
                }

                if(!empty($changed_info['Resumed Time'])) {
                    $resumed_at = date(DATE_TIME_ABBRV, $changed_info['Resumed Time']);
                }

                $partial_compl = ((bool)$changed_info['Partially Completed']) ? "Yes" : "No";

                $hours_worked = floor(($op_audit['end_time'] - $op_audit['start_time']) / 3600);

                $mins_remainder = (($op_audit['end_time'] - $op_audit['start_time']) % 3600);

                $mins_readable = floor($mins_remainder / 60);

                $length_worked = "$hours_worked hrs $mins_readable mins";

                echo "<tr>";
                echo "  <td class='tablesaw-cell-persist'>{$op_audit['so_parent']}-{$op_audit['room']}</td>";
                echo "  <td>{$op_audit['responsible_dept']}</td>";
                echo "  <td>{$op_audit['op_id']}: {$op_audit['job_title']}</td>";
                echo "  <td>$started</td>";
                echo "  <td>$ended</td>";
                echo "  <td>$resumed_at</td>";
                echo "  <td>$rework</td>";
                echo "  <td>$length_worked</td>";
                echo "  <td>$partial_compl</td>";
                echo "</tr>";
                echo $notes;
                echo "<tr>";
                echo "<td colspan='7' style='height: 4px; background-color: #CCCCCC;'></td>";
                echo "</tr>";
            }


            echo "</tbody>";
            echo "</table>";
        }

        break;
    default:
        echo "<tr>";
        echo "  <td colspan='7'>Nothing to report.</td>";
        echo "</tr>";

        break;
}