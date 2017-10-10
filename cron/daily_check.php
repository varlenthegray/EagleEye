<?php
//set_include_path("/home/threeerp/domains/dev.3erp.us/public_html/includes/");
set_include_path("/home/threeerp/public_html/includes/");
// flip these around when pushing to prod

require ("config.php");
//require ("../includes/config.php");

date_default_timezone_set('America/New_York');

// define the start and end of day
$sod = strtotime("12AM");
$eod = strtotime("11:59PM");

$cal_qry = $dbconn->query("SELECT cal_followup.*, notes.*, notes.type_id AS nTypeID FROM cal_followup LEFT JOIN notes ON (cal_followup.type_id = notes.id AND cal_followup.type = notes.note_type) WHERE followup_time BETWEEN $sod AND $eod ORDER BY followup_time ASC");

if($cal_qry->num_rows > 0) {
    while($calendar = $cal_qry->fetch_assoc()) {
        $user_name = array();
        $user_email = array();
        $followup_time = date('n/j/y h:i A', $calendar['followup_time']);

        $so_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = {$calendar['nTypeID']}");
        $so = $so_qry->fetch_assoc();

        $user_qry = $dbconn->query("SELECT * FROM user");

        while($user = $user_qry->fetch_assoc()) {
            $user_name[$user['id']] = $user['name'];
            $user_email[$user['id']] = $user['email'];
        }

        $to = $user_email[$calendar['user_to']];
        $subject = "Inquiry Followup: {$so['so_parent']}{$so['room']}{$so['iteration']}_{$so['product_type']}{$so['rOrderStatus']}{$so['days_to_ship']}-{$so['room_name']}";

        $message = "<html><body>";
        $message .= "<h1>Inquiry followup requested</h1>";

        $message .= "Note to followup on: {$calendar['note']}<br/>";
        $message .= "Followup time: $followup_time<br />";
        $message .= "Initial Requestor: {$user_name[$calendar['user_from']]} at email {$user_email[$calendar['user_from']]}<br />";
        $message .= "Followup Of: {$user_name[$calendar['user_to']]} at email {$user_email[$calendar['user_to']]}<br /><br />";

        $message .= "<a href='https://3erp.us/index.php'>EagleEye Dashboard</a>";

        $message .= "</body></html>";

        $headers = "From: Dashboard <dashboard@3erp.us>\r\n";
        $headers .= "Reply-To: Ben <ben@smcm.us>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        mail($to, $subject, $message, $headers);
    }
}