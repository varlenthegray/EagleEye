<?php
require_once ("../../includes/header_start.php");
header('Content-Type: application/json');

$term = sanitizeInput($_REQUEST['term'], $dbconn);
$search = sanitizeInput($_REQUEST['search'], $dbconn);

function searchIt($db, $column, $table, $term) {
    $qry = $db->query("SELECT DISTINCT $column FROM $table WHERE $column LIKE '%$term%'");

    $print = null;

    echo "[";

    while($result = $qry->fetch_assoc()) {
        $print .= '"' . $result[$column] . '",';
    }

    $print = rtrim($print, ",");

    echo $print;

    echo "]";
}

switch($search) {
    case "cusonum":
        searchIt($dbconn, "so_num", "sales_order", $term);
        break;

    case "cuproject":
        searchit($dbconn, "project", "sales_order", $term);
        break;

    case "cucontractor":
        searchIt($dbconn, "contractor_dealer_code", "sales_order", $term);
        break;

    case "cupm":
        searchIt($dbconn, "project_manager", "sales_order", $term);
        break;

    case "dealerid":
        searchIt($dbconn, "dealer_id", "dealers", $term);
        break;

    default:
        die();
        break;
}