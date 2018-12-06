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

<div class="container-fluid">
  <div class="row">
    <!--<editor-fold desc="Notes/Accounting">-->
    <div class="col-md-6 sticky no-print" style="top:38px;">
      <form id="batch_notes" action="#">
        <div class="row">
          <div class="col-md-12">
            <ul class="nav nav-tabs m-b-10 m-t-10" id="roomNotes" role="tablist">
              <li class="nav-item">
                <a class="nav-link active show" id="so-tab" data-toggle="tab" href="#so" role="tab" aria-controls="so" aria-selected="false">Project Notes</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="room-tab" data-toggle="tab" href="#room" role="tab" aria-controls="room" aria-expanded="true" aria-selected="true">Batch Notes</a>
              </li>
            </ul>
            <div class="tab-content" id="roomNotesContent">
              <div role="tabpanel" class="tab-pane fade in active show" id="so"  aria-labelledby="so-tab">
                <!--<editor-fold desc="SO Notes">-->
                <div class="col-md-6">
                  <textarea class="form-control" name="project_note_input" id="project_note_input" placeholder="Notes" style="width:100%;height:33.4vh;"></textarea>
                  <input type="text" name="project_followup_date" id="project_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                  <label for="project_followup_requested_of" style="float:left;padding:4px;"> requested of </label>
                  <select name="project_followup_requested_of" id="project_followup_requested_of" class="form-control" style="width:45%;float:left;">
                    <option value="null" selected disabled></option>
                    <?php
                    $user_qry = $dbconn->query('SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC');

                    while ($user = $user_qry->fetch_assoc()) {
                      echo "<option value='{$user['id']}'>{$user['name']}</option>";
                    }
                    ?>
                  </select>
                </div>

                <div class="col-md-6">
                  <div class="so_note_box">
                    <table class="table table-custom-nb table-v-top" width="100%">
                      <tr>
                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">Project Notes</h5></td>
                      </tr>
                      <tr style="height:5px;"><td colspan="2"></td></tr>
                      <?php
                      if((bool)$_SESSION['userInfo']['dealer']) {
                        $dealer = strtolower($_SESSION['userInfo']['dealer_code']);
                        $where = "AND user.username LIKE '$dealer%'";
                      } else {
                        $where = null;
                      }

                      $so_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'so_inquiry' OR note_type = 'so_note_log') AND notes.type_id = '{$so['id']}' $where ORDER BY notes.timestamp DESC;");

                      while ($so_inquiry = $so_inquiry_qry->fetch_assoc()) {
                        $inquiry_replies = null;

                        $time = date(DATE_TIME_ABBRV, $so_inquiry['NTimestamp']);

                        if (!empty($so_inquiry['followup_time'])) {
                          $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$so_inquiry['user_to']}");
                          $followup_usr = $followup_usr_qry->fetch_assoc();

                          $followup_time = date(DATE_TIME_ABBRV, $so_inquiry['followup_time']);

                          $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                        } else {
                          $followup = null;
                        }

                        $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$so_inquiry['nID']}' ORDER BY timestamp DESC");

                        if ($inquiry_reply_qry->num_rows > 0) {
                          while ($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                            $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                            $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                          }
                        } else {
                          $inquiry_replies = null;
                        }

                        $notes = str_replace('  ', '&nbsp;&nbsp;', $so_inquiry['note']);

                        echo '<tr>';
//                        echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$so_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                        echo "  <td>$notes -- <small><em>{$so_inquiry['name']} on $time $followup</em></small></td>";
                        echo '</tr>';

                        /*echo "<tr id='inquiry_reply_line_{$so_inquiry['nID']}' style='display:none;'>";
                        echo "  <td colspan='2'>
                                      <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$so_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                                      <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='{$so_inquiry['nID']}'>Reply</button>
                                  </td>";
                        echo '</tr>';

                        echo $inquiry_replies;*/

                        echo "<tr style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                      }
                      ?>
                    </table>
                  </div>
                </div>
                <!--</editor-fold>-->
              </div>
              <div role="tabpanel" class="tab-pane fade" id="room" aria-labelledby="room-tab">
                <!--<editor-fold desc="Room Notes">-->
                <div class="col-md-6">
                  <textarea class="form-control" name="batch_note_input" id="batch_note_input" placeholder="Notes" style="width:100%;height:33.4vh;"></textarea>

                  <?php if(!empty($_SESSION['userInfo'])) { ?>
                    <input type="text" name="batch_inquiry_followup_date" id="batch_inquiry_followup_date" class="form-control" placeholder="Followup On" style="width:30%;float:left;">
                    <label for="batch_inquiry_requested_of" style="float:left;padding:4px;"> by </label>
                    <select name="batch_inquiry_requested_of" id="batch_inquiry_requested_of" class="form-control" style="width:62%;float:right;">
                      <option value="null" selected disabled></option>
                      <?php
                      $user_qry = $dbconn->query('SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC');

                      while($user = $user_qry->fetch_assoc()) {
                        echo "<option value='{$user['id']}'>{$user['name']}</option>";
                      }
                      ?>
                    </select>
                  <?php } ?>
                </div>

                <div class="col-md-6">
                  <div class="room_note_box">
                    <table class="table table-custom-nb table-v-top">
                      <tr>
                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">Batch Notes</h5> <?php if($bouncer->validate('view_audit_log')) { ?><div class="pull-right"><input type="checkbox" class="ignoreSaveAlert" id="display_log" /> <label for="display_log">Show Audit Log</label></div><?php } ?></td>
                      </tr>
                      <tr style="height:5px;"><td colspan="2"></td></tr>
                      <?php
                      //<editor-fold desc="Dealer check">
                      if((bool)$_SESSION['userInfo']['dealer']) {
                        $dealer = strtolower($_SESSION['userInfo']['dealer_code']);
                        $where = "AND user.username LIKE '$dealer%'";
                      } else {
                        $where = null;
                      }
                      //</editor-fold>

                      $room_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'room_note' OR note_type = 'room_note_log') AND notes.type_id = '{$room['id']}' $where ORDER BY notes.timestamp DESC;");

                      while($room_inquiry = $room_inquiry_qry->fetch_assoc()) {
                        $inquiry_replies = null;

                        // get the time the note was logged and format it
                        $time = date(DATE_TIME_ABBRV, $room_inquiry['NTimestamp']);

                        //<editor-fold desc="Followup Note">
                        // if there is a followup time
                        if(!empty($room_inquiry['followup_time'])) {
                          // find the followup user information
                          $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$room_inquiry['user_to']}");
                          $followup_usr = $followup_usr_qry->fetch_assoc();

                          // format the followup date/time
                          $followup_time = date(DATE_TIME_ABBRV, $room_inquiry['followup_time']);

                          // add the followup information
                          $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                        } else {
                          // otherwise, there is no followup information
                          $followup = null;
                        }
                        //</editor-fold>

                        //<editor-fold desc="Replies to the notes, deprecated">
                        /*$inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$room_inquiry['nID']}' ORDER BY timestamp DESC");

                        if($inquiry_reply_qry->num_rows > 0) {
                          while($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                            $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                            $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                          }
                        } else {
                          $inquiry_replies = null;
                        }*/
                        //</editor-fold>

                        $notes = str_replace('  ', '&nbsp;&nbsp;', $room_inquiry['note']);
                        $notes = nl2br($notes);

                        echo "<tr style='height:5px;'><td colspan='2'></td></tr>";

                        $room_note_log = ($room_inquiry['note_type'] === 'room_note_log') ? 'room_note_log' : null;

                        echo "<tr class='$room_note_log'>";
//                        echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$room_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                        echo "  <td>$notes -- <small><em>{$room_inquiry['name']} on $time $followup</em></small><div><button type='button' class='btn waves-effect btn-primary post_to_cal'>Post to Calendar</button></div></td>";
                        echo '</tr>';

                        //<editor-fold desc="Reply textbox, deprecated">
                        /*echo "<tr id='inquiry_reply_line_{$room_inquiry['nID']}' style='display:none;'>";
                        echo "<td colspan='2'>
                              <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$room_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                              <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='r_{$room_inquiry['nID']}_submit'>Reply</button>
                          </td>";
                        echo '</tr>';

                        echo $inquiry_replies;*/
                        //</editor-fold>

                        echo "<tr class='$room_note_log' style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                      }
                      ?>
                      <tr style="height:5px;"><td colspan="2"></td></tr>
                    </table>
                  </div>
                </div>
                <!--</editor-fold>-->
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <!--</editor-fold>-->
  </div>
</div>

<script>
  $(function() {
    $(".room_note_log").hide();
  })
</script>