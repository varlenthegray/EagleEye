<?php
require '../../../includes/header_start.php';

$so_num = sanitizeInput($_REQUEST['so_num']);

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '$so_num'");
$so = $so_qry->fetch_assoc();
?>

<div class="container-fluid">
  <form id="form_so_<?php echo $so['so_num']; ?>">
    <div class="row">
      <div class="col-md-3">
        <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
          <?php if((bool)$_SESSION['userInfo']['dealer']) {
            $dealer_code = ucwords($_SESSION['userInfo']['username']);

            echo "<input type='hidden' name='dealer_code' id='dealer_code' value='$dealer_code'>";
            ?>
            <tr>
              <td colspan="3">
                <input type="text" name="project_name" class="form-control pull-left" placeholder="Project Name" id="project_name" value="<?php echo $so['project_name']; ?>" style="width:50%;" />
                <input type="text" name="project_addr" class="form-control pull-left" placeholder="Job Site Address" id="project_addr"value="<?php echo $so['project_addr']; ?>" style="width:50%;">
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <table style="width: 100%;">
                  <tr>
                    <td style="width: 33.3%;"><input type="text" name="project_city" class="form-control" placeholder="Job Site City" value="<?php echo $so['project_city']; ?>" id="project_city"></td>
                    <td style="width: 33.3%;"><select class="form-control" id="project_state" name="project_state"><?php echo getStateOpts($so['project_state']); ?></select></td>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['project_zip']; ?>" name="project_zip" class="form-control" placeholder="Job Site Zip" id="project_zip"></td>
                  </tr>
                </table>
              </td>
              <td><input type="text" name="project_landline" class="form-control" placeholder="Job Site Landline" value="<?php echo $so['project_landline']; ?>" id="project_landline"></td>
            </tr>
            <tr>
              <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
            </tr>
            <tr>
              <td colspan="3"><h5>Contacts</h5></td>
            </tr>
            <?php
            // TODO: Clean the duplicate up between this and a normal SO (non-dealer)
            $contact_dropdown = null;

            $dealer = substr($_SESSION['userInfo']['dealer_code'], 0, 3);

            $contact_qry = $dbconn->query("SELECT c.id, c.first_name, c.last_name, c.company_name, c2.description FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id WHERE d.dealer_id LIKE '%$dealer%' ORDER BY c2.description, c.first_name, c.last_name ASC");

            if($contact_qry->num_rows > 0) {
              $contact_dropdown = "<select class='form-control pull-left add_contact_id ignoreSaveAlert' name='add_contact' style='width:50%;'>";

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

            echo "<tr><td><div class='form-group'><label for='add_contact' class='pull-left' style='line-height:28px;padding-right:10px;'>Add Contact</label> $contact_dropdown <button type='button' class='btn waves-effect waves-light btn-primary assign_contact_so' style='margin:2px 0 0 10px;'> <i class='zmdi zmdi-plus-circle-o'></i> </button></div></td></tr>";

            // displaying existing contact relationships
            $so_contacts_qry = $dbconn->query("SELECT c.*, c2.description FROM sales_order_contacts soc LEFT JOIN contact c ON soc.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE so_id = '{$so['id']}' ORDER BY c.first_name, c.last_name ASC");

            if($so_contacts_qry->num_rows > 0) {
              while($so_contacts = $so_contacts_qry->fetch_assoc()) {
                $name = !empty($so_contacts['first_name']) ? "{$so_contacts['first_name']} {$so_contacts['last_name']}" : $so_contacts['company_name'];

                echo "<tr><td colspan='3'><button type='button' class='btn waves-effect waves-light btn-danger remove_assigned_contact_so' style='margin:2px 0;' data-id='{$so_contacts['id']}'> <i class='zmdi zmdi-minus-circle-outline'></i> </button> <a href='#' class='get_customer_info' data-view-id='{$so_contacts['id']}''>$name ({$so_contacts['description']})</a></td></tr>";
              }
            } else {
              echo "<tr><td colspan='3'><strong>No Contacts Assigned</strong></td></tr>";
            }
            ?>
          <?php } else { ?>
            <tr>
              <td colspan="2"><div style="margin-left:10px;" class="checkbox"><input id="show_all_fields" class="ignoreSaveAlert" type="checkbox" value="1"><label for="show_all_fields"> Show All Fields</label></div></td>
            </tr>
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
            <tr class="name1group">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="name1group">
              <td><label for="name_1">Name 1:</label></td>
              <td><input type="text" value="<?php echo $so['name_1']; ?>" name="name_1" class="c_input" placeholder="Name 1" id="name_1"></td>
            </tr>
            <tr class="name1group">
              <td><label for="cell_1">Cell 1:</label></td>
              <td><input type="text" value="<?php echo $so['cell_1']; ?>" name="cell_1" class="c_input" placeholder="Cell Phone" id="cell_1"></td>
            </tr>
            <tr class="name1group">
              <td><label for="business_1">Secondary Phone 1:</label></td>
              <td><input type="text" value="<?php echo $so['business_1']; ?>" name="business_1" class="c_input" placeholder="Secondary Phone" id="business_1"></td>
            </tr>
            <tr class="name1group">
              <td><label for="email_1">Email 1:</label></td>
              <td><input type="text" value="<?php echo $so['email_1']; ?>" name="email_1" class="c_input" placeholder="Email Address" id="email_1"></td>
            </tr>
            <tr class="name2group">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="name2group">
              <td><label for="name_2">Name 2:</label></td>
              <td><input type="text" value="<?php echo $so['name_2']; ?>" name="name_2" class="c_input" placeholder="Name 2" id="name_2"></td>
            </tr>
            <tr class="name2group">
              <td><label for="cell_2">Cell 2:</label></td>
              <td><input type="text" value="<?php echo $so['cell_2']; ?>" name="cell_2" class="c_input" placeholder="Cell Phone" id="cell_2"></td>
            </tr>
            <tr class="name2group">
              <td><label for="business_2">Secondary Phone 2:</label></td>
              <td><input type="text" value="<?php echo $so['business_2']; ?>" name="business_2" class="c_input" placeholder="Secondary Phone" id="business_2"></td>
            </tr>
            <tr class="name2group">
              <td><label for="email_2">Email 2:</label></td>
              <td><input type="text" value="<?php echo $so['email_2']; ?>" name="email_2" class="c_input" placeholder="Email Address" id="email_2"></td>
            </tr>
            <tr class="s_addr_empty">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="s_addr_empty">
              <td colspan="3">
                <?php
                if (!empty($so['secondary_addr']) || !empty($so['secondary_city']) || !empty($so['secondary_zip']) || !empty($so['secondary_landline'])) {
                  $secondary_checked = ' checked';
                  echo "<script>$('.secondary_addr_disp').show();</script>";
                } else {
                  $secondary_checked = null;
                  echo "<script>$('.s_addr_empty').hide();</script>";
                }
                ?>
                <div class="checkbox"><input id="secondary_addr_chk" type="checkbox" <?php echo $secondary_checked; ?>><label for="secondary_addr_chk"> Customer Secondary Address</label></div>
              </td>
            </tr>
            <tr style="display:none;" class="secondary_addr_disp">
              <td><label for="secondary_addr">Secondary Address:</label></td>
              <td><input type="text" value="<?php echo $so['secondary_addr']; ?>" name="secondary_addr" class="c_input" placeholder="Secondary Address" id="secondary_addr"></td>
            </tr>
            <tr style="display:none;" class="secondary_addr_disp">
              <td><label for="secondary_city">Secondary City:</label></td>
              <td><input type="text" value="<?php echo $so['secondary_city']; ?>" name="secondary_city" class="c_input" placeholder="Secondary City" id="secondary_city"></td>
            </tr>
            <tr style="display:none;" class="secondary_addr_disp">
              <td><label for="secondary_state">Secondary State:</label></td>
              <td><select class="c_input" id="secondary_state" name="secondary_state"><?php echo getStateOpts($so['secondary_state']); ?></select></td>
            </tr>
            <tr style="display:none;" class="secondary_addr_disp">
              <td><label for="secondary_zip">Secondary Zip:</label></td>
              <td><input type="text" value="<?php echo $so['secondary_zip']; ?>" name="secondary_zip" class="c_input" placeholder="Secondary Zip" id="secondary_zip"></td>
            </tr>
            <tr style="display:none;" class="secondary_addr_disp">
              <td><label for="secondary_landline">Secondary Landline:</label></td>
              <td><input type="text" value="<?php echo $so['secondary_landline']; ?>" name="secondary_landline" class="c_input" placeholder="Secondary Landline" id="secondary_landline"></td>
            </tr>
            <tr class="con_empty">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="con_empty">
              <td colspan="3">
                <?php
                if (!empty($so['contractor_name']) || !empty($so['contractor_business']) || !empty($so['contractor_cell']) || !empty($so['contractor_email']) || !empty($so['contractor_zip']) ||
                  !empty($so['contractor_city']) || !empty($so['contractor_addr'])) {

                  $contractor_checked = ' checked';
                  echo "<script>$('.contractor_disp').show();</script>";
                } else {
                  $contractor_checked = null;
                  echo "<script>$('.con_empty').hide();</script>";
                }
                ?>
                <div class="checkbox"><input id="contractor_chk" type="checkbox" <?php echo $contractor_checked; ?>><label for="contractor_chk"> Contractor</label></div>
              </td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="contractor_name">Contractor Name:</label></td>
              <td><input type="text" value="<?php echo $so['contractor_name']; ?>" name="contractor_name" class="c_input" placeholder="Contractor Name" id="contractor_name"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="contractor_business">Contractor Business Line:</label></td>
              <td><input type="text" value="<?php echo $so['contractor_business']; ?>" name="contractor_business" class="c_input" placeholder="Contractor Business Number" id="contractor_business"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="contractor_cell">Contractor Cell:</label></td>
              <td><input type="text" value="<?php echo $so['contractor_cell']; ?>" name="contractor_cell" class="c_input" placeholder="Contractor Cell Number" id="contractor_cell"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="contractor_addr">Contractor Address:</label></td>
              <td><input type="text" value="<?php echo $so['contractor_addr']; ?>" name="contractor_addr" class="c_input" placeholder="Contractor Address" id="contractor_addr"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="contractor_city">Contractor City:</label></td>
              <td><input type="text" value="<?php echo $so['contractor_city']; ?>" name="contractor_city" class="c_input" placeholder="Contractor City" id="contractor_city"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="contractor_state">Contractor State:</label></td>
              <td><select class="c_input" id="contractor_state" name="contractor_state"><?php echo getStateOpts($so['contractor_state']); ?></select></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="contractor_zip">Contractor Zip:</label></td>
              <td><input type="text" value="<?php echo $so['contractor_zip']; ?>" name="contractor_zip" class="c_input" placeholder="Contractor Zip" id="contractor_zip"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="contractor_email">Contractor Email:</label></td>
              <td><input type="text" value="<?php echo $so['contractor_email']; ?>" name="contractor_email" class="c_input" placeholder="Contractor Email Address" id="contractor_email"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="project_mgr">Project Manager:</label></td>
              <td><input type="text" value="<?php echo $so['project_mgr']; ?>" name="project_mgr" class="c_input" placeholder="Project Manager" id="project_mgr"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="project_mgr_cell">Project Manager Cell:</label></td>
              <td><input type="text" value="<?php echo $so['project_mgr_cell']; ?>" name="project_mgr_cell" class="c_input" placeholder="Project Manager Cell" id="project_mgr_cell"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><label for="project_mgr_email">Project Manager Email:</label></td>
              <td><input type="text" value="<?php echo $so['project_mgr_email']; ?>" name="project_mgr_email" class="c_input" placeholder="Project Manager Email" id="project_mgr_email"></td>
            </tr>
            <tr class="billing_empty">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="billing_empty">
              <td colspan="2">
                <?php
                if (!empty($so['bill_to']) || !empty($so['billing_contact']) || !empty($so['billing_landline']) || !empty($so['billing_cell']) || !empty($so['billing_addr']) ||
                  !empty($so['billing_city']) || !empty($so['billing_zip']) || !empty($so['billing_account']) || !empty($so['billing_routing'])
                  || !empty($so['billing_cc_num']) || !empty($so['billing_cc_exp']) || !empty($so['billing_cc_ccv'])) {

                  $billing_checked = ' checked';
                  echo "<script>$('.billing_info_disp').show();</script>";
                } else {
                  $billing_checked = null;
                  echo "<script>$('.billing_empty').hide();</script>";
                }

                $b_homeowner = null;
                $b_contractor = null;

                if ($so['bill_to'] === 'homeowner') {
                  $b_homeowner = ' checked';
                } elseif ($so['bill_to'] === 'contractor') {
                  $b_contractor = ' checked';
                }
                ?>

                <div class="checkbox"><input id="billing_addr_chk" type="checkbox" <?php echo $billing_checked; ?>><label for="billing_addr_chk"> Billing Information</label></div>
              </td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label class="c-input c-radio"><input id="bill_homeowner" <?php echo $b_homeowner; ?> name="bill_to" type="radio" value="homeowner"><span class="c-indicator"></span>Bill Homeowner</label></td>
              <td><label class="c-input c-radio"><input id="bill_contractor" <?php echo $b_contractor; ?> name="bill_to" type="radio" value="contractor"><span class="c-indicator"></span>Bill Contractor</label></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_contact">Billing Contact:</label></td>
              <td><input type="text" value="<?php echo $so['billing_contact']; ?>" name="billing_contact" class="c_input" placeholder="Billing Contact" id="billing_contact"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_landline">Billing Landline:</label></td>
              <td><input type="text" value="<?php echo $so['billing_landline']; ?>" name="billing_landline" class="c_input" placeholder="Billing Landline" id="billing_landline"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_cell">Billing Cell:</label></td>
              <td><input type="text" value="<?php echo $so['billing_cell']; ?>" name="billing_cell" class="c_input" placeholder="Billing Cell" id="billing_cell"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_addr">Billing Address:</label></td>
              <td><input type="text" value="<?php echo $so['billing_addr']; ?>" name="billing_addr" class="c_input" placeholder="Billing Address" id="billing_addr"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_city">Billing City:</label></td>
              <td><input type="text" value="<?php echo $so['billing_city']; ?>" name="billing_city" class="c_input" placeholder="Billing City" id="billing_city"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_state">Billing State:</label></td>
              <td><select class="c_input" id="billing_state" name="billing_state"><?php echo getStateOpts($so['billing_state']); ?></select></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_zip">Billing Zip:</label></td>
              <td><input type="text" value="<?php echo $so['billing_zip']; ?>" name="billing_zip" class="c_input" placeholder="Billing Zip" id="billing_zip"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td style="height:8px"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_account">ACH Account #:</label></td>
              <td><input type="text" value="<?php echo $so['billing_account']; ?>" name="billing_account" autocomplete="off" class="c_input" placeholder="ACH Account #" id="billing_account"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_routing">ACH Routing #:</label></td>
              <td><input type="text" value="<?php echo $so['billing_routing']; ?>" name="billing_routing" autocomplete="off" class="c_input" placeholder="ACH Routing #" id="billing_routing"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_cc_num">Card Number:</label></td>
              <td><input type="text" value="<?php echo $so['billing_cc_num']; ?>" name="billing_cc_num" autocomplete="off" class="c_input" placeholder="Credit Card #" id="billing_cc_num"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_routing">Card Expiration:</label></td>
              <td><input type="text" value="<?php echo $so['billing_cc_exp']; ?>" name="billing_cc_exp" autocomplete="off" class="c_input" placeholder="Exp. Date" id="billing_cc_exp"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><label for="billing_cc_ccv">Card CCV #:</label></td>
              <td><input type="text" value="<?php echo $so['billing_cc_ccv']; ?>" name="billing_cc_ccv" autocomplete="off" class="c_input" placeholder="CCV Code" id="billing_cc_ccv"></td>
            </tr>
            <tr>
              <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
            </tr>
            <tr>
              <td colspan="3"><h5>Associations</h5></td>
            </tr>
            <?php
            $contact_dropdown = null;

            $contact_qry = $dbconn->query('SELECT c.id, c.first_name, c.last_name, c.company_name, c2.description FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id ORDER BY c2.description, c.first_name, c.last_name ASC');

            if($contact_qry->num_rows > 0) {
              $contact_dropdown = "<select class='form-control pull-left add_contact_id ignoreSaveAlert' name='add_contact' style='width:50%;'><option value=''>Select</option>";

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
            $so_contacts_qry = $dbconn->query("SELECT c.*, c2.description FROM sales_order_contacts soc LEFT JOIN contact c ON soc.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE so_id = '{$so['id']}' ORDER BY c.first_name, c.last_name ASC");

            if($so_contacts_qry->num_rows > 0) {
              while($so_contacts = $so_contacts_qry->fetch_assoc()) {
                $name = !empty($so_contacts['first_name']) ? "{$so_contacts['first_name']} {$so_contacts['last_name']}" : $so_contacts['company_name'];

                echo "<tr><td colspan='3'><button type='button' class='btn waves-effect waves-light btn-danger remove_assigned_contact_so' style='margin:2px 0;' data-id='{$so_contacts['id']}'> <i class='zmdi zmdi-minus-circle'></i> </button> <a href='#' class='get_customer_info' data-view-id='{$so_contacts['id']}''>$name ({$so_contacts['description']})</a></td></tr>";
              }
            } else {
              echo "<tr><td colspan='3'><strong>No Associations</strong></td></tr>";
            }
            ?>
          <?php } ?>
        </table>
      </div>

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
        </div>
      </div>
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
</script>