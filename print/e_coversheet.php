<?php
require '../includes/header_start.php';

$room_id = sanitizeInput($_REQUEST['room_id']);

$info_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = '$room_id'");
$info = $info_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$info['dealer_code']}'");
$dealer_info = $dealer_qry->fetch_assoc();

$sheen_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'sheen' AND `key` = '{$info['sheen']}'");
$sheen = $sheen_qry->fetch_assoc();

function translateVIN($segment, $key) {
  global $dbconn;
  global $info;

  $output = array();

  $custom_keys = ['X', 'Xxx', 'AX', 'DX', 'TX', 'Xx', 'WX', '1cXXXX', '3gXXXX'];

  if($segment === 'finish_code') {
    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE (segment = 'finish_code') AND `key` = '$key'");
  } else {
    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$segment' AND `key` = '$key'");
  }

  $vin = $vin_qry->fetch_assoc();

  $mfg = '';
  $code = '';
  $name = '';
  $ikey = '';
  $desc = '';

  if(!empty($info['custom_vin_info'])) {
    if(in_array($key, $custom_keys)) {
      $custom_info = json_decode($info['custom_vin_info'], true);

      if(count($custom_info[$segment]) > 1) {
        foreach($custom_info[$segment] as $key => $value) {
          $mfg = (stristr($key, 'mfg')) ? $value : $mfg;
          $code = (stristr($key, 'code')) ? $value : $code;
          $name = (stristr($key, 'name')) ? $value : $name;
        }

        $ikey = "{$mfg}-{$code}";
        $desc = "$name";
      } else {
        $ikey = $key;
        $desc = "Custom - " . array_values($custom_info[$segment])[0];
      }
    } else {
      $ikey = $key;
      $desc = $vin['value'];
    }
  } else {
    $ikey = $key;
    $desc = $vin['value'];
  }

  return "$ikey = $desc";
}

$note_arr = array();

$notes_qry = $dbconn->query("SELECT * FROM notes WHERE (note_type = 'room_note_delivery' OR note_type = 'room_note_global' OR note_type = 'room_note_fin_sample') AND type_id = '$room_id'");

if($notes_qry->num_rows > 0) {
  while($notes = $notes_qry->fetch_assoc()) {
    $note_arr[$notes['note_type']] = $notes['note'];
  }
}

if($_REQUEST['action'] === 'sample_req' || $_REQUEST['action'] === 'no_totals') {
  $hide .= "$('#sample_confirmation').hide();";
  $hide .= "$('#terms_acceptance').hide();";
  $hide .= "$('#sample_qty').css('margin-top', '30px');";
}
?>
<!DOCTYPE html>

<html moznomarginboxes="" mozdisallowselectionprint="">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A fully functional ERP designed to manage cabinetry and automation.">
  <meta name="author" content="Stone Mountain Cabinetry & Millwork">

  <link href="css/e_coversheet.css?v=012920181528" type="text/css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
  <script src="/assets/js/jquery.min.js"></script>
  <script src="/includes/js/functions.js?v=<?php echo VERSION; ?>"></script>
</head>

<!--<body onload="printMe()">-->
<body>

<div id="wrapper">
  <form name="e_coversheet_total" id="e_coversheet_total">
    <div id="header_container">
      <div id="header_left">
        <div id="page_type">
          <table>
            <tr>
              <td colspan="2" id="page_type_header"><?php echo ($info['rOrderStatus'] === '$') ? "Production" : "Quote"; ?></td>
            </tr>
            <tr>
              <td class="definition">Dealer PO#:</td>
              <td class="value"><?php echo "{$info['project_name']} - {$info['room_name']}"; ?></td>
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
              <td>Printed:</td>
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
              <td><input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_via" value="<?php echo $info['vin_ship_via']; ?>"></td>
            </tr>
            <tr>
              <td><strong>Ship To:</strong></td>
              <td>
                <!--<input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_1" value="<?php /*echo $info['name_1']; */?>"><br />
                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_2" value="<?php /*echo $info['project_addr']; */?>"><br />
                <input type="text" style="width:76px;" class="static_width align_left border_thin_bottom" name="ship_to_city" value="<?php /*echo $info['project_city']; */?>">
                <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php /*echo $info['project_state']; */?>">
                <input type="text" style="width:30px;" class="static_width align_left border_thin_bottom" name="ship_to_zip" value="<?php /*echo $info['project_zip']; */?>">-->
                <!-- TODO: Fix this so that it lines up with the contact cards, not being used right now -->
                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_1" value="<?php echo $dealer_info['dealer_name']; ?>"><br />
                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_2" value="<?php echo $dealer_info['physical_address']; ?>"><br />
                <input type="text" style="width:76px;" class="static_width align_left border_thin_bottom" name="ship_to_city" value="<?php echo $dealer_info['physical_city']; ?>">
                <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php echo $dealer_info['physical_state']; ?>">
                <input type="text" style="width:30px;" class="static_width align_left border_thin_bottom" name="ship_to_zip" value="<?php echo $dealer_info['physical_zip']; ?>">
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div id="main_section">
      <table>
        <tr>
          <th colspan="3"><span class="pull-left"><?php echo $info['vin_code']; ?></span><span class="pull-right"><?php echo "{$info['dealer_code']} - {$dealer_info['company_name']}"; ?></span></th>
        </tr>
        <tr>
          <td style="border-right:1px solid #000;" class="gray_bg">Room Notes:</td>
          <td style="border-right:1px solid #000;" class="gray_bg">Delivery Notes:</td>
          <td class="gray_bg">Finishing/Sample Notes:</td>
        </tr>

        <tr id="notes_section">
          <td id="global_notes"><textarea name="global_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_global']; ?></textarea></td>
          <td id="delivery_notes"><textarea name="delivery_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_delivery']; ?></textarea></td>
          <td id="layout_notes_title"><textarea name="layout_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_fin_sample']; ?></textarea></td>
        </tr>
      </table>

      <table>
        <tr>
          <th width="65.2%" style="height:15px;"></th>
          <th colspan="2" style="height:15px;"><span class="subtotal">Summary of Charges</span></th>
        </tr>
        <tr class="subtotal">
          <td></td>
          <td class="border_thin_bottom total_text">Modifications & Accessories:</td>
          <td class="text-md-right border_thin_bottom total_text">$<input type="text" name="mods_accessories" value="0.00" maxlength="10"></td>
        </tr>
        <tr class="subtotal">
          <td></td>
          <td class="total_text border_thin_bottom">Cabinet List Price:</td>
          <td class="text-md-right border_thin_bottom total_text">$<input type="text" name="list_price" value="0.00" maxlength="10"></td>
        </tr>
      </table>

      <table style="width:80%;margin:0 auto;">
        <tr>
          <th class="dark_gray_bg" width="7%">Attributes:</th>
          <th class="dark_gray_bg" width="15%">&nbsp;</th>
          <th class="dark_gray_bg" width="18%">&nbsp;</th>
          <th class="dark_gray_bg text-md-right" id="executive_ref"></th>
          <th class="dark_gray_bg">QTY</th>
          <th class="dark_gray_bg">Amount</th>
          <th class="dark_gray_bg text-md-center pct_value">%</th>
          <th class="dark_gray_bg text-md-right subtotal">Upcharges&nbsp;</th>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <?php echo ($info['construction_method'] !== 'L') ? "<td class='gray_bg'>Door/Drawer Head:</td>" : "<td></td>"; ?>
          <td class="border_thin_bottom">Species/Grade:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('species_grade', $info['species_grade']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="dd_species_pct" id="dd_species_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_species_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Construction:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('construction_method', $info['construction_method']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="construction_pct" id="construction_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="construction_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Door Design:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('door_design', $info['door_design']); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="dd_custom_pm" value="">)</span></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="dd_design_pct" id="dd_design_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_deign_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Door Panel Raise:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_door']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
          <td class="text-md-right border_thin_bottom gray_bg subtotal">&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Short Drawer Raise:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_sd']); ?></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom toggle_gray print_gray"><input type="text" name="sd_raise_qty" value="0" maxlength="2" style="width:10px;" class="static_width print_gray">x</td>
          <td class="border_thin_bottom toggle_gray print_gray">$<span id="short_drawer_raise_price"></span></td>
          <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_sd_raise_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Tall Drawer Raise:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_td']); ?></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom toggle_gray print_gray"><input type="text" name="td_raise_qty" value="0" maxlength="2" style="width:10px;" class="static_width print_gray">x</td>
          <td class="border_thin_bottom toggle_gray print_gray">$<span id="tall_drawer_raise_price"></span></td>
          <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_td_raise_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Edge Profile:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('edge_profile', $info['edge_profile']); ?></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom toggle_gray print_gray"><input type="text" name="edge_profile_qty" value="0" maxlength="2" style="width:10px;" class="static_width print_gray">x</td>
          <td class="border_thin_bottom toggle_gray print_gray">$<span id="edge_profile_price"></span></td>
          <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_edge_profile_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Framing Bead:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('framing_bead', $info['framing_bead']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
          <td class="text-md-right border_thin_bottom gray_bg subtotal">&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Frame Option:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('framing_options', $info['framing_options']); ?></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom toggle_gray print_gray"><input type="text" name="frame_opt_qty" value="0" maxlength="2" style="width:10px;" class="static_width print_gray">x</td>
          <td class="border_thin_bottom toggle_gray print_gray">$<span id="frame_option_price"></span></td>
          <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_frame_option_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Styles/Rails:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('style_rail_width', $info['style_rail_width']); ?></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom gray_bg toggle_gray" style="display:none;"></td>
          <td class="border_thin_bottom toggle_gray print_gray"><input type="text" name="style_rail_qty" value="0" maxlength="2" style="width:10px;" class="static_width print_gray">x</td>
          <td class="border_thin_bottom toggle_gray print_gray">$<span id="styles_rails_price"></span></td>
          <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_style_rail_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Finish Code:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['finish_code']); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="finish_code_pm" value="">)</span></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value gray_bg"></td>
          <td class="text-md-right border_thin_bottom subtotal">$<input type="text" name="finish_code_amount" id="finish_code_amount" value="0.00" maxlength="8"><span id="finish_code_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Sheen:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('sheen', $info['sheen']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="sheen_pct" id="sheen_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="sheen_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Glaze Color:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['glaze']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
          <td class="text-md-right border_thin_bottom gray_bg subtotal">&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Glaze Technique:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('glaze_technique', $info['glaze_technique']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="glaze_tech_pct" id="glaze_tech_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="glaze_tech_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom"><input type="checkbox" id="antiquing" style="margin-left:20px;" <?php echo ($info['antiquing'] !== 'A0') ? "checked" : null; ?> disabled> <label for="antiquing">Antiquing</label></td>
          <td class="border_thin_bottom"><?php echo translateVIN('antiquing', $info['antiquing']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="antiquing_pct" id="antiquing_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_antiquing_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom"><input type="checkbox" id="worn_edges" style="margin-left:20px;" <?php echo ($info['worn_edges'] !== 'W0') ? "checked" : null; ?> disabled> <label for="worn_edges">Worn Edges</label></td>
          <td class="border_thin_bottom"><?php echo translateVIN('worn_edges', $info['worn_edges']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="worn_edges_pct" id="worn_edges_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_worn_edges_subtotal"></span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom"><input type="checkbox" id="distressing" style="margin-left:20px;" <?php echo ($info['distress_level'] !== 'D0') ? "checked" : null; ?> disabled> <label for="distressing">Distressing</label></td>
          <td class="border_thin_bottom"><?php echo translateVIN('distress_level', $info['distress_level']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="distressing_pct" id="distressing_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_distressing_subtotal"></span></td>
        </tr>
        <?php
        if($info['construction_method'] !== 'L') {
          ?>
          <tr class="border_top">
            <td>&nbsp;</td>
            <td class="gray_bg">Carcass:</td>
            <td class="border_thin_bottom">Exterior Species:</td>
            <td class="border_thin_bottom"><?php echo translateVIN('carcass_species', $info['carcass_exterior_species']); ?></td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="exterior_species_pct" id="exterior_species_pct" value="0.00" maxlength="4">%</td>
            <td class="text-md-right border_thin_bottom subtotal">$<span id="ext_species_subtotal"></span></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="border_thin_bottom">Exterior Finish Code:</td>
            <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['carcass_exterior_finish_code']); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="e_finish_code_pm" value="">)</span></td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="text-md-center border_thin_bottom pct_value gray_bg">&nbsp;</td>
            <td class="text-md-right border_thin_bottom subtotal">$<input type="text" name="ext_finish_code_amount" id="ext_finish_code_amount" value="0.00" maxlength="8"><span id="ext_finish_code_subtotal"></span></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="border_thin_bottom">Exterior Glaze Color:</td>
            <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['carcass_exterior_glaze_color']); ?></td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
            <td class="text-md-right border_thin_bottom gray_bg subtotal">&nbsp;</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="border_thin_bottom">Exterior Glaze Technique:</td>
            <td class="border_thin_bottom"><?php echo translateVIN('glaze_technique', $info['carcass_exterior_glaze_technique']); ?></td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="ext_glaze_tech_pct" id="ext_glaze_tech_pct" value="0.00" maxlength="4">%</td>
            <td class="text-md-right border_thin_bottom subtotal">$<span id="ext_glaze_tech_subtotal"></span></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="border_thin_bottom">Interior Species:</td>
            <td class="border_thin_bottom"><?php echo translateVIN('carcass_species', $info['carcass_interior_species']); ?></td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="interior_species_pct" id="interior_species_pct" value="0.00" maxlength="4">%</td>
            <td class="text-md-right border_thin_bottom subtotal">$<span id="int_species_subtotal"></span></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="border_thin_bottom">Interior Finish Code:</td>
            <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['carcass_interior_finish_code']); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="i_finish_code_pm" value="">)</span></td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="text-md-center border_thin_bottom pct_value gray_bg">&nbsp;</td>
            <td class="text-md-right border_thin_bottom subtotal">$<input type="text" name="interior_finish_code_amount" id="interior_finish_code_amount" value="0.00" maxlength="8"><span id="int_finish_code_subtotal"></span></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="border_thin_bottom">Interior Glaze Color:</td>
            <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['carcass_interior_glaze_color']); ?></td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="text-md-center border_thin_bottom gray_bg pct_value">&nbsp;</td>
            <td class="text-md-right border_thin_bottom gray_bg subtotal">&nbsp;</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="border_thin_bottom">Interior Glaze Technique:</td>
            <td class="border_thin_bottom"><?php echo translateVIN('glaze_technique', $info['carcass_interior_glaze_technique']); ?></td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="border_thin_bottom gray_bg">&nbsp;</td>
            <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="interior_glaze_tech_pct" id="interior_glaze_tech_pct" value="0.00" maxlength="4">%</td>
            <td class="text-md-right border_thin_bottom subtotal">$<span id="int_glaze_tech_subtotal"></span></td>
          </tr>
          <?php
        }
        ?>
        <tr class="border_double_bottom">
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="border_thin_bottom">Drawer Box:</td>
          <td class="border_thin_bottom"><?php echo translateVIN('drawer_boxes', $info['drawer_boxes']); ?></td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="border_thin_bottom gray_bg">&nbsp;</td>
          <td class="text-md-center border_thin_bottom pct_value"><input type="text" name="drawer_boxes_pct" id="drawer_boxes_pct" value="0.00" maxlength="4">%</td>
          <td class="text-md-right border_thin_bottom subtotal">$<span id="dd_drawer_box_subtotal"></span></td>
        </tr>
      </table>
    </div>

    <div id="sample_confirmation">
      <table>
        <tr>
          <td><span class="text-underline">Finish/Sample</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Check only one)</td>
        </tr>
        <tr>
          <td colspan="2"><input type="checkbox" id="door_approved"> <label for="door_approved">I have seen the Door style w/ Finish and it is APPROVED</label></td>
        </tr>
        <tr>
          <td colspan="2"><input type="checkbox" id="approved_no_sample"> <label for="approved_no_sample">I APPROVE the Finish & Door style without seeing a sample</label></td>
        </tr>
        <tr>
          <td colspan="2"><input type="checkbox" id="requested_sample"> <label for="requested_sample">I request a Sample for approval &nbsp;&nbsp;&nbsp; (Overall Size)</label></td>
        </tr>
        <tr>
          <td colspan="2">
            <table style="margin-left: 20px;">
              <tr>
                <td width="100px"><input type="checkbox" id="sample_block"> <label for="sample_block">Sample Block</label></td>
                <td class="text-md-center" width="100px"></td>
                <td class="text-md-right">$<span id="sample_block_price"></span></td>
              </tr>
              <tr>
                <td colspan="3" style="padding-left:16px;">3" x 6"</td>
              </tr>
              <tr>
                <td><input type="checkbox" id="door_only"> <label for="door_only">Door Only</label></td>
                <td class="text-md-center"></td>
                <td class="text-md-right">$<span id="door_only_price"></span></td>
              </tr>
              <tr>
                <td colspan="3" style="padding-left:16px;">12" x 15"</td>
              </tr>
              <tr>
                <td><input type="checkbox" id="door_drawer"> <label for="door_drawer">Door & Drawer</label></td>
                <td class="text-md-center"></td>
                <td class="text-md-right">$<span id="door_drawer_price"></span></td>
              </tr>
              <tr>
                <td colspan="3" style="padding-left:16px;">15 1/2" x 23 1/2"</td>
              </tr>
              <tr>
                <td colspan="3" style="padding-left:16px;">(Door & Drawer Front attached)</td>
              </tr>
              <tr>
                <td><input type="checkbox" id="inset_square"> <label for="inset_square">Inset Square</label></td>
                <td class="text-md-center"></td>
                <td class="text-md-right">$<span id="inset_square_price"></span></td>
              </tr>
              <tr>
                <td colspan="3" style="padding-left:16px;">15 1/2" x 23 1/2"</td>
              </tr>
              <tr>
                <td colspan="3" style="padding-left:16px;">(Door, Drawer Front & Frame)</td>
              </tr>
              <tr>
                <td><input type="checkbox" id="inset_beaded"> <label for="inset_beaded">Inset Beaded</label></td>
                <td class="text-md-center"></td>
                <td class="text-md-right">$<span id="inset_beaded_price"></span></td>
              </tr>
              <tr>
                <td colspan="3" style="padding-left:16px;">16 1/2" x 23 1/2"</td>
              </tr>
              <tr>
                <td colspan="3" style="padding-left:16px;">(Door, Drawer Front & Frame)</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>

    <div id="charge_summary_std">
      <table align="right" width="100%">
        <tr class="border_thin_bottom">
          <td width="65%" class="total_text">Upcharges:</td>
          <td width="80px" class="gray_bg total_text">&nbsp;</td>
          <td class="text-md-right gray_bg total_text">$<span id="final_upcharges"></span></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text">Lead Time: <span class="em_box" style="margin-left: 50px;">Standard</span></td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<span id="final_leadtime"></span></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text">Total List Price:</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<span id="final_subtotal"></span></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text">Multiplier:</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text"><input type="text" name="multiplier" value="<?php echo $dealer_info['multiplier']; ?>" maxlength="5" class="static_width" style="width:23px;"></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text">Net Price:</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<span id="final_net"></span></td>
        </tr>
        <tr>
          <td class="total_text">Net Charges:</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text"></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text">Shipping Zone: <span class="em_box" style="margin-left: 20px;"><input type="text" name="final_ship_zone" id="final_ship_zone" value="" maxlength="1" class="static_width" style="width:10px;text-align:center;"></span> <span id="final_ship_zone_miles"></span></td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<input type="text" name="final_shipping" id="final_shipping" value="0.00" maxlength="10"></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text"><input type="checkbox" id="final_freight_check" class="no-print"> <label for="final_freight_check">Min. Freight: (Under 6 Cabinets)</label></td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<span id="final_freight"></span></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text" colspan="2"><input type="checkbox" id="final_jobsite_check" class="no-print"> <label for="final_jobsite_check" style="padding-right:15px;">Jobsite Delivery</label> <input type="checkbox" id="final_cust_pickup" class="no-print"> <label for="final_cust_pickup" style="padding-right:15px;">Pickup</label> <input type="checkbox" id="final_multi_so" class="no-print"> <label for="final_multi_so">Multi-room SO</label></td>
          <td class="text-md-right total_text">$<span id="final_jobsite"></span></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text"><input type="checkbox" id="final_cc_check" class="no-print"> <label for="final_cc_check">Credit Card: +3.5%</label></td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<span id="final_cc"></span></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text">Samples</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<span id="final_samples"></span></td>
        </tr>
        <tr class="em_box">
          <td class="total_text">Sub Total:</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<span id="final_last_subtotal"></span></td>
        </tr>
        <tr class="header em_box">
          <td class="total_text">Total Amount</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<span id="final_total"></span></td>
        </tr>
        <tr id="deposit_line">
          <td colspan="2" class="em_box" style="padding-left:20px;">50% Deposit due to start production</td>
          <td class="text-md-right em_box">$<span id="final_deposit"></span></td>
        </tr>
      </table>

      <div class="clearfix"></div>
    </div>

    <div id="charge_summary_arh" style="display: none;">
      <table align="right" width="100%">
        <tr class="em_box">
          <td class="total_text">Products Sub Total:</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<input type="text" name="product_subtotal" value="0.00" maxlength="10"></td>
        </tr>
        <tr class="border_thin_bottom">
          <td class="total_text">
            Multiplier: <input type="radio" name="arh_multiplier_opts" id="arh_landed" value="arh_landed" checked><label for="arh_landed">Landed</label><input type="radio" name="arh_multiplier_opts" id="arh_pickup" value="arh_pickup"><label for="arh_pickup">Pickup</label>
          </td>
          <td class="text-md-right total_text"><input type="text" id="arh_multiplier" name="arh_multiplier" value=".335" maxlength="5"></td>
          <td class="text-md-right total_text">$<input type="text" id="arh_multiplier_total" name="arh_multiplier_total" value="0.00" maxlength="10"></td>
        </tr>
        <tr class="header em_box">
          <td class="total_text">Total Amount:</td>
          <td class="total_text">&nbsp;</td>
          <td class="text-md-right total_text">$<input type="text" class="total_text" name="total_amount" value="0.00" maxlength="10" style="background-color:#000;color:#FFF;font-weight:bold;"></td>
        </tr>
        <tr>
          <td colspan="2" class="em_box" style="padding-left:20px;">50% Deposit due to start production</td>
          <td class="text-md-right em_box">$<input type="text" name="final_deposit" value="0.00" maxlength="10"></td>
        </tr>
      </table>

      <div class="clearfix"></div>
    </div>

    <div id="terms_acceptance">
      <table>
        <tr>
          <td colspan="2"><span class="text-underline">Please check box to accept terms</span> (Must be checked to validate acceptance)</td>
        </tr>
        <tr>
          <td colspan="2"><input type="checkbox" id="lead_start"> <label for="lead_start">Lead time starts once quote has been signed below & deposit has been received</label></td>
        </tr>
        <tr>
          <td colspan="2"><input type="checkbox" id="50_deposit"> <label for="50_deposit">I approve 50% deposit to be charged:</label></td>
        </tr>
        <tr>
          <td colspan="2">
            <table>
              <tr>
                <td width="20px">&nbsp;</td>
                <td><input type="checkbox" id="deposit_ACH"> <label for="deposit_ACH">ACH</label></td>
                <td><input type="checkbox" id="deposit_check"> <label for="deposit_check">Attached photocopy of check</label></td>
                <td><input type="checkbox" id="deposit_credit"> <label for="deposit_credit">Credit Card (<span id="credit_card_pct"></span>%)</label></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2"><input type="checkbox" id="final_payment"> <label for="final_payment">Final payment due upon delivery & will be charged to ACH account prior to offloading</label><div style="margin-left:15px;">(Exceptions to made prior to delivery)</div></td>
        </tr>
        <tr>
          <td colspan="2" id="signature">
            Signature: <div style="border-bottom: 1px solid #000;width:88%;float:right;">&nbsp;</div>
            <div style="clear:both;">(Signature confirms acceptance and acknowledges that<br /> the deposit will be auto-drafted within 24 hours)</div>
          </td>
        </tr>
        <tr>
          <td colspan="2"></td>
        </tr>
      </table>
    </div>

    <div id="closing_statement">Thank you! Our Commitment is to Quality in our Cabinetry & Our Customer Service!</div>

    <div id="sample_qty" style="display:none;">
      <?php if($info['sample_block_ordered'] > 0 || $info['door_only_ordered'] > 0 || $info['door_drawer_ordered'] > 0 || $info['inset_square_ordered'] > 0 || $info['inset_beaded_ordered'] > 0) { ?>
        <table width="80%" style="margin:0 auto;">
          <tr>
            <th class="b-ul">Order</th>
            <th class="b-ul text-md-center" width="75px">On Order</th>
            <th class="b-ul text-md-center" width="75px">Received</th>
          </tr>
          <tr style="height:10px;"></tr>

          <?php
          function displaySampleStatus($title, $sample_object) {
            global $info;
            $output = ($info[$sample_object] > 0) ? $info[$sample_object] : '_________';

            echo "<tr>";
            echo "<td class='middle-border'>$title</td>";
            echo "<td class='text-md-center'>$output</td>";
            echo "<td class='text-md-center'>_________</td>";
            echo "</tr>";
          }

          if($info['sample_block_ordered'] > 0) {
            displaySampleStatus('Sample Block (3" x 6")', 'sample_block_ordered');
          }

          if($info['door_only_ordered'] > 0) {
            displaySampleStatus('Door Only (12" x 15")', 'door_only_ordered');
          }

          if($info['door_drawer_ordered'] > 0) {
            displaySampleStatus('Door & Drawer (14 1/2" x 23 1/2")', 'door_drawer_ordered');
          }

          if($info['inset_square_ordered'] > 0) {
            displaySampleStatus('Inset Square (15 1/2" x 23 1/2")', 'inset_square_ordered');
          }

          if($info['inset_beaded_ordered'] > 0) {
            displaySampleStatus('Inset Beaded (16 1/2" x 23 1/2")', 'inset_beaded_ordered');
          }
          ?>
        </table>
        <?php
      }
      ?>
    </div>
  </form>
</div>

<script>
  var ship_charges_changed = false;

  /** Fixed Pricing */
  var styles_rails_price = 8.00;
  var short_drawer_raise_price = 65.00;
  var tall_drawer_raise_price = 65.00;

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

  if($info['door_design'] === 'B03' && $info['panel_raise_td'] === '7') {
    echo "var edge_profile_price = 95.00;";
  } elseif($info['door_design'] === 'B03') {
    echo "var edge_profile_price = 65.00;";
  } else {
    echo "var edge_profile_price = 57.00;";
  }

  echo $hide;
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
  var cabinet_list_price = $("input[name='list_price']").val().replace(/,/g , "");
  var mods_accessories = $("input[name='mods_accessories']").val().replace(/,/g , "");
  var final_leadtime = 0.00;
  var final_subtotal = 0.00;
  var final_multiplier = $("input[name='multiplier']");
  var final_net = 0.00;
  var final_ship_zone = 'A';
  var final_ship_zone_miles = '(0-100 Miles)';
  var final_shipping = 0.00;
  var final_freight = 0.00;
  var final_jobsite = 0.00;
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
  $("#final_shipping").val(final_shipping.toFixed(2));
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
  var glaze_tech_subtotal = 0.00;
  var sheen_subtotal = 0.00;
  var construction_subtotal = 0.00;
  var ext_species_subtotal = 0.00;
  var ext_finish_code_subtotal = 0.00;
  var ext_glaze_tech_subtotal = 0.00;
  var int_species_subtotal = 0.00;
  var int_finish_code_subtotal = 0.00;
  var int_glaze_tech_subtotal = 0.00;
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
  var finish_code_subtotal = 0.00;

  $("#glaze_tech_subtotal").html(glaze_tech_subtotal.toFixed(2));
  $("#sheen_subtotal").html(sheen_subtotal.toFixed(2));
  $("#construction_subtotal").html(construction_subtotal.toFixed(2));
  $("#ext_species_subtotal").html(ext_species_subtotal.toFixed(2));
  $("#ext_glaze_tech_subtotal").html(ext_glaze_tech_subtotal.toFixed(2));
  $("#int_species_subtotal").html(int_species_subtotal.toFixed(2));
  $("#int_glaze_tech_subtotal").html(int_glaze_tech_subtotal.toFixed(2));
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
  var glaze_tech_pct = $("#glaze_tech_pct").val();
  var sheen_pct = $("#sheen_pct").val();
  var construction_pct = $("#construction_pct").val();
  var exterior_species_pct = $("#exterior_species_pct").val();
  var ext_finish_code_amount = $("#ext_finish_code_amount").val();
  var ext_glaze_tech_pct = $("#ext_glaze_tech_pct").val();
  var interior_species_pct = $("#interior_species_pct").val();
  var interior_finish_code_amount = $("#interior_finish_code_amount").val();
  var interior_glaze_tech_pct = $("#interior_glaze_tech_pct").val();
  var dd_species_pct = $("#dd_species_pct").val();
  var dd_design_pct = $("#dd_design_pct").val();
  var antiquing_pct = $("#antiquing_pct").val();
  var distressing_pct = $("#distressing_pct").val();
  var worn_edges_pct = $("#worn_edges_pct").val();
  var drawer_boxes_pct = $("#drawer_boxes_pct").val();
  var finish_code_amount = $("#finish_code_amount").val();
  /** End Percent Fields */

  /** Qty Fields */
  var style_rail_qty = $("input[name='style_rail_qty']").val();
  var sd_raise_qty = $("input[name='sd_raise_qty']").val();
  var td_raise_qty = $("input[name='td_raise_qty']").val();
  var edge_profile_qty = $("input[name='edge_profile_qty']").val();
  var frame_opt_qty = $("input[name='frame_opt_qty']").val();
  /** End Qty Fields */

  function getUrlParams(prop) {
    var params = {};
    var search = decodeURIComponent( window.location.href.slice( window.location.href.indexOf('?') + 1));
    var definitions = search.split('&');

    definitions.forEach(function(val, key) {
      var parts = val.split('=', 2);
      params[parts[0]] = parts[1];
    } );

    return (prop && prop in params) ? params[prop] : params;
  }

  $(function() {
    if(getUrlParams('action') === 'arh') {
      $("#charge_summary_std").hide();
      $("#charge_summary_arh").show();
      $(".pct_value").hide();
      $(".subtotal").hide();
      $(".toggle_gray").toggle();

      $(".arh_highlight").show().toggleClass('highlight_input');
      $("#executive_ref").html('<span style="margin-right:20px;">Exec. Reference</span>');
    } else if(getUrlParams('action') === 'no_totals') {
      $("#charge_summary_std").hide();
      $("#charge_summary_arh").hide();
      $(".pct_value").hide();
      $(".subtotal").hide();
      $(".toggle_gray").toggle();
      $("#terms_box").hide();
      $("#signature").hide();
      $("#closing_statement").hide();
    } else if(getUrlParams('action') === 'sample_req') {
      $("#charge_summary_std").hide();
      $("#charge_summary_arh").hide();
      $(".pct_value").hide();
      $(".subtotal").hide();
      $(".toggle_gray").toggle();
      $("#terms_box").hide();
      $("#signature").hide();
      $("#closing_statement").hide();

      $("#page_type_header").html("SAMPLE REQUEST");
      $("#sample_qty").show();

      //$("#delivery_notes").hide();
      //$("#layout_notes_title").html("Sample Notes:");
    }

    setTimeout(function() {
      if(getUrlParams('noprint') !== 'true') {
        window.print();
      }
    }, 150);
  });

  function emptyOrZero(data) {
    var count = 0;

    if(typeof(data) == 'number' || typeof(data) == 'boolean') {
      return false;
    }

    if(typeof(data) == 'undefined' || data === null || data === 0 || data === '0') {
      return true;
    }

    if(typeof(data.length) != 'undefined') {
      return data.length == 0;
    }

    for(var i in data) {
      if(data.hasOwnProperty(i)) {
        count ++;
      }
    }

    return count == 0;
  }

  $('body')
    .on("focus", "input[type='text']", function() {
      $(this).select();
    })
    .on("keyup", "input[type='text']", function() {
      if(!$(this).hasClass("static_width")) {
        if($(this).val().length > 4) {
          if ((event.keyCode === 8 || event.keyCode === 46) && $(this).width() > 18) {
            if ($(this).val().length <= 4) {
              $(this).width(18);
            } else {
              $(this).width($(this).width() - 8);
            }
          } else if (((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 65 && event.keyCode <= 90) || (event.keyCode >= 96 && event.keyCode <= 111) || (event.keyCode >= 188 && event.keyCode <= 190)) && $(this).val().length < 10) {
            if ($(this).val().length <= 4) {
              $(this).width(18);
            } else {
              $(this).width($(this).width() + 8);
            }
          }
        } else {
          $(this).width(18);
        }
      }
    })
    .on("change", "input", function() {
      /** Cabinet pricing */
      cabinet_list_price = $("input[name='list_price']").val().replace(/,/g , "");
      mods_accessories = $("input[name='mods_accessories']").val().replace(/,/g , "");

      /** Percent Fields */
      glaze_tech_pct = $("input[name='glaze_tech_pct'").val();
      sheen_pct = $("input[name='sheen_pct'").val();
      construction_pct = $("input[name='construction_pct'").val();
      exterior_species_pct = $("input[name='exterior_species_pct'").val();
      ext_finish_code_amount = $("input[name='ext_finish_code_amount'").val();
      ext_glaze_tech_pct = $("input[name='ext_glaze_tech_pct'").val();
      interior_species_pct = $("input[name='interior_species_pct'").val();
      interior_finish_code_amount = $("input[name='interior_finish_code_amount'").val();
      interior_glaze_tech_pct = $("input[name='interior_glaze_tech_pct'").val();
      dd_species_pct = $("input[name='dd_species_pct'").val();
      dd_design_pct = $("input[name='dd_design_pct'").val();
      antiquing_pct = $("input[name='antiquing_pct'").val();
      distressing_pct = $("input[name='distressing_pct'").val();
      worn_edges_pct = $("input[name='worn_edges_pct'").val();
      drawer_boxes_pct = $("input[name='drawer_boxes_pct'").val();
      finish_code_amount = $("input[name='finish_code_amount'").val();
      /** End Percent Fields */

      /** Qty Fields */
      style_rail_qty = $("input[name='style_rail_qty']").val();
      sd_raise_qty = $("input[name='sd_raise_qty']").val();
      td_raise_qty = $("input[name='td_raise_qty']").val();
      edge_profile_qty = $("input[name='edge_profile_qty']").val();
      frame_opt_qty = $("input[name='frame_opt_qty']").val();
      /** End Qty Fields */

      /** Attribute Subtotals */
      glaze_tech_subtotal = (!emptyOrZero(glaze_tech_pct)) ? (glaze_tech_pct / 100) * cabinet_list_price : 0;
      sheen_subtotal = (!emptyOrZero(sheen_pct)) ? (sheen_pct / 100) * cabinet_list_price : 0;
      construction_subtotal = (!emptyOrZero(construction_pct)) ? (construction_pct / 100) * cabinet_list_price : 0;
      ext_species_subtotal = (!emptyOrZero(exterior_species_pct)) ? (exterior_species_pct / 100) * cabinet_list_price : 0;
      ext_finish_code_subtotal = (!emptyOrZero(ext_finish_code_amount)) ? parseFloat(ext_finish_code_amount) : 0;
      ext_glaze_tech_subtotal = (!emptyOrZero(ext_glaze_tech_pct)) ? (ext_glaze_tech_pct / 100) * cabinet_list_price : 0;
      int_species_subtotal = (!emptyOrZero(interior_species_pct)) ? (interior_species_pct / 100) * cabinet_list_price : 0;
      int_finish_code_subtotal = (!emptyOrZero(interior_finish_code_amount)) ? parseFloat(interior_finish_code_amount) : 0;
      int_glaze_tech_subtotal = (!emptyOrZero(interior_glaze_tech_pct)) ? (interior_glaze_tech_pct / 100) * cabinet_list_price : 0;
      dd_species_subtotal = (!emptyOrZero(dd_species_pct)) ? (dd_species_pct / 100) * cabinet_list_price : 0;
      dd_deign_subtotal = (!emptyOrZero(dd_design_pct)) ? (dd_design_pct / 100) * cabinet_list_price : 0;
      dd_style_rail_subtotal = (!emptyOrZero(style_rail_qty)) ? style_rail_qty * styles_rails_price : 0;
      dd_sd_raise_subtotal = (!emptyOrZero(sd_raise_qty)) ? sd_raise_qty * short_drawer_raise_price : 0;
      dd_td_raise_subtotal = (!emptyOrZero(td_raise_qty)) ? td_raise_qty * tall_drawer_raise_price : 0;
      dd_edge_profile_subtotal = (!emptyOrZero(edge_profile_qty)) ? edge_profile_qty * edge_profile_price : 0;
      dd_frame_option_subtotal = (!emptyOrZero(frame_opt_qty)) ? frame_opt_qty * frame_option_price : 0;
      dd_antiquing_subtotal = (!emptyOrZero(antiquing_pct)) ? (antiquing_pct / 100) * cabinet_list_price : 0;
      dd_distressing_subtotal = (!emptyOrZero(distressing_pct)) ? (distressing_pct / 100) * cabinet_list_price : 0;
      dd_worn_edges_subtotal = (!emptyOrZero(worn_edges_pct)) ? (worn_edges_pct / 100) * cabinet_list_price : 0;
      dd_drawer_box_subtotal = (!emptyOrZero(drawer_boxes_pct)) ? (drawer_boxes_pct / 100) * cabinet_list_price : 0;
      finish_code_subtotal = (!emptyOrZero(finish_code_amount)) ? parseFloat(finish_code_amount) : 0;

      $("#glaze_tech_subtotal").html(addCommas(glaze_tech_subtotal.toFixed(2)));
      $("#sheen_subtotal").html(addCommas(sheen_subtotal.toFixed(2)));
      $("#construction_subtotal").html(addCommas(construction_subtotal.toFixed(2)));
      $("#ext_species_subtotal").html(addCommas(ext_species_subtotal.toFixed(2)));
      $("#ext_finish_code_amount").val(addCommas(ext_finish_code_subtotal.toFixed(2)));
      $("#ext_glaze_tech_subtotal").html(addCommas(ext_glaze_tech_subtotal.toFixed(2)));
      $("#int_species_subtotal").html(addCommas(int_species_subtotal.toFixed(2)));
      $("#interior_finish_code_amount").val(addCommas(int_finish_code_subtotal.toFixed(2)));
      $("#int_glaze_tech_subtotal").html(addCommas(int_glaze_tech_subtotal.toFixed(2)));
      $("#dd_species_subtotal").html(addCommas(dd_species_subtotal.toFixed(2)));
      $("#dd_deign_subtotal").html(addCommas(dd_deign_subtotal.toFixed(2)));
      $("#dd_style_rail_subtotal").html(addCommas(dd_style_rail_subtotal.toFixed(2)));
      $("#dd_sd_raise_subtotal").html(addCommas(dd_sd_raise_subtotal.toFixed(2)));
      $("#dd_td_raise_subtotal").html(addCommas(dd_td_raise_subtotal.toFixed(2)));
      $("#dd_edge_profile_subtotal").html(addCommas(dd_edge_profile_subtotal.toFixed(2)));
      $("#dd_frame_option_subtotal").html(addCommas(dd_frame_option_subtotal.toFixed(2)));
      $("#dd_antiquing_subtotal").html(addCommas(dd_antiquing_subtotal.toFixed(2)));
      $("#dd_distressing_subtotal").html(addCommas(dd_distressing_subtotal.toFixed(2)));
      $("#dd_worn_edges_subtotal").html(addCommas(dd_worn_edges_subtotal.toFixed(2)));
      $("#dd_drawer_box_subtotal").html(addCommas(dd_drawer_box_subtotal.toFixed(2)));
      $("#finish_code_subtotal").val(addCommas(finish_code_subtotal.toFixed(2)));
      /** End Attribute Subtotals */

      /** Charge Summary */
      final_upcharges = construction_subtotal + ext_species_subtotal + ext_finish_code_subtotal + ext_glaze_tech_subtotal + glaze_tech_subtotal + sheen_subtotal +
        int_species_subtotal + int_finish_code_subtotal + int_glaze_tech_subtotal + dd_species_subtotal + dd_deign_subtotal + dd_style_rail_subtotal +
        dd_sd_raise_subtotal + dd_td_raise_subtotal + dd_edge_profile_subtotal + dd_frame_option_subtotal + dd_antiquing_subtotal + dd_distressing_subtotal +
        dd_worn_edges_subtotal + dd_drawer_box_subtotal + finish_code_subtotal;

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
      final_shipping = parseFloat($("#final_shipping").val());

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

      if(!ship_charges_changed) {
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

        $("#final_shipping").val(final_shipping.toFixed(2));
      }

      final_last_subtotal = parseFloat(final_net) + parseFloat(final_shipping) + parseFloat(final_freight) + parseFloat(final_jobsite) + parseFloat(final_cc) + parseFloat(final_samples);
      final_total = parseFloat(final_last_subtotal);
      final_deposit = parseFloat(final_total) * .5;

      $("#final_upcharges").html(addCommas(parseFloat(final_upcharges).toFixed(2)));
      $("#final_leadtime").html(addCommas(final_leadtime.toFixed(2)));
      $("#final_subtotal").html(addCommas(final_subtotal.toFixed(2)));
      $("#final_net").html(addCommas(final_net.toFixed(2)));
      $("#final_ship_zone_miles").html(addCommas(final_ship_zone_miles));

      $("#final_freight").html(addCommas(final_freight.toFixed(2)));
      $("#final_jobsite").html(addCommas(final_jobsite.toFixed(2)));
      $("#final_cc").html(addCommas(final_cc.toFixed(2)));
      $("#final_samples").html(addCommas(final_samples.toFixed(2)));
      $("#final_last_subtotal").html(addCommas(final_last_subtotal.toFixed(2)));
      $("#final_tax").html(addCommas(final_tax.toFixed(2)));
      $("#final_total").html(addCommas(final_total.toFixed(2)));
      $("#final_deposit").html(addCommas(final_deposit.toFixed(2)));
      /** End Charge Summary */

      $("input[name='list_price']").val(addCommas($("input[name='list_price']").val()));
      $("input[name='mods_accessories']").val(addCommas($("input[name='mods_accessories']").val()));
    })
    .on("keyup", "#final_ship_zone", function() {
      $(this).val($(this).val().toUpperCase());

      if($(this).val() !== 'A') {
        $("#final_shipping").width('30px');
      } else {
        $("#final_shipping").width('20px');
      }
    })
    .on("keyup", "#final_shipping", function() {
      ship_charges_changed = true;
    })
    .on("change", "input[name='arh_multiplier_opts']", function() {
      if($("#arh_landed").is(":checked")) {
        $("#arh_multiplier").val(".335");
      } else {
        $("#arh_multiplier").val(".322");
      }
    })
    .on("change", "input[name$='_raise_qty']", function() {
      if($(this).val() !== '' && $(this).val() !== 0 && $(this).val() !== '0') {
        $(this).removeClass('print_gray');
        $(this).parent().removeClass('print_gray');
        $(this).parent().next('td').removeClass('print_gray');
      } else {
        $(this).addClass('print_gray');
        $(this).parent().addClass('print_gray');
        $(this).parent().next('td').addClass('print_gray');
      }
    })
  ;
</script>
</body>
</html>