<?php
require_once ("../../includes/config.php");

$find = sanitizeInput($_REQUEST['find'], $dbconn);
$search = sanitizeInput($_REQUEST['search'], $dbconn);

function searchIt($db, $table, $column, $value) {
    if($column === 'project') {
        $addon_query = " OR dealer_code LIKE '%$value%' OR account_type LIKE '%$value%'";
    }

    $qry = $db->query("SELECT DISTINCT sales_order_num, dealer_code, project, account_type, dealer_contractor, project_manager FROM $table WHERE $column LIKE '%$value%' $addon_query LIMIT 0,100");

    if($qry->num_rows > 0) {
        while($result = $qry->fetch_assoc()) {
            echo "<tr>";
            echo "    <td>{$result['sales_order_num']}</td>";
            echo "    <td>{$result['dealer_code']}-{$result['project']}-{$result['account_type']}</td>";
            echo "    <td>{$result['dealer_contractor']}</td>";
            echo "    <td>{$result['project_manager']}</td>";
            echo "</tr>";
        }
    }
}

switch ($search) {
    case "sonum":
        searchIt($dbconn, "customer", "sales_order_num", $find);
        break;

    case "project":
        searchIt($dbconn, "customer", "project", $find);
        break;

    default:
        die();
        break;
}