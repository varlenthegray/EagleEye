<?php
require_once '../../../includes/header_start.php';
require_once '../classes/customer.php';
require_once '../../../includes/classes/dropdown_options.php';

use customer\customer;

$customer = new \customer\customer();
?>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title" id="modalAddCustomerTitle">Add New</h4>
    </div>
    <div class="modal-body">
      <div class="row new_container">
        <div class="col-md-12">
          <form id="add_new_form">
            <?php $customer->displayFields(); ?>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary waves-effect waves-light" id="add_new_save">Save</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  $(function() {
    crmCompany.init();
  });
</script>