<?php
require '../../../includes/header_start.php';

$so_num = sanitizeInput($_REQUEST['so_num']);

$vin_schema = getVINSchema();

$operations = []; // operation information

// get all operations
$op_qry = $dbconn->query('SELECT * FROM operations');

while($op = $op_qry->fetch_assoc()) {
  $operations[$op['id']] = $op;
}

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '$so_num'");
$so = $so_qry->fetch_assoc();
?>

<style>
  .assign_contact_so {
    padding: 6px;
  }
</style>

<div class="container-fluid">
  <form id="form_so_<?php echo $so['so_num']; ?>">
    <div class="row">
      <!--<editor-fold desc="Project information">-->
      <div class="col-md-3">
        <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
          <tr>
            <td><label for="dealer_code">Dealer:</label></td>
            <td>
              <select class="c_input" id="dealer_code" name="dealer_code">
                <?php
                $dealer_qry = $dbconn->query('SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id ORDER BY dealer_id ASC;');

                while ($dealer = $dealer_qry->fetch_assoc()) {
                  $selected = ($dealer['dealer_id'] === $so['dealer_code']) ? 'selected' : NULL;

                  $name = (empty($dealer['first_name']) && empty($dealer['last_name'])) ? $dealer['company_name'] : "{$dealer['first_name']} {$dealer['last_name']}";

                  echo "<option value='{$dealer['dealer_id']}' $selected>{$dealer['dealer_id']} ($name)</option>";
                }
                ?>
              </select>
            </td>
          </tr>
          <tr style="height: 5px;">
            <td colspan="2"></td>
          </tr>
          <tr>
            <td><label for="project_name">Project Name:</label></td>
            <td><input type="text" value="<?php echo $so['project_name']; ?>" name="project_name" class="c_input" placeholder="Project Name" id="project_name" /></td>
          </tr>
          <tr>
            <td><label for="project_addr">Project Address:</label></td>
            <td><input type="text" value="<?php echo $so['project_addr']; ?>" name="project_addr" class="c_input " placeholder="Project Address" id="project_addr" /></td>
          </tr>
          <tr>
            <td><label for="project_city">Project City:</label></td>
            <td><input type="text" value="<?php echo $so['project_city']; ?>" name="project_city" class="c_input" placeholder="Project City" id="project_city"></td>
          </tr>
          <tr>
            <td><label for="project_state">Project State:</label></td>
            <td><select class="c_input" id="project_state" name="project_state"><?php echo getStateOpts($so['project_state']); ?></select></td>
          </tr>
          <tr>
            <td><label for="project_zip">Project Zip:</label></td>
            <td><input type="text" value="<?php echo $so['project_zip']; ?>" name="project_zip" class="c_input" placeholder="Project Zip" id="project_zip"></td>
          </tr>
          <tr>
            <td><label for="project_landline">Project Landline:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="project_landline" class="c_input" placeholder="Project Landline" id="project_landline"></td>
          </tr>
          <tr>
            <td colspan="3" style="height:5px;"></td>
          </tr>
          <tr>
            <td><label for="secondary_addr">Shipping Address:</label></td>
            <td><input type="text" value="<?php echo $so['secondary_addr']; ?>" name="secondary_addr" class="c_input" placeholder="Secondary Address" id="secondary_addr"></td>
          </tr>
          <tr>
            <td><label for="secondary_city">Shipping City:</label></td>
            <td><input type="text" value="<?php echo $so['secondary_city']; ?>" name="secondary_city" class="c_input" placeholder="Secondary City" id="secondary_city"></td>
          </tr>
          <tr>
            <td><label for="secondary_state">Shipping State:</label></td>
            <td><select class="c_input" id="secondary_state" name="secondary_state"><?php echo getStateOpts($so['secondary_state']); ?></select></td>
          </tr>
          <tr>
            <td><label for="secondary_zip">Shipping Zip:</label></td>
            <td><input type="text" value="<?php echo $so['secondary_zip']; ?>" name="secondary_zip" class="c_input" placeholder="Secondary Zip" id="secondary_zip"></td>
          </tr>
          <tr>
            <td><label for="secondary_landline">Shipping Landline:</label></td>
            <td><input type="text" value="<?php echo $so['secondary_landline']; ?>" name="secondary_landline" class="c_input" placeholder="Secondary Landline" id="secondary_landline"></td>
          </tr>
          <tr>
            <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
          </tr>
        </table>

        <div class="contact-box">
          <h5>Contacts</h5>

          <?php
          function getContactCard($so) {
            return <<<HEREDOC
<div class="contact-card">
  <div style="float:right;"><i class="fa fa-minus-square danger-color cursor-hand remove_assigned_contact_so" data-id="{$so['id']}" title="Remove Contact"></i></div>
  <h5><a href="#">{$so['first_name']} {$so['last_name']}</a></h5>
  <h6>{TITLE}</h6>

  <p>{$so['cell']}<br>{$so['email']}</p>
</div>
HEREDOC;
          }
          
          $contact_dropdown = null;

          $contact_qry = $dbconn->query('SELECT c.id, c.first_name, c.last_name, c.company_name, c2.description FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id ORDER BY c2.description, c.first_name, c.last_name ASC');

          if($contact_qry->num_rows > 0) {
            $contact_dropdown = "<select class='c_input pull-left add_contact_id ignoreSaveAlert' name='add_contact'><option value=''>Select</option>";

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

          echo "<table class='m-b-10'>
                  <tr>
                    <td><label for='add_contact'>Add Association</label></td>
                    <td>$contact_dropdown</td>
                    <td><i class='fa fa-plus-square assign_contact_so primary-color cursor-hand'></i></td>
                  </tr>
                </table>";

          // displaying existing contact relationships
          $so_contacts_qry = $dbconn->query("SELECT c.*, c2.description, a.associated_as FROM contact_associations a LEFT JOIN contact c ON a.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE type_id = '{$so['id']}' ORDER BY c.first_name, c.last_name ASC");

          if($so_contacts_qry->num_rows > 0) {
            while($so_contacts = $so_contacts_qry->fetch_assoc()) {
              echo getContactCard($so_contacts);
            }
          } else {
            echo '<strong>No Contacts</strong>';
          }
          ?>
        </div>
      </div>
      <!--</editor-fold>-->

      <!--<editor-fold desc="Notes">-->
      <div class="col-md-3 no-print">
        <div class="row">
          <div class="col-md-12">
            <textarea class="form-control" name="inquiry" id="inquiry" placeholder="New Inquiry/Note" style="width:100%;height:215px;"></textarea>
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
        </div>

        <div class="row" style="margin-top:5px;">
          <div class="col-md-12">
            <div class="custom_note_box so_note_box">
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
//                  echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$so_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
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
        </div>
      </div>
      <!--</editor-fold>-->

      <!--<editor-fold desc="Bracket Info">-->
      <div class="col-md-4 bracket_info m-t-10">
        <button type="button" class="btn btn-secondary waves-effect waves-light no-print add_room_trigger" data-sonum="<?php echo $so['so_num']; ?>"><span class="btn-label"><i class="fa fa-plus-square"></i> </span>Add Room</button>

        <?php
        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' ORDER BY room, iteration ASC");

        function getBracketStatus($bracket_pub) {
          $out['class'] = (bool)$bracket_pub ? 'col-green' : null;
          $out['text'] = (bool)$bracket_pub ? 'Published' : 'Not Published';

          return $out;
        }

        $prev_room = null;
        $prev_sequence = null;

        while($room = $room_qry->fetch_assoc()) {
          $output['sales_marketing_bracket'] = !empty($operations[$room['sales_marketing_bracket']]) ? $operations[$room['sales_marketing_bracket']] : array('job_title' => 'Unassigned');
          $output['shop_bracket'] = !empty($operations[$room['shop_bracket']]) ? $operations[$room['shop_bracket']] : array('job_title' => 'Unassigned');
          $output['preproduction_bracket'] = !empty($operations[$room['preproduction_bracket']]) ? $operations[$room['preproduction_bracket']] : array('job_title' => 'Unassigned');
          $output['press_bracket'] = !empty($operations[$room['press_bracket']]) ? $operations[$room['press_bracket']] : array('job_title' => 'Unassigned');
          $output['paint_bracket'] = !empty($operations[$room['paint_bracket']]) ? $operations[$room['paint_bracket']] : array('job_title' => 'Unassigned');
          $output['custom_bracket'] = !empty($operations[$room['custom_bracket']]) ? $operations[$room['custom_bracket']] : array('job_title' => 'Unassigned');
          $output['shipping_bracket'] = !empty($operations[$room['shipping_bracket']]) ? $operations[$room['shipping_bracket']] : array('job_title' => 'Unassigned');
          $output['assembly_bracket'] = !empty($operations[$room['assembly_bracket']]) ? $operations[$room['assembly_bracket']] : array('job_title' => 'Unassigned');
          $output['welding_bracket'] = !empty($operations[$room['welding_bracket']]) ? $operations[$room['welding_bracket']] : array('job_title' => 'Unassigned');

          $bstat_sales_marketing = getBracketStatus($room['sales_marketing_published']);
          $bstat_shop = getBracketStatus($room['shop_published']);
          $bstat_preprod = getBracketStatus($room['preproduction_published']);
          $bstat_paint = getBracketStatus($room['paint_published']);
          $bstat_custom = getBracketStatus($room['custom_published']);
          $bstat_shipping = getBracketStatus($room['shipping_published']);
          $bstat_assembly = getBracketStatus($room['assembly_published']);
          $bstat_welding = getBracketStatus($room['welding_published']);

          $seq_it = explode('.', $room['iteration']);

          if($prev_room !== $room['room']) {
            $prev_room = $room['room'];
            $prev_sequence = $seq_it[0];

            $room_header = "{$room['room']}{$room['iteration']}: {$room['room_name']}";
          } else {
            if($prev_sequence !== $seq_it[0]) {
              $prev_sequence = $seq_it[0];

              $room_header = "&nbsp;&nbsp;&nbsp;{$room['iteration']}: {$room['room_name']}";
            } else {
              $room_header = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.{$seq_it[1]}: {$room['room_name']}";
            }
          }

          echo /** @lang HTML */
          <<<HEREDOC
        <div class="room_bracket">
          <div class="sticky bracket_header cursor-hand">
            <button class="btn waves-effect btn-primary room_manage_bracket" data-roomid="{$room['id']}" title="Edit Bracket"><i class="fa fa-code-fork"></i></button> 
            <button class="btn waves-effect btn_secondary disabled" id="show_attachments_room_{$room['id']}"><i class="zmdi zmdi-attachment-alt"></i></button> 
            <button class="btn btn-primary-outline waves-effect add_iteration" data-roomid="{$room['id']}" data-sonum="{$room['so_parent']}" data-addto="sequence" data-iteration="{$room['iteration']}" title="Add additional sequence"> S +1</button> 
            <button class="btn btn-primary-outline waves-effect add_iteration" data-roomid="{$room['id']}" data-sonum="{$room['so_parent']}" data-addto="iteration" data-iteration="{$room['iteration']}" title="Add additional iteration"> I +.01</button></td>

            $room_header
          </div>
          
          <table class="bracket_details">
            <colgroup>
              <col width="150px">
              <col width="200px">
              <col width="*">
            </colgroup>
            <thead>
              <tr>
                <th>Bracket</th>
                <th>Operation</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr class="{$bstat_sales_marketing['class']}">
                <td>Sales/Marketing:</td>
                <td>{$output['sales_marketing_bracket']['job_title']}</td>
                <td>{$bstat_sales_marketing['text']}</td>
              </tr>
              <tr class="{$bstat_shop['class']}">
                <td>Shop:</td>
                <td>{$output['shop_bracket']['job_title']}</td>
                <td>{$bstat_shop['text']}</td>
              </tr>
              <tr class="{$bstat_preprod['class']}">
                <td>Pre-production:</td>
                <td>{$output['preproduction_bracket']['job_title']}</td>
                <td>{$bstat_preprod['text']}</td>
              </tr>
              <tr class="{$bstat_sales_marketing['class']}">
                <td>Press:</td>
                <td>{$output['press_bracket']['job_title']}</td>
                <td>{$bstat_sales_marketing['text']}</td>
              </tr>
              <tr class="{$bstat_paint['class']}">
                <td>Paint:</td>
                <td>{$output['paint_bracket']['job_title']}</td>
                <td>{$bstat_paint['text']}</td>
              </tr>
              <tr class="{$bstat_custom['class']}">
                <td>Custom:</td>
                <td>{$output['custom_bracket']['job_title']}</td>
                <td>{$bstat_custom['text']}</td>
              </tr>
              <tr class="{$bstat_shipping['class']}">
                <td>Shipping:</td>
                <td>{$output['shipping_bracket']['job_title']}</td>
                <td>{$bstat_shipping['text']}</td>
              </tr>
              <tr class="{$bstat_assembly['class']}">
                <td>Assembly:</td>
                <td>{$output['assembly_bracket']['job_title']}</td>
                <td>{$bstat_assembly['text']}</td>
              </tr>
              <tr class="{$bstat_welding['class']}">
                <td>Welding:</td>
                <td>{$output['welding_bracket']['job_title']}</td>
                <td>{$bstat_welding['text']}</td>
              </tr>
            </tbody>
          </table>
        </div>
HEREDOC;
        }
        ?>
      </div>
    </div>
    <!--</editor-fold>-->

    <div class="row">
      <div class="col-md-12">
        <button type="button" class="btn btn-primary waves-effect waves-light w-xs save_so" data-sonum="<?php echo $so['so_num']; ?>">Save</button>
      </div>
    </div>
  </form>
</div>

<script>
  function checkEmptyFields(group) {
    let i = 0;

    $(group + " input").each(function() {
      if($(this).val() !== '') {
        i++;
      }
    });

    if(i === 0) {
      $(group).hide();
    }
  }

  $("#show_all_fields").change(function() {
    if($("#show_all_fields").is(":checked")) {
      $(".name1group").show();
      $(".name2group").show();
      $('.s_addr_empty').show();
      $('.con_empty').show();
      $('.billing_empty').show();
    } else {
      checkEmptyFields(".name1group");
      checkEmptyFields(".name2group");
      $('.s_addr_empty').hide();
      $('.con_empty').hide();
      $('.billing_empty').hide();
    }
  });

  nameOfUser = '<?php echo $_SESSION['userInfo']['name']; ?>';
  checkEmptyFields('.name1group');
  checkEmptyFields('.name2group');

  $(".add_contact_id").select2();

  crmProject.bracketMgr.init();
  crmProject.contactMgr.init();
</script>