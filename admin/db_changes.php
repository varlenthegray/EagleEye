<?php
require ("../includes/header_start.php");

$admin_flag_qry = $dbconn->query("SELECT * FROM admin_flags WHERE flag = 'db_updated'");

if($admin_flag_qry->num_rows > 0) {
    $admin_flag = $admin_flag_qry->fetch_assoc();

    if(!(bool)$admin_flag['value']) {
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

        echo "Added 'Bracket Completed' to every bracket for every room.<br />";
        echo ($dbconn->query("CREATE TABLE sales_order (id INT PRIMARY KEY AUTO_INCREMENT,so_num INT,salesperson VARCHAR(100),contractor_dealer_code VARCHAR(10),
               project VARCHAR(150),project_email VARCHAR(255),project_addr VARCHAR(255),project_city VARCHAR(100),project_state VARCHAR(2),project_zip VARCHAR(10),
                project_landline VARCHAR(15),project_cell VARCHAR(15),contact1_name VARCHAR(100),contact1_cell VARCHAR(15),contact1_business_ph VARCHAR(15),contact1_email VARCHAR(255),
                 contact2_name VARCHAR(100),contact2_cell VARCHAR(15),contact2_business_ph VARCHAR(15),contact2_email VARCHAR(255),billing_contact VARCHAR(100),billing_landline VARCHAR(15),
                  billing_cell VARCHAR(15),billing_addr VARCHAR(255),billing_city VARCHAR(100),billing_state VARCHAR(2),billing_zip VARCHAR(10),ach_account_num VARCHAR(20),ach_routing_num VARCHAR(20),
                   mailing_addr VARCHAR(255),mailing_city VARCHAR(100),mailing_state VARCHAR(2),mailing_zip VARCHAR(10),mailing_landline VARCHAR(15),project_mgr VARCHAR(100),project_mgr_cell VARCHAR(15),
                    project_mgr_email VARCHAR(255),delivery_addr VARCHAR(255),delivery_city VARCHAR(100),delivery_state VARCHAR(2),delivery_zip VARCHAR(10),tax_id VARCHAR(20),physical_addr VARCHAR(255),
                     physical_city VARCHAR(100),physical_state VARCHAR(2),physical_zip VARCHAR(10),business_cell VARCHAR(15),business_email VARCHAR(255),business_landline VARCHAR(15),delivery_landline VARCHAR(15));")) ?
                      "Successful with creating sales order table.<br />" : "<b>Error</b> with creating sales order table.<br />";

        /** Inserting Sales Order information from the Customer table */
        $cu_qry = $dbconn->query("SELECT * FROM customer");

        while($cu = $cu_qry->fetch_assoc()) {
            $dbconn->query("INSERT INTO sales_order (so_num, salesperson, contractor_dealer_code, project, 
             project_addr, project_city, project_state, project_zip, project_landline, project_cell, project_mgr) 
              VALUES ('{$cu['sales_order_num']}', '{$cu['project_manager']}', '{$cu['dealer_code']}', '{$cu['project']}', 
               '{$cu['addr_1']} {$cu['addr_2']}', '{$cu['city']}', '{$cu['state']}', '{$cu['zip']}', '{$cu['pri_phone']}', 
                '{$cu['alt_phone_1']}', '{$cu['project_manager']}')");
                }

        /** End of inserting Sales Order information from the Customer table */

//        echo ($dbconn->query()) ? "Successful with creating sales order table.<br />" : "<b>Error</b> with creating sales order table.<br />";


        echo "<h1>Database prepared.</h1>";

        $dbconn->query("UPDATE admin_flags SET value = TRUE WHERE flag = 'db_updated'");
    } else {
        echo "<h1>Database indicates already updated.</h1>";
    }
} else {
    echo ($dbconn->query("CREATE TABLE admin_flags (id INT PRIMARY KEY AUTO_INCREMENT,flag VARCHAR(50),value BOOLEAN);")) ? "Successful with creating admin_flags table.<br />" : "<b>Error</b> with creating admin_flags table.<br />";
    echo ($dbconn->query("INSERT INTO admin_flags (flag, value) VALUES ('db_updated', FALSE);")) ? "Successful with setting database as updated.<br />" : "<b>Error</b> with setting database as updated.<br />";

    echo "<h1>First run with 'Admin Flags' - please re-run script.</h1>";
}