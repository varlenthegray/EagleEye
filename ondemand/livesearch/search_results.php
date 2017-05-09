<?php
require_once ("../../includes/header_start.php");

$find = sanitizeInput($_REQUEST['find'], $dbconn);
$search = sanitizeInput($_REQUEST['search'], $dbconn);

function searchIt($db, $table, $column, $value) {
    if($column === 'project') {
        $addon_query = " OR dealer_code LIKE '%$value%' OR account_type LIKE '%$value%'";
    }

    $qry = $db->query("SELECT DISTINCT sales_order_num, dealer_code, project, account_type, dealer_contractor, project_manager FROM $table WHERE $column LIKE '%$value%' $addon_query ORDER BY sales_order_num DESC LIMIT 0,25");

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

            echo "<tr class='cursor-hand $soColor' onclick='displaySO({$result['sales_order_num']})'>";
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

    case "contractor":
        searchIt($dbconn, "customer", "project", $find);
        break;

    case "project_manager":
        searchIt($dbconn, "customer", "project_manager", $find);
        break;

    case "room":
        $qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$find'");

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $salesPriority = determinePriority($result['sales_bracket_priority']);
                $preprodPriority = determinePriority($result['preproduction_bracket_priority']);
                $samplePriority = determinePriority($result['sample_bracket_priority']);
                $doorPriority = determinePriority($result['doordrawer_bracket_priority']);
                $customsPriority = determinePriority($result['customs_bracket_priority']);
                $boxPriority = determinePriority($result['box_bracket_priority']);

                $salesBracket = $dbconn->query("SELECT id, op_id, job_title, department FROM operations WHERE id = {$result['sales_bracket']}")->fetch_assoc();
                $salesBracketName = $salesBracket['op_id'] . "-" . $salesBracket['job_title'];

                $preprodBracket = $dbconn->query("SELECT id, op_id, job_title, department FROM operations WHERE id = {$result['preproduction_bracket']}")->fetch_assoc();
                $preprodBracketName = $preprodBracket['op_id'] . "-" . $preprodBracket['job_title'];

                $sampleBracket = $dbconn->query("SELECT id, op_id, job_title, department FROM operations WHERE id = {$result['sample_bracket']}")->fetch_assoc();
                $sampleBracketName = $sampleBracket['op_id'] . "-" . $sampleBracket['job_title'];

                $doorBracket = $dbconn->query("SELECT id, op_id, job_title, department FROM operations WHERE id = {$result['doordrawer_bracket']}")->fetch_assoc();
                $doorBrackettName = $doorBracket['op_id'] . "-" . $doorBracket['job_title'];

                $customsBracket = $dbconn->query("SELECT id, op_id, job_title, department FROM operations WHERE id = {$result['custom_bracket']}")->fetch_assoc();
                $customsBracketName = $customsBracket['op_id'] . "-" . $customsBracket['job_title'];

                $boxBracket = $dbconn->query("SELECT id, op_id, job_title, department FROM operations WHERE id = {$result['box_bracket']}")->fetch_assoc();
                $boxBracketName = $boxBracket['op_id'] . "-" . $boxBracket['job_title'];



                echo "<tr class='cursor-hand' onclick='displayRoomInfo({$result['id']})'>";
                echo "    <td>{$result['room']}-{$result['room_name']}</td>";
                echo "    <td class='$salesPriority'>$salesBracketName</td>";
                echo "    <td class='$preprodPriority'>$preprodBracketName</td>";
                echo "    <td class='$samplePriority'>$sampleBracketName</td>";
                echo "    <td class='$doorPriority'>$doorBrackettName</td>";
                echo "    <td class='$customsPriority'>$customsBracketName</td>";
                echo "    <td class='$boxPriority'>$boxBracketName</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr>";
            echo "    <td colspan='7'>No results to display</td>";
            echo "</tr>";
        }

        break;

    case "edit_so_num":
        $qry = $dbconn->query("SELECT * FROM customer WHERE sales_order_num = '$find'");

        if($qry->num_rows > 0) {
            $result = $qry->fetch_assoc();

            echo json_encode($result);
        } else {
            echo "no results";
        }

        break;

    default:
        die();
        break;
}