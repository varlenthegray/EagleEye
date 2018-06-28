<?php
require '../includes/header_start.php';

$ws_id = sanitizeInput($_REQUEST['ws_id']);

$ws_qry = $dbconn->query("SELECT * FROM appliance_worksheets w LEFT JOIN appliance_specs a ON w.spec = a.id WHERE w.id = $ws_id");
$ws = $ws_qry->fetch_assoc();

$room_id = $ws['room'];

$info_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = '$room_id'");
$info = $info_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$info['dealer_code']}'");
$dealer_info = $dealer_qry->fetch_assoc();

$const_method = translateVIN("construction_method", $ws['const_method']);

$note_arr = array();

$notes_qry = $dbconn->query("SELECT * FROM notes WHERE (note_type = 'room_note_delivery' OR note_type = 'room_note_global' OR note_type = 'room_note_fin_sample') AND type_id = '$room_id'");

if($notes_qry->num_rows > 0) {
    while($notes = $notes_qry->fetch_assoc()) {
        $note_arr[$notes['note_type']] = $notes['note'];
    }
}
?>
<!DOCTYPE html>

<html moznomarginboxes="" mozdisallowselectionprint="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A fully functional ERP designed to manage cabinetry and automation.">
    <meta name="author" content="Stone Mountain Cabinetry & Millwork">

    <link href="css/e_coversheet.css?v=111320171016" type="text/css" rel="stylesheet">
    <link href="css/appliance_spec.css" type="text/css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>

<body onload="window.print();">

<div id="wrapper">
    <form name="e_coversheet_total" id="e_coversheet_total">
        <div id="header_container">
            <div id="header_left">
                <div id="page_type">
                    <table>
                        <tr>
                            <td colspan="2" id="page_type_header" style="font-size: 2.5em;">Appliance Worksheet</td>
                        </tr>
                        <tr>
                            <td class="definition">Dealer PO#:</td>
                            <td class="value"><?php echo "{$info['project_name']} - {$info['room_name']}"; ?></td>
                        </tr>
                        <tr>
                            <td class="definition">Room:</td>
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
                        <tr>
                            <td class="definition">Sales Order #:</td>
                            <td class="value"><?php echo "{$info['so_parent']}{$info['room']}-{$info['iteration']}"; ?></td>
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

            <div id="header_right">
                <div id="page_info">
                    <table>
                        <tr>
                            <td width="80px"># of Pages:</td>
                            <td>1</td>
                        </tr>
                        <tr>
                            <td>Printed:</td>
                            <td><?php echo date("m/d/Y"); ?></td>
                        </tr>
                        <tr>
                            <td>Product Type:</td>
                            <td><?php echo translateVIN('product_type', $info['product_type']); ?></td>
                        </tr>
                        <tr>
                            <td>Lead time:</td>
                            <td><?php echo translateVIN('days_to_ship', $info['days_to_ship']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Ship VIA:</strong></td>
                            <td><input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_via" value="<?php echo $info['vin_ship_via']; ?>"></td>
                        </tr>
                        <tr>
                            <td><strong>Ship To:</strong></td>
                            <td>
                                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_1" value="<?php echo $info['name_1']; ?>"><br />
                                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_2" value="<?php echo $info['project_addr']; ?>"><br />
                                <input type="text" style="width:76px;" class="static_width align_left border_thin_bottom" name="ship_to_city" value="<?php echo $info['project_city']; ?>"> <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php echo $info['project_state']; ?>"> <input type="text" style="width:30px;" class="static_width align_left border_thin_bottom" name="ship_to_zip" value="<?php echo $info['project_zip']; ?>">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div id="main_section">
            <table style="margin-bottom: 20px;">
                <tr>
                    <th colspan="2"><span class="pull-left"><?php echo $info['vin_code']; ?></span><span class="pull-right"><?php echo "{$info['dealer_code']} - {$dealer_info['company_name']}"; ?></span></th>
                </tr>
                <tr class="border_thin_bottom" id="delivery_notes">
                    <td class="gray_bg" width="13%">Delivery Notes:</td>
                    <td><textarea name="delivery_notes" maxlength="280" style="width:100%;text-align:left;" class="static_width" rows="2"><?php echo $note_arr['room_note_delivery']; ?></textarea></td>
                </tr>
                <tr class="border_thin_bottom" width="13%" id="global_notes">
                    <td class="gray_bg">Global Notes:</td>
                    <td><textarea name="global_notes" maxlength="280" style="width:100%;text-align:left;" class="static_width" rows="2"><?php echo $note_arr['room_note_global']; ?></textarea></td>
                </tr>
                <tr id="layout_notes">
                    <td class="gray_bg" width="13%" id="layout_notes_title">Finishing/Sample Notes:</td>
                    <td><textarea name="layout_notes" maxlength="280" style="width:100%;text-align:left;" class="static_width" rows="2"><?php echo $note_arr['room_note_fin_sample']; ?></textarea></td>
                </tr>
                <tr><th colspan="2">&nbsp;</th></tr>
            </table>

            <div class="worksheet_container">
                <div class="ws_header"><?php echo "{$ws['name']} ($const_method)"; ?></div>

                <div class="image"><img src="/assets/images/appliance_specs/<?php echo $ws['image']; ?>.jpg" width="300px" /></div>

                <div class="spec_text"><?php echo $ws['spec_text']; ?></div>

                <div class="values">
                    <?php
                    $values = json_decode($ws['values'], true);

                    foreach($values AS $key => $dim) {
                        echo "$key = $dim<br />";
                    }
                    ?>
                </div>

                <div class="notes">Notes:<br /><br /><?php echo $ws['notes']; ?></div>
            </div>
        </div>
    </form>
</div>

<script src="/assets/js/jquery.min.js"></script>

</body>
</html>