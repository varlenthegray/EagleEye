<?php
require '../includes/header_start.php';

$room_id = sanitizeInput($_REQUEST['room_id']);

$info_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = '$room_id'");
$info = $info_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id = '{$info['contractor_dealer_code']}'");
$dealer_info = $dealer_qry->fetch_assoc();

$sheen_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'sheen' AND `key` = '{$info['sheen']}'");
$sheen = $sheen_qry->fetch_assoc();

function translateVIN($segment, $key) {
    global $dbconn;

    if($segment === 'finish_code') {
        $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE (segment = 'standard_wiping_stains' OR segment = 'colourtone_paints' OR segment = 'benjamin_moore_paints' OR segment = 'sherwin_williams_paints') AND `key` = '$key'");
    } else {
        $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$segment' AND `key` = '$key'");
    }

    $vin = $vin_qry->fetch_assoc();

    return "{$key} = {$vin['value']}";
}
?>

<html>
<head>
    <link href="css/e_coversheet.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>

<!--<body onload="printMe()">-->
<body>

<div id="wrapper">
    <div id="header_container">
        <div id="header_left">
            <div id="page_type">
                <table>
                    <tr>
                        <td colspan="2" id="page_type_header"><?php echo ($info['rOrderStatus'] === '$') ? "Job" : "Quote"; ?></td>
                    </tr>
                    <tr>
                        <td class="definition">Dealer PO#:</td>
                        <td class="value"><?php echo $info['so_parent']; ?></td>
                    </tr>
                    <tr>
                        <td class="definition">Room:</td>
                        <td class="value"><?php echo $info['room']; ?></td>
                    </tr>
                    <tr>
                        <td class="definition">Sequence:</td>
                        <td class="value"><?php echo substr($info['iteration'], 0, 1); ?></td>
                    </tr>
                    <tr>
                        <td class="definition">Iteration:</td>
                        <td class="value"><?php echo substr($info['iteration'], -3, 3); ?></td>
                    </tr>
                    <tr>
                        <td class="definition">Sales Order #:</td>
                        <td class="value"><?php echo "{$info['so_parent']}{$info['room']}-{$info['iteration']}"; ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="logo_container">
            <div id="logo"><img src="/assets/images/smc_logo.png" width="170px" /></div>

            <div id="company_info">
                Stone Mountain Cabinetry, Inc.<br />
                206 Vista Blvd<br/>
                Arden, NC 28704<br />
                828.966.9000<br/>
                orders@smcm.us
            </div>
        </div>

        <div id="header_right">
            <div id="page_info">
                <table>
                    <tr>
                        <td width="80px"># of Pages:</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        <td><?php echo date("m/d/Y"); ?></td>
                    </tr>
                    <tr>
                        <td>Product Type:</td>
                        <td><?php echo translateVIN('product_type', $info['product_type']); ?></td>
                    </tr>
                    <tr>
                        <td>Lead time:</td>
                        <td><?php echo translateVIN('days_to_ship', $info['days_to_ship']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Ship VIA:</strong></td>
                        <td><?php echo (trim($info['vin_ship_via']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['vin_ship_via']}<br />"; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Ship To:</strong></td>
                        <td>
                            <?php
                            echo (trim($info['contact1_name']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['contact1_name']}<br />";
                            echo (trim($info['mailing_addr']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['mailing_addr']}<br />";
                            echo (trim($info['mailing_city']) === '') ? "<span class='highlight'>___________ , ___ , _______</span><br />" : "{$info['mailing_city']}, {$info['mailing_state']} {$info['mailing_zip']}<br />";
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="vin">VIN: <span><?php echo $info['vin_code']; ?></span></div>
    </div>

    <div id="main_section">
        <table>
            <tr>
                <th width="10%"><?php echo "{$info['so_parent']}{$info['room']}-{$info['iteration']}"; ?></th>
                <th class="text-md-right"><?php echo "{$info['contractor_dealer_code']} - {$dealer_info['dealer_name']}"; ?>&nbsp;</th>
            </tr>
            <tr class="border_thin_bottom">
                <td class="gray_bg">Delivery Notes:</td>
                <td><textarea name="delivery_notes" maxlength="280" style="width:100%;text-align:left;" class="static_width" rows="2"></textarea></td>
            </tr>
            <tr class="border_thin_bottom">
                <td class="gray_bg">Global Notes:</td>
                <td><textarea name="global_notes" maxlength="280" style="width:100%;text-align:left;" class="static_width" rows="2"><?php echo $info['vin_notes']; ?></textarea></td>
            </tr>
            <tr>
                <td class="gray_bg">Layout Notes:</td>
                <td><textarea name="layout_notes" maxlength="280" style="width:100%;text-align:left;" class="static_width" rows="2"></textarea></td>
            </tr>
        </table>

        <table>
            <tr>
                <th width="10%">Attributes:</th>
                <th width="12%">&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>QTY</th>
                <th>Amount</th>
                <th class="text-md-center">%</th>
                <th class="text-md-right">Sub-Total&nbsp;</th>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td class="gray_bg">Carcass:</td>
                <td class="border_thin_bottom">Species/Grade:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('species_grade', $info['species_grade']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="dd_species_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_species_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Construction:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('construction_method', $info['construction_method']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="construction_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="construction_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Door Design:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('door_design', $info['door_design']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="dd_design_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_deign_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Door Panel Raise:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_door']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom gray_bg">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Short Drawer Raise:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_sd']); ?></td>
                <td class="border_thin_bottom"><input type="text" name="sd_raise_qty" value="0" maxlength="2" style="width:10px;" class="static_width">x</td>
                <td class="border_thin_bottom">$<span id="short_drawer_raise_price"></span></td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_sd_raise_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Tall Drawer Raise:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_td']); ?></td>
                <td class="border_thin_bottom"><input type="text" name="td_raise_qty" value="0" maxlength="2" style="width:10px;" class="static_width">x</td>
                <td class="border_thin_bottom">$<span id="tall_drawer_raise_price"></span></td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_td_raise_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Edge Profile:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('edge_profile', $info['edge_profile']); ?></td>
                <td class="border_thin_bottom"><input type="text" name="edge_profile_qty" value="0" maxlength="2" style="width:10px;" class="static_width">x</td>
                <td class="border_thin_bottom">$<span id="edge_profile_price"></span></td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_edge_profile_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Framing Bead:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('framing_bead', $info['framing_bead']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom gray_bg">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Frame Option:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('framing_options', $info['framing_options']); ?></td>
                <td class="border_thin_bottom"><input type="text" name="frame_opt_qty" value="0" maxlength="2" style="width:10px;" class="static_width">x</td>
                <td class="border_thin_bottom">$<span id="frame_option_price"></span></td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_frame_option_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Styles/Rails:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('style_rail_width', $info['style_rail_width']); ?></td>
                <td class="border_thin_bottom"><input type="text" name="style_rail_qty" value="0" maxlength="2" style="width:10px;" class="static_width">x</td>
                <td class="border_thin_bottom">$<span id="styles_rails_price"></span></td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_style_rail_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Finish Type:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('finish_type', $info['finish_type']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom gray_bg">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Finish Code:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['finish_code']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom gray_bg">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Sheen:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('sheen', $info['sheen']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom gray_bg">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Glaze Color:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['glaze']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom gray_bg">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Glaze Technique:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('glaze_technique', $info['glaze_technique']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-right border_thin_bottom gray_bg">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom"><input type="checkbox" id="antiquing" style="margin-left:20px;" <?php echo ($info['antiquing'] !== '0') ? "checked" : null; ?> disabled> Antiquing</td>
                <td class="border_thin_bottom"><?php echo translateVIN('antiquing', $info['antiquing']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="antiquing_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_antiquing_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom"><input type="checkbox" id="worn_edges" style="margin-left:20px;" <?php echo ($info['worn_edges'] !== '0') ? "checked" : null; ?> disabled> Worn Edges</td>
                <td class="border_thin_bottom"><?php echo translateVIN('worn_edges', $info['worn_edges']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="worn_edges_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_worn_edges_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom"><input type="checkbox" id="distressing" style="margin-left:20px;" <?php echo ($info['distress_level'] !== '0') ? "checked" : null; ?> disabled> Distressing</td>
                <td class="border_thin_bottom"><?php echo translateVIN('distress_level', $info['distress_level']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="distressing_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_distressing_subtotal"></span></td>
            </tr>
            <tr class="border_top">
                <td>&nbsp;</td>
                <td class="gray_bg">Door/Drawer Head:</td>
                <td class="border_thin_bottom">Exterior Species:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('carcass_exterior_species', $info['carcass_exterior_species']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="exterior_species_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="ext_species_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Exterior Finish Type:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('carcass_exterior_finish_type', $info['carcass_exterior_finish_type']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="exterior_finish_type_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="ext_finish_type_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Exterior Finish Code:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['carcass_exterior_finish_code']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="exterior_finish_code_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="ext_finish_code_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Exterior Glaze Color:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['carcass_exterior_glaze_color']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="exterior_glaze_color_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="ext_glaze_color_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Exterior Glaze Technique:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('carcass_exterior_glaze_technique', $info['carcass_exterior_glaze_technique']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="exterior_glaze_technique_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="ext_glaze_technique_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Interior Species:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('carcass_interior_species', $info['carcass_interior_species']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="interior_species_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="int_species_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Interior Finish Type:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('carcass_interior_finish_type', $info['carcass_interior_finish_type']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="interior_finish_type_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="int_finish_type_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Interior Finish Code:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['carcass_interior_finish_code']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="interior_finish_code_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="int_finish_code_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Interior Glaze Color:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['carcass_interior_glaze_color']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="interior_glaze_color_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="int_glaze_color_subtotal"></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Interior Glaze Technique:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('carcass_interior_glaze_technique', $info['carcass_interior_glaze_technique']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="interior_glaze_technique_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="int_glaze_technique_subtotal"></span></td>
            </tr>
            <tr class="border_double_bottom">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="border_thin_bottom">Drawer Box:</td>
                <td class="border_thin_bottom"><?php echo translateVIN('drawer_boxes', $info['drawer_boxes']); ?></td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="border_thin_bottom gray_bg">&nbsp;</td>
                <td class="text-md-center border_thin_bottom"><input type="text" name="drawer_boxes_pct" value="0.00" maxlength="4">%</td>
                <td class="text-md-right border_thin_bottom">$<span id="dd_drawer_box_subtotal"></span></td>
            </tr>
        </table>
    </div>

    <div id="terms_box">
        <table>
            <tr>
                <td class="text-underline">Please check box to accept terms</td>
                <td>(Must be checked to validate acceptance)</td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" id="lead_start"> Lead time starts once quote has been signed below & deposit has been received</td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" id="50_deposit"> I approve 50% deposit to be charged:</td>
            </tr>
            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td width="20px">&nbsp;</td>
                            <td><input type="checkbox" id="deposit_ACH"> ACH</td>
                            <td><input type="checkbox" id="deposit_check"> Attached photocopy of check</td>
                            <td><input type="checkbox" id="deposit_credit"> Credit Card (<span id="credit_card_pct"></span>%)</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" id="final_payment"> Final payment due upon delivery & will be charged to ACH account prior to offloading<br/><span style="margin-left:25px;">(Exceptions to made prior to delivery)</span></td>
            </tr>
            <tr>
                <td colspan="2" style="height: 5px;"></td>
            </tr>
            <tr>
                <td><span class="text-underline">Finish/Sample</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Check only one)</td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" id="door_approved"> I have seen the Door style w/ Finish and it is APPROVED</td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" id="door_approved"> I APPROVE the Finish & Door style without seeing a sample</td>
            </tr>
            <tr>
                <td colspan="2"><input type="checkbox" id="door_approved"> I request a Sample for approval &nbsp;&nbsp;&nbsp; (Overall Size)</td>
            </tr>
            <tr>
                <td colspan="2">
                    <table style="margin-left: 20px;">
                        <tr>
                            <td width="100px"><input type="checkbox" id="sample_block"> Sample Block</td>
                            <td class="text-md-right">$<span id="sample_block_price"></span></td>
                            <td class="text-md-center" width="100px">5 1/4" x 6 1/8"</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" id="door_only"> Door Only</td>
                            <td class="text-md-right">$<span id="door_only_price"></span></td>
                            <td class="text-md-center">12" x 15"</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" id="door_drawer"> Door & Drawer</td>
                            <td class="text-md-right">$<span id="door_drawer_price"></span></td>
                            <td class="text-md-center">15 1/2" x 23 1/2"</td>
                            <td>(Door & Drawer Front attached)</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" id="inset_square"> Inset Square</td>
                            <td class="text-md-right">$<span id="inset_square_price"></span></td>
                            <td class="text-md-center">15 1/2" x 23 1/2"</td>
                            <td>(Door, Drawer Front & Frame)</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" id="inset_beaded"> Inset Beaded</td>
                            <td class="text-md-right">$<span id="inset_beaded_price"></span></td>
                            <td class="text-md-center">16 1/2" x 23 1/2"</td>
                            <td>(Door, Drawer Front & Frame)</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div id="charge_summary">
        <table align="right">
            <tr>
                <td colspan="2">
                    <table>
                        <tr class="border_thin_bottom">
                            <td width="230px">Upcharges:</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_upcharges"></span></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td>Cabinet List Price:</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<input type="text" name="list_price" value="0.00" maxlength="10"></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td>Modifications & Accessories:</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<input type="text" name="mods_accessories" value="0.00" maxlength="10"></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td>Lead Time: <span class="em_box" style="margin-left: 50px;">Standard</span></td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_leadtime"></span></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td>Sub Total:</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_subtotal"></span></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td>Multiplier:</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right"><input type="text" name="multiplier" value="0.407" maxlength="5" class="static_width" style="width:23px;"></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td>Net:</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_net"></span></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td>Shipping Zone: <span class="em_box" style="margin-left: 20px;"><input type="text" name="final_ship_zone" id="final_ship_zone" value="" maxlength="1" class="static_width" style="width:10px;text-align:center;"></span> <span id="final_ship_zone_miles"></span></td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_shipping"></span></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td><input type="checkbox" id="final_freight_check" class="no-print"> Min. Freight: (Under 6 Cabinets)</td>
                            <td>$<span id="min_freight_price"></span></td>
                            <td class="text-md-right">$<span id="final_freight"></span></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td><input type="checkbox" id="final_jobsite_check" class="no-print"> Jobsite Delivery:</td>
                            <td>$<span id="jobsite_del_price"></span></td>
                            <td class="text-md-right">$<span id="final_jobsite"></span></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td><input type="checkbox" id="final_cc_check" class="no-print"> Credit Card: +3.5%</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_cc"></span></td>
                        </tr>
                        <tr class="border_thin_bottom">
                            <td>Samples</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_samples"></span></td>
                        </tr>
                        <tr class="em_box">
                            <td>Sub Total:</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_last_subtotal"></span></td>
                        </tr>
                        <tr class="header em_box">
                            <td>Total Amount</td>
                            <td>&nbsp;</td>
                            <td class="text-md-right">$<span id="final_total"></span></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="em_box" style="padding-left:20px;">50% Deposit due to start production</td>
                            <td class="text-md-right em_box">$<span id="final_deposit"></span></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div id="signature">
        Signature: ______________________________________________________________________________<br/>
        (Signature confirms acceptance and acknowledges that the deposit will be auto-drafted within 24 hours)
    </div>

    <div id="closing_statement">Thank you! Our Commitment is to Quality in Both our Cabinetry & Our Customer Service!</div>
</div>

<script src="/assets/js/jquery.min.js"></script>

<script>
    /** Fixed Pricing */
    var styles_rails_price = 8.00;
    var short_drawer_raise_price = 65.00;
    var tall_drawer_raise_price = 65.00;
    var edge_profile_price = 57.00;

    <?php
        if($info['framing_options'] === '1') {
            echo "var frame_option_price = 14.00;";
        } elseif($info['framing_options'] === '2') {
            echo "var frame_option_price = 28.00;";
        } elseif($info['framing_options'] === '3') {
            echo "var frame_option_price = 28.00;";
        } else {
            echo "var frame_option_price = 0.00;";
        }
    ?>

    var credit_card_pct = 3.5;

    var sample_block_price = 11.00;
    var door_only_price = 46.00;
    var door_drawer_price = 101.00;
    var inset_square_price = 111.00;
    var inset_beaded_price = 116.00;

    var min_freight_price = 150.00;
    var jobsite_del_price = 150.00;

    $("#min_freight_price").html(min_freight_price.toFixed(2));
    $("#jobsite_del_price").html(jobsite_del_price.toFixed(2));

    $("#inset_beaded_price").html(inset_beaded_price.toFixed(2));
    $("#inset_square_price").html(inset_square_price.toFixed(2));
    $("#door_drawer_price").html(door_drawer_price.toFixed(2));
    $("#door_only_price").html(door_only_price.toFixed(2));
    $("#sample_block_price").html(sample_block_price.toFixed(2));

    $("#credit_card_pct").html(credit_card_pct.toFixed(1));

    $("#styles_rails_price").html(styles_rails_price.toFixed(2));
    $("#short_drawer_raise_price").html(short_drawer_raise_price.toFixed(2));
    $("#tall_drawer_raise_price").html(tall_drawer_raise_price.toFixed(2));
    $("#edge_profile_price").html(edge_profile_price.toFixed(2));
    $("#frame_option_price").html(frame_option_price.toFixed(2));
    /** End Fixed Pricing */

    /** Charge Summary */
    var final_upcharges = 0.00;
    var cabinet_list_price = $("input[name='list_price']").val();
    var mods_accessories = $("input[name='mods_accessories']").val();
    var final_leadtime = 0.00;
    var final_subtotal = 0.00;
    var final_multiplier = $("input[name='multiplier']");
    var final_net = 0.00;
    var final_ship_zone = 'A';
    var final_ship_zone_miles = '(0-100 Miles)';
    var final_shipping = 0.00;
    var final_freight_check = $("#final_freight_check").is(':checked');
    var final_freight = 0.00;
    var final_jobsite_check = $("#final_jobsite_check").is(':checked');
    var final_jobsite = 0.00;
    var final_cc_check = $("#final_cc_check").is(':checked');
    var final_cc = 0.00;
    var final_samples = 0.00;
    var final_last_subtotal = 0.00;
    var final_tax = 0.00;
    var final_total = 0.00;
    var final_deposit = 0.00;

    $("#final_upcharges").html(final_upcharges.toFixed(2));
    $("#final_leadtime").html(final_leadtime.toFixed(2));
    $("#final_subtotal").html(final_subtotal.toFixed(2));
    $("#final_net").html(final_net.toFixed(2));
    $("#final_shipping").html(final_shipping.toFixed(2));
    $("#final_ship_zone").val(final_ship_zone);
    $("#final_ship_zone_miles").html(final_ship_zone_miles);
    $("#final_freight").html(final_freight.toFixed(2));
    $("#final_jobsite").html(final_jobsite.toFixed(2));
    $("#final_cc").html(final_cc.toFixed(2));
    $("#final_samples").html(final_samples.toFixed(2));
    $("#final_last_subtotal").html(final_last_subtotal.toFixed(2));
    $("#final_tax").html(final_tax.toFixed(2));
    $("#final_total").html(final_total.toFixed(2));
    $("#final_deposit").html(final_deposit.toFixed(2));
    /** End Charge Summary */

    /** Attribute Subtotals */
    var construction_subtotal = 0.00;
    var ext_species_subtotal = 0.00;
    var ext_finish_type_subtotal = 0.00;
    var ext_finish_code_subtotal = 0.00;
    var ext_glaze_color_subtotal = 0.00;
    var ext_glaze_technique_subtotal = 0.00;
    var int_species_subtotal = 0.00;
    var int_finish_type_subtotal = 0.00;
    var int_finish_code_subtotal = 0.00;
    var int_glaze_color_subtotal = 0.00;
    var int_glaze_technique_subtotal = 0.00;
    var dd_species_subtotal = 0.00;
    var dd_grade_subtotal = 0.00;
    var dd_deign_subtotal = 0.00;
    var dd_style_rail_subtotal = 0.00;
    var dd_sd_raise_subtotal = 0.00;
    var dd_td_raise_subtotal = 0.00;
    var dd_edge_profile_subtotal = 0.00;
    var dd_frame_option_subtotal = 0.00;
    var dd_antiquing_subtotal = 0.00;
    var dd_distressing_subtotal = 0.00;
    var dd_worn_edges_subtotal = 0.00;
    var dd_drawer_box_subtotal = 0.00;

    $("#construction_subtotal").html(construction_subtotal.toFixed(2));
    $("#ext_species_subtotal").html(ext_species_subtotal.toFixed(2));
    $("#ext_finish_type_subtotal").html(ext_finish_type_subtotal.toFixed(2));
    $("#ext_finish_code_subtotal").html(ext_finish_code_subtotal.toFixed(2));
    $("#ext_glaze_color_subtotal").html(ext_glaze_color_subtotal.toFixed(2));
    $("#ext_glaze_technique_subtotal").html(ext_glaze_technique_subtotal.toFixed(2));
    $("#int_species_subtotal").html(int_species_subtotal.toFixed(2));
    $("#int_finish_type_subtotal").html(int_finish_type_subtotal.toFixed(2));
    $("#int_finish_code_subtotal").html(int_finish_code_subtotal.toFixed(2));
    $("#int_glaze_color_subtotal").html(int_glaze_color_subtotal.toFixed(2));
    $("#int_glaze_technique_subtotal").html(int_glaze_technique_subtotal.toFixed(2));
    $("#dd_species_subtotal").html(dd_species_subtotal.toFixed(2));
    $("#dd_grade_subtotal").html(dd_grade_subtotal.toFixed(2));
    $("#dd_deign_subtotal").html(dd_deign_subtotal.toFixed(2));
    $("#dd_style_rail_subtotal").html(dd_style_rail_subtotal.toFixed(2));
    $("#dd_sd_raise_subtotal").html(dd_sd_raise_subtotal.toFixed(2));
    $("#dd_td_raise_subtotal").html(dd_td_raise_subtotal.toFixed(2));
    $("#dd_edge_profile_subtotal").html(dd_edge_profile_subtotal.toFixed(2));
    $("#dd_frame_option_subtotal").html(dd_frame_option_subtotal.toFixed(2));
    $("#dd_antiquing_subtotal").html(dd_antiquing_subtotal.toFixed(2));
    $("#dd_distressing_subtotal").html(dd_distressing_subtotal.toFixed(2));
    $("#dd_worn_edges_subtotal").html(dd_worn_edges_subtotal.toFixed(2));
    $("#dd_drawer_box_subtotal").html(dd_drawer_box_subtotal.toFixed(2));
    /** End Attribute Subtotals */

    /** Percent Fields */
    var construction_pct = $("#construction_pct").val();
    var exterior_species_pct = $("#exterior_species_pct").val();
    var exterior_finish_type_pct = $("#exterior_finish_type_pct").val();
    var exterior_finish_code_pct = $("#exterior_finish_code_pct").val();
    var exterior_glaze_color_pct = $("#exterior_glaze_color_pct").val();
    var exterior_glaze_technique_pct = $("#exterior_glaze_technique_pct").val();
    var interior_species_pct = $("#interior_species_pct").val();
    var interior_finish_type_pct = $("#interior_finish_type_pct").val();
    var interior_finish_code_pct = $("#interior_finish_code_pct").val();
    var interior_glaze_color_pct = $("#interior_glaze_color_pct").val();
    var interior_glaze_technique_pct = $("#interior_glaze_technique_pct").val();
    var dd_species_pct = $("#dd_species_pct").val();
    var dd_grade_pct = $("#dd_grade_pct").val();
    var dd_design_pct = $("#dd_design_pct").val();
    var antiquing_pct = $("#antiquing_pct").val();
    var distressing_pct = $("#distressing_pct").val();
    var worn_edges_pct = $("#worn_edges_pct").val();
    var drawer_boxes_pct = $("#drawer_boxes_pct").val();
    /** End Percent Fields */

    /** Qty Fields */
    var style_rail_qty = $("input[name='style_rail_qty']").val();
    var sd_raise_qty = $("input[name='sd_raise_qty']").val();
    var td_raise_qty = $("input[name='td_raise_qty']").val();
    var edge_profile_qty = $("input[name='edge_profile_qty']").val();
    var frame_opt_qty = $("input[name='frame_opt_qty']").val();
    /** End Qty Fields */

    function printMe() {
        window.print();

        setTimeout(function() {
            window.close();
        }, 100);
    }

    $('body')
        .on("change", "#exterior_finish_type_rs", function() {
            $(this).hide();
            $("#rs_ext_ft").show();
        })
        .on("click", "#rs_ext_ft", function() {
            $(this).hide();
            $("#exterior_finish_type_rs").attr("checked", false).show();
        })
        .on("change", "#interior_finish_type_rs", function() {
            $(this).hide();
            $("#rs_int_ft").show();
        })
        .on("click", "#rs_int_ft", function() {
            $(this).hide();
            $("#interior_finish_type_rs").attr("checked", false).show();
        })
        .on("change", "#finish_type_rs", function() {
            $(this).hide();
            $("#rs_ft").show();
        })
        .on("click", "#rs_ft", function() {
            $(this).hide();
            $("#finish_type_rs").attr("checked", false).show();
        })
        .on("focus", "input[type='text']", function() {
            $(this).select();
        })
        .on("keyup", "input[type='text']", function() {
            if($(this).val().length > 4) {
                if($(this).attr("class") !== 'static_width') {
                    if((event.keyCode === 8 || event.keyCode === 46) && $(this).width() > 18) {
                        if($(this).val() <= 4) {
                            $(this).width(18);
                        } else {
                            $(this).width($(this).width() - 8);
                        }
                    } else if(((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 65 && event.keyCode <= 90) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode === 190) && $(this).val().length < 10) {
                        if($(this).val() <= 4) {
                            $(this).width(18);
                        } else {
                            $(this).width($(this).width() + 8);
                        }
                    }
                }
            } else {
                if($(this).attr("class") !== 'static_width') {
                    $(this).width(18);
                }
            }
        })
        .on("change", "input", function() {
            /** Percent Fields */
            construction_pct = $("input[name='construction_pct'").val();
            exterior_species_pct = $("input[name='exterior_species_pct'").val();
            exterior_finish_type_pct = $("input[name='exterior_finish_type_pct'").val();
            exterior_finish_code_pct = $("input[name='exterior_finish_code_pct'").val();
            exterior_glaze_color_pct = $("input[name='exterior_glaze_color_pct'").val();
            exterior_glaze_technique_pct = $("input[name='exterior_glaze_technique_pct'").val();
            interior_species_pct = $("input[name='interior_species_pct'").val();
            interior_finish_type_pct = $("input[name='interior_finish_type_pct'").val();
            interior_finish_code_pct = $("input[name='interior_finish_code_pct'").val();
            interior_glaze_color_pct = $("input[name='interior_glaze_color_pct'").val();
            interior_glaze_technique_pct = $("input[name='interior_glaze_technique_pct'").val();
            dd_species_pct = $("input[name='dd_species_pct'").val();
            dd_design_pct = $("input[name='dd_design_pct'").val();
            antiquing_pct = $("input[name='antiquing_pct'").val();
            distressing_pct = $("input[name='distressing_pct'").val();
            worn_edges_pct = $("input[name='worn_edges_pct'").val();
            drawer_boxes_pct = $("input[name='drawer_boxes_pct'").val();
            /** End Percent Fields */

            /** Qty Fields */
            style_rail_qty = $("input[name='style_rail_qty']").val();
            sd_raise_qty = $("input[name='sd_raise_qty']").val();
            td_raise_qty = $("input[name='td_raise_qty']").val();
            edge_profile_qty = $("input[name='edge_profile_qty']").val();
            frame_opt_qty = $("input[name='frame_opt_qty']").val();
            /** End Qty Fields */

            /** Attribute Subtotals */
            construction_subtotal = (construction_pct / 100) * cabinet_list_price;
            ext_species_subtotal = (exterior_species_pct / 100) * cabinet_list_price;
            ext_finish_type_subtotal = (exterior_finish_type_pct / 100) * cabinet_list_price;
            ext_finish_code_subtotal = (exterior_finish_code_pct / 100) * cabinet_list_price;
            ext_glaze_color_subtotal = (exterior_glaze_color_pct / 100) * cabinet_list_price;
            ext_glaze_technique_subtotal = (exterior_glaze_technique_pct / 100) * cabinet_list_price;
            int_species_subtotal = (interior_species_pct / 100) * cabinet_list_price;
            int_finish_type_subtotal = (interior_finish_type_pct / 100) * cabinet_list_price;
            int_finish_code_subtotal = (interior_finish_code_pct / 100) * cabinet_list_price;
            int_glaze_color_subtotal = (interior_glaze_color_pct / 100) * cabinet_list_price;
            int_glaze_technique_subtotal = (interior_glaze_technique_pct / 100) * cabinet_list_price;
            dd_species_subtotal = (dd_species_pct / 100) * cabinet_list_price;
            dd_deign_subtotal = (dd_design_pct / 100) * cabinet_list_price;
            dd_style_rail_subtotal = style_rail_qty * styles_rails_price;
            dd_sd_raise_subtotal = sd_raise_qty * short_drawer_raise_price;
            dd_td_raise_subtotal = td_raise_qty * tall_drawer_raise_price;
            dd_edge_profile_subtotal = edge_profile_qty * edge_profile_price;
            dd_frame_option_subtotal = frame_opt_qty * frame_option_price;
            dd_antiquing_subtotal = (antiquing_pct / 100) * cabinet_list_price;
            dd_distressing_subtotal = (distressing_pct / 100) * cabinet_list_price;
            dd_worn_edges_subtotal = (worn_edges_pct / 100) * cabinet_list_price;
            dd_drawer_box_subtotal = (drawer_boxes_pct / 100) * cabinet_list_price;

            $("#construction_subtotal").html(construction_subtotal.toFixed(2));
            $("#ext_species_subtotal").html(ext_species_subtotal.toFixed(2));
            $("#ext_finish_type_subtotal").html(ext_finish_type_subtotal.toFixed(2));
            $("#ext_finish_code_subtotal").html(ext_finish_code_subtotal.toFixed(2));
            $("#ext_glaze_color_subtotal").html(ext_glaze_color_subtotal.toFixed(2));
            $("#ext_glaze_technique_subtotal").html(ext_glaze_technique_subtotal.toFixed(2));
            $("#int_species_subtotal").html(int_species_subtotal.toFixed(2));
            $("#int_finish_type_subtotal").html(int_finish_type_subtotal.toFixed(2));
            $("#int_finish_code_subtotal").html(int_finish_code_subtotal.toFixed(2));
            $("#int_glaze_color_subtotal").html(int_glaze_color_subtotal.toFixed(2));
            $("#int_glaze_technique_subtotal").html(int_glaze_technique_subtotal.toFixed(2));
            $("#dd_species_subtotal").html(dd_species_subtotal.toFixed(2));
            $("#dd_deign_subtotal").html(dd_deign_subtotal.toFixed(2));
            $("#dd_style_rail_subtotal").html(dd_style_rail_subtotal.toFixed(2));
            $("#dd_sd_raise_subtotal").html(dd_sd_raise_subtotal.toFixed(2));
            $("#dd_td_raise_subtotal").html(dd_td_raise_subtotal.toFixed(2));
            $("#dd_edge_profile_subtotal").html(dd_edge_profile_subtotal.toFixed(2));
            $("#dd_frame_option_subtotal").html(dd_frame_option_subtotal.toFixed(2));
            $("#dd_antiquing_subtotal").html(dd_antiquing_subtotal.toFixed(2));
            $("#dd_distressing_subtotal").html(dd_distressing_subtotal.toFixed(2));
            $("#dd_worn_edges_subtotal").html(dd_worn_edges_subtotal.toFixed(2));
            $("#dd_drawer_box_subtotal").html(dd_drawer_box_subtotal.toFixed(2));
            /** End Attribute Subtotals */

            /** Charge Summary */
            final_upcharges = construction_subtotal + ext_species_subtotal + ext_finish_type_subtotal + ext_finish_code_subtotal + ext_glaze_color_subtotal + ext_glaze_technique_subtotal +
                int_species_subtotal + int_finish_type_subtotal + int_finish_code_subtotal + int_glaze_color_subtotal + int_glaze_technique_subtotal + dd_species_subtotal +
                dd_deign_subtotal + dd_style_rail_subtotal + dd_sd_raise_subtotal + dd_td_raise_subtotal + dd_edge_profile_subtotal + dd_frame_option_subtotal + dd_antiquing_subtotal + dd_distressing_subtotal +
                dd_worn_edges_subtotal + dd_drawer_box_subtotal;
            cabinet_list_price = $("input[name='list_price']").val();
            mods_accessories = $("input[name='mods_accessories']").val();

            <?php
                switch($info['days_to_ship']) {
                    case 'Y':
                        /** @var double Shipping multiplier $sm */
                        $sm = ($info['product_type'] === 'C') ? "var shipping_multiplier = 0.25;" : "var shipping_multiplier = 0;";
                        break;

                    case 'N':
                        if($info['product_type'] === 'C') {
                            $sm = "var shipping_multiplier = 0.50;";
                        } elseif($info['product_type'] === 'L') {
                            $sm = "var shipping_multiplier = 0.25;";
                        } elseif($info['product_type'] === 'A') {
                            $sm = "var shipping_multiplier = 0.25;";
                        }

                        break;

                    case 'R':
                        if($info['product_type'] === 'S') {
                            $sm = "var shipping_multiplier = 0.25;";
                        } elseif($info['product_type'] === 'A') {
                            $sm = "var shipping_multiplier = 0.25;";
                        }
                }

                if(!isset($sm)) {
                    $sm = "var shipping_multiplier = 0;";
                }

                echo $sm;
            ?>

            final_leadtime = parseFloat(shipping_multiplier) * parseFloat(cabinet_list_price);
            final_subtotal = parseFloat(final_upcharges) + parseFloat(cabinet_list_price) + parseFloat(mods_accessories) + parseFloat(final_leadtime);
            final_multiplier = $("input[name='multiplier']").val();
            final_net = parseFloat(final_subtotal) * parseFloat(final_multiplier);

            switch($("input[name='final_ship_zone']").val().toUpperCase()) {
                case 'A':
                    final_ship_zone_miles = '(0-100 Miles)';
                    final_shipping = 0.00;
                    break;

                case 'B':
                    final_ship_zone_miles = '(100-200 Miles)';
                    final_shipping = 150.00;
                    break;

                case 'C':
                    final_ship_zone_miles = '(200-300 Miles)';
                    final_shipping = 300.00;
                    break;

                case 'D':
                    final_ship_zone_miles = '(300-400 Miles)';
                    final_shipping = 450.00;
                    break;

                case 'E':
                    final_ship_zone_miles = '(400-500 Miles)';
                    final_shipping = 600.00;
                    break;

                case 'F':
                    final_ship_zone_miles = '(500-600 Miles)';
                    final_shipping = 750.00;
                    break;
            }

            if($("#final_freight_check").is(':checked')) {
                final_freight = 150.00;
            } else {
                final_freight = 0.00;
            }

            if($("#final_jobsite_check").is(':checked')) {
                final_jobsite = 150.00;
            } else {
                final_jobsite = 0.00;
            }

            if($("#final_cc_check").is(':checked')) {
                final_cc = parseFloat(final_net) * 0.035;
            } else {
                final_cc = 0.00;
            }

            final_samples = 0.00;

            if($("#sample_block").is(':checked')) {
                final_samples = final_samples + sample_block_price;
            }

            if($("#door_only").is(':checked')) {
                final_samples = final_samples + door_only_price;
            }

            if($("#door_drawer").is(':checked')) {
                final_samples = final_samples + door_drawer_price;
            }

            if($("#inset_square").is(':checked')) {
                final_samples = final_samples + inset_square_price;
            }

            if($("#inset_beaded").is(':checked')) {
                final_samples = final_samples + inset_beaded_price;
            }

            final_last_subtotal = parseFloat(final_net) + parseFloat(final_shipping) + parseFloat(final_freight) + parseFloat(final_jobsite) + parseFloat(final_cc) + parseFloat(final_samples);
            final_total = parseFloat(final_last_subtotal) + parseFloat(final_tax);
            final_deposit = parseFloat(final_total) * .5;

            $("#final_upcharges").html(final_upcharges.toFixed(2));
            $("#final_leadtime").html(final_leadtime.toFixed(2));
            $("#final_subtotal").html(final_subtotal.toFixed(2));
            $("#final_net").html(final_net.toFixed(2));
            $("#final_ship_zone_miles").html(final_ship_zone_miles);
            $("#final_shipping").html(final_shipping.toFixed(2));
            $("#final_freight").html(final_freight.toFixed(2));
            $("#final_jobsite").html(final_jobsite.toFixed(2));
            $("#final_cc").html(final_cc.toFixed(2));
            $("#final_samples").html(final_samples.toFixed(2));
            $("#final_last_subtotal").html(final_last_subtotal.toFixed(2));
            $("#final_tax").html(final_tax.toFixed(2));
            $("#final_total").html(final_total.toFixed(2));
            $("#final_deposit").html(final_deposit.toFixed(2));
            /** End Charge Summary */
        })
        .on("keyup", "#final_ship_zone", function() {
            $(this).val($(this).val().toUpperCase());
        })
    ;
</script>
</body>
</html>