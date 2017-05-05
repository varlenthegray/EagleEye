<?php
require ("../includes/config.php");

// Execute auto-logoff in the system
$timecards = $dbconn->query("SELECT * FROM timecards WHERE time_out IS NULL");
$time_out = mktime(16, 45, 0);

if(date('Gi') >= "1645") { // if the current time is equal to or past 4:45PM
    if($timecards->num_rows > 0) {
        while($card = $timecards->fetch_assoc()) {
            if($card['time_out'] === null) {
                $dbconn->query("UPDATE timecards SET time_out = $time_out WHERE id = '{$card['id']}'");
            }
        }
    }
}