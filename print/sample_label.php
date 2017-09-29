<?php
require '../includes/header_start.php';
require '../assets/php/phpqrcode/qrlib.php';

//set it to writable location, a place for temp generated PNG files
$PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

//html PNG location prefix
$PNG_WEB_DIR = 'temp/';

// create the directory if it doesn't exist
if (!file_exists($PNG_TEMP_DIR)) mkdir($PNG_TEMP_DIR);

// the temp QR code
$filename = $PNG_TEMP_DIR . 'tempQR.png';

$room_id = sanitizeInput($_REQUEST['room_id']);

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = '$room_id'");
$room = $room_qry->fetch_assoc();

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '{$room['so_parent']}'");
$so = $so_qry->fetch_assoc();

function translateVIN($segment, $key) {
    global $dbconn;

    if($segment === 'finish_code') {
        $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE (segment = 'standard_wiping_stains' OR segment = 'colourtone_paints' OR segment = 'benjamin_moore_paints' OR segment = 'sherwin_williams_paints') AND `key` = '$key'");
    } else {
        $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$segment' AND `key` = '$key'");
    }

    $vin = $vin_qry->fetch_assoc();

    return "{$key} = {$vin['value']}";
}
?>

<html>
<head>
    <link href="css/sample_label.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>

<!--<body onload="printMe()">-->
<body>

<div class="wrapper">
    <div class="vin">
        <span><?php echo "{$room['room_name']}<br />{$so['so_num']}{$room['room']}-{$room['iteration']}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}_{$so['contractor_dealer_code']}"; ?></span><br />
        <?php echo "{$room['species_grade']}{$room['construction_method']}{$room['door_design']}-{$room['panel_raise_door']}{$room['panel_raise_sd']}{$room['panel_raise_td']}
            -{$room['edge_profile']}{$room['framing_bead']}{$room['framing_options']}{$room['style_rail_width']}_{$room['finish_type']}{$room['finish_code']}{$room['sheen']}-
                {$room['glaze']}-{$room['glaze_technique']}{$room['antiquing']}{$room['worn_edges']}{$room['distress_level']}"; ?>
    </div>

    <div class="details_container">
        <div class="details_left">
            Species/Grade:<br />
            Construction:<br />
            Design:<br />
            Panel Raise:<br />
            <span>Short Drawer:<br /></span>
            <span>Tall Drawer:<br /></span>
            Edge Profile:<br />
            Framing Bead:<br />
            Frame Option:<br />
            Styles/Rails:<br />
            Finish Type:<br />
            Finish Code:<br />
            Sheen:<br />
            Glaze Color:<br />
            Glaze Technique:<br />
            Antiquing:<br />
            Worn Edges:<br />
            Distressing:
        </div>

        <div class="details_right">
            <?php echo translateVIN("species_grade", $room['species_grade']); ?><br />
            <?php echo translateVIN("construction_method", $room['construction_method']); ?><br />
            <?php echo translateVIN("door_design", $room['door_design']); ?><br />
            <?php echo translateVIN("panel_raise", $room['panel_raise_door']); ?><br />
            <?php echo translateVIN("panel_raise", $room['panel_raise_sd']); ?><br />
            <?php echo translateVIN("panel_raise", $room['panel_raise_td']); ?><br />
            <?php echo translateVIN("edge_profile", $room['edge_profile']); ?><br />
            <?php echo translateVIN("framing_bead", $room['framing_bead']); ?><br />
            <?php echo translateVIN("framing_options", $room['framing_options']); ?><br />
            <?php echo translateVIN("style_rail_width", $room['style_rail_width']); ?><br />
            <?php echo translateVIN("finish_type", $room['finish_type']); ?><br />
            <?php echo translateVIN("finish_code", $room['finish_code']); ?><br />
            <?php echo translateVIN("sheen", $room['sheen']); ?><br />
            <?php echo translateVIN("glaze", $room['glaze']); ?><br />
            <?php echo translateVIN("glaze_technique", $room['glaze_technique']); ?><br />
            <?php echo translateVIN("antiquing", $room['antiquing']); ?><br />
            <?php echo translateVIN("worn_edges", $room['worn_edges']); ?><br />
            <?php echo translateVIN("distress_level", $room['distress_level']); ?>
        </div>
    </div>

    <div class="qr-code">
        <?php
            QRcode::png('Demo Text', $filename, QR_ECLEVEL_H, 2, 0);
            echo '<img src="' . $PNG_WEB_DIR . basename($filename) . '" />';
        ?>
    </div>
</div>

<div class="wrapper">
    <div class="smcm_text">
        <span class="ul_default">SMCM, <span class="small">inc</span></span>
    </div>

    <div class="vin">
        <span><?php echo "{$room['room_name']}<br />{$so['so_num']}{$room['room']}-{$room['iteration']}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}_{$so['contractor_dealer_code']}"; ?></span><br />
        <?php echo "{$room['species_grade']}{$room['construction_method']}{$room['door_design']}-{$room['panel_raise_door']}{$room['panel_raise_sd']}{$room['panel_raise_td']}
            -{$room['edge_profile']}{$room['framing_bead']}{$room['framing_options']}{$room['style_rail_width']}_{$room['finish_type']}{$room['finish_code']}{$room['sheen']}-
                {$room['glaze']}-{$room['glaze_technique']}{$room['antiquing']}{$room['worn_edges']}{$room['distress_level']}"; ?>
    </div>

    <div class="details_container">
        <div class="details_left">
            Species/Grade:<br />
            Construction:<br />
            Design:<br />
            Panel Raise:<br />
            <span>Short Drawer:<br /></span>
            <span>Tall Drawer:<br /></span>
            Edge Profile:<br />
            Framing Bead:<br />
            Frame Option:<br />
            Styles/Rails:<br />
            Finish Type:<br />
            Finish Code:<br />
            Sheen:<br />
            Glaze Color:<br />
            Glaze Technique:<br />
            Antiquing:<br />
            Worn Edges:<br />
            Distressing:
        </div>

        <div class="details_right">
            <?php echo translateVIN("species_grade", $room['species_grade']); ?><br />
            <?php echo translateVIN("construction_method", $room['construction_method']); ?><br />
            <?php echo translateVIN("door_design", $room['door_design']); ?><br />
            <?php echo translateVIN("panel_raise", $room['panel_raise_door']); ?><br />
            <?php echo translateVIN("panel_raise", $room['panel_raise_sd']); ?><br />
            <?php echo translateVIN("panel_raise", $room['panel_raise_td']); ?><br />
            <?php echo translateVIN("edge_profile", $room['edge_profile']); ?><br />
            <?php echo translateVIN("framing_bead", $room['framing_bead']); ?><br />
            <?php echo translateVIN("framing_options", $room['framing_options']); ?><br />
            <?php echo translateVIN("style_rail_width", $room['style_rail_width']); ?><br />
            <?php echo translateVIN("finish_type", $room['finish_type']); ?><br />
            <?php echo translateVIN("finish_code", $room['finish_code']); ?><br />
            <?php echo translateVIN("sheen", $room['sheen']); ?><br />
            <?php echo translateVIN("glaze", $room['glaze']); ?><br />
            <?php echo translateVIN("glaze_technique", $room['glaze_technique']); ?><br />
            <?php echo translateVIN("antiquing", $room['antiquing']); ?><br />
            <?php echo translateVIN("worn_edges", $room['worn_edges']); ?><br />
            <?php echo translateVIN("distress_level", $room['distress_level']); ?>
        </div>
    </div>

    <div class="qr-code">
        <?php
        QRcode::png('http://www.stonemountaincabinetry.com/', $filename, QR_ECLEVEL_H, 2, 0);
        echo '<img src="' . $PNG_WEB_DIR . basename($filename) . '" />';
        ?>
    </div>

    <div class="exp_date">Expires<br /> Jan 2019</div>
</div>

</body>
</html>