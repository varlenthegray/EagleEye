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
      <form id="accounting_notes" action="#">
        <div class="row">
          <div class="col-md-12">
            <?php if($bouncer->validate('view_accounting')) { ?>
              <tr>
                <td colspan="2">
                  <label class="c-input c-checkbox">Deposit Received <input type="checkbox" name="deposit_received" value="1" <?php echo ((bool)$room['payment_deposit']) ? 'checked' :null; ?>><span class="c-indicator"></span></label><br />
                  <label class="c-input c-checkbox">Prior to Loading: Distribution - Final Payment<br/><span style="margin-left:110px;">Retail - On Delivery/Payment</span> <input type="checkbox" name="ptl_del" value="1" <?php echo ((bool)$room['payment_del_ptl']) ? 'checked' :null; ?>><span class="c-indicator"></span></label><br />
                  <label class="c-input c-checkbox">Retail - Final Payment <input type="checkbox" name="final_payment" value="1" <?php echo ((bool)$room['payment_final']) ? 'checked' :null; ?>><span class="c-indicator"></span></label>
                </td>
              </tr>
              <tr style="height:10px;">
                <td colspan="2"></td>
              </tr>
            <?php } ?>

            <ul class="nav nav-tabs m-b-10 m-t-10" id="roomNotes" role="tablist">
              <li class="nav-item">
                <a class="nav-link active show" id="room-tab" data-toggle="tab" href="#room" role="tab" aria-controls="room" aria-expanded="true" aria-selected="true">Room Notes</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="so-tab" data-toggle="tab" href="#so" role="tab" aria-controls="so" aria-selected="false">SO Notes</a>
              </li>
            </ul>
            <div class="tab-content" id="roomNotesContent">
              <div role="tabpanel" class="tab-pane fade in active show" id="room" aria-labelledby="room-tab">
                <!--<editor-fold desc="Room Notes">-->
                <div class="col-md-6">
                  <textarea class="form-control" name="room_notes" id="room_notes" placeholder="Notes" style="width:100%;height:33.4vh;"></textarea>

                  <?php if(!empty($_SESSION['userInfo'])) { ?>
                    <input type="text" name="room_inquiry_followup_date" id="room_inquiry_followup_date" class="form-control" placeholder="Followup On" style="width:30%;float:left;">
                    <label for="room_inquiry_requested_of" style="float:left;padding:4px;"> by </label>
                    <select name="room_inquiry_requested_of" id="room_inquiry_requested_of" class="form-control" style="width:62%;float:right;">
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
                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">Room Notes</h5> <?php if($bouncer->validate('view_audit_log')) { ?><div class="pull-right"><input type="checkbox" class="ignoreSaveAlert" id="display_log" /> <label for="display_log">Show Audit Log</label></div><?php } ?></td>
                      </tr>
                      <tr style="height:5px;"><td colspan="2"></td></tr>
                      <?php
                      if((bool)$_SESSION['userInfo']['dealer']) {
                        $dealer = strtolower($_SESSION['userInfo']['dealer_code']);
                        $where = "AND user.username LIKE '$dealer%'";
                      } else {
                        $where = null;
                      }

                      $room_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'room_note' OR note_type = 'room_note_log') AND notes.type_id = '{$room['id']}' $where ORDER BY notes.timestamp DESC;");

                      while($room_inquiry = $room_inquiry_qry->fetch_assoc()) {
                        $inquiry_replies = null;

                        $time = date(DATE_TIME_ABBRV, $room_inquiry['NTimestamp']);

                        if(!empty($room_inquiry['followup_time'])) {
                          $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$room_inquiry['user_to']}");
                          $followup_usr = $followup_usr_qry->fetch_assoc();

                          $followup_time = date(DATE_TIME_ABBRV, $room_inquiry['followup_time']);

                          $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                        } else {
                          $followup = null;
                        }

                        $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$room_inquiry['nID']}' ORDER BY timestamp DESC");

                        if($inquiry_reply_qry->num_rows > 0) {
                          while($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                            $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                            $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                          }
                        } else {
                          $inquiry_replies = null;
                        }

                        $notes = str_replace('  ', '&nbsp;&nbsp;', $room_inquiry['note']);
                        //$notes = $room_inquiry['note'];
                        $notes = nl2br($notes);

                        echo "<tr style='height:5px;'><td colspan='2'></td></tr>";

                        $room_note_log = ($room_inquiry['note_type'] === 'room_note_log') ? 'room_note_log' : null;

                        echo "<tr class='$room_note_log'>";
                        echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$room_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                        echo "  <td>$notes -- <small><em>{$room_inquiry['name']} on $time $followup</em></small><div><button type='button' class='btn waves-effect btn-primary post_to_cal'>Post to Calendar</button></div></td>";
                        echo '</tr>';

                        echo "<tr id='inquiry_reply_line_{$room_inquiry['nID']}' style='display:none;'>";
                        echo "<td colspan='2'>
                              <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$room_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                              <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='r_{$room_inquiry['nID']}_submit'>Reply</button>
                          </td>";
                        echo '</tr>';

                        echo $inquiry_replies;

                        echo "<tr class='$room_note_log' style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                      }
                      ?>
                      <tr style="height:5px;"><td colspan="2"></td></tr>
                    </table>
                  </div>
                </div>
                <!--</editor-fold>-->
              </div>
              <div class="tab-pane fade" id="so" role="tabpanel" aria-labelledby="so-tab">
                <!--<editor-fold desc="SO Notes">-->
                <div class="col-md-6">
                  <textarea class="form-control" name="inquiry" id="inquiry" placeholder="New Inquiry/Note" style="width:100%;height:33.4vh;"></textarea>
                  <input type="text" name="inquiry_followup_date" id="inquiry_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                  <label for="inquiry_requested_of" style="float:left;padding:4px;"> requested of </label>
                  <select name="inquiry_requested_of" id="inquiry_requested_of" class="form-control" style="width:45%;float:left;">
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
                        <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">SO Notes</h5></td>
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
                        echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$so_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                        echo "  <td>$notes -- <small><em>{$so_inquiry['name']} on $time $followup</em></small></td>";
                        echo '</tr>';

                        echo "<tr id='inquiry_reply_line_{$so_inquiry['nID']}' style='display:none;'>";
                        echo "  <td colspan='2'>
                                      <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$so_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                                      <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='{$so_inquiry['nID']}'>Reply</button>
                                  </td>";
                        echo '</tr>';

                        echo $inquiry_replies;

                        echo "<tr style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                      }
                      ?>
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