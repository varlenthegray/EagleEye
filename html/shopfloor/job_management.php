<?php
require("../../includes/header_start.php");

$so_id = sanitizeInput($_POST['so_id']);
$roomID = sanitizeInput($_POST['roomID']);

$cur_iteration_qry = $dbconn->query("SELECT iteration FROM rooms WHERE so_parent = '$so_id' AND room = 'A' ORDER BY iteration DESC LIMIT 0,1");

if($cur_iteration_qry->num_rows > 0) {
    $cur_iteration = $cur_iteration_qry->fetch_assoc();
    $next_iteration = $cur_iteration['iteration'] + 0.01;
} else {
    $next_iteration = .01;
}

if($_REQUEST['action'] === 'add') {
    ?>

    <h4 id="room_info_title">Add New Room (SO# <?php echo $so_id; ?>)</h4>

    <form name="room_adjustment" id="room_adjustment">
        <input type="hidden" value="<?php echo $so_id; ?>" name="add_to_sonum" id="add_to_sonum">

        <table style="width: 100%;">
            <tr>
                <td style="width: 20%;">
                    <fieldset class="form-group">
                        <label for="room">Room</label>
                        <select name="room" id="room" class="form-control">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                            <option value="F">F</option>
                            <option value="G">G</option>
                            <option value="H">H</option>
                            <option value="J">J</option>
                            <option value="K">K</option>
                            <option value="L">L</option>
                            <option value="M">M</option>
                            <option value="N">N</option>
                            <option value="P">P</option>
                            <option value="Q">Q</option>
                            <option value="R">R</option>
                            <option value="S">S</option>
                            <option value="T">T</option>
                            <option value="U">U</option>
                            <option value="V">V</option>
                            <option value="W">W</option>
                            <option value="X">X</option>
                            <option value="Y">Y</option>
                            <option value="Z">Z</option>
                        </select>
                    </fieldset>
                </td>
                <td style="width: 20%;">
                    <fieldset class="form-group">
                        <label for="room_name">Room Name</label>
                        <input type="text" class="form-control" id="room_name" name="room_name" placeholder="Room Name">
                    </fieldset>
                </td>
                <td style="width: 20%;">
                    <fieldset class="form-group">
                        <label for="product_type">Product Type</label>
                        <select name="product_type" id="product_type" class="form-control" tabindex="5">
                            <option value="Cabinet" selected>Cabinet</option>
                            <option value="Closet">Closet</option>
                            <option value="Sample">Sample</option>
                            <option value="Display">Display</option>
                            <option value="Add-on">Add-on</option>
                            <option value="Warranty">Warranty</option>
                        </select>
                    </fieldset>
                </td>
                <td style="width: 20%;">
                    <fieldset class="form-group">
                        <label for="iteration">Iteration</label> <!-- TODO: Iteration auto-incremement JS code when changing room letter -->
                        <input type="text" name="iteration" id="iteration" class="form-control" maxlength="5" placeholder="Iteration" value="<?php echo $next_iteration; ?>" readonly>
                    </fieldset>
                </td>
                <td style="width: 20%;">
                    <fieldset class="form-group">
                        <label for="remodel_required">Remodel Required</label>
                        <select name="remodel_required" id="remodel_required" class="form-control" tabindex="7">
                            <option value="1">Yes</option>
                            <option value="0" selected>No</option>
                        </select>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <div class="radio">
                        <input type="radio" name="viewBracket" id="viewBracket_sales" value="Sales">
                        <label for="viewBracket_sales">Sales Bracket</label>
                    </div>
                    <div class="radio">
                        <input type="radio" name="viewBracket" id="viewBracket_sample" value="Sample">
                        <label for="viewBracket_sample">Sample Bracket</label>
                    </div>
                    <div class="radio">
                        <input type="radio" name="viewBracket" id="viewBracket_preprod" value="Pre-Production">
                        <label for="viewBracket_preprod">Pre-Production Bracket</label>
                    </div>
                    <div class="radio">
                        <input type="radio" name="viewBracket" id="viewBracket_doordrawer" value="Door/Drawer">
                        <label for="viewBracket_doordrawer">Door/Drawer Bracket</label>
                    </div>
                    <div class="radio">
                        <input type="radio" name="viewBracket" id="viewBracket_laminate" value="Laminate">
                        <label for="viewBracket_laminate">Laminate Bracket</label>
                    </div>
                    <div class="radio">
                        <input type="radio" name="viewBracket" id="viewBracket_box" value="Box">
                        <label for="viewBracket_box">Box Bracket</label>
                    </div>
                    <div class="radio">
                        <input type="radio" name="viewBracket" id="viewBracket_custom" value="Custom">
                        <label for="viewBracket_custom">Custom Bracket</label>
                    </div>
                    <div class="radio">
                        <input type="radio" name="viewBracket" id="viewBracket_install" value="Install">
                        <label for="viewBracket_install">Install Bracket</label>
                    </div>
                </td>
                <td colspan="3">
                    <div id="sales_bracket_topublish" style="display: none;">
                        <?php
                        //$pub_qry = $dbconn->query("SELECT * FROM rooms WHERE ") TODO: What am I accomplishing with this?
                        ?>
                        <div class="row bracket-header-custom">
                            <div class="col-md-8"><h5><label for="sales_bracket_adjustments">Sales Bracket</label></h5></div>
                        </div>
                        <select multiple="multiple" class="multi-select" id="sales_bracket_adjustments" name="sales_bracket_adjustments[]" data-plugin="multiselect">
                            <?php
                            $options_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Sales' AND always_visible = FALSE");

                            if ($options_qry->num_rows > 0) {
                                while ($options = $options_qry->fetch_assoc()) {
                                    echo "<option value='{$options['id']}'>{$options['op_id']} - {$options['job_title']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div id="sample_bracket_topublish" style="display: none;">
                        <div class="row bracket-header-custom">
                            <div class="col-md-8"><h5><label for="sample_bracket_adjustments">Sample Bracket</label></h5></div>
                        </div>
                        <select multiple="multiple" class="multi-select" id="sample_bracket_adjustments" name="sample_bracket_adjustments[]" data-plugin="multiselect">
                            <?php
                            $options_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Sample' AND always_visible = FALSE");

                            if ($options_qry->num_rows > 0) {
                                while ($options = $options_qry->fetch_assoc()) {
                                    echo "<option value='{$options['id']}'>{$options['op_id']} - {$options['job_title']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div id="preprod_bracket_topublish" style="display: none;">
                        <div class="row bracket-header-custom">
                            <div class="col-md-8"><h5><label for="preprod_bracket_adjustments">Pre-Production Bracket</label></h5></div>
                        </div>
                        <select multiple="multiple" class="multi-select" id="preprod_bracket_adjustments" name="preprod_bracket_adjustments[]" data-plugin="multiselect">
                            <?php
                            $options_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Pre-Production' AND always_visible = FALSE");

                            if ($options_qry->num_rows > 0) {
                                while ($options = $options_qry->fetch_assoc()) {
                                    echo "<option value='{$options['id']}'>{$options['op_id']} - {$options['job_title']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div id="doordrawer_bracket_topublish" style="display: none;">
                        <div class="row bracket-header-custom">
                            <div class="col-md-8"><h5><label for="doordrawer_bracket_adjustments">Door/Drawer Bracket</label></h5></div>
                        </div>
                        <select multiple="multiple" class="multi-select" id="doordrawer_bracket_adjustments" name="doordrawer_bracket_adjustments[]" data-plugin="multiselect">
                            <?php
                            $options_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Drawer & Doors' AND always_visible = FALSE");

                            if ($options_qry->num_rows > 0) {
                                while ($options = $options_qry->fetch_assoc()) {
                                    echo "<option value='{$options['id']}'>{$options['op_id']} - {$options['job_title']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div id="laminate_bracket_topublish" style="display: none;">
                        <div class="row bracket-header-custom">
                            <div class="col-md-8"><h5><label for="laminate_bracket_adjustments">Laminate Bracket</label></h5></div>
                        </div>
                        <select multiple="multiple" class="multi-select" id="laminate_bracket_adjustments" name="laminate_bracket_adjustments[]" data-plugin="multiselect">
                            <?php
                            $options_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Laminate' AND always_visible = FALSE");

                            if ($options_qry->num_rows > 0) {
                                while ($options = $options_qry->fetch_assoc()) {
                                    echo "<option value='{$options['id']}'>{$options['op_id']} - {$options['job_title']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div id="box_bracket_topublish" style="display: none;">
                        <div class="row bracket-header-custom">
                            <div class="col-md-8"><h5><label for="box_bracket_adjustments">Box Bracket</label></h5></div>
                        </div>
                        <select multiple="multiple" class="multi-select" id="box_bracket_adjustments" name="box_bracket_adjustments[]" data-plugin="multiselect">
                            <?php
                            $options_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Box' AND always_visible = FALSE");

                            if ($options_qry->num_rows > 0) {
                                while ($options = $options_qry->fetch_assoc()) {
                                    echo "<option value='{$options['id']}'>{$options['op_id']} - {$options['job_title']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div id="custom_bracket_topublish" style="display: none;">
                        <div class="row bracket-header-custom">
                            <div class="col-md-8"><h5><label for="custom_bracket_adjustments">Custom Bracket</label></h5></div>
                        </div>
                        <select multiple="multiple" class="multi-select" id="custom_bracket_adjustments" name="custom_bracket_adjustments[]" data-plugin="multiselect">
                            <?php
                            $options_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Custom' AND always_visible = FALSE");

                            if ($options_qry->num_rows > 0) {
                                while ($options = $options_qry->fetch_assoc()) {
                                    echo "<option value='{$options['id']}'>{$options['op_id']} - {$options['job_title']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div id="install_bracket_topublish" style="display: none;">
                        <div class="row bracket-header-custom">
                            <div class="col-md-8"><h5><label for="install_bracket_adjustments">Install Bracket</label></h5></div>
                        </div>
                        <select multiple="multiple" class="multi-select" id="install_bracket_adjustments" name="install_bracket_adjustments[]" data-plugin="multiselect">
                            <?php
                            $options_qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Install' AND always_visible = FALSE");

                            if ($options_qry->num_rows > 0) {
                                while ($options = $options_qry->fetch_assoc()) {
                                    echo "<option value='{$options['id']}'>{$options['op_id']} - {$options['job_title']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </td>
                <td valign="top" style="text-align: center;">
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs" id="save_publish" name="save_publish">Save/Publish</button>
                </td>
            </tr>
            <tr>
                <td colspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <div class="checkbox checkbox-primary">
                        <input id="add_notes" type="checkbox">
                        <label for="add_notes">Add Notes</label>
                    </div>
                </td>
                <td colspan="3">
                    <fieldset class="form-group" id="room_note_visible" style="display: none;">
                        <label for="room_notes">Room Notes</label>
                        <textarea class="form-control" id="room_notes" name="room_notes" maxlength="65530" placeholder="Room Notes" rows="3"
                                  data-toggle="popover" data-placement="top" data-trigger="focus" title="" data-html="true"
                                  data-content="<table style='font-size: 9px;'>
                                <tr>
                                    <td>CON = Conestoga</td>
                                    <td class='text-md-right'>RW = Rework</td>
                                </tr>
                                <tr>
                                    <td>DEL = Delivery</td>
                                    <td class='text-md-right'>S/B = Scheduled Back</td>
                                </tr>
                                <tr>
                                    <td>DPL = Diminishing Punch List</td>
                                    <td class='text-md-right'>SEL = Selections</td>
                                </tr>
                                <tr>
                                    <td>EM = Email</td>
                                    <td class='text-md-right'>T/W = This Week</td>
                                </tr>
                                <tr>
                                    <td>ETA = Estimated Time of Arrival</td>
                                    <td class='text-md-right'>W/A = Will Advise</td>
                                </tr>
                                <tr>
                                    <td>FU = Follow Up</td>
                                    <td class='text-md-right'>W/C = Will Contact</td>
                                </tr>
                                <tr>
                                    <td>N/A = Not Available</td>
                                    <td class='text-md-right'>WO = Work Order</td>
                                </tr>
                            </table>" data-original-title="Abbreviations"></textarea>
                    </fieldset>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="8">
                    <button type="button" id="room_save" name="room_save" class="btn btn-success waves-effect waves-light w-xs">Save</button>
                    <button type="button" id="manage_brackets" name="manage_brackets" class="btn btn-success waves-effect waves-light w-xs">Manage Bracket</button>
                </td>
            </tr>
        </table>
    </form>

    <?php
} elseif($_REQUEST['action'] === 'view') {
    $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$roomID'");

    if($room_qry->num_rows === 1) {
        $room = $room_qry->fetch_assoc();
    }
    ?>

    <h4 id="room_info_title">View Room <?php echo $room['room']; ?> (SO# <?php echo $room['so_parent']; ?>)</h4>

    <form name="room_adjustment" id="room_adjustment">
        <input type="hidden" value="<?php echo $room['so_parent']; ?>" name="add_to_sonum" id="add_to_sonum">

        <div class="row">
            <div class="col-md-12">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 20%;">
                            <fieldset class="form-group">
                                <label for="room">Room</label>
                                <input type="text" class="form-control" name="room" id="room" value="<?php echo $room['room']; ?>" readonly />
                            </fieldset>
                        </td>
                        <td style="width: 20%;">
                            <fieldset class="form-group">
                                <label for="room_name">Room Name</label>
                                <input type="text" class="form-control" id="room_name" name="room_name" value="<?php echo $room['room_name']; ?>" readonly>
                            </fieldset>
                        </td>
                        <td style="width: 20%;">
                            <fieldset class="form-group">
                                <label for="product_type">Product Type</label>
                                <input type="text" class="form-control" id="room_name" name="room_name" value="<?php echo $room['product_type']; ?>" readonly>
                            </fieldset>
                        </td>
                        <td style="width: 20%;">
                            <fieldset class="form-group">
                                <label for="iteration">Iteration</label> <!-- TODO: Iteration auto-incremement JS code when changing room letter -->
                                <input type="text" name="iteration" id="iteration" class="form-control" maxlength="5" placeholder="Iteration" value="<?php echo $next_iteration; ?>" readonly>
                            </fieldset>
                        </td>
                        <td style="width: 20%;">
                            <fieldset class="form-group">
                                <label for="remodel_required">Remodel Required</label>
                                <input type="text" class="form-control" id="room_name" name="room_name" value="<?php echo ((bool)$room['remodel_reqd']) ? "Yes":"No"; ?>" readonly>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 5px 0;"><button type="button" style="min-width: 101px;" class="btn btn-primary waves-effect waves-light w-xs" id="btn_manage_bracket" name="manage_bracket">Manage Bracket</button></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><button type="button" style="min-width: 101px;" class="btn btn-primary waves-effect waves-light w-xs" id="btn_add_iteration" name="add_iteration">Add Iteration</button></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><button type="button" style="min-width: 101px;" class="btn btn-primary waves-effect waves-light w-xs" id="btn_add_warranty" name="add_warranty">Add Warranty</button></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><button type="button" style="min-width: 101px;" class="btn btn-primary waves-effect waves-light w-xs" id="btn_add_addon" name="add_addon">Add Add-on</button></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><button type="button" style="min-width: 101px;" class="btn btn-primary waves-effect waves-light w-xs" id="btn_add_note" name="manage_bracket">Add Note</button></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><button type="button" style="min-width: 101px;" class="btn btn-primary waves-effect waves-light w-xs" id="btn_close" name="btn_close">Close</button></td>
                    </tr>
                </table>
            </div>

            <div class="col-md-8">
                <table style="width: 100%">
                    <tr>
                        <?php
                        function generateSingleOps($bracket) {
                            global $dbconn;
                            global $room;
                            $output = '';

                            $ind_bracket = json_decode($room['individual_bracket_buildout']);

                            foreach($ind_bracket as $operation) {
                                $op_info_qry = $dbconn->query("SELECT * FROM operations WHERE id = '$operation'");

                                if($op_info_qry->num_rows > 0) {
                                    $op_info = $op_info_qry->fetch_assoc();

                                    if($op_info['bracket'] === $bracket) {
                                        $output .= "<option value='{$op_info['id']}'>{$op_info['op_id']}-{$op_info['job_title']}</option>";
                                    }
                                }
                            }

                            return $output;
                        }
                        ?>
                        <td>&nbsp;</td>
                        <td>
                            <fieldset class="form-group">
                                <label for="sales_bracket">Sales Bracket</label>
                                <select id="sales_bracket" name="sales_bracket" class="form-control">
                                    <?php echo generateSingleOps("Sales"); ?>
                                </select>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset class="form-group">
                                <label for="pre_prod_bracket">Pre-production Bracket</label>
                                <select id="pre_prod_bracket" name="pre_prod_bracket" class="form-control">
                                    <?php echo generateSingleOps("Pre-Production"); ?>
                                </select>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset class="form-group">
                                <label for="sample_bracket">Sample Bracket</label>
                                <select id="sample_bracket" name="sample_bracket" class="form-control">
                                    <?php echo generateSingleOps("Sample"); ?>
                                </select>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <fieldset class="form-group">
                                <label for="door_drawer_bracket">Door/Drawer Bracket</label>
                                <select id="door_drawer_bracket" name="door_drawer_bracket" class="form-control">
                                    <?php echo generateSingleOps("Drawer & Doors"); ?>
                                </select>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset class="form-group">
                                <label for="custom_bracket">Custom Bracket</label>
                                <select id="custom_bracket" name="custom_bracket" class="form-control">
                                    <?php echo generateSingleOps("Custom"); ?>
                                </select>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset class="form-group">
                                <label for="box_bracket">Box Bracket</label>
                                <select id="box_bracket" name="box_bracket" class="form-control">
                                    <?php echo generateSingleOps("Box"); ?>
                                </select>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </form>

<?php
}
?>