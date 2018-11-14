<?php
require '../../includes/header_start.php';

$id = sanitizeInput($_REQUEST['id']);
$col = sanitizeInput($_REQUEST['col']);
$val = sanitizeInput($_REQUEST['val']);

if($dbconn->query("UPDATE dealers SET $col = '$val' WHERE id = $id")) {
  echo displayToast('success', 'Successfully updated dealer.', 'Dealer Updated');
} else {
  dbLogSQLErr($dbconn);
}