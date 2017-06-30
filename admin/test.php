<?php
require ("../includes/header_start.php");

function calcDelDate($days_to_ship) {
    global $dbconn;

    switch($days_to_ship) {
        case 'Green':
            $target_date = strtotime('+34 weekdays');

            break;
        case 'Yellow':
            $target_date = strtotime('+14 weekdays');

            break;
        case 'Orange':
            $target_date = strtotime('+10 weekdays');

            break;
        case 'Red':
            $target_date = strtotime('+5 weekdays');

            break;
        default:
            $target_date = strtotime('+34 weekdays');

            break;
    }

    $holiday_count = 0;
    $holiday = [];

    $hol_qry = $dbconn->query("SELECT * FROM cal_holidays");

    while($hol_res = $hol_qry->fetch_assoc()) {
        $holiday[] = $hol_res['unix_time'];
    }

    // take the target date and determine if there are any holidays that fall between now and then
    foreach($holiday as $day) {
        if($target_date > $day && time() < $day) {
            // it falls on a holiday
            $holiday_count += 1;
        }
    }

    $target_date_formatted = date('m/d/y', $target_date);

    $final_date = strtotime("$target_date_formatted + $holiday_count days");

    echo date("m/d/Y", $final_date);
}

calcDelDate("Red");