<?php
require '../../../includes/header_start.php';

$type = sanitizeInput($_REQUEST['type']);
$id = sanitizeInput($_REQUEST['id']);

if(!empty($id)) {
  if($type === 'folder') {
    $info_qry = $dbconn->query("SELECT * FROM pricing_categories WHERE id = $id");
  } elseif($type === 'item') {
    $info_qry = $dbconn->query("SELECT * FROM pricing_nomenclature WHERE id = $id");
  }
}

if($info_qry->num_rows > 0) {
  $info = $info_qry->fetch_assoc();
  $btnSave = 'Save';
  $add_modify = 'Modify';
} else {
  $btnSave = 'Add';
  $add_modify = 'Add';
}

$title = $type === 'folder' || $type === 'newSameFolder' || $type === 'newSubFolder' ? 'Category' : 'Item';
$nameValue = $type === 'folder' ? $info['name'] : $info['sku'];
?>

<style>
  .add_item_table {
    margin-top: 15px;
  }

  .add_item_table td {
    padding: 1px 4px;
  }
</style>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title"><?php echo "$add_modify $title"; ?></h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <form id="catalogAddEditItem" action="#">
          <div class="col-md-12">
            <table class="add_item_table">
              <tr>
                <td><label><?php echo $title; ?> Name:</label></td>
                <td><input type="text" class="form-control" name="title" placeholder='<?php echo $title; ?> Name' value='<?php echo $nameValue; ?>' /> </td>
              </tr>

              <?php
              if($type === 'item' || $type === 'addItem') {
                ?>
                <tr>
                  <td colspan="2" style="padding:0;">
                    <table>
                      <tr>
                        <td><label>Width:</label></td>
                        <td><label>Height:</label></td>
                        <td><label>Depth:</label></td>
                      </tr>
                      <tr>
                        <td><input type="text" class="form-control" name="width" placeholder="Width" value="<?php echo $info['width']; ?>"/></td>
                        <td><input type="text" class="form-control" name="height" placeholder="Height" value="<?php echo $info['height']; ?>"/></td>
                        <td><input type="text" class="form-control" name="depth" placeholder="Depth" value="<?php echo $info['depth']; ?>"/></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <!--<tr>
                  <td><label for="ai_hinge">Hinge:</label></td>
                  <td>
                    <select class="form-control" id="ai_hinge" name="hinge">
                      <option value="L">Left</option>
                      <option value="R">Right</option>
                      <option value="P">Pair</option>
                      <option value="N" selected>None</option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td><label>Description:</label></td>
                  <td><textarea class="form-control" name="description" placeholder="Description" rows="5"></textarea></td>
                </tr>
                <tr>
                  <td><label>Image:</label></td>
                  <td><input type="file" class="form-control" name="image" placeholder="Image"/></td>
                </tr>-->
                <?php
              }
              ?>
            </table>

            <!--<table class="add_item_table">
              <tr>
                <td width="10%"><label for="ai_pg_1">PG1:</label></td>
                <td width="40%"><input type="text" id="ai_pg_1" class="form-control" name="pg1" /> </td>
                <td width="10%"><label for="ai_pg_2">PG2:</label></td>
                <td width="40%"><input type="text" id="ai_pg_2" class="form-control" name="pg2" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_3">PG3:</label></td>
                <td><input type="text" id="ai_pg_3" class="form-control" name="pg3" /> </td>
                <td><label for="ai_pg_4">PG4:</label></td>
                <td><input type="text" id="ai_pg_4" class="form-control" name="pg4" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_5">PG5:</label></td>
                <td><input type="text" id="ai_pg_5" class="form-control" name="pg5" /> </td>
                <td><label for="ai_pg_6">PG6:</label></td>
                <td><input type="text" id="ai_pg_6" class="form-control" name="pg6" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_7">PG7:</label></td>
                <td><input type="text" id="ai_pg_7" class="form-control" name="pg7" /> </td>
                <td><label for="ai_pg_8">PG8:</label></td>
                <td><input type="text" id="ai_pg_8" class="form-control" name="pg8" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_9">PG9:</label></td>
                <td><input type="text" id="ai_pg_9" class="form-control" name="pg9" /> </td>
                <td><label for="ai_pg_10">PG10:</label></td>
                <td><input type="text" id="ai_pg_10" class="form-control" name="pg10" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_11">PG11:</label></td>
                <td><input type="text" id="ai_pg_11" class="form-control" name="pg11" /> </td>
                <td><label for="ai_pg_12">PG12:</label></td>
                <td><input type="text" id="ai_pg_12" class="form-control" name="pg12" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_13">PG13:</label></td>
                <td><input type="text" id="ai_pg_13" class="form-control" name="pg13" /> </td>
                <td><label for="ai_pg_14">PG14:</label></td>
                <td><input type="text" id="ai_pg_14" class="form-control" name="pg14" /> </td>
              </tr>
            </table>-->
          </div>
        </form>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>

      <button type="button" class="btn btn-primary waves-effect waves-light" data-type="<?php echo $type; ?>" id="<?php echo "modal{$btnSave}CatItemSubmit"; ?>"><?php echo $btnSave; ?></button>
    </div>
  </div>
</div>

<script>
  $(function() {

  });
</script>