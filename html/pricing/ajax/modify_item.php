<?php
require '../../../includes/header_start.php';

$type = sanitizeInput($_REQUEST['type']);
$id = sanitizeInput($_REQUEST['id']);

if(!empty($id)) {
  if($type === 'folder') {
    $info_qry = $dbconn->query("SELECT pc.*, pnd.description FROM pricing_categories pc LEFT JOIN pricing_nomenclature_details pnd on pc.description_id = pnd.id WHERE pc.id = $id");
  } elseif($type === 'item') {
    $info_qry = $dbconn->query("SELECT pn.*, pnd.description, pnd.image_path, pnd.title FROM pricing_nomenclature pn LEFT JOIN pricing_nomenclature_details pnd on pn.description_id = pnd.id WHERE pn.id = $id");
  }
}

if($info_qry->num_rows > 0) {
  $info = $info_qry->fetch_assoc();
  $btnSave = 'Save';
  $add_modify = 'Modify';
  $current_img = 'checked';
  $recent_img = null;
} else {
  $btnSave = 'Add';
  $add_modify = 'Add';
  $current_img = 'disabled';
  $recent_img = 'checked';
}

$title = $type === 'folder' || $type === 'newSameFolder' || $type === 'newSubFolder' ? 'Category' : 'Item';
$nameValue = $type === 'folder' ? $info['name'] : $info['title'];
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
            <table class="add_item_table" width="100%">
              <colgroup>
                <col width="110px">
                <col width="*">
              </colgroup>
              <tbody>
                <tr>
                  <td><label><?php echo $title; ?> Name:</label></td>
                  <td><input type="text" class="c_input" name="name" placeholder='<?php echo $title; ?> Name' value='<?php echo $nameValue; ?>' /> </td>
                </tr>

                <?php
                if($type === 'folder') {
                  // https://stackoverflow.com/questions/2441821/getting-all-parent-rows-in-one-sql-query
                  $cat_qry = $dbconn->query("SELECT T2.id, T2.name, T2.description_id FROM (
                  SELECT @r AS _id, (SELECT @r := parent FROM pricing_categories WHERE id = _id) AS parent_id, @l := @l + 1 AS lvl
                  FROM (SELECT @r := $id, @l := 0) vars, pricing_categories h WHERE @r <> 0) T1 
                  JOIN pricing_categories T2 ON T1._id = T2.id ORDER BY T1.lvl DESC");

                  while($cat = $cat_qry->fetch_assoc()) {
                    if(!empty($cat['description_id'])) {
                      $desc_id = $dbconn->query("SELECT * FROM pricing_nomenclature_details WHERE id = {$cat['description_id']}");
                      $desc = $desc_id->fetch_assoc();
                    } else {
                      $desc['description'] = '<i>None Specified</i>';
                    }

                    if($cat['id'] !== $info['id']) {
                      echo "<tr>
                      <td>
                        <label>'{$cat['name']}' Description:</label>
                        <div class='checkbox checkbox-primary'><input id=\"desc_enabled_{$cat['id']}\" name=\"desc_enabled[]\" type='checkbox' value=\"{$cat['id']}\" checked><label for=\"desc_enabled_{$cat['id']}\"> Enabled</label></div>
                      </td>
                      <td><div style='min-height:91px;'>{$desc['description']}</div></td>
                    </tr>";
                    }
                  }
                }
                ?>

                <tr>
                  <td>
                    <label>This Description:</label>
                    <div class='checkbox checkbox-primary'><input id="this_desc_enabled" name="desc_enabled[]" type='checkbox' value="<?php echo $info['id']; ?>" checked><label for="this_desc_enabled"> Enabled</label></div>
                  </td>
                  <td><textarea class="c_input" name="description" placeholder="Description" style="min-height:91px;"><?php echo $info['description']; ?></textarea></td>
                </tr>
                <?php if($type === 'item' || $type === 'addItem') { ?>
                  <tr>
                    <td><label>Nomenclature:</label></td>
                    <td><input type="text" class="c_input" name="sku" placeholder='<?php echo $info['sku']; ?> Name' value='<?php echo $info['sku']; ?>' /> </td>
                  </tr>
                  <tr>
                    <td colspan="2" style="padding:0;">
                      <table width="100%">
                        <tr>
                          <td><label>Width:</label></td>
                          <td><input type="text" class="c_input" name="width" placeholder="Width" value="<?php echo $info['width']; ?>"/></td>
                          <td><label>Height:</label></td>
                          <td><input type="text" class="c_input" name="height" placeholder="Height" value="<?php echo $info['height']; ?>"/></td>
                          <td><label>Depth:</label></td>
                          <td><input type="text" class="c_input" name="depth" placeholder="Depth" value="<?php echo $info['depth']; ?>"/></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td><label>Default Hinge:</label></td>
                    <td>
                      <table>
                        <tr>
                          <td><input class="c_input" id="default_hinge_tbd" value="tbd" name="default_hinge" type="radio" checked><label for="default_hinge_tbd">&nbsp; TBD</label></td>
                          <td><input class="c_input" id="default_hinge_left" value="left" name="default_hinge" type="radio"><label for="default_hinge_left">&nbsp; Left</label></td>
                          <td><input class="c_input" id="default_hinge_right" value="right" name="default_hinge" type="radio"><label for="default_hinge_right">&nbsp; Right</label></td>
                          <td><input class="c_input" id="default_hinge_pair" value="pair" name="default_hinge" type="radio"><label for="default_hinge_pair">&nbsp; Pair</label></td>
                          <td><input class="c_input" id="default_hinge_none" value="none" name="default_hinge" type="radio"><label for="default_hinge_none">&nbsp; None</label></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td><label>Hinging Available:</label></td>
                    <td>
                      <table>
                        <tr>
                          <td><input class="c_input" id="can_hinge_tbd" value="tbd" name="hinge_available[]" type="checkbox" checked><label for="can_hinge_tbd">&nbsp; TBD</label></td>
                          <td><input class="c_input" id="can_hinge_left" value="left" name="hinge_available[]" type="checkbox"><label for="can_hinge_left">&nbsp; Left</label></td>
                          <td><input class="c_input" id="can_hinge_right" value="right" name="hinge_available[]" type="checkbox"><label for="can_hinge_right">&nbsp; Right</label></td>
                          <td><input class="c_input" id="can_hinge_pair" value="pair" name="hinge_available[]" type="checkbox"><label for="can_hinge_pair">&nbsp; Pair</label></td>
                          <td><input class="c_input" id="can_hinge_none" value="none" name="hinge_available[]" type="checkbox"><label for="can_hinge_none">&nbsp; None</label></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td><label>Image:</label></td>
                    <td>
                      <input class="c_input" id="item_current_image" value="current" name="image_type" type="radio" <?php echo $current_img; ?>> <label for="item_current_image"> Use Current Image</label>
                      <input class="c_input" id="item_recent_image" value="recent" name="image_type" type="radio" <?php echo $recent_img; ?>> <label for="item_recent_image"> Use Recent Image</label>
                      <input class="c_input" id="item_new_image" value="new" name="image_type" type="radio"> <label for="item_new_image"> Upload New Image</label>
                      <input class="c_input" id="item_existing_image" value="existing" name="image_type" type="radio" disabled> <label for="item_existing_image"> Image Library</label>
                    </td>
                  </tr>
                  <tr style="height:5px;">
                    <td colspan="2"></td>
                  </tr>
                  <tr class="displayImage" id="displayCurrentImage">
                    <td><label>Current Image:</label></td>
                    <td><img style="max-width:100px;max-height:100px;" src="/html/pricing/images/<?php echo $info['image_path'] ?>" /></td>
                  </tr>
                  <tr class="displayImage" id="displayRecentImage">
                    <td><label>Recent Images:</label></td>
                    <td>
                      <table>
                        <tr>
                          <td><input class="c_input" id="recent_1" value="recent_1" name="recent_image" type="radio" checked></td>
                          <td><label for="recent_1"><img alt="Cutlery Tray" style="max-width:100px;max-height:100px;" src="/html/pricing/images/uploaded/cutlery_tray.PNG" /></label></td>
                          <td><input class="c_input" id="recent_2" value="recent_1" name="recent_image" type="radio"></td>
                          <td><label for="recent_2"><img alt="Cutlery Tray" style="max-width:100px;max-height:100px;" src="/html/pricing/images/uploaded/cutlery_tray.PNG" /></label></td>
                          <td><input class="c_input" id="recent_3" value="recent_1" name="recent_image" type="radio"></td>
                          <td><label for="recent_3"><img alt="Cutlery Tray" style="max-width:100px;max-height:100px;" src="/html/pricing/images/uploaded/cutlery_tray.PNG" /></label></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr class="displayImage" id="displayImageLibrary">
                    <td><label>Image Library:</label></td>
                    <td></td>
                  </tr>
                  <tr class="displayImage" id="displayNewImageUpload">
                    <td><label>New Image:</label></td>
                    <td><input type="file" id="image_upload" class="c_input" style="border:none;" name="image" placeholder="Image"/></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
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
  // TODO: Paste image in - https://codepen.io/netsi1964/pen/IoJbg
  // TODO: Image picker - https://rvera.github.io/image-picker/
  $(function() {
    $("#catalogAddEditItem input[name='image_type']:checked").trigger("change"); // show/hide based on what's currently selected
  });
</script>