<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 3/22/2017
 * Time: 3:33 PM
 */

use FlowrouteNumbersAndMessagingLib\Models;

// Display a PHP toast
function displayToast($type, $message, $subject) {
  return <<<HEREDOC
<script type="text/javascript">
    displayToast("$type", "$message", "$subject");
</script>
HEREDOC;
}

// Log an SQL error
function dbLogSQLErr($db, $toast = true, $err_override = null) {
  $sqlerror = (!empty($err_override)) ? $sqlerror = $err_override : $sqlerror = sanitizeInput($db->error);

  if($qry = $db->query("INSERT INTO log_error (message, time, ref_page, type) VALUES ('$sqlerror', NOW(), '{$_SERVER['REQUEST_URI']}', 1)")) {
    $id = $db->insert_id;

    if(!$toast)
      echo "<E> A severe error has been logged. Please report error code $id to IT.";
    else
      echo displayToast("error", "A severe error has been logged. Please report error code $id to IT.", "Error");
  } else {
    echo "Suffered FATAL ERROR: ~~'" . $db->error . "'~~";
    die();
  }
}

// Sanitize input field
function sanitizeInput($input, $db = '') {
  global $dbconn;

  return trim($dbconn->real_escape_string($input));
}

// log a debug code
function dbLogDebug($code) {
  global $dbconn;
  $dbconn->query("INSERT INTO log_debug (time, message) VALUES (NOW(), '$code')");
}

// display error codes on page
function outputPHPErrs() {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

/** Calculates the delivery date AND counts for holidays AND weekends */
function calcDelDate($days_to_ship) {
  global $dbconn;

  switch($days_to_ship) {
    case 'G':
      $target_date = strtotime('+26 weekdays');

      break;
    case 'Y':
      $target_date = strtotime('+19 weekdays');

      break;
    case 'N':
      $target_date = strtotime('+13 weekdays');

      break;
    case 'R':
      $target_date = strtotime('+6 weekdays');

      break;
    default:
      $target_date = strtotime('+26 weekdays');

      break;
  }

  $holiday_count = 0;
  $holiday = [];

  $hol_qry = $dbconn->query('SELECT * FROM cal_holidays');

  while($hol_res = $hol_qry->fetch_assoc()) {
    $holiday[] = $hol_res['unix_time'];
  }

  // take the target date and determine if there are any holidays that fall between now and then
  foreach($holiday as $day) {
    if($target_date > $day && time() < $day) {
      // it falls on a holiday
      ++$holiday_count;
    }
  }

  $target_date_formatted = date('m/d/y', $target_date);

  $final_date = strtotime("$target_date_formatted + $holiday_count days");

  if(date('N', $final_date) >= 6) {
    $final_date = strtotime(date('m/d/y', $final_date) . ' next monday');
  }

  return $final_date;
}

function mail_nl2br($string) {
  //return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
  return str_replace('\\n', '<br />', $string);
}

function sendText($to, $message) {
  global $flowroute;

  $msg = new Models\Message();

  $msg->from = '18285755727';
  $msg->to = $to;
  $msg->body = $message;

  $messages = $flowroute->getMessages();
  $result = $messages->createSendAMessage($msg);
}

function displayVINOpts($segment, $db_col = null, $id = null) {
  global $vin_schema;
  global $room;

  // assigns SEGMENT = VIN Schema column (panel_raise) of which there may be multiple pulled from VIN SCHEMA, DB_COL of which there is only one (panel_raise_sd, stored in ROOMS table)
  $dblookup = !empty($db_col) ? $db_col : $segment;
  $addl_id = !empty($id) ? "id = '$id'" : null; // for duplicate values (panel_raise vs panel_raise_sd)
  $options = null;
  $option_grid = null;

  $prev_header = null;
  $section_head = null;

  $selected = '';

  foreach($vin_schema[$segment] as $value) {
    if(empty($selected) && ((string)$value['key'] === (string)$room[$dblookup])) {
      if(!empty($value['description'])) {
        $selected_addlInfo = "{$value['description']} - ";
      } else {
        $selected_addlInfo = null;
      }

      $selected = $selected_addlInfo . $value['value'];
      $selected_img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;
      $sel_key = $value['key'];
    }

    if((bool)$value['visible']) {
      $img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;

      if ($value['group'] !== $prev_header) {
        $section_head = "<div class='header'>{$value['group']}</div>";
        $prev_header = $value['group'];
      } else {
        $section_head = null;
      }

      if(!empty($value['subitems'])) {
        $options .= "$section_head <div class='sub_option_header' data-value='{$value['key']}'>{$value['value']} $img</div>";

        $subitems = json_decode($value['subitems']);
        $option_grid .= "$section_head <div class='grid_element' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img";

        if(!empty($value['description'])) {
          $addlInfo = $value['description'];
        } else {
          $addlInfo = $value['value'];
        }

        foreach($subitems as $key => $item) {
          $options .= "<div class='option sub_option' data-value='{$key}' data-addl-info='$addlInfo'>{$item}</div>";
          $option_grid .= "<div class='option sub_option' data-value='{$key}'>{$item}</div>";
        }

        $option_grid .= '</div>';
      } else {
        if(!empty($value['description'])) {
          $addlInfo = $value['description'];
        } else {
          $addlInfo = null;
        }

        $options .= "$section_head <div class='option' data-value='{$value['key']}' data-addl-info='$addlInfo'>{$value['value']} $img</div>";

        $option_grid .= "$section_head <div class='grid_element option' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img</div>";
      }
    }
  }

  $selected = empty($selected) ? 'Not Selected Yet' : $selected;

  $final_out = "<div class='custom_dropdown' $addl_id>";
  $final_out .= "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  $final_out .= "<div class='dropdown_options' data-for='$dblookup'>";
  $final_out .= "<div class='option_list'>$options</div>";
  $final_out .= "<div class='option_grid'>$option_grid</div>";
  $final_out .= "</div><input type='hidden' value='$sel_key' id='{$dblookup}' name='{$dblookup}' /><div class='clearfix'></div></div>";

  return $final_out;
}

function displayFinishOpts($segment, $db_col = null, $id = null) {
  global $vin_schema;
  global $room;

  // assigns SEGMENT = VIN Schema column (panel_raise) of which there may be multiple pulled from VIN SCHEMA, DB_COL of which there is only one (panel_raise_sd, stored in ROOMS table)
  $dblookup = !empty($db_col) ? $db_col : $segment;
  $addl_id = !empty($id) ? $id : $dblookup; // for duplicate values (panel_raise vs panel_raise_sd)
  $options = null;
  $option_grid = null;

  $prev_header = null;
  $section_head = null;

  $selected = '';

  foreach($vin_schema[$segment] as $value) {
    if(((string)$value['key'] === (string)$room[$dblookup]) && empty($selected)) {
      $selected = $value['value'];
      $selected_img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;
      $sel_key = $value['key'];
    }

    if((bool)$value['visible']) {
      $img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;

      if ($value['group'] !== $prev_header) {
        $section_head = "<div class='header'>{$value['group']}</div>";
        $prev_header = $value['group'];
      } else {
        $section_head = null;
      }

      $options .= "$section_head <div class='option' data-value='{$value['key']}' data-display-text=\"{$value['value']}\">{$value['value']} $img</div>";

      if(!empty($value['imagemap_coords']) && false !== strpos($value['imagemap_coords'], '[')) {
        $multimap = json_decode($value['imagemap_coords']);

        foreach($multimap AS $map) {
          $option_grid .= "<area shape='rect' class='option sub_option' style='display:none;' coords='$map' href='#' onclick='return false;' data-value='{$value['key']}' data-display-text=\"{$value['value']}\" />";
        }
      } else {
        $option_grid .= "<area shape='rect' class='option sub_option' style='display:none;' coords='{$value['imagemap_coords']}' onclick='return false;' href='#' data-value='{$value['key']}' data-display-text=\"{$value['value']}\" />";
      }
    }
  }

  $selected = empty($selected) ? 'Not Selected Yet' : $selected;

  $option_grid = "<img src='/assets/images/sample_display.jpg' width='778' height='800' border='0' usemap='#{$dblookup}_map' style='max-width:800px;max-height:800px;' /><map name='{$dblookup}_map' class='grid_element'>$option_grid</map>";

  echo "<div class='custom_dropdown'>";
  echo "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  echo "<div class='dropdown_options' data-for='$dblookup'>";
  echo "<div class='option_list'>$options</div>";
  echo "<div class='option_grid'>$option_grid</div>";
  echo "</div><input type='hidden' value='$sel_key' id='{$dblookup}' name='{$dblookup}' /><div class='clearfix'></div></div>";

  /*echo "<div class='custom_dropdown' $addl_id>";
  echo "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  echo "<div class='dropdown_options' data-for='$dblookup'>";
  echo "<div class='option_list'>$options</div>";
  echo "<div class='option_grid'>$options_grid</div>";
  echo "</div><input type='hidden' value='$selected' id='$dblookup' name='$dblookup' /><div class='clearfix'></div></div>";*/
}

function translateVIN($segment, $key, $db_col = null) {
  global $dbconn;
  global $info;

  // segment = panel raise
  // key = X

  if(empty($db_col)) {
    $db_col = $segment;
  }

  $custom_keys = ['X', 'Xxx', 'AX', 'DX', 'TX', 'Xx', 'WX', '1cXXXX', '3gXXXX'];

  $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$segment' AND `key` = '$key'");
  $vin = $vin_qry->fetch_assoc();

  $mfg = '';
  $code = '';
  $name = '';
  $desc = '';

  if(!empty($info['custom_vin_info'])) {
    if(in_array($key, $custom_keys, true)) {
      $custom_info = json_decode($info['custom_vin_info'], true);

      if(count($custom_info[$db_col]) > 1) {
        foreach($custom_info[$db_col] as $key2 => $value) {
          $mfg = false !== stripos($key2, 'mfg') ? $value : $mfg;
          $code = false !== stripos($key2, 'code') ? $value : $code;
          $name = false !== stripos($key2, 'name') ? $value : $name;
        }

        $desc = $name;
      } else {
        $desc = 'Non-Standard - ' . array_values($custom_info[$db_col])[0];
      }
    } else {
      $desc = $vin['value'];
    }
  } else {
    $desc = $vin['value'];
  }

  return $desc;
}

function calcMiles($zip1, $zip2) {
  // https://developers.google.com/maps/documentation/distance-matrix/intro
  $url = "http://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$zip1&destinations=$zip2&mode=driving&language=en-EN&sensor=false";

  $data = @file_get_contents($url);

  $result = json_decode($data, true);

  // comes back in meters, change that value to miles
  return $result['rows'][0]['elements'][0]['distance']['value'] * 0.000621371;
}

function calcShipZone($dealer_zip) {
  $mileage = calcMiles(28704, $dealer_zip);
  $out = [];
  $out['miles'] = $mileage;

  switch(true) {
    case $mileage <= 100:
      $out['zone'] = 'A (0-100 Miles)';
      $out['cost'] = 0.00;
      break;
    case $mileage > 100 && $mileage <= 200:
      $out['zone'] = 'B (100-200 Miles)';
      $out['cost'] = 150.00;
      break;
    case $mileage > 200 && $mileage <= 300:
      $out['zone'] = 'C (200-300 Miles)';
      $out['cost'] = 300.00;
      break;
    case $mileage > 300 && $mileage <= 400:
      $out['zone'] = 'D (300-400 Miles)';
      $out['cost'] = 450.00;
      break;
    case $mileage > 400 && $mileage <= 500:
      $out['zone'] = 'E (400-500 Miles)';
      $out['cost'] = 600.00;
      break;
  }

  return $out;
}