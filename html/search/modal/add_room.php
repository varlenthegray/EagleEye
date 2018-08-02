<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 8/1/2018
 * Time: 2:08 PM
 */
require '../../../includes/header_start.php';

//outputPHPErrs();

$vin_qry = $dbconn->query("SELECT * FROM vin_schema ORDER BY segment ASC, case `group` when 'Custom' then 1 when 'Other' then 2 else 3 end, `group` ASC,
 FIELD(`value`, 'Custom', 'Other', 'No', 'None') DESC");

while($vin = $vin_qry->fetch_assoc()) {
  $vin_schema[$vin['segment']][] = $vin;
}

$so_num = sanitizeInput($_REQUEST['so_num']);

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' ORDER BY room, iteration ASC LIMIT 0, 1;");
$room = $room_qry->fetch_assoc();

$result_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '{$room['so_parent']}'");
$result = $result_qry->fetch_assoc();

// run functions for heredoc output
$product_type = displayVINOpts('product_type');
$order_status = displayVINOpts('order_status', null, 'modalAddRoomOrderStatus');
$days_to_ship = displayVINOpts('days_to_ship', null, 'modalAddRoomDTS');
$room_type = displayVINOpts('room_type');
// end of function run for heredoc

// days to ship calculation info
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

$dd_value = !empty($room['delivery_date']) ? date('m/d/Y', $room['delivery_date']) : '';
// end days to ship calculation info

$letter = 'A';
$blacklist = ['I','O'];
$letter_series = [];
$letter_out = '';

$blacklist_qry = $dbconn->query("SELECT DISTINCT(room) FROM rooms WHERE so_parent = '$so_num' ORDER BY room, iteration");

if($blacklist_qry->num_rows > 0) {
  while($blacklist_result = $blacklist_qry->fetch_assoc()) {
    if(!in_array($blacklist_result['room'], $blacklist, true)) {
      $blacklist[] = $blacklist_result['room'];
    }
  }
}

for($i = 1; $i <= 26; $i++) {
  $next_letter = $letter++;

  if(!in_array($next_letter, $blacklist, true)) {
    $letter_series[] = $next_letter;
  }
}

$first_selected = 'selected="selected"';

foreach($letter_series as $letter) {
  $letter_out .= "<option value='$letter' $first_selected>$letter</option>";

  $first_selected = null;
}

echo <<<HEREDOC
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title">Add New Room - $so_num</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <form id="modalAddRoomData" action="#">
                <table style="width:50%;margin:0 auto;">
                  <colgroup>
                    <col width="30%">
                    <col width="70%">
                  </colgroup>
                  <tr>
                    <td><label for="room">Room</label></td>
                    <td>
                      <select class="form-control" name="room_letter" id="room_letter" style="float:left;width:19%;padding:.45rem .25rem;">$letter_out</select>
                      <input type="text" class="form-control" id="room_name" name="room_name" placeholder="Room Name" autocomplete="off" style="float:left;width:76%;margin-left:5px;">
                    </td>
                  </tr>
                  <tr>
                    <td><label for="iteration">Iteration</label></td>
                    <td><input type="text" class="form-control" id="iteration" name="iteration" placeholder="Iteration" value="1.01" readonly></td>
                  </tr>
                  <tr>
                    <td><label for="product_type">Product Type</label></td>
                    <td>$product_type</td>
                  </tr>
                  <tr>
                    <td><label for="order_status">Order Status</label></td>
                    <td>$order_status</td>
                  </tr>
                  <tr>
                    <td><label for="days_to_ship">Days to Ship</label></td>
                    <td>$days_to_ship</td>
                  </tr>
                  <tr>
                    <td>Room Type</td>
                    <td>$room_type</td>
                  </tr>
                </table>
                
                <input type="hidden" name="so_num" 
              </form>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary waves-effect waves-light" id="modalAddRoomCreate">Create</button>
        </div>
      </div>
    </div>

    <script>
      $(".delivery_date").datepicker();
      $("#modalAddRoomOrderStatus").find(".selected").html("Quote").parent().find("#order_status").val("#");  // sets the default order status to quote
    </script>
HEREDOC;

