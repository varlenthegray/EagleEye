<?php
require '../../includes/header_start.php';

//outputPHPErrs();

$room_id = sanitizeInput($_REQUEST['room_id']);

$vin_schema = getVINSchema();

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

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '{$room['so_parent']}'");
$so = $so_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$so['dealer_code']}'");
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

<style>
  #roomTabView {
    z-index: 2;
    top: 38px;
    background-color: #FFF;
  }
</style>

<script>
  <?php echo $submit_disabled !== null ? 'var already_submitted = true;' : 'var already_submitted = false;'; ?>
</script>

<div class="container-fluid" style="width:100%;">
  <div class="row sticky no-print" style="background-color:#FFF;z-index:2;top:0;padding:4px;">
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
      <button class="btn waves-effect btn-secondary" title="Recalculate Pricing" id="catalog_recalculate"> <i class="fa fa-retweet fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Download ORD File" id="dl_ord_file"><i class="fa fa-file-text-o fa-2x"></i></button>
      <button class="btn waves-effect btn-secondary" style="display:none;" title="Override Production Lock" id="production_lock"><i class="fa fa-lock fa-2x"></i></button>
    </div>

    <div class="col-md-6 text-md-right"><h4 style="margin:0;padding:0;"><?php echo "{$room['so_parent']}{$room['room']}-{$room['iteration']} $submitted"; ?></h4></div>
  </div>

  <div class="row">
    <div class="col-md-12 m-t-10">
      <ul class="nav nav-tabs sticky" id="roomTabView" role="tablist">
        <li class="nav-item ">
          <a class="nav-link tab-ajax active" data-ajax="/html/pricing/ajax/tabs/room_notes.php?room_id=<?php echo $room_id; ?>" data-toggle="tab"
             id="room-notes-tab" data-toggle="tab" href="#roomNotes" role="tab" aria-controls="room-notes"><i class="fa fa-wpforms m-r-5"></i> Notes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link tab-ajax" data-ajax="/html/pricing/ajax/tabs/room_details.php?room_id=<?php echo $room_id; ?>" data-toggle="tab"
             id="room-details-tab" href="#roomDetails" role="tab" aria-controls="room-details" aria-expanded="true"><i class="fa fa-home m-r-5"></i> Room Details</a>
        </li>
        <li class="nav-item">
          <a class="nav-link tab-ajax" data-ajax="/html/pricing/ajax/tabs/global_cabinet_details.php?room_id=<?php echo $room_id; ?>" data-toggle="tab"
             id="room-cabinet-details-tab" href="#roomCabinetDetails" role="tab" aria-controls="room-cabinet-details"><i class="fa fa-inbox m-r-5"></i> Cabinet Details</a>
        </li>
        <li class="nav-item ">
          <a class="nav-link tab-ajax" data-ajax="/html/pricing/ajax/tabs/item_list.php?room_id=<?php echo $room_id; ?>" data-toggle="tab"
             id="room-item-list-tab" data-toggle="tab" href="#roomItemList" role="tab" aria-controls="room-item-list"><i class="fa fa-shopping-bag m-r-5"></i> Item List</a>
        </li>
        <li class="nav-item ">
          <a class="nav-link tab-ajax" data-ajax="/html/pricing/ajax/tabs/bracket_manager.php?so_num=<?php echo $room['so_parent']; ?>" data-toggle="tab"
             id="room-bracket-management-tab" data-toggle="tab" href="#roomBracketManagement" role="tab" aria-controls="room-bracket-management"><i class="fa fa-code-fork m-r-5"></i> Bracket Details</a>
        </li>
      </ul>
      <div class="tab-content" id="roomTabViewContent">
        <div class="tab-pane fade in active show" id="roomNotes" role="tabpanel" aria-labelledby="room-notes-tab"></div>
        <div class="tab-pane fade" id="roomDetails" role="tabpanel" aria-labelledby="room-details-tab"></div>
        <div class="tab-pane fade" id="roomCabinetDetails" role="tabpanel" aria-labelledby="room-cabinet-details-tab"></div>
        <div class="tab-pane fade" id="roomItemList" role="tabpanel" aria-labelledby="room-item-list-tab"></div>
        <div class="tab-pane fade" id="roomBracketManagement" role="tabpanel" aria-labelledby="room-bracket-management-tab"></div>
      </div>
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
  $(function() {
    crmBatch.index.init();

    setTimeout(function() {
      $("#room-notes-tab").trigger("click");
    }, 150);
  });
</script>