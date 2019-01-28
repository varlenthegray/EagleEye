<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$strip = $_REQUEST['strip'];

$find = sanitizeInput($_REQUEST['search']);

$qry = $dbconn->query("SELECT
  CONCAT(r.so_parent, r.room, r.iteration) AS roomSOInfo,
  so.id AS soID,
  r.id AS rID,
  cc.id AS cID,
  cc.name AS companyName,
  so.so_num,
  so.project_name,
  r.room,
  r.iteration,
  r.room_name,
  r.order_status
FROM sales_order so
      LEFT JOIN rooms r ON so.so_num = r.so_parent
      LEFT JOIN contact_company cc ON so.company_id = cc.id
      LEFT JOIN contact_associations assocAct ON assocAct.type_id = cc.id
      LEFT JOIN contact cAct ON assocAct.contact_id = cAct.id
      LEFT JOIN contact_associations assocSO ON assocSO.type_id = so.id
      LEFT JOIN contact cSO ON assocSO.contact_id = cSO.id
WHERE
      so.so_num LIKE '%$find%' OR
      LOWER(so.dealer_code) LIKE LOWER('%$find%') OR
      LOWER(so.project_name) LIKE LOWER('%$find%') OR
      LOWER(so.project_landline) LIKE LOWER('%$find%') OR
      LOWER(cc.name) LIKE LOWER('%$find%') OR
      LOWER(cc.landline) LIKE LOWER('%$find%') OR
    ((LOWER(cAct.first_name) LIKE LOWER('%$find%') OR LOWER(cAct.last_name) LIKE LOWER('%$find%')) AND assocAct.type = 'account') OR
    ((LOWER(cSO.first_name) LIKE LOWER('%$find%') OR LOWER(cSO.last_name) LIKE LOWER('%$find%')) AND assocSO.type = 'project') OR
      LOWER(cAct.email) LIKE LOWER('%$find%') OR
      LOWER(cSO.email) LIKE LOWER('%$find%') OR
      LOWER(cAct.cell) LIKE LOWER('%$find%') OR
      LOWER(cSO.cell) LIKE LOWER('%$find%')
GROUP BY roomSOInfo
ORDER BY so.so_num, r.room, r.iteration ASC;");

$so_count_qry = $dbconn->query("SELECT id FROM sales_order WHERE so_num = '$find'");
$so_expanded = $so_count_qry->num_rows === 1;

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
    case 'H':
      $icon = 'fa fa-stop-circle';
      $altOStatus = 'hold';
      break;
    case '!':
      $icon = 'fa fa-exclamation';
      $altOStatus = 'pillar_missing';
      break;
    case 'R':
      $icon = 'fa fa-mail-forward';
      $altOStatus = 'referred';
      break;
    default:
      $icon = 'fa fa-exclamation-triangle';
      break;
  }

//  $altData = "{$ans['dealer_name']}, {$ans['project_name']}, {$ans['so_num']}, {$ans['room']}, {$ans['room_name']}, {$ans['so_num']}{$ans['room']}{$ans['iteration']}, $altOStatus, {$ans['dealer_id']}";

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
    $output[$pd]['expanded'] = $so_expanded;

    // establish the first SO within that dealer
    $output[$pd]['children'][$ps]['title'] = "<strong>{$ans['so_num']} - {$ans['project_name']}</strong>";
    $output[$pd]['children'][$ps]['key'] = $ans['soID'];
    $output[$pd]['children'][$ps]['keyType'] = 'soID';
    $output[$pd]['children'][$ps]['altData'] = $altData;
    $output[$pd]['children'][$ps]['expanded'] = $so_expanded;

    // room within the SO
    $output[$pd]['children'][$ps]['children'][$pr]['title'] = $room_header;
    $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
    $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
    $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
    $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
    $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;
    $output[$pd]['children'][$ps]['children'][$pr]['expanded'] = $so_expanded;

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
      $output[$pd]['children'][$ps]['expanded'] = $so_expanded;
    }

    $output[$pd]['children'][$ps]['children'][$pr]['title'] = $room_header;
    $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
    $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
    $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
    $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
    $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;
    $output[$pd]['children'][$ps]['children'][$pr]['expanded'] = $so_expanded;

    $pr++;
  }

  $r++;
}

//echo json_encode($result);
if($strip === 'true') {
  echo strip_tags(json_encode($output));
} else {
  echo json_encode($output);
}

/*$file = fopen('cached_result_tree.json', 'wb');
fwrite($file, json_encode($output));
fclose($file);*/