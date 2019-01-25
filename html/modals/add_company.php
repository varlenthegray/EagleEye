<?php
require_once '../../includes/header_start.php';

$title = 'Add New';
$submit_btn = 'add_new_company';
$default_type = sanitizeInput($_REQUEST['default']);

if($_REQUEST['action'] === 'edit') {
  $id = sanitizeInput($_REQUEST['id']);

  $title = 'Edit';
  $submit_btn = 'update_company';

  $contact_qry = $dbconn->query("SELECT c.*, d.dealer_id FROM contact c LEFT JOIN dealers d ON c.dealer_id = d.id WHERE c.id = $id");
} else {
  $shipping_state = 'NC';
  $billing_state = 'NC';
}

$dealer_display = ($type === '2') ? 'block' : 'none';
?>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title" id="modalAddCustomerTitle"><?php echo $title; ?> Contact/Account</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <form id="add_edit_company_form">
            <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
              <tr>
                <td colspan="2"><u><b>Company</b></u></td>
              </tr>
              <tr>
                <td><label for="company_name">Name:</label></td>
                <td><input type="text" value="<?php echo $so['company_name']; ?>" name="company_name" class="c_input" placeholder="Company Name" id="company_name" /></td>
              </tr>
              <tr>
                <td><label for="company_addr">Address:</label></td>
                <td><input type="text" value="<?php echo $so['company_addr']; ?>" name="company_addr" class="c_input " placeholder="Company Address" id="company_addr" /></td>
              </tr>
              <tr>
                <td><label for="company_city">City:</label></td>
                <td><input type="text" value="<?php echo $so['company_city']; ?>" name="company_city" class="c_input" placeholder="Company City" id="company_city"></td>
              </tr>
              <tr>
                <td><label for="company_state">State:</label></td>
                <td><select class="c_input" id="company_state" name="company_state"><?php echo getStateOpts($so['company_state']); ?></select></td>
              </tr>
              <tr>
                <td><label for="company_zip">Zip:</label></td>
                <td><input type="text" value="<?php echo $so['company_zip']; ?>" name="company_zip" class="c_input" placeholder="Company Zip" id="company_zip"></td>
              </tr>
              <tr>
                <td><label for="company_email">Email:</label></td>
                <td><input type="text" value="<?php echo $so['company_zip']; ?>" name="company_email" class="c_input" placeholder="Company Email" id="company_email"></td>
              </tr>
              <tr>
                <td><label for="company_landline">Landline:</label></td>
                <td><input type="text" value="<?php echo $so['company_landline']; ?>" name="company_landline" class="c_input" placeholder="Company Landline" id="company_landline"></td>
              </tr>
              <tr>
                <td colspan="2"><b><u>Billing</u></b></td>
              </tr>
              <tr>
                <td><label for="company_payment_processor">Payment Processor:</label></td>
                <td>
                  <select class="c_input" id="company_payment_processor" name="company_payment_processor">
                    <option>Stripe</option>
                    <option>Square</option>
                    <option>Bank</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td><label for="ach_acct_1">ACH Account:</label></td>
                <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="ach_acct_1" autocomplete="no" class="c_input" placeholder="ACH Account Number" id="ach_acct_1"></td>
              </tr>
              <tr>
                <td><label for="ach_routing_1">ACH Routing:</label></td>
                <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="ach_routing_1" autocomplete="no" class="c_input" placeholder="ACH Routing Number" id="ach_routing_1"></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td><label for="cc_num_1">CC:</label></td>
                <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="16" name="cc_num_1" autocomplete="no" class="c_input" placeholder="CC Number" id="cc_num_1"></td>
              </tr>
              <tr>
                <td><label for="cc_exp_1">CC Exp:</label></td>
                <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="8" name="cc_exp_1" autocomplete="no" class="c_input" placeholder="CC Expiration" id="cc_exp_1"></td>
              </tr>
              <tr>
                <td><label for="cc_ccv_1">CC CCV:</label></td>
                <td><input type="text" value="<?php echo $so['project_landline']; ?>" maxlength="4" name="cc_ccv_1" autocomplete="no" class="c_input" placeholder="CC CCV" id="cc_ccv_1"></td>
              </tr>
              <tr>
                <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
              </tr>
              <tr>
                <td colspan="2"><div class="pull-left"><b><u>Shipping</u></b></div> <div style="margin-left:10px;float:left;" class="checkbox"><input id="shipping_different" class="ignoreSaveAlert" type="checkbox" value="1"><label for="shipping_different"> Different</label></div></td>
              </tr>
              <tr class="shipping_empty_hide">
                <td><label for="company_shipping_addr">Address:</label></td>
                <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="company_shipping_addr" class="c_input" placeholder="Shipping Address" id="company_shipping_addr"></td>
              </tr>
              <tr class="shipping_empty_hide">
                <td><label for="company_shipping_city">City:</label></td>
                <td><input type="text" value="<?php echo $so['project_city']; ?>" name="company_shipping_city" class="c_input" placeholder="Shipping City" id="company_shipping_city"></td>
              </tr>
              <tr class="shipping_empty_hide">
                <td><label for="company_shipping_state">State:</label></td>
                <td><select class="c_input" id="company_shipping_state" name="company_shipping_state"><?php echo getStateOpts($so['project_state']); ?></select></td>
              </tr>
              <tr class="shipping_empty_hide">
                <td><label for="company_shipping_zip">Zip:</label></td>
                <td><input type="text" value="<?php echo $so['project_zip']; ?>" name="company_shipping_zip" class="c_input" placeholder="Shipping Zip" id="company_shipping_zip"></td>
              </tr>
              <tr class="shipping_empty_hide">
                <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
              </tr>
              <tr>
                <td colspan="2"><div class="pull-left"><b><u>Billing</u></b></div> <div style="margin-left:10px;float:left;" class="checkbox"><input id="billing_different" class="ignoreSaveAlert" type="checkbox" value="1"><label for="billing_different"> Different</label></div></td>
              </tr>
              <tr class="billing_empty_hide">
                <td><label for="company_billing_addr">Address:</label></td>
                <td><input type="text" value="<?php echo $so['project_landline']; ?>" name="company_billing_addr" class="c_input" placeholder="Billing Address" id="company_billing_addr"></td>
              </tr>
              <tr class="billing_empty_hide">
                <td><label for="company_billing_city">City:</label></td>
                <td><input type="text" value="<?php echo $so['project_city']; ?>" name="company_billing_city" class="c_input" placeholder="Billing City" id="company_billing_city"></td>
              </tr>
              <tr class="billing_empty_hide">
                <td><label for="company_billing_state">State:</label></td>
                <td><select class="c_input" id="company_billing_state" name="company_billing_state"><?php echo getStateOpts($so['project_state']); ?></select></td>
              </tr>
              <tr class="billing_empty_hide">
                <td><label for="company_billing_zip">Zip:</label></td>
                <td><input type="text" value="<?php echo $so['project_zip']; ?>" name="company_billing_zip" class="c_input" placeholder="Billing Zip" id="company_billing_zip"></td>
              </tr>
            </table>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary waves-effect waves-light" id="<?php echo $submit_btn; ?>">Save</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  crmCompany.init();

  $(function() {
    crmCompany.checkEmpty('shipping_', '#shipping_different', '.shipping_empty_hide');
    crmCompany.checkEmpty('billing_', '#billing_different', '.billing_empty_hide');
  });
</script>