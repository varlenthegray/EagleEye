<?php
require_once ("../../includes/header_start.php");

switch($_REQUEST['action']) {
    case 'single_result':
        function generateSingleOps($bracket) {
            global $dbconn;
            $output = '';

            $qry = $dbconn->query("SELECT * FROM operations WHERE department = '$bracket' AND always_visible = FALSE ORDER BY op_id ASC");

            if ($qry->num_rows > 0) {
                while ($result = $qry->fetch_assoc()) {
                    $output .= "<option value='{$result['id']}'>{$result['op_id']}-{$result['job_title']}</option>";
                }
            }

            return $output;
        }

        $sales_info = generateSingleOps("Sales");
        $preprod_info = generateSingleOps("Pre-Production");
        $sample_info = generateSingleOps("Sample");
        $doordrawer_info = generateSingleOps("Drawer & Doors");
        $custom_info = generateSingleOps("Custom");
        $box_info = generateSingleOps("Box");


        echo <<<HEREDOC
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <fieldset class="form-group">
                                <label for="sales_bracket">Sales Bracket</label>
                                <select id="sales_bracket" name="sales_bracket" class="form-control">
                                    $sales_info
                                </select>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset class="form-group">
                                <label for="pre_prod_bracket">Pre-production Bracket</label>
                                <select id="pre_prod_bracket" name="pre_prod_bracket" class="form-control">
                                    $preprod_info
                                </select>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset class="form-group">
                                <label for="sample_bracket">Sample Bracket</label>
                                <select id="sample_bracket" name="sample_bracket" class="form-control">
                                    $sample_info
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
                                    $doordrawer_info
                                </select>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset class="form-group">
                                <label for="custom_bracket">Custom Bracket</label>
                                <select id="custom_bracket" name="custom_bracket" class="form-control">
                                    $custom_info
                                </select>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset class="form-group">
                                <label for="box_bracket">Box Bracket</label>
                                <select id="box_bracket" name="box_bracket" class="form-control">
                                    $box_info
                                </select>
                            </fieldset>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
HEREDOC;

        break;
}