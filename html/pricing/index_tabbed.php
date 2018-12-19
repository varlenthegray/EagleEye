<?php
require '../../includes/header_start.php';

outputPHPErrs();

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

$room_qry = $dbconn->query("SELECT r.*, so.dealer_code FROM rooms r LEFT JOIN sales_order so on r.so_parent = so.so_num WHERE r.id = $room_id ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$room['dealer_code']}'");
$dealer = $dealer_qry->fetch_assoc();

//<editor-fold desc="Disable submit buttons (if submitted)">
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
//</editor-fold>

$shipZIP = !empty($room['ship_zip']) ? $room['ship_zip'] : $dealer['shipping_zip'];
$ship_zone_info = calcShipZone($shipZIP);

if($room['ship_cost'] === null) {
  $ship_cost = (bool)$room['multi_room_ship'] ? 0 : $ship_zone_info['cost'];
} else {
  $ship_cost = (bool)$room['multi_room_ship'] ? 0 : $room['ship_cost'];
}

$ship_cost = number_format($ship_cost, 2);

$room['individual_bracket_buildout'] = null;
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
  <div class="row sticky no-print" style="background-color:#FFF;z-index:3;top:0;padding:4px;">
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
             id="room-details-tab" href="#roomDetails" role="tab" aria-controls="room-details" aria-expanded="true"><i class="fa fa-home m-r-5"></i> Batch Details</a>
        </li>
        <li class="nav-item">
          <a class="nav-link tab-ajax" data-ajax="/html/pricing/ajax/tabs/global_cabinet_details.php?room_id=<?php echo $room_id; ?>" data-toggle="tab"
             id="room-cabinet-details-tab" href="#roomCabinetDetails" role="tab" aria-controls="room-cabinet-details"><i class="fa fa-inbox m-r-5"></i> Product Details</a>
        </li>
        <li class="nav-item ">
          <a class="nav-link tab-ajax" data-ajax="/html/pricing/ajax/tabs/item_list.php?room_id=<?php echo $room_id; ?>" data-toggle="tab"
             id="room-item-list-tab" data-toggle="tab" href="#roomItemList" role="tab" aria-controls="room-item-list"><i class="fa fa-shopping-bag m-r-5"></i> Item List</a>
        </li>
      </ul>
      <div class="tab-content" id="roomTabViewContent">
        <div class="tab-pane fade in active show" id="roomNotes" role="tabpanel" aria-labelledby="room-notes-tab"></div>
        <div class="tab-pane fade" id="roomDetails" role="tabpanel" aria-labelledby="room-details-tab"></div>
        <div class="tab-pane fade" id="roomCabinetDetails" role="tabpanel" aria-labelledby="room-cabinet-details-tab"></div>
        <div class="tab-pane fade" id="roomItemList" role="tabpanel" aria-labelledby="room-item-list-tab"></div>
      </div>
    </div>
  </div>
</div>

<div class='info-popup'></div>

<iframe id="dlORDfile" src="" style="display:none;visibility:hidden;"></iframe>

<script>
  pricingVars.nameOfUser = '<?php echo $_SESSION['userInfo']['name']; ?>';
  pricingVars.roomQry = JSON.parse('<?php $room['custom_vin_info'] = null; echo json_encode($room); ?>');
  pricingVars.vinInfo = JSON.parse('<?php echo strip_tags(json_encode($json_vin)); ?>');
  pricingVars.shipCost = <?php echo $ship_cost; ?>;

  $(function() {
    crmBatch.index.init();

    setTimeout(function() {
      $("#room-notes-tab").trigger("click");
    }, 150);
  });
</script>