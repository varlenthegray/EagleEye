<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 12/10/2018
 * Time: 2:53 PM
 */
require '../../includes/header_start.php';

//outputPHPErrs();

$type_id = sanitizeInput($_REQUEST['type_id']);
$contact_id = sanitizeInput($_REQUEST['contact_id']);
?>

<style>
  #contact_role {
    width: auto;
    display: inherit;
  }

  #custom_association {
    margin-top: 4px;
  }
</style>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title">Add Association</h4>
    </div>
    <div class="modal-body">
      <form id="contactAssociationForm" method="post" action="#">
        <div class="row">
          <div class="col-md-4 col-md-offset-4 text-md-center">
            <table width="100%">
              <tr>
                <td><label for="contact_role">How are they associated?</label></td>
              </tr>
              <tr>
                <td id="displayStdRole">
                  <select class="c_input" name="contact_role" id="contact_role">
                    <?php
                    $contact_ass_qry = $dbconn->query('SELECT ctc.associated_as FROM contact_to_contact ctc UNION SELECT ctso.associated_as FROM contact_to_sales_order ctso;');

                    if($contact_ass_qry->num_rows > 0) {
                      while($assoc = $contact_ass_qry->fetch_assoc()) {
                        echo "<option value='{$assoc['associated_as']}'>{$assoc['associated_as']}</option>";
                      }
                    } else {
                      echo "<option value='none' selected disabled>None defined yet</option>";
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <tr style="display:none;" id="displayCustomRole">
                <td><input type="text" class="c_input" name="custom_contact_role" placeholder="Custom Role" id="custom_contact_role" /></td>
              </tr>
              <tr>
                <td style="height:10px;"></td>
              </tr>
              <tr>
                <td>
                  <div class="checkbox">
                    <input id="custom_association" name="custom_association" class="ignoreSaveAlert" type="checkbox" value="1">
                    <label for="custom_association"> Custom</label>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary waves-effect waves-light" id="modalAddContactAssociation" data-type-id="<?php echo $type_id; ?>" data-contact-id="<?php echo $contact_id; ?>">Associate</button>
    </div>
  </div>
</div>

<script>
  // crmProject.contactMgr.contactAssociation();

  association.customAssociation();
  association.modalManager();
</script>