<?php
require_once ("../../includes/config.php");
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

if($search === 'cusonum') {

}

switch($search) {
    case "cusonum":
        searchIt($dbconn, "sales_order_num", "customer", $term);
        break;

    case "cuproject":
        searchit($dbconn, "project", "customer", $term);
        break;

    case "cucontractor":
        searchIt($dbconn, "dealer_contractor", "customer", $term);
        break;

    case "cupm":
        searchIt($dbconn, "project_manager", "customer", $term);
        break;

    default:
        die();
        break;
}