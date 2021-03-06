<?php
require '../../../includes/header_start.php';
require '../../../includes/classes/associations.php';
require '../../../includes/classes/dropdown_options.php';

$so_id = sanitizeInput($_REQUEST['so_id']);

$vin_schema = getVINSchema();

use associations\associations;

use DropdownOpts\dropdown_options;
$drop_opts = new dropdown_options();

$operations = []; // operation information

// get all operations
$op_qry = $dbconn->query('SELECT * FROM operations');

while($op = $op_qry->fetch_assoc()) {
  $operations[$op['id']] = $op;
}

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE id = '$so_id'");
$so = $so_qry->fetch_assoc();

$so_num = $so['so_num'];
?>

<style>
  .assign_contact_so {
    padding: 6px;
  }
</style>

<div class="container-fluid">
  <div class="row sticky no-print" style="background-color:#FFF;z-index:3;top:21px;padding:4px;">
    <div class="col-md-4">
      <button class="btn waves-effect btn-primary-outline save_so" title="Save Changes" data-sonum="<?php echo $so['so_num']; ?>"> <i class="fa fa-save fa-2x"></i> </button>
      <button type="button" class="btn waves-effect btn-primary-outline add_room_trigger" data-sonum="<?php echo $so['so_num']; ?>"><span class="btn-label"><i class="fa fa-plus-circle"></i> </span>Add Room</button>
    </div>
  </div>

  <form id="project_form">
    <div class="row">
      <!--<editor-fold desc="Project information">-->
      <div class="col-md-3">
        <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
          <tr>
            <td colspan="2"><b><u>Project</u></b></td>
          </tr>
          <tr>
            <td><label for="project_name">Name:</label></td>
            <td><input type="text" value="<?php echo $so['project_name']; ?>" name="project_name" class="c_input" placeholder="Project Name" id="project_name" /></td>
          </tr>
          <tr>
            <td><label for="project_addr">Address:</label></td>
            <td><input type="text" value="<?php echo $so['project_addr']; ?>" name="project_addr" class="c_input " placeholder="Project Address" id="project_addr" /></td>
          </tr>
          <tr>
            <td><label for="project_city">City:</label></td>
            <td><input type="text" value="<?php echo $so['project_city']; ?>" name="project_city" class="c_input" placeholder="Project City" id="project_city"></td>
          </tr>
          <tr>
            <td><label for="project_state">State:</label></td>
            <td><select class="c_input" id="project_state" name="project_state"><?php echo $drop_opts->getStateOpts($so['project_state']); ?></select></td>
          </tr>
          <tr>
            <td><label for="project_zip">Zip:</label></td>
            <td><input type="text" value="<?php echo $so['project_zip']; ?>" name="project_zip" class="c_input" placeholder="Project Zip" id="project_zip"></td>
          </tr>
          <tr>
            <td><label for="project_landline">Landline:</label></td>
            <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="project_landline" class="c_input" placeholder="Project Landline" id="project_landline"></td>
          </tr>
          <tr>
            <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
          </tr>
        </table>

        <div class="contact-box-main">
          <h5>Associations</h5>

          <?php
          $association = new \associations\associations();

          $association->displayContactAssociations($so['id'], 'project');
          ?>
        </div>
      </div>
      <!--</editor-fold>-->

      <!--<editor-fold desc="Notes">-->
      <div class="col-md-5 sticky no-print" style="top:38px;">
        <form id="company_notes" action="#">
          <div class="row">
            <div class="col-md-12">
              <ul class="nav nav-tabs m-b-10 m-t-10" id="companyNotes" role="tablist">
                <li class="nav-item">
                  <a class="nav-link" id="p-company-tab" data-toggle="tab" href="#p_company" role="tab" aria-controls="p_company" aria-selected="false">Account Notes</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link active show" id="p-project-tab" data-toggle="tab" href="#p_project" role="tab" aria-controls="p_company" aria-selected="false">Project Notes</a>
                </li>
              </ul>
              <div class="tab-content" id="roomNotesContent">
                <div role="tabpanel" class="tab-pane fade" id="p_company"  aria-labelledby="company-tab">
                  <div class="row">
                    <div class="col-md-12">
                      <textarea class="form-control" name="company_notes" id="p_company_notes" placeholder="New Note" style="width:100%;height:130px;"></textarea>
                      <input type="text" name="company_followup_date" id="p_company_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                      <label for="p_requested_of" style="float:left;padding:4px;"> requested of </label>
                      <select name="requested_of" id="p_requested_of" class="form-control" style="width:45%;float:left;">
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

                  <div class="row">
                    <div class="col-md-12">
                      <h5>History</h5>

                      <table class="table-bordered table-striped table" width="100%">
                        <thead>
                        <tr>
                          <th>Individual</th>
                          <th>Date/Time</th>
                          <th>Note</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $note_qry = $dbconn->query("SELECT n.*, u.name FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'company_note' AND type_id = {$so['company_id']}");

                        if($note_qry->num_rows > 0) {
                          while($note = $note_qry->fetch_assoc()) {
                            $note_preview = substr($note['note'], 0, 80);

                            if(strlen($note['note']) > 80) {
                              $note_preview = trim($note_preview);
                              $note_preview .= '...';
                            }

                            $note_preview = str_ireplace(PHP_EOL, '<i class="fa fa-level-down fa-rotate-90" style="margin:0 5px;"></i>', $note_preview);

                            $time = date(DATE_TIME_ABBRV, $note['timestamp']);

                            $note_translated = nl2br($note['note']);

                            $full_note = "<div style='background-color:#FFF;border:1px solid #000;padding:2px;display:none;'>$note_translated</div>";

                            echo '<tr class="cursor-hand view_note_information">';
                            echo "<td>{$note['name']}</td>";
                            echo "<td>$time</td>";
                            echo "<td>$note_preview $full_note</td>";
                            echo '</tr>';
                          }
                        } else {
                          echo '<tr><td colspan="3"><b>No company notes currently available.</b></td></tr>';
                        }
                        ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <div role="tabpanel" class="tab-pane fade in active show" id="p_project"  aria-labelledby="project-tab">
                  <div class="row">
                    <div class="col-md-12">
                      <textarea class="form-control" name="project_notes" id="p_project_notes" placeholder="New Note" style="width:100%;height:130px;"></textarea>
                      <input type="text" name="project_followup_date" id="p_project_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                      <label for="p_project_requested_of" style="float:left;padding:4px;"> requested of </label>
                      <select name="project_requested_of" id="p_project_requested_of" class="form-control" style="width:45%;float:left;">
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

                  <div class="row">
                    <div class="col-md-12">
                      <h5>History</h5>

                      <div style="max-height:400px;overflow-y:auto;">
                        <table class="table-bordered table-striped table" width="100%">
                          <thead>
                          <tr>
                            <th>Individual</th>
                            <th>Date/Time</th>
                            <th>Note</th>
                          </tr>
                          </thead>
                          <tbody>
                          <?php
                          $note_qry = $dbconn->query("SELECT n.*, u.name FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'so_inquiry' AND type_id = {$so['id']} ORDER BY n.timestamp DESC;");

                          if($note_qry->num_rows > 0) {
                            while($note = $note_qry->fetch_assoc()) {
                              $note_preview = substr($note['note'], 0, 60);

                              if(strlen($note['note']) > 60) {
                                $note_preview = trim($note_preview);
                                $note_preview .= '...';
                              }

                              $note_preview = str_ireplace(PHP_EOL, '<i class="fa fa-level-down fa-rotate-90" style="margin:0 5px;"></i>', $note_preview);

                              $time = date(DATE_TIME_ABBRV, $note['timestamp']);

                              $note_translated = nl2br($note['note']);

                              $full_note = "<div style='background-color:#FFF;border:1px solid #000;padding:2px;display:none;'>$note_translated</div>";

                              echo '<tr class="cursor-hand view_note_information">';
                              echo "<td class='nowrap'>{$note['name']}</td>";
                              echo "<td class='nowrap'>$time</td>";
                              echo "<td>$note_preview $full_note</td>";
                              echo '</tr>';
                            }
                          } else {
                            echo '<tr><td colspan="3"><b>No project notes currently available.</b></td></tr>';
                          }
                          ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <!--</editor-fold>-->

      <!--<editor-fold desc="Bracket Info">-->
      <div class="col-md-4 bracket_info m-t-10">
        <button type="button" class="btn btn-secondary waves-effect waves-light no-print add_room_trigger" data-sonum="<?php echo $so['so_num']; ?>"><span class="btn-label"><i class="fa fa-plus-circle"></i> </span>Add Room</button>

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
              <col width="115px">
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
  </form>
</div>

<script>
  nameOfUser = '<?php echo $_SESSION['userInfo']['name']; ?>';

  $("#project_form").find(".contact_id").select2();

  crmProject.bracketMgr.init();
  association.init();
  crmMain.initNoteExpand();
  // crmProject.contactMgr.init();
</script>