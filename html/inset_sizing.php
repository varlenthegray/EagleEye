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
$style_rail = str_replace('"', '', $style_rail);

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
                            <td><input type="text" id="qty_1" data-id="1" placeholder="Qty" /></td>
                            <td><input type="text" id="width_1" data-id="1" placeholder="Width" /></td>
                            <td><input type="text" id="height_1" data-id="1" placeholder="Height" /></td>
                            <td><input type="text" id="type_1" data-id="1" placeholder="Type" /></td>
                            <td><input type="text" id="hinge_1" data-id="1" placeholder="Hinge" /></td>
                            <td><input type="text" id="cab_1" data-id="1" placeholder="Cab" /></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-md-12">
                    <table id="door_calc" style="min-width:897px;">
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
                            <td id="line_1_subtotal" data-id="1"><strong>1</strong></td>
                            <td id="qty_1_subtotal" data-id="1"></td>
                            <td id="width_1_subtotal" data-id="1"></td>
                            <td id="height_1_subtotal" data-id="1"></td>
                            <td id="style_1_subtotal" data-id="1"></td>
                            <td id="rail_1_subtotal" data-id="1"></td>
                            <td id="addl_clear_1_subtotal"><input type="text" id="addl_clear_input_1" placeholder="Addl Clearance" /></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-md-12">
                    <table id="board_cut_list">
                        <thead>
                            <tr>
                                <td colspan="4"><h1>Board Stock Cut List</h1></td>
                            </tr>
                            <tr>
                                <th>Qty</th>
                                <th>Description</th>
                                <th>Width x Length</th>
                                <th>Sq. Ft.</th>
                                <th>Cab #</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function convertFract(value) {
        var fraction;
        var broken = value.split(".");

        if(broken[1] !== undefined) {
            fraction = math.format(math.fraction("." + broken[1]), {fraction: 'ratio'});

            return broken[0] + " " + fraction;
        } else {
            return value;
        }
    }

    var linesAdded = [];
    var styleArray = [];
    var rail;
    var line = [];

    $("body")
        .on("blur", "[id^='height_']", function() {
            var line_num = $(this).attr('id').replace('height_', '');
            var next_line = Number(line_num) + 1;
            var height = $("#height_" + line_num).val();

            if(height !== '') {
                if(math.smallerEq(math.fraction(height), 8)) {
                    $("#rail_" + line_num + "_subtotal").html("1 1/2");
                    rail = "1 1/2";
                } else {
                    $("#rail_" + line_num + "_subtotal").html(style_rail);
                    rail = style_rail;
                }
            }

            var line = '<tr>\n' +
                '  <td><strong>' + next_line + '</strong></td>\n' +
                '  <td><input type="text" id="qty_' + next_line + '" data-id="' + next_line + '" placeholder="Qty" /></td>\n' +
                '  <td><input type="text" id="width_' + next_line + '" data-id="' + next_line + '" placeholder="Width" /></td>\n' +
                '  <td><input type="text" id="height_' + next_line + '" data-id="' + next_line + '" placeholder="Height" /></td>\n' +
                '  <td><input type="text" id="type_' + next_line + '" data-id="' + next_line + '" placeholder="Type" /></td>\n' +
                '  <td><input type="text" id="hinge_' + next_line + '" data-id="' + next_line + '" placeholder="Hinge" /></td>\n' +
                '  <td><input type="text" id="cab_' + next_line + '" data-id="' + next_line + '" placeholder="Cab" /></td>\n' +
                '</tr>';

            var line2 = '<tr>\n' +
                '  <td><strong>' + next_line + '</strong></td>\n' +
                '  <td id="qty_' + next_line + '_subtotal" data-id="' + next_line + '"></td>\n' +
                '  <td id="width_' + next_line + '_subtotal" data-id="' + next_line + '"></td>\n' +
                '  <td id="height_' + next_line + '_subtotal" data-id="' + next_line + '"></td>\n' +
                '  <td id="style_' + next_line + '_subtotal" data-id="' + next_line + '"></td>\n' +
                '  <td id="rail_' + next_line + '_subtotal" data-id="' + next_line + '"></td>\n' +
                '  <td id="addl_clear_' + next_line + '_subtotal"><input type="text" id="addl_clear_input_' + next_line + '" placeholder="Addl Clearance" /></td>\n' +
                '</tr>';

            if($(this).val() !== '' && $.inArray(next_line, linesAdded) === -1) {
                $("#door_list").find("tr:last").after(line);
                $("#door_calc").find("tr:last").after(line2);
                linesAdded.push(next_line);
            }

            $(this).val(convertFract($(this).val()));
        })
        .on("blur", "[id^='width_']", function() {
            // get the current line number
            var line_num = $(this).attr('id').replace('width_', '');

            // convert the line to prime + fraction
            $(this).val(convertFract($(this).val()));
        })
        .on("keyup blur", "input[type='text']", function() {
            var id = $(this).data('id');

            // on every input we're going to convert those inputs to uppercase
            $(this).val($(this).val().toUpperCase());

            var line_info = $(this).attr('id').split('_'); // obtain the line information for that specific line
            var line_num = line_info[1]; // get the line number

            $("#" + line_info[0] + "_" + line_num + "_subtotal").html($(this).val()); // set the subtotal for whatever input we're entering in
            $("#style_" + line_num + "_subtotal").html(style_rail); // style_rail is defined via PHP above

            var qty = $("#qty_" + line_num).val();

            var height = $("#height_" + line_num);
            var width = $("#width_" + line_num);

            if(height.val().length > 0) {
                var style_wxl = style_rail + " x " + height.val();

                var style_sqft = (math.format(math.divide(math.number(math.fraction(style_rail)), 12), 4) * math.format(math.divide(math.number(math.fraction(height.val())), 12), 4)) * qty;
            }

            if(width.val().length > 0) {
                var rail_length = math.format(math.subtract(math.fraction(width.val()), 3.625), {fraction: 'decimal'});

                rail_length = rail_length.split("."); // break it into parts
                var rail_final = rail_length[0] + " " + math.format(math.fraction("." + rail_length[1]), {fraction: 'fixed'});


            }

            if(width.val().length > 0 && height.val().length > 0) {
                var rail_wxl = rail_final + " x " + rail;
                var rail_sqft = (math.format(math.divide(math.number(math.fraction(rail_final)), 12), 4) * math.format(math.divide(math.number(math.fraction(rail)), 12), 4)) * qty;

                style_sqft = math.format(style_sqft, 2);
                rail_sqft = math.format(rail_sqft, 2);

                var cab = $("#cab_" + line_num).val();

                line[line_num] = "<tr>\n" +
                    "  <td>" + qty + "</td>\n" +
                    "  <td>Left Stiles</td>\n" +
                    "  <td>" + style_wxl + "</td>\n" +
                    "  <td>" + style_sqft + "</td>\n" +
                    "  <td>" + cab + "</td>\n" +
                    "</tr>\n" +
                    "<tr>\n" +
                    "  <td>" + qty + "</td>\n" +
                    "  <td>Right Stiles</td>\n" +
                    "  <td>" + style_wxl + "</td>\n" +
                    "  <td>" + style_sqft + "</td>\n" +
                    "  <td>" + cab + "</td>\n" +
                    "</tr>" +
                    "<tr>\n" +
                    "  <td>" + qty + "</td>\n" +
                    "  <td>Top Rails</td>\n" +
                    "  <td>" + rail_wxl + "</td>\n" +
                    "  <td>" + rail_sqft + "</td>\n" +
                    "  <td>" + cab + "</td>\n" +
                    "</tr>" +
                    "<tr>\n" +
                    "  <td>" + qty + "</td>\n" +
                    "  <td>Bottom Rails</td>\n" +
                    "  <td>" + rail_wxl + "</td>\n" +
                    "  <td>" + rail_sqft + "</td>\n" +
                    "  <td>" + cab + "</td>\n" +
                    "</tr>";
            }

            var output = '';

            for(var key in line) {
                output += line[key];
            }

            $("#board_cut_list").find("tbody").html(output);
        })
    ;
</script>
</body>
</html>