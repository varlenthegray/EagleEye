<?php
require_once '../../../includes/header_start.php';

switch($_REQUEST['action']) {
  case 'getEvents':
    $output = [];

    if($event_qry = $dbconn->query('SELECT `key`, `name` AS label FROM calendar_event_types')) {
      while($event = $event_qry->fetch_assoc()) {
        $output[] = $event;
      }
    }

    echo json_encode($output);

    break;

  case 'getEventCSS':
    header('Content-type: text/css; charset: UTF-8');

    if($event_qry = $dbconn->query('SELECT `key`, `name`, color FROM calendar_event_types')) {
      while($event = $event_qry->fetch_assoc()) {
        $css_name = strtolower(str_replace(' ', '_', $event['name']));

        echo <<<HEREDOC
        .event_$css_name div,
        .dhx_cal_editor.event_$css_name,
        .dhx_cal_event_line.event_$css_name {
          background-color: #{$event['color']}!important;
        }
        .dhx_cal_event_clear.event_$css_name {
          color: #{$event['color']}!important;
        }


HEREDOC;
      }
    }

    break;
}