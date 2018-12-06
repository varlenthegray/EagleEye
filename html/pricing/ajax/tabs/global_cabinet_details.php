<?php
require '../../../../includes/header_start.php';

$room_id = sanitizeInput($_REQUEST['room_id']);

$vin_schema = getVINSchema();

$room_qry = $dbconn->query("SELECT r.*, so.dealer_code FROM rooms r LEFT JOIN sales_order so on r.so_parent = so.so_num WHERE r.id = $room_id ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$room['dealer_code']}'");
$dealer = $dealer_qry->fetch_assoc();
?>

<form id="cabinet_details" method="post" action="#">
  <div class="container-fluid pricing_table_format m-t-10">
    <div class="row">
      <div class="col-sm-6">
        <div class="col-md-12">
          <div class="global_cab_header"><h5><u>Global: Product Details</u></h5></div>
        </div>

        <!--<editor-fold desc="Second Column: Cabinet Details">-->
        <div class="col-sm-6">
          <table width="100%">
            <tr>
              <th style="padding-left:5px;" colspan="2">Design</th>
            </tr>
            <tr class="border_top">
              <td width="35%" class="border_thin_bottom">Construction Method:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('construction_method'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Species/Grade:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('species_grade'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Carcass Material:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('carcass_material'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Door Design:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('door_design'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom"><div>Door Panel Raise:</div></td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('panel_raise', 'panel_raise_door'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Short Drawer Raise:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('panel_raise', 'panel_raise_sd'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Tall Drawer Raise:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('panel_raise', 'panel_raise_td'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Style/Rail Width:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('style_rail_width'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Edge Profile:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('edge_profile'); ?></div></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Framing Bead:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('framing_bead'); ?></div></td>
              <td class="border_thin_bottom"></td>
            </tr>
            <tr>
              <td style="padding-left:20px;" class="border_thin_bottom">Frame Option:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc" style="margin-bottom:-1px;"><?php echo getSelect('framing_options'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Drawer Box:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('drawer_boxes'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Drawer Guide:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('drawer_guide'); ?></div></td>
            </tr>
          </table>
        </div>
        <!--</editor-fold>-->

        <!--<editor-fold desc="Third column: Cabinet Finish">-->
        <div class="col-sm-6" style="padding-left:0;">
          <table width="100%">
            <tr><th colspan="2" style="padding-left:5px;" class="th_17">Finish</th></tr>
            <tr class="border_top">
              <td class="border_thin_bottom" width="30%">Finish Code:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('finish_code'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Sheen:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('sheen'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Glaze Color:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('glaze'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Glaze Technique:</td>
              <td class="border_thin_bottom pricing_value"><div class="cab_specifications_desc"><?php echo getSelect('glaze_technique'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Antiquing:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('antiquing'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Worn Edges:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('worn_edges'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Distressing:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('distress_level'); ?></div></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Enviro-finish:</td>
              <td class="border_thin_bottom"><div class="cab_specifications_desc"><?php echo getSelect('green_gard'); ?></div></td>
            </tr>
          </table>
        </div>
        <!--</editor-fold>-->
      </div>

      <div class="col-sm-3">
        <table width="100%">
          <tr><th>&nbsp;</th></tr>
          <tr><td class="gray_bg">&nbsp;Design Notes:</td></tr>
          <tr><td><textarea name="room_note_design" maxlength="280" class="pricing_textbox"><?php echo $note_arr['room_note_design']['note']; ?></textarea></td>
          </tr>
        </table>

        <input type="hidden" name="design_notes_id" value="<?php echo $note_arr['room_note_design']['id']; ?>" />
      </div>

      <div class="col-sm-3" style="padding-left:0;">
        <table width="100%">
          <tr><th>&nbsp;</th></tr>
          <tr><td class="gray_bg">&nbsp;Finishing/Sample Notes:</td></tr>
          <tr><td><textarea name="fin_sample_notes" maxlength="280" class="static_width pricing_textbox"><?php echo $note_arr['room_note_fin_sample']['note']; ?></textarea></td></tr>
        </table>
      </div>
    </div>
  </div>
</form>

<script>
  $(function() {
    globalFunctions.checkDropdown();

    <?php if($room['order_status'] === '$') { ?>
      pricingFunction.disableInput();
    <?php }?>

    pricingFunction.productTypeSwitch();

    //<editor-fold desc="Auto-note height">
    let tx_design = $("textarea[name='room_note_design']");
    let tx_fin_sample = $("textarea[name='fin_sample_notes']");
    let designheight = (tx_design.prop('scrollHeight') < 180) ? 180 : tx_design.prop('scrollHeight');
    let finsampleheight = (tx_fin_sample.prop('scrollHeight') < 180) ? 180 : tx_fin_sample.prop('scrollHeight');

    tx_design.height(designheight);
    tx_fin_sample.height(finsampleheight);
    //</editor-fold>

    //<editor-fold desc="Custom select field display">
    <?php
    echo !empty($room['custom_vin_info']) ? "let customFieldInfo = JSON.parse('{$room['custom_vin_info']}');": null;

    if (!empty($room['custom_vin_info'])) {
      echo /** @lang JavaScript */
      <<<HEREDOC
      $.each(customFieldInfo, function(mainID, value) {
        $.each(value, function(i, v) {
          $("#" + mainID).parent().find("input[name='" + i + "']").val(v);
        });
      });
HEREDOC;
    }
    ?>
    //</editor-fold>
  });
</script>