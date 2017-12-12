<?php
require '../includes/header_start.php';

outputPHPErrs();

$id = sanitizeInput($_REQUEST['room_id']);

$info_qry = $dbconn->query("SELECT sales_order.*, rooms.* FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = '$id'");
$info = $info_qry->fetch_assoc();

function translateVIN($segment, $key) {
    global $dbconn;

    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$segment' AND `key` = '$key'");
    $vin = $vin_qry->fetch_assoc();

    return ($vin['value'] === '') ? "<span class='highlight'>_____________</span><br />" : $vin['value'];
}

function displayOrder($ordered_var, $human_name) {
    if(!empty($ordered_var)) {
        if($ordered_var > 1) {
            $plural = "s";
        } else {
            $plural = '';
        }

        echo "<tr>
                  <td>($ordered_var) {$human_name}{$plural}</td>
                  <td>_________</td>
                  <td>_________</td>
              </tr>";

        return 1;
    } else {
        return 0;
    }
}
?>

<html>
<head>
    <link href="css/sample.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>

<!--<body onload="printMe()">-->
<body>

<div id="wrapper">
    <div id="header">
        <div id="header-left">
            <div id="page_type">
                <table>
                    <tr>
                        <td colspan="2" id="page_type_header">Sample Request</td>
                    </tr>
                    <tr>
                        <td class="definition">Dealer PO#:</td>
                        <td class="value"><?php echo $info['so_parent']; ?></td>
                    </tr>
                    <tr>
                        <td class="definition">Room/Area:</td>
                        <td class="value"><?php echo $info['room']; ?></td>
                    </tr>
                    <tr>
                        <td class="definition">Sequence:</td>
                        <td class="value"><?php echo substr($info['iteration'], 0, 1); ?></td>
                    </tr>
                    <tr>
                        <td class="definition">Iteration:</td>
                        <td class="value"><?php echo substr($info['iteration'], -3, 3); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="logo_container">
            <div id="logo"><img src="/assets/images/smc_logo.png" width="170px" /></div>

            <div id="company_info">
                Stone Mountain Cabinetry, Inc.<br />
                206 Vista Blvd<br/>
                Arden, NC 28704<br />
                828.966.9000<br/>
                orders@smcm.us
            </div>
        </div>

        <div id="header-right">
            <div id="page_info">
                <table>
                    <tr>
                        <td># of Pages:</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        <td class="b"><?php echo date('n/j/Y'); ?></td>
                    </tr>
                    <tr>
                        <td>Product Type:</td>
                        <td class="b">Sample</td>
                    </tr>
                    <tr>
                        <td>Lead Time:</td>
                        <td class="b">
                            <?php
                            $ltime_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'days_to_ship' AND `key` = '{$info['days_to_ship']}'");
                            $ltime = $ltime_qry->fetch_assoc();

                            echo (trim($ltime['value']) === '') ? "<span class='highlight'>________________</span><br />" : "{$ltime['value']}<br />";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Ship VIA:</strong></td>
                        <td><?php echo (trim($info['vin_ship_via']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['vin_ship_via']}<br />"; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Ship To:</strong></td>
                        <td>
                            <?php
                            echo (trim($info['ship_site']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['ship_site']}<br />";
                            echo "<br />";
                            echo (trim($info['name_1']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['name_1']}<br />";
                            echo (trim($info['secondary_addr']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['secondary_addr']}<br />";
                            echo (trim($info['secondary_city']) === '') ? "<span class='highlight'>___________ , ___ , _______</span><br />" : "{$info['secondary_city']}, {$info['secondary_state']} {$info['secondary_zip']}<br />";
                            echo (trim($info['email_1']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['email_1']}<br />";
                            echo (trim($info['cell_1']) === '') ? "<span class='highlight'>________________________</span>" : "{$info['cell_1']}<br />";
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="vin">VIN: <span><?php echo $info['vin_code']; ?></span></div>

        <div class="clearfix"></div>
    </div>

    <div id="globals">
        <div class="global-subset">
            <table>

            </table>
        </div>

        <div class="shipping-information">
            <table>

            </table>
        </div>

        <div class="notes">
            <table>
                <tr>
                    <td><strong>Notes:</strong></td>
                    <td><span style="color: #FF0000;">INTENTIONALLY LEFT BLANK, FIX ME!</span></td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>
    </div>

    <div id="line-items">
        <table>
            <tr>
                <td colspan="3" class="b-ul">Door/Drawer Head</td>
            </tr>
            <tr>
                <td width="140px">Species:</td>
                <td width="50px"><?php echo (trim($info['species_grade']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['species_grade']}<br />"; ?></td>
                <td><?php echo translateVIN('species_grade', $info['species_grade']); ?></td>
            </tr>
            <tr>
                <td>Door Design:</td>
                <td><?php echo (trim($info['door_design']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['door_design']}<br />"; ?></td>
                <td><?php echo translateVIN('door_design', $info['door_design']); ?></td>
            </tr>
            <tr>
                <td>Styles/Rails:</td>
                <td><?php echo (trim($info['style_rail_width']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['style_rail_width']}<br />"; ?></td>
                <td><?php echo translateVIN('style_rail_width', $info['style_rail_width']); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="ul">Panel Raise</td>
            </tr>
            <tr>
                <td class="sub-item">Door Panel Raise:</td>
                <td><?php echo (trim($info['panel_raise_door']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['panel_raise_door']}<br />"; ?></td>
                <td><?php echo translateVIN('panel_raise', $info['panel_raise_door']); ?></td>
            </tr>
            <tr>
                <td class="sub-item">Short Drawer Raise:</td>
                <td><?php echo (trim($info['panel_raise_sd']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['panel_raise_sd']}<br />"; ?></td>
                <td><?php echo translateVIN('panel_raise', $info['panel_raise_sd']); ?></td>
            </tr>
            <tr>
                <td class="sub-item">Tall Drawer Raise:</td>
                <td><?php echo (trim($info['panel_raise_td']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['panel_raise_td']}<br />"; ?></td>
                <td><?php echo translateVIN('panel_raise', $info['panel_raise_td']); ?></td>
            </tr>
            <tr>
                <td>Edge Profile:</td>
                <td><?php echo (trim($info['edge_profile']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['edge_profile']}<br />"; ?></td>
                <td><?php echo translateVIN('edge_profile', $info['edge_profile']); ?></td>
            </tr>
            <tr>
                <td>Framing Bead:</td>
                <td><?php echo (trim($info['framing_bead']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['framing_bead']}<br />"; ?></td>
                <td><?php echo translateVIN('framing_bead', $info['framing_bead']); ?></td>
            </tr>
            <tr>
                <td>Framing Option:</td>
                <td><?php echo (trim($info['framing_options']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['framing_options']}<br />"; ?></td>
                <td><?php echo translateVIN('framing_options', $info['framing_options']); ?></td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3" class="b-ul">Door/Drawer Finish</td>
            </tr>
            <tr>
                <td>Finish Code:</td>
                <td><?php echo (trim($info['finish_code']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['finish_code']}<br />"; ?></td>
                <td><?php echo translateVIN('finish_code', $info['finish_code']); ?></td>
            </tr>
            <tr>
                <td>Glaze</td>
                <td><?php echo (trim($info['glaze']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['glaze']}<br />"; ?></td>
                <td><?php echo translateVIN('glaze', $info['glaze']); ?></td>
            </tr>
            <tr>
                <td>Glaze Technique:</td>
                <td><?php echo (trim($info['glaze_technique']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['glaze_technique']}<br />"; ?></td>
                <td><?php echo translateVIN('glaze_technique', $info['glaze_technique']); ?></td>
            </tr>
            <tr>
                <td>Sheen:</td>
                <td><?php echo (trim($info['sheen']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['sheen']}<br />"; ?></td>
                <td><?php echo translateVIN('sheen', $info['sheen']); ?></td>
            </tr>
            <tr>
                <td>Antiquing:</td>
                <td><?php echo (trim($info['antiquing']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['antiquing']}<br />"; ?></td>
                <td><?php echo translateVIN('antiquing', $info['antiquing']); ?></td>
            </tr>
            <tr>
                <td>Distress Level:</td>
                <td><?php echo (trim($info['distress_level']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['distress_level']}<br />"; ?></td>
                <td><?php echo translateVIN('distress_level', $info['distress_level']); ?></td>
            </tr>
            <tr>
                <td>Worn Edges:</td>
                <td><?php echo (trim($info['worn_edges']) === '') ? "<span class='highlight'>__________________________________________</span><br />" : "{$info['worn_edges']}<br />"; ?></td>
                <td><?php echo translateVIN('worn_edges', $info['worn_edges']); ?></td>
            </tr>
        </table>
    </div>

    <div id="order-information">
        <table>
            <tr>
                <td class="b-ul" width="150px">Order</td>
                <td class="b-ul" width="75px">On Order</td>
                <td class="b-ul" width="75px">Received</td>
            </tr>
            <tr style="height:10px;">
                <td></td>
            </tr>

            <?php
                $display_count = 0;

                $display_count += displayOrder($info['sample_block_ordered'], "Sample Block");
                $display_count += displayOrder($info['door_only_ordered'], "Door");
                $display_count += displayOrder($info['door_drawer_ordered'], "Door & Drawer");
                $display_count += displayOrder($info['inset_square_ordered'], "Inset Square");
                $display_count += displayOrder($info['inset_beaded_ordered'], "Inset Bead");

                if($display_count === 0) {
                    echo "<tr><td><span class='highlight'>____________________</span></td><td>_________</td><td>_________</td></tr>";
                }
            ?>
        </table>
    </div>
</div>

<script>
    function printMe() {
        window.print();

        setTimeout(function() {
            window.close();
        }, 100);
    }
</script>
</body>
</html>