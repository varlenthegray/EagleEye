<?php
require_once("../includes/header_start.php");

if(!(bool)$_SESSION['userInfo']['dealer']) {
    $qry = $dbconn->query("SELECT DISTINCT so_num FROM sales_order WHERE so_num REGEXP '^[0-9]+$' ORDER BY so_num DESC LIMIT 0,1");

    if($qry->num_rows > 0) {
        $result = $qry->fetch_assoc();

        $next_so = $result['so_num'] + 1;
    } else {
        $next_so = 1;
    }
} else {
    $qry = $dbconn->query('SELECT id FROM sales_order ORDER BY id DESC LIMIT 0, 1;');
    $result = $qry->fetch_assoc();

    $next_so = 'D' . ($result['id'] + 1);
}
?>

<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title" id="modalAddCustomerTitle">Add New Project</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <form id="add_project">
                        <table style="width:100%;margin-top:8px;">
                            <?php
                            echo "<input type='hidden' name='dealer_code' id='dealer_code' value='{$_SESSION['userInfo']['dealer_code']}'>";
                            echo "<input type='hidden' name='so_num' id='so_num' value='$next_so'>";
                            ?>

                            <tr>
                                <td colspan="3">
                                    <input type="text" name="project_name" class="form-control pull-left" placeholder="Project Name" id="project_name" style="width:50%;" />
                                    <input type="text" name="project_addr" class="form-control pull-left" placeholder="Job Site Address" id="project_addr" style="width:50%;">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 33.3%;"><input type="text" name="project_city" class="form-control" placeholder="Job Site City" id="project_city"></td>
                                            <td style="width: 33.3%;"><select class="form-control" id="project_state" name="project_state">
                                                    <option value="AL">Alabama</option>
                                                    <option value="AK">Alaska</option>
                                                    <option value="AZ">Arizona</option>
                                                    <option value="AR">Arkansas</option>
                                                    <option value="CA">California</option>
                                                    <option value="CO">Colorado</option>
                                                    <option value="CT">Connecticut</option>
                                                    <option value="DE">Delaware</option>
                                                    <option value="FL">Florida</option>
                                                    <option value="GA">Georgia</option>
                                                    <option value="HI">Hawaii</option>
                                                    <option value="ID">Idaho</option>
                                                    <option value="IL">Illinois</option>
                                                    <option value="IN">Indiana</option>
                                                    <option value="IA">Iowa</option>
                                                    <option value="KS">Kansas</option>
                                                    <option value="KY">Kentucky</option>
                                                    <option value="LA">Louisiana</option>
                                                    <option value="ME">Maine</option>
                                                    <option value="MD">Maryland</option>
                                                    <option value="MA">Massachusetts</option>
                                                    <option value="MI">Michigan</option>
                                                    <option value="MN">Minnesota</option>
                                                    <option value="MS">Mississippi</option>
                                                    <option value="MO">Missouri</option>
                                                    <option value="MT">Montana</option>
                                                    <option value="NE">Nebraska</option>
                                                    <option value="NV">Nevada</option>
                                                    <option value="NH">New Hampshire</option>
                                                    <option value="NJ">New Jersey</option>
                                                    <option value="NM">New Mexico</option>
                                                    <option value="NY">New York</option>
                                                    <option value="NC" selected>North Carolina</option>
                                                    <option value="ND">North Dakota</option>
                                                    <option value="OH">Ohio</option>
                                                    <option value="OK">Oklahoma</option>
                                                    <option value="OR">Oregon</option>
                                                    <option value="PA">Pennsylvania</option>
                                                    <option value="RI">Rhode Island</option>
                                                    <option value="SC">South Carolina</option>
                                                    <option value="SD">South Dakota</option>
                                                    <option value="TN">Tennessee</option>
                                                    <option value="TX">Texas</option>
                                                    <option value="UT">Utah</option>
                                                    <option value="VT">Vermont</option>
                                                    <option value="VA">Virginia</option>
                                                    <option value="WA">Washington</option>
                                                    <option value="WV">West Virginia</option>
                                                    <option value="WI">Wisconsin</option>
                                                    <option value="WY">Wyoming</option>
                                                </select></td>
                                            <td style="width: 33.3%;"><input type="text" name="project_zip" class="form-control" placeholder="Job Site Zip" id="project_zip"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td><input type="text" name="project_landline" class="form-control" placeholder="Job Site Landline" id="project_landline"></td>
                            </tr>
                            <tr>
                                <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="name_1" class="form-control" placeholder="Name 1" id="name_1"></td>
                                <td><input type="text" name="cell_1" class="form-control" placeholder="Cell Phone" id="cell_1"></td>
                                <td><input type="text" name="business_1" class="form-control" placeholder="Secondary Phone" id="business_1"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="email_1" class="form-control" placeholder="Email Address" id="email_1"></td>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="name_2" class="form-control" placeholder="Name 2" id="name_2"></td>
                                <td><input type="text" name="cell_2" class="form-control" placeholder="Cell Phone" id="cell_2"></td>
                                <td><input type="text" name="business_2" class="form-control" placeholder="Secondary Phone" id="business_2"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="email_2" class="form-control" placeholder="Email Address" id="email_2"></td>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
                            </tr>
                            <tr>
                                <td colspan="3"><h5>Designer</h5></td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <select name="designer" id="designer">
                                        <?php
                                        $designer_qry = $dbconn->query("SELECT c.* FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id WHERE c2.description = 'Designer'");

                                        if($designer_qry->num_rows > 0) {
                                            while($designer = $designer_qry->fetch_assoc()) {
                                                echo "<option value='{$designer['id']}'>{$designer['first_name']} {$designer['last_name']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                    <a href="#" class="nav_add_contact" data-default="Designer" style="margin-left:10px;">Add New Designer</a>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary waves-effect waves-light" id="submit_new_project">Add Project</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->