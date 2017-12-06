<?php
require_once ("../../includes/header_start.php");
require_once ("../../includes/classes/search.php");

//outputPHPErrs();

$find = sanitizeInput($_REQUEST['find']);
$search = sanitizeInput($_REQUEST['search'], $dbconn);

$sClass = new \Search\search();

function determineColor($room, $bracket) {
    global $dbconn;

    if($room['order_status'] === '+' || $room['order_status'] === '-') {
        return 'job-color-gray';
    } else {
        $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room[$bracket . "_bracket"]}'");
        $op = $op_qry->fetch_assoc();

        if($op['job_title'] === 'Bracket Completed') {
            return 'job-color-gray';
        } elseif((bool)$room[$bracket . '_published']) {
            return 'job-color-green';
        }
    }
}

function displayVINOpts($segment) {
    global $vin_schema;
    global $room;

    foreach($vin_schema[$segment] as $key => $value) {
        if($key === $room[$segment]) {
            $selected = "selected";
        } else {
            $selected = ($key === $room[$segment] && empty($room[$segment])) ? "selected" : null;
        }

        echo "<option value='$key' $selected>$value ($key)</option>";
    }
}

function getBracketInfo($bracket, $opID, $room) {
    global $dbconn;

    if(!empty($opID)) {
        $bracket_info = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = $opID")->fetch_assoc();

        if((bool)$room[$bracket . "_published"]) {
            if(stristr($bracket_info['job_title'], 'Bracket Completed') === FALSE) {
                $opacity = null;
                $outline = "btn-primary";
            } else {
                $opacity = "style='color:rgba(80,80,80,.6);'";
                $outline = "btn-primary-outline";
            }

            return "<table class='table-custom-nb' $opacity><tr><td><button class='btn waves-effect $outline' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button></td><td style='padding-left:4px;'>({$bracket_info['op_id']}) </td><td style='padding-left:2px;line-height:1em;'>{$bracket_info['job_title']}</td></tr></table>";
        }
    }
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

        case 'Pick & Materials':
            $bracket_def = 'pick_materials_bracket';
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
    case "general":
        // obtain the VIN database table and commit to memory for this query (MAJOR reduction in DB query count)
        $vin_qry = $dbconn->query("SELECT * FROM vin_schema ORDER BY FIELD(`value`, 'Custom/Other', 'TBD', 'N/A', 'Completed', 'Job', 'Quote', 'Lost') DESC, segment, `key` ASC");

        while($vin = $vin_qry->fetch_assoc()) {
            $vin_schema[$vin['segment']][$vin['key']] = $vin['value'];
        }

        $qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num LIKE '%$find%' OR LOWER(dealer_code) LIKE LOWER('%$find%') 
          OR LOWER(project_name) LIKE LOWER('%$find%') OR LOWER(project_mgr) LIKE LOWER('%$find%') OR LOWER(name_1) LIKE LOWER('%$find%') OR LOWER(name_2) LIKE LOWER('%$find%')  
              ORDER BY so_num DESC");

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

                $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id LIKE '%{$result['dealer_code']}%'");
                $dealer = $dealer_qry->fetch_assoc();

                /** BEGIN LISTING OF SO'S */
                echo "  <tr class='cursor-hand' id='show_room_{$result['so_num']}'>";
                echo "    <td class='nowrap' style='width:50px;'><button class='btn waves-effect btn-primary' id='edit_so_{$result['so_num']}'> <i class='zmdi zmdi-edit'></i> </button> <button class='btn btn-primary-outline waves-effect add_room_trigger' data-sonum='{$result['so_num']}' data-toggle='tooltip' data-placement='top' title='' data-original-title='Add additional room' style='font-size:10px;width:23px;height:22px;margin-top:1px;padding:0;'> +X</button></td>";
                echo "    <td>{$result['so_num']}</td>";
                echo "    <td>{$result['project_name']}</td>";
                echo "    <td>{$dealer['contact']}</td>";
                echo "    <td>{$result['dealer_code']}: {$dealer['dealer_name']}</td>";
                echo "  </tr>";

                /** BEGIN EDIT SO DISPLAY */
                echo "<tr id='tr_edit_so_{$result['so_num']}' style='display: none;'>";
                echo "  <td colspan='8'><div id='div_edit_so_{$result['so_num']}' style='display: none;'>";
                ?>

                <div class="col-md-12">
                    <form id="form_so_<?php echo $result['so_num']; ?>">
                        <div class="row">
                            <div class="col-md-3">
                                <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
                                    <tr>
                                        <td style="width: 33.3%;">
                                            <select class="form-control" id="dealer_code" name="dealer_code">
                                                <?php
                                                $dealer_qry = $dbconn->query("SELECT * FROM dealers ORDER BY dealer_id ASC;");

                                                while($dealer = $dealer_qry->fetch_assoc()) {
                                                    $selected = ($dealer['dealer_id'] === $result['dealer_code']) ? "selected" : NULL;

                                                    echo "<option value='{$dealer['dealer_id']}' $selected>{$dealer['dealer_id']} ({$dealer['contact']})</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr style="height: 5px;">
                                        <td colspan="3"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <input type="text" value="<?php echo $result['project_name']; ?>" name="project_name" class="form-control pull-left" placeholder="Project Name" id="project_name" style="width:50%;" />
                                            <input type="text" value="<?php echo $result['project_addr']; ?>" name="project_addr" class="form-control pull-left" placeholder="Project Address" id="project_addr" style="width:50%;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td style="width: 33.3%;"><input type="text" value="<?php echo $result['project_city']; ?>" name="project_city" class="form-control" placeholder="Project City" id="project_city"></td>
                                                    <td style="width: 33.3%;"><select class="form-control" id="project_state" name="project_state">
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
                                                    <td style="width: 33.3%;"><input type="text" value="<?php echo $result['project_zip']; ?>" name="project_zip" class="form-control" placeholder="Project Zip" id="project_zip"></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td><input type="text" value="<?php echo $result['project_landline']; ?>" name="project_landline" class="form-control" placeholder="Project Landline" id="project_landline"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" value="<?php echo $result['name_1']; ?>" name="name_1" class="form-control" placeholder="Name 1" id="name_1"></td>
                                        <td><input type="text" value="<?php echo $result['cell_1']; ?>" name="cell_1" class="form-control" placeholder="Cell Phone" id="cell_1"></td>
                                        <td><input type="text" value="<?php echo $result['business_1']; ?>" name="business_1" class="form-control" placeholder="Secondary Phone" id="business_1"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" value="<?php echo $result['email_1']; ?>" name="email_1" class="form-control" placeholder="Email Address" id="email_1"></td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" value="<?php echo $result['name_2']; ?>" name="name_2" class="form-control" placeholder="Name 2" id="name_2"></td>
                                        <td><input type="text" value="<?php echo $result['cell_2']; ?>" name="cell_2" class="form-control" placeholder="Cell Phone" id="cell_2"></td>
                                        <td><input type="text" value="<?php echo $result['business_2']; ?>" name="business_2" class="form-control" placeholder="Secondary Phone" id="business_2"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" value="<?php echo $result['email_2']; ?>" name="email_2" class="form-control" placeholder="Email Address" id="email_2"></td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <?php
                                            if(!empty($result['secondary_addr']) || !empty($result['secondary_city']) || !empty($result['secondary_zip']) || !empty($result['secondary_landline'])) {
                                                $secondary_checked = " checked";
                                                echo "<script>$('.secondary_addr_disp').show();</script>";
                                            } else
                                                $secondary_checked = null;
                                            ?>

                                            <div class="checkbox"><input id="secondary_addr_chk" type="checkbox" <?php echo $secondary_checked; ?>><label for="secondary_addr_chk"> Customer Secondary Address</label></div>
                                        </td>
                                    </tr>
                                    <tr style="display:none;" class="secondary_addr_disp">
                                        <td colspan="2"><input type="text" value="<?php echo $result['secondary_addr']; ?>" name="secondary_addr" class="form-control" placeholder="Secondary Address" id="secondary_addr"></td>
                                        <td><input type="text" value="<?php echo $result['secondary_landline']; ?>" name="secondary_landline" class="form-control" placeholder="Secondary Landline" id="secondary_landline"></td>
                                    </tr>
                                    <tr style="display:none;" class="secondary_addr_disp">
                                        <td colspan="2">
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td style="width: 33.3%;"><input type="text" value="<?php echo $result['secondary_city']; ?>" name="secondary_city" class="form-control" placeholder="Secondary City" id="secondary_city"></td>
                                                    <td style="width: 33.3%;"><select class="form-control" id="secondary_state" name="secondary_state">
                                                            <option value="AL" <?php echo ($result['secondary_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                                            <option value="AK" <?php echo ($result['secondary_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                                            <option value="AR" <?php echo ($result['secondary_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                                            <option value="CA" <?php echo ($result['secondary_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                                            <option value="CO" <?php echo ($result['secondary_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                                            <option value="CT" <?php echo ($result['secondary_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                                            <option value="DE" <?php echo ($result['secondary_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                                            <option value="FL" <?php echo ($result['secondary_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                                            <option value="GA" <?php echo ($result['secondary_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                                            <option value="HI" <?php echo ($result['secondary_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                                            <option value="ID" <?php echo ($result['secondary_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                                            <option value="IL" <?php echo ($result['secondary_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                                            <option value="IN" <?php echo ($result['secondary_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                                            <option value="IA" <?php echo ($result['secondary_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                                            <option value="KS" <?php echo ($result['secondary_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                                            <option value="KY" <?php echo ($result['secondary_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                                            <option value="LA" <?php echo ($result['secondary_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                                            <option value="ME" <?php echo ($result['secondary_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                                            <option value="MD" <?php echo ($result['secondary_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                                            <option value="MA" <?php echo ($result['secondary_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                                            <option value="MI" <?php echo ($result['secondary_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                                            <option value="MN" <?php echo ($result['secondary_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                                            <option value="MS" <?php echo ($result['secondary_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                                            <option value="MO" <?php echo ($result['secondary_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                                            <option value="MT" <?php echo ($result['secondary_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                                            <option value="NE" <?php echo ($result['secondary_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                                            <option value="NV" <?php echo ($result['secondary_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                                            <option value="NH" <?php echo ($result['secondary_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                                            <option value="NJ" <?php echo ($result['secondary_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                                            <option value="NM" <?php echo ($result['secondary_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                                            <option value="NY" <?php echo ($result['secondary_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                                            <option value="NC" <?php echo ($result['secondary_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                                            <option value="ND" <?php echo ($result['secondary_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                                            <option value="OH" <?php echo ($result['secondary_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                                            <option value="OK" <?php echo ($result['secondary_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                                            <option value="OR" <?php echo ($result['secondary_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                                            <option value="PA" <?php echo ($result['secondary_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                                            <option value="RI" <?php echo ($result['secondary_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                                            <option value="SC" <?php echo ($result['secondary_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                                            <option value="SD" <?php echo ($result['secondary_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                                            <option value="TN" <?php echo ($result['secondary_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                                            <option value="TX" <?php echo ($result['secondary_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                                            <option value="UT" <?php echo ($result['secondary_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                                            <option value="VT" <?php echo ($result['secondary_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                                            <option value="VA" <?php echo ($result['secondary_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                                            <option value="WA" <?php echo ($result['secondary_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                                            <option value="WV" <?php echo ($result['secondary_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                                            <option value="WI" <?php echo ($result['secondary_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                                            <option value="WY" <?php echo ($result['secondary_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                                                        </select></td>
                                                    <td style="width: 33.3%;"><input type="text" value="<?php echo $result['secondary_zip']; ?>" name="secondary_zip" class="form-control" placeholder="Secondary Zip" id="secondary_zip"></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <?php
                                            if(!empty($result['contractor_name']) || !empty($result['contractor_business']) || !empty($result['contractor_cell']) || !empty($result['contractor_email']) || !empty($result['contractor_zip']) ||
                                                !empty($result['contractor_city']) || !empty($result['contractor_addr'])) {

                                                $contractor_checked = " checked";
                                                echo "<script>$('.contractor_disp').show();</script>";
                                            } else
                                                $contractor_checked = null;
                                            ?>

                                            <div class="checkbox"><input id="contractor_chk" type="checkbox" <?php echo $contractor_checked; ?>><label for="contractor_chk"> Contractor</label></div>
                                        </td>
                                    </tr>
                                    <tr style="display:none;" class="contractor_disp">
                                        <td><input type="text" value="<?php echo $result['contractor_name']; ?>" name="contractor_name" class="form-control" placeholder="Contractor Name" id="contractor_name"></td>
                                        <td><input type="text" value="<?php echo $result['contractor_business']; ?>" name="contractor_business" class="form-control" placeholder="Contractor Business Number" id="contractor_business"></td>
                                        <td><input type="text" value="<?php echo $result['contractor_cell']; ?>" name="contractor_cell" class="form-control" placeholder="Contractor Cell Number" id="contractor_cell"></td>
                                    </tr>
                                    <tr style="display:none;" class="contractor_disp">
                                        <td><input type="text" value="<?php echo $result['contractor_addr']; ?>" name="contractor_addr" class="form-control" placeholder="Contractor Address" id="contractor_addr"></td>
                                        <td colspan="2">
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td style="width: 33.3%;"><input type="text" value="<?php echo $result['contractor_city']; ?>" name="contractor_city" class="form-control" placeholder="Contractor City" id="contractor_city"></td>
                                                    <td style="width: 33.3%;"><select class="form-control" id="contractor_state" name="contractor_state">
                                                            <option value="AL" <?php echo ($result['contractor_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                                            <option value="AK" <?php echo ($result['contractor_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                                            <option value="AR" <?php echo ($result['contractor_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                                            <option value="CA" <?php echo ($result['contractor_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                                            <option value="CO" <?php echo ($result['contractor_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                                            <option value="CT" <?php echo ($result['contractor_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                                            <option value="DE" <?php echo ($result['contractor_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                                            <option value="FL" <?php echo ($result['contractor_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                                            <option value="GA" <?php echo ($result['contractor_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                                            <option value="HI" <?php echo ($result['contractor_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                                            <option value="ID" <?php echo ($result['contractor_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                                            <option value="IL" <?php echo ($result['contractor_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                                            <option value="IN" <?php echo ($result['contractor_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                                            <option value="IA" <?php echo ($result['contractor_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                                            <option value="KS" <?php echo ($result['contractor_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                                            <option value="KY" <?php echo ($result['contractor_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                                            <option value="LA" <?php echo ($result['contractor_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                                            <option value="ME" <?php echo ($result['contractor_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                                            <option value="MD" <?php echo ($result['contractor_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                                            <option value="MA" <?php echo ($result['contractor_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                                            <option value="MI" <?php echo ($result['contractor_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                                            <option value="MN" <?php echo ($result['contractor_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                                            <option value="MS" <?php echo ($result['contractor_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                                            <option value="MO" <?php echo ($result['contractor_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                                            <option value="MT" <?php echo ($result['contractor_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                                            <option value="NE" <?php echo ($result['contractor_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                                            <option value="NV" <?php echo ($result['contractor_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                                            <option value="NH" <?php echo ($result['contractor_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                                            <option value="NJ" <?php echo ($result['contractor_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                                            <option value="NM" <?php echo ($result['contractor_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                                            <option value="NY" <?php echo ($result['contractor_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                                            <option value="NC" <?php echo ($result['contractor_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                                            <option value="ND" <?php echo ($result['contractor_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                                            <option value="OH" <?php echo ($result['contractor_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                                            <option value="OK" <?php echo ($result['contractor_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                                            <option value="OR" <?php echo ($result['contractor_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                                            <option value="PA" <?php echo ($result['contractor_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                                            <option value="RI" <?php echo ($result['contractor_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                                            <option value="SC" <?php echo ($result['contractor_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                                            <option value="SD" <?php echo ($result['contractor_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                                            <option value="TN" <?php echo ($result['contractor_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                                            <option value="TX" <?php echo ($result['contractor_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                                            <option value="UT" <?php echo ($result['contractor_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                                            <option value="VT" <?php echo ($result['contractor_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                                            <option value="VA" <?php echo ($result['contractor_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                                            <option value="WA" <?php echo ($result['contractor_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                                            <option value="WV" <?php echo ($result['contractor_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                                            <option value="WI" <?php echo ($result['contractor_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                                            <option value="WY" <?php echo ($result['contractor_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                                                        </select></td>
                                                    <td style="width: 33.3%;"><input type="text" value="<?php echo $result['contractor_zip']; ?>" name="contractor_zip" class="form-control" placeholder="Contractor Zip" id="contractor_zip"></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="display:none;" class="contractor_disp">
                                        <td><input type="text" value="<?php echo $result['contractor_email']; ?>" name="contractor_email" class="form-control" placeholder="Contractor Email Address" id="contractor_email"></td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr style="display:none;" class="contractor_disp">
                                        <td><input type="text" value="<?php echo $result['project_mgr']; ?>" name="project_mgr" class="form-control" placeholder="Project Manager" id="project_mgr"></td>
                                        <td><input type="text" value="<?php echo $result['project_mgr_cell']; ?>" name="project_mgr_cell" class="form-control" placeholder="Project Manager Cell" id="project_mgr_cell"></td>
                                        <td><input type="text" value="<?php echo $result['project_mgr_email']; ?>" name="project_mgr_email" class="form-control" placeholder="Project Manager Email" id="project_mgr_email"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?php
                                            if(!empty($result['bill_to']) || !empty($result['billing_contact']) || !empty($result['billing_landline']) || !empty($result['billing_cell']) || !empty($result['billing_addr']) ||
                                                !empty($result['billing_city']) || !empty($result['billing_zip']) || !empty($result['billing_account']) || !empty($result['billing_routing'])
                                                || !empty($result['billing_cc_num']) || !empty($result['billing_cc_exp']) || !empty($result['billing_cc_ccv'])) {

                                                $billing_checked = " checked";
                                                echo "<script>$('.billing_info_disp').show();</script>";
                                            } else
                                                $billing_checked = null;

                                            $b_homeowner = null;
                                            $b_contractor = null;

                                            if($result['bill_to'] === 'homeowner')
                                                $b_homeowner = " checked";
                                            elseif($result['bill_to'] === 'contractor')
                                                $b_contractor = " checked";
                                            ?>

                                            <div class="checkbox"><input id="billing_addr_chk" type="checkbox" <?php echo $billing_checked; ?>><label for="billing_addr_chk"> Billing Information</label></div>
                                        </td>
                                        <td style="display:none;" class="billing_info_disp"><label class="c-input c-radio"><input id="bill_homeowner" <?php echo $b_homeowner; ?> name="bill_to" type="radio" value="homeowner"><span class="c-indicator"></span>Bill Homeowner</label></td>
                                        <td style="display:none;" class="billing_info_disp"><label class="c-input c-radio"><input id="bill_contractor" <?php echo $b_contractor; ?> name="bill_to" type="radio" value="contractor"><span class="c-indicator"></span>Bill Contractor</label></td>
                                    </tr>
                                    <tr style="display:none;" class="billing_info_disp">
                                        <td><input type="text" value="<?php echo $result['billing_contact']; ?>" name="billing_contact" class="form-control" placeholder="Billing Contact" id="billing_contact"></td>
                                        <td><input type="text" value="<?php echo $result['billing_landline']; ?>" name="billing_landline" class="form-control" placeholder="Billing Landline" id="billing_landline"></td>
                                        <td><input type="text" value="<?php echo $result['billing_cell']; ?>" name="billing_cell" class="form-control" placeholder="Billing Cell" id="billing_cell"></td>
                                    </tr>
                                    <tr style="display:none;" class="billing_info_disp">
                                        <td><input type="text" value="<?php echo $result['billing_addr']; ?>" name="billing_addr" class="form-control" placeholder="Billing Address" id="billing_addr"></td>
                                        <td colspan="2">
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td style="width: 33.3%;"><input type="text" value="<?php echo $result['billing_city']; ?>" name="billing_city" class="form-control" placeholder="Billing City" id="billing_city"></td>
                                                    <td style="width: 33.3%;"><select class="form-control" id="billing_state" name="billing_state">
                                                            <option value="AL" <?php echo ($result['billing_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                                            <option value="AK" <?php echo ($result['billing_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                                            <option value="AR" <?php echo ($result['billing_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                                            <option value="CA" <?php echo ($result['billing_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                                            <option value="CO" <?php echo ($result['billing_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                                            <option value="CT" <?php echo ($result['billing_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                                            <option value="DE" <?php echo ($result['billing_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                                            <option value="FL" <?php echo ($result['billing_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                                            <option value="GA" <?php echo ($result['billing_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                                            <option value="HI" <?php echo ($result['billing_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                                            <option value="ID" <?php echo ($result['billing_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                                            <option value="IL" <?php echo ($result['billing_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                                            <option value="IN" <?php echo ($result['billing_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                                            <option value="IA" <?php echo ($result['billing_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                                            <option value="KS" <?php echo ($result['billing_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                                            <option value="KY" <?php echo ($result['billing_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                                            <option value="LA" <?php echo ($result['billing_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                                            <option value="ME" <?php echo ($result['billing_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                                            <option value="MD" <?php echo ($result['billing_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                                            <option value="MA" <?php echo ($result['billing_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                                            <option value="MI" <?php echo ($result['billing_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                                            <option value="MN" <?php echo ($result['billing_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                                            <option value="MS" <?php echo ($result['billing_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                                            <option value="MO" <?php echo ($result['billing_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                                            <option value="MT" <?php echo ($result['billing_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                                            <option value="NE" <?php echo ($result['billing_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                                            <option value="NV" <?php echo ($result['billing_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                                            <option value="NH" <?php echo ($result['billing_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                                            <option value="NJ" <?php echo ($result['billing_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                                            <option value="NM" <?php echo ($result['billing_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                                            <option value="NY" <?php echo ($result['billing_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                                            <option value="NC" <?php echo ($result['billing_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                                            <option value="ND" <?php echo ($result['billing_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                                            <option value="OH" <?php echo ($result['billing_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                                            <option value="OK" <?php echo ($result['billing_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                                            <option value="OR" <?php echo ($result['billing_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                                            <option value="PA" <?php echo ($result['billing_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                                            <option value="RI" <?php echo ($result['billing_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                                            <option value="SC" <?php echo ($result['billing_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                                            <option value="SD" <?php echo ($result['billing_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                                            <option value="TN" <?php echo ($result['billing_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                                            <option value="TX" <?php echo ($result['billing_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                                            <option value="UT" <?php echo ($result['billing_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                                            <option value="VT" <?php echo ($result['billing_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                                            <option value="VA" <?php echo ($result['billing_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                                            <option value="WA" <?php echo ($result['billing_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                                            <option value="WV" <?php echo ($result['billing_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                                            <option value="WI" <?php echo ($result['billing_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                                            <option value="WY" <?php echo ($result['billing_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                                                        </select></td>
                                                    <td style="width: 33.3%;"><input type="text" value="<?php echo $result['billing_zip']; ?>" name="billing_zip" class="form-control" placeholder="Billing Zip" id="billing_zip"></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="display:none;" class="billing_info_disp">
                                        <td style="height:8px"></td>
                                    </tr>
                                    <tr style="display:none;" class="billing_info_disp">
                                        <td colspan="3">
                                            <input type="text" value="<?php echo $result['billing_account']; ?>" name="billing_account" autocomplete="off" class="form-control pull-left" placeholder="ACH Account #" id="billing_account" style="width: 50%;">
                                            <input type="text" value="<?php echo $result['billing_routing']; ?>" name="billing_routing" autocomplete="off" class="form-control pull-right" placeholder="ACH Routing #" id="billing_routing" style="width: 50%;">
                                        </td>
                                    </tr>
                                    <tr style="display:none;" class="billing_info_disp">
                                        <td><input type="text" value="<?php echo $result['billing_cc_num']; ?>" name="billing_cc_num" class="form-control" placeholder="Credit Card #" id="billing_cc_num"></td>
                                        <td><input type="text" value="<?php echo $result['billing_cc_exp']; ?>" name="billing_cc_exp" class="form-control" placeholder="Exp. Date" id="billing_cc_exp"></td>
                                        <td><input type="text" value="<?php echo $result['billing_cc_ccv']; ?>" name="billing_cc_ccv" class="form-control" placeholder="CCV Code" id="billing_cc_ccv"></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12" style="min-height:149px;overflow:auto;">
                                        <p style="font-weight:bold;">Inquiries/Notes:</p>

                                        <table class="table table-custom-nb table-v-top" width="100%">
                                            <?php
                                            $so_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'so_inquiry' OR note_type = 'so_note_log') AND notes.type_id = '{$result['id']}' ORDER BY notes.timestamp DESC;");

                                            while($so_inquiry = $so_inquiry_qry->fetch_assoc()) {
                                                $inquiry_replies = null;

                                                $time = date(DATE_TIME_ABBRV, $so_inquiry['NTimestamp']);

                                                if(!empty($so_inquiry['followup_time'])) {
                                                    $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$so_inquiry['user_to']}");
                                                    $followup_usr = $followup_usr_qry->fetch_assoc();

                                                    $followup_time = date(DATE_TIME_ABBRV, $so_inquiry['followup_time']);

                                                    $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                                                } else {
                                                    $followup = null;
                                                }

                                                $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$so_inquiry['nID']}' ORDER BY timestamp DESC");

                                                if($inquiry_reply_qry->num_rows > 0) {
                                                    while($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                                                        $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                                                        $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                                                    }
                                                } else {
                                                    $inquiry_replies = null;
                                                }

                                                $notes = str_replace(" ", "&nbsp;", $so_inquiry['note']);

                                                echo "<tr>";
                                                echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$so_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                                                echo "  <td>$notes -- <small><em>{$so_inquiry['name']} on $time $followup</em></small></td>";
                                                echo "</tr>";

                                                echo "<tr id='inquiry_reply_line_{$so_inquiry['nID']}' style='display:none;'>";
                                                echo "  <td colspan='2'>
                                                                <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$so_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                                                                <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='{$so_inquiry['nID']}'>Reply</button>
                                                            </td>";
                                                echo "</tr>";

                                                echo $inquiry_replies;

                                                echo "<tr style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                                            }
                                            ?>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <textarea class="form-control" name="inquiry" id="inquiry" placeholder="New Inquiry/Note" style="width:100%;height:350px;"></textarea>
                                        <input type="text" name="inquiry_followup_date" id="inquiry_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                                        <label for="inquiry_requested_of" style="float:left;padding:4px;"> requested of </label>
                                        <select name="inquiry_requested_of" id="inquiry_requested_of" class="form-control" style="width:50%;float:left;">
                                            <option value="null" selected disabled></option>
                                            <?php
                                            $user_qry = $dbconn->query("SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC");

                                            while($user = $user_qry->fetch_assoc()) {
                                                echo "<option value='{$user['id']}'>{$user['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row"><div class="col-md-12"><button type="button" class="btn btn-primary waves-effect waves-light w-xs save_so" data-sonum="<?php echo $result['so_num']; ?>">Save</button></div></div>
                    </form>
                </div>

                <?php echo "</div></td>";
                echo "</tr>";
                /** END EDIT SO DISPLAY */

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
                                <th>PICK/MATERIALS</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['so_num']}' ORDER BY room, iteration ASC");

                            $prev_room = null;
                            $prev_seq = null;

                            if($room_qry->num_rows > 0) {
                                while($room = $room_qry->fetch_assoc()) {
                                    $add_iteration_btn = null;
                                    $add_seq_btn = null;

                                    $individual_bracket = json_decode($room['individual_bracket_buildout']);

                                    $iteration = explode(".", number_format($room['iteration'], 2));

                                    if(empty($prev_seq)) {
                                        $prev_seq = $iteration[0];
                                    }

                                    if($room['room'] === $prev_room) {
                                        if($iteration[0] === $prev_seq) {
                                            $room_name = ".{$iteration[1]}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}: {$room['room_name']}";
                                            $tab = "<div class='pull-left' style='width:15px;'>&nbsp</div>";
                                            $add_iteration_btn = null;
                                        } else {
                                            $room_name = "{$room['iteration']}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}: {$room['room_name']}";
                                            $prev_seq = $iteration[0];
                                            $add_iteration_btn = "<button class='btn btn-primary-outline waves-effect add_iteration' data-roomid='{$room['id']}' data-addto='iteration' data-iteration='{$room['iteration']}' data-toggle='tooltip' data-placement='top' title='' data-original-title='Add additional iteration' style='font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;'> I +.01</button>";
                                            $tab = "<div class='pull-left' style='width:8px;'>&nbsp</div>";
                                        }

                                        $add_seq_btn = null;
                                    } else {
                                        $room_name = "{$room['room']}{$room['iteration']}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}: {$room['room_name']}";
                                        $prev_room = $room['room'];
                                        $add_iteration_btn = "<button class='btn btn-primary-outline waves-effect add_iteration' data-roomid='{$room['id']}' data-addto='iteration' data-iteration='{$room['iteration']}' data-toggle='tooltip' data-placement='top' title='' data-original-title='Add additional iteration' style='font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;'> I +.01</button>";
                                        $add_seq_btn = "<button class='btn btn-primary-outline waves-effect add_iteration' data-roomid='{$room['id']}' data-addto='sequence' data-iteration='{$room['iteration']}' data-toggle='tooltip' data-placement='top' title='' data-original-title='Add additional sequence' style='font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;'> S +1</button>";
                                        $tab = null;
                                    }

                                    $salesColor = determineColor($room, 'sales');
                                    $preprodColor = determineColor($room, 'preproduction');
                                    $sampleColor = determineColor($room, 'sample');
                                    $doorColor = determineColor($room, 'doordrawer');
                                    $customsColor = determineColor($room, 'custom');
                                    $mainColor = determineColor($room, 'main');
                                    $shippingColor = determineColor($room, 'shipping');
                                    $installColor = determineColor($room, 'install_bracket');
                                    $pickmatColor = determineColor($room, 'pick_materials');

                                    $sales_published_display = getBracketInfo('sales', $room['sales_bracket'], $room);
                                    $sample_published_display = getBracketInfo('sample', $room['sample_bracket'], $room);
                                    $preprod_published_display = getBracketInfo('preproduction', $room['preproduction_bracket'], $room);
                                    $door_published_display = getBracketInfo('doordrawer', $room['doordrawer_bracket'], $room);
                                    $main_published_display = getBracketInfo('main', $room['main_bracket'], $room);
                                    $customs_published_display = getBracketInfo('custom', $room['custom_bracket'], $room);
                                    $shipping_published_display = getBracketInfo('shipping', $room['shipping_bracket'], $room);
                                    $install_published_display = getBracketInfo('install_bracket', $room['install_bracket'], $room);
                                    $pickmat_published_display = getBracketInfo('pick_materials', $room['pick_materials_bracket'], $room);

                                    $target_dir = SITE_ROOT . "/attachments/";
                                    $attachment_dir = "{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}";
                                    $http_base = "/attachments/{$room['so_parent']}/{$room['room']}/{$room['iteration']}/";

                                    if(file_exists($attachment_dir)) {
                                        $attachment_code = "btn-primary";
                                    } else {
                                        $attachment_code = "btn_secondary disabled";
                                    }

                                    switch($room['order_status']) {
                                        case '$':
                                            $order_status = '<strong>Job</strong>';
                                            break;

                                        case '#':
                                            $order_status = '<strong>Quote</strong>';
                                            break;

                                        case '+':
                                            $order_status = '<strong>Completed</strong>';
                                            break;

                                        case '-':
                                            $order_status = '<strong>Lost</strong>';
                                            break;

                                        case 'A':
                                            $order_status = '<strong>Add-on</strong>';
                                            break;

                                        case 'W':
                                            $order_status = '<strong>Warranty</strong>';
                                            break;
                                    }

                                    echo "<tr class='cursor-hand' id='manage_bracket_{$room['id']}'>";
                                    echo "  <td class='nowrap'>
                                                <button class='btn waves-effect btn-primary' id='show_single_room_{$room['id']}'><i class='zmdi zmdi-edit'></i></button> <button class='btn waves-effect $attachment_code' id='show_attachments_room_{$room['id']}'><i class='zmdi zmdi-attachment-alt'></i></button> 
                                                $add_seq_btn $add_iteration_btn
                                            </td>";
                                    echo "  <td class='nowrap'><span class='pull-left'>{$tab}{$room_name}</span> <span class='pull-right' style='margin-right:5px;'>$order_status</span></td>";
                                    echo "  <td class='$salesColor' style='width:9%'>$sales_published_display</td>";
                                    echo "  <td class='$sampleColor' style='width:9%'>$sample_published_display</td>";
                                    echo "  <td class='$preprodColor' style='width:9%'>$preprod_published_display</td>";
                                    echo "  <td class='$doorColor' style='width:9%'>$door_published_display</td>";
                                    echo "  <td class='$mainColor' style='width:9%'>$main_published_display</td>";
                                    echo "  <td class='$customsColor' style='width:9%'>$customs_published_display</td>";
                                    echo "  <td class='$shippingColor' style='width:9%'>$shipping_published_display</td>";
                                    echo "  <td class='$installColor' style='width:9%'>$install_published_display</td>";
                                    echo "  <td class='$pickmatColor' style='width:9%'>$pickmat_published_display</td>";
                                    echo "</tr>";

                                    /** BEGIN SINGLE ROOM DISPLAY */
                                    echo "<tr id='tr_single_room_{$room['id']}' style='display: none;'>";
                                    echo "  <td colspan='11'><div id='div_single_room_{$room['id']}' style='display: none;'>";
                                    ?>

                                    <div class="col-md-12">
                                        <form id="room_edit_<?php echo $room['id']; ?>">
                                            <div class="row">
                                                <div class="col-md-1 sticky">
                                                    <a class="btn btn-primary btn-block waves-effect waves-light edit_room_save">Save</a>
                                                    <a href='/print/e_coversheet.php?room_id=<?php echo $room['id']; ?>&action=sample_req' target="_blank" class="btn btn-primary-outline btn-block waves-effect waves-light w-xs">Print Sample Request</a>
                                                    <a href='/print/e_coversheet.php?room_id=<?php echo $room['id']; ?>' target="_blank" class="btn btn-primary-outline btn-block waves-effect waves-light w-xs">Print Coversheet</a>
                                                    <a href='/print/e_coversheet.php?room_id=<?php echo $room['id']; ?>&action=arh' target="_blank" class="btn btn-primary-outline btn-block waves-effect waves-light w-xs">Print ARH Coversheet</a>
                                                    <a href='/print/e_coversheet.php?room_id=<?php echo $room['id']; ?>&action=no_totals' target="_blank" class="btn btn-primary-outline btn-block waves-effect waves-light w-xs">Print Shop Coversheet</a>
                                                    <a href='/print/sample_label.php?room_id=<?php echo $room['id']; ?>' target="_blank" class="btn btn-primary-outline btn-block waves-effect waves-light w-xs">Print Sample Label</a>
                                                </div>

                                                <div class="col-md-3 col-md-offset-1">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <input type="hidden" name="vin_so_num_<?php echo $room['id']; ?>" value="<?php echo $room['so_parent']; ?>" id="vin_so_num_<?php echo $room['id']; ?>" />
                                                            <input type="hidden" name="vin_room_<?php echo $room['id']; ?>" value="<?php echo $room['room']; ?>" id="vin_room_<?php echo $room['id']; ?>" />
                                                            <input type="hidden" name="vin_iteration_<?php echo $room['id']; ?>" value="<?php echo $room['iteration']; ?>" id="vin_iteration_<?php echo $room['id']; ?>" />
                                                            <input type="hidden" name="vin_product_type_<?php echo $room['id']; ?>" value="<?php echo $room['product_type']; ?>" id="vin_product_type_<?php echo $room['id']; ?>" />
                                                            <input type="hidden" name="vin_order_status_<?php echo $room['id']; ?>" value="<?php echo $room['order_status']; ?>" id="vin_order_status_<?php echo $room['id']; ?>" />
                                                            <input type="hidden" name="vin_days_to_ship_<?php echo $room['id']; ?>" value="<?php echo $room['days_to_ship']; ?>" id="vin_days_to_ship_<?php echo $room['id']; ?>" />
                                                            <input type="hidden" name="vin_dealer_code_<?php echo $room['id']; ?>" value="<?php echo $result['dealer_code']; ?>" id="vin_dealer_code_<?php echo $room['id']; ?>" />

                                                            <input type="hidden" name="sonum" value="<?php echo $result['so_num']; ?>">
                                                            <input type="hidden" name="room" value="<?php echo $room['room']; ?>">
                                                            <input type="hidden" name="roomid" value="<?php echo $room['id']; ?>">

                                                            <div class="table_outline">
                                                                <table style="width:97%;margin:0 auto;" class="table">
                                                                    <tr>
                                                                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Globals</h5></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="delivery_date">Delivery Date</label></td>
                                                                        <td>
                                                                            <div class="input-group">
                                                                                <input type="text" class="form-control delivery_date" id="edit_del_date_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="delivery_date" placeholder="Delivery Date" value="<?php echo (!empty($room['delivery_date'])) ? date("m/d/Y", $room['delivery_date']) : ""; ?>">
                                                                                <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                                                                            </div>

                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="room">Room</label></td>
                                                                        <td><input type="text" class="form-control" id="edit_room_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room" placeholder="Room" value="<?php echo $room['room']; ?>" readonly></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="product_type">Product Type</label></td>
                                                                        <td>
                                                                            <select class="form-control" id="edit_product_type_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="product_type" value="<?php echo $room['product_type']; ?>">
                                                                                <?php
                                                                                displayVINOpts('product_type');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="iteration">Iteration</label></td>
                                                                        <td><input type="text" class="form-control" id="edit_iteration_<?php echo $room['id']; ?>" name="iteration" placeholder="Iteration" value="<?php echo $room['iteration']; ?>" readonly></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="order_status">Order Status</label></td>
                                                                        <td>
                                                                            <select class="form-control" id="edit_order_status_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="order_status">
                                                                                <?php
                                                                                displayVINOpts('order_status');
                                                                                ?>
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
                                                                    <tr style="height:10px;">
                                                                        <td colspan="2"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input tabindex="37" type="text" class="form-control" name="vin_code_<?php echo $room['id']; ?>" id="vin_code_<?php echo $room['id']; ?>" placeholder="VIN Code" value="<?php echo $room['vin_code']; ?>" /></td>
                                                                    </tr>
                                                                    <tr style="height:10px;">
                                                                        <td colspan="2"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5><label>Door, Drawer & Hardwood</label></h5></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="species_grade_<?php echo $room['id']; ?>">Species</label></td>
                                                                        <td>
                                                                            <select tabindex="2" name="species_grade_<?php echo $room['id']; ?>" id="species_grade_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('species_grade');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="construction_method_<?php echo $room['id']; ?>">Construction Method</label></td>
                                                                        <td>
                                                                            <select tabindex="18" name="construction_method_<?php echo $room['id']; ?>" id="construction_method_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('construction_method');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="door_design_<?php echo $room['id']; ?>">Door Design</label></td>
                                                                        <td>
                                                                            <select tabindex="3" name="door_design_<?php echo $room['id']; ?>" id="door_design_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('door_design');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="height:10px;">
                                                                        <td colspan="2"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><label>Panel Raise</label></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding-left:20px;"><label for="panel_raise_door_<?php echo $room['id']; ?>">Door</label></td>
                                                                        <td>
                                                                            <select tabindex="4" name="panel_raise_door_<?php echo $room['id']; ?>" id="panel_raise_door_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('panel_raise');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding-left:20px;"><label for="panel_raise_sd_<?php echo $room['id']; ?>">Short Drawer</label></td>
                                                                        <td>
                                                                            <select tabindex="5" name="panel_raise_sd_<?php echo $room['id']; ?>" id="panel_raise_sd_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('panel_raise');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding-left:20px;"><label for="panel_raise_td_<?php echo $room['id']; ?>">Tall Drawer</label></td>
                                                                        <td>
                                                                            <select tabindex="6" name="panel_raise_td_<?php echo $room['id']; ?>" id="panel_raise_td_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                // **TODO** This is broken for short/tall drawer, fix it
                                                                                displayVINOpts('panel_raise');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="edge_profile_<?php echo $room['id']; ?>">Edge Profile</label></td>
                                                                        <td>
                                                                            <select tabindex="7" name="edge_profile_<?php echo $room['id']; ?>" id="edge_profile_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('edge_profile');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="framing_bead_<?php echo $room['id']; ?>">Framing Bead</label></td>
                                                                        <td>
                                                                            <select tabindex="8" name="framing_bead_<?php echo $room['id']; ?>" id="framing_bead_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('framing_bead');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="framing_options_<?php echo $room['id']; ?>">Framing Options</label></td>
                                                                        <td>
                                                                            <select tabindex="9" name="framing_options_<?php echo $room['id']; ?>" id="framing_options_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('framing_options');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="style_rail_width_<?php echo $room['id']; ?>">Style/Rail Width</label></td>
                                                                        <td>
                                                                            <select tabindex="4" name="style_rail_width_<?php echo $room['id']; ?>" id="style_rail_width_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('style_rail_width');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                                                        <td>
                                                                            <select tabindex="11" name="finish_code_<?php echo $room['id']; ?>" id="finish_code_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('finish_code');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="sheen_<?php echo $room['id']; ?>">Sheen</label></td>
                                                                        <td>
                                                                            <select tabindex="12" name="sheen_<?php echo $room['id']; ?>" id="sheen_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('sheen');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="glaze_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                        <td>
                                                                            <select tabindex="13" name="glaze_<?php echo $room['id']; ?>" id="glaze_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('glaze');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                        <td>
                                                                            <select tabindex="14" name="glaze_technique_<?php echo $room['id']; ?>" id="glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('glaze_technique');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="antiquing_<?php echo $room['id']; ?>">Antiquing</label></td>
                                                                        <td>
                                                                            <select tabindex="15" name="antiquing_<?php echo $room['id']; ?>" id="antiquing_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('antiquing');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="worn_edges_<?php echo $room['id']; ?>">Worn Edges</label></td>
                                                                        <td>
                                                                            <select tabindex="16" name="worn_edges_<?php echo $room['id']; ?>" id="worn_edges_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('worn_edges');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="distress_level_<?php echo $room['id']; ?>">Distress Level</label></td>
                                                                        <td>
                                                                            <select tabindex="17" name="distress_level_<?php echo $room['id']; ?>" id="distress_level_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('distress_level');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="height:10px;">
                                                                        <td colspan="2"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Carcass Exterior Finish</h5></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="carcass_exterior_species_<?php echo $room['id']; ?>">Species</label></td>
                                                                        <td>
                                                                            <select tabindex="19" name="carcass_exterior_species_<?php echo $room['id']; ?>" id="carcass_exterior_species_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('carcass_species');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="carcass_exterior_finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                                                        <td>
                                                                            <select tabindex="21" name="carcass_exterior_finish_code_<?php echo $room['id']; ?>" id="carcass_exterior_finish_code_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('finish_code');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="carcass_exterior_glaze_color_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                        <td>
                                                                            <select tabindex="22" name="carcass_exterior_glaze_color_<?php echo $room['id']; ?>" id="carcass_exterior_glaze_color_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('glaze');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                        <td>
                                                                            <select tabindex="23" name="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>" id="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('glaze_technique');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="height:10px;">
                                                                        <td colspan="2"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Carcass Interior Finish</h5></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="carcass_interior_species_<?php echo $room['id']; ?>">Species</label></td>
                                                                        <td>
                                                                            <select tabindex="24" name="carcass_interior_species_<?php echo $room['id']; ?>" id="carcass_interior_species_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('carcass_species');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="carcass_interior_finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                                                        <td>
                                                                            <select tabindex="26" name="carcass_interior_finish_code_<?php echo $room['id']; ?>" id="carcass_interior_finish_code_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('finish_code');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="carcass_interior_glaze_color_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                        <td>
                                                                            <select tabindex="27" name="carcass_interior_glaze_color_<?php echo $room['id']; ?>" id="carcass_interior_glaze_color_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('glaze');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="carcass_interior_glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                        <td>
                                                                            <select tabindex="28" name="carcass_interior_glaze_technique_<?php echo $room['id']; ?>" id="carcass_interior_glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('glaze_technique');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="height:10px;">
                                                                        <td colspan="2"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Interior Conveniences</h5></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><label for="drawer_boxes_<?php echo $room['id']; ?>">Drawer Boxes</label></td>
                                                                        <td>
                                                                            <select tabindex="29" name="drawer_boxes_<?php echo $room['id']; ?>" id="drawer_boxes_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                <?php
                                                                                displayVINOpts('drawer_boxes');
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr style="height:10px;">
                                                                        <td colspan="2"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Sample Request</h5></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input tabindex="30" type="text" class="form-control text-md-center pull-left" name="sample_block_<?php echo $room['id']; ?>" id="sample_block_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="0"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="sample_block_<?php echo $room['id']; ?>">Sample Block <small>(5 1/4" x 6 1/8")</small></label></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input tabindex="31" type="text" class="form-control text-md-center pull-left" name="door_only_<?php echo $room['id']; ?>" id="door_only_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['door_only_ordered']; ?>"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="door_only_<?php echo $room['id']; ?>">Door Only <small>(12" x 15")</small></label></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input tabindex="32" type="text" class="form-control text-md-center pull-left" name="door_drawer_<?php echo $room['id']; ?>" id="door_drawer_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['door_drawer_ordered']; ?>"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="door_drawer_<?php echo $room['id']; ?>">Door & Drawer <small>(15 1/2" x 23 1/2")</small></label></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input tabindex="33" type="text" class="form-control text-md-center pull-left" name="inset_square_<?php echo $room['id']; ?>" id="inset_square_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['inset_square_ordered']; ?>"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="inset_square_<?php echo $room['id']; ?>">Inset Square <small>(15 1/2" x 23 1/2")</small></label></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="2"><input tabindex="34" type="text" class="form-control text-md-center pull-left" name="inset_beaded_<?php echo $room['id']; ?>" id="inset_beaded_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['inset_beaded_ordered']; ?>"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="inset_beaded_<?php echo $room['id']; ?>">Inset Beaded <small>(16 1/2" x 23 1/2")</small></label></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-8">
                                                    <div class="row">
                                                        <div class="col-md-4" style="height:304px;overflow-y:auto;">
                                                            <table class="table_outline" width="100%">
                                                                <tr>
                                                                    <td class="bracket-border-top" style="padding: 2px 7px;"><h5>SO Notes</h5></td>
                                                                </tr>
                                                                <tr style="height:5px;"><td colspan="2"></td></tr>
                                                                <?php
                                                                $so_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE note_type = 'so_inquiry' AND notes.type_id = '{$result['id']}' ORDER BY notes.timestamp DESC;");

                                                                while($so_inquiry = $so_inquiry_qry->fetch_assoc()) {
                                                                    $inquiry_replies = null;

                                                                    $time = date(DATE_TIME_ABBRV, $so_inquiry['NTimestamp']);

                                                                    if(!empty($so_inquiry['followup_time'])) {
                                                                        $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$so_inquiry['user_to']}");
                                                                        $followup_usr = $followup_usr_qry->fetch_assoc();

                                                                        $followup_time = date(DATE_TIME_ABBRV, $so_inquiry['followup_time']);

                                                                        $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                                                                    } else {
                                                                        $followup = null;
                                                                    }

                                                                    $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$so_inquiry['nID']}' ORDER BY timestamp DESC");

                                                                    if($inquiry_reply_qry->num_rows > 0) {
                                                                        while($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                                                                            $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                                                                            $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                                                                        }
                                                                    } else {
                                                                        $inquiry_replies = null;
                                                                    }

                                                                    $notes = str_replace(" ", "&nbsp;", $so_inquiry['note']);
                                                                    $notes = nl2br($notes);

                                                                    echo "<tr>";
                                                                    echo "  <td>$notes -- <small><em>{$so_inquiry['name']} on $time $followup</em></small></td>";
                                                                    echo "</tr>";

                                                                    echo $inquiry_replies;

                                                                    echo "<tr style='height:2px;'><td style='background-color:#000;padding:0;'></td></tr>";
                                                                }
                                                                ?>
                                                                <tr style="height:5px;"><td colspan="2"></td></tr>
                                                            </table>
                                                        </div>

                                                        <div class="col-md-4" style="height:304px;overflow-y:auto;">
                                                            <table class="table table-custom-nb table-v-top">
                                                                <tr>
                                                                    <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">Room Notes</h5> <div class="pull-right"><input type="checkbox" class="ignoreSaveAlert" id="display_log" checked /> <label for="display_log">Show Audit Log</label></div></td>
                                                                </tr>
                                                                <tr style="height:5px;"><td colspan="2"></td></tr>
                                                                <?php
                                                                $room_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'room_note' OR note_type = 'room_note_log') AND notes.type_id = '{$room['id']}' ORDER BY notes.timestamp DESC;");

                                                                while($room_inquiry = $room_inquiry_qry->fetch_assoc()) {
                                                                    $inquiry_replies = null;

                                                                    $time = date(DATE_TIME_ABBRV, $room_inquiry['NTimestamp']);

                                                                    if(!empty($room_inquiry['followup_time'])) {
                                                                        $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$room_inquiry['user_to']}");
                                                                        $followup_usr = $followup_usr_qry->fetch_assoc();

                                                                        $followup_time = date(DATE_TIME_ABBRV, $room_inquiry['followup_time']);

                                                                        $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                                                                    } else {
                                                                        $followup = null;
                                                                    }

                                                                    $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$room_inquiry['nID']}' ORDER BY timestamp DESC");

                                                                    if($inquiry_reply_qry->num_rows > 0) {
                                                                        while($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                                                                            $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                                                                            $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                                                                        }
                                                                    } else {
                                                                        $inquiry_replies = null;
                                                                    }

                                                                    $notes = str_replace("  ", "&nbsp;&nbsp;", $room_inquiry['note']);
                                                                    //$notes = $room_inquiry['note'];
                                                                    $notes = nl2br($notes);

                                                                    echo "<tr style='height:5px;'><td colspan='2'></td></tr>";

                                                                    $room_note_log = ($room_inquiry['note_type'] === 'room_note_log') ? 'room_note_log' : null;

                                                                    echo "<tr class='$room_note_log'>";
                                                                    echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$room_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                                                                    echo "  <td>$notes -- <small><em>{$room_inquiry['name']} on $time $followup</em></small></td>";
                                                                    echo "</tr>";

                                                                    echo "<tr id='inquiry_reply_line_{$room_inquiry['nID']}' style='display:none;'>";
                                                                    echo "  <td colspan='2'>
                                                                                <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$room_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                                                                                <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='{$room_inquiry['nID']}'>Reply</button>
                                                                            </td>";
                                                                    echo "</tr>";

                                                                    echo $inquiry_replies;

                                                                    echo "<tr class='$room_note_log' style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                                                                }
                                                                ?>
                                                                <tr style="height:5px;"><td colspan="2"></td></tr>
                                                            </table>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <textarea class="form-control" name="room_inquiry" id="room_inquiry" placeholder="Room Inquiry/Note" style="width:100%;height:277px;"></textarea>
                                                            <input type="text" name="room_inquiry_followup_date" id="room_inquiry_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                                                            <label for="room_inquiry_requested_of" style="float:left;padding:4px;"> requested of </label>
                                                            <select name="room_inquiry_requested_of" id="room_inquiry_requested_of" class="form-control" style="width:50%;float:left;">
                                                                <option value="null" selected disabled></option>
                                                                <?php
                                                                $user_qry = $dbconn->query("SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC");

                                                                while($user = $user_qry->fetch_assoc()) {
                                                                    echo "<option value='{$user['id']}'>{$user['name']}</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
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
                                                                <tr>
                                                                    <td class="bracket-border-top">
                                                                        <div class="row bracket-header-custom">
                                                                            <div class="col-md-8"><h5><label for="pickmat_bracket_adjustments_<?php echo $room['id']; ?>">Pick & Materials Bracket</label></h5></div>
                                                                            <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="pickmat_published" value="1" id="pickmat_bracket_adjustments_<?php echo $room['id']; ?>" <?php echo ((bool)$room['pick_materials_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="background-color: #eceeef;">&nbsp;</td>
                                                                    <td class="bracket-border-top"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="bracket-border-bottom">
                                                                        <?php displayBracketOpsMgmt('Pick & Materials', $room, $individual_bracket); ?>
                                                                    </td>
                                                                    <td style="background-color: #eceeef;">&nbsp;</td>
                                                                    <td class="bracket-border-bottom"></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <?php echo "</div></td>";
                                    echo "</tr>";

                                    /** BEGIN DISPLAY OF ADD ITERATION */
                                    echo "<tr id='tr_iteration_{$room['id']}' style='display: none;'>";
                                    echo "  <td colspan='11'><div id='div_iteration_{$room['id']}' style='display: none;'>";
                                    ?>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <form id="room_add_iteration_<?php echo $room['id']; ?>">
                                                    <div class="row">
                                                        <div class="col-md-1 sticky">
                                                            <a class="btn btn-primary btn-block waves-effect waves-light iteration_save">Save</a>
                                                        </div>

                                                        <div class="col-md-3 col-md-offset-1">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <input type="hidden" name="vin_so_num_<?php echo $room['id']; ?>" value="<?php echo $room['so_parent']; ?>" id="vin_so_num_<?php echo $room['id']; ?>" />
                                                                    <input type="hidden" name="vin_room_<?php echo $room['id']; ?>" value="<?php echo $room['room']; ?>" id="vin_room_<?php echo $room['id']; ?>" />
                                                                    <input type="hidden" name="vin_iteration_<?php echo $room['id']; ?>" value="<?php echo $room['iteration']; ?>" id="vin_iteration_<?php echo $room['id']; ?>" />
                                                                    <input type="hidden" name="vin_product_type_<?php echo $room['id']; ?>" value="<?php echo $room['product_type']; ?>" id="vin_product_type_<?php echo $room['id']; ?>" />
                                                                    <input type="hidden" name="vin_order_status_<?php echo $room['id']; ?>" value="<?php echo $room['order_status']; ?>" id="vin_order_status_<?php echo $room['id']; ?>" />
                                                                    <input type="hidden" name="vin_days_to_ship_<?php echo $room['id']; ?>" value="<?php echo $room['days_to_ship']; ?>" id="vin_days_to_ship_<?php echo $room['id']; ?>" />
                                                                    <input type="hidden" name="vin_dealer_code_<?php echo $room['id']; ?>" value="<?php echo $result['dealer_code']; ?>" id="vin_dealer_code_<?php echo $room['id']; ?>" />

                                                                    <input type="hidden" name="sonum" value="<?php echo $result['so_num']; ?>">
                                                                    <input type="hidden" name="room" value="<?php echo $room['room']; ?>">
                                                                    <input type="hidden" name="roomid" value="<?php echo $room['id']; ?>">

                                                                    <div class="table_outline">
                                                                        <table style="width:97%;margin:0 auto;" class="table">
                                                                            <tr>
                                                                                <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Globals</h5></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="delivery_date">Delivery Date</label></td>
                                                                                <td>
                                                                                    <div class="input-group">
                                                                                        <input type="text" class="form-control delivery_date" id="edit_del_date_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="delivery_date" placeholder="Delivery Date" value="<?php echo (!empty($room['delivery_date'])) ? date("m/d/Y", $room['delivery_date']) : ""; ?>">
                                                                                        <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                                                                                    </div>

                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="room">Room</label></td>
                                                                                <td><input type="text" class="form-control" id="edit_room_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room" placeholder="Room" value="<?php echo $room['room']; ?>" readonly></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="product_type">Product Type</label></td>
                                                                                <td>
                                                                                    <select class="form-control" id="edit_product_type_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="product_type" value="<?php echo $room['product_type']; ?>">
                                                                                        <?php
                                                                                        displayVINOpts('product_type');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="iteration">Iteration</label></td>
                                                                                <td><input type="text" class="form-control" id="edit_iteration_<?php echo $room['id']; ?>" name="iteration" placeholder="Iteration" value="<?php echo $room['iteration']; ?>" readonly></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="order_status">Order Status</label></td>
                                                                                <td>
                                                                                    <select class="form-control" id="edit_order_status_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="order_status">
                                                                                        <?php
                                                                                        displayVINOpts('order_status');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="days_to_ship">Days to Ship</label></td>
                                                                                <td>
                                                                                    <select class="form-control days-to-ship" id="edit_days_to_ship_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="days_to_ship" data-type="edit" data-room="<?php echo $room['room']; ?>">
                                                                                        <option value="G" <?php echo ($room['days_to_ship'] === 'G') ? "selected" : null; ?>>Green (26)</option>
                                                                                        <option value="Y" <?php echo ($room['days_to_ship'] === 'Y') ? "selected" : null; ?>>Yellow (19)</option>
                                                                                        <option value="N" <?php echo ($room['days_to_ship'] === 'N') ? "selected" : null; ?>>Orange (13)</option>
                                                                                        <option value="R" <?php echo ($room['days_to_ship'] === 'R') ? "selected" : null; ?>>Red (6)</option>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="room_name">Room Name</label></td>
                                                                                <td><input type="text" class="form-control" id="edit_room_name_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room_name" placeholder="Room Name" value="<?php echo $room['room_name']; ?>"></td>
                                                                            </tr>
                                                                            <tr style="height:10px;">
                                                                                <td colspan="2"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"><input tabindex="37" type="text" class="form-control" name="vin_code_<?php echo $room['id']; ?>" id="vin_code_<?php echo $room['id']; ?>" placeholder="VIN Code" value="<?php echo $room['vin_code']; ?>" /></td>
                                                                            </tr>
                                                                            <tr style="height:10px;">
                                                                                <td colspan="2"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5><label>Door, Drawer & Hardwood</label></h5></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="species_grade_<?php echo $room['id']; ?>">Species</label></td>
                                                                                <td>
                                                                                    <select tabindex="2" name="species_grade_<?php echo $room['id']; ?>" id="species_grade_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('species_grade');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="construction_method_<?php echo $room['id']; ?>">Construction Method</label></td>
                                                                                <td>
                                                                                    <select tabindex="18" name="construction_method_<?php echo $room['id']; ?>" id="construction_method_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('construction_method');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="door_design_<?php echo $room['id']; ?>">Door Design</label></td>
                                                                                <td>
                                                                                    <select tabindex="3" name="door_design_<?php echo $room['id']; ?>" id="door_design_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('door_design');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr style="height:10px;">
                                                                                <td colspan="2"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"><label>Panel Raise</label></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="padding-left:20px;"><label for="panel_raise_door_<?php echo $room['id']; ?>">Door</label></td>
                                                                                <td>
                                                                                    <select tabindex="4" name="panel_raise_door_<?php echo $room['id']; ?>" id="panel_raise_door_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('panel_raise');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="padding-left:20px;"><label for="panel_raise_sd_<?php echo $room['id']; ?>">Short Drawer</label></td>
                                                                                <td>
                                                                                    <select tabindex="5" name="panel_raise_sd_<?php echo $room['id']; ?>" id="panel_raise_sd_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('panel_raise');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="padding-left:20px;"><label for="panel_raise_td_<?php echo $room['id']; ?>">Tall Drawer</label></td>
                                                                                <td>
                                                                                    <select tabindex="6" name="panel_raise_td_<?php echo $room['id']; ?>" id="panel_raise_td_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('panel_raise');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="edge_profile_<?php echo $room['id']; ?>">Edge Profile</label></td>
                                                                                <td>
                                                                                    <select tabindex="7" name="edge_profile_<?php echo $room['id']; ?>" id="edge_profile_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('edge_profile');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="framing_bead_<?php echo $room['id']; ?>">Framing Bead</label></td>
                                                                                <td>
                                                                                    <select tabindex="8" name="framing_bead_<?php echo $room['id']; ?>" id="framing_bead_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('framing_bead');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="framing_options_<?php echo $room['id']; ?>">Framing Options</label></td>
                                                                                <td>
                                                                                    <select tabindex="9" name="framing_options_<?php echo $room['id']; ?>" id="framing_options_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('framing_options');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="style_rail_width_<?php echo $room['id']; ?>">Style/Rail Width</label></td>
                                                                                <td>
                                                                                    <select tabindex="4" name="style_rail_width_<?php echo $room['id']; ?>" id="style_rail_width_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('style_rail_width');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2">&nbsp;</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                                                                <td>
                                                                                    <select tabindex="11" name="finish_code_<?php echo $room['id']; ?>" id="finish_code_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('finish_code');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="sheen_<?php echo $room['id']; ?>">Sheen</label></td>
                                                                                <td>
                                                                                    <select tabindex="12" name="sheen_<?php echo $room['id']; ?>" id="sheen_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('sheen');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="glaze_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                                <td>
                                                                                    <select tabindex="13" name="glaze_<?php echo $room['id']; ?>" id="glaze_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('glaze');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                                <td>
                                                                                    <select tabindex="14" name="glaze_technique_<?php echo $room['id']; ?>" id="glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('glaze_technique');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="antiquing_<?php echo $room['id']; ?>">Antiquing</label></td>
                                                                                <td>
                                                                                    <select tabindex="15" name="antiquing_<?php echo $room['id']; ?>" id="antiquing_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('antiquing');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="worn_edges_<?php echo $room['id']; ?>">Worn Edges</label></td>
                                                                                <td>
                                                                                    <select tabindex="16" name="worn_edges_<?php echo $room['id']; ?>" id="worn_edges_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('worn_edges');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="distress_level_<?php echo $room['id']; ?>">Distress Level</label></td>
                                                                                <td>
                                                                                    <select tabindex="17" name="distress_level_<?php echo $room['id']; ?>" id="distress_level_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('distress_level');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr style="height:10px;">
                                                                                <td colspan="2"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Carcass Exterior Finish</h5></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="carcass_exterior_species_<?php echo $room['id']; ?>">Species</label></td>
                                                                                <td>
                                                                                    <select tabindex="19" name="carcass_exterior_species_<?php echo $room['id']; ?>" id="carcass_exterior_species_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('species_grade');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="carcass_exterior_finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                                                                <td>
                                                                                    <select tabindex="21" name="carcass_exterior_finish_code_<?php echo $room['id']; ?>" id="carcass_exterior_finish_code_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('finish_code');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="carcass_exterior_glaze_color_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                                <td>
                                                                                    <select tabindex="22" name="carcass_exterior_glaze_color_<?php echo $room['id']; ?>" id="carcass_exterior_glaze_color_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('glaze');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                                <td>
                                                                                    <select tabindex="23" name="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>" id="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('glaze_technique');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr style="height:10px;">
                                                                                <td colspan="2"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Carcass Interior Finish</h5></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="carcass_interior_species_<?php echo $room['id']; ?>">Species</label></td>
                                                                                <td>
                                                                                    <select tabindex="24" name="carcass_interior_species_<?php echo $room['id']; ?>" id="carcass_interior_species_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('species_grade');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="carcass_interior_finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                                                                <td>
                                                                                    <select tabindex="26" name="carcass_interior_finish_code_<?php echo $room['id']; ?>" id="carcass_interior_finish_code_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('finish_code');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="carcass_interior_glaze_color_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                                                                <td>
                                                                                    <select tabindex="27" name="carcass_interior_glaze_color_<?php echo $room['id']; ?>" id="carcass_interior_glaze_color_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('glaze');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="carcass_interior_glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                                                                <td>
                                                                                    <select tabindex="28" name="carcass_interior_glaze_technique_<?php echo $room['id']; ?>" id="carcass_interior_glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('glaze_technique');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr style="height:10px;">
                                                                                <td colspan="2"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Interior Conveniences</h5></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="drawer_boxes_<?php echo $room['id']; ?>">Drawer Boxes</label></td>
                                                                                <td>
                                                                                    <select tabindex="29" name="drawer_boxes_<?php echo $room['id']; ?>" id="drawer_boxes_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                                                        <?php
                                                                                        displayVINOpts('drawer_boxes');
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr style="height:10px;">
                                                                                <td colspan="2"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Sample Request</h5></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"><input tabindex="30" type="text" class="form-control text-md-center pull-left" name="sample_block_<?php echo $room['id']; ?>" id="sample_block_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="0"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="sample_block_<?php echo $room['id']; ?>">Sample Block <small>(5 1/4" x 6 1/8")</small></label></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"><input tabindex="31" type="text" class="form-control text-md-center pull-left" name="door_only_<?php echo $room['id']; ?>" id="door_only_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['door_only_ordered']; ?>"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="door_only_<?php echo $room['id']; ?>">Door Only <small>(12" x 15")</small></label></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"><input tabindex="32" type="text" class="form-control text-md-center pull-left" name="door_drawer_<?php echo $room['id']; ?>" id="door_drawer_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['door_drawer_ordered']; ?>"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="door_drawer_<?php echo $room['id']; ?>">Door & Drawer <small>(15 1/2" x 23 1/2")</small></label></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"><input tabindex="33" type="text" class="form-control text-md-center pull-left" name="inset_square_<?php echo $room['id']; ?>" id="inset_square_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['inset_square_ordered']; ?>"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="inset_square_<?php echo $room['id']; ?>">Inset Square <small>(15 1/2" x 23 1/2")</small></label></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"><input tabindex="34" type="text" class="form-control text-md-center pull-left" name="inset_beaded_<?php echo $room['id']; ?>" id="inset_beaded_<?php echo $room['id']; ?>" placeholder="X" style="width:40px;" value="<?php echo $room['inset_beaded_ordered']; ?>"> <label class="pull-left" style="margin-left:5px;line-height:28px;" for="inset_beaded_<?php echo $room['id']; ?>">Inset Beaded <small>(16 1/2" x 23 1/2")</small></label></td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-8">
                                                            <div class="row">
                                                                <div class="col-md-4" style="height:304px;overflow-y:auto;">
                                                                    <table class="table_outline" width="100%">
                                                                        <tr>
                                                                            <td class="bracket-border-top" style="padding: 2px 7px;"><h5>SO Notes</h5></td>
                                                                        </tr>
                                                                        <tr style="height:5px;"><td colspan="2"></td></tr>
                                                                        <?php
                                                                        $so_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE note_type = 'so_inquiry' AND notes.type_id = '{$result['id']}' ORDER BY notes.timestamp DESC;");

                                                                        while($so_inquiry = $so_inquiry_qry->fetch_assoc()) {
                                                                            $inquiry_replies = null;

                                                                            $time = date(DATE_TIME_ABBRV, $so_inquiry['NTimestamp']);

                                                                            if(!empty($so_inquiry['followup_time'])) {
                                                                                $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$so_inquiry['user_to']}");
                                                                                $followup_usr = $followup_usr_qry->fetch_assoc();

                                                                                $followup_time = date(DATE_TIME_ABBRV, $so_inquiry['followup_time']);

                                                                                $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                                                                            } else {
                                                                                $followup = null;
                                                                            }

                                                                            $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$so_inquiry['nID']}' ORDER BY timestamp DESC");

                                                                            if($inquiry_reply_qry->num_rows > 0) {
                                                                                while($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                                                                                    $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                                                                                    $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                                                                                }
                                                                            } else {
                                                                                $inquiry_replies = null;
                                                                            }

                                                                            $notes = str_replace(" ", "&nbsp;", $so_inquiry['note']);
                                                                            $notes = nl2br($notes);

                                                                            echo "<tr>";
                                                                            echo "  <td>$notes -- <small><em>{$so_inquiry['name']} on $time $followup</em></small></td>";
                                                                            echo "</tr>";

                                                                            echo $inquiry_replies;

                                                                            echo "<tr style='height:2px;'><td style='background-color:#000;padding:0;'></td></tr>";
                                                                        }
                                                                        ?>
                                                                        <tr style="height:5px;"><td colspan="2"></td></tr>
                                                                    </table>
                                                                </div>

                                                                <div class="col-md-4" style="height:304px;overflow-y:auto;">
                                                                    <table class="table table-custom-nb table-v-top">
                                                                        <tr>
                                                                            <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">Room Notes</h5> <div class="pull-right"><input type="checkbox" class="ignoreSaveAlert" id="display_log" checked /> <label for="display_log">Show Audit Log</label></div></td>
                                                                        </tr>
                                                                        <tr style="height:5px;"><td colspan="2"></td></tr>
                                                                        <?php
                                                                        $room_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'room_note' OR note_type = 'room_note_log') AND notes.type_id = '{$room['id']}' ORDER BY notes.timestamp DESC;");

                                                                        while($room_inquiry = $room_inquiry_qry->fetch_assoc()) {
                                                                            $inquiry_replies = null;

                                                                            $time = date(DATE_TIME_ABBRV, $room_inquiry['NTimestamp']);

                                                                            if(!empty($room_inquiry['followup_time'])) {
                                                                                $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$room_inquiry['user_to']}");
                                                                                $followup_usr = $followup_usr_qry->fetch_assoc();

                                                                                $followup_time = date(DATE_TIME_ABBRV, $room_inquiry['followup_time']);

                                                                                $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                                                                            } else {
                                                                                $followup = null;
                                                                            }

                                                                            $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$room_inquiry['nID']}' ORDER BY timestamp DESC");

                                                                            if($inquiry_reply_qry->num_rows > 0) {
                                                                                while($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                                                                                    $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                                                                                    $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                                                                                }
                                                                            } else {
                                                                                $inquiry_replies = null;
                                                                            }

                                                                            $notes = str_replace("  ", "&nbsp;&nbsp;", $room_inquiry['note']);
                                                                            //$notes = $room_inquiry['note'];
                                                                            $notes = nl2br($notes);

                                                                            echo "<tr style='height:5px;'><td colspan='2'></td></tr>";

                                                                            $room_note_log = ($room_inquiry['note_type'] === 'room_note_log') ? 'room_note_log' : null;

                                                                            echo "<tr class='$room_note_log'>";
                                                                            echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$room_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                                                                            echo "  <td>$notes -- <small><em>{$room_inquiry['name']} on $time $followup</em></small></td>";
                                                                            echo "</tr>";

                                                                            echo "<tr id='inquiry_reply_line_{$room_inquiry['nID']}' style='display:none;'>";
                                                                            echo "  <td colspan='2'>
                                                                                    <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$room_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                                                                                    <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='{$room_inquiry['nID']}'>Reply</button>
                                                                                </td>";
                                                                            echo "</tr>";

                                                                            echo $inquiry_replies;

                                                                            echo "<tr class='$room_note_log' style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                                                                        }
                                                                        ?>
                                                                        <tr style="height:5px;"><td colspan="2"></td></tr>
                                                                    </table>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <textarea class="form-control" name="room_inquiry" id="room_inquiry" placeholder="Room Inquiry/Note" style="width:100%;height:277px;"></textarea>
                                                                    <input type="text" name="room_inquiry_followup_date" id="room_inquiry_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                                                                    <label for="room_inquiry_requested_of" style="float:left;padding:4px;"> requested of </label>
                                                                    <select name="room_inquiry_requested_of" id="room_inquiry_requested_of" class="form-control" style="width:50%;float:left;">
                                                                        <option value="null" selected disabled></option>
                                                                        <?php
                                                                        $user_qry = $dbconn->query("SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC");

                                                                        while($user = $user_qry->fetch_assoc()) {
                                                                            echo "<option value='{$user['id']}'>{$user['name']}</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
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
                                                                        <tr>
                                                                            <td class="bracket-border-top">
                                                                                <div class="row bracket-header-custom">
                                                                                    <div class="col-md-8"><h5><label for="pickmat_bracket_adjustments_<?php echo $room['id']; ?>">Pick & Materials Bracket</label></h5></div>
                                                                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="pickmat_published" value="1" id="pickmat_bracket_adjustments_<?php echo $room['id']; ?>" <?php echo ((bool)$room['pick_materials_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                                                                </div>
                                                                            </td>
                                                                            <td style="background-color: #eceeef;">&nbsp;</td>
                                                                            <td class="bracket-border-top"></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="bracket-border-bottom">
                                                                                <?php displayBracketOpsMgmt('Pick & Materials', $room, $individual_bracket); ?>
                                                                            </td>
                                                                            <td style="background-color: #eceeef;">&nbsp;</td>
                                                                            <td class="bracket-border-bottom"></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <?php echo "</div></td>";
                                    echo "</tr>";
                                    /** END DISPLAY OF ADD ITERATION */

                                    /** BEGIN DISPLAY OF ATTACHMENTS */
                                    echo "<tr id='tr_attachments_{$room['id']}' style='display: none;'>";
                                    echo "  <td colspan='11'><div id='div_attachments_{$room['id']}' style='display: none;'>";
                                    ?>

                                    <div class="col-md-12">
                                        <?php
                                        $scanned_directory = array_diff(scandir($attachment_dir), array('..', '.'));

                                        foreach($scanned_directory as $file) {
                                            echo "<a href='{$http_base}$file' target='_blank'>$file</a><br />";
                                        }
                                        ?>
                                    </div>

                                    <?php echo "</div></td>";
                                    echo "</tr>";
                                    /** END DISPLAY OF ATTACHMENTS */
                                    /** END SINGLE ROOM DISPLAY */
                                }
                            }

                            /** BEGIN ADD SINGLE ROOM INFORMATION */
                            echo "<tr id='tr_add_single_room_info_{$result['so_num']}' style='display: none;'>";
                            echo "  <td colspan='9'><div id='div_add_single_room_info_{$result['so_num']}' style='display: none;'>";
                            ?>

                            <div class="col-md-12 add_room_info">
                                <div class="row">
                                    <div class="col-md-12">
                                        <form id="form_add_room_<?php echo $result['so_num']; ?>">
                                            <div class="col-md-3">
                                                <table width="100%" class="table table-custom-nb">
                                                    <tr>
                                                        <td><label for="delivery_date">Delivery Date</label></td>
                                                        <td>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control delivery_date_add job-color-green" id="delivery_date_add_<?php echo $result['so_num']; ?>" name="delivery_date" placeholder="Delivery Date" value="<?php echo calcDelDate("Green"); ?>">
                                                                <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                                                            </div>
                                                        </td>
                                                    </tr>
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
                                                                <?php
                                                                $pt_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'product_type'");

                                                                while($pt = $pt_qry->fetch_assoc()) {
                                                                    echo "<option value='{$pt['key']}'>{$pt['value']}</option>";
                                                                }
                                                                ?>
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
                                                                <?php
                                                                $ostatus_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'order_status' ORDER BY FIELD(`key`,'(', '#', '$', 'W', 'A', ')');");

                                                                while($ostatus = $ostatus_qry->fetch_assoc()) {
                                                                    echo "<option value='{$ostatus['key']}' $selected>{$ostatus['value']}</option>";
                                                                }
                                                                ?>
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
                                                    <textarea class="form-control" name="room_notes" maxlength="65530" placeholder="Room Notes" rows="3"></textarea>
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
                /** END LISTING OF SO'S */
            }
        }

        break;
    case "find_base":
        $sClass->displayResults($find);

        break;
    default:
        die();
        break;
}