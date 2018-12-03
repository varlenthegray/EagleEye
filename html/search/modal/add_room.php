<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 8/1/2018
 * Time: 2:08 PM
 */
require '../../../includes/header_start.php';

//outputPHPErrs();

$so_num = sanitizeInput($_REQUEST['so_num']);
$add_type = sanitizeInput($_REQUEST['addType']);
$room_id = sanitizeInput($_REQUEST['room_id']);

//<editor-fold desc="VIN Schema loading">
$vin_schema = getVINSchema();
//</editor-fold>

//<editor-fold desc="Functions for Heredoc Output">
$product_type = getSelect('product_type');
$order_status = getSelect('order_status', null, 'modalAddRoomOrderStatus');
$days_to_ship = getSelect('days_to_ship', null, 'modalAddRoomDTS');
//</editor-fold>

//<editor-fold desc="Room Lettering">
$letter = 'A';
$blacklist = ['I','O'];
$letter_series = [];
$letter_out = '';

//<editor-fold desc="Blacklist Room Letters">
$blacklist_qry = $dbconn->query("SELECT DISTINCT(room) FROM rooms WHERE so_parent = '$so_num' ORDER BY room");

if($blacklist_qry->num_rows > 0) {
  while($blacklist_result = $blacklist_qry->fetch_assoc()) {
    if(!in_array($blacklist_result['room'], $blacklist, true)) {
      $blacklist[] = $blacklist_result['room'];
    }
  }
}
//</editor-fold>

//<editor-fold desc="Building the Letter Series">
for($i = 1; $i <= 26; $i++) {
  $next_letter = $letter++;

  if(!in_array($next_letter, $blacklist, true)) {
    $letter_series[] = $next_letter;
  }
}
//</editor-fold>
//</editor-fold>

//<editor-fold desc="Determining iteration">
$next_iteration = 1.01;
$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");

if($room_qry->num_rows > 0) {
  $room = $room_qry->fetch_assoc();

  if($add_type === 'sequence') {
    $highest_seq_qry = $dbconn->query("SELECT MAX(iteration) as iteration FROM rooms WHERE so_parent = '{$room['so_parent']}' AND room = '{$room['room']}'");
    $highest_seq = $highest_seq_qry->fetch_assoc();
    $seq_iteration = $highest_seq['iteration'];

    $next_seq = (double)$seq_iteration + 1.00;

    $sequence = explode('.', $next_seq);

    $next_iteration = $sequence[0] . '.01';
  } elseif($add_type === 'iteration') {
    $cur_seq = explode('.', $room['iteration']);

    $highest_it_qry = $dbconn->query("SELECT MAX(iteration) as iteration FROM rooms WHERE so_parent = '{$room['so_parent']}' AND room = '{$room['room']}' AND iteration LIKE '{$cur_seq[0]}%'");
    $highest_it = $highest_it_qry->fetch_assoc();

    $iteration = $highest_it['iteration'];

    $next_iteration = (double)$iteration + 0.01;
  }
}
//</editor-fold>

//<editor-fold desc="Dropdown Setup">
if($add_type === 'room') {
  foreach($letter_series as $letter) {
    $letter_out .= "<option value='$letter'>$letter</option>";
  }
} else {
  $letter_out = "<option value='{$room['room']}'>{$room['room']}</option>";
}
//</editor-fold>

switch($add_type) {
  case 'room':
    $title = "Add New Room - $so_num";
    break;
  case 'sequence':
    $title = "Add New Sequence - $next_iteration";
    break;
  case 'iteration':
    $title = "Add New Iteration - $next_iteration";
    break;
  case 'default':
    $title = 'Error determining what is being added';
    break;
}

echo <<<HEREDOC
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title">$title</h4>
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
                    <td><input type="text" class="form-control" id="iteration" name="iteration" placeholder="Iteration" value="$next_iteration" readonly></td>
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
                </table>
                
                <input type="hidden" name="add_room_so_num" id="add_room_so_num" value="$so_num" />
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
      $("#order_status").val('#'); // sets the default order status to quote
      $("#product_type").val('C'); // sets the default product type
      $("#days_to_ship").val('G'); // sets the default days to ship to green
    </script>
HEREDOC;

