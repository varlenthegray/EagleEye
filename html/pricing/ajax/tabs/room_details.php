<?php
require '../../../../includes/header_start.php';

$room_id = sanitizeInput($_REQUEST['room_id']);

$vin_schema = getVINSchema();

$room_qry = $dbconn->query("SELECT r.*, so.dealer_code, so.project_addr, so.project_city, so.project_state, so.project_zip FROM rooms r LEFT JOIN sales_order so on r.so_parent = so.so_num WHERE r.id = $room_id ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$room['dealer_code']}'");
$dealer = $dealer_qry->fetch_assoc();

$note_arr = array();

// FIXME: This should really be a limit of 1 with a sort order attached to it
$notes_qry = $dbconn->query("SELECT * FROM notes WHERE note_type = 'room_note_delivery' AND type_id = '$room_id'");

if($notes_qry->num_rows > 0) {
  while($note_result = $notes_qry->fetch_assoc()) {
    $note_arr[$note_result['note_type']] = $note_result;
  }
}

$ship_addr = empty(trim($room['ship_address'])) ? $room['project_addr'] : $room['ship_address'];
$ship_city = empty(trim($room['ship_city'])) ? $room['project_city'] : $room['ship_city'];
$ship_state = empty(trim($room['ship_state'])) ? $room['project_state'] : $room['ship_state'];
$ship_zip = empty(trim($room['ship_zip'])) ? $room['project_zip'] : $room['ship_zip'];
?>
<form id="batch_details" method="post" action="#">
  <div class="container-fluid pricing_table_format m-t-10">
    <div class="row">
      <div class="col-md-4 gray_bg" style="border-radius:.25rem;border:1px solid #000;padding-bottom:25px;">
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
            <td>Dealer PO:</td>
            <td colspan="2"><input type="text" class="form-control border_thin_bottom" id="room_name" name="room_name" placeholder="Room Name" value="<?php echo $room['room_name']; ?>"></td>
          </tr>
          <tr>
            <td>Billing Type:</td>
            <td colspan="2"><strong><?php echo $dealer['account_type'] === 'R' ? 'Retail' : 'Distribution'; ?></strong></td>
          </tr>
          <tr>
            <td>Order Type:</td>
            <td><?php echo getSelect('product_type'); ?></td>
            <td id="product_type_cost" class="pricing_value"></td>
          </tr>
          <tr>
            <td><span id="leadTimeDef">Lead Time:</span></td>
            <td><?php echo getSelect('days_to_ship'); ?></td>
            <td class="pricing_value"></td>
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
            <td class="pricing_value"></td>
          </tr>
          <tr rowspan="3">
            <td style="vertical-align:top !important;">Ship To:</td>
            <td colspan="2">
              <input type="text" style="width:75%;" class="static_width align_left border_thin_bottom" placeholder="Name" name="ship_to_name" value="<?php echo $room['ship_name']; ?>"><br />
              <input type="text" style="width:75%;" class="static_width align_left border_thin_bottom" placeholder="Address" name="ship_to_address" value="<?php echo $ship_addr; ?>"><br />
              <input type="text" style="width:50%;" class="static_width align_left border_thin_bottom" placeholder="City" name="ship_to_city" value="<?php echo $ship_city; ?>"> <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php echo $ship_state; ?>"> <input type="text" style="width:51px;" class="static_width align_left border_thin_bottom" placeholder="ZIP" name="ship_to_zip" value="<?php echo $ship_zip; ?>">
            </td>
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
        <!--</editor-fold>-->
      </div>

      <div class="col-sm-3">
        <table width="100%">
          <tr><th>&nbsp;Notes</th></tr>
          <tr><td class="gray_bg">&nbsp;Delivery Notes:</td></tr>
          <tr><td id="delivery_notes" style="border:none;"><textarea name="delivery_notes" maxlength="280" class="static_width pricing_textbox"><?php echo $note_arr['room_note_delivery']['note']; ?></textarea></td></tr>
        </table>

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

        <input type="hidden" name="delivery_notes_id" value="<?php echo $note_arr['room_note_delivery']['id']; ?>" />
      </div>
    </div>
  </div>
</form>

<script>
$(function() {
  if($("#ship_via").val() === '4') {
    let ship_info = $("input[name='ship_to_name']").parent().parent();

    ship_info.hide();
    ship_info.next("tr").hide();
    ship_info.next("tr").next("tr").hide();
  }

  //<editor-fold desc="Auto-delivery note height">
  let tx_delnotes = $("textarea[name='delivery_notes']");
  let delheight = (tx_delnotes.prop('scrollHeight') < 180) ? 180 : tx_delnotes.prop('scrollHeight');
  tx_delnotes.height(delheight);
  //</editor-fold>

  <?php if($room['order_status'] === '$') { ?>
    pricingFunction.disableInput();
  <?php }?>
});
</script>
