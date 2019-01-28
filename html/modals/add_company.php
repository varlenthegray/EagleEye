<?php
require_once '../../includes/header_start.php';
?>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title" id="modalAddCustomerTitle">Add New</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-4">
          <table width="100%">
            <tr>
              <td><label for="new_type">New:</label></td>
              <td>
                <select class="c_input" id="new_type">
                  <option>Account</option>
                  <option>Contact</option>
                </select>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <div class="row new_container"><!-- AJAX loaded, onChange of select dropdown --></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary waves-effect waves-light new_save_button">Save</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  $("#new_type").change(function() {
    let selected = $(this).find(":selected").text();
    let saveBtn = $(".new_save_button");

    switch(selected) {
      case 'Account':
        saveBtn.attr("id", "add_new_company");

        $.get("/html/modals/new_popup/account.php", function(data) {
          $(".new_container").html(data);
        }).done(function() {
          crmCompany.init();

          $(function() {
            crmCompany.checkEmpty('shipping_', '#shipping_different', '.shipping_empty_hide');
            crmCompany.checkEmpty('billing_', '#billing_different', '.billing_empty_hide');
          });
        });

        break;
      case 'Contact':
        saveBtn.attr("id", "submit_new_contact");

        $.get("/html/modals/new_popup/contact.php", function(data) {
          $(".new_container").html(data);
        });

        break;
    }
  });

  $(function() {
    $("#new_type").trigger("change");
  });
</script>