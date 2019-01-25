<?php
require_once '../../includes/header_start.php';

$title = 'Add';
$submit_btn = 'submit_new_contact';
$default_type = sanitizeInput($_REQUEST['default']);

if($_REQUEST['action'] === 'edit') {
  $id = sanitizeInput($_REQUEST['id']);

  $title = 'Edit';
  $submit_btn = 'update_contact';

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
    $shipping_state = !empty($contact['shipping_state']) ? $contact['shipping_state'] : 'NC';
    $shipping_zip = $contact['shipping_zip'];
    $billing_first_name = $contact['billing_first_name'];
    $billing_last_name = $contact['billing_last_name'];
    $billing_addr = $contact['billing_addr'];
    $billing_city = $contact['billing_city'];
    $billing_state = !empty($contact['billing_state']) ? $contact['billing_state'] : 'NC';
    $billing_zip = $contact['billing_zip'];

    $contact_id = "<input type='hidden' name='id' value='{$contact['id']}' />";

    // used in the delete button (data section)
    $output_name = empty($first_name) ? $company_name : "$first_name $last_name";
  }
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
      <h4 class="modal-title" id="modalAddCustomerTitle"><?php echo $title; ?> Contact</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <form id="contact_form">
            <?php echo $contact_id; ?>
            <table style="width:100%;margin-top:8px;">
              <tr>
                <td><label for="contact_type">Contact Type:</label></td>
                <td>
                  <select class="c_input" id="contact_type" name="contact_type">
                    <?php
                    $con_type_qry = $dbconn->query("SELECT * FROM contact_types WHERE permission_level <= {$_SESSION['permissions']['contact_permission']} ORDER BY description ASC;");

                    if($con_type_qry->num_rows > 0) {
                      while($con_type = $con_type_qry->fetch_assoc()) {
                        $selected = ($con_type['id'] === $type) ? 'selected' : null;

                        if($default_type === $con_type['description']) {
                          $selected = 'selected';
                        }

                        echo "<option value='{$con_type['id']}' $selected>{$con_type['description']}</option>";
                      }
                    } else {
                      echo "<option value='7'>General</option>";
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <tr style="display:<?php echo $dealer_display; ?>;">
                <td><label for="dealer_code">Dealer Code:</label></td>
                <td><input type="text" class="c_input" id="dealer_code" name="dealer_code" value="<?php echo $dealer_code; ?>" placeholder="Dealer Code" maxlength="4"></td>
              </tr>
              <tr style="height: 10px;">
                <td colspan="2"></td>
              </tr>
              <tr>
                <td><label for="first_name">First Name:</label></td>
                <td><input type="text" class="c_input" id="first_name" name="first_name" value="<?php echo $first_name; ?>" placeholder="First Name"></td>
              </tr>
              <tr>
                <td><label for="last_name">Last Name:</label></td>
                <td><input type="text" class="c_input" id="last_name" name="last_name" value="<?php echo $last_name; ?>" placeholder="Last Name"></td>
              </tr>
              <tr>
                <td><label for="company_name">Company Name:</label></td>
                <td><input type="text" class="c_input" id="company_name" name="company_name" value="<?php echo $company_name; ?>" placeholder="Company Name"></td>
              </tr>
              <tr>
                <td><label for="email">Email Address:</label></td>
                <td><input type="text" class="c_input" id="email" name="email" value="<?php echo $email; ?>" placeholder="Email Address"></td>
              </tr>
              <tr>
                <td><label for="cell">Cell Phone:</label></td>
                <td><input type="text" class="c_input" id="cell" name="cell" value="<?php echo $cell; ?>" placeholder="Cell Phone"></td>
              </tr>
              <tr>
                <td><label for="phone_2">Phone 2:</label></td>
                <td><input type="text" class="c_input" id="phone_2" name="phone_2" value="<?php echo $phone_2; ?>" placeholder="Phone 2"></td>
              </tr>
              <tr style="height: 10px;">
                <td colspan="2"></td>
              </tr>
              <tr>
                <td><label for="shipping_first_name">Shipping First Name:</label></td>
                <td><input type="text" class="c_input" id="shipping_first_name" name="shipping_first_name" value="<?php echo $shipping_first_name; ?>" placeholder="Shipping First Name"></td>
              </tr>
              <tr>
                <td><label for="shipping_last_name">Shipping Last Name:</label></td>
                <td><input type="text" class="c_input" id="shipping_last_name" name="shipping_last_name" value="<?php echo $shipping_last_name; ?>" placeholder="Shipping Last Name"></td>
              </tr>
              <tr>
                <td><label for="shipping_addr">Shipping Address:</label></td>
                <td><input type="text" class="c_input" id="shipping_addr" name="shipping_addr" value="<?php echo $shipping_addr; ?>" placeholder="Shipping Address"></td>
              </tr>
              <tr>
                <td><label for="shipping_city">Shipping City:</label></td>
                <td><input type="text" name="shipping_city" class="c_input" value="<?php echo $shipping_city; ?>" placeholder="Shipping City" id="shipping_city"></td>
              </tr>
              <tr>
                <td><label for="shipping_state">Shipping State:</label></td>
                <td><select class="c_input" id="shipping_state" name="shipping_state"><?php echo getStateOpts($shipping_state); ?></select></td>
              </tr>
              <tr>
                <td><label for="shipping_zip">Shipping Zip:</label></td>
                <td><input type="text" name="shipping_zip" class="c_input" value="<?php echo $shipping_city; ?>" placeholder="Shipping Zip" id="shipping_zip"></td>
              </tr>
              <tr style="height: 10px;">
                <td colspan="2"></td>
              </tr>
              <tr>
                <td><label for="billing_first_name">Billing First Name:</label></td>
                <td><input type="text" class="c_input" id="billing_first_name" name="billing_first_name" value="<?php echo $billing_first_name; ?>" placeholder="Billing First Name"></td>
              </tr>
              <tr>
                <td><label for="billing_last_name">Billing Last Name:</label></td>
                <td><input type="text" class="c_input" id="billing_last_name" name="billing_last_name" value="<?php echo $billing_last_name; ?>" placeholder="Billing Last Name"></td>
              </tr>
              <tr>
                <td><label for="billing_addr">Billing Address:</label></td>
                <td><input type="text" class="c_input" id="billing_addr" name="billing_addr" value="<?php echo $billing_addr; ?>" placeholder="Billing Address"></td>
              </tr>
              <tr>
                <td><label for="billing_city">Billing City:</label></td>
                <td><input type="text" name="billing_city" class="c_input" value="<?php echo $billing_city; ?>" placeholder="Billing City" id="billing_city"></td>
              </tr>
              <tr>
                <td><label for="billing_state">Billing State:</label></td>
                <td><select class="c_input" id="billing_state" name="billing_state"><?php echo getStateOpts($shipping_state); ?></select></td>
              </tr>
              <tr>
                <td><label for="billing_zip">Billing Zip:</label></td>
                <td><input type="text" name="billing_zip" class="c_input" value="<?php echo $billing_zip; ?>" placeholder="Billing Zip" id="billing_zip"></td>
              </tr>
              <?php
              if($_REQUEST['action'] === 'edit') {
                echo "<tr style='height: 10px;'><td colspan='3'></td></tr><tr><td colspan='3'><h5>Assigned Projects</h5></td></tr>";
                
                $so_contact_qry = $dbconn->query("SELECT * FROM contact_associations soc LEFT JOIN sales_order o ON soc.type_id = o.id WHERE contact_id = '$id'");
                
                if($so_contact_qry->num_rows > 0) {
                  while($so_contact = $so_contact_qry->fetch_assoc()) {
                    $so_list .= "<a href='#' id='{$so_contact['so_num']}' class='view_so_info'>{$so_contact['so_num']}</a>, ";
                  }

                  $so_list = rtrim($so_list, ', ');

                  echo "<tr><td colspan='3'>$so_list</td></tr>";
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