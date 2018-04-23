<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$unique_id = sanitizeInput($_REQUEST['unique_id']);
$user_id = sanitizeInput($_REQUEST['user_id']);
$indexHeir = $_REQUEST['indexHeir'];
$title = $_REQUEST['title'];
?>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title"><?php echo "$indexHeir: $title"; ?></h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12 opl_notes_section">
          <?php
          $row_qry = $dbconn->query("SELECT ori.note, ori.timestamp, u.name FROM opl_row_info ori LEFT JOIN user u on ori.user_id = u.id WHERE unique_id = '$unique_id' ORDER BY timestamp DESC");

          $prev_date = null;

          if($row_qry->num_rows > 0) {
            while($row = $row_qry->fetch_assoc()) {
              $cur_date = date(DATE_DEFAULT, $row['timestamp']);

              if($prev_date !== $cur_date) {
                echo "</section><section class='date_block'><span class='date'>$cur_date</span>";
                $prev_date = $cur_date;
              }

              $time = date(TIME_ONLY, $row['timestamp']);

              $name_exploded = explode(' ', $row['name']);

              $initials = null;

              foreach($name_exploded AS $name_part) {
                $initials .= substr($name_part, 0, 1);
              }

              $note = str_replace("  ", "&nbsp;&nbsp;", nl2br($row['note']));

              // TODO: Set this up in classes
              echo "<div class='opl_note'><div style='float:left;'>$time $initials: </div><div style='float:left;padding-left:5px;'>$note</div><div class='clearfix'></div></div>";
            }

            echo '</section>';
          } else {
            echo 'No notes logged at this time.';
          }
          ?>
        </div>
      </div>

      <div class="row" style="margin-top:5px;">
        <div class="col-md-12">
          <textarea class="form-control" placeholder="New Notes..." id="oplTaskNewNotes"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-success waves-effect" data-dismiss="modal" style="display:none;" data-unique-id="<?php echo $unique_id; ?>" id="oplTaskInfoSave">Save</button>
      <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">Close</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->