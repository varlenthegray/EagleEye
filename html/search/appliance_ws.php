<?php
require_once("../../includes/header_start.php");

$room_id = sanitizeInput($_REQUEST['room_id']);

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");
$room = $room_qry->fetch_assoc();
?>

<div class="col-md-1 sticky">
    <h5 class="text-md-center"><?php echo "Appliance Worksheets {$room['room']}{$room['iteration']}" ?></h5>
    <a class="btn btn-primary btn-block waves-effect waves-light appliance_ws_save">Save</a>
    <a class="btn btn-primary-outline btn-block waves-effect waves-light w-xs print_app_ws" id="">Print</a>

    <div class="m-b-5">&nbsp;</div>

    <table width="100%">
        <tr><th class="text-md-center">Saved Sheets</th></tr>
        <?php
        $worksheet_qry = $dbconn->query("SELECT w.*, s.*, v.value AS construction_method, w.id AS worksheetID FROM appliance_worksheets w LEFT JOIN appliance_specs s ON w.spec = s.id LEFT JOIN vin_schema v ON v.`key` = s.const_method WHERE w.room = $room_id AND v.segment = 'construction_method'");

        if($worksheet_qry->num_rows > 0) {
            while($worksheet = $worksheet_qry->fetch_assoc()) {
                echo "<tr><td><a class='load_app_worksheet' id='{$worksheet['worksheetID']}' href='#'>{$worksheet['name']} ({$worksheet['construction_method']})</a></td></tr>";
            }
        } else {
            echo "<tr><td><strong>No worksheets saved.</strong></td></tr>";
        }
        ?>
    </table>
</div>

<div class="col-md-11 sheet_data"></div>

<script>
    $.post("/html/search/appliance_ws_info.php?room_id=" + active_room_id + "&id=1", function(data) {
        $(".sheet_data").html(data);
    });
</script>