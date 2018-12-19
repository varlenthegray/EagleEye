<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$find = sanitizeInput($_REQUEST['search']);

$qry = $dbconn->query("SELECT
    so.id AS soID,
    r.id as rID,
    cc.id as cID,
    d.id as dID,
    d.dealer_name,
    cc.name AS companyName,
    so.so_num,
    so.project_name,
    r.room,
    r.iteration,
    r.room_name,
    r.order_status,
    d.dealer_id
  FROM sales_order so
    LEFT JOIN rooms r ON so.so_num = r.so_parent
    LEFT JOIN dealers d ON so.dealer_code = d.dealer_id
    LEFT JOIN contact_company cc on so.company_id = cc.id
  WHERE (so.so_num LIKE '%$find%' OR LOWER(so.dealer_code) LIKE LOWER('%$find%') OR LOWER(so.project_name) LIKE LOWER('%$find%') 
    OR LOWER(so.project_mgr) LIKE LOWER('%$find%') OR LOWER(so.name_1) LIKE LOWER('%$find%') OR LOWER(so.name_2) LIKE LOWER('%$find%') 
    OR LOWER(d.dealer_name) LIKE LOWER('%$find%'))
  ORDER BY d.dealer_name, so.so_num, r.room, r.iteration ASC;");

$result = [];
$output = [];
$i = 0;

if($qry->num_rows > 0) {
  while($response = $qry->fetch_assoc()) {
    $result[] = $response;
  }
}

$children = [];

$prevCompany = null; $pd = -1;
$prevSO = null; $ps = 0;
$prevRoom = null; $pr = 0;
$prevSeq = null;
$prev_room = null; $prev_sequence = null;

$r = 0;

foreach($result AS $ans) {
  $altOStatus = '';

  switch($ans['order_status']) {
    case 'N':
      $icon = 'fa fa-fire';
      $altOStatus = 'lead';
      break;
    case '#':
      $icon = 'fa fa-flag-o';
      $altOStatus = 'quote';
      break;
    case '$':
      $icon = 'fa fa-sitemap';
      $altOStatus = 'production, prod';
      break;
    case '-':
      $icon = 'fa fa-thumbs-o-down';
      $altOStatus = 'lost';
      break;
    case '+':
      $icon = 'fa fa-thumbs-up';
      $altOStatus = 'completed';
      break;
    case 'P':
      $icon = 'fa fa-hourglass-3';
      $altOStatus = 'pending';
      break;
    default:
      $icon = 'fa fa-exclamation-triangle';
      break;
  }

  $altData = "{$ans['dealer_name']}, {$ans['project_name']}, {$ans['so_num']}, {$ans['room']}, {$ans['room_name']}, {$ans['so_num']}{$ans['room']}{$ans['iteration']}, $altOStatus, {$ans['dealer_id']}";

  $ans['iteration'] = number_format((float)$ans['iteration'], 2);

  $seq_it = explode('.', $ans['iteration']);

  if($prev_room !== $ans['room']) {
    $prev_room = $ans['room'];
    $prev_sequence = $seq_it[0];

    $room_header = "{$ans['room']}{$ans['iteration']}: {$ans['room_name']}";
  } else {
    if($prev_sequence !== $seq_it[0]) {
      $prev_sequence = $seq_it[0];

      $room_header = "&nbsp;&nbsp;{$ans['iteration']}: {$ans['room_name']}";
    } else {
      $room_header = "&nbsp;&nbsp;&nbsp;&nbsp;.{$seq_it[1]}: {$ans['room_name']}";
    }
  }

  if($ans['companyName'] !== $prevCompany) {
    $pd++; // increment the dealer code
    $ps = 0; // set previous SO back to 0
    $pr = 0;

    // establish dealer information
    $output[$pd]['title'] = $ans['companyName'];
    $output[$pd]['folder'] = 'true';
    $output[$pd]['key'] = $ans['cID'];
    $output[$pd]['keyType'] = 'cID';

    // establish the first SO within that dealer
    $output[$pd]['children'][$ps]['title'] = "<strong>{$ans['so_num']} - {$ans['project_name']}</strong>";
    $output[$pd]['children'][$ps]['key'] = $ans['soID'];
    $output[$pd]['children'][$ps]['keyType'] = 'soID';
    $output[$pd]['children'][$ps]['altData'] = $altData;

    // room within the SO
    $output[$pd]['children'][$ps]['children'][$pr]['title'] = $room_header;
    $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
    $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
    $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
    $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
    $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;

    $prevSO = $ans['soID']; // tell the system what SO we just worked on was
    $prevCompany = $ans['companyName']; // let the system know what dealer we just worked on

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
    }

    $output[$pd]['children'][$ps]['children'][$pr]['title'] = $room_header;
    $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
    $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
    $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
    $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
    $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;

    $pr++;
  }

  $r++;
}

//echo json_encode($result);
echo json_encode($output);

/*$file = fopen('cached_result_tree.json', 'wb');
fwrite($file, json_encode($output));
fclose($file);*/