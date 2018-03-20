<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 11/1/2017
 * Time: 12:47 PM
 */

namespace Search;


class search {
    function displayResults($find) {
        global $dbconn;

        $qry = $dbconn->query("SELECT * FROM sales_order WHERE LOWER(so_num) LIKE LOWER('%$find%') OR LOWER(dealer_code) LIKE LOWER('%$find%') 
          OR LOWER(project_name) LIKE LOWER('%$find%') OR LOWER(project_mgr) LIKE LOWER('%$find%') OR LOWER(name_1) LIKE LOWER('%$find%') OR LOWER(name_2) LIKE LOWER('%$find%')  
              ORDER BY so_num DESC");

        if($qry->num_rows > 0) {
            while($result = $qry->fetch_assoc()) {
                $dealer_qry = $dbconn->query("SELECT * FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id LIKE '%{$result['dealer_code']}%'");
                $dealer = $dealer_qry->fetch_assoc();

                /** BEGIN LISTING OF SO'S */
                echo "  <tr class='cursor-hand' id='show_room_{$result['so_num']}'>";
                echo "    <td width='26px'><button class='btn waves-effect btn-primary pull-right' id='edit_so_{$result['so_num']}'> <i class='zmdi zmdi-edit'></i> </button></td>";
                echo "    <td>{$result['so_num']}</td>";
                echo "    <td>{$result['project_name']}</td>";
                echo "    <td>{$dealer['first_name']} {$dealer['last_name']}</td                                                                                                                                                                                                                                                                                             nnnnnnnnn
    >";
                echo "    <td>{$result['dealer_code']}: {$dealer['company_name']}</td>";
                echo "  </tr>";

                /** BEGIN ROOM INFORMATION */
                echo "  <tr id='tr_room_{$result['so_num']}'>";
                echo "    <td colspan='8'><div id='div_room_{$result['so_num']}'>";?>

                <div class="col-md-12">
                    <div class="row">
                        <table class="table pull-right" style="width:99%">
                            <thead>
                            <tr>
                                <th colspan="2">ROOM</th>
                                <th>SALES</th>
                                <th>SAMPLE</th>
                                <th>PRE-PRODUCTION</th>
                                <th>DOOR/DRAWER</th>
                                <th>MAIN</th>
                                <th>CUSTOM</th>
                                <th>SHIPPING</th>
                                <th>INSTALLATION</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['so_num']}' ORDER BY room, iteration ASC");

                            if($room_qry->num_rows > 0) {
                                while($room = $room_qry->fetch_assoc()) {
                                    $room_name = "{$room['room']}{$room['iteration']}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}: {$room['room_name']}";

                                    $salesBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['sales_bracket']}")->fetch_assoc();
                                    $salesBracketName = $salesBracket['op_id'] . "-" . $salesBracket['job_title'];

                                    $preprodBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['preproduction_bracket']}")->fetch_assoc();
                                    $preprodBracketName = $preprodBracket['op_id'] . "-" . $preprodBracket['job_title'];

                                    $sampleBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['sample_bracket']}")->fetch_assoc();
                                    $sampleBracketName = $sampleBracket['op_id'] . "-" . $sampleBracket['job_title'];

                                    $doorBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['doordrawer_bracket']}")->fetch_assoc();
                                    $doorBrackettName = $doorBracket['op_id'] . "-" . $doorBracket['job_title'];

                                    $customsBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['custom_bracket']}")->fetch_assoc();
                                    $customsBracketName = $customsBracket['op_id'] . "-" . $customsBracket['job_title'];

                                    $mainBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['main_bracket']}")->fetch_assoc();
                                    $mainBracketName = $mainBracket['op_id'] . "-" . $mainBracket['job_title'];

                                    $shippingBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['shipping_bracket']}")->fetch_assoc();
                                    $shippingBracketName = $shippingBracket['op_id'] . "-" . $shippingBracket['job_title'];

                                    $installBracket = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = {$room['install_bracket']}")->fetch_assoc();
                                    $installBracketName = $installBracket['op_id'] . "-" . $installBracket['job_title'];

                                    $tab = ($room['iteration'] > 1.01) ? "<div class='pull-left' style='width:15px;'>&nbsp</div>" : null;

                                    $sales_published_display = (checkPublished('sales')) ? "<td><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $salesBracketName</td>" : "<td>---</td>";
                                    $sample_published_display = (checkPublished('sample')) ? "<td><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $sampleBracketName</td>" : "<td>---</td>";
                                    $preprod_published_display = (checkPublished('preproduction')) ? "<td><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $preprodBracketName</td>" : "<td>---</td>";
                                    $door_published_display = (checkPublished('doordrawer')) ? "<td><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $doorBrackettName</td>" : "<td>---</td>";
                                    $main_published_display = (checkPublished('main')) ? "<td><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $mainBracketName</td>" : "<td>---</td>";
                                    $customs_published_display = (checkPublished('custom')) ? "<td><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $customsBracketName</td>" : "<td>---</td>";
                                    $shipping_published_display = (checkPublished('shipping')) ? "<td><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $shippingBracketName</td>" : "<td>---</td>";
                                    $install_published_display = (checkPublished('install_bracket')) ? "<td><button class='btn waves-effect btn-primary' id='manage_bracket_{$room['id']}'><i class='zmdi zmdi-filter-center-focus'></i></button> $installBracketName</td>" : "<td>---</td>";

                                    $target_dir = SITE_ROOT . "/attachments/";
                                    $attachment_dir = "{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}";

                                    if(file_exists($attachment_dir)) {
                                        $attachment_code = "btn-primary";
                                    } else {
                                        $attachment_code = "btn_secondary disabled";
                                    }

                                    echo "<tr class='cursor-hand' id='manage_bracket_{$room['id']}'>";
                                    echo "  <td class='nowrap' style='width:106px;'><button class='btn waves-effect btn-primary' id='show_single_room_{$room['id']}'><i class='zmdi zmdi-edit'></i></button> <button class='btn waves-effect btn-primary' id='show_vin_room_{$room['id']}'><i class='zmdi zmdi-developer-board'></i></button> <button class='btn waves-effect $attachment_code' id='show_attachments_room_{$room['id']}'><i class='zmdi zmdi-attachment-alt'></i></button> <button class='btn waves-effect btn-primary' id='print_{$room['id']}'><i class='fa fa-print'></i></button></td>";
                                    echo "  <td>{$tab}{$room_name}</td>";
                                    echo "  $sales_published_display";
                                    echo "  $sample_published_display";
                                    echo "  $preprod_published_display";
                                    echo "  $door_published_display";
                                    echo "  $main_published_display";
                                    echo "  $customs_published_display";
                                    echo "  $shipping_published_display";
                                    echo "  $install_published_display";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php echo "    </td>";
                echo "  </tr>";
                /** END ROOM INFORMATION */
                /** END LISTING OF SO'S */
            }
        }
    }

    function displayEditRoom() {

    }
}