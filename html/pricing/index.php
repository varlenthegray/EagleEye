<?php
require '../../includes/header_start.php';

// TODO: Cabinet list load from database
// TODO: Save cabinet list to the database
// TODO: Display price group price based on database values
// TODO: Import items and line items based on nomenclature
// TODO: Correct header information (above coversheet)
// TODO: Tag based on room ID #
// TODO: Allow information on coversheet to be updated directly from page

//outputPHPErrs();

$room_id = 799;

$vin_qry = $dbconn->query("SELECT * FROM vin_schema ORDER BY segment ASC, case `group` when 'Custom' then 1 when 'Other' then 2 else 3 end, `group` ASC,
 FIELD(`value`, 'Custom', 'Other', 'No', 'None') DESC");

while($vin = $vin_qry->fetch_assoc()) {
  $vin_schema[$vin['segment']][] = $vin;
}

$info_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = '$room_id'");
$info = $info_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$info['dealer_code']}'");
$dealer_info = $dealer_qry->fetch_assoc();

$sheen_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'sheen' AND `key` = '{$info['sheen']}'");
$sheen = $sheen_qry->fetch_assoc();

function displayVINOpts($segment, $db_col = null, $id = null) {
  global $vin_schema;
  global $room;

  // assigns SEGMENT = VIN Schema column (panel_raise) of which there may be multiple pulled from VIN SCHEMA, DB_COL of which there is only one (panel_raise_sd, stored in ROOMS table)
  $dblookup = (!empty($db_col)) ? $db_col : $segment;
  $addl_id = (!empty($id)) ? "id = '$id'" : null; // for duplicate values (panel_raise vs panel_raise_sd)
  $options = null;
  $option_grid = null;

  $prev_header = null;
  $section_head = null;

  $selected = '';

  foreach($vin_schema[$segment] as $value) {
    if(((string)$value['key'] === (string)$room[$dblookup]) && empty($selected)) {
      $selected = "{$value['value']}";
      $selected_img = (!empty($value['image'])) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;
      $sel_key = $value['key'];
    }

    if((bool)$value['visible']) {
      $img = (!empty($value['image'])) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;

      if ($value['group'] !== $prev_header) {
        $section_head = "<div class='header'>{$value['group']}</div>";
        $prev_header = $value['group'];
      } else {
        $section_head = null;
      }

      $options .= "$section_head <div class='option' data-value='{$value['key']}'>{$value['value']} $img</div>";

      if(!empty($value['subitems'])) {
        $subitems = json_decode($value['subitems']);
        $option_grid .= "$section_head <div class='grid_element' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img";

        foreach($subitems as $key => $item) {
          $options .= "<div class='option sub_option' data-value='{$key}'>{$item}</div>";
          $option_grid .= "<div class='option sub_option' data-value='{$key}'>{$item}</div>";
        }

        $option_grid .= "</div>";
      } else {
        $option_grid .= "$section_head <div class='grid_element option' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img</div>";
      }
    }
  }

  $selected = (empty($selected)) ? "Not Selected Yet" : $selected;

  echo "<div class='custom_dropdown' $addl_id>";
  echo "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  echo "<div class='dropdown_options' data-for='$dblookup'>";
  echo "<div class='option_list'>$options</div>";
  echo "<div class='option_grid'>$option_grid</div>";
  echo "</div><input type='hidden' value='$sel_key' id='{$dblookup}' name='{$dblookup}' /><div class='clearfix'></div></div>";
}

function displayFinishOpts($segment, $db_col = null, $id = null) {
  global $vin_schema;
  global $room;

  // assigns SEGMENT = VIN Schema column (panel_raise) of which there may be multiple pulled from VIN SCHEMA, DB_COL of which there is only one (panel_raise_sd, stored in ROOMS table)
  $dblookup = (!empty($db_col)) ? $db_col : $segment;
  $addl_id = (!empty($id)) ? $id : $dblookup; // for duplicate values (panel_raise vs panel_raise_sd)
  $options = null;
  $option_grid = null;

  $prev_header = null;
  $section_head = null;

  $selected = '';

  foreach($vin_schema[$segment] as $value) {
    if(((string)$value['key'] === (string)$room[$dblookup]) && empty($selected)) {
      $selected = "{$value['value']}";
      $selected_img = (!empty($value['image'])) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;
      $sel_key = $value['key'];
    }

    if((bool)$value['visible']) {
      $img = (!empty($value['image'])) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;

      if ($value['group'] !== $prev_header) {
        $section_head = "<div class='header'>{$value['group']}</div>";
        $prev_header = $value['group'];
      } else {
        $section_head = null;
      }

      $options .= "$section_head <div class='option' data-value='{$value['key']}' data-display-text=\"{$value['value']}\">{$value['value']} $img</div>";

      if(!empty($value['imagemap_coords']) && stristr($value['imagemap_coords'], '[')) {
        $multimap = json_decode($value['imagemap_coords']);

        foreach($multimap AS $map) {
          $option_grid .= "<area shape='rect' class='option sub_option' style='display:none;' coords='$map' href='#' onclick='return false;' data-value='{$value['key']}' data-display-text=\"{$value['value']}\" />";
        }
      } else {
        $option_grid .= "<area shape='rect' class='option sub_option' style='display:none;' coords='{$value['imagemap_coords']}' onclick='return false;' href='#' data-value='{$value['key']}' data-display-text=\"{$value['value']}\" />";
      }
    }
  }

  $selected = (empty($selected)) ? "Not Selected Yet" : $selected;

  $option_grid = "<img src='/assets/images/sample_display.jpg' width='778' height='800' border='0' usemap='#{$dblookup}_map' style='max-width:800px;max-height:800px;' /><map name='{$dblookup}_map' class='grid_element'>$option_grid</map>";

  echo "<div class='custom_dropdown'>";
  echo "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  echo "<div class='dropdown_options' data-for='$dblookup'>";
  echo "<div class='option_list'>$options</div>";
  echo "<div class='option_grid'>$option_grid</div>";
  echo "</div><input type='hidden' value='$sel_key' id='{$dblookup}' name='{$dblookup}' /><div class='clearfix'></div></div>";

  /*echo "<div class='custom_dropdown' $addl_id>";
  echo "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
  echo "<div class='dropdown_options' data-for='$dblookup'>";
  echo "<div class='option_list'>$options</div>";
  echo "<div class='option_grid'>$options_grid</div>";
  echo "</div><input type='hidden' value='$selected' id='$dblookup' name='$dblookup' /><div class='clearfix'></div></div>";*/
}

function translateVIN($segment, $key) {
  global $dbconn;
  global $info;

  $output = array();

  $custom_keys = ['X', 'Xxx', 'AX', 'DX', 'TX', 'Xx', 'WX', '1cXXXX', '3gXXXX'];

  if($segment === 'finish_code') {
    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE (segment = 'finish_code') AND `key` = '$key'");
  } else {
    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$segment' AND `key` = '$key'");
  }

  $vin = $vin_qry->fetch_assoc();

  $mfg = '';
  $code = '';
  $name = '';
  $desc = '';

  if(!empty($info['custom_vin_info'])) {
    if(in_array($key, $custom_keys)) {
      $custom_info = json_decode($info['custom_vin_info'], true);

      if(count($custom_info[$segment]) > 1) {
        foreach($custom_info[$segment] as $key => $value) {
          $mfg = (stristr($key, 'mfg')) ? $value : $mfg;
          $code = (stristr($key, 'code')) ? $value : $code;
          $name = (stristr($key, 'name')) ? $value : $name;
        }

        $desc = "$name";
      } else {
        $desc = "Custom - " . array_values($custom_info[$segment])[0];
      }
    } else {
      $desc = $vin['value'];
    }
  } else {
    $desc = $vin['value'];
  }

  return "$desc";
}

$note_arr = array();

$notes_qry = $dbconn->query("SELECT * FROM notes WHERE (note_type = 'room_note_delivery' OR note_type = 'room_note_global' OR note_type = 'room_note_fin_sample') AND type_id = '$room_id'");

if($notes_qry->num_rows > 0) {
  while($notes = $notes_qry->fetch_assoc()) {
    $note_arr[$notes['note_type']] = $notes['note'];
  }
}

if($_REQUEST['action'] === 'sample_req' || $_REQUEST['action'] === 'no_totals') {
  $hide .= "$('#sample_confirmation').hide();";
  $hide .= "$('#terms_acceptance').hide();";
  $hide .= "$('#sample_qty').css('margin-top', '30px');";
}

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id' ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

$result_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '{$room['so_parent']}'");
$result = $result_qry->fetch_assoc();
?>

<div class="card-box">
  <div class="row">
    <div class="col-md-2 pricing_left_nav no-print sticky">
      <div class="sticky nav_filter">
        <div class="form-group">
          <label for="catalog">Catalog</label>
          <select class="form-control" name="catalog" id="catalog">
            <?php
            $cat_id = 1;

            // TODO: At some point, limit this based on user catalog mapping (so that only certain users can access certain catalogs)
            $cat_qry = $dbconn->query("SELECT id, name  FROM pricing_catalog pc WHERE enabled = TRUE ORDER BY sort_order ASC;");

            if($cat_qry->num_rows > 0) {
              while($cat = $cat_qry->fetch_assoc()) {
                $room_cat_qry = $dbconn->query("SELECT catalog_id FROM pricing_cabinet_list WHERE room_id = $room_id;");

                if($room_cat_qry->num_rows > 0) {
                  $room_cat = $room_cat_qry->fetch_assoc();
                  $cat_id = $room_cat['catalog_id'];
                }

                if($cat_id === $cat['id']) {
                  $selected = 'selected';
                } else {
                  $selected = null;
                }

                echo "<option id='{$cat['id']}' $selected>{$cat['name']}</option>";
              }
            }
            ?>
          </select>
        </div>
        <div class="form-group">
          <label for="treeFilter">Search Catalog</label>
          <input type="text" class="form-control fc-simple ignoreSaveAlert" id="treeFilter" placeholder="Find" width="100%" >
        </div>
      </div>

      <label for="below">Categories</label>
      <div id="catalog_categories"></div>
    </div>

    <div class="col-md-10 pricing_table_format">
      <div class="row">
        <div class="col-md-12" style="margin-top:5px;">
          <table style="max-width:955px;" width="100%">
            <tr>
              <td colspan="8" class="text-md-center"><h2>Pricing Program Cover Sheet</h2></td>
            </tr>
            <tr>
              <td colspan="3" width="33.3%"><h3>Quote</h3></td>
              <td colspan="3" width="33.3%" class="text-md-center" id="page_count"></td>
              <td colspan="2" width="33.3%" class="text-md-right">Production Type: Cabinet</td>
            </tr>
            <tr>
              <td colspan="3"><h4>RTWard_Taylor - Kitchen Perimiter</h4></td>
              <td colspan="3" class="text-md-center">Printed <?php echo date(DATE_DEFAULT); ?></td>
              <td colspan="2" class="text-md-right">Production Status: Green</td>
            </tr>
            <tr>
              <td colspan="3">Sales Order #: <strong>667A-1.01</strong></td>
              <td colspan="3">&nbsp;</td>
              <td colspan="2" class="text-md-right">Ship Date: 4/1/2018</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td colspan="3">&nbsp;</td>
              <td colspan="2" class="text-md-right">Delivery Date: ---</td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <th colspan="3">Design</th>
              <th colspan="3">Finish</th>
              <th colspan="2">Delivery</th>
            </tr>
            <tr>
              <td colspan="3" class='gray_bg'><?php echo ($info['construction_method'] !== 'L') ? "Door/Drawer Head" : null; ?></td>
              <td colspan="3" class='gray_bg'>Door/Drawer</td>
              <td colspan="2" class='gray_bg'>&nbsp;</td>
            </tr>
            <tr class="border_top">
              <td class="border_thin_bottom" width="12%"><label for="species_grade_<?php echo $room['id']; ?>">Species/Grade:</label></td>
              <td class="border_thin_bottom"><?php displayVINOpts('species_grade'); ?></td>
              <td width="3%">&nbsp;</td>
              <td class="border_thin_bottom" width="14%">Finish Code:</td>
              <td class="border_thin_bottom"><?php displayFinishOpts("finish_code", "finish_code"); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="finish_code_pm" value="">)</span></td>
              <td width="3%">&nbsp;</td>
              <td width="16%"><strong>Ship VIA:</strong></td>
              <td><input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_via" value="<?php echo $info['vin_ship_via']; ?>"></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Construction:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('construction_method'); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Sheen:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('sheen'); ?></td>
              <td>&nbsp;</td>
              <td><strong>Ship To:</strong></td>
              <td rowspan="3">
                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_1" value="<?php echo $info['name_1']; ?>"><br />
                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_2" value="<?php echo $info['project_addr']; ?>"><br />
                <input type="text" style="width:76px;" class="static_width align_left border_thin_bottom" name="ship_to_city" value="<?php echo $info['project_city']; ?>"> <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php echo $info['project_state']; ?>"> <input type="text" style="width:30px;" class="static_width align_left border_thin_bottom" name="ship_to_zip" value="<?php echo $info['project_zip']; ?>">
              </td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Door Design:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('door_design'); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="dd_custom_pm" value="">)</span></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Glaze Color:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('glaze'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Door Panel Raise:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('panel_raise', 'panel_raise_door'); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Glaze Technique:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('glaze_technique'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Short Drawer Raise:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('panel_raise', 'panel_raise_sd'); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Antiquing</td>
              <td class="border_thin_bottom"><?php displayVINOpts('antiquing'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Tall Drawer Raise:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('panel_raise', 'panel_raise_td'); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Worn Edges</td>
              <td class="border_thin_bottom"><?php displayVINOpts('worn_edges'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Edge Profile:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('edge_profile'); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Distressing</td>
              <td class="border_thin_bottom"><?php displayVINOpts('distress_level'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Framing Bead:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('framing_bead'); ?></td>
              <td>&nbsp;</td>
              <td colspan="2" class='gray_bg border_thin_bottom'>Carcass</td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Frame Option:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('framing_options'); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom"><strong>Exterior</strong> Species:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('carcass_species', 'carcass_exterior_species'); ?></td>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Styles/Rails:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('style_rail_width'); ?></td>
              <td style="border:solid #FFF;">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Finish Code:</td>
              <td class="border_thin_bottom"><?php displayFinishOpts("finish_code", "carcass_exterior_finish_code", "carcass_exterior_finish_code"); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="e_finish_code_pm" value="">)</span></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Glaze Color:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('glaze', 'carcass_exterior_glaze_color'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Glaze Technique:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('glaze_technique', 'carcass_exterior_glaze_technique'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Drawer Box:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('drawer_boxes'); ?></td>
              <td style="border:solid #FFF;">&nbsp;</td>
              <td class="border_thin_bottom"><strong>Interior</strong> Species:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('carcass_species', 'carcass_interior_species'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Finish Code:</td>
              <td class="border_thin_bottom"><?php displayFinishOpts("finish_code", "carcass_interior_finish_code", "carcass_interior_finish_code"); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="i_finish_code_pm" value="">)</span></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Glaze Color:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('glaze', 'carcass_interior_glaze_color'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Glaze Technique:</td>
              <td class="border_thin_bottom"><?php displayVINOpts('glaze_technique', 'carcass_interior_glaze_technique'); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <th colspan="8">Notes</th>
            </tr>
            <tr>
              <td colspan="3" style="border-right:1px solid #000;" class="gray_bg">Global Notes:</td>
              <td colspan="3" class="gray_bg" style="border-right:1px solid #000;">Finishing/Sample Notes:</td>
              <td colspan="2" class="gray_bg">Delivery Notes:</td>
            </tr>
            <tr id="notes_section">
              <td colspan="3" id="global_notes"><textarea name="global_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_global']; ?></textarea></td>
              <td colspan="3" id="layout_notes_title" style="border-right:1px solid #000;"><textarea name="layout_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_fin_sample']; ?></textarea></td>
              <td colspan="2" id="delivery_notes" style="border:none;"><textarea name="delivery_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_delivery']; ?></textarea></td>
            </tr>
            <tr>
              <th colspan="8">&nbsp;</th>
            </tr>
          </table>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12" style="margin-top:5px;">
          <h2>Cabinet List</h2>
          <h5 class="no-print" style="margin:10px 0;">
            <span class="cursor-hand no-select" id="catalog_add_item"><i class="zmdi zmdi-plus-circle-o"></i> Add Item</span>
            <span class="cursor-hand no-select" style="margin-left:10px;display:none;" id="catalog_remove_checked"><i class="zmdi zmdi-minus-circle-outline"></i> Remove Checked Items</span>
          </h5>

          <input type="button" class="btn-success no-print" id="cabinet_list_save" value="Save" />

          <table id="cabinet_list">
            <colgroup>
              <col width="30px">
              <col width="50px">
              <col width="50px">
              <col width="500px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="75px">
            </colgroup>
            <thead>
            <tr>
              <th></th>
              <th>#</th>
              <th class="text-md-center">Qty</th>
              <th>Line Item</th>
              <th class="text-md-center">Width</th>
              <th class="text-md-center">Height</th>
              <th class="text-md-center">Depth</th>
              <th class="text-md-center">Price Ea</th>
              <th class="text-md-center">Total</th>
              <th class="text-md-center">Catalog</th>
            </tr>
            </thead>
            <tbody>
            <!-- Define a row template for all invariant markup: -->
            <tr>
              <td class="text-md-center"></td>
              <td></td>
              <td><input type="text" class="form-control qty_input" value="1" placeholder="Qty" /> </td>
              <td></td>
              <td class="text-md-center"></td>
              <td class="text-md-center"></td>
              <td class="text-md-center"></td>
              <td class="text-md-right"></td>
              <td class="text-md-right"></td>
              <td class="text-md-center"></td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  var total = 0; // define initial total

  function delNoData() {
    let getNegNode = cabinetList.fancytree("getTree").getNodeByKey('-1');

    if(getNegNode !== null) {
      cabinetList.fancytree("getTree").getNodeByKey('-1').remove();
    }
  }

  $("body")
    .on("keyup", "#treeFilter", function() { // filters per keystroke on search catalog
      // grab this value and filter it down to the node needed
      catalog.fancytree("getTree").filterNodes($(this).val());

      // TODO: Enable filter dropdown allowing keywords - expected result, type microwave and get nomenclature available under microwave
      // TODO: https://github.com/mar10/fancytree/issues/551
    })
    .on("click", "#catalog_add_item", function() { // the click of the "Add Item" button
      delNoData();

      var root = cabinetList.fancytree("getRootNode");
      var child = root.addChildren({
        title: "Nomenclature...",
        tooltip: "Type your nomenclature here."
      });
    })
    .on("click", "#catalog_remove_checked", function() { // removes whatever is checked
      var tree = cabinetList.fancytree("getTree"), // get the tree
        selected = tree.getSelectedNodes(); // define what is selected

      // for every selected node
      selected.forEach(function(node) {
        node.remove(); // remove it
      });

      // re-render the tree deeply so that we can recalculate the line item numbers
      cabinetList.fancytree("getRootNode").render(true,true);

      // hide the remove items button, there are no items to remove now
      $(this).hide();
    })
    .on("click", "#cabinet_list_save", function() {
      var cab_list = JSON.stringify(cabinetList.fancytree("getTree").toDict(true));
      var cat_id = $("#catalog").find(":selected").attr("id");

      $.post("/html/pricing/ajax/item_actions.php?action=saveCatalog&room_id=<?php echo $room_id; ?>", {cabinet_list: cab_list, catalog_id: cat_id}, function(data) {
        $("body").append(data);
      });
    })
    .on("focus", ".qty_input", function() { // when clicking or tabbing to quantity
      $(this).select(); // auto-select the text
    })
    .on("keyup", ".qty_input", function() {
      let id = $(this).attr("id");

      cabinetList.fancytree("getTree").getNodeByKey(id).data.qty = $(this).val();
    })
    .on("click", ".view_item_info", function() {
      // TODO: Implement popup for item information including description and image
    })
    .on("click", ".add_item_cabinet_list", function() {
      delNoData();

      var root = cabinetList.fancytree("getRootNode");

      $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: $(this).attr('data-id')}, function(data) {
        let itemInfo = JSON.parse(data);

        root.addChildren({
          qty: 1,
          title: itemInfo.sku,
          width: itemInfo.width,
          height: itemInfo.height,
          depth: itemInfo.depth,
          itemID: itemInfo.id,
          catalog: itemInfo.catalog
        });
      });
    })
    .on("change", "#catalog", function() {
      let id = $(this).find(":selected").attr("id");

      let catalogData = {
        url: '/html/pricing/ajax/nav_menu.php',
        type: 'POST',
        data: {
          catalog: id
        },
        dataType: 'json'
      };

      catalog.fancytree('getTree').reload(catalogData);
    })
  ;

  var CLIPBOARD = null;
  var cabinetList = $("#cabinet_list");
  var catalog = $("#catalog_categories");

  $(function() {
    /******************************************************************************
     *  Cabinet List
     ******************************************************************************/
    cabinetList.fancytree({
      select: function(event, data) { // TODO: Determine if this is valuable
        // Display list of selected nodes
        var selNodes = data.tree.getSelectedNodes();
        // convert to title/key array
        var selKeys = $.map(selNodes, function(node){
          return "[" + node.key + "]: '" + node.title + "'";
        });

        console.log(selKeys.join(", "));

        if(selKeys.length > 0) {
          $("#catalog_remove_checked").show();
        } else {
          $("#catalog_remove_checked").hide();
        }
      },
      imagePath: "/assets/images/cabinet_icons/",
      cookieId: "fancytree-cabList",
      idPrefix: "fancytree-cabList-",
      checkbox: true,
      titlesTabbable: true,     // Add all node titles to TAB chain
      quicksearch: true,        // Jump to nodes when pressing first character
      source: { url: "/html/pricing/ajax/item_actions.php?action=getCabinetList&room_id=<?php echo $room_id; ?>" },
      extensions: ["edit", "dnd", "table", "gridnav"],
      debugLevel: 0,
      dnd: { // drag and drop
        preventVoidMoves: true,
        preventRecursiveMoves: true,
        autoExpandMS: 400,
        dragStart: function(node, data) {
          return true;
        },
        dragEnter: function(node, data) {
          // return ["before", "after"];
          return true;
        },
        dragDrop: function(node, data) {
          data.otherNode.moveTo(node, data.hitMode);
        }
      },
      edit: {
        triggerStart: ["clickActive", "dblclick", "f2", "shift+click", "mac+enter"],
        close: function(event, data) {
          if( data.save && data.isNew ){
            // Quick-enter: add new nodes until we hit [enter] on an empty title
            cabinetList.trigger("nodeCommand", {cmd: "addSibling"});
          }
        }
      },
      table: {
        indentation: 20,
        nodeColumnIdx: 3,
        checkboxColumnIdx: 0
      },
      gridnav: {
        autofocusInput: false,
        handleCursorKeys: true
      },
      /*lazyLoad: function(event, data) { // may take advantage of this in the future
        data.result = {url: "../demo/ajax-sub2.json"};
      },*/
      /*createNode: function(event, data) {
        var node = data.node,
          $tdList = $(node.tr).find(">td");

        // Span the remaining columns if it's a folder.
        // We can do this in createNode instead of renderColumns, because
        // the `isFolder` status is unlikely to change later
        if( node.isFolder() ) {
          $tdList.eq(2)
            .prop("colspan", 6)
            .nextAll().remove();
        }
      },*/
      renderColumns: function(event, data) {
        // this section handles the column data itself
        var node = data.node, $tdList = $(node.tr).find(">td");

        // (Index #0 is rendered by fancytree by adding the checkbox)
        // Set column #1 info from node data:
        $tdList.eq(1).text(node.getIndexHier());
        // (Index #2 is the quantity input field)
        $tdList.eq(2).find("input").attr("id", node.key).val(node.data.qty);
        // (Index #3 is rendered by fancytree in child table under nodeColumnIdx)
        // (Index #4 is the width)
        $tdList.eq(4).text(node.data.width);
        // (Index #5 is the height)
        $tdList.eq(5).text(node.data.height);
        // (Index #6 is the depth)
        $tdList.eq(6).text(node.data.depth);
        // (Index #7 is price, calculated below)
        let price = 0; // define price

        if(node.data.price !== undefined) { // if there is a price
          price = node.data.price.formatMoney(); // format it

          // add the individual node total to the running total
          total += node.data.price; // TODO: Fix this, it doesn't add each columns data nor does it take into account quantity!
        } else { // otherwise
          price = node.data.price; // display whatever was there
        }

        // (Index #7)
        $tdList.eq(7).text(price); // price column

        let total_formatted = 0; // final output of total, initial definition of total_formatted

        // (Index #8 is the total)
        total_formatted = total.formatMoney(); // format it

        // (Index #8)
        $tdList.eq(8).text(total_formatted);

        // (Index #9 is the catalog)
        $tdList.eq(9).text(node.data.catalog);
      }
    }).on("nodeCommand", function(event, data){
      // Custom event handler that is triggered by keydown-handler and
      // context menu:
      var refNode, moveMode,
        tree = $(this).fancytree("getTree"),
        node = tree.getActiveNode();

      switch( data.cmd ) {
        case "moveUp":
          refNode = node.getPrevSibling();
          if( refNode ) {
            node.moveTo(refNode, "before");
            node.setActive();
          }
          break;
        case "moveDown":
          refNode = node.getNextSibling();
          if( refNode ) {
            node.moveTo(refNode, "after");
            node.setActive();
          }
          break;
        case "indent":
          refNode = node.getPrevSibling();
          if( refNode ) {
            node.moveTo(refNode, "child");
            refNode.setExpanded();
            node.setActive();
          }
          break;
        case "outdent":
          if( !node.isTopLevel() ) {
            node.moveTo(node.getParent(), "after");
            node.setActive();
          }
          break;
        case "rename":
          node.editStart();
          break;
        case "remove":
          refNode = node.getNextSibling() || node.getPrevSibling() || node.getParent();
          node.remove();
          if( refNode ) {
            refNode.setActive();
          }
          break;
        case "addChild":
          node.editCreateNode("child", "");
          break;
        case "addSibling":
          node.editCreateNode("after", "");
          break;
        case "cut":
          CLIPBOARD = {mode: data.cmd, data: node};
          break;
        case "copy":
          CLIPBOARD = {
            mode: data.cmd,
            data: node.toDict(function(n){
              delete n.key;
            })
          };
          break;
        case "clear":
          CLIPBOARD = null;
          break;
        case "paste":
          if( CLIPBOARD.mode === "cut" ) {
            // refNode = node.getPrevSibling();
            CLIPBOARD.data.moveTo(node, "child");
            CLIPBOARD.data.setActive();
          } else if( CLIPBOARD.mode === "copy" ) {
            node.addChildren(CLIPBOARD.data).setActive();
          }
          break;
        default:
          alert("Unhandled command: " + data.cmd);
          return;
      }
    }).on("keydown", function(e){
      var cmd = null;

      // console.log(e.type, $.ui.fancytree.eventToString(e));
      switch( $.ui.fancytree.eventToString(e) ) {
        case "ctrl+shift+n":
        case "meta+shift+n": // mac: cmd+shift+n
          cmd = "addChild";
          break;
        case "ctrl+c":
        case "meta+c": // mac
          cmd = "copy";
          break;
        case "ctrl+v":
        case "meta+v": // mac
          cmd = "paste";
          break;
        case "ctrl+x":
        case "meta+x": // mac
          cmd = "cut";
          break;
        case "ctrl+n":
        case "meta+n": // mac
          cmd = "addSibling";
          break;
        case "del":
        case "meta+backspace": // mac
          cmd = "remove";
          break;
        // case "f2":  // already triggered by ext-edit pluging
        //   cmd = "rename";
        //   break;
        case "ctrl+up":
          cmd = "moveUp";
          break;
        case "ctrl+down":
          cmd = "moveDown";
          break;
        case "ctrl+right":
        case "ctrl+shift+right": // mac
          cmd = "indent";
          break;
        case "ctrl+left":
        case "ctrl+shift+left": // mac
          cmd = "outdent";
      }
      if( cmd ){
        $(this).trigger("nodeCommand", {cmd: cmd});
        return false;
      }
    });

     // Context menu (https://github.com/mar10/jquery-ui-contextmenu)
    cabinetList.contextmenu({
      delegate: "span.fancytree-node",
      menu: [
        {title: "Edit <kbd>[F2]</kbd>", cmd: "rename", uiIcon: "ui-icon-pencil" },
        {title: "Delete <kbd>[Del]</kbd>", cmd: "remove", uiIcon: "ui-icon-trash" },
        {title: "----"},
        {title: "New sibling <kbd>[Ctrl+N]</kbd>", cmd: "addSibling", uiIcon: "ui-icon-plus" },
        {title: "New child <kbd>[Ctrl+Shift+N]</kbd>", cmd: "addChild", uiIcon: "ui-icon-arrowreturn-1-e" },
        {title: "----"},
        {title: "Cut <kbd>Ctrl+X</kbd>", cmd: "cut", uiIcon: "ui-icon-scissors"},
        {title: "Copy <kbd>Ctrl-C</kbd>", cmd: "copy", uiIcon: "ui-icon-copy"},
        {title: "Paste as child<kbd>Ctrl+V</kbd>", cmd: "paste", uiIcon: "ui-icon-clipboard", disabled: true }
      ],
      beforeOpen: function(event, ui) {
        var node = $.ui.fancytree.getNode(ui.target);
        cabinetList.contextmenu("enableEntry", "paste", !!CLIPBOARD);
        node.setActive();
      },
      select: function(event, ui) {
        var that = this;
        // delay the event, so the menu can close and the click event does
        // not interfere with the edit control
        setTimeout(function(){
          $(that).trigger("nodeCommand", {cmd: ui.cmd});
        }, 100);
      }
    });

    /******************************************************************************
    *  Navigation menu
    ******************************************************************************/

    // this is the navigation menu on the left side
    catalog.fancytree({
      source: { url: "/html/pricing/ajax/nav_menu.php?catalog=<?php echo $cat_id; ?>" },
      extensions: ["filter"],
      debugLevel: 0,
      filter: {
        autoApply: true,   // Re-apply last filter if lazy data is loaded
        autoExpand: true, // Expand all branches that contain matches while filtered
        counter: true,     // Show a badge with number of matching child nodes near parent icons
        fuzzy: true,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
        hideExpandedCounter: true,  // Hide counter badge if parent is expanded
        hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
        highlight: true,   // Highlight matches by wrapping inside <mark> tags
        leavesOnly: false, // Match end nodes only
        nodata: false,      // Display a 'no data' status node if result is empty
        mode: "hide"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
      }
    });
  });
</script>