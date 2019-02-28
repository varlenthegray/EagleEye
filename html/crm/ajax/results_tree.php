<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$strip = $_REQUEST['strip'];

$find = sanitizeInput($_REQUEST['search']);

$qry = $dbconn->query("SELECT
  IF(so.id IS NOT NULL, CONCAT(r.so_parent, r.room, r.iteration), UUID()) AS roomSOInfo,
  so.id AS soID,
  r.id AS rID,
  c.id AS cID,
  IF(TRIM(c.company_name) != '', c.company_name, CONCAT(c.first_name, ' ', c.last_name)) AS title,
  IF(TRIM(c.company_name) != '', 'company', 'individual') AS type,
  so.so_num,
  so.project_name,
  r.room,
  r.iteration,
  r.room_name,
  r.order_status,
  ctcFrom.contact_to,
  ctcTo.contact_from,
  c.unique_id
FROM contact c
  LEFT JOIN sales_order so on c.id = so.contact_id
  LEFT JOIN rooms r on so.so_num = r.so_parent
  LEFT JOIN contact_to_sales_order ctso on ctso.sales_order_id = so.id
  LEFT JOIN contact ctsoCon on ctso.contact_id = ctsoCon.id
  LEFT JOIN contact_to_contact ctcFrom on c.id = ctcFrom.contact_from
  LEFT JOIN contact_to_contact ctcTo on c.id = ctcTo.contact_to
WHERE
  so.so_num LIKE '%$find%' OR
  LOWER(so.dealer_code) LIKE LOWER('%$find%') OR
  LOWER(so.project_name) LIKE LOWER('%$find%') OR
  LOWER(so.project_landline) LIKE LOWER('%$find%') OR
  LOWER(c.unique_id) LIKE LOWER('%$find%') OR
  LOWER(c.company_name) LIKE LOWER('%$find%') OR
  LOWER(c.primary_phone) LIKE LOWER('%$find%') OR
  LOWER(c.first_name) LIKE LOWER('%$find%') OR
  LOWER(c.last_name) LIKE LOWER('%$find%') OR
  LOWER(ctsoCon.first_name) LIKE LOWER('%$find%') OR
  LOWER(ctsoCon.last_name) LIKE LOWER('%$find%') OR
  LOWER(ctsoCon.company_name) LIKE LOWER('%$find%')
GROUP BY roomSOInfo
ORDER BY unique_id, title, so.so_num, r.room, r.iteration ASC;");

$so_count_qry = $dbconn->query("SELECT id FROM sales_order WHERE so_num = '$find'");
$so_expanded = $so_count_qry->num_rows === 1;

$result = [];
$output = [];
$i = 0;

// preload contacts into query to reduce number of database hits
$contacts = [];
$contact_table_qry = $dbconn->query('SELECT * FROM contact');

while($contact_table = $contact_table_qry->fetch_assoc()) {
  $contacts[$contact_table['id']] = $contact_table;
}

function getSingleContact($contact_id) {
  global $contacts;

  if(!empty($contact_id)) {
    $single_contact = $contacts[$contact_id];
    $assoc_to['cID'] = $single_contact['id'];
    $assoc_to['title'] = !empty($single_contact['company_name']) ? $single_contact['company_name'] : "{$single_contact['first_name']} {$single_contact['last_name']}";
    $assoc_to['type'] = !empty($single_contact['company_name']) ? 'company' : 'individual';
  } else {
    $assoc_to = null;
  }

  return $assoc_to;
}

$contacts_run = [];
$contacts_out = [];

if($qry->num_rows > 0) {
  while($response = $qry->fetch_assoc()) {
    $result[] = $response;

    // now to find associations per response
    if(!in_array($response['contact_to'], $contacts_run, TRUE) && !empty(getSingleContact($response['contact_to']))) {
      $contacts_out[] = getSingleContact($response['contact_to']);
      $contacts_run[] = $response['contact_to'];
    }

    if(!in_array($response['contact_from'], $contacts_run, TRUE) && !empty(getSingleContact($response['contact_from']))) {
      $contacts_out[] = getSingleContact($response['contact_from']);
      $contacts_run[] = $response['contact_from'];
    }
  }
}

$result = array_merge($contacts_out, $result);

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

  if($ans['title'] !== $prevCompany) {
    $pd++; // increment the dealer code
    $ps = 0; // set previous SO back to 0
    $pr = 0;

    if($ans['type'] === 'individual') {
      $output[$pd]['icon'] = 'fa fa-user';
    } else {
      $output[$pd]['icon'] = 'fa fa-building';
    }

    $title_uid = !empty($ans['unique_id']) ? "({$ans['unique_id']}) " : null;

    // establish dealer information
    $output[$pd]['title'] = "{$title_uid}{$ans['title']}";
    $output[$pd]['folder'] = 'true';
    $output[$pd]['key'] = $ans['cID'];
    $output[$pd]['keyType'] = 'cID';
    $output[$pd]['expanded'] = $so_expanded;
    $output[$pd]['contactType'] = $ans['type'];

    if(!empty($ans['soID'])) {
      // establish the first SO within that dealer
      $output[$pd]['children'][$ps]['title'] = "<strong>{$ans['so_num']} - {$ans['project_name']}</strong>";
      $output[$pd]['children'][$ps]['key'] = $ans['soID'];
      $output[$pd]['children'][$ps]['keyType'] = 'soID';
      $output[$pd]['children'][$ps]['altData'] = $altData;
      $output[$pd]['children'][$ps]['expanded'] = $so_expanded;
      $output[$pd]['children'][$ps]['icon'] = 'fa fa-file-text-o';
      $output[$pd]['children'][$ps]['contactType'] = $ans['type'];

      // room within the SO
      $output[$pd]['children'][$ps]['children'][$pr]['title'] = $room_header;
      $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
      $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
      $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
      $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
      $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;
      $output[$pd]['children'][$ps]['children'][$pr]['expanded'] = $so_expanded;
      $output[$pd]['children'][$ps]['children'][$pr]['contactType'] = $ans['type'];

      $prevSO = $ans['soID']; // tell the system what SO we just worked on was
    }

    $prevCompany = $ans['title']; // let the system know what dealer we just worked on

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
      $output[$pd]['children'][$ps]['icon'] = 'fa fa-file-text-o';
      $output[$pd]['children'][$ps]['contactType'] = $ans['type'];
    }

    $output[$pd]['children'][$ps]['children'][$pr]['title'] = $room_header;
    $output[$pd]['children'][$ps]['children'][$pr]['key'] = $ans['rID'];
    $output[$pd]['children'][$ps]['children'][$pr]['keyType'] = 'rID';
    $output[$pd]['children'][$ps]['children'][$pr]['altData'] = $altData;
    $output[$pd]['children'][$ps]['children'][$pr]['orderStatus'] = $ans['order_status'];
    $output[$pd]['children'][$ps]['children'][$pr]['icon'] = $icon;
    $output[$pd]['children'][$ps]['children'][$pr]['expanded'] = $so_expanded;
    $output[$pd]['children'][$ps]['children'][$pr]['contactType'] = $ans['type'];

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