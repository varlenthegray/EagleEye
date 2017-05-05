<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<!-- Multi-select -->
<link href="/assets/plugins/multiselect/css/multi-select.css" rel="stylesheet" type="text/css"/>


<div class="row">
    <!-- Left column -->
    <div class="col-md-6" style="min-height: 240px;">
        <div class="col-md-12">
            <!-- Search box -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card-box">
                        <ul class="nav nav-tabs m-b-10" id="searchTabs" role="tablist">
                            <li class="nav-item" id="search1_li">
                                <a class="nav-link active" searchid="1" id="searchTab1" data-toggle="tab" href="#search1" role="tab" aria-controls="search1" aria-expanded="true">Search 1</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="search_add_tab" data-toggle="tab" href="#" role="tab" aria-controls="search_add" aria-expanded="true"><strong>+</strong></a>
                            </li>
                        </ul>

                        <div class="tab-content" id="searchTabContent">
                            <div role="tabpanel" class="tab-pane fade in active" id="search1" aria-labelledby="search1">
                                <div id="search_accordion1">
                                    <h3>Customer</h3>
                                    <div class="pad-lr-12">
                                        <div class="row">
                                            <div class="col-md-9" style="padding-top: 5px;">
                                                <label class="c-input c-checkbox">
                                                    <input type="checkbox" name="cu_project_status" value="2" checked>
                                                    <span class="c-indicator"></span>
                                                    Quote
                                                </label>
                                                <label class="c-input c-checkbox">
                                                    <input type="checkbox" name="cu_project_status" value="1" checked>
                                                    <span class="c-indicator"></span>
                                                    Production
                                                </label>
                                                <label class="c-input c-checkbox">
                                                    <input type="checkbox" name="cu_project_status" value="3">
                                                    <span class="c-indicator"></span>
                                                    Closed
                                                </label>
                                            </div>

                                            <div class="col-md-3" style="padding-bottom: 5px;">
                                                <button class="btn waves-effect btn-primary pull-right" data-toggle="modal" data-target="#modalAddCustomer"> <i class="zmdi zmdi-account-add"></i> </button>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="SO #" id="cu_sales_order_num1" name="cu_sales_order_num1" />
                                            </div>

                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Project" id="cu_project_name1" name="project_name1" />
                                            </div>

                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Dealer/Contractor" id="cu_dealer_contractor1" name="dealer_contractor1" />
                                            </div>

                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Project Manager" id="cu_project_manager1" name="project_manager1" />
                                            </div>
                                        </div>
                                    </div>

                                    <h3>Vendor</h3>
                                    <div class="pad-lr-12">
                                        <div class="row">
                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Sales Order #" id="vn_sales_order_num1" name="vn_sales_order_num1" />
                                            </div>

                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Project Name" id="vn_project_name1" name="vn_project_name1" />
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Vendor" id="vn_vendor1" name="vn_vendor1" />
                                            </div>

                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Acknowledgement #" id="vn_ack_number1" name="vn_ack_number1" />
                                            </div>

                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Invoice Number" id="vn_invoice_num1" name="vn_invoice_num1" />
                                            </div>

                                            <div class="col-md-3 pad-lr-4">
                                                <input class="form-control" type="text" placeholder="Date Range" id="vn_date_range1" name="vn_date_range1" />
                                            </div>
                                        </div>
                                    </div>

                                    <h3>Inventory</h3>
                                    <div class="pad-lr-12">
                                        <div class="col-md-3 pad-lr-4">
                                            <input class="form-control" type="text" placeholder="Sales Order #" id="inv_sales_order_num1" name="inv_sales_order_num1" />
                                        </div>

                                        <div class="col-md-3 pad-lr-4">
                                            <input class="form-control" type="text" placeholder="Description" id="inv_description1" name="inv_description1" />
                                        </div>

                                        <div class="col-md-3 pad-lr-4">
                                            <input class="form-control" type="text" placeholder="Part #" id="inv_part_num1" name="inv_part_num1" />
                                        </div>

                                        <div class="col-md-3 pad-lr-4">
                                            <input class="form-control" type="text" placeholder="Date Range" id="inv_date_range1" name="inv_date_range1" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End search box -->

            <!-- Add Customer modal -->
            <div id="modalAddCustomer" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddCustomerLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="modalAddCustomerTitle">Add New Customer</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <form name="add_new_customer" id="add_new_customer">
                                        <table style="width: 100%;">
                                            <tr>
                                                <?php
                                                $qry = $dbconn->query("SELECT DISTINCT sales_order_num FROM customer ORDER BY sales_order_num DESC LIMIT 0,1");

                                                if($qry->num_rows > 0) {
                                                    $result = $qry->fetch_assoc();

                                                    $new_so_num = $result['sales_order_num'] + 1;
                                                } else {
                                                    $new_so_num = 1;
                                                }
                                                ?>
                                                <td><input type="text" class="form-control" id="new_so_num" name="new_so_num" placeholder="SO #" value="<?php echo $new_so_num; ?>"></td>
                                                <td><input type="text" class="form-control" id="new_dealer_code" name="new_dealer_code" placeholder="Dealer Code"></td>
                                                <td><input type="text" class="form-control" id="new_project_name" name="new_project_name" placeholder="Project Name"></td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" id="new_org_name" name="new_org_name" placeholder="Organization Name"></td>
                                                <td><input type="text" class="form-control" id="new_dealer_contractor" name="new_dealer_contractor" placeholder="Dealer/Contractor"></td>
                                                <td><input type="text" class="form-control" id="new_project_manager" name="new_project_manager" placeholder="Project Manager"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3"><input type="text" class="form-control" id="new_addr_1" name="new_addr_1" placeholder="Address 1"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3"><input type="text" class="form-control" id="new_addr_2" name="new_addr_2" placeholder="Address 2"></td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" id="new_city" name="new_city" placeholder="City"></td>
                                                <td>
                                                    <select class="form-control" id="new_state" name="new_state">
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
                                                    </select>
                                                </td>
                                                <td><input type="text" class="form-control" id="new_zip" name="new_zip" placeholder="ZIP"></td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" id="new_phone1" name="new_phone1" placeholder="Primary Phone"></td>
                                                <td><input type="text" class="form-control" id="new_phone2" name="new_phone2" placeholder="Alt Phone"></td>
                                                <td><input type="text" class="form-control" id="new_phone3" name="new_phone3" placeholder="Alt Phone 2"></td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" id="new_email1" name="new_email1" placeholder="Primary Email Address"></td>
                                                <td><input type="text" class="form-control" id="new_email2" name="new_email2" placeholder="Alternate Email Address"></td>
                                                <td>
                                                    <select name="new_account_type" id="new_account_type" class="form-control">
                                                        <option value="R" disabled>Account Type</option>
                                                        <option value="R" selected>Retail</option>
                                                        <option value="W">Wholesale</option>
                                                        <option value="D">Distribution</option>
                                                    </select>
                                                </td>
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
            </div>
            <!-- /.modal -->

            <!-- Search results box -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card-box" id="search_results_card" style="display: none;min-height: 294px;">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="tablesaw table" data-tablesaw-mode="swipe" data-tablesaw-sortable data-tablesaw-minimap>
                                    <thead>
                                    <tr>
                                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="persist"">SO#</th>
                                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Project</th>
                                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Dealer/Contractor</th>
                                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Project Manager</th>
                                    </tr>
                                    </thead>
                                    <tbody id="search_results_table">
                                    <tr>
                                        <td colspan="4">No results to display</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-md-right">
                                <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Collapse</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Print</button>
                                <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Export</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End search results box -->
        </div>
    </div>
    <!-- End left column -->

    <!-- Right column -->
    <div class="col-md-6">
        <div class="card-box" id="cal_email_tasks" style="min-height: 511px;">
            <!-- Loaded in by /ondemand/js/page_content_functions.js and /html/right_panel.php -->
        </div>
    </div>
    <!-- End right column -->
</div>

<div class="row" id="room_results_row" style="display: none;">
    <div class="col-md-10">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <table class="tablesaw table" data-tablesaw-sortable>
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="persist">Room</th>
                            <th scope="col" data-tablesaw-sortable-col>Sales</th>
                            <th scope="col" data-tablesaw-sortable-col>Pre-Production</th>
                            <th scope="col" data-tablesaw-sortable-col>Sample</th>
                            <th scope="col" data-tablesaw-sortable-col>Door/Drawer</th>
                            <th scope="col" data-tablesaw-sortable-col>Custom</th>
                            <th scope="col" data-tablesaw-sortable-col>Box</th>
                        </tr>
                        </thead>
                        <tbody id="room_search_table">
                        <tr>
                            <td colspan="5">No results to display</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <button type="button" id="add_room" name="add_room" class="btn btn-success waves-effect waves-light w-xs">Add Room</button>
                </div>

                <div class="col-md-6 text-md-right">
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Collapse</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Print</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Export</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="individual_room_info" style="display: none;">
    <div class="col-md-8">
        <div class="card-box">
            <h4 id="room_info_title">Add New Room</h4>

            <form name="room_adjustment" id="room_adjustment">
                <input type="hidden" value="???" name="add_to_sonum" id="add_to_sonum">

                <table style="width: 100%;">
                    <tr>
                        <td><label for="room">Room</label></td>
                        <td>
                            <select name="room" id="room" class="form-control">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                                <option value="F">F</option>
                                <option value="G">G</option>
                                <option value="H">H</option>
                                <option value="J">J</option>
                                <option value="K">K</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="N">N</option>
                                <option value="P">P</option>
                                <option value="Q">Q</option>
                                <option value="R">R</option>
                                <option value="S">S</option>
                                <option value="T">T</option>
                                <option value="U">U</option>
                                <option value="V">V</option>
                                <option value="W">W</option>
                                <option value="X">X</option>
                                <option value="Y">Y</option>
                                <option value="Z">Z</option>
                            </select>
                        </td>
                        <td><label for="room_name">Room Name</label></td>
                        <td>
                            <input type="text" class="form-control" id="room_name" name="room_name" placeholder="Room Name">
                        </td>
                        <td><label for="product_type">Product Type</label></td>
                        <td>
                            <select name="product_type" id="product_type" class="form-control" tabindex="5">
                                <option value="Cabinet" selected>Cabinet</option>
                                <option value="Closet">Closet</option>
                                <option value="Sample">Sample</option>
                                <option value="Display">Display</option>
                                <option value="Add-on">Add-on</option>
                                <option value="Warranty">Warranty</option>
                            </select>
                        </td>
                        <td><label for="remodel_required">Remodel Required</label></td>
                        <td>
                            <select name="remodel_required" id="remodel_required" class="form-control" tabindex="7">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="room_notes">Room Notes</label></td>
                        <td>
                        <textarea class="form-control" id="room_notes" name="room_notes" placeholder="Room Notes" rows="3"
                                  data-toggle="popover" data-placement="top" data-trigger="focus" title="" data-html="true"
                                  data-content="<table style='font-size: 9px;'>
                                <tr>
                                    <td>CON = Conestoga</td>
                                    <td class='text-md-right'>RW = Rework</td>
                                </tr>
                                <tr>
                                    <td>DEL = Delivery</td>
                                    <td class='text-md-right'>S/B = Scheduled Back</td>
                                </tr>
                                <tr>
                                    <td>DPL = Diminishing Punch List</td>
                                    <td class='text-md-right'>SEL = Selections</td>
                                </tr>
                                <tr>
                                    <td>EM = Email</td>
                                    <td class='text-md-right'>T/W = This Week</td>
                                </tr>
                                <tr>
                                    <td>ETA = Estimated Time of Arrival</td>
                                    <td class='text-md-right'>W/A = Will Advise</td>
                                </tr>
                                <tr>
                                    <td>FU = Follow Up</td>
                                    <td class='text-md-right'>W/C = Will Contact</td>
                                </tr>
                                <tr>
                                    <td>N/A = Not Available</td>
                                    <td class='text-md-right'>WO = Work Order</td>
                                </tr>
                            </table>" data-original-title="Abbreviations"></textarea>
                        </td>
                        <td><label for="assigned_bracket">Assigned Bracket</label></td>
                        <td>
                            <select name="assigned_bracket" id="assigned_bracket" class="form-control">
                                <option value="N/A" disabled selected>N/A</option>
                                <?php
                                $qry = $dbconn->query("SELECT * FROM brackets ORDER BY bracket_name ASC");

                                if($qry->num_rows > 0) {
                                    while($result = $qry->fetch_assoc()) {
                                        echo "<option value='{$result['id']}'>{$result['bracket_name']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="8">&nbsp;</td>
                    </tr>
                    <tr>
                        <td><label for="sales_bracket">Sales Bracket</label></td>
                        <td>
                            <select id="sales_bracket" name="sales_bracket" class="form-control">
                                <option value="N/A">N/A</option>
                                <?php
                                $qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Sales' ORDER BY op_id ASC");

                                if($qry->num_rows > 0) {
                                    while($result = $qry->fetch_assoc()) {
                                        echo "<option value='{$result['id']}'>{$result['op_id']}-{$result['job_title']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><label for="pre_prod_bracket">Pre-production Bracket</label></td>
                        <td>
                            <select id="pre_prod_bracket" name="pre_prod_bracket" class="form-control">
                                <option value="N/A">N/A</option>
                                <?php
                                $qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Pre-Production' ORDER BY op_id ASC");

                                if($qry->num_rows > 0) {
                                    while($result = $qry->fetch_assoc()) {
                                        echo "<option value='{$result['id']}'>{$result['op_id']}-{$result['job_title']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><label for="sample_bracket">Sample Bracket</label></td>
                        <td>
                            <select id="sample_bracket" name="sample_bracket" class="form-control">
                                <option value="N/A">N/A</option>
                                <?php
                                $qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Sample' ORDER BY op_id ASC");

                                if($qry->num_rows > 0) {
                                    while($result = $qry->fetch_assoc()) {
                                        echo "<option value='{$result['id']}'>{$result['op_id']}-{$result['job_title']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="door_drawer_bracket">Door/Drawer Bracket</label></td>
                        <td>
                            <select id="door_drawer_bracket" name="door_drawer_bracket" class="form-control">
                                <option value="N/A">N/A</option>
                                <?php
                                $qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Drawer & Doors' ORDER BY op_id ASC");

                                if($qry->num_rows > 0) {
                                    while($result = $qry->fetch_assoc()) {
                                        echo "<option value='{$result['id']}'>{$result['op_id']}-{$result['job_title']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><label for="custom_bracket">Custom Bracket</label></td>
                        <td>
                            <select id="custom_bracket" name="custom_bracket" class="form-control">
                                <option value="N/A">N/A</option>
                                <?php
                                $qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Custom' ORDER BY op_id ASC");

                                if($qry->num_rows > 0) {
                                    while($result = $qry->fetch_assoc()) {
                                        echo "<option value='{$result['id']}'>{$result['op_id']}-{$result['job_title']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><label for="box_bracket">Box Bracket</label></td>
                        <td>
                            <select id="box_bracket" name="box_bracket" class="form-control">
                                <option value="N/A">N/A</option>
                                <?php
                                $qry = $dbconn->query("SELECT * FROM operations WHERE department = 'Box' ORDER BY op_id ASC");

                                if($qry->num_rows > 0) {
                                    while($result = $qry->fetch_assoc()) {
                                        echo "<option value='{$result['id']}'>{$result['op_id']}-{$result['job_title']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            <button type="button" id="room_save" name="room_save" class="btn btn-success waves-effect waves-light w-xs">Save</button>
                            <button type="button" id="manage_brackets" name="manage_brackets" class="btn btn-success waves-effect waves-light w-xs">Manage Brackets</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>

<div class="row" id="manage_bracket" style="display: none;">
    <div class="col-md-8">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h4>Manage Brackets (<span id="bracket_adjustment_title">SO# 736 ROOM A</span>)</h4>

                    <table class="bracket-adjustment-table">
                        <tr>
                            <td class="bracket-border-top" style="width: 350px;">
                                <div class="row bracket-header-custom">
                                    <div class="col-md-8"><h5><label for="sales_bracket_adjustments">Sales Bracket</label></h5></div>
                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sales_published" id="sales_published"> <span class="c-indicator"></span> Published</label> </div>
                                </div>
                            </td>
                            <td style="width:80px;">&nbsp;</td>
                            <td class="bracket-border-top" style="width: 350px;">
                                <div class="row bracket-header-custom">
                                    <div class="col-md-8"><h5><label for="pre_prod_bracket_adjustments">Pre-production Bracket</label></h5></div>
                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="pre_prod_published" id="pre_prod_published"> <span class="c-indicator"></span> Published</label> </div>
                                </div>
                            </td>
                            <td style="width:80px;">&nbsp;</td>
                            <td class="bracket-border-top" style="width: 350px;">
                                <div class="row bracket-header-custom">
                                    <div class="col-md-8"><h5><label for="sample_bracket_adjustments">Sample Bracket</label></h5></div>
                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="sample_published" id="sample_published"> <span class="c-indicator"></span> Published</label> </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="bracket-border-bottom">
                                <select multiple="multiple" class="multi-select" id="sales_bracket_adjustments" name="sales_bracket_adjustments[]" data-plugin="multiselect"></select>
                            </td>
                            <td>&nbsp;</td>
                            <td class="bracket-border-bottom">
                                <select multiple="multiple" class="multi-select" id="pre_prod_bracket_adjustments" name="pre_prod_bracket_adjustments[]" data-plugin="multiselect"></select>
                            </td>
                            <td>&nbsp;</td>
                            <td class="bracket-border-bottom">
                                <select multiple="multiple" class="multi-select" id="sample_bracket_adjustments" name="sample_bracket_adjustments[]" data-plugin="multiselect"></select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="bracket-border-top">
                                <div class="row bracket-header-custom">
                                    <div class="col-md-9"><h5><label for="door_drawer_bracket_adjustments">Door/Drawer Bracket</label></h5></div>
                                    <div class="col-md-3"><label class="c-input c-checkbox"><input type="checkbox" name="door_drawer_published" id="door_drawer_published"> <span class="c-indicator"></span> Published</label> </div>
                                </div>
                            </td>
                            <td style="width:80px;">&nbsp;</td>
                            <td class="bracket-border-top">
                                <div class="row bracket-header-custom">
                                    <div class="col-md-8"><h5><label for="custom_bracket_adjustments">Custom Bracket</label></h5></div>
                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="custom_published" id="custom_published"> <span class="c-indicator"></span> Published</label> </div>
                                </div>
                            </td>
                            <td style="width:80px;">&nbsp;</td>
                            <td class="bracket-border-top">
                                <div class="row bracket-header-custom">
                                    <div class="col-md-8"><h5><label for="box_bracket_adjustments">Box Bracket</label></h5></div>
                                    <div class="col-md-4"><label class="c-input c-checkbox"><input type="checkbox" name="box_published" id="box_published"> <span class="c-indicator"></span> Published</label> </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="bracket-border-bottom">
                                <select multiple="multiple" class="multi-select" id="door_drawer_bracket_adjustments" name="door_drawer_bracket_adjustments[]" data-plugin="multiselect"></select>
                            </td>
                            <td>&nbsp;</td>
                            <td class="bracket-border-bottom">
                                <select multiple="multiple" class="multi-select" id="custom_bracket_adjustments" name="custom_bracket_adjustments[]" data-plugin="multiselect"></select>
                            </td>
                            <td>&nbsp;</td>
                            <td class="bracket-border-bottom">
                                <select multiple="multiple" class="multi-select" id="box_bracket_adjustments" name="box_bracket_adjustments[]" data-plugin="multiselect"></select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6">&nbsp;</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="button" id="bracket_adjustment_save" name="bracket_adjustment_save" class="btn btn-success waves-effect waves-light w-xs">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Multi-select -->
<script type="text/javascript" src="/assets/plugins/multiselect/js/jquery.multi-select.js"></script>


<!-- Loading page content -->
<script src="/ondemand/js/page_content_functions.js"></script>

<script>
    var active_so_num = null;
    var active_room = null;
    var active_room_id = null;

    loadCalendarPane(); // found in page_content_functions

    function updateSearchTable(field, search, functn) {
        if(field.length >= 1) {
            $.post("/ondemand/livesearch/search_results.php?search=" + search, {find: field, functn: functn}, function(data) {
                $("#search_results_table").html(data);

                if(data !== '') {
                    $("#search_results_card").show();
                }
            });
        } else {
            $("#search_results_card").hide();
        }
    }

    function displaySO(sonum) {
        active_so_num = sonum;

        $.post("/ondemand/livesearch/search_results.php?search=room", {find: sonum}, function(data) {
            $("#room_search_table").html(data);
            $("#room_results_row").show();
            $("#individual_room_info").hide();
            $("#manage_bracket").hide();
        });
    }

    function recalculateBrackets(overrideSelected) {
        var selectedBracket = $("#assigned_bracket").val();

        $.post("/ondemand/shopfloor/job_actions.php?action=update_brackets", {bracketID: selectedBracket, room: active_room, sonum: active_so_num}, function(data) {
            // HERE WE GO, into the rabbit hole with this!

            var input = $.parseJSON(data); // grab the JSON data returned, this is a 3D array, objects inside of objects

            function generateOptions(department) { // generates the options based on the department provided
                var outputOptions = "<option value='N/A'>N/A</option>"; // initial option is always N/A

                $.each(input[department], function(key, value) { // for each MULTIDIMENSIONAL result inside of SALES concatenate INFORMATION
                    outputOptions += "<option value='" + value.id + "'>" + value.op_id + "-" + value.job_title + "</option>"; // value is the object inside of the object
                }); // end

                return outputOptions; // send back the final output
            }

            // grab each of the departments and their related options
            var salesOptions = generateOptions("Sales");
            var preprodOptions = generateOptions("Pre-Production");
            var sampleOptions = generateOptions("Sample");
            var doordrawerOptions = generateOptions("Drawer & Doors");
            var customOptions = generateOptions("Custom");
            var boxOptions = generateOptions("Box");

            // find the options inside of the SALES bracket, REMOVE them, wait for that DOM update to FINISH, then ADD the options again
            $("#sales_bracket").find("option").remove().end().append(salesOptions);
            
            $("#pre_prod_bracket").find("option").remove().end().append(preprodOptions); //FIND ALLOWS YOU TO PICK THE SUB!
            $("#sample_bracket").find("option").remove().end().append(sampleOptions);
            $("#door_drawer_bracket").find("option").remove().end().append(doordrawerOptions);
            $("#custom_bracket").find("option").remove().end().append(customOptions);
            $("#box_bracket").find("option").remove().end().append(boxOptions);

            if(overrideSelected === undefined) {
                // select the second option inside of the list
                $("#sales_bracket option:nth-child(2)").attr("selected", "selected"); //TODO: find a different way to select the second option, "Inefficient"
                $("#pre_prod_bracket option:nth-child(2)").attr("selected", "selected");
                $("#sample_bracket option:nth-child(2)").attr("selected", "selected");
                $("#door_drawer_bracket option:nth-child(2)").attr("selected", "selected");
                $("#custom_bracket option:nth-child(2)").attr("selected", "selected");
                $("#box_bracket option:nth-child(2)").attr("selected", "selected");
            } else {
                $("#sales_bracket").val(overrideSelected.sales_bracket);
                $("#pre_prod_bracket").val(overrideSelected.preproduction_bracket);
                $("#sample_bracket").val(overrideSelected.sample_bracket);
                $("#door_drawer_bracket").val(overrideSelected.doordrawer_bracket);
                $("#custom_bracket").val(overrideSelected.custom_bracket);
                $("#box_bracket").val(overrideSelected.box_bracket);
            }
        });
    }

    function displayRoomInfo(roomID) {
        $("#add_to_sonum").val(active_so_num);

        $.post("/ondemand/shopfloor/job_actions.php?action=edit_room", {roomID: roomID}, function(data) {
            var roomInfo;

            if(roomInfo = $.parseJSON(data)) {
                active_room = roomInfo.room;
                active_room_id = data.id;

                $("#room").val(roomInfo.room);
                $("#room_name").val(roomInfo.room_name);
                $("#product_type").val(roomInfo.product_type);
                $("#remodel_required").val(roomInfo.remodel_reqd);
                $("#room_notes").val(roomInfo.room_notes);
                $("#assigned_bracket").val(roomInfo.assigned_bracket);

                recalculateBrackets(roomInfo);

                $("#room_info_title").text("Edit room " + roomInfo.room + " of SO# " + active_so_num);


            } else {
                $("body").append(data);
            }
        }).done(function() {
            $("#individual_room_info").show();
        });
    }

    function displayBracketInfo(active_room) {
        $("#bracket_adjustment_title").text("SO# " + active_so_num + " ROOM " + active_room);

        var selectedBracket = $("#assigned_bracket").val();

        $.post("/ondemand/shopfloor/job_actions.php?action=update_brackets", {bracketID: selectedBracket, sonum: active_so_num, room: active_room}, function(data) {
            // HERE WE GO, into the rabbit hole with this!

            var input = $.parseJSON(data); // grab the JSON data returned, this is a 3D array, objects inside of objects

            function generateOptions(department) { // generates the options based on the department provided
                var outputOptions = ''; // no initial options in this case; we want only real operations

                $.each(input[department], function(key, value) { // for each MULTIDIMENSIONAL result inside of SALES concatenate INFORMATION
                    outputOptions += "<option value='" + value.id + "'>" + value.op_id + "-" + value.job_title + "</option>"; // value is the object inside of the object
                }); // end

                return outputOptions; // send back the final output
            }

            // grab each of the departments and their related options
            var salesOptions = generateOptions("Sales");
            var preprodOptions = generateOptions("Pre-Production");
            var sampleOptions = generateOptions("Sample");
            var doordrawerOptions = generateOptions("Drawer & Doors");
            var customOptions = generateOptions("Custom");
            var boxOptions = generateOptions("Box");

            console.log(input['Published']);

            if(input['Published'][0] === "1") $("#sales_published").prop('checked', true);
            if(input['Published'][1] === "1") $("#pre_prod_published").prop('checked', true);
            if(input['Published'][2] === "1") $("#sample_published").prop('checked', true);
            if(input['Published'][3] === "1") $("#door_drawer_published").prop('checked', true);
            if(input['Published'][4] === "1") $("#custom_published").prop('checked', true);
            if(input['Published'][5] === "1") $("#box_published").prop('checked', true);

            // find the options inside of the SALES bracket, REMOVE them, wait for that DOM update to FINISH, then ADD the options again
            $("#sales_bracket_adjustments").find("option").remove().end().append(salesOptions).multiSelect('refresh');
            $("#pre_prod_bracket_adjustments").find("option").remove().end().append(preprodOptions).multiSelect('refresh');
            $("#sample_bracket_adjustments").find("option").remove().end().append(sampleOptions).multiSelect('refresh');
            $("#door_drawer_bracket_adjustments").find("option").remove().end().append(doordrawerOptions).multiSelect('refresh');
            $("#custom_bracket_adjustments").find("option").remove().end().append(customOptions).multiSelect('refresh');
            $("#box_bracket_adjustments").find("option").remove().end().append(boxOptions).multiSelect('refresh');
        }).done(function() {
            $("#manage_bracket").show();
        });
    }

    function saveRoomInfo() {
        var room_info = $("#room_adjustment").serialize();
        active_room = $("#room").val();

        $.post("/ondemand/shopfloor/job_actions.php?action=save_room", room_info, function(data) {
            if(data === 'success') {
                displayToast("success", "Added new room to SO# " + active_so_num, "New Room Added");
            } else if(data === 'success - update') {
                displayToast("info", "Updated room on existing SO#", "Updated room");
            } else {
                $("body").append(data);
            }
        });
    }

    $("#sales_bracket_adjustments").multiSelect({
        selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
        selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
    });

    $("#pre_prod_bracket_adjustments").multiSelect({
        selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
        selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
    });

    $("#sample_bracket_adjustments").multiSelect({
        selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
        selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
    });

    $("#door_drawer_bracket_adjustments").multiSelect({
        selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
        selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
    });

    $("#custom_bracket_adjustments").multiSelect({
        selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
        selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
    });

    $("#box_bracket_adjustments").multiSelect({
        selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
        selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
    });

    $("#search_accordion1").accordion();

    $("#search_add_tab").on("click", function() {
        searchCounter = generateTab(searchCounter);
    });

    $("body")
        .on("click", "[id^=searchTab]", function(e) { // this allows for the automation of search tabs
            var accordion = "search_accordion" + e.target.getAttribute("searchid"); // add more accordions

            setTimeout(function() {
                $("#" + accordion).accordion("refresh"); // refresh the accordion on click of tab
            }, 200);
        })
        .on("click", "#submit_new_customer", function() {
            var cuData = $("#add_new_customer").serialize();

            $.post("/ondemand/customer.php?action=add_new", cuData, function(data) {
                if(data === 'success') {
                    displayToast("success", "Inserted new customer information successfully!", "Added Customer");

                    $("[id^='new_']").val("");
                    $("#new_state").val("NC").change();

                    $("#modalAddCustomer").modal('hide');
                } else {
                    $("body").append(data);
                }
            });
        })
        .on("change", "#assigned_bracket", function() { // hey uh, this... i'm sorry... this one is bad - it assigns all bracket information dynamically...
            recalculateBrackets();
            $("#manage_bracket").hide();
        })
        .on("click", "#add_room", function() {
            $("#add_to_sonum").val(active_so_num);

            $("#room").val("A");
            $("#room_name").val("Kitchen");
            $("#product_type").val("Cabinet");
            $("#remodel_required").val("0");
            $("#room_notes").val("");
            $("#assigned_bracket").val("N/A");

            $("#sales_bracket").val("N/A");
            $("#pre_prod_bracket").val("N/A");
            $("#sample_bracket").val("N/A");
            $("#door_drawer_bracket").val("N/A");
            $("#custom_bracket").val("N/A");
            $("#box_bracket").val("N/A");

            $("#room_info_title").text("Add new room");

            $("#individual_room_info").show();
        })
        .on("click", "#room_save", function() {
            saveRoomInfo();
        })
        .on("click", "#manage_brackets", function() {
            saveRoomInfo();

            displayBracketInfo(active_room);
        })
        .on("click", "#bracket_adjustment_save", function() {
            var salesBracketAdjusted = $("#sales_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var preprodBracketAdjusted = $("#pre_prod_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var sampleBracketAdjusted = $("#sample_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var doordrawerBracketAdjusted = $("#door_drawer_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var customBracketAdjusted = $("#custom_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var boxBracketAdjusted = $("#box_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();

            var salesPublished = $("#sales_published").is(":checked");
            var preProdPublished = $("#pre_prod_published").is(":checked");
            var samplePublished = $("#sample_published").is(":checked");
            var doordrawerPublished = $("#door_drawer_published").is(":checked");
            var customPublished = $("#custom_published").is(":checked");
            var boxPublished = $("#box_published").is(":checked");

            var fullBracketAdjusted = [salesBracketAdjusted, preprodBracketAdjusted, sampleBracketAdjusted, doordrawerBracketAdjusted, customBracketAdjusted, boxBracketAdjusted];

            var fullBracketPayload = JSON.stringify(fullBracketAdjusted);

            var publishedString = [salesPublished, preProdPublished, samplePublished, doordrawerPublished, customPublished, boxPublished];

            var publishedPayload = JSON.stringify(publishedString);

            $.post("/ondemand/shopfloor/job_actions.php?action=update_individual_bracket", {payload: fullBracketPayload, sonum: active_so_num, room: active_room, published: publishedPayload}, function(data) {
                if(data === 'success') {
                    displayToast("success", "Successfully updated bracket for room " + active_room + " on SO# " + active_so_num + ".", "Updated Bracket")
                } else {
                    $("body").append(data);
                }
            });
        })
        .on("change", "#box_bracket", function() {
            $.post("/ondemand/shopfloor/job_actions.php?action=update_in_queue", {roomID: active_room_id, opID: $(this).val()}, function(data) {
                if(data === "success") {
                    displayToast("success", "Updated assigned operation.", "Operation Updated");
                } else {
                    $("body").append(data);
                }
            })
        });

    $("#cu_sales_order_num1")
        .on("keyup", function() { // this is on keyboard change
            updateSearchTable($(this).val(), "sonum", "displaySO");
            $("#edit_so_info").hide();
        })
        .autocomplete({
            source: "/ondemand/livesearch/general.php?search=cusonum"
        })
        .on("autocompleteselect", function(e, ui) {
            updateSearchTable(ui.item.label, "sonum", "displaySO"); // this is on click of the auto-complete
            $("#edit_so_info").hide();
        });

    $("#cu_project_name1")
        .on("keyup", function () { // this is on keyboard change
            updateSearchTable($(this).val(), "project", "displaySO");
            $("#edit_so_info").hide();
        })
        .autocomplete({
            source: "/ondemand/livesearch/general.php?search=cuproject"
        })
        .on("autocompleteselect", function(e, ui) {
            updateSearchTable(ui.item.label, "project", "displaySO"); // this is on click of the auto-complete
            $("#edit_so_info").hide();
        });

    $("#cu_dealer_contractor1")
        .on("keyup", function () { // this is on keyboard change
            updateSearchTable($(this).val(), "contractor", "displaySO");
            $("#edit_so_info").hide();
        })
        .autocomplete({
            source: "/ondemand/livesearch/general.php?search=cucontractor"
        })
        .on("autocompleteselect", function(e, ui) {
            updateSearchTable(ui.item.label, "contractor", "displaySO"); // this is on click of the auto-complete
            $("#edit_so_info").hide();
        });

    $("#cu_project_manager1")
        .on("keyup", function () { // this is on keyboard change
            updateSearchTable($(this).val(), "project_manager", "displaySO");
            $("#edit_so_info").hide();
        })
        .autocomplete({
            source: "/ondemand/livesearch/general.php?search=cupm"
        })
        .on("autocompleteselect", function(e, ui) {
            updateSearchTable(ui.item.label, "project_manager", "displaySO"); // this is on click of the auto-complete
            $("#edit_so_info").hide();
        });
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>