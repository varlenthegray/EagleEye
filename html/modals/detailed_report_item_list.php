<?php
require_once '../../includes/header_start.php';

//outputPHPErrs();

$room_id = sanitizeInput($_REQUEST['room_id']);

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
$room = $room_qry->fetch_assoc();
?>

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