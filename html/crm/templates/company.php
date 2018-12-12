<?php
//require '../../../includes/header_start.php';

//outputPHPErrs();

$so_num = sanitizeInput($_REQUEST['so_num']);

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '$so_num'");
$so = $so_qry->fetch_assoc();
?>

<div class="container-fluid">
  <form id="form_so_<?php echo $so['so_num']; ?>">
    <div class="row">
      <div class="col-md-3">
        <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
          <tr>
            <td colspan="2"><u><b>Company</b></u></td>
          </tr>
          <tr>
            <td><label for="project_name">Name:</label></td>
            <td><input type="text" value="<?php echo $so['project_name']; ?>" name="project_name" class="c_input" placeholder="Company Name" id="project_name" /></td>
          </tr>
          <tr>
            <td><label for="project_addr">Address:</label></td>
            <td><input type="text" value="<?php echo $so['project_addr']; ?>" name="project_addr" class="c_input " placeholder="Company Address" id="project_addr" /></td>
          </tr>
          <tr>
            <td><label for="project_city">City:</label></td>
            <td><input type="text" value="<?php echo $so['project_city']; ?>" name="project_city" class="c_input" placeholder="Company City" id="project_city"></td>
          </tr>
          <tr>
            <td><label for="project_state">State:</label></td>
            <td><select class="c_input" id="project_state" name="project_state"><?php echo getStateOpts($so['project_state']); ?></select></td>
          </tr>
          <tr>
            <td><label for="project_zip">Zip:</label></td>
            <td><input type="text" value="<?php echo $so['project_zip']; ?>" name="project_zip" class="c_input" placeholder="Company Zip" id="project_zip"></td>
          </tr>
          <tr>
            <td><label for="project_landline">Landline:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="project_landline" class="c_input" placeholder="Company Landline" id="project_landline"></td>
          </tr>
          <tr>
            <td colspan="2"><b><u>Billing</u></b></td>
          </tr>
          <tr>
            <td><label for="ach_acct_1">ACH Account 1:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="ach_acct_1" autocomplete="no" class="c_input" placeholder="ACH Account Number" id="ach_acct_1"></td>
          </tr>
          <tr>
            <td><label for="ach_routing_1">ACH Routing 1:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="ach_routing_1" autocomplete="no" class="c_input" placeholder="ACH Routing Number" id="ach_routing_1"></td>
          </tr>
          <tr>
            <td><label for="ach_acct_2">ACH Account 2:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="ach_acct_2" autocomplete="no" class="c_input" placeholder="ACH Account Number" id="ach_acct_2"></td>
          </tr>
          <tr>
            <td><label for="ach_routing_2">ACH Routing 2:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="ach_routing_2" autocomplete="no" class="c_input" placeholder="ACH Routing Number" id="ach_routing_2"></td>
          </tr>
          <tr>
            <td><label for="cc_num_1">CC 1:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="16" name="cc_num_1" autocomplete="no" class="c_input" placeholder="CC Number" id="cc_num_1"></td>
          </tr>
          <tr>
            <td><label for="cc_exp_1">CC Exp 1:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="8" name="cc_exp_1" autocomplete="no" class="c_input" placeholder="CC Expiration" id="cc_exp_1"></td>
          </tr>
          <tr>
            <td><label for="cc_ccv_1">CC CCV 1:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="4" name="cc_ccv_1" autocomplete="no" class="c_input" placeholder="CC CCV" id="cc_ccv_1"></td>
          </tr>
          <tr>
            <td><label for="cc_num_2">CC 2:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="16" name="cc_num_2" autocomplete="no" class="c_input" placeholder="CC Number" id="cc_num_2"></td>
          </tr>
          <tr>
            <td><label for="cc_exp_2">CC Exp 2:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="8" name="cc_exp_2" autocomplete="no" class="c_input" placeholder="CC Expiration" id="cc_exp_2"></td>
          </tr>
          <tr>
            <td><label for="cc_ccv_2">CC CCV 2:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="4" name="cc_ccv_2" autocomplete="no" class="c_input" placeholder="CC CCV" id="cc_ccv_2"></td>
          </tr>
          <tr>
            <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
          </tr>
          <tr>
            <td colspan="2"></td>
          </tr>
          <tr>
            <td colspan="3"><h5>Contacts</h5></td>
          </tr>
          <?php
          $contact_dropdown = null;

          $contact_qry = $dbconn->query('SELECT c.id, c.first_name, c.last_name, c.company_name, c2.description FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id ORDER BY c2.description, c.first_name, c.last_name ASC');

          if($contact_qry->num_rows > 0) {
            $contact_dropdown = "<select class='c_input pull-left add_contact_id ignoreSaveAlert' name='add_contact' style='width:50%;margin-top:7px;'><option value=''>Select</option>";

            $last_group = null;

            while($contact = $contact_qry->fetch_assoc()) {
              if($contact['description'] !== $last_group) {
                $contact_dropdown .= "</optgroup><optgroup label='{$contact['description']}'>";
                $last_group = $contact['description'];
              }

              $name = !empty($contact['first_name']) ? "{$contact['first_name']} {$contact['last_name']}" : $contact['company_name'];

              $contact_dropdown .= "<option value='{$contact['id']}'>$name</option>";
            }

            $contact_dropdown .= '</optgroup></select>';
          }

          echo "<tr><td colspan='3'><div class='form-group'><label for='add_contact' class='pull-left' style='line-height:28px;padding-right:10px;'>Add Association</label> $contact_dropdown <button type='button' class='btn waves-effect waves-light btn-primary assign_contact_so' style='margin:2px 0 0 10px;'> <i class='zmdi zmdi-plus-circle'></i> </button></div></td></tr>";

          // displaying existing contact relationships
          $so_contacts_qry = $dbconn->query("SELECT c.*, c2.description FROM contact_associations soc LEFT JOIN contact c ON soc.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE so_id = '{$so['id']}' ORDER BY c.first_name, c.last_name ASC");

          if($so_contacts_qry->num_rows > 0) {
            while($so_contacts = $so_contacts_qry->fetch_assoc()) {
              $name = !empty($so_contacts['first_name']) ? "{$so_contacts['first_name']} {$so_contacts['last_name']}" : $so_contacts['company_name'];

              echo "<tr><td colspan='3'><button type='button' class='btn waves-effect waves-light btn-danger remove_assigned_contact_so' style='margin:2px 0;' data-id='{$so_contacts['id']}'> <i class='zmdi zmdi-minus-circle'></i> </button> <a href='#' class='get_customer_info' data-view-id='{$so_contacts['id']}''>$name ({$so_contacts['description']})</a></td></tr>";
            }
          } else {
            echo "<tr><td colspan='3'><strong>No Contacts</strong></td></tr>";
          }
          ?>
        </table>
      </div>

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
                    <div class="custom_note_box so_note_box">
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

    <div class="row">
      <div class="col-md-12">
        <button type="button" class="btn btn-primary waves-effect waves-light w-xs save_so" data-sonum="<?php echo $so['so_num']; ?>">Save</button>
      </div>
    </div>
  </form>
</div>

<script>
  nameOfUser = '<?php echo $_SESSION['userInfo']['name']; ?>';
</script>