<?php
require_once ("../../includes/header_start.php");

$action = $_REQUEST['action'];

switch($action) {
    case 'report':
        $daterange = sanitizeInput($_POST['dates']);
        $user_id = sanitizeInput($_POST['employee']);

        $dates = explode(" - ", $daterange);

        $start_date = strtotime($dates[0]);
        $end_date = strtotime($dates[1]);

        $op_audit_qry = $dbconn->query("SELECT * FROM op_audit_trail WHERE shop_id = '$user_id' AND timestamp >= '$start_date' AND timestamp <= '$end_date' ORDER BY timestamp ASC");

        if($op_audit_qry->num_rows > 0) {
            $current_date = '';

            while($op_audit = $op_audit_qry->fetch_assoc()) {
                $op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '{$op_audit['op_id']}'");
                $op_queue = $op_queue_qry->fetch_assoc();

                $operation_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$op_queue['operation_id']}'");
                $operation = $operation_qry->fetch_assoc();

                if(date('j', $op_queue['end_time']) !== $current_date) {
                    $current_date = date('j', $op_queue['end_time']);

                    $formatted_date = date(DATE_DEFAULT, $op_queue['end_time']);

                    echo "</tbody>";
                    echo "</table>";

                    echo <<<HEREDOC
                        <h4>$formatted_date</h4>
            
                        <table class="tablesaw table">
                            <thead>
                            <tr>
                                <th scope="col" data-tablesaw-priority="persist" style="width: 10%;">SO #</th>
                                <th scope="col" style="width: 10%;">Department</th>
                                <th scope="col" style="width: 30%;">Operation</th>
                                <th scope="col" style="width: 13%;">Released</th>
                                <th scope="col" style="width: 13%;">Started At</th>
                                <th scope="col" style="width: 13%;">Ended At</th>
                                <th scope="col" style="width: 10%;">Rework</th>
                            </tr>
                            </thead>
                            <tbody id="timecard_audit_table">
HEREDOC;
                }

                $released = date(DATE_TIME_ABBRV, $op_queue['created']);
                $started = date(DATE_TIME_ABBRV, $op_queue['start_time']);
                $ended = date(DATE_TIME_ABBRV, $op_queue['end_time']);

                $rework = ((bool)$op_queue['rework']) ? "Yes" : "No";

                echo "<tr>";
                echo "  <td class='tablesaw-cell-persist'>{$op_queue['so_parent']}-{$op_queue['room']}</td>";
                echo "  <td>{$operation['responsible_dept']}</td>";
                echo "  <td>{$operation['op_id']}: {$operation['job_title']}</td>";
                echo "  <td>$released</td>";
                echo "  <td>$started</td>";
                echo "  <td>$ended</td>";
                echo "  <td>$rework</td>";
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