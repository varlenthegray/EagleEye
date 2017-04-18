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
            $qry2 = $db->query("SELECT * FROM rooms WHERE so_parent = '{$result['sales_order_num']}'");

            $soColor = "job-color-green";

            if($qry2->num_rows > 0) {
                $bracketPri['sample'] = 4;
                $bracketPri['main'] = 4;
                $bracketPri['door'] = 4;
                $bracketPri['customs'] = 4;

                while($result2 = $qry2->fetch_assoc()) {
                    $bracketPri['sample'] = ($result2['sample_bracket_priority'] < $bracketPri['sample']) ? $result2['sample_bracket_priority'] : $bracketPri['sample'];
                    $bracketPri['main'] = ($result2['main_bracket_priority'] < $bracketPri['main']) ? $result2['main_bracket_priority'] : $bracketPri['main'];
                    $bracketPri['door'] = ($result2['doordrawer_bracket_priority'] < $bracketPri['door']) ? $result2['doordrawer_bracket_priority'] : $bracketPri['door'];
                    $bracketPri['customs'] = ($result2['customs_bracket_priority'] < $bracketPri['customs']) ? $result2['customs_bracket_priority'] : $bracketPri['customs'];
                }

                if(in_array("1", $bracketPri, true)) {
                    $soColor = "job-color-red";
                } elseif(in_array("2", $bracketPri, true)) {
                    $soColor = "job-color-orange";
                } elseif(in_array("3", $bracketPri, true)) {
                    $soColor = "job-color-yellow";
                }
            }

            echo "<tr class='cursor-hand $soColor' onclick='displaySO({$result['sales_order_num']});'>";
            echo "    <td>{$result['sales_order_num']}</td>";
            echo "    <td>{$result['dealer_code']}-{$result['project']}-{$result['account_type']}</td>";
            echo "    <td>{$result['dealer_contractor']}</td>";
            echo "    <td>{$result['project_manager']}</td>";
            echo "</tr>";
        }
    }
}

function determinePriority($priority) {
    switch($priority) {
        case 1:
            return "job-color-red";
            break;

        case 2:
            return "job-color-orange";
            break;

        case 3:
            return "job-color-yellow";
            break;

        case 4:
            return "job-color-green";
            break;

        default:
            return "job-color-green";
            break;
    }
}

switch ($search) {
    case "sonum":
        searchIt($dbconn, "customer", "sales_order_num", $find);
        break;

    case "project":
        searchIt($dbconn, "customer", "project", $find);
        break;

    case "room":
        $qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$find'");

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $samplePriority = determinePriority($result['sample_bracket_priority']);
                $mainPriority = determinePriority($result['main_bracket_priority']);
                $doorPriority = determinePriority($result['doordrawer_bracket_priority']);
                $customsPriority = determinePriority($result['customs_bracket_priority']);

                echo "<tr class='cursor-hand' onclick='displayGantt({$result['id']})'>";
                echo "    <td>{$result['room']}-{$result['room_name']}</td>";
                echo "    <td class='$samplePriority'>{$result['sample_bracket_status']}</td>";
                echo "    <td class='$mainPriority'>{$result['main_bracket_status']}</td>";
                echo "    <td class='$doorPriority'>{$result['doordrawer_bracket_status']}</td>";
                echo "    <td class='$customsPriority'>{$result['customs_bracket_status']}</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr>";
            echo "    <td colspan='5'>No results to display</td>";
            echo "</tr>";
        }

        break;

    default:
        die();
        break;
}