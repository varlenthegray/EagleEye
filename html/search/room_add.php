<?php
require_once("../../includes/header_start.php");

function displayVINOpts($segment, $db_col = null, $default = null) {
    global $vin_schema;

    foreach($vin_schema[$segment] as $key => $value) {
        $selected = ($key === $default) ? "selected" : null;

        echo "<option value='$key' $selected>$value ($key)</option>";
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

    $i = 0;

    while($op = $op_qry->fetch_assoc()) {
        $selected = ($i === 0) ? "checked" : null;

        $op_room_id = "op_{$op['id']}_room_{$room['id']}";

        $left_info .= <<<HEREDOC
            <li class="active_ops" data-opnum="{$op['op_id']}" data-opid="{$op['id']}" data-roomid="{$room['id']}">
                <input type="radio" name="{$bracket_def}" id="$op_room_id" value="{$op['id']}" $selected>
                <label for="$op_room_id">{$op['op_id']}-{$op['job_title']}</label>
                <span class="pull-right cursor-hand text-md-center deactivate_op" data-opid="{$op['id']}" data-roomid="{$room['id']}" data-soid="{$room['so_parent']}"> <i class="fa fa-arrow-circle-right" style="width: 18px;"></i> </button>
            </li>
HEREDOC;

        $i++;
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

// obtain the VIN database table and commit to memory for this query (MAJOR reduction in DB query count)
$vin_qry = $dbconn->query("SELECT * FROM vin_schema ORDER BY 
  case right(`value`, 5) when 'Paint' then 1 when 'Stain' then 2 end, 
  FIELD(`value`, '0.15', 'Design Specific', 'Design Specific (5X-Wood)', 'Design Specific (5X-MDF)', 'Custom/Other', 'TBD', 'N/A', 'None', 'No', 'Completed', 'Job', 'Quote', 'Lost', ' - Paint', ' - Stain') DESC, segment, `value` ASC");

while($vin = $vin_qry->fetch_assoc()) {
    $vin_schema[$vin['segment']][$vin['key']] = $vin['value'];
}

$so_num = sanitizeInput($_REQUEST['so_num']);

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' ORDER BY room, iteration ASC LIMIT 0, 1;");
$room = $room_qry->fetch_assoc();

$result_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = {$room['so_parent']}");
$result = $result_qry->fetch_assoc();

$delivery_date = (!empty($room['delivery_date'])) ? date("m/d/Y", $room['delivery_date']) : "";

$individual_bracket = json_decode($room['individual_bracket_buildout']);

echo "<script>
        active_room_id = '{$room['id']}';
        $('#vin_room_' + active_room_id).val(active_room_id);
      </script>";
?>

<div class="col-md-12">
    <form id="add_iteration">
        <div class="row">
            <div class="col-md-1 sticky">
                <h5 class="text-md-center" id="add_iteration_header_<?php echo $room['id']; ?>">Add Room</h5>
                <a class="btn btn-primary btn-block waves-effect waves-light iteration_save">Save</a>
            </div>

            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="vin_dealer_code_<?php echo $room['id']; ?>" value="<?php echo $result['dealer_code']; ?>" id="vin_dealer_code_<?php echo $room['id']; ?>" />

                        <input type="hidden" name="sonum" value="<?php echo $result['so_num']; ?>">
                        <input type="hidden" name="roomid" value="<?php echo $room['id']; ?>">

                        <div class="table_outline">
                            <table style="width:97%;margin:0 auto;" class="table">
                                <?php if($bouncer->validate('view_globals')) { ?>
                                    <tr>
                                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5>Globals</h5></td>
                                    </tr>
                                    <tr>
                                        <td><label for="room">Room</label></td>
                                        <td>
                                            <select class="form-control" name="room" style="float:left;width:12%;padding:.375rem .1rem;">
                                                <?php
                                                $letter = 'A';
                                                $blacklist = ['I','O'];
                                                $letter_series = [];

                                                $blacklist_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num'");

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
                                            <input type="text" class="form-control" id="edit_room_name_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="room_name" placeholder="Room Name" value="<?php echo $room['room_name']; ?>" style="float:left;width:86%;margin-left:5px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="iteration">Iteration</label></td>
                                        <td><input type="text" class="form-control" id="edit_iteration_<?php echo $room['id']; ?>" name="iteration" placeholder="Iteration" value="<?php echo $room['iteration']; ?>" readonly></td>
                                    </tr>
                                    <tr>
                                        <td><label for="product_type">Product Type</label></td>
                                        <td>
                                            <select class="form-control" id="edit_product_type_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="product_type" value="<?php echo $room['product_type']; ?>">
                                                <?php
                                                displayVINOpts('product_type', null, 'C');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php if($bouncer->validate('change_order_status')) { ?>
                                        <tr>
                                            <td><label for="order_status">Order Status</label></td>
                                            <td>
                                                <select class="form-control" id="edit_order_status_<?php echo $room['room']; ?>_so_<?php echo $result['so_num']; ?>" name="order_status">
                                                    <?php
                                                    displayVINOpts('order_status', null, '#');
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php } ?>
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
                                        <td><label for="delivery_date">Ship Date</label></td>
                                        <td>
                                            <div class="input-group">
                                                <?php
                                                switch($room['days_to_ship']) {
                                                    case 'G':
                                                        $dd_class = 'job-color-green';
                                                        break;

                                                    case 'Y':
                                                        $dd_class = 'job-color-yellow';
                                                        break;

                                                    case 'N':
                                                        $dd_class = 'job-color-orange';
                                                        break;

                                                    case 'R':
                                                        $dd_class = 'job-color-red';
                                                        break;

                                                    default:
                                                        $dd_class = 'job-color-gray';
                                                        break;
                                                }
                                                ?>

                                                <input type="text" class="form-control delivery_date <?php echo $dd_class; ?>" name="delivery_date" placeholder="Delivery Date" value="<?php echo (!empty($room['delivery_date'])) ? date("m/d/Y", $room['delivery_date']) : ""; ?>">
                                                <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr style="height:10px;">
                                        <td colspan="2"></td>
                                    </tr>
                                    <?php if($bouncer->validate('view_accounting')) { ?>
                                        <tr>
                                            <td colspan="2">
                                                <label class="c-input c-checkbox">Deposit Received <input type="checkbox" name="deposit_received" value="1" <?php echo ((bool)$room['payment_deposit']) ? "checked":null; ?>><span class="c-indicator"></span></label><br />
                                                <label class="c-input c-checkbox">Prior to Loading: Distribution - Final Payment<br/><span style="margin-left:110px;">Retail - On Delivery/Payment</span> <input type="checkbox" name="ptl_del" value="1" <?php echo ((bool)$room['payment_del_ptl']) ? "checked":null; ?>><span class="c-indicator"></span></label><br />
                                                <label class="c-input c-checkbox">Retail - Final Payment <input type="checkbox" name="final_payment" value="1" <?php echo ((bool)$room['payment_final']) ? "checked":null; ?>><span class="c-indicator"></span></label>
                                            </td>
                                        </tr>
                                        <tr style="height:10px;">
                                            <td colspan="2"></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td colspan="2"><input tabindex="37" type="text" class="form-control" name="vin_code_<?php echo $room['id']; ?>" id="vin_code_<?php echo $room['id']; ?>" placeholder="VIN Code" value="" /></td>
                                    </tr>
                                    <tr style="height:10px;">
                                        <td colspan="2"></td>
                                    </tr>
                                <?php } if($bouncer->validate('view_vin')) { ?>

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
                                                displayVINOpts('panel_raise', 'panel_raise_door');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left:20px;"><label for="panel_raise_sd_<?php echo $room['id']; ?>">Short Drawer</label></td>
                                        <td>
                                            <select tabindex="5" name="panel_raise_sd_<?php echo $room['id']; ?>" id="panel_raise_sd_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                <?php
                                                displayVINOpts('panel_raise', 'panel_raise_sd');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left:20px;"><label for="panel_raise_td_<?php echo $room['id']; ?>">Tall Drawer</label></td>
                                        <td>
                                            <select tabindex="6" name="panel_raise_td_<?php echo $room['id']; ?>" id="panel_raise_td_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                <?php
                                                displayVINOpts('panel_raise', 'panel_raise_td');
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
                                                displayVINOpts('carcass_species', 'carcass_exterior_species');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="carcass_exterior_finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                        <td>
                                            <select tabindex="21" name="carcass_exterior_finish_code_<?php echo $room['id']; ?>" id="carcass_exterior_finish_code_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                <?php
                                                displayVINOpts('finish_code', 'carcass_exterior_finish_code');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="carcass_exterior_glaze_color_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                        <td>
                                            <select tabindex="22" name="carcass_exterior_glaze_color_<?php echo $room['id']; ?>" id="carcass_exterior_glaze_color_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                <?php
                                                displayVINOpts('glaze', 'carcass_exterior_glaze_color');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                        <td>
                                            <select tabindex="23" name="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>" id="carcass_exterior_glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                <?php
                                                displayVINOpts('glaze_technique', 'carcass_exterior_glaze_technique');
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
                                                displayVINOpts('carcass_species', 'carcass_interior_species');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="carcass_interior_finish_code_<?php echo $room['id']; ?>">Finish Code</label></td>
                                        <td>
                                            <select tabindex="26" name="carcass_interior_finish_code_<?php echo $room['id']; ?>" id="carcass_interior_finish_code_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                <?php
                                                displayVINOpts('finish_code', 'carcass_interior_finish_code');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="carcass_interior_glaze_color_<?php echo $room['id']; ?>">Glaze Color</label></td>
                                        <td>
                                            <select tabindex="27" name="carcass_interior_glaze_color_<?php echo $room['id']; ?>" id="carcass_interior_glaze_color_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                <?php
                                                displayVINOpts('glaze', 'carcass_interior_glaze_color');
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="carcass_interior_glaze_technique_<?php echo $room['id']; ?>">Glaze Technique</label></td>
                                        <td>
                                            <select tabindex="28" name="carcass_interior_glaze_technique_<?php echo $room['id']; ?>" id="carcass_interior_glaze_technique_<?php echo $room['id']; ?>" class="form-control" onchange="calcVin(<?php echo $room['id']; ?>)">
                                                <?php
                                                displayVINOpts('glaze_technique', 'carcass_interior_glaze_technique');
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
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row">
                    <?php if($bouncer->validate('view_so_notes')) { ?>
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

                                    $notes = str_replace("  ", "&nbsp;&nbsp;", $so_inquiry['note']);
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
                    <?php } ?>
                </div>

                <?php if($bouncer->validate('edit_brackets')) { ?>
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
                                            <div class="col-md-8"><h5><label for="edgebanding_bracket_adjustments_<?php echo $room['id']; ?>">Edge Banding Bracket</label></h5></div>
                                            <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="edgebanding_published" value="1" id="edgebanding_bracket_adjustments_<?php echo $room['id']; ?>" <?php echo ((bool)$room['edgebanding_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bracket-border-bottom">
                                        <?php displayBracketOpsMgmt('Main', $room, $individual_bracket); ?>
                                    </td>
                                    <td style="background-color: #eceeef;">&nbsp;</td>
                                    <td class="bracket-border-bottom">
                                        <?php displayBracketOpsMgmt('Edge Banding', $room, $individual_bracket); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bracket-border-top">
                                        <div class="row bracket-header-custom">
                                            <div class="col-md-8"><h5><label for="custom_bracket_adjustments_<?php echo $room['id']; ?>">Custom Bracket</label></h5></div>
                                            <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="custom_published" value="1" id="custom_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['custom_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                        </div>
                                    </td>
                                    <td style="background-color: #eceeef;">&nbsp;</td>
                                    <td class="bracket-border-top">
                                        <div class="row bracket-header-custom">
                                            <div class="col-md-8"><h5><label for="shipping_bracket_adjustments_<?php echo $room['id']; ?>">Shipping Bracket</label></h5></div>
                                            <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="shipping_published" value="1" id="shipping_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['shipping_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bracket-border-bottom">
                                        <?php displayBracketOpsMgmt('Custom', $room, $individual_bracket); ?>
                                    </td>
                                    <td style="background-color: #eceeef;">&nbsp;</td>
                                    <td class="bracket-border-bottom">
                                        <?php displayBracketOpsMgmt('Shipping', $room, $individual_bracket); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bracket-border-top">
                                        <div class="row bracket-header-custom">
                                            <div class="col-md-8"><h5><label for="install_bracket_adjustments_<?php echo $room['id']; ?>">Install Bracket</label></h5></div>
                                            <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="install_published" value="1" id="install_published_<?php echo $room['id']; ?>" <?php echo ((bool)$room['install_bracket_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                        </div>
                                    </td>
                                    <td style="background-color: #eceeef;">&nbsp;</td>
                                    <td class="bracket-border-top">
                                        <div class="row bracket-header-custom">
                                            <div class="col-md-8"><h5><label for="pickmat_bracket_adjustments_<?php echo $room['id']; ?>">Pick & Materials Bracket</label></h5></div>
                                            <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="pickmat_published" value="1" id="pickmat_bracket_adjustments_<?php echo $room['id']; ?>" <?php echo ((bool)$room['pick_materials_published']) ? "checked" : NULL; ?>> <span class="c-indicator"></span> Published</label> </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bracket-border-bottom">
                                        <?php displayBracketOpsMgmt('Installation', $room, $individual_bracket); ?>
                                    </td>
                                    <td style="background-color: #eceeef;">&nbsp;</td>
                                    <td class="bracket-border-bottom">
                                        <?php displayBracketOpsMgmt('Pick & Materials', $room, $individual_bracket); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </form>
    </div>

<script>
    $(".delivery_date").datepicker();

    $(".room_note_log").hide();
</script>