<?php
require '../../includes/header_start.php';

//outputPHPErrs();

$room_id = sanitizeInput($_REQUEST['room_id']);

$vin_schema = getVINSchema();

//<editor-fold desc="Get specific VIN info for pricing usage">
$vin_qry = $dbconn->query("SELECT segment, `key`, markup, markup_calculator
  FROM vin_schema ORDER BY segment ASC, case `group` when 'Custom' then 1 when 'Other' then 2 else 3 end, `group` ASC,
  FIELD(`value`, 'Custom', 'Other', 'No', 'None') DESC, FIELD(`key`, 'B78') DESC");

$json_vin = null;

while($vin = $vin_qry->fetch_assoc()) {
  $json_vin[$vin['segment']][$vin['key']] = array('markup' => $vin['markup'], 'markup_calculator' => $vin['markup_calculator']);
}
//</editor-fold>

$room_qry = $dbconn->query("SELECT r.*, so.dealer_code, so.company_id, so.project_addr, so.project_city, so.project_state, so.project_zip
  FROM rooms r LEFT JOIN sales_order so on r.so_parent = so.so_num WHERE r.id = $room_id ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

//<editor-fold desc="Disable submit buttons (if submitted)">
$existing_quote_qry = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $room_id");

if($existing_quote_qry->num_rows === 1) {
  $existing_quote = $existing_quote_qry->fetch_assoc();
} else {
  $existing_quote = null;
}

$company_qry = $dbconn->query("SELECT
       so.id AS soID,
       cc.id AS cID,
       d.account_type,
       cc.address AS companyAddr,
       cc.city AS companyCity,
       cc.state AS companyState,
       cc.zip AS companyZip,
       d.shipping_address AS dealerAddress,
       d.shipping_city AS dealerCity,
       d.shipping_state AS dealerState,
       d.shipping_zip AS dealerZip,
       d.multiplier
FROM sales_order so
  LEFT JOIN contact_company cc on so.company_id = cc.id
  LEFT JOIN dealers d ON cc.dealer_id = d.id
WHERE so.so_num = '{$room['so_parent']}';");
$company = $company_qry->fetch_assoc();

if(!empty($existing_quote['quote_submission'])) {
  $submit_disabled = 'disabled';

  $submitted_time = date(DATE_TIME_ABBRV, $existing_quote['quote_submission']);
  $submitted = "- Submitted on $submitted_time";
} else {
  $submit_disabled = null;
  $submitted = null;
}
//</editor-fold>

$notes_qry = $dbconn->query("SELECT * FROM notes WHERE (note_type = 'room_note_delivery' OR note_type = 'room_note_design' OR note_type = 'room_note_fin_sample') AND type_id = '$room_id'");

if($notes_qry->num_rows > 0) {
  while($note_result = $notes_qry->fetch_assoc()) {
    $note_arr[$note_result['note_type']] = $note_result;
  }
}

$shipZIP = !empty($room['ship_zip']) ? $room['ship_zip'] : $company['dealerZip'];
$ship_zone_info = calcShipZone($shipZIP);

if($room['ship_cost'] === null) {
  $ship_cost = (bool)$room['multi_room_ship'] ? 0 : $ship_zone_info['cost'];
} else {
  $ship_cost = (bool)$room['multi_room_ship'] ? 0 : $room['ship_cost'];
}

$ship_cost = number_format($ship_cost, 2);

$room['individual_bracket_buildout'] = null;

//<editor-fold desc="Determining the price group (for JavaScript)">
$pg_qry = $dbconn->query("SELECT vs1.id AS species_grade_id, vs2.id AS door_design_id FROM rooms r
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
//</editor-fold>
?>

<link href="/assets/css/pricing.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/css?family=Marck+Script" rel="stylesheet">

<style>
  #roomTabView {
    z-index: 2;
    top: 38px;
    background-color: #FFF;
  }

  .white-right-border {
    border-right: 1px solid #FFF;
  }
</style>

<script>
  <?php echo $submit_disabled !== null ? 'var already_submitted = true;' : 'var already_submitted = false;'; ?>
</script>

<div class="container-fluid" style="width:100%;">
  <div class="row sticky no-print" style="background-color:#FFF;z-index:3;top:21px;padding:4px;">
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
      <!--      <button class="btn waves-effect btn-secondary" title="Bracket Management" id="bracket_management"> <i class="fa fa-code-fork fa-2x"></i> </button>-->
      <button class="btn waves-effect btn-secondary" title="Door Sizing" onclick="window.open('/html/inset_sizing.php?room_id=<?php echo $room['id']; ?>','_blank')"> <i class="fa fa-arrows-alt fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Appliance Worksheets" id='appliance_ws' data-roomid='<?php echo $room['id']; ?>'> <i class="fa fa-cubes fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Recalculate Pricing" id="catalog_recalculate"> <i class="fa fa-retweet fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Download ORD File" id="dl_ord_file"><i class="fa fa-file-text-o fa-2x"></i></button>
      <button class="btn waves-effect btn-secondary" style="display:none;" title="Override Production Lock" id="production_lock"><i class="fa fa-lock fa-2x"></i></button>
    </div>
  </div>

  <form id="batch_info" method="post" action="#">
    <div class="row">
      <div class="col-md-12 m-t-10">
        <!--<editor-fold desc="Notes">-->
        <div class="col-md-6 sticky no-print" style="top:38px;border:1px solid #000;">
          <div class="row">
            <div class="col-md-12">
              <ul class="nav nav-tabs m-b-10 m-t-10" id="companyNotes" role="tablist">
                <li class="nav-item">
                  <a class="nav-link" id="p-company-tab" data-toggle="tab" href="#b_company" role="tab" aria-controls="b_company" aria-selected="false">Contact/Account Notes</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="p-project-tab" data-toggle="tab" href="#b_project" role="tab" aria-controls="b_project" aria-selected="false">Project Notes</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link active show" id="b-batch-tab" data-toggle="tab" href="#b_batch" role="tab" aria-controls="b_batch" aria-selected="false">Batch Notes</a>
                </li>
              </ul>
              <div class="tab-content" id="roomNotesContent">
                <div role="tabpanel" class="tab-pane fade" id="b_company" aria-labelledby="company-tab">
                  <div class="col-md-12">
                    <textarea class="form-control" name="company_notes" id="b_company_notes" placeholder="New Note" style="width:100%;height:130px;"></textarea>
                    <input type="text" name="company_followup_date" id="b_company_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                    <label for="b_requested_of" style="float:left;padding:4px;"> requested of </label>
                    <select name="requested_of" id="b_requested_of" class="form-control" style="width:45%;float:left;">
                      <option value="null" selected disabled></option>
                      <?php
                      $user_qry = $dbconn->query('SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC');

                      while ($user = $user_qry->fetch_assoc()) {
                        echo "<option value='{$user['id']}'>{$user['name']}</option>";
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-md-12">
                    <h5>History</h5>

                    <table class="table-bordered table-striped table" width="100%">
                      <thead>
                      <tr>
                        <th>Individual</th>
                        <th>Date/Time</th>
                        <th>Note</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php
                      $note_qry = $dbconn->query("SELECT n.*, u.name FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'company_note' AND type_id = {$company['cID']}");

                      if($note_qry->num_rows > 0) {
                        while($note = $note_qry->fetch_assoc()) {
                          $note_preview = substr($note['note'], 0, 80);

                          if(strlen($note['note']) > 80) {
                            $note_preview = trim($note_preview);
                            $note_preview .= '...';
                          }

                          $note_preview = str_ireplace(PHP_EOL, '<i class="fa fa-level-down fa-rotate-90" style="margin:0 5px;"></i>', $note_preview);

                          $time = date(DATE_TIME_ABBRV, $note['timestamp']);

                          $note_translated = nl2br($note['note']);

                          $full_note = "<div style='background-color:#FFF;border:1px solid #000;padding:2px;display:none;'>$note_translated</div>";

                          echo '<tr class="cursor-hand view_note_information">';
                          echo "<td>{$note['name']}</td>";
                          echo "<td>$time</td>";
                          echo "<td>$note_preview $full_note</td>";
                          echo '</tr>';
                        }
                      } else {
                        echo '<tr><td colspan="3"><b>No company notes currently available.</b></td></tr>';
                      }
                      ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div role="tabpanel" class="tab-pane fade" id="b_project" aria-labelledby="project-tab">
                  <div class="col-md-12">
                    <textarea class="form-control" name="project_notes" id="b_project_notes" placeholder="New Note" style="width:100%;height:130px;"></textarea>
                    <input type="text" name="project_followup_date" id="b_project_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                    <label for="b_project_requested_of" style="float:left;padding:4px;"> requested of </label>
                    <select name="project_requested_of" id="b_project_requested_of" class="form-control" style="width:45%;float:left;">
                      <option value="null" selected disabled></option>
                      <?php
                      $user_qry = $dbconn->query('SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC');

                      while ($user = $user_qry->fetch_assoc()) {
                        echo "<option value='{$user['id']}'>{$user['name']}</option>";
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-md-12">
                    <h5>History</h5>

                    <table class="table-bordered table-striped table" width="100%">
                      <thead>
                      <tr>
                        <th>Individual</th>
                        <th>Date/Time</th>
                        <th>Note</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php
                      $note_qry = $dbconn->query("SELECT n.*, u.name FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'so_inquiry' AND type_id = {$company['soID']}");

                      if($note_qry->num_rows > 0) {
                        while($note = $note_qry->fetch_assoc()) {
                          $note_preview = substr($note['note'], 0, 80);

                          if(strlen($note['note']) > 80) {
                            $note_preview = trim($note_preview);
                            $note_preview .= '...';
                          }

                          $note_preview = str_ireplace(PHP_EOL, '<i class="fa fa-level-down fa-rotate-90" style="margin:0 5px;"></i>', $note_preview);

                          $time = date(DATE_TIME_ABBRV, $note['timestamp']);

                          $note_translated = nl2br($note['note']);

                          $full_note = "<div style='background-color:#FFF;border:1px solid #000;padding:2px;display:none;'>$note_translated</div>";

                          echo '<tr class="cursor-hand view_note_information">';
                          echo "<td>{$note['name']}</td>";
                          echo "<td>$time</td>";
                          echo "<td>$note_preview $full_note</td>";
                          echo '</tr>';
                        }
                      } else {
                        echo '<tr><td colspan="3"><b>No project notes currently available.</b></td></tr>';
                      }
                      ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div role="tabpanel" class="tab-pane fade in active show" id="b_batch" aria-labelledby="batch-tab">
                  <div class="col-md-12">
                    <textarea class="form-control" name="batch_notes" id="b_batch_notes" placeholder="New Note" style="width:100%;height:130px;"></textarea>
                    <input type="text" name="batch_followup_date" id="b_batch_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                    <label for="b_batch_requested_of" style="float:left;padding:4px;"> requested of </label>
                    <select name="batch_requested_of" id="b_batch_requested_of" class="form-control" style="width:45%;float:left;">
                      <option value="null" selected disabled></option>
                      <?php
                      $user_qry = $dbconn->query('SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC');

                      while ($user = $user_qry->fetch_assoc()) {
                        echo "<option value='{$user['id']}'>{$user['name']}</option>";
                      }
                      ?>
                    </select>
                  </div>

                  <div class="col-md-12">
                    <h5>History</h5>

                    <table class="table-bordered table-striped table" width="100%">
                      <thead>
                      <tr>
                        <th>Individual</th>
                        <th>Date/Time</th>
                        <th>Note</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php
                      $note_qry = $dbconn->query("SELECT n.*, u.name FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'room_note' AND type_id = $room_id");

                      if($note_qry->num_rows > 0) {
                        while($note = $note_qry->fetch_assoc()) {
                          $note_preview = substr($note['note'], 0, 80);

                          if(strlen($note['note']) > 80) {
                            $note_preview = trim($note_preview);
                            $note_preview .= '...';
                          }

                          $note_preview = str_ireplace(PHP_EOL, '<i class="fa fa-level-down fa-rotate-90" style="margin:0 5px;"></i>', $note_preview);

                          $time = date(DATE_TIME_ABBRV, $note['timestamp']);

                          $note_translated = nl2br($note['note']);

                          $full_note = "<div style='background-color:#FFF;border:1px solid #000;padding:2px;display:none;'>$note_translated</div>";

                          echo '<tr class="cursor-hand view_note_information">';
                          echo "<td>{$note['name']}</td>";
                          echo "<td>$time</td>";
                          echo "<td>$note_preview $full_note</td>";
                          echo '</tr>';
                        }
                      } else {
                        echo '<tr><td colspan="3"><b>No batch notes currently available.</b></td></tr>';
                      }
                      ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--</editor-fold>-->
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">
        <h5>Payment</h5>

        <?php if($bouncer->validate('view_accounting')) { ?>
          <table>
          <tr>
            <td colspan="2">
              <label class="c-input c-checkbox">Deposit Received <input type="checkbox" name="deposit_received" value="1" <?php echo ((bool)$room['payment_deposit']) ? 'checked' :null; ?>><span class="c-indicator"></span></label><br />
              <label class="c-input c-checkbox">Prior to Loading: Distribution - Final Payment<br/><span style="margin-left:110px;">Retail - On Delivery/Payment</span> <input type="checkbox" name="ptl_del" value="1" <?php echo ((bool)$room['payment_del_ptl']) ? 'checked' :null; ?>><span class="c-indicator"></span></label><br />
              <label class="c-input c-checkbox">Retail - Final Payment <input type="checkbox" name="final_payment" value="1" <?php echo ((bool)$room['payment_final']) ? 'checked' :null; ?>><span class="c-indicator"></span></label>
            </td>
          </tr>
          </table>
        <?php } ?>
      </div>

      <div class="col-md-3">
        <h5>Sample</h5>

        <h5>Approved</h5>

        <p>Reference: <b>None</b></p>
      </div>
    </div>

    <div class="row m-t-10">
      <div class="col-md-4" style="border-radius:.25rem;border:1px solid #000;padding-bottom:25px;background-color:#d8d8d8;">
        <!--<editor-fold desc="Global Details (Left Side)">-->
        <h5><u>Global: Batch Details (Net Price)</u></h5>

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
            <td>Dealer PO/Batch:</td>
            <td colspan="2"><input type="text" class="c_input border_thin_bottom" id="room_name" name="room_name" placeholder="Room Name" value="<?php echo $room['room_name']; ?>"></td>
          </tr>
          <tr>
            <td>Billing Type:</td>
            <td colspan="2"><strong><?php echo $company['account_type'] === 'R' ? 'Retail' : 'Distribution'; ?></strong></td>
          </tr>
          <tr>
            <td>Order Type:</td>
            <td><?php echo getSelect('product_type'); ?></td>
            <td></td>
          </tr>
          <tr>
            <td><span id="leadTimeDef">Lead Time:</span></td>
            <td><?php echo getSelect('days_to_ship'); ?></td>
            <td></td>
          </tr>
          <tr>
            <td>Order Status:</td>
            <td><?php echo getSelect('order_status'); ?></td>
            <td></td>
          </tr>
          <tr>
            <td><span class="estimated"><?php echo $room['order_status'] === '#' ? 'Est. ' : null; ?></span>Ship Date (*):</td>
            <td><strong id="calcd_ship_date"><?php echo !empty($room['ship_date']) ? date(DATE_DEFAULT, $room['ship_date']) : 'TBD'; ?></strong></td>
            <td style="font-size:1.25em;white-space:nowrap;"><i class="fa fa-truck cursor-hand no-print" title="Calculate Ship Date" id='ship_date_recalc' data-roomid='<?php echo $room['id']; ?>'></i> &nbsp;<i class="fa fa-pencil-square no-print cursor-hand" id="overrideShipDate" title="Edit/Override Ship Date"></i></td>
          </tr>
          <tr>
            <td><span class="estimated"><?php echo $room['order_status'] === '#' ? 'Est. ' : null; ?></span>Delivery Date (*):</td>
            <td><strong id="calcd_del_date"><?php echo !empty($room['ship_date']) ? date(DATE_DEFAULT, $room['delivery_date']) : 'TBD'; ?></strong></td>
            <td></td>
          </tr>
          <tr>
            <td>Ship VIA:</td>
            <td><?php echo getSelect('ship_via'); ?></td>
            <td></td>
          </tr>

          <?php
          // determine ship to address
          if($room['jobsite_delivery']) {
            $ship_addr = $room['project_addr'];
            $ship_city = $room['project_city'];
            $ship_state = $room['project_state'];
            $ship_zip = $room['project_zip'];
          } else {
            if(empty($room['ship_addr'])) { // batch doesn't have an address, so lets move up to company
              $ship_addr = $company['companyAddr'];
              $ship_city = $company['companyCity'];
              $ship_state = $company['companyState'];
              $ship_zip = $company['companyZip'];
            } else { // batch has an address
              $ship_addr = $room['ship_addr'];
              $ship_city = $room['ship_city'];
              $ship_state = $room['ship_state'];
              $ship_zip = $room['ship_zip'];
            }
          }
          ?>

          <tr rowspan="3">
            <td style="vertical-align:top !important;">Ship To:</td>
            <td colspan="2">
              <input type="text" style="width:75%;" class="c_input static_width align_left" placeholder="Name" name="ship_to_name" value="<?php echo $room['ship_name']; ?>">
              <input type="text" style="width:75%;" class="c_input static_width align_left" placeholder="Address" name="ship_to_address" value="<?php echo $ship_addr; ?>">
              <input type="text" style="width:50%;" class="c_input static_width align_left pull-left" placeholder="City" name="ship_to_city" value="<?php echo $ship_city; ?>"> <input type="text" style="width:15px;margin-left:10px;" class="static_width align_left c_input pull-left" name="ship_to_state" value="<?php echo $ship_state; ?>"> <input type="text" style="width:51px;margin-left:10px;" class="static_width align_left c_input pull-left" placeholder="ZIP" name="ship_to_zip" value="<?php echo $ship_zip; ?>">
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td colspan="2"><input type="checkbox" value="1" name="jobsite_delivery" id="jobsite_delivery" <?php echo $room['jobsite_delivery'] ? 'checked' : null; ?>> <label for="jobsite_delivery">Jobsite Delivery</label></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td colspan="2"><input type="checkbox" value="1" name="multi_room_ship" id="multi_room_ship" <?php echo $room['multi_room_ship'] ? 'checked' : null; ?>> <label for="multi_room_ship">Multi-room shipping</label></td>
          </tr>
          <tr>
            <td>Shipping Zone:</td>
            <td><strong><?php echo $ship_zone_info['zone']; ?></strong><i style="font-size:1.25em;float:right;" class="fa fa-pencil-square no-print cursor-hand" id="overrideShipCost" title="Override Ship Cost"></td>
            <td id="shipping_cost" class="pricing_value" data-cost="<?php echo $ship_cost; ?>"></td>
          </tr>
          <tr>
            <td colspan="3" style="height:2px;"></td>
          </tr>
          <tr>
            <td>Payment Method:</td>
            <td><?php echo getSelect('payment_method'); ?></td>
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
            <td colspan="3">
              <table width="100%">
                <tr><th>&nbsp;Notes</th></tr>
                <tr><td class="gray_bg">&nbsp;Delivery Notes:</td></tr>
                <tr><td id="delivery_notes" style="border:none;"><textarea name="delivery_notes" maxlength="280" class="static_width pricing_textbox"><?php echo stripcslashes($note_arr['room_note_delivery']['note']); ?></textarea></td></tr>
              </table>

              <input type="hidden" name="delivery_notes_id" value="<?php echo $note_arr['room_note_delivery']['id']; ?>" />
            </td>
          </tr>
        </table>
        <!--</editor-fold>-->
      </div>

      <div class="col-sm-8 pricing_table_format m-t-10">
        <div class="col-md-12">
          <div class="global_cab_header"><h5><u>Global: Product Details</u></h5></div>
        </div>

        <!--<editor-fold desc="Second Column: Cabinet Details">-->
        <div class="col-sm-6">
          <table width="100%">
            <tr>
              <th style="padding-left:5px;" colspan="2">Design</th>
            </tr>
            <tr class="border_top">
              <td width="35%" class="border_thin_bottom">Construction Method:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('construction_method'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Species/Grade:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('species_grade'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Carcass Material:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('carcass_material'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Door Design:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('door_design'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom"><div>Door Panel Raise:</div></td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('panel_raise', 'panel_raise_door'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Short Drawer Raise:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('panel_raise', 'panel_raise_sd'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Tall Drawer Raise:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('panel_raise', 'panel_raise_td'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Style/Rail Width:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('style_rail_width'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Edge Profile:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('edge_profile'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Framing Bead:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('framing_bead'); ?></div></td>
              <td class="border_thin_bottom"></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Frame Option:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('framing_options'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Drawer Box:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('drawer_boxes'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Drawer Guide:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('drawer_guide'); ?></div></td>
            </tr>
            <tr>
              <td colspan="2" style="height:117px;"></td>
            </tr>
            <tr>
              <td colspan="2">
                <table width="100%">
                  <tr><th>&nbsp;</th></tr>
                  <tr><td class="gray_bg">&nbsp;Design Notes:</td></tr>
                  <tr><td><textarea name="room_note_design" maxlength="280" class="pricing_textbox"><?php echo stripcslashes($note_arr['room_note_design']['note']); ?></textarea></td>
                  </tr>
                </table>

                <input type="hidden" name="design_notes_id" value="<?php echo $note_arr['room_note_design']['id']; ?>" />
              </td>
            </tr>
          </table>
        </div>
        <!--</editor-fold>-->

        <!--<editor-fold desc="Third column: Cabinet Finish">-->
        <div class="col-sm-6" style="padding-left:0;">
          <table width="100%">
            <tr><th colspan="2" style="padding-left:5px;" class="th_17">Finish</th></tr>
            <tr class="border_top">
              <td class="border_thin_bottom" width="30%">Finish Code:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('finish_code'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Sheen:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('sheen'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Glaze Color:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('glaze'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Glaze Technique:</td>
              <td class="border_thin_bottom pricing_value"><div class="cab_specifications_desc"><?php echo getSelect('glaze_technique'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Antiquing:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('antiquing'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Worn Edges:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('worn_edges'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Distressing:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('distress_level'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Enviro-finish:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('green_gard'); ?></div></td>
            </tr>
            <tr>
              <td colspan="2" style="height:212px;"></td>
            </tr>
            <tr>
              <td colspan="2">
                <table width="100%">
                  <tr><th>&nbsp;</th></tr>
                  <tr><td class="gray_bg">&nbsp;Finishing/Sample Notes:</td></tr>
                  <tr><td><textarea name="fin_sample_notes" maxlength="280" class="static_width pricing_textbox"><?php echo stripcslashes($note_arr['room_note_fin_sample']['note']); ?></textarea></td></tr>
                </table>

                <input type="hidden" name="fin_sample_notes_id" value="<?php echo $note_arr['room_note_fin_sample']['id']; ?>" />
              </td>
            </tr>
          </table>
        </div>
        <!--</editor-fold>-->
      </div>
    </div>

    <div class="row m-t-10 pricing_table_format">
      <div class="col-md-3 pricing_left_nav no-print sticky">
        <div class="sticky nav_filter">
          <table width="100%" style="margin-bottom:10px;">
            <tr>
              <td><label for="left_menu_options">Library:</label></td>
              <td>
                <select id="left_menu_options" class="c_input ignoreSaveAlert">
                  <option value="catalog">Catalog</option>
                  <option value="samples">Samples</option>
                </select>
              </td>
            </tr>
            <tr>
              <td><label for="treeFilter">Search:</label></td>
              <td><input type="text" class="form-control fc-simple ignoreSaveAlert border_thin_bottom" id="treeFilter" placeholder="Search..." width="100%" ></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td><span class="pull-left cursor-hand"><i id="category_collapse" class="fa fa-fw fa-level-up" title="Collapse all categories"></i> Collapse</span></td>
              <td><?php echo $bouncer->validate('pricing_change_catalog') ? '<span class="pull-right"><i id="editCatalogLock" class="fa fa-fw fa-lock cursor-hand" title="Lock/Unlock Catalog for Changes"></i></span>' : null ?></td>
            </tr>
          </table>
        </div>

        <div id="action_container"></div>
      </div>

      <!--<editor-fold desc="Item List">-->
      <div class="col-md-9 itemListWrapper" style="margin-top:5px;">
        <div class="item_list_header sticky">
          <h5><u>Item List</u></h5>

          <input type="button" class="btn btn-danger waves-effect waves-light no-print" style="display:none;" id="catalog_remove_checked" value="Delete" />
          <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="item_note"><span class="btn-label"><i class="fa fa-commenting-o"></i> </span>Custom Note</button>
          <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="item_custom_line"><span class="btn-label"><i class="fa fa-plus"></i> </span>Custom Item</button>
          <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="detailed_item_summary"><span class="btn-label"><i class="fa fa-list"></i> </span>Detailed Report</button>

          <div class="clearfix"></div>
        </div>

        <table class="sticky" style="width:100%;top:60px;">
          <colgroup>
            <col width="40px">
            <col width="35px">
            <col width="50px">
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
        </table>

        <table id="cabinet_list" style="width:100%;">
          <colgroup>
            <col width="40px">
            <col width="40px">
            <col width="50px">
            <col width="150px">
            <col width="350px">
            <col width="50px">
            <col width="50px">
            <col width="50px">
            <col width="50px">
            <col width="50px">
          </colgroup>

          <tbody>
          <!-- Define a row template for all invariant markup: -->
          <tr>
            <td></td>
            <td><input type="text" class="form-control qty_input" value="1" placeholder="Qty" /> </td>
            <td class="text-md-center">
              <i class="fa fa-info-circle primary-color view_item_info cursor-hand" data-id=""></i>
              <i class="fa fa-minus-circle danger-color delete_item cursor-hand" title="Delete line"></i>
              <i class="fa fa-pencil-square secondary-color add_item_mod cursor-hand" title="Add modification"></i>
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

        <!--<editor-fold desc="Summary of Charges">-->
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
              <td class="text-md-right total_text" id="itemListMultiplier"><?php echo $company['multiplier']; ?></td>
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
            <tr class="border_thin_bottom" style="<?php echo $room['jobsite_delivery'] ? null : 'display:none;' ?>">
              <td class="total_text">Jobsite Delivery:</td>
              <td class="total_text">&nbsp;</td>
              <td class="text-md-right total_text" id="itemListJobsiteDelivery">$150.00</td>
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
            <tr>
              <td>&nbsp;</td>
            </tr>
            <tr class="white-right-border">
              <td colspan="3" style="vertical-align:top !important;">Signature:</td>
            </tr>
            <tr class="white-right-border">
              <td colspan="3"><input type="text" class="esig" name="signature" placeholder="(Digital signature affirms the following:)" style="width:100%;border:1px dashed #000;padding:3px;" value="<?php echo $room['esig']; ?>" <?php echo !empty($room['esig']) ? "disabled" : null;  ?> /></td>
            </tr>
            <tr class="white-right-border">
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
            <tr class="white-right-border">
              <td colspan="3" style="height:5px;"></td>
            </tr>
            <tr class="white-right-border">
              <td colspan="3" style="padding-left:2px;">
                <ul>
                  <li>A 50% deposit will be drafted within 24 hours.</li>
                  <li>(*) Shipping/delivery date confirmed upon deposit.</li>
                  <li>Final payment is due prior to delivery.</li>
                </ul>
              </td>
            </tr>
          </table>

          <div class="clearfix"></div>
        </div>
        <!--</editor-fold>-->
      </div>
      <!--</editor-fold>-->
    </div>
  </form>
</div>



<iframe id="dlORDfile" src="" style="display:none;visibility:hidden;"></iframe>

<script>
  pricingVars.nameOfUser = '<?php echo $_SESSION['userInfo']['name']; ?>';
  pricingVars.vinInfo = JSON.parse('<?php echo strip_tags(json_encode($json_vin)); ?>');
  pricingVars.shipCost = <?php echo $ship_cost; ?>;

  <?php
  $shipZone = !empty($room['ship_zip']) ? $room['ship_zip'] : $company['dealerZip'];
  $ship_zone_info = calcShipZone($shipZone);
  $shipInfo = json_encode($ship_zone_info, true);

  echo !empty($price_group) ? "var priceGroup = $price_group;" : null;
  echo "var calcShipZip = '{$room['ship_zip']}';";
  echo "var calcDealerShipZip = '{$company['dealerZip']}';";
  echo "var calcShipInfo = '$shipInfo';";
  ?>

  var CLIPBOARD = null,

    editCatalog = $("#editCatalogLock"),
    cabinetList = $("#cabinet_list"),
    catalog = $("#action_container"),
    itemModifications = $("#item_modifications");

  $(function() {
    crmBatch.index.init();

    globalFunctions.checkDropdown();

    <?php if($room['order_status'] === '$') { ?>
    pricingFunction.disableInput();
    <?php }?>

    pricingFunction.productTypeSwitch();

    //<editor-fold desc="Auto-note height">
    let tx_design = $("textarea[name='room_note_design']");
    let tx_fin_sample = $("textarea[name='fin_sample_notes']");
    let designheight = (tx_design.prop('scrollHeight') < 180) ? 180 : tx_design.prop('scrollHeight');
    let finsampleheight = (tx_fin_sample.prop('scrollHeight') < 180) ? 180 : tx_fin_sample.prop('scrollHeight');

    tx_design.height(designheight);
    tx_fin_sample.height(finsampleheight);
    //</editor-fold>

    //<editor-fold desc="Custom select field display">
    <?php
    echo !empty($room['custom_vin_info']) ? "let customFieldInfo = JSON.parse('{$room['custom_vin_info']}');": null;

    if (!empty($room['custom_vin_info'])) {
      echo /** @lang JavaScript */
      <<<HEREDOC
      $.each(customFieldInfo, function(mainID, value) {
        $.each(value, function(i, v) {
          $("#" + mainID).parent().find("input[name='" + i + "']").val(v);
        });
      });
HEREDOC;
    }
    ?>
    //</editor-fold>

    //<editor-fold desc="Cabinet List">
    cabinetList.fancytree({
      imagePath: "/assets/images/cabinet_icons/",
      titlesTabbable: true,     // Add all node titles to TAB chain
      quicksearch: true,        // Jump to nodes when pressing first character
      source: { url: "/html/pricing/ajax/item_actions.php?action=getCabinetList&room_id=" + active_room_id },
      extensions: ["dnd", "table", "gridnav", "persist"],
      dnd: { // drag and drop
        preventVoidMoves: true,
        preventRecursiveMoves: true,
        autoExpandMS: 600,
        dragStart: function(node, data) {
          return true;
        },
        dragEnter: function(node, data) {
          // return ["before", "after"];
          return true;
        },
        dragDrop: function(node, data) {
          data.otherNode.moveTo(node, data.hitMode);
          cabinetList.fancytree("getTree").visit(function(node) {
              var $tdList = $(node.tr).find(">td");
              $tdList.eq(0).text(node.getIndexHier());
          });
        }
      },
      table: {
        indentation: 20,
        nodeColumnIdx: 3
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
        console.log(node.data.width);
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
        } else {
          $tdList.eq(2).find('.add_item_mod, .item_copy').css('visibility', 'hidden');
        }

        // calculation of how many pages are going to print (total)
        numPages = Math.ceil($(".wrapper").outerHeight() / 980);

        $("#num_of_pgs").text(numPages);

        // update of Global: Cabinet Details pricing
        // TODO: Short Drawer Raise, Tall Drawer Raise, Frame Option, Drawer Box

        // Glaze Technique:
        $("#gt_amt").text();
      },
      modifyChild: function(event, data) {
        pricingFunction.recalcSummary();
      },
      init: function() {
        pricingFunction.recalcSummary();

        // automatically expand all sub-lines
        cabinetList.fancytree("getTree").visit(function(node){
          let $tdList = $(node.tr).find(">td"); // get the columns of the item list

          node.setExpanded(); // set the node as expanded

          if(!node.isTopLevel()) { // if it's not a top level item
            $tdList.eq(2).find('.add_item_mod, .item_copy').css('visibility', 'hidden'); // set the visibility of both item copy and item mod as hidden
          }
        });
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
    //</editor-fold>

    //<editor-fold desc="Navigation menu">
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
          return pricingFunction.catalogCanEdit(editCatalog);
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
              $("#modalGlobal").html(data).modal("show");
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
            let hasChildren = (node.hasChildren()) ? " <b>and <u>ALL</u> items/categories under it</b>" : '';

            $.confirm({
              title: "Are you sure you want to remove this item?",
              content: "You are about to remove " + node.title + hasChildren + ". Are you sure?",
              type: 'red',
              buttons: {
                yes: function() {
                  let type = (node.isFolder()) ? 'folder' : 'item';

                  $.post("/html/pricing/ajax/item_actions.php", {action: 'delete', type: type, key: node.key}, function(data) {
                    $("body").append(data);
                    node.remove();
                  });
                },
                no: function() {}
              }
            });
            break;
          case "addSubFolder":
            $.get("/html/pricing/ajax/modify_item.php", {type: 'newSubFolder', id: node.key}, function(data) {
              $("#modalGlobal").html(data).modal("show");
            });
            break;
          case "addFolder":
            $.get("/html/pricing/ajax/modify_item.php", {type: 'newSameFolder', id: node.key}, function(data) {
              $("#modalGlobal").html(data).modal("show");
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
              $("#modalGlobal").html(data).modal("show");
            });
            break;
          case "duplicateItem":

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
        case "ctrl+d":
        case "meta+d": // mac: cmd+d
          cmd = "duplicateItem";
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
        {title: "Duplicate Item <kbd>[Ctrl+D]</kbd>", cmd: "duplicateItem", uiIcon: "ui-icon-copy"},
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
        $(".info-popup").hide();

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

        return pricingFunction.catalogCanEdit(editCatalog);
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
    //</editor-fold>

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
  });

  pricingVars.roomQry = JSON.parse('<?php $room['custom_vin_info'] = null; echo json_encode($room); ?>');
</script>