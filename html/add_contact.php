<?php
require_once("../includes/header_start.php");

$title = "Add";
$submit_btn = "submit_new_contact";
$default_type = sanitizeInput($_REQUEST['default']);

if($_REQUEST['action'] === 'edit') {
  $id = sanitizeInput($_REQUEST['id']);

  $title = "Edit";
  $submit_btn = "update_contact";

  $contact_qry = $dbconn->query("SELECT c.*, d.dealer_id FROM contact c LEFT JOIN dealers d ON c.dealer_id = d.id WHERE c.id = $id");

  if($contact_qry->num_rows === 1) {
    $contact = $contact_qry->fetch_assoc();

    $type = $contact['type'];
    $dealer_code = $contact['dealer_id'];
    $first_name = $contact['first_name'];
    $last_name = $contact['last_name'];
    $company_name = $contact['company_name'];
    $email = $contact['email'];
    $cell = $contact['cell'];
    $phone_2 = $contact['line_2'];
    $shipping_first_name = $contact['shipping_first_name'];
    $shipping_last_name = $contact['shipping_last_name'];
    $shipping_addr = $contact['shipping_addr'];
    $shipping_city = $contact['shipping_city'];
    $shipping_state = (!empty($contact['shipping_state'])) ? $contact['shipping_state'] : "NC";
    $shipping_zip = $contact['shipping_zip'];
    $billing_first_name = $contact['billing_first_name'];
    $billing_last_name = $contact['billing_last_name'];
    $billing_addr = $contact['billing_addr'];
    $billing_city = $contact['billing_city'];
    $billing_state = (!empty($contact['billing_state'])) ? $contact['billing_state'] : "NC";
    $billing_zip = $contact['billing_zip'];

    $contact_id = "<input type='hidden' name='id' value='{$contact['id']}' />";

    // used in the delete button (data section)
    $output_name = (empty($first_name)) ? $company_name : "$first_name $last_name";
  }
} else {
  $shipping_state = "NC";
  $billing_state = "NC";
}

$dealer_display = ($type === '2') ? "block" : "none";
?>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title" id="modalAddCustomerTitle"><?php echo $title; ?> Contact</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <form id="contact_form">
            <?php echo $contact_id; ?>
            <table style="width:100%;margin-top:8px;">
              <tr>
                <td>
                  <select class="form-control" id="contact_type" name="contact_type" data-toggle="tooltip" data-placement="top" title="" data-original-title="Contact Type">
                    <?php
                    $con_type_qry = $dbconn->query("SELECT * FROM contact_types WHERE permission_level <= {$_SESSION['permissions']['contact_permission']} ORDER BY description ASC;");

                    if($con_type_qry->num_rows > 0) {
                      while($con_type = $con_type_qry->fetch_assoc()) {
                        $selected = ($con_type['id'] === $type) ? "selected" : null;

                        if($default_type === $con_type['description']) {
                          $selected = "selected";
                        }

                        echo "<option value='{$con_type['id']}' $selected>{$con_type['description']}</option>";
                      }
                    } else {
                      echo "<option value='7'>General</option>";
                    }
                    ?>
                  </select>
                </td>
                <td><input type="text" class="form-control" id="dealer_code" name="dealer_code" value="<?php echo $dealer_code; ?>" placeholder="Dealer Code" maxlength="4" style="display:<?php echo $dealer_display; ?>;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Dealer Code"></td>
              </tr>
              <tr style="height: 10px;">
                <td colspan="3"></td>
              </tr>
              <tr>
                <td><input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $first_name; ?>" placeholder="First Name" data-toggle="tooltip" data-placement="top" title="" data-original-title="First Name"></td>
                <td><input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $last_name; ?>" placeholder="Last Name" data-toggle="tooltip" data-placement="top" title="" data-original-title="Last Name"></td>
                <td><input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo $company_name; ?>" placeholder="Company Name" data-toggle="tooltip" data-placement="top" title="" data-original-title="Company Name"></td>
              </tr>
              <tr>
                <td><input type="text" class="form-control" id="email" name="email" value="<?php echo $email; ?>" placeholder="Email Address" data-toggle="tooltip" data-placement="top" title="" data-original-title="Email Address"></td>
                <td><input type="text" class="form-control" id="cell" name="cell" value="<?php echo $cell; ?>" placeholder="Cell Phone" data-toggle="tooltip" data-placement="top" title="" data-original-title="Cell Phone"></td>
                <td><input type="text" class="form-control" id="phone_2" name="phone_2" value="<?php echo $phone_2; ?>" placeholder="Phone 2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Phone 2"></td>
              </tr>
              <tr style="height: 10px;">
                <td colspan="3"></td>
              </tr>
              <tr>
                <td><input type="text" class="form-control" id="shipping_first_name" name="shipping_first_name" value="<?php echo $shipping_first_name; ?>" placeholder="Shipping First Name" data-toggle="tooltip" data-placement="top" title="" data-original-title="Shipping First Name"></td>
                <td><input type="text" class="form-control" id="shipping_last_name" name="shipping_last_name" value="<?php echo $shipping_last_name; ?>" placeholder="Shipping Last Name" data-toggle="tooltip" data-placement="top" title="" data-original-title="Shipping Last Name"></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="3"><input type="text" class="form-control" id="shipping_addr" name="shipping_addr" value="<?php echo $shipping_addr; ?>" placeholder="Shipping Address" data-toggle="tooltip" data-placement="top" title="" data-original-title="Shipping Address"></td>
              </tr>
              <tr>
                <td><input type="text" name="shipping_city" class="form-control" value="<?php echo $shipping_city; ?>" placeholder="Shipping City" id="shipping_city" data-toggle="tooltip" data-placement="top" title="" data-original-title="Shipping City"></td>
                <td>
                  <select class="form-control" id="shipping_state" name="shipping_state" data-toggle="tooltip" data-placement="top" title="" data-original-title="Shipping State">
                    <option value="AL" <?php echo ($shipping_state === 'AL') ? "selected" : null; ?>>Alabama</option>
                    <option value="AK" <?php echo ($shipping_state === 'AK') ? "selected" : null; ?>>Alaska</option>
                    <option value="AR" <?php echo ($shipping_state === 'AR') ? "selected" : null; ?>>Arkansas</option>
                    <option value="CA" <?php echo ($shipping_state === 'CA') ? "selected" : null; ?>>California</option>
                    <option value="CO" <?php echo ($shipping_state === 'CO') ? "selected" : null; ?>>Colorado</option>
                    <option value="CT" <?php echo ($shipping_state === 'CT') ? "selected" : null; ?>>Connecticut</option>
                    <option value="DE" <?php echo ($shipping_state === 'DE') ? "selected" : null; ?>>Delaware</option>
                    <option value="FL" <?php echo ($shipping_state === 'FL') ? "selected" : null; ?>>Florida</option>
                    <option value="GA" <?php echo ($shipping_state === 'GA') ? "selected" : null; ?>>Georgia</option>
                    <option value="HI" <?php echo ($shipping_state === 'HI') ? "selected" : null; ?>>Hawaii</option>
                    <option value="ID" <?php echo ($shipping_state === 'ID') ? "selected" : null; ?>>Idaho</option>
                    <option value="IL" <?php echo ($shipping_state === 'IL') ? "selected" : null; ?>>Illinois</option>
                    <option value="IN" <?php echo ($shipping_state === 'IN') ? "selected" : null; ?>>Indiana</option>
                    <option value="IA" <?php echo ($shipping_state === 'IA') ? "selected" : null; ?>>Iowa</option>
                    <option value="KS" <?php echo ($shipping_state === 'KS') ? "selected" : null; ?>>Kansas</option>
                    <option value="KY" <?php echo ($shipping_state === 'KY') ? "selected" : null; ?>>Kentucky</option>
                    <option value="LA" <?php echo ($shipping_state === 'LA') ? "selected" : null; ?>>Louisiana</option>
                    <option value="ME" <?php echo ($shipping_state === 'ME') ? "selected" : null; ?>>Maine</option>
                    <option value="MD" <?php echo ($shipping_state === 'MD') ? "selected" : null; ?>>Maryland</option>
                    <option value="MA" <?php echo ($shipping_state === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                    <option value="MI" <?php echo ($shipping_state === 'MI') ? "selected" : null; ?>>Michigan</option>
                    <option value="MN" <?php echo ($shipping_state === 'MN') ? "selected" : null; ?>>Minnesota</option>
                    <option value="MS" <?php echo ($shipping_state === 'MS') ? "selected" : null; ?>>Mississippi</option>
                    <option value="MO" <?php echo ($shipping_state === 'MO') ? "selected" : null; ?>>Missouri</option>
                    <option value="MT" <?php echo ($shipping_state === 'MT') ? "selected" : null; ?>>Montana</option>
                    <option value="NE" <?php echo ($shipping_state === 'NE') ? "selected" : null; ?>>Nebraska</option>
                    <option value="NV" <?php echo ($shipping_state === 'NV') ? "selected" : null; ?>>Nevada</option>
                    <option value="NH" <?php echo ($shipping_state === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                    <option value="NJ" <?php echo ($shipping_state === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                    <option value="NM" <?php echo ($shipping_state === 'NM') ? "selected" : null; ?>>New Mexico</option>
                    <option value="NY" <?php echo ($shipping_state === 'NY') ? "selected" : null; ?>>New York</option>
                    <option value="NC" <?php echo ($shipping_state === 'NC') ? "selected" : null; ?>>North Carolina</option>
                    <option value="ND" <?php echo ($shipping_state === 'ND') ? "selected" : null; ?>>North Dakota</option>
                    <option value="OH" <?php echo ($shipping_state === 'OH') ? "selected" : null; ?>>Ohio</option>
                    <option value="OK" <?php echo ($shipping_state === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                    <option value="OR" <?php echo ($shipping_state === 'OR') ? "selected" : null; ?>>Oregon</option>
                    <option value="PA" <?php echo ($shipping_state === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                    <option value="RI" <?php echo ($shipping_state === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                    <option value="SC" <?php echo ($shipping_state === 'SC') ? "selected" : null; ?>>South Carolina</option>
                    <option value="SD" <?php echo ($shipping_state === 'SD') ? "selected" : null; ?>>South Dakota</option>
                    <option value="TN" <?php echo ($shipping_state === 'TN') ? "selected" : null; ?>>Tennessee</option>
                    <option value="TX" <?php echo ($shipping_state === 'TX') ? "selected" : null; ?>>Texas</option>
                    <option value="UT" <?php echo ($shipping_state === 'UT') ? "selected" : null; ?>>Utah</option>
                    <option value="VT" <?php echo ($shipping_state === 'VT') ? "selected" : null; ?>>Vermont</option>
                    <option value="VA" <?php echo ($shipping_state === 'VA') ? "selected" : null; ?>>Virginia</option>
                    <option value="WA" <?php echo ($shipping_state === 'WA') ? "selected" : null; ?>>Washington</option>
                    <option value="WV" <?php echo ($shipping_state === 'WV') ? "selected" : null; ?>>West Virginia</option>
                    <option value="WI" <?php echo ($shipping_state === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                    <option value="WY" <?php echo ($shipping_state === 'WY') ? "selected" : null; ?>>Wyoming</option>
                  </select>
                </td>
                <td><input type="text" name="shipping_zip" class="form-control" value="<?php echo $shipping_city; ?>" placeholder="Shipping Zip" id="shipping_zip" data-toggle="tooltip" data-placement="top" title="" data-original-title="Shipping Zip"></td>
              </tr>
              <tr style="height: 10px;">
                <td colspan="3"></td>
              </tr>
              <tr>
                <td><input type="text" class="form-control" id="billing_first_name" name="billing_first_name" value="<?php echo $billing_first_name; ?>" placeholder="Billing First Name" data-toggle="tooltip" data-placement="top" title="" data-original-title="Billing First Name"></td>
                <td><input type="text" class="form-control" id="billing_last_name" name="billing_last_name" value="<?php echo $billing_last_name; ?>" placeholder="Billing Last Name" data-toggle="tooltip" data-placement="top" title="" data-original-title="Billing Last Name"></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="3"><input type="text" class="form-control" id="billing_addr" name="billing_addr" value="<?php echo $billing_addr; ?>" placeholder="Billing Address" data-toggle="tooltip" data-placement="top" title="" data-original-title="Billing Address"></td>
              </tr>
              <tr>
                <td><input type="text" name="billing_city" class="form-control" value="<?php echo $billing_city; ?>" placeholder="Billing City" id="billing_city" data-toggle="tooltip" data-placement="top" title="" data-original-title="Billing City"></td>
                <td>
                  <select class="form-control" id="billing_state" name="billing_state" data-toggle="tooltip" data-placement="top" title="" data-original-title="Billing State">
                    <option value="AL" <?php echo ($billing_state === 'AL') ? "selected" : null; ?>>Alabama</option>
                    <option value="AK" <?php echo ($billing_state === 'AK') ? "selected" : null; ?>>Alaska</option>
                    <option value="AR" <?php echo ($billing_state === 'AR') ? "selected" : null; ?>>Arkansas</option>
                    <option value="CA" <?php echo ($billing_state === 'CA') ? "selected" : null; ?>>California</option>
                    <option value="CO" <?php echo ($billing_state === 'CO') ? "selected" : null; ?>>Colorado</option>
                    <option value="CT" <?php echo ($billing_state === 'CT') ? "selected" : null; ?>>Connecticut</option>
                    <option value="DE" <?php echo ($billing_state === 'DE') ? "selected" : null; ?>>Delaware</option>
                    <option value="FL" <?php echo ($billing_state === 'FL') ? "selected" : null; ?>>Florida</option>
                    <option value="GA" <?php echo ($billing_state === 'GA') ? "selected" : null; ?>>Georgia</option>
                    <option value="HI" <?php echo ($billing_state === 'HI') ? "selected" : null; ?>>Hawaii</option>
                    <option value="ID" <?php echo ($billing_state === 'ID') ? "selected" : null; ?>>Idaho</option>
                    <option value="IL" <?php echo ($billing_state === 'IL') ? "selected" : null; ?>>Illinois</option>
                    <option value="IN" <?php echo ($billing_state === 'IN') ? "selected" : null; ?>>Indiana</option>
                    <option value="IA" <?php echo ($billing_state === 'IA') ? "selected" : null; ?>>Iowa</option>
                    <option value="KS" <?php echo ($billing_state === 'KS') ? "selected" : null; ?>>Kansas</option>
                    <option value="KY" <?php echo ($billing_state === 'KY') ? "selected" : null; ?>>Kentucky</option>
                    <option value="LA" <?php echo ($billing_state === 'LA') ? "selected" : null; ?>>Louisiana</option>
                    <option value="ME" <?php echo ($billing_state === 'ME') ? "selected" : null; ?>>Maine</option>
                    <option value="MD" <?php echo ($billing_state === 'MD') ? "selected" : null; ?>>Maryland</option>
                    <option value="MA" <?php echo ($billing_state === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                    <option value="MI" <?php echo ($billing_state === 'MI') ? "selected" : null; ?>>Michigan</option>
                    <option value="MN" <?php echo ($billing_state === 'MN') ? "selected" : null; ?>>Minnesota</option>
                    <option value="MS" <?php echo ($billing_state === 'MS') ? "selected" : null; ?>>Mississippi</option>
                    <option value="MO" <?php echo ($billing_state === 'MO') ? "selected" : null; ?>>Missouri</option>
                    <option value="MT" <?php echo ($billing_state === 'MT') ? "selected" : null; ?>>Montana</option>
                    <option value="NE" <?php echo ($billing_state === 'NE') ? "selected" : null; ?>>Nebraska</option>
                    <option value="NV" <?php echo ($billing_state === 'NV') ? "selected" : null; ?>>Nevada</option>
                    <option value="NH" <?php echo ($billing_state === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                    <option value="NJ" <?php echo ($billing_state === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                    <option value="NM" <?php echo ($billing_state === 'NM') ? "selected" : null; ?>>New Mexico</option>
                    <option value="NY" <?php echo ($billing_state === 'NY') ? "selected" : null; ?>>New York</option>
                    <option value="NC" <?php echo ($billing_state === 'NC') ? "selected" : null; ?>>North Carolina</option>
                    <option value="ND" <?php echo ($billing_state === 'ND') ? "selected" : null; ?>>North Dakota</option>
                    <option value="OH" <?php echo ($billing_state === 'OH') ? "selected" : null; ?>>Ohio</option>
                    <option value="OK" <?php echo ($billing_state === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                    <option value="OR" <?php echo ($billing_state === 'OR') ? "selected" : null; ?>>Oregon</option>
                    <option value="PA" <?php echo ($billing_state === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                    <option value="RI" <?php echo ($billing_state === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                    <option value="SC" <?php echo ($billing_state === 'SC') ? "selected" : null; ?>>South Carolina</option>
                    <option value="SD" <?php echo ($billing_state === 'SD') ? "selected" : null; ?>>South Dakota</option>
                    <option value="TN" <?php echo ($billing_state === 'TN') ? "selected" : null; ?>>Tennessee</option>
                    <option value="TX" <?php echo ($billing_state === 'TX') ? "selected" : null; ?>>Texas</option>
                    <option value="UT" <?php echo ($billing_state === 'UT') ? "selected" : null; ?>>Utah</option>
                    <option value="VT" <?php echo ($billing_state === 'VT') ? "selected" : null; ?>>Vermont</option>
                    <option value="VA" <?php echo ($billing_state === 'VA') ? "selected" : null; ?>>Virginia</option>
                    <option value="WA" <?php echo ($billing_state === 'WA') ? "selected" : null; ?>>Washington</option>
                    <option value="WV" <?php echo ($billing_state === 'WV') ? "selected" : null; ?>>West Virginia</option>
                    <option value="WI" <?php echo ($billing_state === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                    <option value="WY" <?php echo ($billing_state === 'WY') ? "selected" : null; ?>>Wyoming</option>
                  </select>
                </td>
                <td><input type="text" name="billing_zip" class="form-control" value="<?php echo $billing_zip; ?>" placeholder="Billing Zip" id="billing_zip" data-toggle="tooltip" data-placement="top" title="" data-original-title="Billing Zip"></td>
              </tr>
              <?php
              if($_REQUEST['action'] === 'edit') {
                echo "<tr style='height: 10px;'><td colspan='3'></td></tr><tr><td colspan='3'><h5>Assigned Projects</h5></td></tr>";
                
                $so_contact_qry = $dbconn->query("SELECT * FROM sales_order_contacts soc LEFT JOIN sales_order o ON soc.so_id = o.id WHERE contact_id = '$id'");
                
                if($so_contact_qry->num_rows > 0) {
                  while($so_contact = $so_contact_qry->fetch_assoc()) {
                    echo "<tr><td colspan='3'><a href='#' id='{$so_contact['so_num']}' class='view_so_info'>{$so_contact['so_num']}</a></td></tr>";
                  }
                } else {
                  echo "<tr><td colspan='3'><em>None Assigned</em></td></tr>";
                }
              }
              ?>
            </table>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <?php echo ($_REQUEST['action'] === 'edit' && $bouncer->validate('delete_contact')) ? "<button type='button' class='btn btn-danger waves-effect pull-left' id='delete_contact' data-name='$output_name' data-contact-id='$id' data-dismiss='modal'>Delete</button>": null; ?>
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary waves-effect waves-light" id="<?php echo $submit_btn; ?>">Save</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  $('[data-toggle="tooltip"]').tooltip({
    trigger: 'focus'
  })
</script>