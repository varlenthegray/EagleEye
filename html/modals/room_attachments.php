<?php
require_once '../../includes/header_start.php';

//outputPHPErrs();

$room_id = sanitizeInput($_REQUEST['room_id']);

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
$room = $room_qry->fetch_assoc();
?>

<form id="room_attachments">
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
</form>