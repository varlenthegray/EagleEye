<?php
require '../includes/header_start.php';

$id = sanitizeInput($_REQUEST['room_id']);

$info_qry = $dbconn->query("SELECT sales_order.order_status AS orderStatus, sales_order.*, rooms.* FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = '$id'");
$info = $info_qry->fetch_assoc();

function translateVIN($segment, $key) {
    global $dbconn;

    $segment_def = ($segment === 'finish_code') ? "segment = 'standard_wiping_stains' OR segment = 'colourtone_paints' OR segment = 'benjamin_moore_paints' OR segment = 'sherwin_williams_paints'" : $segment_def = $segment;

    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$segment_def' AND `key` = '$key'");
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
    <link href="/assets/css/print.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">


</head>

<body onload="printMe()">

<div id="wrapper">
    <div id="header">
        <div id="logo"><img src="/assets/images/smc_logo.png" width="170px" /></div>

        <div class="header-mid">
            <div class="title">Sample Request</div>

            <div class="project-info">
                <?php
                    echo "{$info['contractor_dealer_code']} {$info['project']}<br />";
                    echo "{$info['contact1_name']}<br />";
                    echo "{$info['contact1_cell']}<br />";
                    echo "{$info['contact1_email']}";
                ?>
            </div>
        </div>

        <div class="clearfix"></div>
    </div>

    <div id="globals">
        <div class="company-addr">
            309 S. Country Club Road<br/>
            Brevard, NC 28712<br/>
            (828) 966-9000<br />
            orders@smcm.us
        </div>

        <div class="so-num"><?php echo "{$info['so_parent']}{$info['room']}"; ?></div>

        <div class="date">
            <strong>Date Ordered: </strong><?php echo (trim($info['sample_ordered_date']) === '') ? "<span class='highlight'>___________</span>" : date('n/j/Y', $info['sample_ordered_date']); ?><br />
            <strong>Date Printed: </strong> <?php echo date('n/j/Y'); ?>
        </div>

        <div class="global-subset">
            <table>
                <tr>
                    <td><strong>VIN:</strong></td>
                    <td><?php echo $info['vin_code']; ?></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td><strong>Dealer PO#:</strong></td>
                                <td><?php echo $info['so_parent']; ?></td>
                                <td style="padding-left:8px;"><strong>Room/Area:</strong></td>
                                <td><?php echo $info['room']; ?></td>
                                <td style="padding-left:8px;"><strong>Series:</strong></td>
                                <td><?php $series = explode('.', $info['iteration']); echo $series[0]; ?></td>
                                <td style="padding-left:8px;"><strong>Iteration:</strong></td>
                                <td><?php $series = explode('.', $info['iteration']); echo $series[1]; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td><strong>Order Type:</strong></td>
                    <td><?php echo ($info['orderStatus'] === '$') ? "Job" : "Quote"; ?></td>
                </tr>
                <tr>
                    <td><strong>Product Type:</strong></td>
                    <td>
                        <?php
                            $ptype_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'product_type' AND `key` = '{$info['product_type']}'");
                            $ptype = $ptype_qry->fetch_assoc();

                            echo $ptype['value'];
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Lead Time:</strong></td>
                    <td>
                        <?php
                            $ltime_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'days_to_ship' AND `key` = '{$info['days_to_ship']}'");
                            $ltime = $ptype_qry->fetch_assoc();

                            echo (trim($ltime['value']) === '') ? "<span class='highlight'>________________</span><br />" : "{$ltime['value']}<br />";
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="shipping-information">
            <table>
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
                        echo (trim($info['contact1_name']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['contact1_name']}<br />";
                        echo (trim($info['mailing_addr']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['mailing_addr']}<br />";
                        echo (trim($info['mailing_city']) === '') ? "<span class='highlight'>___________ , ___ , _______</span><br />" : "{$info['mailing_city']}, {$info['mailing_state']} {$info['mailing_zip']}<br />";
                        echo (trim($info['contact1_email']) === '') ? "<span class='highlight'>________________________</span><br />" : "{$info['contact1_email']}<br />";
                        echo (trim($info['contact1_cell']) === '') ? "<span class='highlight'>________________________</span>" : "{$info['contact1_cell']}<br />";
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="notes">
            <table>
                <tr>
                    <td><strong>Notes:</strong></td>
                    <td><?php echo (trim($info['vin_notes']) === '') ? "_______________________________________________________________________________________________<br />" : "{$info['vin_notes']}"; ?></td>
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
                <td width="50px"><?php echo (trim($info['species_grade']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['species_grade']}<br />"; ?></td>
                <td><?php echo translateVIN('species_grade', $info['species_grade']); ?></td>
            </tr>
            <tr>
                <td>Door Design:</td>
                <td><?php echo (trim($info['door_design']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['door_design']}<br />"; ?></td>
                <td><?php echo translateVIN('door_design', $info['door_design']); ?></td>
            </tr>
            <tr>
                <td>Styles/Rails:</td>
                <td><?php echo (trim($info['style_rail_width']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['style_rail_width']}<br />"; ?></td>
                <td><?php echo translateVIN('style_rail_width', $info['style_rail_width']); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="ul">Panel Raise</td>
            </tr>
            <tr>
                <td class="sub-item">Door Panel Raise:</td>
                <td><?php echo (trim($info['panel_raise_door']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['panel_raise_door']}<br />"; ?></td>
                <td><?php echo translateVIN('panel_raise', $info['panel_raise_door']); ?></td>
            </tr>
            <tr>
                <td class="sub-item">Short Drawer Raise:</td>
                <td><?php echo (trim($info['panel_raise_sd']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['panel_raise_sd']}<br />"; ?></td>
                <td><?php echo translateVIN('panel_raise', $info['panel_raise_sd']); ?></td>
            </tr>
            <tr>
                <td class="sub-item">Tall Drawer Raise:</td>
                <td><?php echo (trim($info['panel_raise_td']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['panel_raise_td']}<br />"; ?></td>
                <td><?php echo translateVIN('panel_raise', $info['panel_raise_td']); ?></td>
            </tr>
            <tr>
                <td>Edge Profile:</td>
                <td><?php echo (trim($info['edge_profile']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['edge_profile']}<br />"; ?></td>
                <td><?php echo translateVIN('edge_profile', $info['edge_profile']); ?></td>
            </tr>
            <tr>
                <td>Framing Bead:</td>
                <td><?php echo (trim($info['framing_bead']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['framing_bead']}<br />"; ?></td>
                <td><?php echo translateVIN('framing_bead', $info['framing_bead']); ?></td>
            </tr>
            <tr>
                <td>Framing Option:</td>
                <td><?php echo (trim($info['framing_options']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['framing_options']}<br />"; ?></td>
                <td><?php echo translateVIN('framing_options', $info['framing_options']); ?></td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3" class="b-ul">Door/Drawer Finish</td>
            </tr>
            <tr>
                <td>Finish Type:</td>
                <td><?php echo (trim($info['finish_type']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['finish_type']}<br />"; ?></td>
                <td><?php echo translateVIN('finish_type', $info['finish_type']); ?></td>
            </tr>
            <tr>
                <td>Finish Code:</td>
                <td><?php echo (trim($info['finish_code']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['finish_code']}<br />"; ?></td>
                <td><?php echo translateVIN('standard_wiping_stains', $info['finish_code']); ?></td>
            </tr>
            <tr>
                <td>Glaze</td>
                <td><?php echo (trim($info['glaze']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['glaze']}<br />"; ?></td>
                <td><?php echo translateVIN('glaze', $info['glaze']); ?></td>
            </tr>
            <tr>
                <td>Glaze Technique:</td>
                <td><?php echo (trim($info['glaze_technique']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['glaze_technique']}<br />"; ?></td>
                <td><?php echo translateVIN('glaze_technique', $info['glaze_technique']); ?></td>
            </tr>
            <tr>
                <td>Sheen:</td>
                <td><?php echo (trim($info['sheen']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['sheen']}<br />"; ?></td>
                <td><?php echo translateVIN('sheen', $info['sheen']); ?></td>
            </tr>
            <tr>
                <td>Antiquing:</td>
                <td><?php echo (trim($info['antiquing']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['antiquing']}<br />"; ?></td>
                <td><?php echo translateVIN('antiquing', $info['antiquing']); ?></td>
            </tr>
            <tr>
                <td>Distress Level:</td>
                <td><?php echo (trim($info['distress_level']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['distress_level']}<br />"; ?></td>
                <td><?php echo translateVIN('distress_level', $info['distress_level']); ?></td>
            </tr>
            <tr>
                <td>Worn Edges:</td>
                <td><?php echo (trim($info['worn_edges']) === '') ? "<span class='highlight'>___</span><br />" : "{$info['worn_edges']}<br />"; ?></td>
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