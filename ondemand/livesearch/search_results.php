<?php
require_once ("../../includes/header_start.php");

$find = sanitizeInput($_REQUEST['find']);
$search = sanitizeInput($_REQUEST['search'], $dbconn);

function searchIt($table, $column, $value) {
    global $dbconn;
    if($column === 'project') {
        $addon_query = " OR dealer_code LIKE '%$value%' OR account_type LIKE '%$value%'";
    }

    $qry = $dbconn->query("SELECT * FROM $table WHERE $column LIKE '%$value%' $addon_query ORDER BY sales_order_num DESC LIMIT 0,25");

    if($qry->num_rows > 0) {
        while($result = $qry->fetch_assoc()) {
            $qry2 = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['sales_order_num']}'");

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

            $dealer_prefix = substr($result['dealer_code'], 0,3);

            $dealer_qry = $dbconn->query("SELECT dealer_name FROM dealers WHERE dealer_id LIKE '%$dealer_prefix%'");
            $dealer = $dealer_qry->fetch_assoc();

            $account_type = ($result['account_type'] === 'R') ? "Retail" : "Wholesale";

            echo "<tr class='cursor-hand $soColor' id='display_{$result['sales_order_num']}'>";
            echo "    <td>{$result['sales_order_num']}</td>";
            echo "    <td>{$result['job_type']}</td>";
            echo "    <td>{$result['project']}</td>";
            echo "    <td>{$result['salesperson']}</td>";
            echo "    <td>{$result['dealer_code']}: {$dealer['dealer_name']}</td>";
            echo "    <td>$account_type</td>";
            echo "    <td>{$result['project_manager']}</td>";
            echo "    <td width='26px'><button class='btn waves-effect btn-primary pull-right' id='edit_{$result['sales_order_num']}'> <i class='zmdi zmdi-edit'></i> </button></td>";
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

function checkPublished($bracket) {
    global $dbconn;
    global $roomid;

    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$roomid'");
    $room = $room_qry->fetch_assoc();

    return ((bool)$room[$bracket . "_published"]) ? '<strong style="font-size: 1.1em;"><i class="zmdi zmdi-assignment-check"></i></strong>' : NULL;
}

switch ($search) {
    case "sonum":
        searchIt("customer", "sales_order_num", $find);
        break;
    case "project":
        searchIt("customer", "project", $find);
        break;
    case "contractor":
        searchIt("customer", "project", $find);
        break;
    case "project_manager":
        searchIt("customer", "project_manager", $find);
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

                $salesBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$result['sales_bracket']}")->fetch_assoc();
                $salesBracketName = $salesBracket['op_id'] . "-" . $salesBracket['job_title'];

                $preprodBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$result['preproduction_bracket']}")->fetch_assoc();
                $preprodBracketName = $preprodBracket['op_id'] . "-" . $preprodBracket['job_title'];

                $sampleBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$result['sample_bracket']}")->fetch_assoc();
                $sampleBracketName = $sampleBracket['op_id'] . "-" . $sampleBracket['job_title'];

                $doorBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$result['doordrawer_bracket']}")->fetch_assoc();
                $doorBrackettName = $doorBracket['op_id'] . "-" . $doorBracket['job_title'];

                $customsBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$result['custom_bracket']}")->fetch_assoc();
                $customsBracketName = $customsBracket['op_id'] . "-" . $customsBracket['job_title'];

                $mainBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$result['box_bracket']}")->fetch_assoc();
                $mainBracketName = $mainBracket['op_id'] . "-" . $mainBracket['job_title'];



                echo "<tr class='cursor-hand' onclick='displayRoomInfo({$result['id']})'>";
                echo "    <td>{$result['room']}-{$result['room_name']}</td>";
                echo "    <td class='$salesPriority'>$salesBracketName</td>";
                echo "    <td class='$preprodPriority'>$preprodBracketName</td>";
                echo "    <td class='$samplePriority'>$sampleBracketName</td>";
                echo "    <td class='$doorPriority'>$doorBrackettName</td>";
                echo "    <td class='$customsPriority'>$customsBracketName</td>";
                echo "    <td class='$boxPriority'>$mainBracketName</td>";
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
    case "general":
        $qry = $dbconn->query("SELECT * FROM customer WHERE sales_order_num LIKE 
            '%$find%' OR project LIKE '%$find%' OR dealer_code LIKE '%$find%'
            OR account_type LIKE '%$find%' OR dealer_contractor LIKE '%$find%' OR project_manager LIKE '%$find%'
            ORDER BY sales_order_num DESC LIMIT 0,25");

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $qry2 = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['sales_order_num']}'");

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
                        $bracketPri['customs'] = ($result2['custom_bracket_priority'] < $bracketPri['customs']) ? $result2['custom_bracket_priority'] : $bracketPri['customs'];
                    }

                    if(in_array("1", $bracketPri, true)) {
                        $soColor = "job-color-red";
                    } elseif(in_array("2", $bracketPri, true)) {
                        $soColor = "job-color-orange";
                    } elseif(in_array("3", $bracketPri, true)) {
                        $soColor = "job-color-yellow";
                    }
                }

                $dealer_prefix = substr($result['dealer_code'], 0,3);

                $dealer_qry = $dbconn->query("SELECT dealer_name FROM dealers WHERE dealer_id LIKE '%$dealer_prefix%'");
                $dealer = $dealer_qry->fetch_assoc();

                switch($result['account_type']) {
                    case 'R':
                        $account_type = "Retail";
                        break;
                    case 'W':
                        $account_type = "Wholesale";
                        break;
                    case 'D':
                        $account_type = "Distribution";
                        break;
                    default:
                        $account_type = "<i>Unknown</i>";
                        break;
                }



                /** BEGIN LISTING OF SO'S */
                echo "  <tr class='cursor-hand $soColor' id='show_room_{$result['sales_order_num']}'>";
                echo "    <td width='26px'><button class='btn waves-effect btn-primary pull-right' id='edit_{$result['sales_order_num']}'> <i class='zmdi zmdi-edit'></i> </button></td>";
                echo "    <td>{$result['sales_order_num']}</td>";
                echo "    <td>{$result['project']}</td>";
                echo "    <td>{$result['salesperson']}</td>";
                echo "    <td>{$result['dealer_code']}: {$dealer['dealer_name']}</td>";
                echo "    <td>$account_type</td>";
                echo "    <td>{$result['project_manager']}</td>";
                echo "  </tr>";

                /** BEGIN ROOM INFORMATION */
                echo "  <tr id='tr_room_{$result['sales_order_num']}' style='display: none;'>";
                echo "    <td colspan='8'><div id='div_room_{$result['sales_order_num']}' style='display: none;'>";?>

                <div class="col-md-12">
                    <div class="row">
                        <table width="100%" class="table">
                            <thead>
                            <tr>
                                <th colspan="2">ROOM</th>
                                <th>SALES</th>
                                <th>SAMPLE</th>
                                <th>PRE-PRODUCTION</th>
                                <th>DOOR/DRAWER</th>
                                <th>MAIN</th>
                                <th>CUSTOM</th>
                                <th>SHIPPING</th>
                                <th>INSTALLATION</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['sales_order_num']}'");

                            if($room_qry->num_rows > 0) {
                                while($room = $room_qry->fetch_assoc()) {
                                    $individual_bracket = json_decode($room['individual_bracket_buildout']);

                                    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'product_type' AND `key` = '{$room['product_type']}'");
                                    $vin_result = $vin_qry->fetch_assoc();

                                    $iteration = 1 + $room['iteration'];

                                    $ship_days_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'days_to_ship' AND `key` = '{$room['days_to_ship']}'");
                                    $ship_days = $ship_days_qry->fetch_assoc();

                                    $room_name = "{$room['room']}-{$vin_result['value']}$iteration-{$room['order_status']}{$ship_days['value']}: {$room['room_name']}";

                                    $salesPriority = determinePriority($room['sales_bracket_priority']);
                                    $preprodPriority = determinePriority($room['preproduction_bracket_priority']);
                                    $samplePriority = determinePriority($room['sample_bracket_priority']);
                                    $doorPriority = determinePriority($room['doordrawer_bracket_priority']);
                                    $customsPriority = determinePriority($room['custom_bracket_priority']);
                                    $mainPriority = determinePriority($room['main_bracket_priority']);
                                    $shippingPriority = determinePriority($room['shipping_bracket_priority']);
                                    $installPriority = determinePriority($room['install_bracket_priority']);

                                    $salesBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['sales_bracket']}")->fetch_assoc();
                                    $salesBracketName = $salesBracket['op_id'] . "-" . $salesBracket['job_title'];

                                    $preprodBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['preproduction_bracket']}")->fetch_assoc();
                                    $preprodBracketName = $preprodBracket['op_id'] . "-" . $preprodBracket['job_title'];

                                    $sampleBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['sample_bracket']}")->fetch_assoc();
                                    $sampleBracketName = $sampleBracket['op_id'] . "-" . $sampleBracket['job_title'];

                                    $doorBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['doordrawer_bracket']}")->fetch_assoc();
                                    $doorBrackettName = $doorBracket['op_id'] . "-" . $doorBracket['job_title'];

                                    $customsBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['custom_bracket']}")->fetch_assoc();
                                    $customsBracketName = $customsBracket['op_id'] . "-" . $customsBracket['job_title'];

                                    $mainBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['main_bracket']}")->fetch_assoc();
                                    $mainBracketName = $mainBracket['op_id'] . "-" . $mainBracket['job_title'];

                                    $shippingBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['shipping_bracket']}")->fetch_assoc();
                                    $shippingBracketName = $shippingBracket['op_id'] . "-" . $shippingBracket['job_title'];

                                    $installBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['install_bracket']}")->fetch_assoc();
                                    $installBracketName = $installBracket['op_id'] . "-" . $installBracket['job_title'];

                                    $roomid = $room['id'];

                                    $sales_published = checkPublished('sales');
                                    $preprod_published = checkPublished('preproduction');
                                    $sample_published = checkPublished('sample');
                                    $door_published = checkPublished('doordrawer');
                                    $customs_published = checkPublished('custom');
                                    $main_published = checkPublished('main');
                                    $shipping_published = checkPublished('shipping');
                                    $install_published = checkPublished('install_bracket');

                                    echo "<tr class='cursor-hand' id='show_single_room_{$room['id']}'>";
                                    echo "  <td style='width: 26px;'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button></td>";
                                    echo "  <td>$room_name</td>";
                                    echo "  <td class='$salesPriority'>$salesBracketName $sales_published</td>";
                                    echo "  <td class='$samplePriority'>$sampleBracketName $sample_published</td>";
                                    echo "  <td class='$preprodPriority'>$preprodBracketName $preprod_published</td>";
                                    echo "  <td class='$doorPriority'>$doorBrackettName $door_published</td>";
                                    echo "  <td class='$mainPriority'>$mainBracketName $main_published</td>";
                                    echo "  <td class='$customsPriority'>$customsBracketName $customs_published</td>";
                                    echo "  <td class='$installPriority'>$installBracketName $install_published</td>";
                                    echo "  <td class='$shippingPriority'>$shippingBracketName $shipping_published</td>";
                                    echo "</tr>";

                                    /** BEGIN SINGLE ROOM DISPLAY */
                                    echo "<tr id='tr_single_room_{$room['id']}' style='display: none;'>";
                                    echo "  <td colspan='9'><div id='div_single_room_{$room['id']}' style='display: none;'>";

                                    $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '%{$result['dealer_code']}%'");
                                    $dealer = $dealer_qry->fetch_assoc();
                                    ?>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <form id="form_room_<?php echo $room['id']; ?>">
                                                    <div class="col-md-3">
                                                        <form>
                                                            <table width="100%" class="table table-custom-nb">
                                                                <tr>
                                                                    <td><label for="dealer_code">Dealer Code</label></td>
                                                                    <td><input type="text" class="form-control" id="dealer_code" placeholder="Dealer Code" value="<?php echo $result['dealer_code']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="account_type">Account Type</label></td>
                                                                    <td>
                                                                        <select class="form-control" id="account_type" value="<?php echo $result['account_type']; ?>">
                                                                            <option value="R" <?php echo ($result['account_type'] === 'R') ? "selected" : null; ?>>Retail</option>
                                                                            <option value="W" <?php echo ($result['account_type'] === 'W') ? "selected" : null; ?>>Wholesale</option>
                                                                            <option value="D" <?php echo ($result['account_type'] === 'D') ? "selected" : null; ?>>Distribution</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="dealer">Dealer</label></td>
                                                                    <td><input type="text" class="form-control" id="dealer" placeholder="Dealer" value="<?php echo $dealer['dealer_name']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="contact">Contact</label></td>
                                                                    <td><input type="text" class="form-control" id="contact" placeholder="Contact" value="<?php echo $dealer['contact']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="phone_number">Phone Number</label></td>
                                                                    <td><input type="text" class="form-control" id="phone_number" placeholder="Phone Number" value="<?php echo $dealer['phone']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="email">Email</label></td>
                                                                    <td><input type="text" class="form-control" id="email" placeholder="Email" value="<?php echo $dealer['email']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="salesperson">Salesperson</label></td>
                                                                    <td><input type="text" class="form-control" id="salesperson" placeholder="Salesperson" value="<?php echo $dealer['contact']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="shipping_addr">Shipping Address</label></td>
                                                                    <td><input type="text" class="form-control" id="shipping_addr" placeholder="Shipping Address" value="<?php echo $dealer['shipping_address']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"><input type="text" class="form-control pull-left" id="city" style="width: 33.3%;" placeholder="City" value="<?php echo $dealer['physical_city']; ?>"><select class="form-control pull-left" id="p_state" style="width: 33.3%;" name="p_state">
                                                                            <option value="AL" <?php echo ($dealer['physical_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                                                            <option value="AK" <?php echo ($dealer['physical_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                                                            <option value="AR" <?php echo ($dealer['physical_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                                                            <option value="CA" <?php echo ($dealer['physical_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                                                            <option value="CO" <?php echo ($dealer['physical_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                                                            <option value="CT" <?php echo ($dealer['physical_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                                                            <option value="DE" <?php echo ($dealer['physical_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                                                            <option value="FL" <?php echo ($dealer['physical_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                                                            <option value="GA" <?php echo ($dealer['physical_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                                                            <option value="HI" <?php echo ($dealer['physical_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                                                            <option value="ID" <?php echo ($dealer['physical_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                                                            <option value="IL" <?php echo ($dealer['physical_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                                                            <option value="IN" <?php echo ($dealer['physical_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                                                            <option value="IA" <?php echo ($dealer['physical_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                                                            <option value="KS" <?php echo ($dealer['physical_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                                                            <option value="KY" <?php echo ($dealer['physical_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                                                            <option value="LA" <?php echo ($dealer['physical_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                                                            <option value="ME" <?php echo ($dealer['physical_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                                                            <option value="MD" <?php echo ($dealer['physical_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                                                            <option value="MA" <?php echo ($dealer['physical_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                                                            <option value="MI" <?php echo ($dealer['physical_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                                                            <option value="MN" <?php echo ($dealer['physical_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                                                            <option value="MS" <?php echo ($dealer['physical_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                                                            <option value="MO" <?php echo ($dealer['physical_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                                                            <option value="MT" <?php echo ($dealer['physical_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                                                            <option value="NE" <?php echo ($dealer['physical_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                                                            <option value="NV" <?php echo ($dealer['physical_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                                                            <option value="NH" <?php echo ($dealer['physical_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                                                            <option value="NJ" <?php echo ($dealer['physical_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                                                            <option value="NM" <?php echo ($dealer['physical_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                                                            <option value="NY" <?php echo ($dealer['physical_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                                                            <option value="NC" <?php echo ($dealer['physical_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                                                            <option value="ND" <?php echo ($dealer['physical_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                                                            <option value="OH" <?php echo ($dealer['physical_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                                                            <option value="OK" <?php echo ($dealer['physical_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                                                            <option value="OR" <?php echo ($dealer['physical_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                                                            <option value="PA" <?php echo ($dealer['physical_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                                                            <option value="RI" <?php echo ($dealer['physical_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                                                            <option value="SC" <?php echo ($dealer['physical_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                                                            <option value="SD" <?php echo ($dealer['physical_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                                                            <option value="TN" <?php echo ($dealer['physical_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                                                            <option value="TX" <?php echo ($dealer['physical_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                                                            <option value="UT" <?php echo ($dealer['physical_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                                                            <option value="VT" <?php echo ($dealer['physical_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                                                            <option value="VA" <?php echo ($dealer['physical_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                                                            <option value="WA" <?php echo ($dealer['physical_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                                                            <option value="WV" <?php echo ($dealer['physical_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                                                            <option value="WI" <?php echo ($dealer['physical_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                                                            <option value="WY" <?php echo ($dealer['physical_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                                                                        </select><input type="text" class="form-control pull-left" id="zip" style="width: 33.3%;" placeholder="ZIP" value="<?php echo $dealer['physical_zip']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="delivery_date">Delivery Date</label></td>
                                                                    <td><input type="text" class="form-control" id="delivery_date" placeholder="Delivery Date"></td>
                                                                </tr>
                                                            </table>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <table width="100%" class="table table-custom-nb">
                                                            <tr>
                                                                <td><label for="room">Room</label></td>
                                                                <td><input type="text" class="form-control" id="room" placeholder="Room" value="<?php echo $room['room']; ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="product_type">Product Type</label></td>
                                                                <td><input type="text" class="form-control" id="product_type" placeholder="Product Type" value="<?php echo $room['product_type']; ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="iteration">Iteration</label></td>
                                                                <td><input type="text" class="form-control" id="iteration" placeholder="Iteration" value="<?php echo $room['iteration']; ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="order_status">Order Status</label></td>
                                                                <td>
                                                                    <select class="form-control" id="order_status">
                                                                        <option value="#" <?php echo ($room['order_status'] === '#') ? "selected" : null; ?>>Quote</option>
                                                                        <option value="$" <?php echo ($room['order_status'] === '$') ? "selected" : null; ?>>Job</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="days_to_ship">Days to Ship</label></td>
                                                                <td>
                                                                    <select class="form-control" id="order_status">
                                                                        <option value="Green" <?php echo ($room['days_to_ship'] === 'Green') ? "selected" : null; ?>>Green</option>
                                                                        <option value="Yellow" <?php echo ($room['days_to_ship'] === 'Yellow') ? "selected" : null; ?>>Yellow</option>
                                                                        <option value="Orange" <?php echo ($room['days_to_ship'] === 'Orange') ? "selected" : null; ?>>Orange</option>
                                                                        <option value="Red" <?php echo ($room['days_to_ship'] === 'Red') ? "selected" : null; ?>>Red</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="room_name">Room Name</label></td>
                                                                <td><input type="text" class="form-control" id="room_name" placeholder="Room Name" value="<?php echo $room['room_name']; ?>"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <fieldset class="form-group">
                                                            <label for="room_notes">Room Notes</label>
                                                            <textarea class="form-control" id="room_notes" name="room_notes" maxlength="65530" placeholder="Room Notes" rows="3" data-toggle="popover" data-placement="top" data-trigger="focus" title="" data-html="true" data-content="<table style='font-size: 9px;'>
                        <tr>
                            <td>CON = Conestoga</td>
                            <td class='text-md-right'>RW = Rework</td>
                        </tr>
                        <tr>
                            <td>DEL = Delivery</td>
                            <td class='text-md-right'>S/B = Scheduled Back</td>
                        </tr>
                        <tr>
                            <td>DPL = Diminishing Punch List</td>
                            <td class='text-md-right'>SEL = Selections</td>
                        </tr>
                        <tr>
                            <td>EM = Email</td>
                            <td class='text-md-right'>T/W = This Week</td>
                        </tr>
                        <tr>
                            <td>ETA = Estimated Time of Arrival</td>
                            <td class='text-md-right'>W/A = Will Advise</td>
                        </tr>
                        <tr>
                            <td>FU = Follow Up</td>
                            <td class='text-md-right'>W/C = Will Contact</td>
                        </tr>
                        <tr>
                            <td>N/A = Not Available</td>
                            <td class='text-md-right'>WO = Work Order</td>
                        </tr>
                    </table>" data-original-title="Abbreviations"></textarea>
                                                        </fieldset>
                                                    </div>

                                                    <div class="col-md-12 text-md-right" style="margin: 10px 0;">
                                                        <button type="button" class="btn btn-primary waves-effect waves-light w-xs" id="save_room_<?php echo $room['id']; ?>">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <?php echo "</div></td>";
                                    echo "</tr>";

                                    /** BEGIN DISPLAY OF MANAGE BRACKET */
                                    echo "<tr id='tr_room_bracket_{$room['id']}' style='display: none;'>";
                                    echo "  <td colspan='12'><div id='div_room_bracket_{$room['id']}' style='display: none;'>";

                                    $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '%{$result['dealer_code']}%'");
                                    $dealer = $dealer_qry->fetch_assoc();
                                    ?>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6 col-md-offset-2">
                                                <form id="form_bracket_<?php echo $room['id']; ?>">
                                                    <table width="100%" class="bracket-adjustment-table">
                                                        <tr>
                                                            <td style="width: 48%;" class="bracket-border-top">
                                                                <div class="row bracket-header-custom">
                                                                    <div class="col-md-8"><h5><label for="sales_bracket_adjustments_<?php echo $room['id']; ?>">Sales Bracket</label></h5></div>
                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sales_published_<?php echo $room['id']; ?>" id="sales_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['sales_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                </div>
                                                            </td>
                                                            <td style="width:18px;"></td>
                                                            <td style="width: 48%;" class="bracket-border-top">
                                                                <div class="row bracket-header-custom">
                                                                    <div class="col-md-8"><h5><label for="sample_bracket_adjustments_<?php echo $room['id']; ?>">Sample Bracket</label></h5></div>
                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sample_published_<?php echo $room['id']; ?>" id="sample_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['sample_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bracket-border-bottom">
                                                                <select multiple="multiple" class="multi-select" id="sales_bracket_adjustments_<?php echo $room['id']; ?>" name="sales_bracket_adjustments_<?php echo $room['id']; ?>[]" data-plugin="multiselect">
                                                                    <?php
                                                                        $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = 'Sales' AND always_visible = FALSE ORDER BY op_id ASC");

                                                                        while($op = $op_qry->fetch_assoc()) {
                                                                            if(in_array($op['id'], $individual_bracket)) {
                                                                                echo "<option value='{$op['id']}'>{$op['op_id']}-{$op['job_title']}</option>";
                                                                            } else {
                                                                                echo "<option value='{$op['id']}' selected>{$op['op_id']}-{$op['job_title']}</option>";
                                                                            }
                                                                        }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>&nbsp;</td>
                                                            <td class="bracket-border-bottom">
                                                                <select multiple="multiple" class="multi-select" id="sample_bracket_adjustments_<?php echo $room['id']; ?>" name="sample_bracket_adjustments_<?php echo $room['id']; ?>[]" data-plugin="multiselect">
                                                                    <?php
                                                                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = 'Pre-Production' AND always_visible = FALSE ORDER BY op_id ASC");

                                                                    while($op = $op_qry->fetch_assoc()) {
                                                                        if(in_array($op['id'], $individual_bracket)) {
                                                                            echo "<option value='{$op['id']}'>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        } else {
                                                                            echo "<option value='{$op['id']}' selected>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bracket-border-top">
                                                                <div class="row bracket-header-custom">
                                                                    <div class="col-md-8"><h5><label for="pre_prod_bracket_adjustments_<?php echo $room['id']; ?>">Pre-production Bracket</label></h5></div>
                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="pre_prod_published_<?php echo $room['id']; ?>" id="pre_prod_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['preproduction_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                </div>
                                                            </td>
                                                            <td>&nbsp;</td>
                                                            <td class="bracket-border-top">
                                                                <div class="row bracket-header-custom">
                                                                    <div class="col-md-8"><h5><label for="door_drawer_bracket_adjustments_<?php echo $room['id']; ?>">Door/Drawer Bracket</label></h5></div>
                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="door_drawer_published_<?php echo $room['id']; ?>" id="door_drawer_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['doordrawer_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bracket-border-bottom">
                                                                <select multiple="multiple" class="multi-select" id="pre_prod_bracket_adjustments_<?php echo $room['id']; ?>" name="pre_prod_bracket_adjustments_<?php echo $room['id']; ?>[]" data-plugin="multiselect">
                                                                    <?php
                                                                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = 'Pre-Production' AND always_visible = FALSE ORDER BY op_id ASC");

                                                                    while($op = $op_qry->fetch_assoc()) {
                                                                        if(in_array($op['id'], $individual_bracket)) {
                                                                            echo "<option value='{$op['id']}'>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        } else {
                                                                            echo "<option value='{$op['id']}' selected>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>&nbsp;</td>
                                                            <td class="bracket-border-bottom">
                                                                <select multiple="multiple" class="multi-select" id="door_drawer_bracket_adjustments_<?php echo $room['id']; ?>" name="door_drawer_bracket_adjustments_<?php echo $room['id']; ?>[]" data-plugin="multiselect">
                                                                    <?php
                                                                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = 'Pre-Production' AND always_visible = FALSE ORDER BY op_id ASC");

                                                                    while($op = $op_qry->fetch_assoc()) {
                                                                        if(in_array($op['id'], $individual_bracket)) {
                                                                            echo "<option value='{$op['id']}'>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        } else {
                                                                            echo "<option value='{$op['id']}' selected>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bracket-border-top">
                                                                <div class="row bracket-header-custom">
                                                                    <div class="col-md-8"><h5><label for="main_bracket_adjustments_<?php echo $room['id']; ?>">Main Bracket</label></h5></div>
                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="box_published_<?php echo $room['id']; ?>" id="box_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['main_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                </div>
                                                            </td>
                                                            <td>&nbsp;</td>
                                                            <td class="bracket-border-top">
                                                                <div class="row bracket-header-custom">
                                                                    <div class="col-md-8"><h5><label for="custom_bracket_adjustments_<?php echo $room['id']; ?>">Custom Bracket</label></h5></div>
                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="custom_published_<?php echo $room['id']; ?>" id="custom_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['custom_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bracket-border-bottom">
                                                                <select multiple="multiple" class="multi-select" id="main_bracket_adjustments_<?php echo $room['id']; ?>" name="main_bracket_adjustments_<?php echo $room['id']; ?>[]" data-plugin="multiselect">
                                                                    <?php
                                                                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = 'Main' AND always_visible = FALSE ORDER BY op_id ASC");

                                                                    while($op = $op_qry->fetch_assoc()) {
                                                                        if(in_array($op['id'], $individual_bracket)) {
                                                                            echo "<option value='{$op['id']}'>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        } else {
                                                                            echo "<option value='{$op['id']}' selected>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>&nbsp;</td>
                                                            <td class="bracket-border-bottom">
                                                                <select multiple="multiple" class="multi-select" id="custom_bracket_adjustments_<?php echo $room['id']; ?>" name="custom_bracket_adjustments_<?php echo $room['id']; ?>[]" data-plugin="multiselect">
                                                                    <?php
                                                                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = 'Drawer & Doors' AND always_visible = FALSE ORDER BY op_id ASC");

                                                                    while($op = $op_qry->fetch_assoc()) {
                                                                        if(in_array($op['id'], $individual_bracket)) {
                                                                            echo "<option value='{$op['id']}'>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        } else {
                                                                            echo "<option value='{$op['id']}' selected>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bracket-border-top">
                                                                <div class="row bracket-header-custom">
                                                                    <div class="col-md-8"><h5><label for="shipping_bracket_adjustments_<?php echo $room['id']; ?>">Shipping Bracket</label></h5></div>
                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="shipping_published_<?php echo $room['id']; ?>" id="shipping_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['shipping_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                </div>
                                                            </td>
                                                            <td>&nbsp;</td>
                                                            <td class="bracket-border-top">
                                                                <div class="row bracket-header-custom">
                                                                    <div class="col-md-8"><h5><label for="install_bracket_adjustments_<?php echo $room['id']; ?>">Install Bracket</label></h5></div>
                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="install_published_<?php echo $room['id']; ?>" id="install_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['install_bracket_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bracket-border-bottom">
                                                                <select multiple="multiple" class="multi-select" id="shipping_bracket_adjustments_<?php echo $room['id']; ?>" name="shipping_bracket_adjustments_<?php echo $room['id']; ?>[]" data-plugin="multiselect">
                                                                    <?php
                                                                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = 'Shipping' AND always_visible = FALSE ORDER BY op_id ASC");

                                                                    while($op = $op_qry->fetch_assoc()) {
                                                                        if(in_array($op['id'], $individual_bracket)) {
                                                                            echo "<option value='{$op['id']}'>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        } else {
                                                                            echo "<option value='{$op['id']}' selected>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>&nbsp;</td>
                                                            <td class="bracket-border-bottom">
                                                                <select multiple="multiple" class="multi-select" id="install_bracket_adjustments_<?php echo $room['id']; ?>" name="install_bracket_adjustments_<?php echo $room['id']; ?>[]" data-plugin="multiselect">
                                                                    <?php
                                                                    $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = 'Installation' AND always_visible = FALSE ORDER BY op_id ASC");

                                                                    while($op = $op_qry->fetch_assoc()) {
                                                                        if(in_array($op['id'], $individual_bracket)) {
                                                                            echo "<option value='{$op['id']}'>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        } else {
                                                                            echo "<option value='{$op['id']}' selected>{$op['op_id']}-{$op['job_title']}</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <div class="col-md-12 text-md-right" style="margin: 10px 0;">
                                                        <button type="button" class="btn btn-primary waves-effect waves-light w-xs" id="save_bracket_<?php echo $room['id']; ?>">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <?php echo "</div></td>";
                                    echo "</tr>";
                                    /** END DISPLAY OF MANAGE BRACKET */
                                    /** END SINGLE ROOM DISPLAY */
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php echo "    </td>";
                echo "  </tr>";
                /** END ROOM INFORMATION */

                /** BEGIN EDIT SO DISPLAY */
                echo "<tr id='tr_edit_so_{$result['sales_order_num']}' style='display: none;'>";
                echo "  <td colspan='9'><div id='div_edit_so_{$result['sales_order_num']}' style='display: none;'>";
                ?>

                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-10">
                            <form id="form_so_<?php echo $room['id']; ?>">
                                <table width="100%" class="table table-custom-nb">
                                    <tr>
                                        <td width="8%"><label for="account_type">Account Type</label></td>
                                        <td width="12%">
                                            <select class="form-control" id="account_type" name="account_type">
                                                <option value="Retail" <?php echo ($result['account_type'] === 'R')? 'selected' : NULL; ?>>Retail</option>
                                                <option value="Distribution" <?php echo ($result['account_type'] === 'D')? 'selected' : NULL; ?>>Distribution</option>
                                                <option value="Contract Cutting" <?php echo ($result['account_type'] === 'C')? 'selected' : NULL; ?>>Contract Cutting</option>
                                            </select>
                                        </td>
                                        <td width="8%">&nbsp;</td>
                                        <td width="12%">&nbsp;</td>
                                        <td width="8%">&nbsp;</td>
                                        <td width="12%">&nbsp;</td>
                                        <td width="8%">&nbsp;</td>
                                        <td width="12%">&nbsp;</td>
                                        <td width="8%">&nbsp;</td>
                                        <td width="12%">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td><label for="salesperson">Salesperson</label></td>
                                        <td>
                                            <select class="form-control" id="salesperson" name="salesperson">
                                                <option value="Robert" <?php echo ($result['salesperson'] === 'Robert')? 'selected' : NULL; ?>>Robert</option>
                                                <option value="Brent" <?php echo ($result['salesperson'] === 'Brent')? 'selected' : NULL; ?>>Brent</option>
                                                <option value="Cindy" <?php echo ($result['salesperson'] === 'Cindy')? 'selected' : NULL; ?>>Cindy</option>
                                                <option value="Shane" <?php echo ($result['salesperson'] === 'Shane')? 'selected' : NULL; ?>>Shane</option>
                                            </select>
                                        </td>
                                        <td><label for="contractor">Contractor</label></td>
                                        <td><input type="text" class="form-control" id="contractor" name="contractor" placeholder="Contractor" value="<?php echo $result['dealer_contractor']; ?>" /></td>
                                        <td colspan="2">&nbsp;</td>
                                        <td><label for="project">Project</label></td>
                                        <td><input type="text" class="form-control" id="project" name="project" placeholder="Project" value="<?php echo $result['project']; ?>" /></td>
                                        <td><label for="project_addr">Project Address</label></td>
                                        <td><input type="text" name="project_addr" class="form-control" placeholder="Project Address" id="project_addr" value="<?php echo $result['addr_1']; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td><label for="contractor_code">Contractor Code</label></td>
                                        <td><input type="text" name="contractor_code" class="form-control" placeholder="Contractor Code" id="contractor_code" value="<?php echo $result['dealer_code']; ?>"></td>
                                        <td colspan="2">&nbsp;</td>
                                        <td><label for="p_landline">Project Landline</label></td>
                                        <td><input type="text" name="p_landline" class="form-control" placeholder="Project Landline" id="p_landline" value="<?php echo $result['pri_phone']; ?>"></td>
                                        <td colspan="6">
                                            <table width="100%">
                                                <tr>
                                                    <td width="33.3%"><input type="text" name="p_city" class="form-control" placeholder="City" id="p_city" value="<?php echo $result['city']; ?>"></td>
                                                    <td width="33.3%"><select class="form-control" id="p_state" name="p_state">
                                                            <option value="AL" <?php echo ($result['state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                                            <option value="AK" <?php echo ($result['state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                                            <option value="AR" <?php echo ($result['state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                                            <option value="CA" <?php echo ($result['state'] === 'CA') ? "selected" : null; ?>>California</option>
                                                            <option value="CO" <?php echo ($result['state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                                            <option value="CT" <?php echo ($result['state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                                            <option value="DE" <?php echo ($result['state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                                            <option value="FL" <?php echo ($result['state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                                            <option value="GA" <?php echo ($result['state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                                            <option value="HI" <?php echo ($result['state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                                            <option value="ID" <?php echo ($result['state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                                            <option value="IL" <?php echo ($result['state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                                            <option value="IN" <?php echo ($result['state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                                            <option value="IA" <?php echo ($result['state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                                            <option value="KS" <?php echo ($result['state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                                            <option value="KY" <?php echo ($result['state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                                            <option value="LA" <?php echo ($result['state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                                            <option value="ME" <?php echo ($result['state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                                            <option value="MD" <?php echo ($result['state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                                            <option value="MA" <?php echo ($result['state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                                            <option value="MI" <?php echo ($result['state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                                            <option value="MN" <?php echo ($result['state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                                            <option value="MS" <?php echo ($result['state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                                            <option value="MO" <?php echo ($result['state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                                            <option value="MT" <?php echo ($result['state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                                            <option value="NE" <?php echo ($result['state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                                            <option value="NV" <?php echo ($result['state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                                            <option value="NH" <?php echo ($result['state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                                            <option value="NJ" <?php echo ($result['state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                                            <option value="NM" <?php echo ($result['state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                                            <option value="NY" <?php echo ($result['state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                                            <option value="NC" <?php echo ($result['state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                                            <option value="ND" <?php echo ($result['state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                                            <option value="OH" <?php echo ($result['state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                                            <option value="OK" <?php echo ($result['state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                                            <option value="OR" <?php echo ($result['state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                                            <option value="PA" <?php echo ($result['state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                                            <option value="RI" <?php echo ($result['state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                                            <option value="SC" <?php echo ($result['state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                                            <option value="SD" <?php echo ($result['state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                                            <option value="TN" <?php echo ($result['state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                                            <option value="TX" <?php echo ($result['state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                                            <option value="UT" <?php echo ($result['state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                                            <option value="VT" <?php echo ($result['state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                                            <option value="VA" <?php echo ($result['state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                                            <option value="WA" <?php echo ($result['state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                                            <option value="WV" <?php echo ($result['state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                                            <option value="WI" <?php echo ($result['state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                                            <option value="WY" <?php echo ($result['state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                                                        </select></td>
                                                    <td width="33.3%"><input type="text" name="p_zip" class="form-control" placeholder="Zip" id="p_zip" value="<?php echo $result['zip']; ?>"></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="height: 5px;">
                                        <td colspan="11"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="contact_1">Contact 1</label></td>
                                        <td><input type="text" name="contact_1" class="form-control" placeholder="Contact 1" id="contact_1" value="<?php echo $result['contact_1']; ?>"></td>
                                        <td><label for="cell_1">Cell Phone</label></td>
                                        <td><input type="text" name="cell_1" class="form-control" placeholder="Cell Phone" id="cell_1" value="<?php echo $result['contact_1_cell']; ?>"></td>
                                        <td><label for="business_1">Business Phone</label></td>
                                        <td><input type="text" name="business_1" class="form-control" placeholder="Business Phone" id="business_1" value="<?php echo $result['contact_1_business_ph']; ?>"></td>
                                        <td><label for="email_1">Email Address</label></td>
                                        <td><input type="text" name="email_1" class="form-control" placeholder="Email Address" id="email_1" value="<?php echo $result['contact_1_email']; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="contact_2">Contact 2</label></td>
                                        <td><input type="text" name="contact_2" class="form-control" placeholder="Contact 2" id="contact_2" value="<?php echo $result['contact_2']; ?>"></td>
                                        <td><label for="cell_2">Cell Phone</label></td>
                                        <td><input type="text" name="cell_2" class="form-control" placeholder="Cell Phone" id="cell_2" value="<?php echo $result['contact_2_cell']; ?>"></td>
                                        <td><label for="business_2">Business Phone</label></td>
                                        <td><input type="text" name="business_2" class="form-control" placeholder="Business Phone" id="business_2" value="<?php echo $result['contact_2_business_ph']; ?>"></td>
                                        <td><label for="email_2">Email Address</label></td>
                                        <td><input type="text" name="email_2" class="form-control" placeholder="Email Address" id="email_2" value="<?php echo $result['contact_2_email']; ?>"></td>
                                    </tr>
                                    <tr style="height: 5px;">
                                        <td colspan="11"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="physical_addr">Physical Address</label></td>
                                        <td><input type="text" name="physical_addr" class="form-control" placeholder="Physical Address" id="physical_addr" value="<?php echo $result['phys_addr']; ?>"></td>
                                        <td><label for="ph_city">Physical City</label></td>
                                        <td><input type="text" name="ph_city" class="form-control" placeholder="Physical City" id="ph_city" value="<?php echo $result['phys_city']; ?>"></td>
                                        <td><label for="ph_state">Physical State</label></td>
                                        <td><select class="form-control" id="ph_state" name="ph_state">
                                                <option value="AL" <?php echo ($result['phys_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                                <option value="AK" <?php echo ($result['phys_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                                <option value="AR" <?php echo ($result['phys_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                                <option value="CA" <?php echo ($result['phys_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                                <option value="CO" <?php echo ($result['phys_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                                <option value="CT" <?php echo ($result['phys_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                                <option value="DE" <?php echo ($result['phys_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                                <option value="FL" <?php echo ($result['phys_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                                <option value="GA" <?php echo ($result['phys_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                                <option value="HI" <?php echo ($result['phys_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                                <option value="ID" <?php echo ($result['phys_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                                <option value="IL" <?php echo ($result['phys_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                                <option value="IN" <?php echo ($result['phys_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                                <option value="IA" <?php echo ($result['phys_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                                <option value="KS" <?php echo ($result['phys_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                                <option value="KY" <?php echo ($result['phys_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                                <option value="LA" <?php echo ($result['phys_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                                <option value="ME" <?php echo ($result['phys_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                                <option value="MD" <?php echo ($result['phys_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                                <option value="MA" <?php echo ($result['phys_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                                <option value="MI" <?php echo ($result['phys_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                                <option value="MN" <?php echo ($result['phys_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                                <option value="MS" <?php echo ($result['phys_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                                <option value="MO" <?php echo ($result['phys_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                                <option value="MT" <?php echo ($result['phys_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                                <option value="NE" <?php echo ($result['phys_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                                <option value="NV" <?php echo ($result['phys_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                                <option value="NH" <?php echo ($result['phys_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                                <option value="NJ" <?php echo ($result['phys_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                                <option value="NM" <?php echo ($result['phys_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                                <option value="NY" <?php echo ($result['phys_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                                <option value="NC" <?php echo ($result['phys_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                                <option value="ND" <?php echo ($result['phys_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                                <option value="OH" <?php echo ($result['phys_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                                <option value="OK" <?php echo ($result['phys_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                                <option value="OR" <?php echo ($result['phys_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                                <option value="PA" <?php echo ($result['phys_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                                <option value="RI" <?php echo ($result['phys_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                                <option value="SC" <?php echo ($result['phys_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                                <option value="SD" <?php echo ($result['phys_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                                <option value="TN" <?php echo ($result['phys_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                                <option value="TX" <?php echo ($result['phys_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                                <option value="UT" <?php echo ($result['phys_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                                <option value="VT" <?php echo ($result['phys_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                                <option value="VA" <?php echo ($result['phys_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                                <option value="WA" <?php echo ($result['phys_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                                <option value="WV" <?php echo ($result['phys_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                                <option value="WI" <?php echo ($result['phys_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                                <option value="WY" <?php echo ($result['phys_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                                                </select></td>
                                        <td><label for="ph_zip">Physical Zip</label></td>
                                        <td><input type="text" name="ph_zip" class="form-control" placeholder="Physical Zip" id="ph_zip" value="<?php echo $result['phys_zip']; ?>"></td>
                                    </tr>
                                    <tr style="height: 5px;">
                                        <td colspan="11"></td>
                                    </tr>
                                    <tr>
                                        <td><label for="email_address">Email Address</label></td>
                                        <td><input type="text" name="email_address" class="form-control pull-right" placeholder="Email Address" id="email_address" value="<?php echo $result['global_email']; ?>"></td>
                                        <td><label for="cell_phone">Cell Phone</label></td>
                                        <td><input type="text" name="cell_phone" class="form-control" placeholder="Cell Phone" id="cell_phone" value="<?php echo $result['global_cell']; ?>"></td>
                                    </tr>
                                </table>

                                <div class="col-md-12 text-md-right" style="margin: 10px 0;">
                                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs" id="save_so_<?php echo $result['sales_order_num']; ?>">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php echo "</div></td>";
                echo "</tr>";
                /** END EDIT SO DISPLAY */
                /** END LISTING OF SO'S */
            }
        }

        break;
    case "gen_json":
        $qry = $dbconn->query("SELECT * FROM customer WHERE sales_order_num LIKE 
            '%$find%' OR project LIKE '%$find%' OR dealer_code LIKE '%$find%'
            OR account_type LIKE '%$find%' OR dealer_contractor LIKE '%$find%' OR project_manager LIKE '%$find%'
            ORDER BY sales_order_num DESC");

        $return = array();

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $qry2 = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['sales_order_num']}'");

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
                        $bracketPri['customs'] = ($result2['custom_bracket_priority'] < $bracketPri['customs']) ? $result2['custom_bracket_priority'] : $bracketPri['customs'];
                    }

                    if(in_array("1", $bracketPri, true)) {
                        $soColor = "job-color-red";
                    } elseif(in_array("2", $bracketPri, true)) {
                        $soColor = "job-color-orange";
                    } elseif(in_array("3", $bracketPri, true)) {
                        $soColor = "job-color-yellow";
                    }
                }

                $dealer_prefix = substr($result['dealer_code'], 0,3);

                $dealer_qry = $dbconn->query("SELECT dealer_name FROM dealers WHERE dealer_id LIKE '%$dealer_prefix%'");
                $dealer = $dealer_qry->fetch_assoc();

                $account_type = ($result['account_type'] === 'R') ? "Retail" : "Wholesale";

                $return[] = ['id'=>$result['sales_order_num'], 'sales_order_num'=>$result['sales_order_num'], 'purchase_order'=>$result['project'], 'salesperson'=>$result['salesperson'], 'dealer_contractor'=>"{$result['dealer_code']}: {$dealer['dealer_name']}", 'account_type'=>$account_type, 'project_mgr_contact'=>$result['project_manager']];
            }
        }

        echo json_encode($return);

        break;
    default:
        die();
        break;
}