<?php
require '../../../../includes/header_start.php';

$room_id = sanitizeInput($_REQUEST['room_id']);

$vin_schema = getVINSchema();

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '{$room['so_parent']}'");
$so = $so_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$so['dealer_code']}'");
$dealer = $dealer_qry->fetch_assoc();
?>
<form id="cabinet_specifications" method="post" action="#">
  <div class="container-fluid pricing_table_format">
    <div class="row">
      <div class="col-md-4 gray_bg" style="border-radius:.25rem;border:1px solid #000;padding-bottom:25px;">
        <!--<editor-fold desc="Global Details (Left Side)">-->
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
              <input type="text" style="width:75%;" class="static_width align_left border_thin_bottom" placeholder="Address" name="ship_to_address" value="<?php echo $room['project_addr']; ?>"><br />
              <input type="text" style="width:50%;" class="static_width align_left border_thin_bottom" placeholder="City" name="ship_to_city" value="<?php echo $room['project_city']; ?>"> <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php echo $room['project_state']; ?>"> <input type="text" style="width:51px;" class="static_width align_left border_thin_bottom" placeholder="ZIP" name="ship_to_zip" value="<?php echo $room['project_zip']; ?>">
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td colspan="2"><input type="checkbox" value="1" name="multi_room_ship" id="multi_room_ship" <?php echo $room['multi_room_ship'] ? 'checked' : null; ?>> <label for="multi_room_ship">Multi-room shipping</label></td>
          </tr>
          <tr>
            <?php
            $shipZone = !empty($room['ship_zip']) ? $room['ship_zip'] : $dealer['shipping_zip'];
            $ship_zone_info = calcShipZone($shipZone);

            if($room['ship_cost'] === null) {
              $ship_cost = (bool)$room['multi_room_ship'] ? 0 : $ship_zone_info['cost'];
            } else {
              $ship_cost = (bool)$room['multi_room_ship'] ? 0 : $room['ship_cost'];
            }

            setlocale(LC_MONETARY, 'en_US');

            $ship_cost_formatted = number_format($ship_cost, 2);
            ?>

            <td>Shipping Zone:</td>
            <td><strong><?php echo $ship_zone_info['zone']; ?></strong><i style="font-size:1.25em;float:right;" class="fa fa-pencil-square no-print cursor-hand" id="overrideShipCost" title="Override Ship Cost"></td>
            <td id="shipping_cost" class="pricing_value" data-cost="<?php echo $ship_cost; ?>"></td>
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

        <input type="hidden" name="delivery_notes_id" value="<?php echo $note_arr['room_note_delivery']['id']; ?>" />
      </div>
    </div>
  </div>
</form>