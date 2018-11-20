<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 11/20/2018
 * Time: 2:09 PM
 */

namespace GlobalSearch;


class search {
  public function determineColor($room, $bracket) {
    global $dbconn;

    $job_color = null;

    $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room[$bracket . '_bracket']}'");
    $op = $op_qry->fetch_assoc();

    if ($room['order_status'] === '+' || $room['order_status'] === '-' || $op['job_title'] === 'Bracket Completed' || $op['job_title'] === 'N/A') {
      $job_color = 'job-color-gray';
    }

    if ((bool)$room[$bracket . '_published']) {
      $job_color = 'job-color-green';
    }

    return $job_color;
  }

  public function getBracketInfo($bracket, $opID, $room) {
    global $dbconn;

    if(!empty($opID)) {
      $bracket_info = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = $opID")->fetch_assoc();

      if((bool)$room[$bracket . '_published']) {
        if((false === stripos($bracket_info['job_title'], 'Bracket Completed')) && (false === stripos($bracket_info['job_title'], 'N/A'))) {
          $opacity = null;
        } else {
          $opacity = "style='color:rgba(80,80,80,.6);'";
        }

        return "<table class='table-custom-nb' $opacity><tr><td style='padding-left:2px;line-height:1em;'>{$bracket_info['job_title']}</td></tr></table>";
      }
    }
  }
}