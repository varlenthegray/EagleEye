<?php
require ("../includes/header_start.php");

// first, grab the room
$room_qry = $dbconn->query("SELECT * FROM rooms");

function addBC($bracket, $full_bracket, $op) {
    global $dbconn;

    // find all operations available in sales
    $ops_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket'");

    $sales_ops = array();

    while($ops = $ops_qry->fetch_assoc()) {
        // add them to the sales ops array
        $sales_ops[] = $ops['id'];
    }

    $ind_sales_bracket = array();

    // now find all ops within the individual bracket buildout that fit within sales ops
    foreach($full_bracket as $ind_op) {
        if(in_array($ind_op, $sales_ops)) {
            $ind_sales_bracket[] = $ind_op;
        }
    }

    if((int)end($ind_sales_bracket) !== $op) {
        array_push($ind_sales_bracket, $op);
    }

    return $ind_sales_bracket;
}

while($room = $room_qry->fetch_assoc()) {
    $output = '';
    $final = '';

    // now, explode the bracket
    $full_bracket = json_decode($room['individual_bracket_buildout']);

    $final[] = addBC('Sales', $full_bracket, 93);
    $final[] = addBC('Sample', $full_bracket, 94);
    $final[] = addBC('Pre-Production', $full_bracket, 95);
    $final[] = addBC('Drawer & Doors', $full_bracket, 96);
    $final[] = addBC('Main', $full_bracket, 97);
    $final[] = addBC('Custom', $full_bracket, 98);
    $final[] = addBC('Installation', $full_bracket, 99);
    $final[] = addBC('Shipping', $full_bracket, 100);

    foreach($final as $individual) {
        foreach($individual as $op) {
            $output[] = (int)$op;
        }
    }

    $output_final = json_encode($output);

    $dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$output_final' WHERE id = '{$room['id']}'");
}