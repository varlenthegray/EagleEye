<?php
require '../../includes/header_start.php';

//outputPHPErrs();

$room_id = sanitizeInput($_REQUEST['room_id']);

$vin_qry = $dbconn->query("SELECT * FROM vin_schema 
ORDER BY segment ASC, case `group` when 'Custom' then 1 when 'Other' then 2 else 3 end, `group` ASC,
 FIELD(`value`, 'Custom', 'Other', 'No', 'None') DESC,
 FIELD(`key`, 'B78') DESC");

while($vin = $vin_qry->fetch_assoc()) {
  $vin_schema[$vin['segment']][] = $vin;
}

$info_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = '$room_id'");
$info = $info_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$info['dealer_code']}'");
$dealer_info = $dealer_qry->fetch_assoc();

$sheen_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'sheen' AND `key` = '{$info['sheen']}'");
$sheen = $sheen_qry->fetch_assoc();

$note_arr = array();

// FIXME: This should really be a limit of 1 with a sort order attached to it
$notes_qry = $dbconn->query("SELECT * FROM notes WHERE (note_type = 'room_note_delivery' OR note_type = 'room_note_design' OR note_type = 'room_note_fin_sample') AND type_id = '$room_id'");

if($notes_qry->num_rows > 0) {
  while($note_result = $notes_qry->fetch_assoc()) {
    $note_arr[$note_result['note_type']] = $note_result;
  }
}

if($_REQUEST['action'] === 'sample_req' || $_REQUEST['action'] === 'no_totals') {
  $hide .= "$('#sample_confirmation').hide();";
  $hide .= "$('#terms_acceptance').hide();";
  $hide .= "$('#sample_qty').css('margin-top', '30px');";
}

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

$result_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '{$room['so_parent']}'");
$result = $result_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id = '{$result['dealer_code']}'");
$dealer = $dealer_qry->fetch_assoc();

// This section refers to the submit buttons and disabling of them
$existing_quote_qry = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $room_id");

if($existing_quote_qry->num_rows === 1) {
  $existing_quote = $existing_quote_qry->fetch_assoc();
} else {
  $existing_quote = null;
}

if(!empty($existing_quote['quote_submission'])) {
  $submit_disabled = 'disabled';

  $submitted_time = date(DATE_TIME_ABBRV, $existing_quote['quote_submission']);
  $submitted = "- Submitted on $submitted_time";
} else {
  $submit_disabled = null;
  $submitted = null;
}

// determine the price group
$pg_qry = $dbconn->query("SELECT 
  vs1.id AS species_grade_id, vs2.id AS door_design_id
FROM rooms r 
  LEFT JOIN vin_schema vs1 ON r.species_grade = vs1.key
  LEFT JOIN vin_schema vs2 ON r.door_design = vs2.key
WHERE r.id = $room_id AND vs1.segment = 'species_grade' AND vs2.segment = 'door_design'");

if($pg_qry->num_rows > 0) {
  $pg = $pg_qry->fetch_assoc();

  if ($pg['door_design_id'] !== '1544' && $pg['species_grade_id'] !== '11') {
    $price_group_qry = $dbconn->query("SELECT * FROM pricing_price_group_map WHERE door_style_id = {$pg['door_design_id']} AND species_id = {$pg['species_grade_id']}");
    $price_group = $price_group_qry->fetch_assoc();
    $price_group = $price_group['price_group_id'];
  } else {
    $price_group = '0';
  }
} else {
  $price_group = '0';
}
?>

<link href="/assets/css/pricing.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/css?family=Marck+Script" rel="stylesheet">

<script>
  <?php echo $submit_disabled !== null ? 'var already_submitted = true;' : 'var already_submitted = false;'; ?>
</script>

<div class="card-box">
  <div class="row sticky no-print" style="background-color:#FFF;z-index:2;top:84px;padding:4px;">
    <div class="col-md-4">
      <button class="btn waves-effect btn-primary-outline" title="Save Changes" id="save" <?php echo $submit_disabled; ?>> <i class="fa fa-save fa-2x"></i> </button>
      <button class="btn waves-effect btn-success-outline" title="Submit Quote" id="submit_for_quote_BROKEN" <?php echo $submit_disabled; ?>> <i class="fa fa-paper-plane-o fa-2x"></i> </button>
      <!--<button class="btn waves-effect btn-secondary" title="Edit Global Information" id="global_info"> <i class="fa fa-globe fa-2x"></i> </button>-->
      <div class="btn-group">
        <button type="button" title="Print" class="btn btn-secondary dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false"> <i class="fa fa-print fa-2x"></i> </button>
        <div class="dropdown-menu" x-placement="bottom-start" style="position:absolute;transform:translate3d(0,38px,0);top:0;left:0;will-change:transform;">
          <a class="dropdown-item" href="/main.php?page=pricing/index?room_id=<?php echo $room_id; ?>&print=true" target="_blank" title="Print this page specifically">Print Item List</a>
          <?php
          echo $bouncer->validate('print_sample') ? "<a href='/print/e_coversheet.php?room_id={$room['id']}&action=sample_req' target='_blank' class='dropdown-item'>Print Sample Request</a>" : null;
//          echo $bouncer->validate('print_coversheet') ? "<a href='/print/e_coversheet.php?room_id={$room['id']}' target='_blank' class='dropdown-item'>Print Coversheet</a>" : null;
          echo $bouncer->validate('print_shop_coversheet') ? "<a href='/main.php?page=pricing/index?room_id=$room_id&print=true&hidePrice=true' target='_blank' class='dropdown-item'>Print Shop Item List</a>" : null;
          echo $bouncer->validate('print_sample_label') ? "<a href='/print/sample_label.php?room_id={$room['id']}' target='_blank' class='dropdown-item'>Print Sample Label</a>" : null;
          ?>
        </div>
      </div>
      <button class="btn waves-effect btn-secondary" title="Room Attachments" id="add_attachment"> <i class="fa fa-paperclip fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Copy Room" id="copy_room"> <i class="fa fa-copy fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Bracket Management" id="bracket_management"> <i class="fa fa-code-fork fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Door Sizing" onclick="window.open('/html/inset_sizing.php?room_id=<?php echo $room['id']; ?>','_blank')"> <i class="fa fa-arrows-alt fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Appliance Worksheets" id='appliance_ws' data-roomid='<?php echo $room['id']; ?>'> <i class="fa fa-cubes fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Recalculate Ship Date" id='ship_date_recalc' data-roomid='<?php echo $room['id']; ?>'> <i class="fa fa-truck fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Recalculate Pricing" id="catalog_recalculate"> <i class="fa fa-retweet fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Download ORD File" id="dl_ord_file"><i class="fa fa-file-text-o fa-2x"></i></button>
    </div>

    <div class="col-md-6 text-md-right"><h4 style="margin:0;padding:0;"><?php echo "{$info['so_parent']}{$info['room']}-{$info['iteration']} $submitted"; ?></h4></div>
  </div>

  <div class="row">
    <div class="col-md-2 pricing_left_nav no-print sticky" style="top:122px;">
      <div class="sticky nav_filter">
        <div class="form-group">
          <label for="left_menu_options">Action:</label>
          <select id="left_menu_options" class="pricing_dropdown ignoreSaveAlert" style="margin:5px 0;">
            <option value="catalog">Catalog</option>
            <option value="globals">Globals</option>
            <option value="samples">Samples</option>
          </select>
        </div>

        <div class="form-group action_search">
          <label for="treeFilter">Search Catalog</label>
          <?php echo $bouncer->validate('pricing_change_catalog') ? '<span class="pull-right"><i id="editCatalogLock" class="fa fa-fw fa-lock cursor-hand" title="Lock/Unlock Catalog for Changes"></i></span>' : null ?>
          <input type="text" class="form-control fc-simple ignoreSaveAlert" id="treeFilter" placeholder="Find" width="100%" >
        </div>

        <label for="below" id="category_collapse">Catalog</label>
      </div>

      <div id="action_container"></div>
    </div>

    <div class="col-md-8 pricing_table_format">
      <form id="cabinet_specifications" method="post" action="#">
        <div class="row">
          <div class="col-sm-4">
            <table width="100%">
              <tr>
                <td colspan="2"><h3><u>Item List</u></h3></td>
              </tr>
              <tr>
                <td width="20%">Dealer:</td>
                <td class="text-bold"><?php echo "{$dealer_info['dealer_id']}_{$dealer_info['dealer_name']} - {$dealer_info['contact']}"; ?></td>
              </tr>
              <tr>
                <td>Project Name:</td>
                <td class="text-bold"><strong><?php echo $result['project_name']; ?></strong></td>
              </tr>
              <tr>
                <td>Dealer PO:</td>
                <td class="text-bold"><strong><?php echo $room['room_name']; ?></strong></td>
              </tr>
              <tr>
                <td>SO:</td>
                <td class="text-bold"><?php echo "{$info['so_parent']}{$info['room']}-{$info['iteration']}"; ?></td>
              </tr>
            </table>
          </div>

          <div class="col-sm-4 center_header">
            <div id="logo"><img src="/assets/images/smc_logo.png" width="140px" /></div>
          </div>

          <div class="col-sm-2 col-sm-offset-2">
            <table>
              <tr>
                <td width="80px"># of Pages:</td>
                <td class="text-bold" id="num_of_pgs">1</td>
              </tr>
              <tr>
                <td>Printed:</td>
                <td class="text-bold"><?php echo date(DATE_DEFAULT); ?></td>
              </tr>
            </table>
          </div>
        </div>

        <div class="row">
            <div class="col-sm-4 gray_bg" style="border-radius:.25rem;border:1px solid #000;padding-bottom:25px;">
              <h5><u>Global: Room Details (Net Price)</u></h5>

              <table width="100%">
                <colgroup>
                  <col width="30%">
                  <col width="60%">
                  <col width="10%">
                </colgroup>
                <tr>
                  <th colspan="3">&nbsp;</th>
                </tr>
                <tr>
                  <td>Dealer PO:</td>
                  <td colspan="2"><input type="text" class="form-control border_thin_bottom" id="room_name" name="room_name" placeholder="Room Name" value="<?php echo $room['room_name']; ?>"></td>
                </tr>
                <tr>
                  <td>Billing Type:</td>
                  <td colspan="2"><strong><?php echo $dealer_info['account_type'] === 'R' ? 'Retail' : 'Distribution'; ?></strong></td>
                </tr>
                <tr>
                  <td>Order Type:</td>
                  <td><?php echo displayVINOpts('product_type'); ?></td>
                  <td id="product_type_cost" class="pricing_value">$0.00</td>
                </tr>
                <tr>
                  <td><span id="leadTimeDef"><?php echo $room['order_status'] === '#' ? 'Est. ' : null; ?>Lead Time:</span></td>
                  <td><?php echo displayVINOpts('days_to_ship'); ?></td>
                  <td class="pricing_value">$0.00</td>
                </tr>
                <tr>
                  <td>Order Status:</td>
                  <td><?php echo displayVINOpts('order_status'); ?></td>
                  <td></td>
                </tr>
                <tr>
                  <td>Ship Date (*):</td>
                  <td><strong id="calcd_ship_date"><?php echo !empty($room['ship_date']) ? date(DATE_DEFAULT, $room['ship_date']) : 'TBD'; ?></strong></td>
                  <td><i style="font-size:1.25em;" class="fa fa-pencil-square no-print cursor-hand" id="overrideShipDate" title="Edit/Override Ship Date"></i></td>
                </tr>
                <tr>
                  <td>Delivery Date (*):</td>
                  <td><strong id="calcd_del_date"><?php echo !empty($room['ship_date']) ? date(DATE_DEFAULT, $room['delivery_date']) : 'TBD'; ?></strong></td>
                  <td></td>
                </tr>
                <tr>
                  <td>Ship VIA:</td>
                  <td><?php echo displayVINOpts('ship_via'); ?></td>
                  <td class="pricing_value">$0.00</td>
                </tr>
                <tr rowspan="3">
                  <td style="vertical-align:top !important;">Ship To:</td>
                  <td colspan="2">
                    <input type="text" style="width:75%;" class="static_width align_left border_thin_bottom" placeholder="Name" name="ship_to_name" value="<?php echo $info['ship_name']; ?>"><br />
                    <input type="text" style="width:75%;" class="static_width align_left border_thin_bottom" placeholder="Address" name="ship_to_address" value="<?php echo $info['project_addr']; ?>"><br />
                    <input type="text" style="width:50%;" class="static_width align_left border_thin_bottom" placeholder="City" name="ship_to_city" value="<?php echo $info['project_city']; ?>"> <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php echo $info['project_state']; ?>"> <input type="text" style="width:51px;" class="static_width align_left border_thin_bottom" placeholder="ZIP" name="ship_to_zip" value="<?php echo $info['project_zip']; ?>">
                  </td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td colspan="2"><input type="checkbox" value="1" name="multi_room_ship" id="multi_room_ship" <?php echo $room['multi_room_ship'] ? 'checked' : null; ?>> <label for="multi_room_ship">Multi-room shipping</label></td>
                </tr>
                <tr>
                  <?php
                  $shipZone = !empty($info['ship_zip']) ? $info['ship_zip'] : $dealer['shipping_zip'];
                  $ship_zone_info = calcShipZone($shipZone);

                  $ship_cost = (bool)$room['multi_room_ship'] ? 0 : $ship_zone_info['cost'];
                  ?>

                  <td>Shipping Zone:</td>
                  <td><strong><?php echo $ship_zone_info['zone']; ?></strong></td>
                  <td id="shipping_cost" class="pricing_value" data-cost="<?php echo $ship_cost; ?>">$<?php echo "{$ship_cost}.00"; ?></td>
                </tr>
                <input type="hidden" name="shipping_cubes" value="0" id="shipping_cubes" />
                <!--<tr>
                  <td>Shipping Cubes:<br /><em>(Min of 6)</em></td>
                  <td><strong id="ship_cube_count">0</strong> <input type="hidden" name="shipping_cubes" value="0" id="shipping_cubes" /></td>
                  <td id="ship_cube_cost">$0.00</td>
                </tr>-->
                <tr>
                  <td colspan="3" style="height:2px;"></td>
                </tr>
                <tr>
                  <td>Payment Method:</td>
                  <td><?php echo displayVINOpts('payment_method'); ?></td>
                  <td></td>
                </tr>
                <tr>
                  <td colspan="3" style="height:5px;"></td>
                </tr>
                <tr>
                  <td colspan="3">Finish/Sample:</td>
                </tr>
                <tr>
                  <td colspan="3"><input type="checkbox" value="1" name="seen_approved" class="sample_checkbox" id="seen_approved" <?php echo $room['sample_seen_approved'] ? 'checked' : null; ?>> <label for="seen_approved">I have seen the door style with finish and it is approved.</label></td>
                </tr>
                <tr>
                  <td colspan="3"><input type="checkbox" value="1" name="unseen_approved" class="sample_checkbox" id="unseen_approved" <?php echo $room['sample_unseen_approved'] ? 'checked' : null; ?>> <label for="unseen_approved">I approve the finish and door style without seeing a sample.</label></td>
                </tr>
                <tr>
                  <td colspan="3"><input type="checkbox" value="1" name="requested_sample" class="sample_checkbox" id="requested_sample" <?php echo $room['sample_requested'] ? 'checked' : null; ?>> <label for="requested_sample">I have requested a sample for approval, reference:</label></td>
                </tr>
                <tr>
                  <td colspan="3"><input type="text" name="sample_reference" class="form-control border_thin_bottom sample_reference" placeholder="Sample Reference" value="<?php echo $room['sample_reference']; ?>" /></td>
                </tr>
                <tr>
                  <td colspan="3" style="height:5px;"></td>
                </tr>
                <tr>
                  <td colspan="3" style="vertical-align:top !important;">Signature:</td>
                </tr>
                <tr>
                  <td colspan="3"><input type="text" class="esig" name="signature" placeholder="(Digital signature affirms the following:)" style="width:100%;border:1px dashed #000;padding:3px;" value="<?php echo $room['esig']; ?>" <?php echo !empty($room['esig']) ? "disabled" : null;  ?> /></td>
                </tr>
                <tr>
                  <td colspan="3" class="esig_id">
                    <?php
                     if(!empty($room['esig'])) {
                       $ip = $room['esig_ip'];
                       $time = date(DATE_TIME_ABBRV, $room['esig_time']);
                     } else {
                       $ip = $_SERVER['REMOTE_ADDR'];
                       $time = date(DATE_TIME_ABBRV);
                     }

                     $output = "$ip ($time)";

                     echo $output;
                    ?>
                    <input type="hidden" name="esig_ip" value="<?php echo $output; ?>" id="esig_ip" />
                  </td>
                </tr>
                <tr>
                  <td colspan="3" style="height:5px;"></td>
                </tr>
                <tr>
                  <td colspan="3" style="padding-left:2px;">
                    <ul>
                      <li>A 50% deposit will be drafted within 24 hours.</li>
                      <li>(*) Shipping/delivery date confirmed upon deposit.</li>
                      <li>Final payment is due prior to delivery.</li>
                    </ul>
                  </td>
                </tr>
                <?php if(empty($room['esig'])) { ?>
                <tr>
                  <td colspan="3"><input type="button" class="btn btn-warning waves-effect waves-light no-print pull-right" id="terms_confirm" value="Sign & Agree" disabled></td>
                </tr>
                <?php } ?>
              </table>
            </div>

            <div class="col-sm-8">
              <div class="col-md-12">
                <div class="global_cab_header"><h5><u>Global: Cabinet Details</u></h5></div>
              </div>

              <div class="col-sm-6">
                <table width="100%">
                  <tr>
                    <th style="padding-left:5px;" width="70%">Design<label class="c-input c-checkbox pull-right no-print" style="color:#FFF;margin-top:2px;padding-right:13px;">Show Image Popups <input type='checkbox' id='show_image_popups' class='ignoreSaveAlert'><span class="c-indicator"></span></label></th>
                    <th>Pct</th>
                    <th>Cost</th>
                  </tr>
                  <tr class="border_top">
                    <td class="border_thin_bottom">Construction Method:<div class="cab_specifications_desc"><?php echo displayVINOpts('construction_method'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="const_pct">0.00%</td>
                    <td class="border_thin_bottom pricing_value" id="const_amt">$0.00</td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Species/Grade:<div class="cab_specifications_desc"><?php echo displayVINOpts('species_grade', null, 'pricingSpeciesGrade'); ?></div></td>
                    <td class="border_thin_bottom"></td>
                    <td class="border_thin_bottom"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Carcass Material:<div class="cab_specifications_desc"><?php echo displayVINOpts('carcass_material'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="box_const_pct">0.00%</td>
                    <td class="border_thin_bottom pricing_value" id="box_const_amt">$0.00</td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Door Design:<div class="cab_specifications_desc"><?php echo displayVINOpts('door_design', null, 'pricingDoorDesign'); ?></div></td>
                    <td class="border_thin_bottom text-md-center" id="const_pg" colspan="2">Price Group <span id="cab_spec_pg"><?php echo $price_group; ?></span></td>
                  </tr>
                  <tr>
                    <td style="padding-left:20px;">Door Panel Raise:<div class="cab_specifications_desc border_thin_bottom" style="margin-bottom:-1px;"><?php echo displayVINOpts('panel_raise', 'panel_raise_door'); ?></div></td>
                    <td class="border_thin_bottom pricing_value"></td>
                    <td class="border_thin_bottom pricing_value"></td>
                  </tr>
                  <tr>
                    <td style="padding-left:20px;">Short Drawer Raise:<div class="cab_specifications_desc border_thin_bottom" style="margin-bottom:-1px;"><?php echo displayVINOpts('panel_raise', 'panel_raise_sd'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="sdr_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="sdr_amt"></td>
                  </tr>
                  <tr>
                    <td style="padding-left:20px;">Tall Drawer Raise:<div class="cab_specifications_desc border_thin_bottom" style="margin-bottom:-1px;"><?php echo displayVINOpts('panel_raise', 'panel_raise_td'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="tdr_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="tdr_amt"></td>
                  </tr>
                  <tr>
                    <td style="padding-left:20px;">Style/Rail Width:<div class="cab_specifications_desc border_thin_bottom" style="margin-bottom:-1px;"><?php echo displayVINOpts('style_rail_width'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="srw_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="srw_amt"></td>
                  </tr>
                  <tr>
                    <td style="padding-left:20px;">Edge Profile:<div class="cab_specifications_desc border_thin_bottom" style="margin-bottom:-1px;"><?php echo displayVINOpts('edge_profile'); ?></div></td>
                    <td class="border_thin_bottom pricing_value"></td>
                    <td class="border_thin_bottom pricing_value"></td>
                  </tr>
                  <tr>
                    <td style="padding-left:20px;">Framing Bead:<div class="cab_specifications_desc border_thin_bottom" style="margin-bottom:-1px;"><?php echo displayVINOpts('framing_bead'); ?></div></td>
                    <td class="border_thin_bottom pricing_value"></td>
                    <td class="border_thin_bottom pricing_value"></td>
                  </tr>
                  <tr>
                    <td style="padding-left:20px;">Frame Option:<div class="cab_specifications_desc border_thin_bottom" style="margin-bottom:-1px;"><?php echo displayVINOpts('framing_options'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="fo_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="fo_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Drawer Box:<div class="cab_specifications_desc"><?php echo displayVINOpts('drawer_boxes'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="drwr_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="drwr_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Drawer Guide:<div class="cab_specifications_desc"><?php echo displayVINOpts('drawer_guide'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="drwguide_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="drwguide_amt"></td>
                  </tr>
                </table>
              </div>

              <div class="col-sm-6" style="padding-left:0;">
                <table width="100%">
                  <tr><th colspan="3" style="padding-left:5px;" class="th_17">Finish</th></tr>
                  <tr class="border_top">
                    <td class="border_thin_bottom" width="70%">Finish Code:<div class="cab_specifications_desc"><?php displayFinishOpts("finish_code", "finish_code"); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="fc_pct"><?php if($room['product_type'] === 'P' && (false !== stripos($room['finish_code'], 'p') || $room['finish_code'] === '1cXXXX')) { echo '10.00%'; } ?></td>
                    <td class="border_thin_bottom pricing_value" id="fc_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Sheen:<div class="cab_specifications_desc"><?php echo displayVINOpts('sheen'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="sheen_pct"><?php if($room['sheen'] === 'a' || $room['sheen'] === 'X' || $room['sheen'] === 'h' ) { echo '5.00%'; } ?></td>
                    <td class="border_thin_bottom pricing_value" id="sheen_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Glaze Color:<div class="cab_specifications_desc"><?php echo displayVINOpts('glaze'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="gc_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="gc_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Glaze Technique:<div class="cab_specifications_desc"><?php echo displayVINOpts('glaze_technique'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="gt_pct"><?php echo $room['glaze_technique'] === 'G2' ? '10.00%' : null; ?></td>
                    <td class="border_thin_bottom pricing_value" id="gt_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Antiquing:<div class="cab_specifications_desc"><?php echo displayVINOpts('antiquing'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="ant_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="ant_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Worn Edges:<div class="cab_specifications_desc"><?php echo displayVINOpts('worn_edges'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="we_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="we_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Distressing:<div class="cab_specifications_desc"><?php echo displayVINOpts('distress_level'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="dist_pct"></td>
                    <td class="border_thin_bottom pricing_value" id="dist_amt"></td>
                  </tr>
                  <tr>
                    <td class="border_thin_bottom">Enviro-finish:<div class="cab_specifications_desc"><?php echo displayVINOpts('green_gard'); ?></div></td>
                    <td class="border_thin_bottom pricing_value" id="ggard_pct"><?php echo $room['green_gard'] === 'G1' ? '5.00%' : null; ?></td>
                    <td class="border_thin_bottom pricing_value" id="ggard_amt"></td>
                  </tr>
                </table>
              </div>
            </div>
        </div>

        <div class="row" id="notes_section" style="margin-top:5px;">
          <div class="col-sm-4">
            <table width="100%">
              <tr><th>&nbsp;Notes</th></tr>
              <tr><td class="gray_bg">&nbsp;Delivery Notes:</td></tr>
              <tr><td id="delivery_notes" style="border:none;"><textarea name="delivery_notes" maxlength="280" class="static_width" rows="5"><?php echo $note_arr['room_note_delivery']['note']; ?></textarea></td></tr>
            </table>

            <input type="hidden" name="delivery_notes_id" value="<?php echo $note_arr['room_note_delivery']['id']; ?>" />
          </div>

          <div class="col-sm-4">
            <table width="100%">
              <tr><th>&nbsp;</th></tr>
              <tr><td class="gray_bg">&nbsp;Design Notes:</td></tr>
              <tr><td style="border-right:2px solid #FFF;"><textarea name="room_note_design" maxlength="280" rows="5"><?php echo $note_arr['room_note_design']['note']; ?></textarea></td>
              </tr>
            </table>

            <input type="hidden" name="design_notes_id" value="<?php echo $note_arr['room_note_design']['id']; ?>" />
          </div>

          <div class="col-sm-4" style="padding-left:0;">
            <table width="100%">
              <tr><th>&nbsp;</th></tr>
              <tr><td class="gray_bg">&nbsp;Finishing/Sample Notes:</td></tr>
              <tr><td style="border-right:2px solid #FFF;"><textarea name="fin_sample_notes" maxlength="280" class="static_width" rows="5"><?php echo $note_arr['room_note_fin_sample']['note']; ?></textarea></td></tr>
            </table>

            <input type="hidden" name="fin_sample_notes_id" value="<?php echo $note_arr['room_note_fin_sample']['id']; ?>" />
          </div>
        </div>
      </form>

      <div class="row" style="border-top: 1px solid #000;">
        <div class="col-md-12" style="margin-top:5px;">
          <div class="item_list_header sticky">
            <h5><u>Item List</u></h5>

            <input type="button" class="btn btn-danger waves-effect waves-light no-print" style="display:none;" id="catalog_remove_checked" value="Delete" />
            <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="item_note"><span class="btn-label"><i class="fa fa-commenting-o"></i> </span>Custom Note</button>
            <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="item_custom_line"><span class="btn-label"><i class="fa fa-plus"></i> </span>Custom Line</button>
            <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="detailed_item_summary"><span class="btn-label"><i class="fa fa-list"></i> </span>Detailed Report</button>

            <div class="clearfix"></div>
          </div>

          <table id="cabinet_list" style="width:100%;">
            <colgroup>
              <col width="30px">
              <col width="30px">
              <col width="40px">
              <col width="150px">
              <col width="350px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
            </colgroup>
            <thead>
            <tr>
              <th>#</th>
              <th class="text-md-center">Qty</th>
              <th class="text-md-center">Actions</th>
              <th>Nomenclature</th>
              <th>Description</th>
              <th class="text-md-center">Width</th>
              <th class="text-md-center">Height</th>
              <th class="text-md-center">Depth</th>
              <th class="text-md-center">Hinge</th>
              <th class="text-md-center pricing_value">Price</th>
            </tr>
            </thead>
            <tbody>
            <!-- Define a row template for all invariant markup: -->
            <tr>
              <td></td>
              <td><input type="text" class="form-control qty_input" value="1" placeholder="Qty" /> </td>
              <td class="text-md-center">
                <i class="fa fa-info-circle primary-color view_item_info cursor-hand" data-id=""></i>
                <i class="fa fa-minus-circle danger-color delete_item cursor-hand" title="Delete line"></i>
                <i class="fa fa-plus-circle secondary-color add_item_mod cursor-hand" title="Add modification"></i>
                <i class="fa fa-copy secondary-color item_copy cursor-hand" title="Copy line"></i>
              </td>
              <td style="white-space:nowrap;"></td>
              <td></td>
              <td class="text-md-center"><input type="text" class="form-control itm_width text-md-center" placeholder="W" /></td>
              <td class="text-md-center"><input type="text" class="form-control itm_height text-md-center" placeholder="H" /></td>
              <td class="text-md-center"><input type="text" class="form-control itm_depth text-md-center" placeholder="D" /></td>
              <td class="text-md-center">
                <select class="item_hinge custom-select">
                  <option value="L">Left</option>
                  <option value="R">Right</option>
                  <option value="P">Pair</option>
                  <option value="N" selected>None</option>
                </select>
              </td>
              <td class="text-md-right cab-price pricing_value"></td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
              <td colspan="12">
                <div class="row no_global_info" style="display:none;"><i class="fa fa-exclamation-triangle" style="font-size:2em;"></i>Unable to price with the current information.<br />Any price displayed is not a reflection of the final price until this quote has been processed by SMCM.<i class="fa fa-exclamation-triangle pull-right" style="font-size:2em;"></i></div>
              </td>
            </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="row pricing_value">
        <div class="col-sm-4 col-sm-offset-8 summary_of_charges">
          <div class="left_header"><h5>Summary of Charges:</h5></div>

            <table align="right" width="100%">
              <tr class="border_thin_bottom">
                <td width="65%" class="total_text">Item List:</td>
                <td width="80px">&nbsp;</td>
                <td class="text-md-right gray_bg total_text" id="itemListTotal">$0.00</td>
              </tr>
              <tr class="border_thin_bottom">
                <td class="total_text">Global: Cabinet Details:</td>
                <td class="total_text">&nbsp;</td>
                <td class="text-md-right total_text" id="itemListGlobalCabDetails">$0.00</td>
              </tr>
              <tr class="border_thin_bottom">
                <td class="total_text">Total:</td>
                <td class="total_text">&nbsp;</td>
                <td class="text-md-right total_text" id="itemListSubTotal1">$0.00</td>
              </tr>
              <tr class="border_thin_bottom">
                <td class="total_text">Multiplier:</td>
                <td class="total_text">&nbsp;</td>
                <td class="text-md-right total_text" id="itemListMultiplier"><?php echo $dealer['multiplier']; ?></td>
              </tr>
              <tr class="border_thin_bottom">
                <td class="total_text">NET:</td>
                <td class="total_text">&nbsp;</td>
                <td class="text-md-right total_text" id="itemListNET">$0.00</td>
              </tr>
              <tr class="border_thin_bottom">
                <td class="total_text">Global: Room Details/Shipping:</td>
                <td class="total_text">&nbsp;</td>
                <td class="text-md-right total_text" id="itemListGlobalRoomDetails">$0.00</td>
              </tr>
              <tr class="border_thin_bottom">
                <td class="total_text">Credit Card:</td>
                <td class="total_text">3.5%</td>
                <td class="text-md-right total_text">$0.00</td>
              </tr>
              <tr class="em_box">
                <td class="total_text">Sub Total:</td>
                <td class="total_text">&nbsp;</td>
                <td class="text-md-right total_text" id="finalSubTotal">$0.00</td>
              </tr>
              <tr class="header em_box">
                <td class="total_text">Total Amount</td>
                <td class="total_text">&nbsp;</td>
                <td class="text-md-right total_text" id="finalTotal">$0.00</td>
              </tr>
              <tr id="deposit_line">
                <td colspan="2" class="em_box" style="padding-left:20px;">50% Deposit due to start production</td>
                <td class="text-md-right em_box" id="finalDeposit">$0.00</td>
              </tr>
            </table>

            <div class="clearfix"></div>
        </div>
      </div>

      <div class="no-print" style="height:100px;">&nbsp;</div>
    </div>

    <div class="col-md-2 sticky no-print" style="top:122px;">
      <form id="accounting_notes" action="#">
        <div class="row">
          <div class="col-md-12">
            <?php if($bouncer->validate('view_accounting')) { ?>
            <tr>
              <td colspan="2">
                <label class="c-input c-checkbox">Deposit Received <input type="checkbox" name="deposit_received" value="1" <?php echo ((bool)$room['payment_deposit']) ? 'checked' :null; ?>><span class="c-indicator"></span></label><br />
                <label class="c-input c-checkbox">Prior to Loading: Distribution - Final Payment<br/><span style="margin-left:110px;">Retail - On Delivery/Payment</span> <input type="checkbox" name="ptl_del" value="1" <?php echo ((bool)$room['payment_del_ptl']) ? 'checked' :null; ?>><span class="c-indicator"></span></label><br />
                <label class="c-input c-checkbox">Retail - Final Payment <input type="checkbox" name="final_payment" value="1" <?php echo ((bool)$room['payment_final']) ? 'checked' :null; ?>><span class="c-indicator"></span></label>
              </td>
            </tr>
            <tr style="height:10px;">
              <td colspan="2"></td>
            </tr>
            <?php } ?>

            <textarea class="form-control" name="room_notes" id="room_notes" placeholder="Notes" style="width:100%;height:277px;"></textarea>

            <?php if(!empty($_SESSION['userInfo'])) { ?>
              <input type="text" name="room_inquiry_followup_date" id="room_inquiry_followup_date" class="form-control" placeholder="Followup On" style="width:30%;float:left;">
              <label for="room_inquiry_requested_of" style="float:left;padding:4px;"> by </label>
              <select name="room_inquiry_requested_of" id="room_inquiry_requested_of" class="form-control" style="width:62%;float:right;">
                <option value="null" selected disabled></option>
                <?php
                $user_qry = $dbconn->query('SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC');

                while($user = $user_qry->fetch_assoc()) {
                  echo "<option value='{$user['id']}'>{$user['name']}</option>";
                }
                ?>
              </select>
            <?php } ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="room_note_box">
              <table class="table table-custom-nb table-v-top">
                <tr>
                  <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">Room Notes</h5> <?php if($bouncer->validate('view_audit_log')) { ?><div class="pull-right"><input type="checkbox" class="ignoreSaveAlert" id="display_log" /> <label for="display_log">Show Audit Log</label></div><?php } ?></td>
                </tr>
                <tr style="height:5px;"><td colspan="2"></td></tr>
                <?php
                if((bool)$_SESSION['userInfo']['dealer']) {
                  $dealer = strtolower($_SESSION['userInfo']['dealer_code']);
                  $where = "AND user.username LIKE '$dealer%'";
                } else {
                  $where = null;
                }

                $room_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'room_note' OR note_type = 'room_note_log') AND notes.type_id = '{$room['id']}' $where ORDER BY notes.timestamp DESC;");

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

                  $notes = str_replace('  ', '&nbsp;&nbsp;', $room_inquiry['note']);
                  //$notes = $room_inquiry['note'];
                  $notes = nl2br($notes);

                  echo "<tr style='height:5px;'><td colspan='2'></td></tr>";

                  $room_note_log = ($room_inquiry['note_type'] === 'room_note_log') ? 'room_note_log' : null;

                  echo "<tr class='$room_note_log'>";
                  echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$room_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                  echo "  <td>$notes -- <small><em>{$room_inquiry['name']} on $time $followup</em></small><div><button type='button' class='btn waves-effect btn-primary post_to_cal'>Post to Calendar</button></div></td>";
                  echo '</tr>';

                  echo "<tr id='inquiry_reply_line_{$room_inquiry['nID']}' style='display:none;'>";
                  echo "<td colspan='2'>
                            <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$room_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                            <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='r_{$room_inquiry['nID']}_submit'>Reply</button>
                        </td>";
                  echo '</tr>';

                  echo $inquiry_replies;

                  echo "<tr class='$room_note_log' style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                }
                ?>
                <tr style="height:5px;"><td colspan="2"></td></tr>
              </table>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class='info-popup'></div>

<!-- modal -->
<div id="modalAddModification" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddModificationLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Add Modifications</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="modificationsFilter">Search Modifications</label>
              <input type="text" class="form-control fc-simple ignoreSaveAlert" id="modificationsFilter" placeholder="Find" width="100%" >
            </div>

            <table id="item_modifications" width="100%">
              <colgroup>
                <col width="35%">
                <col width="40%">
                <col width="25%">
              </colgroup>
              <thead>
              <tr>
                <th></th>
                <th>Modification</th>
                <th>Additional Info</th>
              </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary waves-effect waves-light" id="modificationAddSelected">Add Selected</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- modal -->
<div id="modalDetailedItemList" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalDetailedItemListLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Item List Breakdown</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <table id="cost_audit" width="100%"  style="vertical-align:top;">
              <colgroup>
                <col width="20%">
                <col width="70%">
                <col width="10%">
              </colgroup>
              <thead>
              <tr>
                <th>Line</th>
                <th>Calculation</th>
                <th>Total</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td>Product Type</td>
                <td id="calcProductType"></td>
                <td id="calcProductTypeTotal"></td>
              </tr>
              <tr>
                <td>Lead Time</td>
                <td id="calcLeadTime"></td>
                <td id="calcLeadTimeTotal"></td>
              </tr>
              <tr>
                <td>Ship VIA</td>
                <td id="calcShipVIA"></td>
                <td id="calcShipVIATotal"></td>
              </tr>
              <tr>
                <td>Shipping Zone</td>
                <td id="calcShipZone"></td>
                <td id="calcShipZoneTotal"></td>
              </tr>
              <tr>
                <td>Glaze Technique</td>
                <td id="calcGlazeTech"></td>
                <td id="calcGlazeTechTotal"></td>
              </tr>
              <tr>
                <td>Sheen</td>
                <td id="calcSheen"></td>
                <td id="calcSheenTotal"></td>
              </tr>
              <tr>
                <td>Green Gard</td>
                <td id="calcGreenGard"></td>
                <td id="calcGreenGardTotal"></td>
              </tr>
              <tr>
                <td>Finish Cost</td>
                <td id="calcFinishCode"></td>
                <td id="calcFinishCodeTotal"></td>
              </tr>
              <tr>
                <td>Cabinet Lines</td>
                <td id="calcCabinetLines"></td>
                <td id="calcCabinetLinesTotal"></td>
              </tr>
              <tr>
                <td>Non-Cabinet Lines</td>
                <td id="calcNonCabLines"></td>
                <td id="calcNonCabLinesTotal"></td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary waves-effect waves-light" id="modificationAddSelected">Add Selected</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- modal -->
<div id="modalGeneral" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalGeneralLabel" aria-hidden="true">
      <!-- AJAX Loaded based on button press -->
</div><!-- /.modal -->

<form id="room_attachments">
  <!-- Attachment modal -->
  <div id="modalAddAttachment" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddAttachmentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title">Add Attachment to <?php echo "{$room['so_parent']}{$room['room']}-{$room['iteration']}"; ?></h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple><br />
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple><br />
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple><br />
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple><br />
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple>
            </div>
          </div>
        </div>
        <div class="modal-footer" id="r_attachments_footer">
          <button type="button" class="btn btn-primary waves-effect" id="submit_attachments">Submit</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
</form>

<iframe id="dlORDfile" src="" style="display:none;visibility:hidden;"></iframe>

<script>
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

  function catalogCanEdit() {
    if(editCatalog.hasClass("fa-unlock")) {
      return true;
    } else {
      return false;
    }
  }

  <?php
  echo "active_room_id = $room_id;";
  echo !empty($price_group) ? "var priceGroup = $price_group;" : null;

  $shipZone = !empty($info['ship_zip']) ? $info['ship_zip'] : $dealer['shipping_zip'];
  $ship_zone_info = calcShipZone($shipZone);
  $shipInfo = json_encode($ship_zone_info, true);

  echo "var calcShipZip = '{$info['ship_zip']}';";
  echo "var calcDealerShipZip = '{$dealer['shipping_zip']}';";
  echo "var calcShipInfo = '$shipInfo';";
  ?>

  var numPages = 1,

    CLIPBOARD = null,

    editCatalog = $("#editCatalogLock"),
    cabinetList = $("#cabinet_list"),
    catalog = $("#action_container"),
    itemModifications = $("#item_modifications"),
    nameOfUser = '<?php echo $_SESSION['userInfo']['name']; ?>';

  $(function() {
    setTimeout(function() {
      let tx_delnotes = $("textarea[name='delivery_notes']");
      let tx_design = $("textarea[name='room_note_design']");
      let tx_fin_sample = $("textarea[name='fin_sample_notes']");

      let delheight = 0;
      let designheight = 0;
      let finsampleheight = 0;


      if(getUrlParams('print') === 'true') {
        delheight = tx_delnotes.prop('scrollHeight') - 48;
        designheight = tx_design.prop('scrollHeight') - 48;
        finsampleheight = tx_fin_sample.prop('scrollHeight') - 48;
      } else {
        delheight = tx_delnotes.prop('scrollHeight');
        designheight = tx_design.prop('scrollHeight');
        finsampleheight = tx_fin_sample.prop('scrollHeight');
      }

      tx_delnotes.height(delheight);
      tx_design.height(designheight);
      tx_fin_sample.height(finsampleheight);
    }, 50);

    /******************************************************************************
     *  Cabinet List
     ******************************************************************************/
    cabinetList.fancytree({
      select: function(event, data) { // TODO: Determine if this is valuable
        // Display list of selected nodes
        var selNodes = data.tree.getSelectedNodes();
        // convert to title/key array
        var selKeys = $.map(selNodes, function(node){
          return "[" + node.key + "]: '" + node.title + "'";
        });

        // console.log(selKeys.join(", "));

        if(selKeys.length > 0) {
          $("#catalog_remove_checked").show();
        } else {
          $("#catalog_remove_checked").hide();
        }
      },
      imagePath: "/assets/images/cabinet_icons/",
      cookieId: "fancytree-cabList",
      idPrefix: "fancytree-cabList-",
      titlesTabbable: true,     // Add all node titles to TAB chain
      quicksearch: true,        // Jump to nodes when pressing first character
      source: { url: "/html/pricing/ajax/item_actions.php?action=getCabinetList&room_id=" + active_room_id },
      extensions: ["dnd", "table", "gridnav", "persist"],
      debugLevel: 0,
      dnd: { // drag and drop
        preventVoidMoves: true,
        preventRecursiveMoves: true,
        autoExpandMS: 400,
        dragStart: function(node, data) {
          return true;
        },
        dragEnter: function(node, data) {
          // return ["before", "after"];
          return true;
        },
        dragDrop: function(node, data) {
          data.otherNode.moveTo(node, data.hitMode);
        }
      },
      edit: {
        triggerStart: ["clickActive", "f2", "shift+click", "mac+enter"],
        close: function(event, data) {
          if( data.save && data.isNew ){
            // Quick-enter: add new nodes until we hit [enter] on an empty title
            cabinetList.trigger("nodeCommand", {cmd: "addSibling"});
          }
        }
      },
      table: {
        indentation: 20,
        nodeColumnIdx: 3,
        checkboxColumnIdx: 0
      },
      gridnav: {
        autofocusInput: false,
        handleCursorKeys: true
      },
      renderColumns: function(event, data) {
        // this section handles the column data itself
        var node = data.node, $tdList = $(node.tr).find(">td");

        // lets begin by getting the quantity and the total and multiplying them
        let qty = parseInt(node.data.qty);
        let price = parseFloat(node.data.price);
        let line_total = qty * price;

        // Index #0 => Line Numbering
        $tdList.eq(0).text(node.getIndexHier());

        // Index #1 => Quantity
        $tdList.eq(1).find("input").attr("data-id", node.key).val(node.data.qty);

        // Index #2 => Update all of the buttons to this specific node
        $tdList.eq(2).find(".view_item_info").attr("data-id", node.data.itemID);

        // Index #3 => Nomenclature (SKU) - generated by node.title

        // Index #4 => Description
        if(node.data.customNote === 1) {
          $tdList.eq(4).html('<input type="text" class="form-control custom-line-item" placeholder="Custom Description..." value="' + node.data.name + '" data-id="' + node.key + '" >');
        } else {
          $tdList.eq(4).text(node.data.name);
        }

        // Index #4 => Width
        // $tdList.eq(5).text(node.data.width);
        $tdList.eq(5).find("input").attr("data-id", node.key).val(node.data.width);

        // Index #6 => Height
        // $tdList.eq(6).text(node.data.height);
        $tdList.eq(6).find("input").attr("data-id", node.key).val(node.data.height);

        // Index #7 => Depth
        // $tdList.eq(7).text(node.data.depth);
        $tdList.eq(7).find("input").attr("data-id", node.key).val(node.data.depth);

        // Index #8 => Hinge
        if(node.data.hinge !== undefined) {
          $tdList.eq(8).find(".item_hinge").val(node.data.hinge);
        }

        // Index #9 => Price (individual)
        if(node.data.customPrice === 1) {
          $tdList.eq(9).html('<input type="text" class="form-control custom_price" placeholder="Price" data-id="' + node.key + '" value="' + parseFloat(node.data.price).formatMoney() + '" >');
        } else {
          if (!isNaN(price)) {
            $tdList.eq(9).text(price.formatMoney()).removeAttr("style title"); // price column

            $(".no_global_info").css("display", "none");

            if (!already_submitted) {
              $("#submit_for_quote").attr("disabled", false).attr("title", "");
            }
          } else {
            $tdList.eq(9).css("background-color", "#FF0000").attr("title", "Unknown global attributes, unable to find price.");

            $("#submit_for_quote").attr("disabled", true).attr("title", "Unknown global attributes, unable to submit.");

            $(".no_global_info").css("display", "block");
          }
        }

        //** Adjustments based on the line items being added

        // lining and styles for main lines
        if(node.isTopLevel()) {
          $tdList.addClass("main-level");
        }

        // calculation of how many pages are going to print (total)
        numPages = Math.ceil($(".wrapper").outerHeight() / 980);

        $("#num_of_pgs").text(numPages);

        // update of Global: Cabinet Details pricing
        // TODO: Short Drawer Raise, Tall Drawer Raise, Frame Option, Drawer Box

        // Glaze Technique:
        $("#gt_amt").text()
      },
      modifyChild: function(event, data) {
        recalcSummary();
      },
      init: function() {
        setTimeout(function() {
          cabinetList.floatThead({ top: 151 });
        }, 500);

        if(getUrlParams('hidePrice') === 'true') {
          $(".pricing_value").hide();
        }
      }
    }).on("nodeCommand", function(event, data) {
      // Custom event handler that is triggered by keydown-handler and
      // context menu:
      var refNode, moveMode,
        tree = $(this).fancytree("getTree"),
        node = tree.getActiveNode();

      switch( data.cmd ) {
        case "moveUp":
          refNode = node.getPrevSibling();
          if( refNode ) {
            node.moveTo(refNode, "before");
            node.setActive();
          }
          break;
        case "moveDown":
          refNode = node.getNextSibling();
          if( refNode ) {
            node.moveTo(refNode, "after");
            node.setActive();
          }
          break;
        case "indent":
          refNode = node.getPrevSibling();
          if( refNode ) {
            node.moveTo(refNode, "child");
            refNode.setExpanded();
            node.setActive();
          }
          break;
        case "outdent":
          if( !node.isTopLevel() ) {
            node.moveTo(node.getParent(), "after");
            node.setActive();
          }
          break;
        case "rename":
          node.editStart();
          break;
        case "remove":
          refNode = node.getNextSibling() || node.getPrevSibling() || node.getParent();
          node.remove();
          if( refNode ) {
            refNode.setActive();
          }
          recalcTotal();
          break;
        case "addModifications":
          $("#modalAddModification").modal('show');
          break;
        case "cut":
          CLIPBOARD = {mode: data.cmd, data: node};
          break;
        case "copy":
          CLIPBOARD = {
            mode: data.cmd,
            data: node.toDict(function(n){
              delete n.key;
            })
          };
          break;
        case "clear":
          CLIPBOARD = null;
          break;
        case "paste":
          if( CLIPBOARD.mode === "cut" ) {
            // refNode = node.getPrevSibling();
            CLIPBOARD.data.moveTo(node, "child");
            CLIPBOARD.data.setActive();
          } else if( CLIPBOARD.mode === "copy" ) {
            node.addChildren(CLIPBOARD.data).setActive();
          }
          break;
        default:
          alert("Unhandled command: " + data.cmd);
          return;
      }
    }).on("keydown", function(e){
      var cmd = null;

      // console.log(e.type, $.ui.fancytree.eventToString(e));
      switch( $.ui.fancytree.eventToString(e) ) {
        case "ctrl+shift+n":
        case "meta+shift+n": // mac: cmd+shift+n
          cmd = "addChild";
          break;
        case "ctrl+c":
        case "meta+c": // mac
          cmd = "copy";
          break;
        case "ctrl+v":
        case "meta+v": // mac
          cmd = "paste";
          break;
        case "ctrl+x":
        case "meta+x": // mac
          cmd = "cut";
          break;
        case "ctrl+n":
        case "meta+n": // mac
          cmd = "addSibling";
          break;
        case "del":
        case "meta+backspace": // mac
          cmd = "remove";
          break;
        // case "f2":  // already triggered by ext-edit pluging
        //   cmd = "rename";
        //   break;
        case "ctrl+up":
          cmd = "moveUp";
          break;
        case "ctrl+down":
          cmd = "moveDown";
          break;
        case "ctrl+right":
        case "ctrl+shift+right": // mac
          cmd = "indent";
          break;
        case "ctrl+left":
        case "ctrl+shift+left": // mac
          cmd = "outdent";
      }
      if( cmd ){
        $(this).trigger("nodeCommand", {cmd: cmd});
        return false;
      }
    });

    // Context menu (https://github.com/mar10/jquery-ui-contextmenu)
    cabinetList.contextmenu({
      delegate: "span.fancytree-node",
      menu: [
        {title: "Delete <kbd>[Del]</kbd>", cmd: "remove", uiIcon: "ui-icon-trash" },
        {title: "----"},
        {title: "Add Modifications <kbd>[Ctrl+M]</kbd>", cmd: "addModifications", uiIcon: "ui-icon-plus" },
        {title: "----"},
        {title: "Cut <kbd>Ctrl+X</kbd>", cmd: "cut", uiIcon: "ui-icon-scissors"},
        {title: "Copy <kbd>Ctrl-C</kbd>", cmd: "copy", uiIcon: "ui-icon-copy"},
        {title: "Paste<kbd>Ctrl+V</kbd>", cmd: "paste", uiIcon: "ui-icon-clipboard", disabled: true }
      ],
      beforeOpen: function(event, ui) {
        var node = $.ui.fancytree.getNode(ui.target);
        cabinetList.contextmenu("enableEntry", "paste", !!CLIPBOARD);
        node.setActive();
      },
      select: function(event, ui) {
        var that = this;
        // delay the event, so the menu can close and the click event does
        // not interfere with the edit control
        setTimeout(function(){
          $(that).trigger("nodeCommand", {cmd: ui.cmd});
        }, 100);
      }
    });

    /******************************************************************************
     *  Navigation menu
     ******************************************************************************/

    // this is the navigation menu on the left side
    catalog.fancytree({
      extensions: ["dnd", "filter"],
      source: { url: "/html/pricing/ajax/nav_menu.php" },
      filter: {
        autoApply: true,   // Re-apply last filter if lazy data is loaded
        autoExpand: true, // Expand all branches that contain matches while filtered
        counter: true,     // Show a badge with number of matching child nodes near parent icons
        fuzzy: true,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
        hideExpandedCounter: true,  // Hide counter badge if parent is expanded
        hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
        highlight: true,   // Highlight matches by wrapping inside <mark> tags
        leavesOnly: false, // Match end nodes only
        nodata: false,      // Display a 'no data' status node if result is empty
        mode: "hide"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
      },
      dnd: {
        autoExpandMS: 800,
        focusOnClick: true,
        preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
        preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
        dragStart: function(node, data) {
          /** This function MUST be defined to enable dragging for the tree.
           *  Return false to cancel dragging of node.
           */
          return catalogCanEdit();
        },
        dragEnter: function(node, data) {
          /** data.otherNode may be null for non-fancytree droppables.
           *  Return false to disallow dropping on node. In this case
           *  dragOver and dragLeave are not called.
           *  Return 'over', 'before, or 'after' to force a hitMode.
           *  Return ['before', 'after'] to restrict available hitModes.
           *  Any other return value will calc the hitMode from the cursor position.
           */
          // Prevent dropping a parent below another parent (only sort
          // nodes under the same parent)
          /*           if(node.parent !== data.otherNode.parent){
                      return false;
                    }
                    // Don't allow dropping *over* a node (would create a child)
                    return ["before", "after"];
          */
          // return true;
          if(node.isFolder()) {
            return true;
          } else {
            return ["before", "after"];
          }
        },
        dragDrop: function(node, data) {
          /** This function MUST be defined to enable dropping of items on
           *  the tree.
           */
          data.otherNode.moveTo(node, data.hitMode);
        },
        dragStop: function(node, data) {
          var neworder = [];
          var i = 0;
          var all_drop_data = node.getParent();

          if(node.isFolder()) {
            all_drop_data.visit(function(all_drop_data) {
              if(all_drop_data.isFolder()) {
                neworder[i] = all_drop_data.key;
                i++;
              }
            });
          } else {
            all_drop_data.visit(function(all_drop_data) {
              if(!all_drop_data.isFolder()) {
                neworder[i] = all_drop_data.key;
                i++;
              }
            });
          }

          neworder = JSON.stringify(neworder);

          $.post("/html/pricing/ajax/item_actions.php?action=updateCategoryOrder", {newOrder: neworder, parent: node.parent.key, curCat: node.key, isFolder: node.isFolder()}, function(data) {
            $("body").append(data);
          });
        }
      }
    }).on("nodeCommand", function(event, data){
      // Custom event handler that is triggered by keydown-handler and
      // context menu:
      var refNode, moveMode,
        tree = $(this).fancytree("getTree"),
        node = tree.getActiveNode(),
        addType = null;

      if(editCatalog.hasClass("fa-unlock")) {
        switch (data.cmd) {
          case "moveUp":
            refNode = node.getPrevSibling();
            if (refNode) {
              node.moveTo(refNode, "before");
              node.setActive();
            }
            break;
          case "moveDown":
            refNode = node.getNextSibling();
            if (refNode) {
              node.moveTo(refNode, "after");
              node.setActive();
            }
            break;
          case "indent":
            refNode = node.getPrevSibling();
            if (refNode) {
              node.moveTo(refNode, "child");
              refNode.setExpanded();
              node.setActive();
            }
            break;
          case "outdent":
            if (!node.isTopLevel()) {
              node.moveTo(node.getParent(), "after");
              node.setActive();
            }
            break;
          case "addChild":
            if(node.isFolder()) {
              addType = 'child';
            } else {
              addType = 'after';
            }

            $.get("/html/pricing/ajax/modify_item.php", {type: 'addItem', id: node.key}, function(data) {
              $("#modalGeneral").html(data).modal("show");
            });
            break;
          case "cut":
            CLIPBOARD = {mode: data.cmd, data: node};
            break;
          case "copy":
            CLIPBOARD = {
              mode: data.cmd,
              data: node.toDict(function (n) {
                delete n.key;
              })
            };
            break;
          case "clear":
            CLIPBOARD = null;
            break;
          case "paste":
            if (CLIPBOARD.mode === "cut") {
              // refNode = node.getPrevSibling();
              CLIPBOARD.data.moveTo(node, "child");
              CLIPBOARD.data.setActive();
            } else if (CLIPBOARD.mode === "copy") {
              node.addChildren(CLIPBOARD.data).setActive();
            }
            break;
          case "deselect":
            if (node !== null)
              node.setActive(false);
            break;
          case "delete":
            $.confirm({
              title: "Are you sure you want to remove this item?",
              content: "You are about to remove " + node.title + ". Are you sure?",
              type: 'red',
              buttons: {
                yes: function() {
                  node.remove();
                },
                no: function() {}
              }
            });
            break;
          case "addSubFolder":
            $.get("/html/pricing/ajax/modify_item.php", {type: 'newSubFolder', id: node.key}, function(data) {
              $("#modalGeneral").html(data).modal("show");
            });
            break;
          case "addFolder":
            $.get("/html/pricing/ajax/modify_item.php", {type: 'newSameFolder', id: node.key}, function(data) {
              $("#modalGeneral").html(data).modal("show");
            });
            break;
          case "save":
            $("#saveOPL").trigger('click');
            break;
          case "edit":
            let type = null;

            if(node.isFolder()) {
              type = 'folder';
            } else {
              type = 'item';
            }

            $.get("/html/pricing/ajax/modify_item.php", {type: type, id: node.key}, function(data) {
              $("#modalGeneral").html(data).modal("show");
            });
            break;
          default:
            alert("Unhandled command: " + data.cmd);
            return;
        }
      }
    }).on("keydown", function(e){
      var cmd = null;

      switch( $.ui.fancytree.eventToString(e) ) {
        case "ctrl+shift+n":
        case "meta+shift+n": // mac: cmd+shift+n
          cmd = "addChild";
          break;
        case "ctrl+shift+e":
        case "meta+shift+e":
          cmd = "addSibling";
          break;
        case "ctrl+shift+f":
        case "meta+shift+f":
          cmd = "addFolder";
          break;
        case "ctrl+r": // beacause this is refresh and I lost my changes :(
        case "meta+r":
        case "ctrl+shift+r":
        case "meta+shift+r":
          e.preventDefault();
          break;
        case "ctrl+f":
        case "meta+f":
          e.preventDefault();
          cmd = "addSubFolder";
          break;
        case "ctrl+e":
        case "meta+e":
          cmd = "addChild";
          break;
        case "ctrl+c":
        case "meta+c": // mac
          cmd = "copy";
          break;
        case "ctrl+v":
        case "meta+v": // mac
          cmd = "paste";
          break;
        case "ctrl+x":
        case "meta+x": // mac
          cmd = "cut";
          break;
        case "ctrl+s":
        case "meta+s":
          e.preventDefault();
          cmd = "save";
          break;
        case "ctrl+o":
        case "meta+o":
          // TODO: Assign an SO # to lines, we're gonna show operations and edit SO's from here
          break;
        case "ctrl+shift+up":
        case "ctrl+up":
          cmd = "moveUp";
          break;
        case "ctrl+shift+down":
        case "ctrl+down":
          cmd = "moveDown";
          break;
        case "ctrl+right":
        case "ctrl+shift+right": // mac
          cmd = "indent";
          break;
        case "ctrl+left":
        case "ctrl+shift+left": // mac
          cmd = "outdent";
          break;
        case "ctrl+del":
          cmd = "delete";
          break;
        case "esc":
          cmd = "deselect";
          break;
      }

      if(cmd) {
        $(this).trigger("nodeCommand", {cmd: cmd});
        return false;
      }
    });

    catalog.contextmenu({
      delegate: "span.fancytree-node",
      menu: [
        {title: "Edit <kbd>[F2]</kbd>", cmd: "edit", uiIcon: "ui-icon-pencil" },
        {title: "----"},
        {title: "New Item <kbd>[Ctrl+E]</kbd>", cmd: "addChild", uiIcon: "ui-icon-plus"},
        {title: "----"},
        {title: "New Same Level Category <kbd>[Ctrl+Shift+F]</kbd>", cmd: "addFolder", uiIcon: "ui-icon-folder-collapsed"},
        {title: "New Sub-Category <kbd>[Ctrl+F]</kbd>", cmd: "addSubFolder", uiIcon: "ui-icon-folder-open"},
        {title: "----"},
        {title: "Delete <kbd>[Ctrl+Del]</kbd>", cmd: "delete", uiIcon: "ui-icon-circle-minus"}
      ],
      beforeOpen: function(event, ui) {
        var node = $.ui.fancytree.getNode(ui.target);
        catalog.contextmenu("enableEntry", "paste", !!CLIPBOARD);
        node.setActive();

        ui.menu.css('zIndex', 1001);

        // check each sub-node for the active node
        catalog.fancytree("getTree").getActiveNode().visit(function(e) {
          if(e.isFolder()) { // if there is a folder, we're disabling add new item
            catalog.contextmenu('updateEntry', 'addChild', {disabled: true}); // update addchild (item) menu item to be disabled
            return false; // no need to continue, we've found a folder
          } else { // otherwise, there are no folders, we can proceed with adding items but cannot add folders
            catalog.contextmenu('updateEntry', 'addChild', {disabled: false});
          }
        });

        return catalogCanEdit();
      },
      select: function(event, ui) {
        var that = this;
        // delay the event, so the menu can close and the click event does
        // not interfere with the edit control
        setTimeout(function(){
          $(that).trigger("nodeCommand", {cmd: ui.cmd});
        }, 100);
      }
    });

    // this is the modifications modal popup
    itemModifications.fancytree({
      source: { url: "/html/pricing/ajax/modifications.php" },
      extensions: ["filter", "table"],
      debugLevel: 0,
      keyboard: false,
      table: {
        indentation: 20,
        nodeColumnIdx: 0
      },
      filter: {
        autoApply: true,   // Re-apply last filter if lazy data is loaded
        autoExpand: true, // Expand all branches that contain matches while filtered
        counter: true,     // Show a badge with number of matching child nodes near parent icons
        fuzzy: true,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
        hideExpandedCounter: true,  // Hide counter badge if parent is expanded
        hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
        highlight: true,   // Highlight matches by wrapping inside <mark> tags
        leavesOnly: false, // Match end nodes only
        nodata: true,      // Display a 'no data' status node if result is empty
        mode: "hide"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
      },
      renderColumns: function(event, data) {
        // this section handles the column data itself
        var node = data.node, $tdList = $(node.tr).find(">td");

        // Index #0 => Checkbox

        // Index #1 => SKU/Description
        let extraMod = '';

        if(node.data.description !== undefined) {
          extraMod += node.data.description;
        }

        if(node.data.info !== undefined) {
          extraMod += node.data.info;
        }

        $tdList.eq(1).html(extraMod);

        // Index #2 => Additional Info Box
        // Addl Info is a JSON element encoded in a database, we need to parse each of them
        if(node.data.addl_info !== undefined && node.data.addl_info !== null && node.data.addl_info !== '') {
          let ai = JSON.parse(node.data.addl_info);
          let addlInfoOut = "<input type='" + ai.type + "' class='modAddlInfo' id='" + node.key + "' name='addlInfo[]' placeholder='" + ai.placeholder + "' />";

          $tdList.eq(2).html(addlInfoOut);
        }
      }
    });

    if($("#submit_for_quote").prop("disabled")) {
      $.confirm({
        title: "Item List Submitted.",
        content: "You are unable to save this form. It has already been submitted. Please check with your representative if you require any modifications.",
        type: 'red',
        buttons: {
          ok: function() {}
        }
      });
    }

    $("#modalAddModification").on("show.bs.modal", function() {
      $("#modificationsFilter").val('');
      itemModifications.fancytree("getTree").clearFilter();

      let modificationTree = {
        url: "/html/pricing/ajax/modifications.php?priceGroup=" + priceGroup + "&itemID=" + cabinetList.fancytree("getTree").getActiveNode().data.itemID,
        type: "POST",
        dataType: 'json'
      };

      itemModifications.fancytree("getTree").reload(modificationTree);
    });
    
    $(".room_note_log").hide();

    <?php echo !empty($room['custom_vin_info']) ? "customFieldInfo = JSON.parse('{$room['custom_vin_info']}');": null; ?>

    productTypeSwitch();

    <?php if (!empty($room['custom_vin_info'])) { ?>
    $.each(customFieldInfo, function(mainID, value) {
      $.each(value, function(i, v) {
        $("#" + mainID).parent().find(".selected").find("input[name='" + i + "']").val(v);
      });
    });
    <?php } ?>

    // Calculate totals and summary
    setTimeout(function() {
      recalcSummary();

      // automatically expand all sub-lines
      cabinetList.fancytree("getTree").visit(function(node){
        node.setExpanded();
      });
    }, 100);

    if($("#ship_via").val() === '4') {
      let ship_info = $("input[name='ship_to_name']").parent().parent();

      ship_info.hide();
      ship_info.next("tr").hide();
      ship_info.next("tr").next("tr").hide();
    }

    if(getUrlParams('print') === 'true') {
      setTimeout(function() {
        window.print();
        window.close();
      }, 250);
    }
  });
</script>