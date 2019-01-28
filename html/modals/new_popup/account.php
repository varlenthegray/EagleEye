<?php
require_once '../../../includes/header_start.php';
?>

<div class="col-md-12">
  <form id="add_edit_company_form">
    <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
      <tr>
        <td colspan="2"><u><b>Account</b></u></td>
      </tr>
      <tr>
        <td><label for="company_name">Name:</label></td>
        <td><input type="text" value="" name="company_name" class="c_input" placeholder="Account Name" id="company_name" /></td>
      </tr>
      <tr>
        <td><label for="company_addr">Address:</label></td>
        <td><input type="text" value="" name="company_addr" class="c_input " placeholder="Account Address" id="company_addr" /></td>
      </tr>
      <tr>
        <td><label for="company_city">City:</label></td>
        <td><input type="text" value="" name="company_city" class="c_input" placeholder="Account City" id="company_city"></td>
      </tr>
      <tr>
        <td><label for="company_state">State:</label></td>
        <td><select class="c_input" id="company_state" name="company_state"><?php echo getStateOpts('NC'); ?></select></td>
      </tr>
      <tr>
        <td><label for="company_zip">Zip:</label></td>
        <td><input type="text" value="" name="company_zip" class="c_input" placeholder="Account Zip" id="company_zip"></td>
      </tr>
      <tr>
        <td><label for="company_email">Email:</label></td>
        <td><input type="text" value="" name="company_email" class="c_input" placeholder="Account Email" id="company_email"></td>
      </tr>
      <tr>
        <td><label for="company_landline">Landline:</label></td>
        <td><input type="text" value="" name="company_landline" class="c_input" placeholder="Account Phone" id="company_landline"></td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2"><b><u>Billing</u></b></td>
      </tr>
      <tr>
        <td><label for="company_billing_terms">Billing Type:</label></td>
        <td>
          <select id="company_billing_terms" class="c_input">
            <option>Distribution - 50/50</option>
            <option>Distribution - 100</option>
            <option>Retail - 50/40/10</option>
            <option>Wholesale - 50/50</option>
            <option>Distribution - 100</option>
          </select>
        </td>
      </tr>
      <tr>
        <td><label for="company_multiplier">Multiplier:</label></td>
        <td><input type="text" value="" maxlength="5" name="company_multiplier" autocomplete="no" class="c_input" placeholder="Multiplier" id="company_multiplier"></td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td><label for="company_payment_processor">Payment Method:</label></td>
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
        <td><input type="text" value="" name="ach_acct_1" autocomplete="no" class="c_input" placeholder="ACH Account Number" id="ach_acct_1"></td>
      </tr>
      <tr>
        <td><label for="ach_routing_1">ACH Routing:</label></td>
        <td><input type="text" value="" name="ach_routing_1" autocomplete="no" class="c_input" placeholder="ACH Routing Number" id="ach_routing_1"></td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td><label for="cc_num_1">CC:</label></td>
        <td><input type="text" value="" maxlength="16" name="cc_num_1" autocomplete="no" class="c_input" placeholder="CC Number" id="cc_num_1"></td>
      </tr>
      <tr>
        <td><label for="cc_exp_1">CC Exp:</label></td>
        <td><input type="text" value="" maxlength="8" name="cc_exp_1" autocomplete="no" class="c_input" placeholder="CC Expiration" id="cc_exp_1"></td>
      </tr>
      <tr>
        <td><label for="cc_ccv_1">CC CCV:</label></td>
        <td><input type="text" value="" maxlength="4" name="cc_ccv_1" autocomplete="no" class="c_input" placeholder="CC CCV" id="cc_ccv_1"></td>
      </tr>

      <tr>
        <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
      </tr>
      <tr>
        <td colspan="2"><div class="pull-left"><b><u>Shipping</u></b></div> <div style="margin-left:10px;float:left;" class="checkbox"><input id="shipping_different" class="ignoreSaveAlert" type="checkbox" value="1"><label for="shipping_different"> If Different</label></div></td>
      </tr>
      <tr class="shipping_empty_hide">
        <td><label for="company_shipping_addr">Address:</label></td>
        <td><input type="text" value="" name="company_shipping_addr" class="c_input" placeholder="Shipping Address" id="company_shipping_addr"></td>
      </tr>
      <tr class="shipping_empty_hide">
        <td><label for="company_shipping_city">City:</label></td>
        <td><input type="text" value="" name="company_shipping_city" class="c_input" placeholder="Shipping City" id="company_shipping_city"></td>
      </tr>
      <tr class="shipping_empty_hide">
        <td><label for="company_shipping_state">State:</label></td>
        <td><select class="c_input" id="company_shipping_state" name="company_shipping_state"><?php echo getStateOpts('NC'); ?></select></td>
      </tr>
      <tr class="shipping_empty_hide">
        <td><label for="company_shipping_zip">Zip:</label></td>
        <td><input type="text" value="" name="company_shipping_zip" class="c_input" placeholder="Shipping Zip" id="company_shipping_zip"></td>
      </tr>
      <tr class="shipping_empty_hide">
        <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
      </tr>
      <tr>
        <td colspan="2"><div class="pull-left"><b><u>Billing</u></b></div> <div style="margin-left:10px;float:left;" class="checkbox"><input id="billing_different" class="ignoreSaveAlert" type="checkbox" value="1"><label for="billing_different"> If Different</label></div></td>
      </tr>
      <tr class="billing_empty_hide">
        <td><label for="company_billing_addr">Address:</label></td>
        <td><input type="text" value="" name="company_billing_addr" class="c_input" placeholder="Billing Address" id="company_billing_addr"></td>
      </tr>
      <tr class="billing_empty_hide">
        <td><label for="company_billing_city">City:</label></td>
        <td><input type="text" value="" name="company_billing_city" class="c_input" placeholder="Billing City" id="company_billing_city"></td>
      </tr>
      <tr class="billing_empty_hide">
        <td><label for="company_billing_state">State:</label></td>
        <td><select class="c_input" id="company_billing_state" name="company_billing_state"><?php echo getStateOpts('NC'); ?></select></td>
      </tr>
      <tr class="billing_empty_hide">
        <td><label for="company_billing_zip">Zip:</label></td>
        <td><input type="text" value="" name="company_billing_zip" class="c_input" placeholder="Billing Zip" id="company_billing_zip"></td>
      </tr>
    </table>
  </form>
</div>