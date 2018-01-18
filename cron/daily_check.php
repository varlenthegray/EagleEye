<?php
//set_include_path("/home/threeerp/domains/dev.3erp.us/public_html/includes/");
set_include_path("/home/threeerp/public_html/includes/");
// flip these around when pushing to prod

require("config.php");
require("classes/mail_handler.php");
require("functions.php");

date_default_timezone_set('America/New_York');

$mailer = new \MailHandler\mail_handler();

// define the start and end of day
$sod = strtotime("tomorrow 12AM");
$eod = strtotime("tomorrow 11:59PM");

$message = '';

$cal_qry = $dbconn->query("SELECT cal_followup.*, notes.*, notes.type_id AS nTypeID FROM cal_followup LEFT JOIN notes ON (cal_followup.type_id = notes.id AND cal_followup.type = notes.note_type) WHERE followup_time BETWEEN $sod AND $eod ORDER BY followup_time ASC");

if($cal_qry->num_rows > 0) {
    while($calendar = $cal_qry->fetch_assoc()) {
        $user_name = array();
        $user_email = array();
        $followup_time = date('n/j/y h:i A', $calendar['followup_time']);

        if($calendar['type'] === 'room_inquiry_reply' || $calendar['type'] === 'so_inquiry_reply') {
            $so_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM cal_followup
                LEFT JOIN notes ON cal_followup.type_id = notes.id LEFT JOIN rooms ON notes.type_id = rooms.id LEFT JOIN sales_order ON rooms.so_parent = sales_order.id
                WHERE `type` = 'room_inquiry_reply';");
            $so = $so_qry->fetch_assoc();
        } else {
            $so_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = {$calendar['nTypeID']}");
            $so = $so_qry->fetch_assoc();
        }

        $user_qry = $dbconn->query("SELECT * FROM user");

        while($user = $user_qry->fetch_assoc()) {
            $user_name[$user['id']] = $user['name'];
            $user_email[$user['id']] = $user['email'];
        }

        $to = $user_email[$calendar['user_to']];
        $subject = "Inquiry Followup: {$so['so_parent']}{$so['room']}{$so['iteration']}_{$so['product_type']}{$so['rOrderStatus']}{$so['days_to_ship']}-{$so['room_name']}";

        $message .= "Note to followup on: {$calendar['notes']}<br/>";
        $message .= "Followup time: $followup_time<br />";
        $message .= "Initial Requestor: {$user_name[$calendar['user_from']]} at email {$user_email[$calendar['user_from']]}<br />";
        $message .= "Followup Of: {$user_name[$calendar['user_to']]} at email {$user_email[$calendar['user_to']]}";

        $mailer->sendMessage($to, $user['email'], $subject, $message);
    }
}