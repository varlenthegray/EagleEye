<?php
require_once ("../../includes/header_start.php");

$find = sanitizeInput($_REQUEST['find']);
$search = sanitizeInput($_REQUEST['search'], $dbconn);

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

    return ((bool)$room[$bracket . "_published"]) ? TRUE : FALSE;
}

function displayBracketOpsMgmt($bracket, $room, $individual_bracket) {
    global $dbconn;

    $bracket_def = null;

    switch($bracket) {
        case 'Sales':
            $bracket_def = 'sales_bracket';
            break;

        case 'Sample':
            $bracket_def = 'sample_bracket';
            break;

        case 'Pre-Production':
            $bracket_def = 'preproduction_bracket';
            break;

        case 'Drawer & Doors':
            $bracket_def = 'doordrawer_bracket';
            break;

        case 'Main':
            $bracket_def = 'main_bracket';
            break;

        case 'Custom':
            $bracket_def = 'custom_bracket';
            break;

        case 'Shipping':
            $bracket_def = 'shipping_bracket';
            break;

        case 'Installation':
            $bracket_def = 'install_bracket';
            break;

        default:
            $bracket_def = null;
    }

    $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE ORDER BY op_id ASC");

    $left_info = '';
    $right_info = '';

    while($op = $op_qry->fetch_assoc()) {
        $op_room_id = "op_{$op['id']}_room_{$room['id']}";

        if(in_array($op['id'], $individual_bracket)) {
            if($op['id'] === $room[$bracket_def]) {
                $selected = "checked='checked'";
            } else {
                $selected = '';
            }

            if((int)substr($op['op_id'], -2) !== 98) {
                $deactivate = "<span class=\"pull-right cursor-hand text-md-center deactivate_op\" data-opid=\"{$op['id']}\" data-roomid=\"{$room['id']}\" data-soid=\"{$room['so_parent']}\"> <i class=\"fa fa-arrow-circle-right\" style=\"width: 18px;\"></i> </button>";
            } else {
                $deactivate = null;
            }

            $left_info .= <<<HEREDOC
            <li class="active_ops_{$room['id']}" data-opnum="{$op['op_id']}" data-opid="{$op['id']}" data-roomid="{$room['id']}">
                <input type="radio" name="{$bracket_def}" id="$op_room_id" value="{$op['id']}" $selected>
                <label for="$op_room_id">{$op['op_id']}-{$op['job_title']}</label>
                $deactivate
            </li>
HEREDOC;
        } else {
            $right_info .= <<<HEREDOC
                <li class="inactive_ops_{$room['id']}" data-opnum="{$op['op_id']}" data-opid="{$op['id']}" data-roomid="{$room['id']}">
                    <span class="pull-left cursor-hand activate_op" style="height:18px;width:18px;" data-opid="{$op['id']}" data-roomid="{$room['id']}" data-soid="{$room['so_parent']}"> <i class="fa fa-arrow-circle-left pull-left" style="margin:5px;"></i></span>
                    {$op['op_id']}-{$op['job_title']}
                </li>
HEREDOC;
        }
    }
    ?>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 custom_ul" style="border-right: 2px solid #000;">
                <h3 class="text-md-center">Active</h3>
                <ul class="radio" class="activeops_<?php echo "{$room['id']}"; ?>" id="activeops_<?php echo "{$room['id']}_$bracket_def"; ?>" data-bracket="<?php echo $bracket_def; ?>">
                    <?php echo $left_info; ?>
                </ul>
            </div>

            <div class="col-md-6 custom_ul">
                <h3 class="text-md-center">Inactive</h3>
                <ul style="padding: 0;" class="inactiveops_<?php echo "{$room['id']}"; ?>" id="inactiveops_<?php echo "{$room['id']}_$bracket_def"; ?>" data-bracket="<?php echo $bracket_def; ?>">
                    <?php echo $right_info; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

switch ($search) {
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
        $qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '$find'");

        if($qry->num_rows > 0) {
            $result = $qry->fetch_assoc();

            echo json_encode($result);
        } else {
            echo "no results";
        }

        break;
    case "general":
        $qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num LIKE 
            '%$find%' OR project LIKE '%$find%' OR contractor_dealer_code LIKE '%$find%'
            OR project_mgr LIKE '%$find%' ORDER BY so_num DESC");

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $qry2 = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['so_num']}'");

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

                /** BEGIN LISTING OF SO'S */
                echo "  <tr class='cursor-hand' id='show_room_{$result['so_num']}'>";
                echo "    <td width='26px'><button class='btn waves-effect btn-primary pull-right' id='edit_so_{$result['so_num']}'> <i class='zmdi zmdi-edit'></i> </button></td>";
                echo "    <td>{$result['so_num']}</td>";
                echo "    <td>{$result['project']}</td>";
                echo "    <td>{$result['salesperson']}</td>";
                echo "    <td>{$result['contractor_dealer_code']}: {$dealer['dealer_name']}</td>";
                echo "    <td>{$result['project_mgr']}</td>";
                echo "  </tr>";

                /** BEGIN ROOM INFORMATION */
                echo "  <tr id='tr_room_{$result['so_num']}'>";
                echo "    <td colspan='8'><div id='div_room_{$result['so_num']}'>";?>

                <div class="col-md-12">
                    <div class="row">
                        <table class="table pull-right" style="width:99%">
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
                            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['so_num']}' ORDER BY room, iteration ASC");

                            if($room_qry->num_rows > 0) {
                                while($room = $room_qry->fetch_assoc()) {
                                    $individual_bracket = json_decode($room['individual_bracket_buildout']);

                                    $room_name = "{$room['room']}{$room['iteration']}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}: {$room['room_name']}";

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

                                    $tab = ($room['iteration'] > 1.01) ? "<div class='pull-left' style='width:15px;'>&nbsp</div>" : null;

                                    $sales_published_display = (checkPublished('sales')) ? "<td class='$salesPriority'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $salesBracketName</td>" : "<td>---</td>";
                                    $sample_published_display = (checkPublished('sample')) ? "<td class='$samplePriority'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $sampleBracketName</td>" : "<td>---</td>";
                                    $preprod_published_display = (checkPublished('preproduction')) ? "<td class='$preprodPriority'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $preprodBracketName</td>" : "<td>---</td>";
                                    $door_published_display = (checkPublished('doordrawer')) ? "<td class='$doorPriority'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $doorBrackettName</td>" : "<td>---</td>";
                                    $main_published_display = (checkPublished('main')) ? "<td class='$mainPriority'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $mainBracketName</td>" : "<td>---</td>";
                                    $customs_published_display = (checkPublished('custom')) ? "<td class='$customsPriority'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $customsBracketName</td>" : "<td>---</td>";
                                    $shipping_published_display = (checkPublished('shipping')) ? "<td class='$shippingPriority'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $shippingBracketName</td>" : "<td>---</td>";
                                    $install_published_display = (checkPublished('install_bracket')) ? "<td class='$installPriority'><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $installBracketName</td>" : "<td>---</td>";

                                    echo "<tr class='cursor-hand' id='manage_bracket_{$room['id']}'>";
                                    echo "  <td style='width:55px;'><button class='btn waves-effect btn-primary' id='show_single_room_{$room['id']}'><i class='zmdi zmdi-edit'></i></button> <button class='btn waves-effect btn-primary' id='show_vin_room_{$room['id']}'><i class='zmdi zmdi-developer-board'></i></button></td>";
                                    echo "  <td>{$tab}{$room_name}</td>";
                                    echo "  $sales_published_display";
                                    echo "  $sample_published_display";
                                    echo "  $preprod_published_display";
                                    echo "  $door_published_display";
                                    echo "  $main_published_display";
                                    echo "  $customs_published_display";
                                    echo "  $shipping_published_display";
                                    echo "  $install_published_display";
                                    echo "</tr>";

                                    /** BEGIN SINGLE ROOM DISPLAY */
                                    echo "<tr id='tr_single_room_{$room['id']}' style='display: none;'>";
                                    echo "  <td colspan='10'><div id='div_single_room_{$room['id']}' style='display: none;'>";

                                    $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '%{$result['dealer_code']}%' ORDER BY dealer_id ASC");
                                    $dealer = $dealer_qry->fetch_assoc();
                                    ?>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <form id="room_edit_<?php echo $room['id']; ?>">
                                                <div class="col-md-12">
                                                    <div class="col-md-3">
                                                        <form>
                                                            <table width="100%" class="table table-custom-nb">
                                                                <tr>
                                                                    <td><label for="dealer_code">Dealer Code</label></td>
                                                                    <td>
                                                                        <select class="form-control dealer_code" name="dealer_code" readonly>
                                                                            <?php

                                                                            $dealers_qry = $dbconn->query("SELECT * FROM dealers");

                                                                            while($dealers = $dealers_qry->fetch_assoc()) {
                                                                                $selected = ($dealers['dealer_id'] === $result['dealer_code']) ? "selected" : "";
                                                                                echo "<option value='{$dealers['id']}' $selected>{$dealers['dealer_id']} ({$dealers['contact']} of {$dealers['dealer_name']})</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="account_type">Account Type</label></td>
                                                                    <td>
                                                                        <select readonly class="form-control" id="edit_account_type_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="account_type" value="<?php echo $result['account_type']; ?>">
                                                                            <option value="R" <?php echo ($result['account_type'] === 'R') ? "selected" : null; ?>>Retail</option>
                                                                            <option value="W" <?php echo ($result['account_type'] === 'W') ? "selected" : null; ?>>Wholesale</option>
                                                                            <option value="D" <?php echo ($result['account_type'] === 'D') ? "selected" : null; ?>>Distribution</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="dealer">Dealer</label></td>
                                                                    <td><input readonly type="text" class="form-control" id="edit_dealer_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="dealer" placeholder="Dealer" value="<?php echo $dealer['dealer_name']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="contact">Contact</label></td>
                                                                    <td><input readonly type="text" class="form-control" id="edit_contact_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="contact" placeholder="Contact" value="<?php echo $dealer['contact']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="phone_number">Phone Number</label></td>
                                                                    <td><input readonly type="text" class="form-control" id="edit_phone_num_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="phone_number" placeholder="Phone Number" value="<?php echo $dealer['phone']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="email">Email</label></td>
                                                                    <td><input readonly type="text" class="form-control" id="edit_email_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="email" placeholder="Email" value="<?php echo $dealer['email']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="salesperson">Salesperson</label></td>
                                                                    <td><input readonly type="text" class="form-control" id="edit_salesperson_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="salesperson" placeholder="Salesperson" value="<?php echo $dealer['contact']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="shipping_addr">Shipping Address</label></td>
                                                                    <td><input readonly type="text" class="form-control" id="edit_shipping_addr_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="shipping_addr" placeholder="Shipping Address" value="<?php echo $dealer['shipping_address']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"><input readonly type="text" class="form-control pull-left" id="edit_city_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="city" style="width: 33.3%;" placeholder="City" value="<?php echo $dealer['physical_city']; ?>"><select readonly class="form-control pull-left" id="edit_state_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" style="width: 33.3%;" name="p_state">
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
                                                                        </select><input readonly type="text" class="form-control pull-left" id="edit_zip_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="zip" style="width: 33.3%;" placeholder="ZIP" value="<?php echo $dealer['physical_zip']; ?>"></td>
                                                                </tr>
                                                                <tr>
                                                                    <?php
                                                                        if(!empty($room['delivery_date'])) {
                                                                            switch ($room['days_to_ship']) {
                                                                                case 'G':
                                                                                    $status_color = "job-color-green";

                                                                                    break;
                                                                                case 'Y':
                                                                                    $status_color = "job-color-yellow";

                                                                                    break;
                                                                                case 'N':
                                                                                    $status_color = "job-color-orange";

                                                                                    break;
                                                                                case 'R':
                                                                                    $status_color = "job-color-red";

                                                                                    break;
                                                                                default:
                                                                                    $status_color = "job-color-green";

                                                                                    break;
                                                                            }
                                                                        } else {
                                                                            $status_color = null;
                                                                        }
                                                                    ?>
                                                                    <td><label for="delivery_date">Delivery Date</label></td>
                                                                    <td>
                                                                        <div class="input-group">
                                                                            <input type="text" class="form-control delivery_date <?php echo $status_color; ?>" id="edit_del_date_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="delivery_date" placeholder="Delivery Date" value="<?php echo (!empty($room['delivery_date'])) ? date("m/d/Y", $room['delivery_date']) : ""; ?>">
                                                                            <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                                                                        </div>

                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </form>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <table width="100%" class="table table-custom-nb">
                                                            <tr>
                                                                <td><label for="room">Room</label></td>
                                                                <td><input type="text" class="form-control" id="edit_room_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room" placeholder="Room" value="<?php echo $room['room']; ?>" readonly></td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="product_type">Product Type</label></td>
                                                                <td>
                                                                    <select class="form-control" id="edit_product_type_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="product_type" value="<?php echo $room['product_type']; ?>">
                                                                        <option value="C" <?php echo ($room['product_type'] === 'C') ? "selected" : null; ?>>Cabinet</option>
                                                                        <option value="L" <?php echo ($room['product_type'] === 'L') ? "selected" : null; ?>>Closet</option>
                                                                        <option value="S" <?php echo ($room['product_type'] === 'S') ? "selected" : null; ?>>Sample</option>
                                                                        <option value="D" <?php echo ($room['product_type'] === 'D') ? "selected" : null; ?>>Display</option>
                                                                        <option value="A" <?php echo ($room['product_type'] === 'A') ? "selected" : null; ?>>Add-on</option>
                                                                        <option value="W" <?php echo ($room['product_type'] === 'W') ? "selected" : null; ?>>Warranty</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="iteration">Iteration</label></td>
                                                                <td>
                                                                    <div class="input-group">
                                                                        <span class="input-group-addon cursor-hand add_iteration" data-roomid="<?php echo $room['id']; ?>" data-addto="sequence" data-iteration="<?php echo $room['iteration']; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add additional sequence"> <span class="zmdi zmdi-plus-1"></span> </span>
                                                                        <input type="text" class="form-control" id="edit_iteration_<?php echo $room['id']; ?>" name="iteration" placeholder="Iteration" value="<?php echo $room['iteration']; ?>" readonly>
                                                                        <span class="input-group-addon cursor-hand add_iteration" data-roomid="<?php echo $room['id']; ?>" data-addto="iteration" data-iteration="<?php echo $room['iteration']; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add additional iteration"> <span class="zmdi zmdi-plus-1"></span> </span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="order_status">Order Status</label></td>
                                                                <td>
                                                                    <select class="form-control" id="edit_order_status_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="order_status">
                                                                        <option value="#" <?php echo ($room['order_status'] === '#') ? "selected" : null; ?>>Quote</option>
                                                                        <option value="$" <?php echo ($room['order_status'] === '$') ? "selected" : null; ?>>Job</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="days_to_ship">Days to Ship</label></td>
                                                                <td>
                                                                    <select class="form-control days-to-ship" id="edit_days_to_ship_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="days_to_ship" data-type="edit" data-room="<?php echo $room['room']; ?>">
                                                                        <option value="G" <?php echo ($room['days_to_ship'] === 'G') ? "selected" : null; ?>>Green (34)</option>
                                                                        <option value="Y" <?php echo ($room['days_to_ship'] === 'Y') ? "selected" : null; ?>>Yellow (14)</option>
                                                                        <option value="N" <?php echo ($room['days_to_ship'] === 'N') ? "selected" : null; ?>>Orange (10)</option>
                                                                        <option value="R" <?php echo ($room['days_to_ship'] === 'R') ? "selected" : null; ?>>Red (5)</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><label for="room_name">Room Name</label></td>
                                                                <td><input type="text" class="form-control" id="edit_room_name_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room_name" placeholder="Room Name" value="<?php echo $room['room_name']; ?>"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <fieldset class="form-group">
                                                            <label for="room_notes">Room Notes</label>
                                                            <textarea class="form-control"  id="edit_room_notes_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room_notes" maxlength="65530" placeholder="Room Notes" rows="3"></textarea>
                                                        </fieldset>
                                                    </div>

                                                    <input type="hidden" name="sonum" value="<?php echo $result['so_num']; ?>">
                                                    <input type="hidden" name="room" value="<?php echo $room['room']; ?>">
                                                    <input type="hidden" name="roomid" value="<?php echo $room['id']; ?>">

                                                    <div class="col-md-12" style="margin: 10px 0;">
                                                        <button type="button" class="btn btn-primary waves-effect waves-light w-xs edit_room_save">Save</button>
                                                    </div>
                                                </div>
                                            </form>
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
                                                <div class="col-md-7 col-md-offset-1">
                                                    <form id="form_bracket_<?php echo $room['id']; ?>">
                                                        <table width="100%" class="bracket-adjustment-table">
                                                            <tr>
                                                                <td style="width: 49.8%;" class="bracket-border-top">
                                                                    <div class="row bracket-header-custom">
                                                                        <div class="col-md-8"><h5><label for="sales_bracket_adjustments_<?php echo $room['id']; ?>">Sales Bracket</label></h5></div>
                                                                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sales_published" value="1" id="sales_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['sales_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                    </div>
                                                                </td>
                                                                <td style="background-color:#eceeef;"></td>
                                                                <td style="width: 49.8%;" class="bracket-border-top">
                                                                    <div class="row bracket-header-custom">
                                                                        <div class="col-md-8"><h5><label for="sample_bracket_adjustments_<?php echo $room['id']; ?>">Sample Bracket</label></h5></div>
                                                                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sample_published" value="1" id="sample_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['sample_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bracket-border-bottom">
                                                                    <?php displayBracketOpsMgmt('Sales', $room, $individual_bracket); ?>
                                                                </td>
                                                                <td style="background-color: #eceeef;">&nbsp;</td>
                                                                <td class="bracket-border-bottom">
                                                                    <?php displayBracketOpsMgmt('Sample', $room, $individual_bracket); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bracket-border-top">
                                                                    <div class="row bracket-header-custom">
                                                                        <div class="col-md-8"><h5><label for="pre_prod_bracket_adjustments_<?php echo $room['id']; ?>">Pre-production Bracket</label></h5></div>
                                                                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="preprod_published" value="1" id="pre_prod_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['preproduction_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                    </div>
                                                                </td>
                                                                <td style="background-color: #eceeef;">&nbsp;</td>
                                                                <td class="bracket-border-top">
                                                                    <div class="row bracket-header-custom">
                                                                        <div class="col-md-8"><h5><label for="door_drawer_bracket_adjustments_<?php echo $room['id']; ?>">Door/Drawer Bracket</label></h5></div>
                                                                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="doordrawer_published" value="1" id="doordrawer_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['doordrawer_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bracket-border-bottom">
                                                                    <?php displayBracketOpsMgmt('Pre-Production', $room, $individual_bracket); ?>
                                                                </td>
                                                                <td style="background-color: #eceeef;">&nbsp;</td>
                                                                <td class="bracket-border-bottom">
                                                                    <?php displayBracketOpsMgmt('Drawer & Doors', $room, $individual_bracket); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bracket-border-top">
                                                                    <div class="row bracket-header-custom">
                                                                        <div class="col-md-8"><h5><label for="main_bracket_adjustments_<?php echo $room['id']; ?>">Main Bracket</label></h5></div>
                                                                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="main_published" value="1" id="main_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['main_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                    </div>
                                                                </td>
                                                                <td style="background-color: #eceeef;">&nbsp;</td>
                                                                <td class="bracket-border-top">
                                                                    <div class="row bracket-header-custom">
                                                                        <div class="col-md-8"><h5><label for="custom_bracket_adjustments_<?php echo $room['id']; ?>">Custom Bracket</label></h5></div>
                                                                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="custom_published" value="1" id="custom_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['custom_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bracket-border-bottom">
                                                                    <?php displayBracketOpsMgmt('Main', $room, $individual_bracket); ?>
                                                                </td>
                                                                <td style="background-color: #eceeef;">&nbsp;</td>
                                                                <td class="bracket-border-bottom">
                                                                    <?php displayBracketOpsMgmt('Custom', $room, $individual_bracket); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bracket-border-top">
                                                                    <div class="row bracket-header-custom">
                                                                        <div class="col-md-8"><h5><label for="shipping_bracket_adjustments_<?php echo $room['id']; ?>">Shipping Bracket</label></h5></div>
                                                                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="shipping_published" value="1" id="shipping_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['shipping_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                    </div>
                                                                </td>
                                                                <td style="background-color: #eceeef;">&nbsp;</td>
                                                                <td class="bracket-border-top">
                                                                    <div class="row bracket-header-custom">
                                                                        <div class="col-md-8"><h5><label for="install_bracket_adjustments_<?php echo $room['id']; ?>">Install Bracket</label></h5></div>
                                                                        <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="install_published" value="1" id="install_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['install_bracket_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="bracket-border-bottom">
                                                                    <?php displayBracketOpsMgmt('Shipping', $room, $individual_bracket); ?>
                                                                </td>
                                                                <td style="background-color: #eceeef;">&nbsp;</td>
                                                                <td class="bracket-border-bottom">
                                                                    <?php displayBracketOpsMgmt('Installation', $room, $individual_bracket); ?>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </form>
                                                </div>
                                            </div>

                                            <?php if($_SESSION['userInfo']['account_type'] <= 4) { ?><button type="button" class="btn btn-primary waves-effect waves-light w-xs save_bracket floating-button-left" data-roomid="<?php echo $room['id']; ?>">Save</button><?php } ?>
                                        </div>

                                        <?php echo "</div></td>";
                                        echo "</tr>";
                                        /** END DISPLAY OF MANAGE BRACKET */

                                        /** BEGIN DISPLAY OF ADD ITERATION */
                                        echo "<tr id='tr_iteration_{$room['id']}' style='display: none;'>";
                                        echo "  <td colspan='10'><div id='div_iteration_{$room['id']}' style='display: none;'>";

                                        $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '%{$result['contractor_dealer_code']}%' ORDER BY dealer_id ASC");
                                        $dealer = $dealer_qry->fetch_assoc();
                                        ?>

                                        <div class="col-md-12">
                                            <div class="row">
                                                <form id="room_add_iteration_<?php echo $room['id']; ?>">
                                                    <div class="col-md-12">
                                                        <h4>Adding Iteration...</h4>

                                                        <div class="col-md-3">
                                                            <form>
                                                                <table width="100%" class="table table-custom-nb">
                                                                    <tr>
                                                                        <td><label for="dealer_code">Dealer Code</label></td>
                                                                        <td>
                                                                            <select class="form-control dealer_code" name="dealer_code" readonly>
                                                                                <?php

                                                                                $dealers_qry = $dbconn->query("SELECT * FROM dealers");

                                                                                while($dealers = $dealers_qry->fetch_assoc()) {
                                                                                    $selected = ($dealers['dealer_id'] === $result['dealer_code']) ? "selected" : "";
                                                                                    echo "<option value='{$dealers['id']}' $selected>{$dealers['dealer_id']} ({$dealers['contact']} of {$dealers['dealer_name']})</option>";
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="account_type">Account Type</label></td>
                                                                        <td>
                                                                            <select readonly class="form-control" id="edit_account_type_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="account_type" value="<?php echo $result['account_type']; ?>">
                                                                                <option value="R" <?php echo ($result['account_type'] === 'R') ? "selected" : null; ?>>Retail</option>
                                                                                <option value="W" <?php echo ($result['account_type'] === 'W') ? "selected" : null; ?>>Wholesale</option>
                                                                                <option value="D" <?php echo ($result['account_type'] === 'D') ? "selected" : null; ?>>Distribution</option>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="dealer">Dealer</label></td>
                                                                        <td><input readonly type="text" class="form-control" id="edit_dealer_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="dealer" placeholder="Dealer" value="<?php echo $dealer['dealer_name']; ?>"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="contact">Contact</label></td>
                                                                        <td><input readonly type="text" class="form-control" id="edit_contact_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="contact" placeholder="Contact" value="<?php echo $dealer['contact']; ?>"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="phone_number">Phone Number</label></td>
                                                                        <td><input readonly type="text" class="form-control" id="edit_phone_num_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="phone_number" placeholder="Phone Number" value="<?php echo $dealer['phone']; ?>"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="email">Email</label></td>
                                                                        <td><input readonly type="text" class="form-control" id="edit_email_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="email" placeholder="Email" value="<?php echo $dealer['email']; ?>"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="salesperson">Salesperson</label></td>
                                                                        <td><input readonly type="text" class="form-control" id="edit_salesperson_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="salesperson" placeholder="Salesperson" value="<?php echo $dealer['contact']; ?>"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="shipping_addr">Shipping Address</label></td>
                                                                        <td><input readonly type="text" class="form-control" id="edit_shipping_addr_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="shipping_addr" placeholder="Shipping Address" value="<?php echo $dealer['shipping_address']; ?>"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input readonly type="text" class="form-control pull-left" id="edit_city_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="city" style="width: 33.3%;" placeholder="City" value="<?php echo $dealer['physical_city']; ?>"><select readonly class="form-control pull-left" id="edit_state_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" style="width: 33.3%;" name="p_state">
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
                                                                            </select><input readonly type="text" class="form-control pull-left" id="edit_zip_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="zip" style="width: 33.3%;" placeholder="ZIP" value="<?php echo $dealer['physical_zip']; ?>"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <?php
                                                                        if(!empty($room['delivery_date'])) {
                                                                            switch ($room['days_to_ship']) {
                                                                                case 'Green':
                                                                                    $status_color = "job-color-green";

                                                                                    break;
                                                                                case 'Yellow':
                                                                                    $status_color = "job-color-yellow";

                                                                                    break;
                                                                                case 'Orange':
                                                                                    $status_color = "job-color-orange";

                                                                                    break;
                                                                                case 'Red':
                                                                                    $status_color = "job-color-red";

                                                                                    break;
                                                                                default:
                                                                                    $status_color = "job-color-green";

                                                                                    break;
                                                                            }
                                                                        } else {
                                                                            $status_color = null;
                                                                        }
                                                                        ?>
                                                                        <td><label for="delivery_date">Delivery Date</label></td>
                                                                        <td>
                                                                            <div class="input-group">
                                                                                <input type="text" class="form-control delivery_date <?php echo $status_color; ?>" id="iteration_del_date_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="delivery_date" placeholder="Delivery Date" value="<?php echo (!empty($room['delivery_date'])) ? date("m/d/Y", $room['delivery_date']) : ""; ?>">
                                                                                <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </form>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <table width="100%" class="table table-custom-nb">
                                                                <tr>
                                                                    <td><label for="room">Room</label></td>
                                                                    <td><input type="text" class="form-control" id="edit_room_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room" placeholder="Room" value="<?php echo $room['room']; ?>" readonly></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="product_type">Product Type</label></td>
                                                                    <td>
                                                                        <select class="form-control" id="edit_product_type_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="product_type" value="<?php echo $room['product_type']; ?>">
                                                                            <option value="C">Cabinet</option>
                                                                            <option value="L">Closet</option>
                                                                            <option value="S">Sample</option>
                                                                            <option value="D">Display</option>
                                                                            <option value="A">Add-on</option>
                                                                            <option value="W">Warranty</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="iteration">Iteration</label></td>
                                                                    <td>
                                                                        <div class="input-group">
                                                                            <span class="input-group-addon cursor-hand add_iteration" data-roomid="<?php echo $room['name']; ?>" data-addto="sequence" data-iteration="<?php echo $room['iteration']; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add additional sequence"> <span class="zmdi zmdi-plus-1"></span> </span>
                                                                            <input type="text" class="form-control" id="next_iteration_<?php echo $room['id']; ?>" name="iteration" placeholder="Iteration" value="<?php echo $room['iteration']; ?>" readonly>
                                                                            <span class="input-group-addon cursor-hand add_iteration" data-roomid="<?php echo $room['name']; ?>" data-addto="iteration" data-iteration="<?php echo $room['iteration']; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add additional iteration"> <span class="zmdi zmdi-plus-1"></span> </span>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="order_status">Order Status</label></td>
                                                                    <td>
                                                                        <select class="form-control" id="edit_order_status_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="order_status">
                                                                            <option value="#" <?php echo ($room['order_status'] === '#') ? "selected" : null; ?>>Quote</option>
                                                                            <option value="$" <?php echo ($room['order_status'] === '$') ? "selected" : null; ?>>Job</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="days_to_ship">Days to Ship</label></td>
                                                                    <td>
                                                                        <select class="form-control days-to-ship" id="edit_days_to_ship_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="days_to_ship" data-type="iteration" data-room="<?php echo $room['room']; ?>">
                                                                            <option value="G" <?php echo ($room['days_to_ship'] === 'Green') ? "selected" : null; ?>>Green (34)</option>
                                                                            <option value="Y" <?php echo ($room['days_to_ship'] === 'Yellow') ? "selected" : null; ?>>Yellow (14)</option>
                                                                            <option value="N" <?php echo ($room['days_to_ship'] === 'Orange') ? "selected" : null; ?>>Orange (10)</option>
                                                                            <option value="R" <?php echo ($room['days_to_ship'] === 'Red') ? "selected" : null; ?>>Red (5)</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="room_name">Room Name</label></td>
                                                                    <td><input type="text" class="form-control" id="edit_room_name_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room_name" placeholder="Room Name" value="<?php echo $room['room_name']; ?>"></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <fieldset class="form-group">
                                                                <label for="room_notes">Room Notes</label>
                                                                <textarea class="form-control"  id="edit_room_notes_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room_notes" maxlength="65530" placeholder="Room Notes" rows="3"></textarea>
                                                            </fieldset>
                                                        </div>

                                                        <input type="hidden" name="sonum" value="<?php echo $result['so_num']; ?>">
                                                        <input type="hidden" name="room" value="<?php echo $room['room']; ?>">
                                                        <input type="hidden" name="roomid" value="<?php echo $room['id']; ?>">

                                                        <div class="col-md-12 text-md-right" style="margin: 10px 0;">
                                                            <button type="button" class="btn btn-primary waves-effect waves-light w-xs iteration_save">Save</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <?php echo "</div></td>";
                                        echo "</tr>";
                                        /** END DISPLAY OF ADD ITERATION */

                                        /** BEGIN DISPLAY OF VIN MANAGEMENT */
                                        echo "<tr id='tr_vin_{$room['id']}' style='display: none;'>";
                                        echo "  <td colspan='10'><div id='div_vin_{$room['id']}' style='display: none;'>";
                                        ?>

                                        <div class="col-md-12">
                                            <form id="vin_contents_<?php echo $room['id']; ?>" class="vin-codes">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <input type="hidden" name="vin_so_num_<?php echo $room['id']; ?>" value="<?php echo $room['so_parent']; ?>" id="vin_so_num_<?php echo $room['id']; ?>" />
                                                        <input type="hidden" name="vin_room_<?php echo $room['id']; ?>" value="<?php echo $room['room']; ?>" id="vin_room_<?php echo $room['id']; ?>" />
                                                        <input type="hidden" name="vin_iteration_<?php echo $room['id']; ?>" value="<?php echo $room['iteration']; ?>" id="vin_iteration_<?php echo $room['id']; ?>" />
                                                        <input type="hidden" name="vin_product_type_<?php echo $room['id']; ?>" value="<?php echo $room['product_type']; ?>" id="vin_product_type_<?php echo $room['id']; ?>" />
                                                        <input type="hidden" name="vin_order_status_<?php echo $room['id']; ?>" value="<?php echo $room['order_status']; ?>" id="vin_order_status_<?php echo $room['id']; ?>" />
                                                        <input type="hidden" name="vin_days_to_ship_<?php echo $room['id']; ?>" value="<?php echo $room['days_to_ship']; ?>" id="vin_days_to_ship_<?php echo $room['id']; ?>" />
                                                        <input type="hidden" name="vin_dealer_code_<?php echo $room['id']; ?>" value="<?php echo $result['contractor_dealer_code']; ?>" id="vin_dealer_code_<?php echo $room['id']; ?>" />

                                                        <table width="100%" class="table table-custom-nb label-right">
                                                            <tr>
                                                                <td style="width:30px;"><label for="notes">Notes</label></td>
                                                                <td><input tabindex="1" type="text" name="notes_<?php echo $room['id']; ?>" id="notes_<?php echo $room['id']; ?>" placeholder="Notes..." class="form-control" maxlength="300"value="<?php echo $room['vin_notes']; ?>"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <table width="100%" class="custom-table label-right">
                                                            <tr>
                                                                <td colspan="2" class="text-md-center custom-border-1px-tlr"><h5>Door, Drawer & Hardwood</h5></td>
                                                                <td colspan="2" class="text-md-center custom-border-1px-tr"><h5>Finish</h5></td>
                                                                <td colspan="2" class="text-md-center custom-border-none"><h5>Carcass Exterior</h5></td>
                                                                <td colspan="2" class="text-md-center custom-border-none"><h5>Carcass Interior</h5></td>
                                                                <td colspan="2" class="text-md-center custom-border-none"><h5>Drawer Boxes</h5></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-left"><label for="species_grade_<?php echo $room['id']; ?>">Species</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="2" name="species_grade_<?php echo $room['id']; ?>" id="species_grade_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'species_grade'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['species_grade']) {
                                                                                $species_grade_selected = "selected";
                                                                            } else {
                                                                                $species_grade_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $species_grade_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="finish_type_<?php echo $room['id']; ?>">Type</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="10" name="finish_type_<?php echo $room['id']; ?>" id="finish_type_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'finish_type'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['finish_type']) {
                                                                                $finish_type_selected = "selected";
                                                                            } else {
                                                                                $finish_type_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $finish_type_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="construction_method_<?php echo $room['id']; ?>">Construction Method</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="18" name="construction_method_<?php echo $room['id']; ?>" id="construction_method_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'construction_method'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['construction_method']) {
                                                                                $construction_method_selected = "selected";
                                                                            } else {
                                                                                $construction_method_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $construction_method_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="carcass_interior_species_<?php echo $room['id']; ?>">Species</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="24" name="carcass_interior_species_<?php echo $room['id']; ?>" id="carcass_interior_species_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_interior_species'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['carcass_interior_species']) {
                                                                                $carcass_interior_species_selected = "selected";
                                                                            } else {
                                                                                $carcass_interior_species_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $carcass_interior_species_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="drawer_boxes_<?php echo $room['id']; ?>">Drawer Boxes</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="29" name="drawer_boxes_<?php echo $room['id']; ?>" id="drawer_boxes_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'drawer_boxes'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['drawer_boxes']) {
                                                                                $drawer_boxes_selected = "selected";
                                                                            } else {
                                                                                $drawer_boxes_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $drawer_boxes_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-left"><label for="door_design_<?php echo $room['id']; ?>">Door Design</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="3" name="door_design_<?php echo $room['id']; ?>" id="door_design_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'door_design'");

                                                                        if($segment['key'] === $room['construction_method']) {
                                                                            $door_design_selected = "selected";
                                                                        } else {
                                                                            $door_design_selected = null;
                                                                        }

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            echo "<option value='{$segment['key']}' $door_design_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="finish_code_<?php echo $room['id']; ?>">Code</label></td>
                                                                <td class="custom-border-1px-right"><input tabindex="11" type="text" class="form-control force-width" onchange="calcVin(<?php echo $room['id']; ?>)" name="finish_code_<?php echo $room['id']; ?>" id="finish_code_<?php echo $room['id']; ?>" placeholder="XXXX" value="S001" maxlength="4" ></td>
                                                                <td class="custom-border-none"><label for="carcass_exterior_species_<?php echo $room['id']; ?>">Species</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="19" name="carcass_exterior_species_<?php echo $room['id']; ?>" id="carcass_exterior_species_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_exterior_species'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['carcass_exterior_species']) {
                                                                                $carcass_exterior_species_selected = "selected";
                                                                            } else {
                                                                                $carcass_exterior_species_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $carcass_exterior_species_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="carcass_interior_finish_type_<?php echo $room['id']; ?>">Finish Type</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="25" name="carcass_interior_finish_type_<?php echo $room['id']; ?>" id="carcass_interior_finish_type_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_interior_finish_type'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['carcass_interior_finish_type']) {
                                                                                $carcass_interior_finish_type_selected = "selected";
                                                                            } else {
                                                                                $carcass_interior_finish_type_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $carcass_interior_finish_type_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-left"><label for="style_rail_width_<?php echo $room['id']; ?>">Style/Rail Width</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="4" name="style_rail_width_<?php echo $room['id']; ?>" id="style_rail_width_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'style_rail_width'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['style_rail_width']) {
                                                                                $style_rail_width_selected = "selected";
                                                                            } else {
                                                                                $style_rail_width_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $style_rail_width_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="sheen_<?php echo $room['id']; ?>">Sheen</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="12" name="sheen_<?php echo $room['id']; ?>" id="sheen_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'sheen'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['sheen']) {
                                                                                $sheen_selected = "selected";
                                                                            } else {
                                                                                $sheen_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $sheen_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="carcass_exterior_finish_type_<?php echo $room['id']; ?>">Finish Type</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="20" name="carcass_exterior_finish_type_<?php echo $room['id']; ?>" id="carcass_exterior_finish_type_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_exterior_finish_type'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['carcass_exterior_finish_type']) {
                                                                                $carcass_exterior_finish_type_selected = "selected";
                                                                            } else {
                                                                                $carcass_exterior_finish_type_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $carcass_exterior_finish_type_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="carcass_interior_finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                                                <td class="custom-border-none"><input tabindex="26" type="text" class="form-control force-width" onchange="calcVin(<?php echo $room['id']; ?>)" name="carcass_interior_finish_code_<?php echo $room['id']; ?>" id="carcass_interior_finish_code_<?php echo $room['id']; ?>" placeholder="XXXX" value="S001" ></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2" class="text-md-center custom-border-1px-tlr"><h5>Panel Raise</h5></td>
                                                                <td class="custom-border-none"><label for="glaze_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="13" name="glaze_<?php echo $room['id']; ?>" id="glaze_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'glaze'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['glaze']) {
                                                                                $glaze_selected = "selected";
                                                                            } else {
                                                                                $glaze_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $glaze_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="carcass_exterior_finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                                                <td class="custom-border-none"><input tabindex="21" type="text" class="form-control force-width" onchange="calcVin(<?php echo $room['id']; ?>)" name="carcass_exterior_finish_code_<?php echo $room['id']; ?>" id="carcass_exterior_finish_code_<?php echo $room['id']; ?>" placeholder="XXXX" value="S001" maxlength="4" ></td>
                                                                <td class="custom-border-none"><label for="carcass_interior_glaze_color_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="27" name="carcass_interior_glaze_color_<?php echo $room['id']; ?>" id="carcass_interior_glaze_color_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_interior_glaze_color'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['carcass_interior_glaze_color']) {
                                                                                $carcass_interior_glaze_color_selected = "selected";
                                                                            } else {
                                                                                $carcass_interior_glaze_color_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $carcass_interior_glaze_color_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-left"><label for="panel_raise_door_<?php echo $room['id']; ?>">Door</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="4" name="panel_raise_door_<?php echo $room['id']; ?>" id="panel_raise_door_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'panel_raise'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['panel_raise_door']) {
                                                                                $panel_raise_door_selected = "selected";
                                                                            } else {
                                                                                $panel_raise_door_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $panel_raise_door_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="14" name="glaze_technique_<?php echo $room['id']; ?>" id="glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'glaze_technique'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['glaze_technique']) {
                                                                                $glaze_technique_selected = "selected";
                                                                            } else {
                                                                                $glaze_technique_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $glaze_technique_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="carcass_exterior_glaze_color_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="22" name="carcass_exterior_glaze_color_<?php echo $room['id']; ?>" id="carcass_exterior_glaze_color_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_exterior_glaze_color'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['carcass_exterior_glaze_color']) {
                                                                                $carcass_exterior_glaze_color_selected = "selected";
                                                                            } else {
                                                                                $carcass_exterior_glaze_color_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $carcass_exterior_glaze_color_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="carcass_interior_glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="28" name="carcass_interior_glaze_technique_<?php echo $room['id']; ?>" id="carcass_interior_glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_interior_glaze_technique'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['carcass_interior_glaze_technique']) {
                                                                                $carcass_interior_glaze_technique_selected = "selected";
                                                                            } else {
                                                                                $carcass_interior_glaze_technique_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $carcass_interior_glaze_technique_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-left"><label for="panel_raise_sd_<?php echo $room['id']; ?>">Short Drawer</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="5" name="panel_raise_sd_<?php echo $room['id']; ?>" id="panel_raise_sd_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'panel_raise'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['panel_raise_sd']) {
                                                                                $panel_raise_sd_selected = "selected";
                                                                            } else {
                                                                                $panel_raise_sd_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $panel_raise_sd_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="antiquing_<?php echo $room['id']; ?>">Antiquing</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="15" name="antiquing_<?php echo $room['id']; ?>" id="antiquing_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'antiquing'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['antiquing']) {
                                                                                $antiquing_selected = "selected";
                                                                            } else {
                                                                                $antiquing_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $antiquing_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                <td class="custom-border-none">
                                                                    <select tabindex="23" name="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>" id="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_exterior_glaze_technique'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['carcass_exterior_glaze_technique']) {
                                                                                $carcass_exterior_glaze_technique_selected = "selected";
                                                                            } else {
                                                                                $carcass_exterior_glaze_technique_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $carcass_exterior_glaze_technique_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none">&nbsp;</td>
                                                                <td class="custom-border-none" colspan="2"><label for="sample_block_<?php echo $room['id']; ?>" class="text-md-right">Sample Block (5 1/4" x 6 1/8")</label></td>
                                                                <td class="custom-border-none"><input tabindex="30" type="text" class="form-control text-md-center" name="sample_block_<?php echo $room['id']; ?>" id="sample_block_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['sample_block_ordered']; ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-left"><label for="panel_raise_td_<?php echo $room['id']; ?>">Tall Drawer</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="6" name="panel_raise_td_<?php echo $room['id']; ?>" id="panel_raise_td_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'panel_raise'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['panel_raise_td']) {
                                                                                $panel_raise_td_selected = "selected";
                                                                            } else {
                                                                                $panel_raise_td_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $panel_raise_td_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none"><label for="worn_edges_<?php echo $room['id']; ?>">Worn Edges</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="16" name="worn_edges_<?php echo $room['id']; ?>" id="worn_edges_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'worn_edges'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['worn_edges']) {
                                                                                $worn_edges_selected = "selected";
                                                                            } else {
                                                                                $worn_edges_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $worn_edges_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none" colspan="3">&nbsp;</td>
                                                                <td class="custom-border-none" colspan="2"><label for="door_only_<?php echo $room['id']; ?>" class="text-md-right">Door Only (12" x 15")</label></td>
                                                                <td class="custom-border-none"><input tabindex="31" type="text" class="form-control text-md-center" name="door_only_<?php echo $room['id']; ?>" id="door_only_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['door_only_ordered']; ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-tl"><label for="edge_profile_<?php echo $room['id']; ?>">Edge Profile</label></td>
                                                                <td class="custom-border-1px-tr">
                                                                    <select tabindex="7" name="edge_profile_<?php echo $room['id']; ?>" id="edge_profile_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'edge_profile'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['edge_profile']) {
                                                                                $edge_profile_selected = "selected";
                                                                            } else {
                                                                                $edge_profile_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $edge_profile_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-1px-bottom"><label for="distress_level_<?php echo $room['id']; ?>">Distress Level</label></td>
                                                                <td class="custom-border-1px-rb">
                                                                    <select tabindex="17" name="distress_level_<?php echo $room['id']; ?>" id="distress_level_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'distress_level'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['distress_level']) {
                                                                                $distress_level_selected = "selected";
                                                                            } else {
                                                                                $distress_level_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $distress_level_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none" colspan="3">&nbsp;</td>
                                                                <td class="custom-border-none" colspan="2"><label for="door_only_<?php echo $room['id']; ?>" class="text-md-right">Door & Drawer (15 1/2" x 23 1/2")</label></td>
                                                                <td class="custom-border-none"><input tabindex="32" type="text" class="form-control text-md-center" name="door_drawer_<?php echo $room['id']; ?>" id="door_drawer_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['door_drawer_ordered']; ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-left"><label for="framing_bead_<?php echo $room['id']; ?>">Framing Bead</label></td>
                                                                <td class="custom-border-1px-right">
                                                                    <select tabindex="8" name="framing_bead_<?php echo $room['id']; ?>" id="framing_bead_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'framing_bead'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['framing_bead']) {
                                                                                $framing_bead_selected = "selected";
                                                                            } else {
                                                                                $framing_bead_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $framing_bead_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td class="custom-border-none" colspan="2">&nbsp;</td>
                                                                <td class="custom-border-none" colspan="3">&nbsp;</td>
                                                                <td class="custom-border-none" colspan="2"><label for="inset_square_<?php echo $room['id']; ?>" class="text-md-right">Inset Square (15 1/2" x 23 1/2")</label></td>
                                                                <td class="custom-border-none"><input tabindex="33" type="text" class="form-control text-md-center" name="inset_square_<?php echo $room['id']; ?>" id="inset_square_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['inset_square_ordered']; ?>"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="custom-border-1px-lb"><label for="framing_options_<?php echo $room['id']; ?>">Framing Options</label></td>
                                                                <td class="custom-border-1px-rb">
                                                                    <select tabindex="9" name="framing_options_<?php echo $room['id']; ?>" id="framing_options_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                        <?php
                                                                        $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'framing_options'");

                                                                        while($segment = $segment_qry->fetch_assoc()) {
                                                                            if($segment['key'] === $room['framing_options']) {
                                                                                $framing_options_selected = "selected";
                                                                            } else {
                                                                                $framing_options_selected = null;
                                                                            }

                                                                            echo "<option value='{$segment['key']}' $framing_options_selected>{$segment['value']} ({$segment['key']})</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </td>
                                                                <td colspan="5" class="custom-border-none"><input tabindex="37" type="text" class="form-control" name="vin_code_<?php echo $room['id']; ?>" id="vin_code_<?php echo $room['id']; ?>" placeholder="VIN Code" value="<?php echo $room['vin_code']; ?>" style="width:50%;margin-left:25%;" /></td>
                                                                <td colspan="2" class="custom-border-none"><label for="inset_beaded_<?php echo $room['id']; ?>" class="text-md-right">Inset Beaded (16 1/2" x 23 1/2")</label></td>
                                                                <td class="custom-border-none"><input tabindex="34" type="text" class="form-control text-md-center" name="inset_beaded_<?php echo $room['id']; ?>" id="inset_beaded_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['inset_beaded_ordered']; ?>"></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 col-md-offset-3"> </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12" style="margin:12px;">
                                                        <button tabindex="35" type="button" class="btn btn-primary waves-effect waves-light w-sm hidden-print create-vin" id="<?php echo $room['id']; ?>">Save</button>
                                                        <button tabindex="36" type="button" class="btn btn-primary waves-effect waves-light w-sm hidden-print print-sample" id="<?php echo $room['id']; ?>">Print Sample Request</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <?php echo "</div></td>";
                                        echo "</tr>";
                                        /** END DISPLAY OF VIN MANAGEMENT */
                                    /** END SINGLE ROOM DISPLAY */
                                }
                            }

                            /** BEGIN DISPLAY OF ADD SINGLE ROOM */
                            echo "<tr class='cursor-hand add_room_trigger' data-sonum='{$result['so_num']}'>";
                            echo "  <td style='width: 26px;'><span class='btn btn-primary faux_button'><i class='zmdi zmdi-plus-1'></i></span></td>";
                            echo "  <td colspan='9' style='font-weight:bold;'>Add room</td>";
                            echo "</tr>";

                            /** BEGIN ADD SINGLE ROOM INFORMATION */
                            echo "<tr id='tr_add_single_room_info_{$result['so_num']}' style='display: none;'>";
                            echo "  <td colspan='9'><div id='div_add_single_room_info_{$result['so_num']}' style='display: none;'>";
                            ?>

                            <div class="col-md-12 add_room_info">
                                <div class="row">
                                    <div class="col-md-12">
                                        <form id="form_add_room_<?php echo $result['so_num']; ?>">
                                            <div class="col-md-4">
                                                    <table width="100%" class="table table-custom-nb">
                                                        <tr>
                                                            <td><label for="dealer_code">Dealer Code</label></td>
                                                            <td>
                                                                <select class="form-control dealer_code" name="dealer_code" readonly>
                                                                    <?php
                                                                    $dealers_qry = $dbconn->query("SELECT * FROM dealers");

                                                                    while($dealer = $dealers_qry->fetch_assoc()) {
                                                                        $selected = ($dealer['dealer_id'] === $result['dealer_code']) ? "selected" : "";
                                                                        echo "<option value='{$dealer['id']}' $selected>{$dealer['dealer_id']} ({$dealer['contact']} of {$dealer['dealer_name']})</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="account_type">Account Type</label></td>
                                                            <td>
                                                                <select class="form-control" name="account_type" id="add_room_account_type_<?php echo $result['so_num']; ?>" readonly>
                                                                    <option value="R">Retail</option>
                                                                    <option value="W">Wholesale</option>
                                                                    <option value="D">Distribution</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="dealer">Dealer</label></td>
                                                            <?php
                                                                $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '{$result['dealer_code']}%'");
                                                                $dealer = $dealer_qry->fetch_assoc();
                                                            ?>
                                                            <td><input type="text" class="form-control" name="dealer" placeholder="Dealer" value="<?php echo $dealer['dealer_name']; ?>" id="add_room_dealer_<?php echo $result['so_num']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="contact">Contact</label></td>
                                                            <td><input type="text" class="form-control" name="contact" placeholder="Contact" value="<?php echo $dealer['contact']; ?>" id="add_room_contact_<?php echo $result['so_num']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="phone_number">Phone Number</label></td>
                                                            <td><input type="text" class="form-control mask-phone" name="phone_number" placeholder="Phone Number" value="<?php echo $dealer['phone']; ?>" id="add_room_phone_num_<?php echo $result['so_num']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="email">Email</label></td>
                                                            <td><input type="text" class="form-control" name="email" placeholder="Email" value="<?php echo $dealer['email']; ?>" id="add_room_email_<?php echo $result['so_num']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="salesperson">Salesperson</label></td>
                                                            <td><input type="text" class="form-control" name="salesperson" placeholder="Salesperson" value="<?php echo $result['salesperson']; ?>" id="add_room_salesperson_<?php echo $result['so_num']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="shipping_addr">Shipping Address</label></td>
                                                            <td><input type="text" class="form-control" name="shipping_addr" placeholder="Shipping Address" value="<?php echo $dealer['shipping_address']; ?>" id="add_room_shipping_addr_<?php echo $result['so_num']; ?>" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2"><input type="text" class="form-control pull-left" name="city" style="width: 33.3%;" placeholder="City" value="<?php echo $dealer['shipping_city']; ?>" id="add_room_shipping_city_<?php echo $result['so_num']; ?>" readonly><select readonly class="form-control pull-left" style="width: 33.3%;" name="p_state" id="add_room_shipping_state_<?php echo $result['so_num']; ?>">
                                                                    <option value="AL">Alabama</option>
                                                                    <option value="AK">Alaska</option>
                                                                    <option value="AR">Arkansas</option>
                                                                    <option value="CA">California</option>
                                                                    <option value="CO">Colorado</option>
                                                                    <option value="CT">Connecticut</option>
                                                                    <option value="DE">Delaware</option>
                                                                    <option value="FL">Florida</option>
                                                                    <option value="GA">Georgia</option>
                                                                    <option value="HI">Hawaii</option>
                                                                    <option value="ID">Idaho</option>
                                                                    <option value="IL">Illinois</option>
                                                                    <option value="IN">Indiana</option>
                                                                    <option value="IA">Iowa</option>
                                                                    <option value="KS">Kansas</option>
                                                                    <option value="KY">Kentucky</option>
                                                                    <option value="LA">Louisiana</option>
                                                                    <option value="ME">Maine</option>
                                                                    <option value="MD">Maryland</option>
                                                                    <option value="MA">Massachusetts</option>
                                                                    <option value="MI">Michigan</option>
                                                                    <option value="MN">Minnesota</option>
                                                                    <option value="MS">Mississippi</option>
                                                                    <option value="MO">Missouri</option>
                                                                    <option value="MT">Montana</option>
                                                                    <option value="NE">Nebraska</option>
                                                                    <option value="NV">Nevada</option>
                                                                    <option value="NH">New Hampshire</option>
                                                                    <option value="NJ">New Jersey</option>
                                                                    <option value="NM">New Mexico</option>
                                                                    <option value="NY">New York</option>
                                                                    <option value="NC" selected>North Carolina</option>
                                                                    <option value="ND">North Dakota</option>
                                                                    <option value="OH">Ohio</option>
                                                                    <option value="OK">Oklahoma</option>
                                                                    <option value="OR">Oregon</option>
                                                                    <option value="PA">Pennsylvania</option>
                                                                    <option value="RI">Rhode Island</option>
                                                                    <option value="SC">South Carolina</option>
                                                                    <option value="SD">South Dakota</option>
                                                                    <option value="TN">Tennessee</option>
                                                                    <option value="TX">Texas</option>
                                                                    <option value="UT">Utah</option>
                                                                    <option value="VT">Vermont</option>
                                                                    <option value="VA">Virginia</option>
                                                                    <option value="WA">Washington</option>
                                                                    <option value="WV">West Virginia</option>
                                                                    <option value="WI">Wisconsin</option>
                                                                    <option value="WY">Wyoming</option>
                                                                </select><input readonly type="text" class="form-control pull-left mask-zip" name="zip" style="width: 33.3%;" placeholder="ZIP" value="<?php echo $dealer['shipping_zip']; ?>" id="add_room_shipping_zip_<?php echo $result['so_num']; ?>"></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="delivery_date">Delivery Date</label></td>
                                                            <td>
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control delivery_date_add job-color-green" id="delivery_date_add_<?php echo $result['so_num']; ?>" name="delivery_date" placeholder="Delivery Date" value="<?php echo calcDelDate("Green"); ?>">
                                                                    <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                            </div>
                                            <div class="col-md-3">
                                                <table width="100%" class="table table-custom-nb">
                                                    <tr>
                                                        <td><label for="room">Room</label></td>
                                                        <td>
                                                            <select class="form-control" name="room">
                                                                <?php
                                                                $letter = 'A';
                                                                $blacklist = ['I','O'];
                                                                $letter_series = [];

                                                                $blacklist_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['so_num']}'");

                                                                if($blacklist_qry->num_rows > 0) {
                                                                    while($blacklist_result = $blacklist_qry->fetch_assoc()) {
                                                                        if(!in_array($blacklist_result['room'], $blacklist)) {
                                                                            $blacklist[] = $blacklist_result['room'];
                                                                        }
                                                                    }
                                                                }

                                                                for($i = 1; $i <= 26; $i++) {
                                                                    $next_letter = $letter++;

                                                                    if(!in_array($next_letter, $blacklist)) {
                                                                        $letter_series[] = $next_letter;
                                                                    }
                                                                }

                                                                foreach($letter_series as $letter) {
                                                                    echo "<option value='$letter'>$letter</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><label for="product_type">Product Type</label></td>
                                                        <td>
                                                            <select class="form-control" name="product_type">
                                                                <option value="Cabinet">Cabinet</option>
                                                                <option value="Closet">Closet</option>
                                                                <option value="Sample">Sample</option>
                                                                <option value="Display">Display</option>
                                                                <option value="Add-on">Add-on</option>
                                                                <option value="Warranty">Warranty</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><label for="iteration">Iteration</label></td>
                                                        <td><input type="text" class="form-control" name="iteration" placeholder="Iteration" value="1.01" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td><label for="order_status">Order Status</label></td>
                                                        <td>
                                                            <select class="form-control" name="order_status">
                                                                <option value="#">Quote</option>
                                                                <option value="$">Job</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><label for="days_to_ship">Days to Ship</label></td>
                                                        <td>
                                                            <select class="form-control days-to-ship" name="days_to_ship" data-type="add" data-sonum="<?php echo $result['so_num']; ?>">
                                                                <option value="Green">Green (34)</option>
                                                                <option value="Yellow">Yellow (14)</option>
                                                                <option value="Orange">Orange (10)</option>
                                                                <option value="Red">Red (5)</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><label for="room_name">Room Name</label></td>
                                                        <td><input type="text" class="form-control" name="room_name" placeholder="Room Name"></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-3">
                                                <fieldset class="form-group">
                                                    <label for="room_notes">Room Notes</label>
                                                    <textarea class="form-control" name="room_notes" maxlength="65530" placeholder="Room Notes" rows="3" data-toggle="popover" data-placement="top" data-trigger="focus" title="" data-html="true" data-content="<table style='font-size: 9px;'>
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

                                            <div class="col-md-12" style="margin: 10px 0;">
                                                <button type="button" class="btn btn-primary waves-effect waves-light w-xs" id="add_room_save_<?php echo $result['so_num']; ?>" data-sonum="<?php echo $result['so_num']; ?>">Save</button>
                                            </div>

                                            <input type="hidden" name="sonum" value="<?php echo $result['so_num']; ?>">
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php
                            echo "</tr>";
                            /** END ADD SINGLE ROOM INFORMATION */
                            /** END DISPLAY OF ADD SINGLE ROOM */
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php echo "    </td>";
                echo "  </tr>";
                /** END ROOM INFORMATION */

                /** BEGIN EDIT SO DISPLAY */
                echo "<tr id='tr_edit_so_{$result['so_num']}' style='display: none;'>";
                echo "  <td colspan='9'><div id='div_edit_so_{$result['so_num']}' style='display: none;'>";

                $so_info_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id = '{$result['contractor_dealer_code']}'");
                $so_info = $so_info_qry->fetch_assoc();

                switch($so_info['account_type']) {
                    case 'R':
                        $atype = "Retail";
                        break;

                    case 'W':
                        $atype = "Wholesale";
                        break;

                    case "D":
                        $atype = "Distribution";
                        break;

                    default:
                        $atype = "Retail";
                        break;
                }
                ?>

                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-10">
                            <form id="form_so_<?php echo $result['so_num']; ?>">
                                <table width="100%" class="table table-custom-nb">
                                    <tr>
                                        <td><label for="dealer_code">Dealer Code</label></td>
                                        <td>
                                            <select name="dealer_code" id="dealer_code" class="form-control">
                                                <?php
                                                $dealer_qry = $dbconn->query("SELECT * FROM dealers");

                                                while($dealer = $dealer_qry->fetch_assoc()) {
                                                    $selected = ($dealer['dealer_id'] === $result['contractor_dealer_code']) ? "selected" : NULL;

                                                    echo "<option value='{$dealer['dealer_id']}' $selected>{$dealer['dealer_id']} ({$dealer['contact']})</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td><label for="project">Project</label></td>
                                        <td><input type="text" class="form-control" id="project" name="project" placeholder="Project" value="<?php echo $result['project']; ?>" /></td>
                                        <td><label for="contact_1">Contact 1</label></td>
                                        <td><input type="text" name="contact_1" class="form-control" placeholder="Contact 1" id="contact_1" value="<?php echo $result['contact1_name']; ?>"></td>
                                        <td><label for="contact_2">Contact 2</label></td>
                                        <td><input type="text" name="contact_2" class="form-control" placeholder="Contact 2" id="contact_2" value="<?php echo $result['contact2_name']; ?>"></td>
                                        <td><label for="physical_addr">Physical Address</label></td>
                                        <td><input type="text" name="physical_addr" class="form-control" placeholder="Physical Address" id="physical_addr" value="<?php echo $result['physical_addr']; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Account Type:</td>
                                        <td><?php echo $atype; ?></td>
                                        <td><label for="project_addr">Project Address</label></td>
                                        <td><input type="text" name="project_addr" class="form-control" placeholder="Project Address" id="project_addr" value="<?php echo $result['project_addr']; ?>"></td>
                                        <td><label for="cell_1">Cell Phone</label></td>
                                        <td><input type="text" name="cell_1" class="form-control mask-phone" placeholder="Cell Phone" id="cell_1" value="<?php echo $result['contact1_cell']; ?>"></td>
                                        <td><label for="cell_2">Cell Phone</label></td>
                                        <td><input type="text" name="cell_2" class="form-control mask-phone" placeholder="Cell Phone" id="cell_2" value="<?php echo $result['contact2_cell']; ?>"></td>
                                        <td colspan="2" style="width: 260px;"><table width="100%">
                                                <tr>
                                                    <td width="33.3%"><input type="text" name="ph_city" class="form-control" placeholder="City" id="ph_city" value="<?php echo $result['physical_city']; ?>"></td>
                                                    <td width="33.3%"><select class="form-control" id="ph_state" name="ph_state">
                                                            <option value="AL" <?php echo ($result['physical_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                                            <option value="AK" <?php echo ($result['physical_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                                            <option value="AR" <?php echo ($result['physical_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                                            <option value="CA" <?php echo ($result['physical_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                                            <option value="CO" <?php echo ($result['physical_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                                            <option value="CT" <?php echo ($result['physical_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                                            <option value="DE" <?php echo ($result['physical_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                                            <option value="FL" <?php echo ($result['physical_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                                            <option value="GA" <?php echo ($result['physical_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                                            <option value="HI" <?php echo ($result['physical_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                                            <option value="ID" <?php echo ($result['physical_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                                            <option value="IL" <?php echo ($result['physical_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                                            <option value="IN" <?php echo ($result['physical_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                                            <option value="IA" <?php echo ($result['physical_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                                            <option value="KS" <?php echo ($result['physical_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                                            <option value="KY" <?php echo ($result['physical_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                                            <option value="LA" <?php echo ($result['physical_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                                            <option value="ME" <?php echo ($result['physical_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                                            <option value="MD" <?php echo ($result['physical_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                                            <option value="MA" <?php echo ($result['physical_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                                            <option value="MI" <?php echo ($result['physical_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                                            <option value="MN" <?php echo ($result['physical_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                                            <option value="MS" <?php echo ($result['physical_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                                            <option value="MO" <?php echo ($result['physical_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                                            <option value="MT" <?php echo ($result['physical_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                                            <option value="NE" <?php echo ($result['physical_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                                            <option value="NV" <?php echo ($result['physical_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                                            <option value="NH" <?php echo ($result['physical_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                                            <option value="NJ" <?php echo ($result['physical_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                                            <option value="NM" <?php echo ($result['physical_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                                            <option value="NY" <?php echo ($result['physical_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                                            <option value="NC" <?php echo ($result['physical_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                                            <option value="ND" <?php echo ($result['physical_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                                            <option value="OH" <?php echo ($result['physical_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                                            <option value="OK" <?php echo ($result['physical_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                                            <option value="OR" <?php echo ($result['physical_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                                            <option value="PA" <?php echo ($result['physical_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                                            <option value="RI" <?php echo ($result['physical_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                                            <option value="SC" <?php echo ($result['physical_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                                            <option value="SD" <?php echo ($result['physical_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                                            <option value="TN" <?php echo ($result['physical_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                                            <option value="TX" <?php echo ($result['physical_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                                            <option value="UT" <?php echo ($result['physical_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                                            <option value="VT" <?php echo ($result['physical_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                                            <option value="VA" <?php echo ($result['physical_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                                            <option value="WA" <?php echo ($result['physical_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                                            <option value="WV" <?php echo ($result['physical_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                                            <option value="WI" <?php echo ($result['physical_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                                            <option value="WY" <?php echo ($result['physical_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                                                        </select></td>
                                                    <td width="33.3%"><input type="text" name="ph_zip" class="form-control mask-zip" placeholder="Zip" id="ph_zip" value="<?php echo $result['physical_zip']; ?>"></td>
                                                </tr>
                                            </table></td>
                                    </tr>
                                    <tr>
                                        <td>Dealer Name:</td>
                                        <td><?php echo $so_info['dealer_name']; ?></td>
                                        <td colspan="2" style="width: 260px;"><table width="100%">
                                                <tr>
                                                    <td width="33.3%"><input type="text" name="p_city" class="form-control" placeholder="City" id="p_city" value="<?php echo $result['project_city']; ?>"></td>
                                                    <td width="33.3%"><select class="form-control" id="p_state" name="p_state">
                                                            <option value="AL" <?php echo ($result['project_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                                            <option value="AK" <?php echo ($result['project_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                                            <option value="AR" <?php echo ($result['project_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                                            <option value="CA" <?php echo ($result['project_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                                            <option value="CO" <?php echo ($result['project_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                                            <option value="CT" <?php echo ($result['project_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                                            <option value="DE" <?php echo ($result['project_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                                            <option value="FL" <?php echo ($result['project_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                                            <option value="GA" <?php echo ($result['project_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                                            <option value="HI" <?php echo ($result['project_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                                            <option value="ID" <?php echo ($result['project_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                                            <option value="IL" <?php echo ($result['project_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                                            <option value="IN" <?php echo ($result['project_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                                            <option value="IA" <?php echo ($result['project_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                                            <option value="KS" <?php echo ($result['project_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                                            <option value="KY" <?php echo ($result['project_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                                            <option value="LA" <?php echo ($result['project_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                                            <option value="ME" <?php echo ($result['project_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                                            <option value="MD" <?php echo ($result['project_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                                            <option value="MA" <?php echo ($result['project_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                                            <option value="MI" <?php echo ($result['project_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                                            <option value="MN" <?php echo ($result['project_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                                            <option value="MS" <?php echo ($result['project_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                                            <option value="MO" <?php echo ($result['project_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                                            <option value="MT" <?php echo ($result['project_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                                            <option value="NE" <?php echo ($result['project_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                                            <option value="NV" <?php echo ($result['project_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                                            <option value="NH" <?php echo ($result['project_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                                            <option value="NJ" <?php echo ($result['project_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                                            <option value="NM" <?php echo ($result['project_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                                            <option value="NY" <?php echo ($result['project_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                                            <option value="NC" <?php echo ($result['project_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                                            <option value="ND" <?php echo ($result['project_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                                            <option value="OH" <?php echo ($result['project_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                                            <option value="OK" <?php echo ($result['project_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                                            <option value="OR" <?php echo ($result['project_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                                            <option value="PA" <?php echo ($result['project_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                                            <option value="RI" <?php echo ($result['project_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                                            <option value="SC" <?php echo ($result['project_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                                            <option value="SD" <?php echo ($result['project_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                                            <option value="TN" <?php echo ($result['project_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                                            <option value="TX" <?php echo ($result['project_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                                            <option value="UT" <?php echo ($result['project_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                                            <option value="VT" <?php echo ($result['project_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                                            <option value="VA" <?php echo ($result['project_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                                            <option value="WA" <?php echo ($result['project_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                                            <option value="WV" <?php echo ($result['project_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                                            <option value="WI" <?php echo ($result['project_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                                            <option value="WY" <?php echo ($result['project_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                                                        </select></td>
                                                    <td width="33.3%"><input type="text" name="p_zip" class="form-control mask-zip" placeholder="Zip" id="p_zip" value="<?php echo $result['project_zip']; ?>"></td>
                                                </tr>
                                            </table></td>
                                        <td><label for="business_1">Business Phone</label></td>
                                        <td><input type="text" name="business_1" class="form-control mask-phone" placeholder="Business Phone" id="business_1" value="<?php echo $result['contact1_business_ph']; ?>"></td>
                                        <td><label for="business_2">Business Phone</label></td>
                                        <td><input type="text" name="business_2" class="form-control mask-phone" placeholder="Business Phone" id="business_2" value="<?php echo $result['contact2_business_ph']; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Dealer Phone:</td>
                                        <td><?php echo $so_info['phone']; ?></td>
                                        <td><label for="p_landline">Project Landline</label></td>
                                        <td><input type="text" name="p_landline" class="form-control mask-phone" placeholder="Project Landline" id="p_landline" value="<?php echo $result['project_landline']; ?>"></td>
                                        <td><label for="email_1">Email Address</label></td>
                                        <td><input type="text" name="email_1" class="form-control" placeholder="Email Address" id="email_1" value="<?php echo $result['contact1_email']; ?>"></td>
                                        <td><label for="email_2">Email Address</label></td>
                                        <td><input type="text" name="email_2" class="form-control" placeholder="Email Address" id="email_2" value="<?php echo $result['contact2_email']; ?>"></td>
                                    </tr>
                                    <tr>
                                        <td>Dealer Email:</td>
                                        <td><?php echo "<a href='mailto:{$so_info['email']}'>{$so_info['email']}</a>"; ?></td>
                                    </tr>
                                    <tr>
                                        <td><label for="order_status">Order Status</label></td>
                                        <td>
                                            <select class="form-control" id="order_status" name="order_status">
                                                <option <?php echo ($result['order_status'] === ')') ? "selected" : null; ?> value=")">Lost</option>
                                                <option <?php echo ($result['order_status'] === '#') ? "selected" : null; ?> value="#">Quote</option>
                                                <option <?php echo ($result['order_status'] === '$') ? "selected" : null; ?> value="$">Job</option>
                                                <option <?php echo ($result['order_status'] === '(') ? "selected" : null; ?> value="(">Completed</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <input type="hidden" name="so_num" value="<?php echo $result['so_num']; ?>">

                                <div class="col-md-12" style="margin: 10px 0;">
                                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs save_so" data-sonum="<?php echo $result['so_num']; ?>">Save</button>
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
        $qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num LIKE 
            '%$find%' OR project LIKE '%$find%' OR contractor_dealer_code LIKE '%$find%'
            OR project_mgr LIKE '%$find%' ORDER BY so_num DESC LIMIT 0,25");

        $return = array();

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $qry2 = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['so_num']}'");

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

                $return[] = ['id'=>$result['so_num'], 'so_num'=>$result['so_num'], 'purchase_order'=>$result['project'], 'salesperson'=>$result['salesperson'], 'dealer_contractor'=>"{$result['dealer_code']}: {$dealer['dealer_name']}", 'account_type'=>$account_type, 'project_mgr_contact'=>$result['project_manager']];
            }
        }

        echo json_encode($return);

        break;
    default:
        die();
        break;
}