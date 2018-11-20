<?php
require '../../includes/header_start.php';

outputPHPErrs();

header('Content-Type:text/plain');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

$start = new DateTime('2018-01-01');
$end = new DateTime('now');

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($start, $interval, $end);

$i = 0;

foreach($period AS $day) {
  if($day->format('N') < 6) {
    $startTime = $day->format('U');
    $endTime = $day->format('U') + 86400;

    $op_trail_qry = $dbconn->query("SELECT * FROM op_audit_trail WHERE shop_id = 1 AND timestamp BETWEEN $startTime AND $endTime");

    if($op_trail_qry->num_rows === 0) {
      $timecard_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = 1 AND time_in BETWEEN $startTime AND $endTime");

      if($timecard_qry->num_rows === 0) {
        $holiday_qry = $dbconn->query("SELECT * FROM cal_holidays WHERE unix_time = $startTime");

        if($holiday_qry->num_rows === 0) {
          $dayOut = date(DATE_DEFAULT, $startTime);

          if((int)date('N', $startTime) === 5) {
            $friday = '(Friday)';
          } else {
            $friday = null;
          }

          echo "No Timecard data for day: $dayOut $friday\n";

          $i++;
        }
      }
    }
  }
}

echo "\n\nTotal Days Out: $i";