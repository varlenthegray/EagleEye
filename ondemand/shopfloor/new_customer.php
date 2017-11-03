<?php
require ("../../includes/header_start.php");

$qry = $dbconn->query("SELECT DISTINCT so_num FROM sales_order ORDER BY so_num DESC LIMIT 0,1");

if($qry->num_rows > 0) {
    $result = $qry->fetch_assoc();

    $next_so = $result['so_num'] + 1;
} else {
    $next_so = 1;
}

?>

<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title" id="modalAddCustomerTitle">Add New Customer</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="so_num" name="so_num" placeholder="SO #" value="<?php echo $next_so; ?>">
                            <div class="radio" style="margin-top: 5px;">
                                <input name="cu_type" id="retail" value="retail" type="radio"><label for="retail"> Retail</label><br/>
                                <input name="cu_type" id="distribution" value="distribution" type="radio"><label for="distribution"> Distribution</label><br/>
                                <input name="cu_type" id="cutting" value="cutting" type="radio"><label for="cutting"> Contract Cutting</label>
                            </div>
                        </div>
                    </div>

                    <form id="add_retail_customer" style="display:none;">
                        <table style="width:100%;margin-top:8px;">
                            <tr>
                                <td style="width: 33.3%;">
                                    <select class="form-control" id="dealer_code" name="dealer_code">
                                        <?php
                                        $dealer_qry = $dbconn->query("SELECT * FROM dealers ORDER BY dealer_id ASC;");

                                        while($dealer = $dealer_qry->fetch_assoc()) {
                                            echo "<option value='{$dealer['dealer_id']}'>{$dealer['dealer_id']} ({$dealer['contact']})</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr style="height: 5px;">
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <input type="text" name="project_name" class="form-control pull-left" placeholder="Project Name" id="project_name" style="width:50%;" />
                                    <input type="text" name="project_addr" class="form-control pull-left" placeholder="Project Address" id="project_addr" style="width:50%;">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 33.3%;"><input type="text" name="project_city" class="form-control" placeholder="Project City" id="project_city"></td>
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
                                            <td style="width: 33.3%;"><input type="text" name="project_zip" class="form-control" placeholder="Project Zip" id="project_zip"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td><input type="text" name="project_landline" class="form-control" placeholder="Project Landline" id="project_landline"></td>
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
                                <td colspan="3"><div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div></td>
                            </tr>
                            <tr>
                                <td colspan="3"><div class="checkbox"><input id="secondary_addr_chk" type="checkbox"><label for="secondary_addr_chk"> Customer Secondary Address</label></div></td>
                            </tr>
                            <tr style="display:none;" class="secondary_addr_disp">
                                <td colspan="2"><input type="text" name="secondary_addr" class="form-control" placeholder="Secondary Address" id="secondary_addr"></td>
                                <td><input type="text" name="secondary_landline" class="form-control" placeholder="Secondary Landline" id="secondary_landline"></td>
                            </tr>
                            <tr style="display:none;" class="secondary_addr_disp">
                                <td colspan="2">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 33.3%;"><input type="text" name="secondary_city" class="form-control" placeholder="Secondary City" id="secondary_city"></td>
                                            <td style="width: 33.3%;"><select class="form-control" id="secondary_state" name="secondary_state">
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
                                            <td style="width: 33.3%;"><input type="text" name="secondary_zip" class="form-control" placeholder="Secondary Zip" id="secondary_zip"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3"><div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div></td>
                            </tr>
                            <tr>
                                <td colspan="3"><div class="checkbox"><input id="contractor_chk" type="checkbox"><label for="contractor_chk"> Contractor</label></div></td>
                            </tr>
                            <tr style="display:none;" class="contractor_disp">
                                <td><input type="text" name="contractor_name" class="form-control" placeholder="Contractor Name" id="contractor_name"></td>
                                <td><input type="text" name="contractor_business_num" class="form-control" placeholder="Contractor Business Number" id="contractor_business_num"></td>
                                <td><input type="text" name="contractor_cell_num" class="form-control" placeholder="Contractor Cell Number" id="contractor_cell_num"></td>
                            </tr>
                            <tr style="display:none;" class="contractor_disp">
                                <td><input type="text" name="contractor_addr" class="form-control" placeholder="Contractor Address" id="contractor_addr"></td>
                                <td colspan="2">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 33.3%;"><input type="text" name="contractor_city" class="form-control" placeholder="Contractor City" id="contractor_city"></td>
                                            <td style="width: 33.3%;"><select class="form-control" id="contractor_state" name="contractor_state">
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
                                            <td style="width: 33.3%;"><input type="text" name="contractor_zip" class="form-control" placeholder="Contractor Zip" id="contractor_zip"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr style="display:none;" class="contractor_disp">
                                <td><input type="text" name="contractor_email" class="form-control" placeholder="Contractor Email Address" id="contractor_email"></td>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                            <tr style="display:none;" class="contractor_disp">
                                <td><input type="text" name="project_mgr" class="form-control" placeholder="Project Manager" id="project_mgr"></td>
                                <td><input type="text" name="project_mgr_cell" class="form-control" placeholder="Project Manager Cell" id="project_mgr_cell"></td>
                                <td><input type="text" name="project_mgr_email" class="form-control" placeholder="Project Manager Email" id="project_mgr_email"></td>
                            </tr>
                            <tr>
                                <td colspan="3"><div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div></td>
                            </tr>
                            <tr>
                                <td><div class="checkbox"><input id="billing_addr_chk" type="checkbox"><label for="billing_addr_chk"> Billing Information</label></div></td>
                                <td style="display:none;" class="billing_info_disp"><label class="c-input c-radio"><input id="bill_homeowner" name="bill_to" type="radio" value="homeowner"><span class="c-indicator"></span>Bill Homeowner</label></td>
                                <td style="display:none;" class="billing_info_disp"><label class="c-input c-radio"><input id="bill_contractor" name="bill_to" type="radio" value="contractor"><span class="c-indicator"></span>Bill Contractor</label></td>
                            </tr>
                            <tr style="display:none;" class="billing_info_disp">
                                <td><input type="text" name="billing_contact" class="form-control" placeholder="Billing Contact" id="billing_contact"></td>
                                <td><input type="text" name="billing_landline" class="form-control" placeholder="Billing Landline" id="billing_landline"></td>
                                <td><input type="text" name="billing_cell" class="form-control" placeholder="Billing Cell" id="billing_cell"></td>
                            </tr>
                            <tr style="display:none;" class="billing_info_disp">
                                <td><input type="text" name="billing_addr" class="form-control" placeholder="Billing Address" id="billing_addr"></td>
                                <td colspan="2">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 33.3%;"><input type="text" name="billing_city" class="form-control" placeholder="Billing City" id="billing_city"></td>
                                            <td style="width: 33.3%;"><select class="form-control" id="billing_state" name="billing_state">
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
                                            <td style="width: 33.3%;"><input type="text" name="billing_zip" class="form-control" placeholder="Billing Zip" id="billing_zip"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr style="display:none;" class="billing_info_disp">
                                <td style="height:8px"></td>
                            </tr>
                            <tr style="display:none;" class="billing_info_disp">
                                <td colspan="3">
                                    <input type="text" name="billing_account" autocomplete="off" class="form-control pull-left" placeholder="ACH Account #" id="billing_account" style="width: 50%;">
                                    <input type="text" name="billing_routing" autocomplete="off" class="form-control pull-right" placeholder="ACH Routing #" id="billing_routing" style="width: 50%;">
                                </td>
                            </tr>
                            <tr style="display:none;" class="billing_info_disp">
                                <td><input type="text" name="billing_cc_num" class="form-control" placeholder="Credit Card #" id="billing_cc_num"></td>
                                <td><input type="text" name="billing_cc_exp" class="form-control" placeholder="Exp. Date" id="billing_cc_exp"></td>
                                <td><input type="text" name="billing_cc_ccv" class="form-control" placeholder="CCV Code" id="billing_cc_ccv"></td>
                            </tr>
                        </table>
                    </form>

                    <form id="add_distributor_cc" style="display:none;">
                        <table style="width:100%;">
                            <tr>
                                <td style="width: 33.3%;">
                                    <select class="form-control" id="salesperson" name="salesperson">
                                        <option value="Charles McCamy" selected disabled>Salesperson</option>
                                        <option value="Charles McCamy">Charles McCamy</option>
                                        <option value="Criterion Sales">Criterion Sales</option>
                                    </select>
                                </td>
                                <td style="width: 33.3%;">
                                    <select class="form-control" id="contractor_dealer_code" name="contractor_dealer_code">
                                        <?php
                                            $dealer_qry = $dbconn->query("SELECT * FROM dealers");

                                            while($dealer = $dealer_qry->fetch_assoc()) {
                                                echo "<option value='{$dealer['dealer_id']}'>{$dealer['dealer_id']} ({$dealer['contact']})</option>";
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td style="width: 33.3%;">&nbsp;</td>
                            </tr>
                            <tr style="height: 5px;">
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="business_landline" class="form-control" placeholder="Business Phone" id="business_landline"></td>
                                <td><input type="text" name="business_cell" class="form-control" placeholder="Cell Phone" id="business_cell"></td>
                                <td><input type="text" name="business_email" class="form-control" placeholder="Email Address" id="business_email"></td>
                            </tr>
                            <tr style="height: 5px;">
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="project_name" class="form-control" placeholder="Project/Customer PO" id="project_name" /></td>
                                <td><input type="text" name="project_cell" class="form-control" placeholder="Cell Phone (Project)" id="project_cell" /></td>
                                <td><input type="text" name="project_email" class="form-control" placeholder="Email Address" id="project_email" /></td>
                            </tr>
                            <tr style="height: 5px;">
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><input type="text" name="delivery_addr" class="form-control" placeholder="Delivery Address" id="delivery_addr"></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 33.3%;"><input type="text" name="delivery_city" class="form-control" placeholder="City" id="delivery_city"></td>
                                            <td style="width: 33.3%;"><select class="form-control" id="delivery_state" name="delivery_state">
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
                                            <td style="width: 33.3%;"><input type="text" name="delivery_zip" class="form-control" placeholder="Zip" id="delivery_zip"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td><input type="text" name="delivery_landline" class="form-control" placeholder="Landline" id="delivery_landline"></td>
                            </tr>
                            <tr style="height: 5px;">
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="billing_contact" class="form-control" placeholder="Billing Contact" id="billing_contact"></td>
                                <td><input type="text" name="billing_landline" class="form-control" placeholder="Billing Landline" id="billing_landline"></td>
                                <td><input type="text" name="billing_cell" class="form-control" placeholder="Billing Cell" id="billing_cell"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="billing_addr" class="form-control" placeholder="Billing Address" id="billing_addr"></td>
                                <td colspan="2">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 33.3%;"><input type="text" name="billing_city" class="form-control" placeholder="City" id="billing_city"></td>
                                            <td style="width: 33.3%;"><select class="form-control" id="billing_state" name="billing_state">
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
                                            <td style="width: 33.3%;"><input type="text" name="billing_zip" class="form-control" placeholder="Zip" id="billing_zip"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr style="height: 5px;">
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="billing_account" class="form-control pull-left" placeholder="ACH Account #" id="billing_account" autocomplete="off"></td>
                                <td><input type="text" name="billing_routing" class="form-control pull-right" placeholder="ACH Routing #" id="billing_routing" autocomplete="off"></td>
                                <td><input type="text" name="tax_id" class="form-control pull-right" placeholder="Tax ID" id="tax_id" autocomplete="off"></td>
                            </tr>
                            <tr style="height: 5px;">
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><input type="text" name="physical_addr" class="form-control" placeholder="Physical Address" id="physical_addr"></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 33.3%;"><input type="text" name="physical_city" class="form-control" placeholder="City" id="physical_city"></td>
                                            <td style="width: 33.3%;"><select class="form-control" id="physical_state" name="physical_state">
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
                                            <td style="width: 33.3%;"><input type="text" name="physical_zip" class="form-control" placeholder="Zip" id="physical_zip"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr style="height: 5px;">
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="project_mgr" class="form-control" placeholder="Project Manager" id="project_mgr"></td>
                                <td><input type="text" name="project_mgr_cell" class="form-control" placeholder="Project Manager Cell" id="project_mgr_cell"></td>
                                <td><input type="text" name="project_mgr_email" class="form-control" placeholder="Project Manager Email" id="project_mgr_email"></td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary waves-effect waves-light" id="submit_new_customer">Add Customer</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->