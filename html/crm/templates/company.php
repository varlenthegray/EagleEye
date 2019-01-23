<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$company_id = sanitizeInput($_REQUEST['company_id']);

$company_qry = $dbconn->query("SELECT * FROM contact_company WHERE id = $company_id");
$company = $company_qry->fetch_assoc();
?>

<div class="container-fluid">
  <div class="row sticky no-print" style="background-color:#FFF;z-index:3;top:21px;padding:4px;">
    <div class="col-md-4">
      <button class="btn waves-effect btn-primary-outline save_company" title="Save Changes" data-id="<?php echo $company['id']; ?>"> <i class="fa fa-save fa-2x"></i> </button>
      <button class="btn waves-effect btn-primary-outline add_project" title="Add Project" data-id="<?php echo $company['id']; ?>"> <i class="fa fa-plus-circle fa-2x"></i> </button>
    </div>
  </div>

  <form id="company_information">
    <input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />

    <div class="row">
      <div class="col-md-3">
        <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
          <tr>
            <td colspan="2"><u><b>Bill To</b></u></td>
          </tr>
          <tr>
            <td style="width:165px;"><label for="company_name">Name:</label></td>
            <td><input type="text" value="<?php echo $company['name']; ?>" name="company_name" class="c_input" placeholder="Company Name" id="company_name" /></td>
          </tr>
          <tr>
            <td><label for="company_address">Address:</label></td>
            <td><input type="text" value="<?php echo $company['address']; ?>" name="company_address" class="c_input " placeholder="Company Address" id="company_address" /></td>
          </tr>
          <tr>
            <td><label for="company_city">City:</label></td>
            <td><input type="text" value="<?php echo $company['city']; ?>" name="company_city" class="c_input" placeholder="Company City" id="company_city"></td>
          </tr>
          <tr>
            <td><label for="company_state">State:</label></td>
            <td><select class="c_input" id="company_state" name="company_state"><?php echo getStateOpts($company['state']); ?></select></td>
          </tr>
          <tr>
            <td><label for="company_zip">Zip:</label></td>
            <td><input type="text" value="<?php echo $company['zip']; ?>" name="company_zip" class="c_input" placeholder="Company Zip" id="company_zip"></td>
          </tr>
          <tr>
            <td><label for="company_email">Email:</label></td>
            <td><input type="text" value="<?php echo $company['email']; ?>" name="company_email" class="c_input" placeholder="Company Email" id="company_email"></td>
          </tr>
          <tr>
            <td><label for="company_landline">Landline:</label></td>
            <td><input type="text" value="<?php echo $company['landline']; ?>" name="company_landline" class="c_input" placeholder="Company Landline" id="company_landline"></td>
          </tr>
          <tr>
            <td colspan="2"><b><u>Billing</u></b></td>
          </tr>
          <tr>
            <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
          </tr>
          <tr>
            <td colspan="2"><div class="pull-left"><b><u>Shipping Address</u></b></div></td>
          </tr>
          <tr>
            <td colspan="2"><div style="margin-left:20px;float:left;" class="checkbox"><input id="shipping_different" class="ignoreSaveAlert" type="checkbox" value="1"><label for="shipping_different"> Alternate Address</label></div></td>
          </tr>
          <tr class="shipping_empty_hide">
            <td><label for="company_ship_addr">Address:</label></td>
            <td><input type="text" value="<?php echo $company['shipping_address']; ?>" name="company_ship_addr" class="c_input" placeholder="Shipping Address" id="company_ship_addr"></td>
          </tr>
          <tr class="shipping_empty_hide">
            <td><label for="company_ship_city">City:</label></td>
            <td><input type="text" value="<?php echo $company['shipping_city']; ?>" name="company_ship_city" class="c_input" placeholder="Shipping City" id="company_ship_city"></td>
          </tr>
          <tr class="shipping_empty_hide">
            <td><label for="company_ship_state">State:</label></td>
            <td><select class="c_input" id="company_ship_state" name="company_ship_state"><?php echo getStateOpts($company['shipping_state']); ?></select></td>
          </tr>
          <tr class="shipping_empty_hide">
            <td><label for="company_ship_zip">Zip:</label></td>
            <td><input type="text" value="<?php echo $company['shipping_zip']; ?>" name="company_ship_zip" class="c_input" placeholder="Shipping Zip" id="company_ship_zip"></td>
          </tr>
          <tr class="shipping_empty_hide">
            <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
          </tr>
          <tr>
            <td colspan="2"><div class="pull-left"><b><u>Billing</u></b></div></td>
          </tr>
          <tr>
            <td colspan="2"><div style="margin-left:20px;float:left;" class="checkbox"><input id="show_payment_info" class="ignoreSaveAlert" type="checkbox" value="1"><label for="show_payment_info"> Payment Information</label></div></td>
          </tr>
          <tr class="payment_info">
            <td><label for="company_payment_processor">Payment Processor:</label></td>
            <td>
              <select class="c_input" id="company_payment_processor" name="company_payment_processor">
                <option value="Stripe">Stripe</option>
                <option value="Square">Square</option>
                <option value="Bank">Bank</option>
              </select>
            </td>
          </tr>
          <tr class="payment_info">
            <td><label for="company_payment_ach_account">ACH Account:</label></td>
            <td><input type="text" value="" name="company_payment_ach_account" autocomplete="no" class="c_input" placeholder="ACH Account Number" id="company_payment_ach_account"></td>
          </tr>
          <tr class="payment_info">
            <td><label for="company_payment_ach_routing">ACH Routing:</label></td>
            <td><input type="text" value="" name="company_payment_ach_routing" autocomplete="no" class="c_input" placeholder="ACH Routing Number" id="company_payment_ach_routing"></td>
          </tr>
          <tr class="payment_info">
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr class="payment_info">
            <td><label for="company_payment_cc_num">CC:</label></td>
            <td><input type="text" value="" maxlength="16" name="company_payment_cc_num" autocomplete="no" class="c_input" placeholder="CC Number" id="company_payment_cc_num"></td>
          </tr>
          <tr class="payment_info">
            <td><label for="company_payment_cc_exp">CC Exp:</label></td>
            <td><input type="text" value="" maxlength="8" name="company_payment_cc_exp" autocomplete="no" class="c_input" placeholder="CC Expiration" id="company_payment_cc_exp"></td>
          </tr>
          <tr class="payment_info">
            <td><label for="company_payment_cc_ccv">CC CCV:</label></td>
            <td><input type="text" value="" maxlength="4" name="company_payment_cc_ccv" autocomplete="no" class="c_input" placeholder="CC CCV" id="company_payment_cc_ccv"></td>
          </tr>
          <tr class="payment_info">
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="2"><div style="margin-left:20px;float:left;" class="checkbox"><input id="billing_different" class="ignoreSaveAlert" type="checkbox" value="1"><label for="billing_different"> Alternate Address</label></div></td>
          </tr>
          <tr class="billing_empty_hide">
            <td><label for="company_billing_addr">Address:</label></td>
            <td><input type="text" value="<?php echo $company['billing_address']; ?>" name="company_billing_addr" class="c_input" placeholder="Billing Address" id="company_billing_addr"></td>
          </tr>
          <tr class="billing_empty_hide">
            <td><label for="company_billing_city">City:</label></td>
            <td><input type="text" value="<?php echo $company['billing_city']; ?>" name="company_billing_city" class="c_input" placeholder="Billing City" id="company_billing_city"></td>
          </tr>
          <tr class="billing_empty_hide">
            <td><label for="company_billing_state">State:</label></td>
            <td><select class="c_input" id="company_billing_state" name="company_billing_state"><?php echo getStateOpts($company['billing_state']); ?></select></td>
          </tr>
          <tr class="billing_empty_hide">
            <td><label for="company_billing_zip">Zip:</label></td>
            <td><input type="text" value="<?php echo $company['billing_zip']; ?>" name="company_billing_zip" class="c_input" placeholder="Billing Zip" id="company_billing_zip"></td>
          </tr>
          <tr>
            <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
          </tr>
          <tr>
            <td colspan="3"><h5>Contacts</h5></td>
          </tr>
          <tr>
            <td colspan="3">
              <?php
              function getContactCard($contact) {
                return <<<HEREDOC
    <div class="contact-card">
      <div style="float:right;"><i class="fa fa-minus-square danger-color cursor-hand remove_assigned_contact" data-id="{$contact['id']}" title="Remove Contact"></i></div>
      <h5><a href="#">{$contact['first_name']} {$contact['last_name']}</a></h5>
      <h6>{$contact['associated_as']}</h6>
    
      <p>{$contact['cell']}<br>{$contact['email']}</p>
    </div>
HEREDOC;
              }

              $contact_dropdown = null;

              $contact_qry = $dbconn->query('SELECT c.id, c.first_name, c.last_name, c.company_name, c2.description FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id ORDER BY c2.description, c.first_name, c.last_name ASC');

              if($contact_qry->num_rows > 0) {
                $contact_dropdown = "<select class='c_input pull-left contact_id ignoreSaveAlert' style='width:100%;' name='add_contact'><option value='' disabled selected>Select</option>";

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

              echo "<table class='m-b-10' width='100%'>
                      <tr>
                        <td width='90px'><label for='add_contact'>Add Association</label></td>
                        <td>$contact_dropdown</td>
                        <td width='20px'><i class='fa fa-plus-square assign_contact primary-color cursor-hand' data-type-id='{$company['id']}'></i></td>
                      </tr>
                    </table>";

              // displaying existing contact relationships
              $so_contacts_qry = $dbconn->query("SELECT c.*, a.id AS id, c2.description, a.associated_as FROM contact_associations a LEFT JOIN contact c ON a.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE a.type_id = '{$company['id']}' AND a.type = 'company' ORDER BY c.first_name, c.last_name ASC");

              echo "<div class='contact-box'>";

              if($so_contacts_qry->num_rows > 0) {
                while($so_contacts = $so_contacts_qry->fetch_assoc()) {
                  echo getContactCard($so_contacts);
                }
              } else {
                echo '<strong>No Contacts</strong>';
              }

              echo '</div>';
              ?>
            </td>
          </tr>
        </table>
      </div>

      <!--<editor-fold desc="Notes">-->
      <div class="col-md-5 sticky no-print" style="top:38px;">
        <form id="company_notes" action="#">
          <div class="row">
            <div class="col-md-12">
              <ul class="nav nav-tabs m-b-10 m-t-10" id="companyNotes" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active show" id="c-company-tab" data-toggle="tab" href="#c_company" role="tab" aria-controls="c_company" aria-selected="false">Bill To Notes</a>
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
                        $note_qry = $dbconn->query("SELECT n.*, u.name FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'company_note' AND type_id = $company_id ORDER BY timestamp DESC;");

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
    crmCompany.checkEmpty('company_ship_', '#shipping_different', '.shipping_empty_hide');
    crmCompany.checkEmpty('company_billing_', '#billing_different', '.billing_empty_hide');
    crmCompany.checkEmpty('company_payment_', '#show_payment_info', '.payment_info');

    $("#company_information").find(".contact_id").select2();

    association.init();
  });
</script>