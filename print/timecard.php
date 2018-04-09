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

function getHMM($duration) {
  $hours_worked = floor($duration / 3600);
  $mins_remainder = ($duration % 3600);
  $mins_readable = floor($mins_remainder / 60);

  if(strlen($mins_readable) === 1) {
    $mins_readable = "0" . $mins_readable;
  }

  return "$hours_worked:$mins_readable";
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
      $total_days = ($end_date - $start_date) / 86400; // this gets the total number of days between the period requested
      $current_day = $start_date; // current day that we're starting with

      $week_total = 0; // total amount of time for the week (in seconds)

      for($i = 0; $i <= $total_days; $i++) { // for each day between total days, increment
        $next_day = (int)$current_day + 86400; // the next day is the current day + 24 hours in seconds

        $day_total = 0; // the day total (in seconds)

        $day_readable = date("l (" . DATE_DEFAULT . ")", $current_day); // format the date with DAY (DATE)
        echo "<tr class='border_solid'><th colspan='10'><h4>$day_readable</h4></th></tr>"; // show the date with DAY (DATE)

        // fkn black magic query (just kidding, it is a nested select) - Credit Brandon Christensen
        /** mq = Main Query, sq = Sub Query */
        $audit_qry = $dbconn->query("SELECT mq.op_id, q.id AS queueID, q.subtask, r.so_parent, r.room, r.iteration, o.responsible_dept, o.op_id AS opID, o.job_title, o.id AS operationID, MAX(mq.start_time) AS c_start_time,
           (SELECT sq.end_time FROM op_audit_trail sq WHERE sq.end_time IS NOT NULL AND sq.end_time > MAX(mq.start_time) AND sq.op_id = mq.op_id ORDER BY sq.end_time ASC LIMIT 1) AS c_end_time
        FROM op_audit_trail mq
          LEFT JOIN op_queue q ON mq.op_id = q.id 
          LEFT JOIN rooms r ON q.room_id = r.id
          LEFT JOIN operations o ON q.operation_id = o.id
        WHERE mq.start_time IS NOT NULL
          AND shop_id = $employee
          AND timestamp BETWEEN $current_day AND $next_day
        GROUP BY mq.op_id, mq.start_time
        ORDER BY c_start_time ASC;");

        echo "<tr class='border_solid'><th>SO #</th><th>Department</th><th>Operation</th><th class='center_text'>Started</th><th class='center_text'>Ended</th><th class='center_text'>Op Time</th><th class='center_text'>N.P.</th><th class='center_text'>N.B.</th><th class='center_text'>B.</th><th class='center_text'>R.T.</th></tr>";
        echo "<tr style='height:4px;' class='excluded_bg'><td colspan='6'></td></tr>";

        // get the timecard data specifically for that day
        $clocked_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = '$employee' AND time_in BETWEEN $current_day AND $next_day ORDER BY time_in ASC");

        $time_in = 0; // set time in to 0 seconds
        $time_out = 0; // set time out to 0 seconds

        // if there is a timecard record for this day
        if($clocked_qry->num_rows > 0) {
          while($clocked = $clocked_qry->fetch_assoc()) { // for those records, we're going to record the time in and time out
            $time_in = $clocked['time_in'];
            $time_out = $clocked['time_out'];

            $time_in_human = date(TIME_ONLY, $clocked['time_in']);
            $time_out_human = (!empty($clocked['time_out'])) ? date(TIME_ONLY, $clocked['time_out']) : "N/A";
          }
        }

        // setup accounting totals
        $non_payable_total = 0;
        $non_billable_total = 0;
        $billable_total = 0;
        $running_total = 0;

        // if we have more than one result
        if($audit_qry->num_rows > 0) {
          while($audit = $audit_qry->fetch_assoc()) {
            $started = $audit['c_start_time']; // we're starting with the first row of the query
            $ended = $audit['c_end_time']; // first row's end time
            $ended_na = null; // this is the visible portion of the timecard (*), in this case we're using the true end time of the operation
            $notes = null; // fresh notes

            $op_times = array();

            // accounting individual lines
            $non_payable_time = 0;
            $non_billable_time = 0;
            $billable_time = 0;

            // if we're working with a non-billable item
            if($audit['op_id'] === 'NB00') {
              $addl_op = "({$audit['subtask']})"; // get the additional operation information
            } else { // otherwise, it's not non-billable
              $addl_op = null; // don't worry about additional notes
            }

            // if there is an SO for the operation
            if(!empty($audit['so_parent'])) {
              $so = "{$audit['so_parent']}{$audit['room']}-{$audit['iteration']}"; // set it as such
              $notes = null; // don't worry about notes
            } else { // otherwise, if there is no SO for the operation
              $so = "Non-Billable"; // set the SO as non-billable

              $notes_qry = $dbconn->query("SELECT * FROM notes WHERE note_type = 'op_note' AND type_id = '{$audit['queueID']}'"); // grab the notes

              if($notes_qry->num_rows > 0) { // if there were notes logged
                while($note_result = $notes_qry->fetch_assoc()) { // get those notes
                  $notes .= "<br />{$note_result['note']}"; // add those to the notes section (for every note recorded)
                }
              }
            }

            // if there is an end time
            if(empty($ended)) { // if there is no end time
              if(!empty($time_out)) { // and there is a clocked time out
                $ended = $time_out; // set the ended time to clocked out time
                $ended_na = "(*)"; // note it for the audit trail that this time was calculated by the system
              } else { // otherwise, if there is no clock out time
                $ended = time();  // the current time is the end time for the operation
                $ended_na = "(**)"; // note that the current time was used
              }
            }

            // qry fudge time
            $qry_start = $started - 3;
            $qry_end = $ended + 3;

            /** This is where the time split magic happens */
            $sub_audit_qry = $dbconn->query("SELECT mq.op_id, MAX(mq.start_time) AS c_start_time,
              (SELECT sq.end_time FROM op_audit_trail sq WHERE sq.end_time IS NOT NULL AND sq.end_time > MAX(mq.start_time) AND sq.op_id = mq.op_id ORDER BY sq.end_time ASC LIMIT 1) AS c_end_time
            FROM op_audit_trail mq WHERE mq.start_time IS NOT NULL
              AND shop_id = $employee AND timestamp BETWEEN $qry_start AND $qry_end GROUP BY mq.op_id, mq.start_time ORDER BY c_start_time ASC;");

            if($sub_audit_qry->num_rows > 0) {
              while($sub_audit = $sub_audit_qry->fetch_assoc()) {
                $op_times[] = ['op_id' => $sub_audit['op_id'], 'start_time' => $sub_audit['c_start_time'], 'end_time' => $sub_audit['c_end_time']];
              }

              # Mash all of the start and end times together in an array
              $all_times = array();
              foreach ($op_times as $time) {
                $all_times[] = $time['start_time'];
                $all_times[] = $time['end_time'];
              }
              # ...and sort it.
              sort($all_times);

              $all_times_count = count($all_times);
              $timeframes = array();
              for ($i = 0; $i < ($all_times_count - 1); $i++) {
                # Create timeframes based on the start/end times of all operations
                $timeframes[] = ['start_time' => $all_times[$i], 'end_time' => $all_times[$i + 1]];
              }

              foreach ($timeframes as $key => $timeframe) {
                # Calculate how many operations are "open" during each timeframe
                $open_ops = 0;
                foreach ($op_times as $time) {
                  # Check for open operations by comparing the start/end times of the timeframe and the operation
                  if ($time['start_time'] <= $timeframe['start_time'] && $time['end_time'] >= $timeframe['end_time']) {
                    # If the operation was open during the timeframe's window, add an open operation
                    $open_ops++;
                  }
                }
                # Save these values to the original timeframes array for later use
                $timeframes[$key]['open_ops'] = $open_ops;

                # Save the "split" time value based on how many operations were active at the time
                $timeframes[$key]['actual_time'] = ($timeframe['end_time'] - $timeframe['start_time']) / $open_ops;
              }

              # Debug value
              $time_split_total = 0;

              # Loop through every operation now and calculate its actual, properly split time
              foreach ($op_times as $time) {
                $total_time = 0;
                # For every operation, loop through all timeframes
                foreach ($timeframes as $timeframe) {
                  # Find which timeframes apply to this particular operation by checking the start and end times
                  if ($timeframe['start_time'] >= $time['start_time'] && $timeframe['end_time'] <= $time['end_time']) {
                    $count_timeframes = ((count($timeframes) - 1) > 0) ? count($timeframes) - 1 : 1;

                    # If this timeframe applies to this operation, add the timeframe's split time
                    $total_time += $timeframe['actual_time'] / $count_timeframes;
                  }
                }

                /*# Debug visualization info
                echo '<ul>';
                foreach ($timeframes as $key => $timeframe) {
                  $time = $timeframe['end_time'] - $timeframe['start_time'];
                  echo '<li>';
                  echo '<strong>Timeframe '.$key.'</strong>';
                  echo '<ul>';
                  echo '<li>Time Span: '.$timeframe['start_time'].' -> '.$timeframe['end_time'].'</li>';
                  echo '<li>Open Ops: '.$timeframe['open_ops'].'</li>';
                  echo '<li>Actual time: '.$time.' seconds</li>';
                  echo '<li>Split time: '.$timeframe['actual_time'].' seconds</li>';
                  echo '</ul>';
                  echo '</li>';
                }
                echo '</ul>';*/

                $time_split_total += $total_time;

                $timesplit_audit = "P";
              }
            } else {
              $timesplit_audit = "S";
            }
            /** End time split magic */

            $total_worked = $ended - $started;

            // this should no longer ever evaluate to 0:00, but just in case we'll leave it here as a red flag
            if(!empty($ended) && !empty($started)) {
              // for the day total, add up everything
              $day_total += $total_worked;

              // get the hours and minutes for the total worked
              $length_worked = getHMM($total_worked);

              // if the operation is break
              if((int)$audit['operationID'] === 201) {
                $non_payable_time = $time_split_total; // add the time to non-payable time
                $non_payable_total += $time_split_total; // add the time to non-payable time
              } elseif($audit['opID'] === 'NB00') { // if the operation is non-billable
                $non_billable_time = $time_split_total; // add the time to non-billable time
                $non_billable_total += $time_split_total; // add the time to non-billable time
              } else { // we've covered everything else, so now it's time to add the time to billable
                $billable_time = $time_split_total; // add the time to billable time
                $billable_total += $time_split_total; // add the time to billable time
              }

              $running_total += $time_split_total; // the running total can now be added in too
            } else {
              $length_worked = "<b style='color:red;'>** 0:00 **</b>"; // throw all sorts of bells and alerts that something went wrong
            }

            $started_readable = date(TIME_ONLY, $started); // when the operation started
            $ended_readable = (!empty($ended)) ? date(TIME_ONLY, $ended) : "<b style='color:red;'>** N/A**</b>"; // when the operation ended, should NEVER evaluate to N/A

            // subtract non-payable time from running total
            $running_total -= $non_payable_time;

            // time to do the tabulation for accounting
            $np_readable = getHMM($non_payable_time);
            $nb_readable = getHMM($non_billable_time);
            $billable_readable = getHMM($billable_time);
            $running_readable = getHMM($running_total);

            $temp_started = substr($started, -5, 5);
            $temp_ended = substr($ended, -5, 5);

            echo "<tr>";
            #echo "<td>{$audit['op_id']} - $so</td>";
            echo "<td>$so</td>";
            echo "<td>{$audit['responsible_dept']}</td>";
            echo "<td>{$audit['job_title']} $addl_op $notes</td>";
            #echo "<td class='center_text'>$started_readable<br />$temp_started</td>";
            echo "<td class='center_text'>$started_readable</td>";
            #echo "<td class='center_text'>$ended_readable <br />$temp_ended $ended_na</td>";
            echo "<td class='center_text'>$ended_readable $ended_na</td>";
            #echo "<td class='center_text'>$length_worked $timesplit_audit</td>";
            echo "<td class='center_text'>$length_worked</td>";
            echo "<td class='border_solid center_text'>$np_readable</td>";
            echo "<td class='border_solid center_text'>$nb_readable</td>";
            echo "<td class='border_solid center_text'>$billable_readable</td>";
            echo "<td class='border_solid center_text'>$running_readable</td>";
            echo "</tr>";
          }

          if($time_out_human !== 'N/A') {
            $total_length_worked = $time_out - $time_in;
            $week_total += $total_length_worked;

            $length_worked_output = getHMM($total_length_worked);
            $day_output = getHMM($day_total);
          } else {
            $length_worked_output = "N/A";
            $day_output = "N/A";
          }

          $np_total_readable = getHMM($non_payable_total);
          $nb_total_readable = getHMM($non_billable_total);
          $billable_total_readable = getHMM($billable_total);

          echo "<tr class='excluded_bg'><td colspan='5'></td><td style='text-align:right;'>Total:</td><td class='border_solid center_text'>$np_total_readable</td><td class='border_solid center_text'>$nb_total_readable</td><td class='border_solid center_text'>$billable_total_readable</td><td class='border_solid center_text' style='background-color:#000'></td></tr>";
          echo "<tr class='excluded_bg' style='height:12px;'><td colspan='6'></td></tr>";
          echo "<tr class='excluded_bg'><td colspan='6'>Clocked In: $time_in_human / Clocked Out: $time_out_human</td><td colspan='3'>Day Timecard Total:</td><td class='center_text'>$length_worked_output</td></tr>";
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

                $week_total += $total_length_worked;

                $length_worked_output = getHMM($total_length_worked);
              } else {
                $length_worked_output = "N/A";
              }

              echo "<tr class='excluded_bg'><td colspan='6'>Clocked In: $time_in_human / Clocked Out: $time_out_human</td><td colspan='3'>Day Timecard Total:</td><td>$length_worked_output</td></tr>";
            }
          }

          $total_length_worked = '0:00';
        }

        echo "<tr class='excluded_bg' style='height:4px;'><td colspan='6'></td></tr>";

        $current_day = $next_day;
      }

      echo "<tr class='excluded_bg' style='height:8px;'><td colspan='6'></td></tr>";

      $week_total_readable = getHMM($week_total);

      echo "<tr class='excluded_bg' style='height:4px;'><td colspan='6' style='text-align:right;'></td><td colspan='3'>Week Total:</td><td class='center_text'>$week_total_readable</td></tr>";
      ?>
    </table>

    <h4>
      (*) Indicates that the time was overridden and set to the clock out time due to there being no "end time" for that operation.<br />
      (**) Indicates that the time was overridden and set to the current time due to there being no "end time" for that operation or clock out time.
    </h4>
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