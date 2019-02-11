<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$type = sanitizeInput($_REQUEST['type']);
$id = sanitizeInput($_REQUEST['id']);

if(!empty($id)) {
  if($type === 'folder') {
    $info_qry = $dbconn->query("SELECT pc.*, pnd.description FROM pricing_categories pc LEFT JOIN pricing_nomenclature_details pnd on pc.description_id = pnd.id WHERE pc.id = $id");
  } elseif($type === 'item') {
    $price_groups = [];

    $pg_qry = $dbconn->query("SELECT * FROM pricing_price_map WHERE nomenclature_id = $id");

    while($pg = $pg_qry->fetch_assoc()) {
      $price_groups[$pg['price_group_id']] = $pg['price'];
    }

    $info_qry = $dbconn->query("SELECT pn.*, pnd.description, pnd.image_path, pnd.image_perspective, pnd.image_side, pnd.image_plan, pnd.title FROM pricing_nomenclature pn LEFT JOIN pricing_nomenclature_details pnd on pn.description_id = pnd.id WHERE pn.id = $id");
  }
}

if($info_qry && $info_qry->num_rows > 0) {
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
  $new_img = 'checked';
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
                <col width="130px">
                <col width="*">
              </colgroup>
              <tbody>
              <tr>
                <td><label>Nomenclature:</label></td>
                <td><input type="text" class="c_input" name="sku" placeholder='<?php echo $info['sku']; ?> Name' value='<?php echo $info['sku']; ?>' /> </td>
              </tr>
                <tr>
                  <td><label><?php echo $title; ?> Name:</label></td>
                  <td><input type="text" class="c_input" name="name" placeholder='<?php echo $title; ?> Name' value='<?php echo $nameValue; ?>' /> </td>
                </tr>

                <?php // display all category notes
                $catID = $type === 'folder' ? $id : $info['category_id'];
                $desc_available = json_decode($info['desc_available']);

                // https://stackoverflow.com/questions/2441821/getting-all-parent-rows-in-one-sql-query
                $cat_qry = $dbconn->query("SELECT T2.id, T2.name, T2.description_id FROM (
                SELECT @r AS _id, (SELECT @r := parent FROM pricing_categories WHERE id = _id) AS parent_id, @l := @l + 1 AS lvl
                FROM (SELECT @r := $catID, @l := 0) vars, pricing_categories h WHERE @r <> 0) T1 
                JOIN pricing_categories T2 ON T1._id = T2.id ORDER BY T1.lvl DESC");

                if($cat_qry) {
                  while($cat = $cat_qry->fetch_assoc()) {
                    if(!empty($cat['description_id'])) {
                      $desc_id = $dbconn->query("SELECT * FROM pricing_nomenclature_details WHERE id = {$cat['description_id']}");
                      $desc = $desc_id->fetch_assoc();
                    } else {
                      $desc['description'] = '<i>None Specified</i>';
                    }

                    if($cat['id'] !== $info['id']) {
                      $description = nl2br($desc['description']);

                      if($type === 'item') {
                        $checked = in_array($cat['id'], $desc_available, true) ? 'checked' : null;

                        $checkbox = "<div class='checkbox checkbox-primary'><input id=\"desc_enabled_{$cat['id']}\" name=\"desc_enabled[]\" type='checkbox' value=\"{$cat['id']}\" $checked><label for=\"desc_enabled_{$cat['id']}\"> Enabled</label></div>";
                      } else {
                        $checkbox = null;
                      }

                      echo "<tr>
                    <td>
                      <label>'{$cat['name']}' Note:</label>
                      $checkbox
                    </td>
                    <td><div style='min-height:91px;'>$description</div></td>
                  </tr>";
                    }
                  }
                }
                ?>

                <tr>
                  <td><label>Current Item Notes:</label></td>
                  <td><textarea class="c_input" name="description" placeholder="Description" style="min-height:91px;"><?php echo $info['description']; ?></textarea></td>
                </tr>
                <?php if($type === 'item' || $type === 'addItem') { ?>
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
                      <?php
                      $dh_checked = [];

                      switch($info['default_hinge']) {
                        case 'tbd':
                          $dh_checked['tbd'] = 'checked';
                          break;
                        case 'left':
                          $dh_checked['left'] = 'checked';
                          break;
                        case 'right':
                          $dh_checked['right'] = 'checked';
                          break;
                        case 'pair':
                          $dh_checked['pair'] = 'checked';
                          break;
                        case 'none':
                          $dh_checked['None N/A'] = 'checked';
                          break;
                      }
                      ?>

                      <table>
                        <tr>
                          <td><input class="c_input" id="default_hinge_tbd" value="tbd" name="default_hinge" type="radio" <?php echo $dh_checked['tbd']; ?>><label for="default_hinge_tbd">&nbsp; TBD</label></td>
                          <td><input class="c_input" id="default_hinge_left" value="left" name="default_hinge" type="radio" <?php echo $dh_checked['left']; ?>><label for="default_hinge_left">&nbsp; Left</label></td>
                          <td><input class="c_input" id="default_hinge_right" value="right" name="default_hinge" type="radio" <?php echo $dh_checked['right']; ?>><label for="default_hinge_right">&nbsp; Right</label></td>
                          <td><input class="c_input" id="default_hinge_pair" value="pair" name="default_hinge" type="radio" <?php echo $dh_checked['pair']; ?>><label for="default_hinge_pair">&nbsp; Pair</label></td>
                          <td><input class="c_input" id="default_hinge_none" value="none" name="default_hinge" type="radio" <?php echo $dh_checked['None N/A']; ?>><label for="default_hinge_none">&nbsp; None N/A</label></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td><label>Hinging Available:</label></td>
                    <td>
                      <?php
                      $hinge_available = null;

                      if(false !== strpos($info['hinge'], '[')) {
                        $hinge_available = json_decode($info['hinge'], true);

                        foreach($hinge_available AS $key => $line) {
                          $hinge_available[$line] = 'checked';
                        }
                      }
                      ?>

                      <table>
                        <tr>
                          <td><input class="c_input" id="can_hinge_tbd" value="tbd" name="hinge_available[]" type="checkbox" <?php echo $hinge_available['tbd']; ?>><label for="can_hinge_tbd">&nbsp; TBD</label></td>
                          <td><input class="c_input" id="can_hinge_left" value="left" name="hinge_available[]" type="checkbox" <?php echo $hinge_available['left']; ?>><label for="can_hinge_left">&nbsp; Left</label></td>
                          <td><input class="c_input" id="can_hinge_right" value="right" name="hinge_available[]" type="checkbox" <?php echo $hinge_available['right']; ?>><label for="can_hinge_right">&nbsp; Right</label></td>
                          <td><input class="c_input" id="can_hinge_pair" value="pair" name="hinge_available[]" type="checkbox" <?php echo $hinge_available['pair']; ?>><label for="can_hinge_pair">&nbsp; Pair</label></td>
                          <td><input class="c_input" id="can_hinge_none" value="none" name="hinge_available[]" type="checkbox" <?php echo $hinge_available['None N/A']; ?>><label for="can_hinge_none">&nbsp; None N/A</label></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td><label>Image:</label></td>
                    <td>
                      <input class="c_input" id="item_current_image" value="current" name="image_type" type="radio" <?php echo $current_img; ?>> <label for="item_current_image"> Use Current Images</label>
                      <input class="c_input" id="item_new_image" value="new" name="image_type" type="radio" <?php echo $new_img; ?>> <label for="item_new_image"> Upload New Images</label>
                    </td>
                  </tr>
                  <tr style="height:5px;">
                    <td colspan="2"></td>
                  </tr>

                  <tr class="displayImage displayCurrentImage">
                    <td><label>Perspective Image:</label></td>
                    <td><img style="max-width:100px;max-height:100px;" src="/html/pricing/images/<?php echo $info['image_perspective'] ?>" /></td>
                  </tr>
                  <tr class="displayImage displayCurrentImage">
                    <td><label>Plan Image:</label></td>
                    <td><?php echo !empty($info['image_plan']) ? "<img style='max-width:100px;max-height:100px;' src='/html/pricing/images/{$info['image_plan']}' />" : 'None'; ?></td>
                  </tr>
                  <tr class="displayImage displayCurrentImage">
                    <td><label>Side/Front Image:</label></td>
                    <td><?php echo !empty($info['image_side']) ? "<img style='max-width:100px;max-height:100px;' src='/html/pricing/images/{$info['image_side']}' />" : 'None'; ?></td>
                  </tr>

                  <tr class="displayImage displayNewImageUpload">
                    <td><label>Perspective Image:</label></td>
                    <td><input type="file" id="perspective_image" class="c_input" style="border:none;" name="perspective_image" /></td>
                  </tr>
                  <tr class="displayImage displayNewImageUpload">
                    <td><label>Plan Image:</label></td>
                    <td><input type="file" id="plan_image" class="c_input" style="border:none;" name="plan_image" /></td>
                  </tr>
                  <tr class="displayImage displayNewImageUpload">
                    <td><label>Side/Front Image:</label></td>
                    <td><input type="file" id="side_image" class="c_input" style="border:none;" name="side_image" /></td>
                  </tr>
                  <tr style="height:5px;">
                    <td colspan="2"><input type="hidden" id="image_description_id" name="image_description_id" value="<?php echo $info['description_id']; ?>" /></td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      <table style="width:75%;">
                        <colgroup>
                          <col width="5%">
                          <col width="16%">
                          <col width="5%">
                          <col width="5%">
                          <col width="16%">
                          <col width="5%">
                          <col width="5%">
                          <col width="16%">
                          <col width="5%">
                          <col width="5%">
                          <col width="16%">
                        </colgroup>
                        <tbody>
                        <tr>
                          <td colspan="11"><b><u>Price Groups: Enter Net Price into PG4 (Remaining PG's auto-calculated)</u></b></td>
                        </tr>
                        <tr>
                          <td><label for="ai_pg_1">PG1:</label></td>
                          <td><input type="text" id="pg1" placeholder="0.00" class="c_input price_group_input" name="pg1" value="<?php echo $price_groups[1]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_5">PG5:</label></td>
                          <td><input type="text" id="pg5" placeholder="0.00" class="c_input price_group_input" name="pg5" value="<?php echo $price_groups[5]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_9">PG9:</label></td>
                          <td><input type="text" id="pg9" placeholder="0.00" class="c_input price_group_input" name="pg9" value="<?php echo $price_groups[9]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_13">PG13:</label></td>
                          <td><input type="text" id="pg13" placeholder="0.00" class="c_input price_group_input" name="pg13" value="<?php echo $price_groups[13]; ?>" disabled /> </td>
                        </tr>
                        <tr>
                          <td><label for="ai_pg_2">PG2:</label></td>
                          <td><input type="text" id="pg2" placeholder="0.00" class="c_input price_group_input" name="pg2" value="<?php echo $price_groups[2]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_6">PG6:</label></td>
                          <td><input type="text" id="pg6" placeholder="0.00" class="c_input price_group_input" name="pg6" value="<?php echo $price_groups[6]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_10">PG10:</label></td>
                          <td><input type="text" id="pg10" placeholder="0.00" class="c_input price_group_input" name="pg10" value="<?php echo $price_groups[10]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_14">PG14:</label></td>
                          <td><input type="text" id="pg14" placeholder="0.00" class="c_input price_group_input" name="pg14" value="<?php echo $price_groups[14]; ?>" disabled /> </td>
                        </tr>
                        <tr>
                          <td><label for="ai_pg_3">PG3:</label></td>
                          <td><input type="text" id="pg3" placeholder="0.00" class="c_input price_group_input" name="pg3" value="<?php echo $price_groups[3]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_7">PG7:</label></td>
                          <td><input type="text" id="pg7" placeholder="0.00" class="c_input price_group_input" name="pg7" value="<?php echo $price_groups[7]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_11">PG11:</label></td>
                          <td><input type="text" id="pg11" placeholder="0.00" class="c_input price_group_input" name="pg11" value="<?php echo $price_groups[11]; ?>" disabled /> </td>
                        </tr>
                        <tr>
                          <td><label for="ai_pg_4">PG4:</label></td>
                          <td><input type="text" id="pg4" placeholder="0.00" style="background-color:#00EE00;" class="c_input price_group_input" name="pg4" value="<?php echo $price_groups[4]; ?>" /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_8">PG8:</label></td>
                          <td><input type="text" id="pg8" placeholder="0.00" class="c_input price_group_input" name="pg8" value="<?php echo $price_groups[8]; ?>" disabled /> </td>
                          <td>&nbsp;</td>
                          <td><label for="ai_pg_12">PG12:</label></td>
                          <td><input type="text" id="pg12" placeholder="0.00" class="c_input price_group_input" name="pg12" value="<?php echo $price_groups[12]; ?>" disabled /> </td>
                        </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" style="height:5px;"></td>
                  </tr>
                  <tr>
                    <td><label for="drawer_box_count">Drawer Box Count:</label></td>
                    <td><input type="text" class="c_input" name="drawer_box_count" placeholder="0" value="<?php echo $info['drawer_box_count']; ?>" style="width:25px;" /></td>
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