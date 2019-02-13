<?php
require '../../../includes/header_start.php';
require '../../../includes/classes/associations.php';
require '../../../includes/classes/dropdown_options.php';
require '../classes/customer.php';

use customer\customer;
$customer = new customer();

use associations\associations;
$association = new associations();

//outputPHPErrs();

$contact_id = sanitizeInput($_REQUEST['org_id']);

$contact_qry = $dbconn->query("SELECT * FROM contact WHERE id = $contact_id");
$contact = $contact_qry->fetch_assoc();
?>

<div class="container-fluid">
  <div class="row sticky no-print" style="background-color:#FFF;z-index:3;top:21px;padding:4px;">
    <div class="col-md-4">
      <button class="btn waves-effect btn-primary-outline save_company" title="Save Changes" data-id="<?php echo $contact['id']; ?>"> <i class="fa fa-save fa-2x"></i> </button>
      <button type="button" class="btn waves-effect btn-primary-outline add_project" title="Add Project" data-id="<?php echo $contact['id']; ?>"><span class="btn-label"><i class="fa fa-plus-circle"></i> </span>Add Project</button>
<!--      <button class="btn waves-effect btn-primary-outline add_project" title="Add Project" data-id="--><?php //echo $contact['id']; ?><!--"> <i class="fa fa-plus-circle fa-2x"></i> </button>-->
    </div>
  </div>

  <form id="company_information">
    <div class="row">
      <div class="col-md-4 customer_edit">
        <?php $customer->displayFields($contact_id); ?>

        <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
          <tr><td colspan="3"><h5>Associations</h5></td></tr>
          <tr><td colspan="3"> <?php $association->displayContactAssociations($contact['id'], 'contact'); ?> </td></tr>
        </table>
      </div>

      <!--<editor-fold desc="Notes">-->
      <div class="col-md-5 sticky no-print" style="top:38px;">
        <form id="company_notes" action="#">
          <div class="row">
            <div class="col-md-12">
              <ul class="nav nav-tabs m-b-10 m-t-10" id="companyNotes" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active show" id="c-company-tab" data-toggle="tab" href="#c_company" role="tab" aria-controls="c_company" aria-selected="false">Organization Notes</a>
                </li>
              </ul>
              <div class="tab-content" id="roomNotesContent">
                <div role="tabpanel" class="tab-pane fade in active show" id="c_company"  aria-labelledby="so-tab">
                  <div class="row">
                    <div class="col-md-12">
                      <textarea class="form-control" name="company_notes" id="c_company_notes" placeholder="New Note" style="width:100%;height:130px;"></textarea>
                      <input type="text" name="company_followup_date" id="c_company_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                      <label for="c_requested_of" style="float:left;padding:4px;"> requested of </label>
                      <select name="requested_of" id="c_requested_of" class="form-control" style="width:45%;float:left;">
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
                          <th class="no-wrap">Individual</th>
                          <th>Date/Time</th>
                          <th>Note</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $note_qry = $dbconn->query("SELECT n.*, u.name FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'contact_note' AND type_id = $contact_id ORDER BY timestamp DESC;");

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
                            echo "<td class='nowrap'>$note_preview $full_note</td>";
                            echo '</tr>';
                          }
                        } else {
                          echo '<tr><td colspan="3"><b>No notes currently available.</b></td></tr>';
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
        </form>
      </div>
      <!--</editor-fold>-->
    </div>
  </form>
</div>

<script>
  nameOfUser = '<?php echo $_SESSION['userInfo']['name']; ?>';

  crmCompany.init();

  crmMain.initNoteExpand();

  $(function() {
    let editDiv = $(".customer_edit");

    crmCompany.editCustomerDisplay('.add_customer_checked', '.customer_data');
    crmCompany.editCustomerDisplay('.cust_ship_addr_different', '.cust_ship_different');
    crmCompany.editCustomerDisplay('.add_vendor_check', '.vendor_data');
    crmCompany.editCustomerDisplay('.vend_receive_addr_different', '.vend_receive_different');
    crmCompany.editCustomerDisplay('.vend_payment_contact_different', '.vend_payment_contact');
    crmCompany.editCustomerDisplay('.add_employee_check', '.employee_data');
    crmCompany.editCustomerDisplay('.emp_personal_info_check', '.emp_personal_different');
    crmCompany.editCustomerDisplay('.emp_emergency_contact_info_check', '.emp_emergency_contact');

    let activeNode = crmNav.getTree.getActiveNode();

    editDiv.find('.identifier').prop("disabled", true);
    editDiv.find(".contactType").attr("value", activeNode.data.contactType);
    editDiv.find(".contactID").attr("value", crmNav.navKeys[0]);

    if(activeNode.data.contactType === 'individual') {
      editDiv.find(".add_new_org").hide();
      editDiv.find(".add_new_individual").show();
      editDiv.find(".employee_info_checkbox").show();

    } else {
      editDiv.find(".add_new_org").show();
      editDiv.find(".add_new_individual").hide();
      editDiv.find(".employee_info_checkbox").hide();
    }

    $("#company_information").find(".contact_id").select2();

    association.init();
  });
</script>