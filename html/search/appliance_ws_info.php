<?php
require_once("../../includes/header_start.php");

$id = sanitizeInput($_REQUEST['id']);
$roomid = sanitizeInput($_REQUEST['room_id']);

$spec_qry = $dbconn->query("SELECT * FROM appliance_specs WHERE id = $id");
$cur_spec = $spec_qry->fetch_assoc();
?>

<form id="appliance_info">
    <div class="row">
        <div class="col-md-3">
            <select name="sheet_type" id="sheet_type" class="form-control" style="font-weight:bold;font-size:1.5em;margin-bottom:5px;">
                <?php
                $prev_const_method = null;

                $specs_qry = $dbconn->query("SELECT * FROM appliance_specs ORDER BY const_method, name DESC");

                while($specs = $specs_qry->fetch_assoc()) {
                    $selected = ($id === $specs['id']) ? "selected" : null;

                    $vin_translation_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'construction_method' AND `key` = '{$specs['const_method']}'");
                    $vin_translation = $vin_translation_qry->fetch_assoc();

                    if($prev_const_method !== $specs['const_method']) {
                        echo "</optgroup><optgroup label='{$vin_translation['value']}'>";

                        $prev_const_method = $specs['const_method'];
                    }

                    echo "<option value='{$specs['id']}' $selected>{$specs['name']}</option>";
                }

                echo "</optgroup>";
                ?>
            </select>

            <?php echo "<img src='../../assets/images/appliance_specs/{$cur_spec['image']}.jpg' width='400px'>"; ?>
        </div>

        <div class="col-md-3">
            <div class="row" style="margin-bottom:5px;">
                <div class="col-md-12">
                    <h5><?php echo $cur_spec['spec_text']; ?></h5>
                </div>
            </div>

            <div class="row" style="margin-bottom:5px;">
                <div class="col-md-12">
                    <textarea name="notes" id="notes" placeholder="Notes" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="table-custom-nb">
                        <?php
                        $fields = json_decode($cur_spec['fields']);

                        foreach($fields AS $specification) {
                            echo "<tr>";
                            echo "  <td style='width:15px;'><label for='$specification' class='font-weight-bold'>$specification:</label></td>";
                            echo "  <td><input type='text' placeholder='$specification Value' class='form-control-sm' name='spec[$specification]' id='$specification' /></td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>