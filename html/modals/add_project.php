<?php
require_once '../../includes/header_start.php';

$company_id = sanitizeInput($_REQUEST['id']);

$company_qry = $dbconn->query("SELECT * FROM contact_company WHERE id = $company_id");
$company = $company_qry->fetch_assoc();

if(!(bool)$_SESSION['userInfo']['dealer']) {
  $qry = $dbconn->query("SELECT DISTINCT so_num FROM sales_order WHERE so_num REGEXP '^[0-9]+$' ORDER BY so_num DESC LIMIT 0,1");

  if($qry->num_rows > 0) {
    $result = $qry->fetch_assoc();

    $next_so = $result['so_num'] + 1;
  } else {
    $next_so = 1;
  }
} else {
  $qry = $dbconn->query('SELECT id FROM sales_order ORDER BY id DESC LIMIT 0, 1;');
  $result = $qry->fetch_assoc();

  $next_so = 'D' . ($result['id'] + 1);
}
?>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title">Add Project to <?php echo $company['name'] ?></h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <form id="add_project_form">
            <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
              <tr>
                <td colspan="2"><b><u>Project</u></b></td>
              </tr>
              <tr>
                <td><label for="project_name">Project/SO #:</label></td>
                <td><input type="text" name="project_num" value="<?php echo $next_so; ?>" class="c_input" placeholder="Project/SO Number" id="project_num" /></td>
              </tr>
              <tr>
                <td><label for="project_name">Name:</label></td>
                <td><input type="text" name="project_name" class="c_input" placeholder="Project Name" id="project_name" /></td>
              </tr>
              <tr>
                <td><label for="project_addr">Address:</label></td>
                <td><input type="text" name="project_addr" class="c_input " placeholder="Project Address" id="project_addr" /></td>
              </tr>
              <tr>
                <td><label for="project_city">City:</label></td>
                <td><input type="text" name="project_city" class="c_input" placeholder="Project City" id="project_city"></td>
              </tr>
              <tr>
                <td><label for="project_state">State:</label></td>
                <td><select class="c_input" id="project_state" name="project_state"><?php echo getStateOpts(null); ?></select></td>
              </tr>
              <tr>
                <td><label for="project_zip">Zip:</label></td>
                <td><input type="text" name="project_zip" class="c_input" placeholder="Project Zip" id="project_zip"></td>
              </tr>
              <tr>
                <td><label for="project_landline">Landline:</label></td>
                <td><input type="text" name="project_landline" class="c_input" placeholder="Project Landline" id="project_landline"></td>
              </tr>
            </table>

            <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
          </form>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary waves-effect waves-light" id="modalAddProjectSave">Add Project</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->