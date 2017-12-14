<?php
require '../includes/header_start.php';
require '../includes/header_end.php';

$id = sanitizeInput($_REQUEST['room_id']);

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$id'");

if($room_qry->num_rows === 1) {
    $room = $room_qry->fetch_assoc();
}

function translateVIN($segment, $key) {
    global $dbconn;

    if($segment === 'finish_code') {
        $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE (segment = 'finish_code') AND `key` = '$key'");
    } else {
        $vin_qry = $dbconn->query("SELECT `value` FROM vin_schema WHERE segment = '$segment' AND `key` = '$key'");
    }

    if($vin_qry->num_rows === 1) {
        $vin = $vin_qry->fetch_assoc();
    } else {
        $vin = null;
    }

    return "{$key} = {$vin['value']}";
}

$style_rail_qry = $dbconn->query("SELECT value FROM vin_schema WHERE `key` = '{$room['style_rail_width']}' AND segment = 'style_rail_width'");
$style_rail = $style_rail_qry->fetch_assoc();

$style_rail = str_replace('-', ' ', $style_rail['value']);

echo "<script>var style_rail = '$style_rail';</script>";
?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <table id="door_list">
                        <tr><td colspan="7"><h1>Door List</h1></td></tr>
                        <tr>
                            <td colspan="7">SO: <?php echo "{$room['so_parent']}{$room['room']}-{$room['iteration']}"; ?></td>
                        </tr>
                        <tr>
                            <td colspan="7">Print Date: <?php echo date(DATE_DEFAULT); ?></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2">Edge Profile:</td>
                            <td colspan="2"><?php echo translateVIN('edge_profile', $room['edge_profile']); ?></td>
                            <td>Panel Raise:</td>
                            <td colspan="2"><?php echo translateVIN('panel_raise', $room['panel_raise_door']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2">Framing Bead:</td>
                            <td colspan="2"><?php echo translateVIN('framing_bead', $room['framing_bead']); ?></td>
                            <td>Option:</td>
                            <td><input type="text" id="option_1" placeholder="Option"></td>
                        </tr>
                        <tr>
                            <td colspan="7">&nbsp;</td>
                        </tr>
                        <tr>
                            <th>Line</th>
                            <th>Qty</th>
                            <th>Width</th>
                            <th>Height</th>
                            <th>Type</th>
                            <th>Hinge</th>
                            <th>Cab #</th>
                        </tr>
                        <tr>
                            <td><strong>1</strong></td>
                            <td><input type="text" id="qty_1" placeholder="Qty" /></td>
                            <td><input type="text" id="width_1" placeholder="Width" /></td>
                            <td><input type="text" id="height_1" placeholder="Height" /></td>
                            <td><input type="text" id="type_1" placeholder="Type" /></td>
                            <td><input type="text" id="hinge_1" placeholder="Hinge" /></td>
                            <td><input type="text" id="cab_1" placeholder="Cab" /></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-md-12">
                    <table id="door_calc">
                        <tr>
                            <th>Line</th>
                            <th>Door Qty</th>
                            <th>Door Width</th>
                            <th>Door Height</th>
                            <th>Stile Width</th>
                            <th>Rail Height</th>
                            <th>Addl. Clearance</th>
                        </tr>
                        <tr>
                            <td id="line_1_subtotal">1</td>
                            <td id="qty_1_subtotal"></td>
                            <td id="width_1_subtotal"></td>
                            <td id="height_1_subtotal"></td>
                            <td id="style_1_subtotal"></td>
                            <td id="rail_1_subtotal"></td>
                            <td id="addl_clear_1_subtotal"><input type="text" id="addl_clear_input_1" placeholder="Addl Clearance" /></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-md-12">
                    <table id="board_cut_list">
                        <tr>
                            <td colspan="4"><h1>Board Stock Cut List</h1></td>
                        </tr>
                        <tr>
                            <th>Qty</th>
                            <th>Description</th>
                            <th>Width x Length</th>
                            <th>Cabinet #</th>
                            <th>Sq. Ft.</th>
                        </tr>
                        <tr>
                            <td id="qty_1_cut"></td>
                            <td id="desc_1_cut"></td>
                            <td id="width_length_1_cut"></td>
                            <td id="cab_1_cut"></td>
                            <td id="sqft_1_cut"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var linesAdded = [];

    $("body")
        .on("blur", "[id^='height_']", function() {
            var line_num = $(this).attr('id').replace('height_', '');
            var next_line = Number(line_num) + 1;

            var line = '<tr>\n' +
                '  <td><strong>' + next_line + '</strong></td>\n' +
                '  <td><input type="text" id="qty_' + next_line + '" placeholder="Qty" /></td>\n' +
                '  <td><input type="text" id="width_' + next_line + '" placeholder="Width" /></td>\n' +
                '  <td><input type="text" id="height_' + next_line + '" placeholder="Height" /></td>\n' +
                '  <td><input type="text" id="type_' + next_line + '" placeholder="Type" /></td>\n' +
                '  <td><input type="text" id="hinge_' + next_line + '" placeholder="Hinge" /></td>\n' +
                '  <td><input type="text" id="cab_' + next_line + '" placeholder="Cab" /></td>\n' +
                '</tr>';

            var line2 = '<tr>\n' +
                '  <td><strong>' + next_line + '</strong></td>\n' +
                '  <td id="qty_' + next_line + '_subtotal"></td>\n' +
                '  <td id="width_' + next_line + '_subtotal"></td>\n' +
                '  <td id="height_' + next_line + '_subtotal"></td>\n' +
                '  <td id="style_' + next_line + '_subtotal"></td>\n' +
                '  <td id="rail_' + next_line + '_subtotal"></td>\n' +
                '  <td id="addl_clear_' + next_line + '_subtotal"><input type="text" id="addl_clear_input_' + next_line + '" placeholder="Addl Clearance" /></td>\n' +
                '</tr>';

            if($(this).val() !== '' && $.inArray(next_line, linesAdded) === -1) {
                $("#door_list").find("tr:last").after(line);
                $("#door_calc").find("tr:last").after(line2);
                linesAdded.push(next_line);
            }

            var height = $(this).val();

            $(this).val(math.fraction(height));
        })
        .on("keyup", "input[type='text']", function() {
            var line = $(this).attr('id').split('_');
            var line_num = line[1];
            var height = $("#height_" + line_num).val();

            $(this).val($(this).val().toUpperCase());

            $("#" + line[0] + "_" + line_num + "_subtotal").html($(this).val());
            $("#style_" + line_num + "_subtotal").html(style_rail);

            if(math.smallerEq(math.fraction(height), 8)) {
                $("#rail_" + line_num + "_subtotal").html("1 1/2\"");
            } else {
                $("#rail_" + line_num + "_subtotal").html(style_rail);
            }


        })
    ;
</script>
</body>
</html>