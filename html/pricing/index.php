<?php
require '../../includes/header_start.php';

//outputPHPErrs();

$room_id = sanitizeInput($_REQUEST['room_id']);

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
  $dblookup = !empty($db_col) ? $db_col : $segment;
  $addl_id = !empty($id) ? "id = '$id'" : null; // for duplicate values (panel_raise vs panel_raise_sd)
  $options = null;
  $option_grid = null;

  $prev_header = null;
  $section_head = null;

  $selected = '';

  foreach($vin_schema[$segment] as $value) {
    if(((string)$value['key'] === (string)$room[$dblookup]) && empty($selected)) {
      $selected = $value['value'];
      $selected_img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;
      $sel_key = $value['key'];
    }

    if((bool)$value['visible']) {
      $img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;

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

        $option_grid .= '</div>';
      } else {
        $option_grid .= "$section_head <div class='grid_element option' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img</div>";
      }
    }
  }

  $selected = (empty($selected)) ? 'Not Selected Yet' : $selected;

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
  $dblookup = !empty($db_col) ? $db_col : $segment;
  $addl_id = !empty($id) ? $id : $dblookup; // for duplicate values (panel_raise vs panel_raise_sd)
  $options = null;
  $option_grid = null;

  $prev_header = null;
  $section_head = null;

  $selected = '';

  foreach($vin_schema[$segment] as $value) {
    if(((string)$value['key'] === (string)$room[$dblookup]) && empty($selected)) {
      $selected = $value['value'];
      $selected_img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;
      $sel_key = $value['key'];
    }

    if((bool)$value['visible']) {
      $img = !empty($value['image']) ? "<br /><img src='/assets/images/vin/{$value['image']}'>" : null;

      if ($value['group'] !== $prev_header) {
        $section_head = "<div class='header'>{$value['group']}</div>";
        $prev_header = $value['group'];
      } else {
        $section_head = null;
      }

      $options .= "$section_head <div class='option' data-value='{$value['key']}' data-display-text=\"{$value['value']}\">{$value['value']} $img</div>";

      if(!empty($value['imagemap_coords']) && false !== strpos($value['imagemap_coords'], '[')) {
        $multimap = json_decode($value['imagemap_coords']);

        foreach($multimap AS $map) {
          $option_grid .= "<area shape='rect' class='option sub_option' style='display:none;' coords='$map' href='#' onclick='return false;' data-value='{$value['key']}' data-display-text=\"{$value['value']}\" />";
        }
      } else {
        $option_grid .= "<area shape='rect' class='option sub_option' style='display:none;' coords='{$value['imagemap_coords']}' onclick='return false;' href='#' data-value='{$value['key']}' data-display-text=\"{$value['value']}\" />";
      }
    }
  }

  $selected = empty($selected) ? 'Not Selected Yet' : $selected;

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
    if(in_array($key, $custom_keys, true)) {
      $custom_info = json_decode($info['custom_vin_info'], true);

      if(count($custom_info[$segment]) > 1) {
        foreach($custom_info[$segment] as $key2 => $value) {
          $mfg = false !== stripos($key2, 'mfg') ? $value : $mfg;
          $code = false !== stripos($key2, 'code') ? $value : $code;
          $name = false !== stripos($key2, 'name') ? $value : $name;
        }

        $desc = $name;
      } else {
        $desc = 'Custom - ' . array_values($custom_info[$segment])[0];
      }
    } else {
      $desc = $vin['value'];
    }
  } else {
    $desc = $vin['value'];
  }

  return $desc;
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

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

$result_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = {$room['so_parent']}");
$result = $result_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE dealer_id = '{$result['dealer_code']}'");
$dealer = $dealer_qry->fetch_assoc();

// This section refers to the submit buttons and disabling of them
$existing_quote_qry = $dbconn->query("SELECT * FROM pricing_cabinet_list WHERE room_id = $room_id");

if($existing_quote_qry->num_rows === 1) {
  $existing_quote = $existing_quote_qry->fetch_assoc();
} else {
  $existing_quote = null;
}

if(!empty($existing_quote['quote_submission'])) {
  $submit_disabled = 'disabled';

  $submitted_time = date(DATE_TIME_ABBRV, $existing_quote['quote_submission']);
  $submitted = "- Submitted on $submitted_time";
} else {
  $submit_disabled = null;
  $submitted = null;
}
?>

<link href="/assets/css/pricing.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />

<script>
  <?php echo !empty($submit_disabled) ? 'var already_submitted = true;' : 'var already_submitted = false;'; ?>
</script>

<div class="card-box">
  <div class="row sticky no-print" style="background-color:#FFF;z-index:2;top:84px;padding:4px;">
    <div class="col-md-3">
      <button class="btn waves-effect btn-primary-outline" title="Save Changes" id="cabinet_list_save" <?php echo $submit_disabled; ?>> <i class="fa fa-save fa-2x"></i> </button>
      <button class="btn waves-effect btn-success-outline" title="Submit Quote" id="submit_for_quote" <?php echo $submit_disabled; ?>> <i class="fa fa-paper-plane-o fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Global Information" id="global_info"> <i class="fa fa-globe fa-2x"></i> </button>
      <div class="btn-group">
        <button type="button" title="Print" class="btn btn-secondary dropdown-toggle waves-effect" data-toggle="dropdown" aria-expanded="false"> <i class="fa fa-print fa-2x"></i> </button>
        <div class="dropdown-menu" x-placement="bottom-start" style="position:absolute;transform:translate3d(0,38px,0);top:0;left:0;will-change:transform;">
          <a class="dropdown-item" href="#" title="Print this page specifically" onclick="window.print();">Print Item List</a>
          <?php
          echo $bouncer->validate('print_sample') ? "<a href='/print/e_coversheet.php?room_id={$room['id']}&action=sample_req' target='_blank' class='dropdown-item'>Print Sample Request</a>" : null;
          echo $bouncer->validate('print_coversheet') ? "<a href='/print/e_coversheet.php?room_id={$room['id']}' target='_blank' class='dropdown-item'>Print Coversheet</a>" : null;
          echo $bouncer->validate('print_shop_coversheet') ? "<a href='/print/e_coversheet.php?room_id={$room['id']}&action=no_totals' target='_blank' class='dropdown-item'>Print Shop Coversheet</a>" : null;
          echo $bouncer->validate('print_sample_label') ? "<a href='/print/sample_label.php?room_id={$room['id']}' target='_blank' class='dropdown-item'>Print Sample Label</a>" : null;
          ?>
        </div>
      </div>
      <button class="btn waves-effect btn-secondary" title="Room Attachments" id="add_attachment"> <i class="fa fa-paperclip fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Copy Room" id="copy_room"> <i class="fa fa-copy fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Bracket Management" id="bracket_management"> <i class="fa fa-code-fork fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Door Sizing" onclick="window.open('/html/inset_sizing.php?room_id=<?php echo $room['id']; ?>','_blank')"> <i class="fa fa-arrows-alt fa-2x"></i> </button>
      <button class="btn waves-effect btn-secondary" title="Appliance Worksheets" id='appliance_ws' data-roomid='<?php echo $room['id']; ?>'> <i class="fa fa-cubes fa-2x"></i> </button>
    </div>

    <div class="col-md-5 text-md-right"><h4 style="margin:0;padding:0;"><?php echo "Room {$room['room']}{$room['iteration']} $submitted"; ?></h4></div>
  </div>

  <div class="row">
    <div class="col-md-2 pricing_left_nav no-print sticky" style="top:122px;">
      <div class="sticky nav_filter">
        <div class="form-group">
          <label for="treeFilter">Search Catalog</label>
          <input type="text" class="form-control fc-simple ignoreSaveAlert" id="treeFilter" placeholder="Find" width="100%" >
        </div>

        <label for="below" id="category_collapse">Categories</label>
      </div>


      <div id="catalog_categories"></div>
    </div>

    <div class="col-md-6 pricing_table_format">
      <div class="row">
        <div class="col-md-12" style="margin-top:5px;">
          <table style="max-width:955px;" width="100%">
            <tr>
              <td colspan="8">
                <div class="row no_global_info" style="display:none;"><i class="fa fa-exclamation-triangle" style="font-size:2em;"></i>Unable to price with the current information.<br />Any price displayed is not a reflection of the final price until this quote has been processed by SMCM.<i class="fa fa-exclamation-triangle pull-right" style="font-size:2em;"></i></div>
              </td>
            </tr>
            <tr>
              <td colspan="8" class="text-md-center"><h2>Item List</h2></td>
            </tr>
            <tr>
              <td colspan="3" width="33.3%" style="text-decoration: underline;"><h3>Quote</h3></td>
              <td colspan="3" width="33.3%" class="text-md-center" id="page_count"></td>
              <td colspan="2" width="33.3%" class="text-md-right">Production Type: <?php echo translateVIN('product_type', $room['product_type']); ?></td>
            </tr>
            <tr>
              <td colspan="3"><h5><?php echo $result['project_name'] . ' - ' . $room['room_name']; ?></h5></td>
              <td colspan="3" class="text-md-center">&nbsp;</td>
              <td colspan="2" class="text-md-right">Production Status: <?php echo translateVIN('days_to_ship', $room['days_to_ship']); ?></td>
            </tr>
            <tr>
              <td colspan="3"><?php echo $dealer['dealer_id'] . '_' . $dealer['dealer_name'] . ' - ' . $dealer['contact']; ?></td>
              <td colspan="3" class="text-md-center">&nbsp;</td>
              <td colspan="2" class="text-md-right">Ship Date: ---</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td colspan="3" class="text-md-center">&nbsp;</td>
              <td colspan="2" class="text-md-right">Delivery Date: <?php echo date(DATE_DEFAULT, $room['delivery_date']); ?></td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="8">
                <form id="pricing_global_attributes" method="post" action="#">
                  <table class="pull-left" style="width:33%;margin-left:0.3%;">
                    <tr><th colspan="2" style="padding-left:5px;">Design<label class="c-input c-checkbox pull-right" style="color:#FFF;margin-top:2px;">Show Image Popups <input type='checkbox' id='show_image_popups' class='ignoreSaveAlert'><span class="c-indicator"></span></label></th></tr>
                    <tr><td colspan="2" class='gray_bg' style="padding-left:5px;"><?php echo ($info['construction_method'] !== 'L') ? "Door/Drawer Head" : null; ?></td></tr>
                    <tr class="border_top">
                      <td class="border_thin_bottom" width="40%"><label for="species_grade_<?php echo $room['id']; ?>">Species/Grade:</label></td>
                      <td class="border_thin_bottom"><?php displayVINOpts('species_grade'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Construction:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('construction_method'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Door Design:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('door_design'); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="dd_custom_pm" value="">)</span></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Door Panel Raise:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('panel_raise', 'panel_raise_door'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Short Drawer Raise:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('panel_raise', 'panel_raise_sd'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Tall Drawer Raise:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('panel_raise', 'panel_raise_td'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Edge Profile:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('edge_profile'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Framing Bead:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('framing_bead'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Frame Option:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('framing_options'); ?></td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Drawer Box Mount</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('drawer_box_mount'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Drawer Box:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('drawer_boxes'); ?></td>
                    </tr>
                  </table>

                  <table class="pull-left" style="width:33%;margin-left:0.3%;">
                    <tr><th colspan="3" style="padding-left:5px;">Finish</th></tr>
                    <tr><td colspan="3" class='gray_bg' style="padding-left:5px;">Door/Drawer</td></tr>
                    <tr class="border_top">
                      <td class="border_thin_bottom" width="40%">Finish Code:</td>
                      <td class="border_thin_bottom"><?php displayFinishOpts("finish_code", "finish_code"); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="finish_code_pm" value="">)</span></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Sheen:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('sheen'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Glaze Color:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('glaze'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Glaze Technique:</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('glaze_technique'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Antiquing</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('antiquing'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Worn Edges</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('worn_edges'); ?></td>
                    </tr>
                    <tr>
                      <td class="border_thin_bottom">Distressing</td>
                      <td class="border_thin_bottom"><?php displayVINOpts('distress_level'); ?></td>
                    </tr>
                  </table>

                  <table class="pull-left" style="width:33%;margin-left:0.3%;">
                    <tr><th colspan="2" style="padding-left:5px;">Delivery</th></tr>
                    <tr><td colspan="2" class='gray_bg' style="padding-left:5px;">&nbsp;</td></tr>
                    <tr class="border_top">
                      <td width="30%"><strong>Ship VIA:</strong></td>
                      <td><?php displayVINOpts('ship_via'); ?></td>
                    </tr>
                    <tr>
                      <td style="vertical-align:top !important;"><strong>Ship To:</strong></td>
                      <td rowspan="3">
                        <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_1" value="<?php echo $info['name_1']; ?>"><br />
                        <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_2" value="<?php echo $info['project_addr']; ?>"><br />
                        <input type="text" style="width:76px;" class="static_width align_left border_thin_bottom" name="ship_to_city" value="<?php echo $info['project_city']; ?>"> <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php echo $info['project_state']; ?>"> <input type="text" style="width:30px;" class="static_width align_left border_thin_bottom" name="ship_to_zip" value="<?php echo $info['project_zip']; ?>">
                      </td>
                    </tr>
                  </table>
                </form>
              </td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <th colspan="3" style="border-right:2px solid #FFF;">&nbsp;Notes</th>
              <th colspan="3" style="border-right:2px solid #FFF;">&nbsp;</th>
              <th colspan="2">&nbsp;</th>
            </tr>
            <tr>
              <td colspan="3" style="border-right:2px solid #FFF;" class="gray_bg">&nbsp;Global Notes:</td>
              <td colspan="3" class="gray_bg" style="border-right:2px solid #FFF;">&nbsp;Finishing/Sample Notes:</td>
              <td colspan="2" class="gray_bg">&nbsp;Delivery Notes:</td>
            </tr>
            <tr id="notes_section">
              <td colspan="3" id="global_notes" style="border-right:2px solid #FFF;"><textarea name="global_notes" maxlength="280" class="static_width"><?php echo $note_arr['room_note_global']; ?></textarea></td>
              <td colspan="3" id="layout_notes_title" style="border-right:2px solid #FFF;"><textarea name="layout_notes" maxlength="280" class="static_width"><?php echo $note_arr['room_note_fin_sample']; ?></textarea></td>
              <td colspan="2" id="delivery_notes" style="border:none;"><textarea name="delivery_notes" maxlength="280" class="static_width"><?php echo $note_arr['room_note_delivery']; ?></textarea></td>
            </tr>
          </table>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12" style="margin-top:5px;">
          <h5><u>Cabinet List</u></h5>

          <table id="cabinet_list">
            <colgroup>
              <col width="30px">
              <col width="30px">
              <col width="30px">
              <col width="150px">
              <col width="350px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
              <col width="50px">
            </colgroup>
            <thead>
            <tr>
              <td colspan="12" style="padding-bottom:5px;">
                <input type="button" class="btn btn-danger waves-effect waves-light no-print" style="display:none;" id="catalog_remove_checked" value="Delete" />
              </td>
            </tr>
            <tr>
              <th></th>
              <th>#</th>
              <th class="text-md-center">Qty</th>
              <th>Nomenclature</th>
              <th>Description</th>
              <th class="text-md-center">Width</th>
              <th class="text-md-center">Height</th>
              <th class="text-md-center">Depth</th>
              <th class="text-md-center">Hinge</th>
              <th class="text-md-center">Finish</th>
              <th class="text-md-center">Price Ea</th>
              <th class="text-md-center">Total</th>
            </tr>
            </thead>
            <tbody>
            <!-- Define a row template for all invariant markup: -->
            <tr>
              <td class="text-md-center"></td>
              <td></td>
              <td><input type="text" class="form-control qty_input" value="1" placeholder="Qty" /> </td>
              <td style="white-space:nowrap;"></td>
              <td></td>
              <td class="text-md-center"></td>
              <td class="text-md-center"></td>
              <td class="text-md-center"></td>
              <td class="text-md-center">
                <select class="item_hinge custom-select">
                  <option value="L">Left</option>
                  <option value="R">Right</option>
                  <option value="P">Pair</option>
                  <option value="N" selected>None</option>
                </select>
              </td>
              <td class="text-md-center">
                <select class="item_finish custom-select">
                  <option value="L">Left</option>
                  <option value="R">Right</option>
                  <option value="B">Both</option>
                  <option value="N" selected>None</option>
                </select>
              </td>
              <td class="text-md-right cab-price"></td>
              <td class="text-md-right cab-price cab-total"></td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
              <td colspan="12">
                <div class="row no_global_info" style="display:none;"><i class="fa fa-exclamation-triangle" style="font-size:2em;"></i>Unable to price with the current information.<br />Any price displayed is not a reflection of the final price until this quote has been processed by SMCM.<i class="fa fa-exclamation-triangle pull-right" style="font-size:2em;"></i></div>
              </td>
            </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div style="height:100px;">&nbsp;</div>
    </div>

    <div class="col-md-2 sticky no-print" style="top:122px;margin-left:10px;">
      <div class="row">
        <div class="col-md-12">
          <?php if($bouncer->validate('view_accounting')) { ?>
          <tr>
            <td colspan="2">
              <label class="c-input c-checkbox">Deposit Received <input type="checkbox" name="deposit_received" value="1" <?php echo ((bool)$room['payment_deposit']) ? 'checked' :null; ?>><span class="c-indicator"></span></label><br />
              <label class="c-input c-checkbox">Prior to Loading: Distribution - Final Payment<br/><span style="margin-left:110px;">Retail - On Delivery/Payment</span> <input type="checkbox" name="ptl_del" value="1" <?php echo ((bool)$room['payment_del_ptl']) ? 'checked' :null; ?>><span class="c-indicator"></span></label><br />
              <label class="c-input c-checkbox">Retail - Final Payment <input type="checkbox" name="final_payment" value="1" <?php echo ((bool)$room['payment_final']) ? 'checked' :null; ?>><span class="c-indicator"></span></label>
            </td>
          </tr>
          <tr style="height:10px;">
            <td colspan="2"></td>
          </tr>
          <?php } ?>

          <textarea class="form-control" name="room_notes" id="room_notes" placeholder="Notes" style="width:100%;height:277px;"></textarea>

          <?php if(!empty($_SESSION['userInfo'])) { ?>
            <input type="text" name="room_inquiry_followup_date" id="room_inquiry_followup_date" class="form-control" placeholder="Followup On" style="width:30%;float:left;">
            <label for="room_inquiry_requested_of" style="float:left;padding:4px;"> by </label>
            <select name="room_inquiry_requested_of" id="room_inquiry_requested_of" class="form-control" style="width:62%;float:right;">
              <option value="null" selected disabled></option>
              <?php
              $user_qry = $dbconn->query('SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC');

              while($user = $user_qry->fetch_assoc()) {
                echo "<option value='{$user['id']}'>{$user['name']}</option>";
              }
              ?>
            </select>
          <?php } ?>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="room_note_box">
            <table class="table table-custom-nb table-v-top">
              <tr>
                <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">Room Notes</h5> <?php if($bouncer->validate('view_audit_log')) { ?><div class="pull-right"><input type="checkbox" class="ignoreSaveAlert" id="display_log" /> <label for="display_log">Show Audit Log</label></div><?php } ?></td>
              </tr>
              <tr style="height:5px;"><td colspan="2"></td></tr>
              <?php
              if((bool)$_SESSION['userInfo']['dealer']) {
                $dealer = strtolower(DEALER);
                $where = "AND user.username LIKE '$dealer%'";
              } else {
                $where = null;
              }

              $room_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'room_note' OR note_type = 'room_note_log') AND notes.type_id = '{$room['id']}' $where ORDER BY notes.timestamp DESC;");

              while($room_inquiry = $room_inquiry_qry->fetch_assoc()) {
                $inquiry_replies = null;

                $time = date(DATE_TIME_ABBRV, $room_inquiry['NTimestamp']);

                if(!empty($room_inquiry['followup_time'])) {
                  $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$room_inquiry['user_to']}");
                  $followup_usr = $followup_usr_qry->fetch_assoc();

                  $followup_time = date(DATE_TIME_ABBRV, $room_inquiry['followup_time']);

                  $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                } else {
                  $followup = null;
                }

                $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$room_inquiry['nID']}' ORDER BY timestamp DESC");

                if($inquiry_reply_qry->num_rows > 0) {
                  while($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                    $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                    $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                  }
                } else {
                  $inquiry_replies = null;
                }

                $notes = str_replace('  ', '&nbsp;&nbsp;', $room_inquiry['note']);
                //$notes = $room_inquiry['note'];
                $notes = nl2br($notes);

                echo "<tr style='height:5px;'><td colspan='2'></td></tr>";

                $room_note_log = ($room_inquiry['note_type'] === 'room_note_log') ? 'room_note_log' : null;

                echo "<tr class='$room_note_log'>";
                echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$room_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                echo "  <td>$notes -- <small><em>{$room_inquiry['name']} on $time $followup</em></small><div><button type='button' class='btn waves-effect btn-primary post_to_cal'>Post to Calendar</button></div></td>";
                echo '</tr>';

                echo "<tr id='inquiry_reply_line_{$room_inquiry['nID']}' style='display:none;'>";
                echo "<td colspan='2'>
                          <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$room_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                          <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='r_{$room_inquiry['nID']}_submit'>Reply</button>
                      </td>";
                echo '</tr>';

                echo $inquiry_replies;

                echo "<tr class='$room_note_log' style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
              }
              ?>
              <tr style="height:5px;"><td colspan="2"></td></tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class='info-popup'></div>

<!-- modal -->
<div id="modalAddModification" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddModificationLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Add Modifications</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="modificationsFilter">Search Modifications</label>
              <input type="text" class="form-control fc-simple ignoreSaveAlert" id="modificationsFilter" placeholder="Find" width="100%" >
            </div>

            <div id="item_modifications"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary waves-effect waves-light" id="modificationAddSelected">Add Selected</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- modal -->
<div id="modalGeneral" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalGeneralLabel" aria-hidden="true">
      <!-- AJAX Loaded based on button press -->
</div><!-- /.modal -->

<form id="room_attachments">
  <!-- Attachment modal -->
  <div id="modalAddAttachment" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddAttachmentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title">Add Attachment to <?php echo "{$room['so_parent']}{$room['room']}-{$room['iteration']}"; ?></h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple><br />
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple><br />
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple><br />
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple><br />
              <input type="file" name="room_attachments[]" accept="<?php echo FILE_TYPES; ?>" multiple>
            </div>
          </div>
        </div>
        <div class="modal-footer" id="r_attachments_footer">
          <button type="button" class="btn btn-primary waves-effect" id="submit_attachments">Submit</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
</form>

<script>
  <?php echo "var roomID = $room_id;"; ?>
</script>

<script src="/html/pricing/pricing.min.js"></script>