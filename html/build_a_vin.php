<?php
require '../includes/header_start.php';


?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="col-md-12">
                <form id="form_so_<?php echo $result['so_num']; ?>">
                    <div class="row">
                        <div class="col-md-8">
                            <table width="100%" class="table table-custom-nb label-right">
                                <tr>
                                    <td><label for="so_num">SO #</label></td>
                                    <td><input type="text" class="form-control" id="so_num" name="so_num" placeholder="SO Number" style="width:90px;" /></td>
                                    <td><label for="room">Room</label></td>
                                    <td><select class="form-control" id="room"><option value="--" disabled selected>--</option></select></td>
                                    <td><label for="iteration">Iteration</label></td>
                                    <td><select class="form-control" id="iteration"><option value="--" disabled selected>--</option></select></td>
                                    <td><label for="product_type">Product Type</label></td>
                                    <td>
                                        <select name="product_type" id="product_type" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'product_type'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td><label for="order_status">Order Status</label></td>
                                    <td>
                                        <select name="order_status" id="order_status" class="form-control">
                                            <option value="#">Quote</option>
                                            <option value="$">Job</option>
                                        </select>
                                    </td>
                                    <td><label for="days_to_ship">Days to Ship</label></td>
                                    <td>
                                        <select name="days_to_ship" id="days_to_ship" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'days_to_ship'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td><label for="dealer_code">Dealer</label></td>
                                    <td><input type="text" class="form-control" id="dealer_code" name="dealer_code" placeholder="Dealer Code" /></td>
                                </tr>
                                <tr>
                                    <td><label for="notes">Notes</label></td>
                                    <td colspan="13">
                                        <input type="text" name="notes" id="notes" placeholder="Notes..." class="form-control">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <table width="100%" class="table table-custom-nb label-right">
                                <tr><td colspan="2" class="text-md-center"><h5>Door, Drawer & Hardwood</h5></td></tr>
                                <tr>
                                    <td><label for="species_grade">Species Grade</label></td>
                                    <td>
                                        <select name="species_grade" id="species_grade" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'species_grade'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="construction_method">Construction Method</label></td>
                                    <td>
                                        <select name="construction_method" id="construction_method" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'construction_method'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="door_design">Door Design</label></td>
                                    <td>
                                        <select name="door_design" id="door_design" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'door_design'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" class="table table-custom-nb label-right" style="width:100%;">
                                <tr><td colspan="2" class="text-md-center"><h5>Panel Raise</h5></td></tr>
                                <tr>
                                    <td style="width:120px;"><label for="panel_raise_door">Door</label></td>
                                    <td>
                                        <select name="panel_raise_door" id="panel_raise_door" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'panel_raise'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="panel_raise_sd">Short Drawer</label></td>
                                    <td>
                                        <select name="panel_raise_sd" id="panel_raise_sd" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'panel_raise'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="panel_raise_td">Tall Drawer</label></td>
                                    <td>
                                        <select name="panel_raise_td" id="panel_raise_td" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'panel_raise'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="edge_profile">Edge Profile</label></td>
                                    <td>
                                        <select name="edge_profile" id="edge_profile" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'edge_profile'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="framing_bead">Framing Bead</label></td>
                                    <td>
                                        <select name="framing_bead" id="framing_bead" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'framing_bead'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="framing_options">Framing Options</label></td>
                                    <td>
                                        <select name="framing_options" id="framing_options" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'framing_options'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="style_rail_width">Style/Rail Width</label></td>
                                    <td>
                                        <select name="style_rail_width" id="style_rail_width" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'style_rail_width'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-2">
                            <table width="100%" class="table table-custom-nb label-right" style="width:100%;">
                                <tr><td colspan="2" class="text-md-center"><h5>Finish</h5></td></tr>
                                <tr>
                                    <td><label for="finish_type">Type</label></td>
                                    <td>
                                        <select name="finish_type" id="finish_type" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'finish_type'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="finish_code">Code</label></td>
                                    <td><input type="text" class="form-control" name="finish_code" id="finish_code" placeholder="XXXX" value="S001" ></td>
                                </tr>
                                <tr>
                                    <td><label for="sheen">Sheen</label></td>
                                    <td>
                                        <select name="sheen" id="sheen" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'sheen'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="glaze">Glaze Color</label></td>
                                    <td>
                                        <select name="glaze" id="glaze" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'glaze'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="glaze_technique">Glaze Technique</label></td>
                                    <td>
                                        <select name="glaze_technique" id="glaze_technique" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'glaze_technique'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="antiquing">Antiquing</label></td>
                                    <td>
                                        <select name="antiquing" id="antiquing" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'antiquing'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="worn_edges">Worn Edges</label></td>
                                    <td>
                                        <select name="worn_edges" id="worn_edges" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'worn_edges'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="distress_level">Distress Level</label></td>
                                    <td>
                                        <select name="distress_level" id="distress_level" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'distress_level'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-2">
                            <table width="100%" class="table table-custom-nb label-right">
                                <tr><td colspan="2" class="text-md-center"><h5>Carcass Exterior</h5></td></tr>
                                <tr>
                                    <td><label for="carcass_exterior_species">Species</label></td>
                                    <td>
                                        <select name="carcass_exterior_species" id="carcass_exterior_species" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_exterior_species'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="carcass_exterior_finish_type">Finish Type</label></td>
                                    <td>
                                        <select name="carcass_exterior_finish_type" id="carcass_exterior_finish_type" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_exterior_finish_type'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="carcass_exterior_finish_code">Finish Code</label></td>
                                    <td><input type="text" class="form-control" name="carcass_exterior_finish_code" id="carcass_exterior_finish_code" placeholder="XXXX" value="S001" ></td>
                                </tr>
                                <tr>
                                    <td><label for="carcass_exterior_glaze_color">Glaze Color</label></td>
                                    <td>
                                        <select name="carcass_exterior_glaze_color" id="carcass_exterior_glaze_color" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_exterior_glaze_color'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="carcass_exterior_glaze_technique">Glaze Technique</label></td>
                                    <td>
                                        <select name="carcass_exterior_glaze_technique" id="carcass_exterior_glaze_technique" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_exterior_glaze_technique'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-2">
                            <table width="100%" class="table table-custom-nb label-right">
                                <tr><td colspan="2" class="text-md-center"><h5>Carcass Interior</h5></td></tr>
                                <tr>
                                    <td><label for="carcass_interior_species">Species</label></td>
                                    <td>
                                        <select name="carcass_interior_species" id="carcass_interior_species" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_interior_species'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="carcass_interior_finish_type">Finish Type</label></td>
                                    <td>
                                        <select name="carcass_interior_finish_type" id="carcass_interior_finish_type" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_interior_finish_type'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="carcass_interior_finish_code">Finish Code</label></td>
                                    <td><input type="text" class="form-control" name="carcass_interior_finish_code" id="carcass_interior_finish_code" placeholder="XXXX" value="S001" ></td>
                                </tr>
                                <tr>
                                    <td><label for="carcass_interior_glaze_color">Glaze Color</label></td>
                                    <td>
                                        <select name="carcass_interior_glaze_color" id="carcass_interior_glaze_color" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_interior_glaze_color'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="carcass_interior_glaze_technique">Glaze Technique</label></td>
                                    <td>
                                        <select name="carcass_interior_glaze_technique" id="carcass_interior_glaze_technique" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'carcass_interior_glaze_technique'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-2">
                            <table width="100%" class="table table-custom-nb label-right">
                                <tr><td colspan="2" class="text-md-center"><h5>Drawer Boxes</h5></td></tr>
                                <tr>
                                    <td><label for="drawer_boxes">Drawer Boxes</label></td>
                                    <td>
                                        <select name="drawer_boxes" id="drawer_boxes" class="form-control">
                                            <?php
                                            $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'drawer_boxes'");

                                            while($segment = $segment_qry->fetch_assoc()) {
                                                echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-md-offset-3"><input type="text" class="form-control" name="vin_code" id="vin_code" placeholder="VIN Code" /> </div>
                    </div>

                    <div class="row" style="margin-top:12px;">
                        <div class="col-md-12 text-md-center"><button type="button" class="btn btn-primary waves-effect waves-light w-sm" id="create-vin">Create</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var sonum;

    function update(sonum) {
        $.post("/ondemand/livesearch/build_a_vin.php?search=room&so_num=" + sonum, function(data) {
            $("#room").html(data);

            $.post("/ondemand/livesearch/build_a_vin.php?search=iteration&so_num=" + sonum + "&room=" + $("#room option:selected").val(), function(data) {
                $("#iteration").html(data);
            });
        });
    }

    $("#so_num").autocomplete({
        source: '/ondemand/livesearch/build_a_vin.php?search=so_num',
        minLength: 2,
        select: function(e, ui) {
            sonum = ui.item.value;
            update(sonum);
        }
    });
</script>