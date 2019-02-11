<?php
require '../includes/header_start.php';

//outputPHPErrs();

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


//print_r($contacts_out);

$array_uid = array_column($contacts_out, 'unique_id');
$cont_out = array_multisort($array_uid, SORT_ASC, $contacts_out);

$result = array_merge($contacts_out, $result);

print_r($result);