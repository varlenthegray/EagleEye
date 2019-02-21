<?php
require_once '../../../includes/header_start.php';
require_once '../../../includes/classes/dropdown_options.php';

use DropdownOpts\dropdown_options;
$drop_opts = new dropdown_options();
?>

<div class="col-md-12">
  <form id="contact_form">
    <table style="width:100%;margin-top:8px;">
      <tr>
        <td colspan="2"><u><b>Contact</b></u></td>
      </tr>
      <tr>
        <td><label for="first_name">First Name:</label></td>
        <td><input type="text" class="c_input" id="first_name" name="first_name" value="" placeholder="First Name"></td>
      </tr>
      <tr>
        <td><label for="last_name">Last Name:</label></td>
        <td><input type="text" class="c_input" id="last_name" name="last_name" value="" placeholder="Last Name"></td>
      </tr>
      <tr>
        <td><label for="email">Email Address:</label></td>
        <td><input type="text" class="c_input" id="email" name="email" value="" placeholder="Email Address"></td>
      </tr>
      <tr>
        <td><label for="cell">Cell Phone:</label></td>
        <td><input type="text" class="c_input" id="cell" name="cell" value="" placeholder="Cell Phone"></td>
      </tr>
      <tr>
        <td><label for="phone_2">Phone 2:</label></td>
        <td><input type="text" class="c_input" id="phone_2" name="phone_2" value="" placeholder="Phone 2"></td>
      </tr>
      <tr style="height: 10px;">
        <td colspan="2"></td>
      </tr>
      <tr>
        <td colspan="2"><u><b>Shipping</b></u></td>
      </tr>
      <tr>
        <td><label for="shipping_addr">Address:</label></td>
        <td><input type="text" class="c_input" id="shipping_addr" name="shipping_addr" value="" placeholder="Shipping Address"></td>
      </tr>
      <tr>
        <td><label for="shipping_city">City:</label></td>
        <td><input type="text" name="shipping_city" class="c_input" value="" placeholder="Shipping City" id="shipping_city"></td>
      </tr>
      <tr>
        <td><label for="shipping_state">State:</label></td>
        <td><select class="c_input" id="shipping_state" name="shipping_state"><?php echo $drop_opts->getStateOpts('NC'); ?></select></td>
      </tr>
      <tr>
        <td><label for="shipping_zip">Zip:</label></td>
        <td><input type="text" name="shipping_zip" class="c_input" value="" placeholder="Shipping Zip" id="shipping_zip"></td>
      </tr>
      <tr style="height: 10px;">
        <td colspan="2"></td>
      </tr>
      <tr>
        <td colspan="2"><u><b>Billing</b></u></td>
      </tr>
      <tr>
        <td><label for="billing_addr">Address:</label></td>
        <td><input type="text" class="c_input" id="billing_addr" name="billing_addr" value="" placeholder="Billing Address"></td>
      </tr>
      <tr>
        <td><label for="billing_city">City:</label></td>
        <td><input type="text" name="billing_city" class="c_input" value="" placeholder="Billing City" id="billing_city"></td>
      </tr>
      <tr>
        <td><label for="billing_state">State:</label></td>
        <td><select class="c_input" id="billing_state" name="billing_state"><?php echo $drop_opts->getStateOpts('NC'); ?></select></td>
      </tr>
      <tr>
        <td><label for="billing_zip">Zip:</label></td>
        <td><input type="text" name="billing_zip" class="c_input" value="" placeholder="Billing Zip" id="billing_zip"></td>
      </tr>
    </table>
  </form>
</div>