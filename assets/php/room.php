<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 6/20/2018
 * Time: 8:47 AM
 */

namespace room;


class room {
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

        if(!empty($value['subitems'])) {
          $options .= "$section_head <div class='sub_option_header' data-value='{$value['key']}'>{$value['value']} $img</div>";

          $subitems = json_decode($value['subitems']);
          $option_grid .= "$section_head <div class='grid_element' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img";

          foreach($subitems as $key => $item) {
            $options .= "<div class='option sub_option' data-value='{$key}' data-addl-info='{$value['value']}'>{$item}</div>";
            $option_grid .= "<div class='option sub_option' data-value='{$key}'>{$item}</div>";
          }

          $option_grid .= "</div>";
        } else {
          $options .= "$section_head <div class='option' data-value='{$value['key']}'>{$value['value']} $img</div>";

          $option_grid .= "$section_head <div class='grid_element option' data-value='{$value['key']}'><div class='header'>{$value['value']}</div>$img</div>";
        }
      }
    }

    $selected = empty($selected) ? 'Not Selected Yet' : $selected;

    echo "<div class='custom_dropdown' $addl_id>";
    echo "<div class='selected'>$selected $selected_img</div><div class='dropdown_arrow'><i class='zmdi zmdi-chevron-down'></i></div>";
    echo "<div class='dropdown_options' data-for='$dblookup'>";
    echo "<div class='option_list'>$options</div>";
    echo "<div class='option_grid'>$option_grid</div>";
    echo "</div><input type='hidden' value='$sel_key' id='{$dblookup}' name='{$dblookup}' /><div class='clearfix'></div></div>";
  }
}