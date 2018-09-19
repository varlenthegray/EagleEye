<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$qry = $dbconn->query('SELECT
    so.id AS soID,
    r.id as rID,
    d.id as dID,
    d.dealer_name,
    so.so_num,
    so.project_name,
    r.room,
    r.iteration,
    r.room_name,
    r.order_status
  FROM sales_order so
    LEFT JOIN dealers d ON so.dealer_code = d.dealer_id
    LEFT JOIN rooms r ON so.so_num = r.so_parent
  ORDER BY d.dealer_name, so.so_num, r.room, r.iteration ASC;');

$result = [];
$output = [];
$i = 0;

if($qry->num_rows > 0) {
  while($response = $qry->fetch_assoc()) {
    $result[] = $response;
  }
}

$children = [];

$prevDealer = null; $pd = -1;
$prevSO = null; $ps = 0;
$prevRoom = null; $pr = 0;
$prevSeq = null;

$r = 0;

foreach($result AS $ans) {
  $altData = "{$ans['dealer_name']}, {$ans['project_name']}, {$ans['so_num']}, {$ans['room']}, {$ans['room_name']}, {$ans['so_num']}{$ans['room']}{$ans['iteration']}";

  switch($ans['order_status']) {
    case 'N':
      $icon = 'fa fa-fire';
      break;
    case '#':
      $icon = 'fa fa-flag-o';
      break;
    case '$':
      $icon = 'fa fa-sitemap';
      break;
    case '-':
      $icon = 'fa fa-thumbs-o-down';
      break;
    case '+':
      $icon = 'fa fa-thumbs-up';
      break;
    case 'P':
      $icon = 'fa fa-hourglass-3';
      break;
    default:
      $icon = 'fa fa-exclamation-triangle';
      break;
  }

  if(true) {
    if($ans['dealer_name'] !== $prevDealer) {
      $pd++; // increment the dealer code
      $ps = 0; // set previous SO back to 0
      $pr = 0;

      // establish dealer information
      $output[$pd]['title'] = $ans['dealer_name'];
      $output[$pd]['folder'] = 'true';
      $output[$pd]['key'] = $ans['dID'];
      $output[$pd]['keyType'] = 'dID';

      // establish the first SO within that dealer
      $output[$pd]['children'][$ps]['title'] = "<strong>{$ans['so_num']} - {$ans['project_name']}</strong>";
      $output[$pd]['children'][$ps]['key'] = $ans['soID'];
      $output[$pd]['children'][$ps]['keyType'] = 'soID';
      $output[$pd]['children'][$ps]['altData'] = $altData;

      // room within the SO
      $output[$pd]['children'][$ps]['children'][$pr]['title'] = "{$ans['room']}{$ans['iteration']} - {$ans['room_name']}";
      $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
      $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
      $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
      $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
      $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;

      $prevSO = $ans['soID']; // tell the system what SO we just worked on was
      $prevDealer = $ans['dealer_name']; // let the system know what dealer we just worked on

      $pr++;
    } else { // otherwise, we're continuing on with that one dealer
      if($ans['soID'] !== $prevSO) { // if the current SO does not match the SO we just worked on, we're gonna establish a new SO
        $ps++;

        $output[$pd]['children'][$ps]['children'] = array_values($output[$pd]['children'][$ps]['children']);
        $pr = 0; // set the previous room increment to 0
        $prevSO = $ans['soID']; // tell the system the new SO that we're working on

        // define the rest of the children (so's) for that dealer
        $output[$pd]['children'][$ps]['title'] = "<strong>{$ans['so_num']} - {$ans['project_name']}</strong>";
        $output[$pd]['children'][$ps]['key'] = $ans['soID'];
        $output[$pd]['children'][$ps]['keyType'] = 'soID';
        $output[$pd]['children'][$ps]['altData'] = $altData;

        $output[$pd]['children'][$ps]['children'][$pr]['title'] = "{$ans['room']}{$ans['iteration']} - {$ans['room_name']}";
        $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
        $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
        $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
        $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
        $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;
        $pr++;
      } else {
        $output[$pd]['children'][$ps]['children'][$pr]['title'] = "{$ans['room']}{$ans['iteration']} - {$ans['room_name']}";
        $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
        $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
        $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
        $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
        $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;

        $pr++;
      }
    }

    $r++;
  }
}

//print_r($result);
echo json_encode($output);

$file = fopen('cached_result_tree.json', 'wb');
fwrite($file, json_encode($output));
fclose($file);