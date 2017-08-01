<?php
require '../includes/header_start.php';


?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="col-md-12">
                <form id="form_so_<?php echo $result['so_num']; ?>">
                    <table width="100%" class="table table-custom-nb label-right">
                        <tr>
                            <td><label for="so_num">SO #</label></td>
                            <td><input type="text" class="form-control" id="so_num" name="so_num" placeholder="SO Number" /></td>
                            <td><label for="room">Room</label></td>
                            <td><select class="form-control" id="room"><option value="--" disabled selected>--</option></select></td>
                            <td><label for="iteration">Iteration</label></td>
                            <td><select class="form-control" id="iteration"><option value="--" disabled selected>--</option></select></td>
                        </tr>
                        <tr>
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
                        <tr>
                            <td><label for="panel_raise">Panel Raise</label></td>
                            <td>
                                <select name="panel_raise" id="panel_raise" class="form-control">
                                    <?php
                                    $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'panel_raise'");

                                    while($segment = $segment_qry->fetch_assoc()) {
                                        echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
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
                        <tr>
                            <td><label for="finish_type">Finish Type</label></td>
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
                            <td><label for="standard_wiping_stains">Standard Wiping Stains</label></td>
                            <td>
                                <select name="standard_wiping_stains" id="standard_wiping_stains" class="form-control">
                                    <?php
                                    $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'standard_wiping_stains'");

                                    while($segment = $segment_qry->fetch_assoc()) {
                                        echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><label for="colourtone_paints">Colourtone Paints</label></td>
                            <td>
                                <select name="colourtone_paints" id="colourtone_paints" class="form-control">
                                    <?php
                                    $segment_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'colourtone_paints'");

                                    while($segment = $segment_qry->fetch_assoc()) {
                                        echo "<option value='{$segment['key']}'>{$segment['value']}</option>";
                                    }
                                    ?>
                                </select>
                            </td>
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
                            <td><label for="glaze">Glaze</label></td>
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
                            <td><label for="carcass_exterior_species">Carcass Exterior Species</label></td>
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
                            <td><label for="carcass_exterior_finish_type">Carcass Exterior Finish Type</label></td>
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
                            <td><label for="carcass_exterior_glaze_color">Carcass Exterior Glaze Color</label></td>
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
                            <td><label for="carcass_exterior_glaze_technique">Carcass Exterior Glaze Technique</label></td>
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
                            <td><label for="carcass_interior_species">Carcass Interior Species</label></td>
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
                            <td><label for="carcass_interior_finish_type">Carcass Interior Finish Type</label></td>
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
                            <td><label for="carcass_interior_glaze_color">Carcass Interior Glaze Color</label></td>
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
                            <td><label for="carcass_interior_glaze_technique">Carcass Interior Glaze Technique</label></td>
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
                        <tr>
                            <td colspan="10"><button type="button" class="pull-right btn btn-primary waves-effect waves-light w-sm">Update</button></td>
                        </tr>
                    </table>
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

    $('body')
        .on("change", "#room", function() {
            $.post("/ondemand/livesearch/build_a_vin.php?search=iteration&so_num=" + sonum + "&room=" + $("#room option:selected").val(), function(data) {
                $("#iteration").html(data);
            })
            .on("focus", "#room,#iteration", function() {

            });
    });
</script>