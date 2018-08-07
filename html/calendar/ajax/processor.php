<?php
require_once '../../../includes/header_start.php';

// this file automatically processes all dataProcessor (autosave) requests

$pack = json_decode(file_get_contents('php://input'), true);

dbLogDebug(file_get_contents('php://input'));

$id = sanitizeInput($pack['id']);
$title = sanitizeInput($pack['data']['text']);
$start_date = sanitizeInput($pack['data']['start_date']);
$end_date = sanitizeInput($pack['data']['end_date']);
$type = !empty(trim(sanitizeInput($pack['data']['evType']))) ? sanitizeInput($pack['data']['evType']) : 'null';
$rec_type = sanitizeInput($pack['data']['rec_type']);
$event_length = !empty(trim(sanitizeInput($pack['data']['event_length']))) ? sanitizeInput($pack['data']['event_length']) : 'null';
$event_pid = !empty(trim(sanitizeInput($pack['data']['event_pid']))) ? sanitizeInput($pack['data']['event_pid']) : 'null';

if(($pack['action'] === 'deleted' || $pack['action'] === 'updated') && !empty(trim($rec_type))) {
  $dbconn->query("DELETE FROM calendar WHERE event_pid = '$id';");
}

if($event_pid !== 0 && $pack['action'] === 'deleted') {
  $dbconn->query("UPDATE calendar SET rec_type = 'none' WHERE id = '$id';");
}

switch($pack['action']) {
  case 'inserted':
    if($dbconn->query("INSERT INTO calendar (start_date, end_date, text, evType, rec_type, event_length, event_pid) 
    VALUES ('$start_date', '$end_date', '$title', '$type', '$rec_type', $event_length, $event_pid)")) {
      dbLogDebug('Successfully entered into DB.');
    } else {
      dbLogDebug('Unable to enter into DB. ' . $dbconn->error);
    }

    break;

  case 'updated':
    if($dbconn->query("UPDATE calendar SET text = '$title', start_date = '$start_date',
    end_date = '$end_date', evType = '$type', rec_type = '$rec_type', event_length = $event_length, event_pid = $event_pid WHERE id = $id")) {
      dbLogDebug("Successfully updated $id");
    } else {
      dbLogDebug("Logging error on $id, type: '$type'");
    }

    break;

  case 'deleted':
    if($dbconn->query("DELETE FROM calendar WHERE id = $id")) {
      dbLogDebug('Successfully deleted.');
    } else {
      dbLogDebug('Unable to delete.');
    }

    break;
}