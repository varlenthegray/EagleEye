<?php
require '../../includes/header_start.php';

$room_id = sanitizeInput($_REQUEST['room_id']);
$so_id = sanitizeInput($_REQUEST['so_id']);

$quote_qry = $dbconn->query("SELECT so.id AS soID, r.id AS rID, r.*, o.*, so.* FROM rooms r LEFT JOIN operations o ON r.sales_bracket = o.id LEFT JOIN sales_order so ON r.so_parent = so.so_num WHERE r.id = '$room_id';");
$quote = $quote_qry->fetch_assoc();

if($_REQUEST['type'] === 'quote') {
  $header_type = "Quote Request";
}
?>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title"><?php echo "{$quote['so_parent']}-{$quote['room']}{$quote['iteration']}-{$quote['dealer_code']}_{$quote['room_name']} ($header_type)"; ?></h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <h4>SO Notes</h4>

          <div style="max-height:25vh;overflow-y:auto;border:1px solid #CCC;border-radius:4px 0 0 4px;">
            <?php
            $so_note_qry = $dbconn->query("SELECT * FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'so_inquiry' AND type_id = '$so_id' ORDER BY timestamp DESC;");

            if($so_note_qry->num_rows > 0) {
              while($so_note = $so_note_qry->fetch_assoc()) {
                $name = explode(" ", $so_note['name']);
                $first_initial = substr($name[0], 0, 1);
                $last_initial = substr($name[1], 0, 1);

                $time = date(DATE_DEFAULT, $so_note['timestamp']);

                echo "$time {$first_initial}{$last_initial}: {$so_note['note']}<br /><br />";
              }
            } else {
              echo "None logged.";
            }
            ?>
          </div>
        </div>
      </div>

      <div class="row" style="margin-top:20px;">
        <div class="col-md-12">
          <h4>Room Notes</h4>

          <div style="max-height:25vh;overflow-y:auto;border:1px solid #CCC;border-radius:4px 0 0 4px;">
            <?php
            $room_note_qry = $dbconn->query("SELECT * FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'room_note' AND type_id = '{$quote['rID']}' ORDER BY timestamp DESC;");

            if($room_note_qry->num_rows > 0) {
              while($room_note = $room_note_qry->fetch_assoc()) {
                $name = explode(" ", $room_note['name']);
                $first_initial = substr($name[0], 0, 1);
                $last_initial = substr($name[1], 0, 1);

                $time = date(DATE_DEFAULT, $room_note['timestamp']);

                echo "$time {$first_initial}{$last_initial}: {$room_note['note']}<br /><br />";
              }
            } else {
              echo "None logged.";
            }
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">Close</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->