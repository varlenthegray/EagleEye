<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 2/28/2019
 * Time: 8:50 AM
 */

namespace GlobalManager;

class global_manager {
  private $vin_schema;
  private $contact;

  public function __construct($room_id) {
    global $dbconn;

    $contact_qry = $dbconn->query("SELECT
       so.id AS soID,
       c.id AS cID,
       r.id AS rID,
       cao.`option` AS billing_type,
       c.address AS contactAddress, c.city AS contactCity, c.state AS contactState, c.zip AS contactZip, 
       so.project_addr AS projectAddress, so.project_city AS projectCity, so.project_state AS projectState, so.project_zip AS projectZip,
       cc.ship_address AS shipAddress, cc.ship_city AS shipCity, cc.ship_state AS shipState, cc.ship_zip AS shipZip, cc.multiplier,
       r.ship_address AS batchAddress, r.ship_city AS batchCity, r.ship_state AS batchState, r.ship_zip AS batchZip,
       r.multi_room_ship, r.ship_cost, r.individual_bracket_buildout, r.payment_deposit, r.payment_del_ptl, r.payment_final, r.room_name,
       r.order_status, r.ship_date, r.delivery_date, r.ship_name, r.sample_seen_approved, r.sample_unseen_approved, r.sample_requested,
       r.sample_reference, r.esig, r.esig_ip, r.esig_time, r.custom_vin_info, r.product_type, r.days_to_ship, r.ship_via, r.payment_method,
       r.construction_method, r.species_grade, r.carcass_material, r.door_design, r.panel_raise_door, r.panel_raise_sd, r.panel_raise_td,
       r.style_rail_width, r.edge_profile, r.framing_bead, r.framing_options, r.drawer_boxes, r.drawer_guide, r.finish_code, r.sheen,
       r.glaze, r.glaze_technique, r.antiquing, r.worn_edges, r.distress_level, r.green_gard
    FROM rooms r
      LEFT JOIN sales_order so on r.so_parent = so.so_num
      LEFT JOIN contact c ON so.contact_id = c.id
      LEFT JOIN contact_customer cc on c.id = cc.contact_id
      LEFT JOIN contact_add_options cao on cc.billing_type = cao.id
    WHERE r.id = $room_id;");

    $this->contact = $contact_qry->fetch_assoc();
    $this->vin_schema = getVINSchema();
  }

  public function getGlobal($segment, $dbName = null) {
    $room_val = !empty($dbName) ? $dbName : $segment;

    $nsy = empty($this->contact[$room_val]) ? 'selected' : null;

    $option = "<optgroup label='Empty'><option value='' $nsy disabled>Not Selected Yet</option>";
    $prev_group = null;
    $addl_html = null;

    foreach($this->vin_schema[$segment] AS $element) {
      if((bool)$element['visible']) {
        if($prev_group !== $element['group']) {
          $option .= "</optgroup><optgroup label='{$element['group']}'>";
          $prev_group = $element['group'];
        }

        $selected = $this->contact[$room_val] === $element['key'] ? 'selected' : null;

        $val = strip_tags($element['value']);

        $option .= "<option value='{$element['key']}' $selected>$val</option>";

        $addl_html[$element['key']] = $element['addl_html'];
      }
    }

    $html_addl_out = null;

    foreach($addl_html AS $key => $html) {
      if(!empty($html)) {
        $html_addl_out .= "<div class='addl_select_html'>$html</div>";
      }
    }

    return "<select name='$room_val' id='$room_val' class='c_input' style='border:none;margin-left:-4px;font-weight:bold;'>$option</select> $html_addl_out";
  }
}