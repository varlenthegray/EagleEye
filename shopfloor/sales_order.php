<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

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
                                                <button class="btn waves-effect btn-primary pull-right" id="btn_add_acct"> <i class="zmdi zmdi-account-add"></i> </button>
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
                <!-- Inserted via AJAX -->
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

<div class="row" id="edit_so_info" style="display: none;">
    <div class="col-md-8">
        <div class="card-box">
            <h4>Edit SO# <span id="so_num">???</span></h4>

            <form name="edit_so" id="edit_so">
                <table style="width: 100%">
                    <tr class="form-group">
                        <td><label for="sales_order_num">SO #</label></td>
                        <td><input class="form-control" type="text" name="sales_order_num" id="sales_order_num" placeholder="SO #"></td>
                        <td><label for="project">Project</label></td>
                        <td><input class="form-control" type="text" name="project" id="project" placeholder="Project"></td>
                        <td><label for="dealer_contractor">Dealer/Contractor</label></td>
                        <td><input class="form-control" type="text" name="dealer_contractor" id="dealer_contractor" placeholder="Dealer/Contractor"></td>
                    </tr>
                    <tr>
                        <td><label for="project_manager">Project Manager</label></td>
                        <td><input class="form-control" type="text" name="project_manager" id="project_manager" placeholder="Project Manager"></td>
                        <td><label for="dealer_code">Dealer Code</label></td>
                        <td><input class="form-control" type="text" name="dealer_code" id="dealer_code" placeholder="Dealer Code"></td>
                        <td><label for="account_type">Account Type</label></td>
                        <td>
                            <select name="account_type" id="account_type" class="form-control">
                                <option value="R">Retail</option>
                                <option value="W">Wholesale</option>
                                <option value="D">Distribution</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="org_name">Organization Name</label></td>
                        <td><input class="form-control" type="text" name="org_name" id="org_name" placeholder="Organization Name"></td>
                        <td><label for="addr1">Address 1</label></td>
                        <td><input class="form-control" type="text" name="addr1" id="addr1" placeholder="Address 1"></td>
                        <td><label for="addr2">Address 2</label></td>
                        <td><input class="form-control" type="text" name="addr2" id="addr2" placeholder="Address 2"></td>
                    </tr>
                    <tr>
                    <tr>
                        <td><label for="city">City</label></td>
                        <td><input type="text" class="form-control" id="city" name="city" placeholder="City"></td>
                        <td><label for="state">State</label></td>
                        <td><select class="form-control" id="state" name="state">
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
                        <td><label for="zip">Zip</label></td>
                        <td><input type="text" class="form-control" id="zip" name="zip" placeholder="ZIP"></td>
                    </tr>
                    </tr>
                    <tr>
                        <td><label for="pri_phone">Primary Phone</label></td>
                        <td><input class="form-control" type="text" name="pri_phone" id="pri_phone" placeholder="Primary Phone"></td>
                        <td><label for="alt_phone1">Alternate Phone 1</label></td>
                        <td><input class="form-control" type="text" name="alt_phone1" id="alt_phone1" placeholder="Alternate Phone 1"></td>
                        <td><label for="alt_phone2">Alternate Phone 2</label></td>
                        <td><input class="form-control" type="text" name="alt_phone2" id="alt_phone2" placeholder="Alternate Phone 2"></td>
                    </tr>
                    <tr>
                        <td><label for="pri_email">Primary Email</label></td>
                        <td><input class="form-control" type="text" name="pri_email" id="pri_email" placeholder="Primary Email"></td>
                        <td><label for="alt_email">Alternate Email</label></td>
                        <td><input class="form-control" type="text" name="alt_email" id="alt_email" placeholder="Alternate Email"></td>
                        <td colspan="2" class="text-md-right">
                            <button type="button" class="btn btn-primary waves-effect waves-light w-xs" id="update_customer" name="update_customer">Update</button>
                        </td>
                    </tr>
                </table>

                <input type="hidden" name="record_id" id="record_id" value="???">
            </form>
        </div>
    </div>
</div>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Loading page content -->
<script src="/ondemand/js/page_content_functions.js"></script>

<script>
    loadCalendarPane(); // found in page_content_functions

    function updateSearchTable(field, search) {
        if(field.length >= 1) {
            $.post("/ondemand/livesearch/search_results.php?search=" + search, {find: field}, function(data) {
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
        $.post("/ondemand/livesearch/search_results.php?search=edit_so_num", {find: sonum}, function(data) {
            var cuInfo = $.parseJSON(data);

            $("#so_num").text(cuInfo.sales_order_num);

            $("#sales_order_num").val(cuInfo.sales_order_num);
            $("#project").val(cuInfo.project);
            $("#dealer_contractor").val(cuInfo.dealer_contractor);
            $("#project_manager").val(cuInfo.project_manager);
            $("#dealer_code").val(cuInfo.dealer_code);
            $("#account_type").val(cuInfo.account_type);
            $("#org_name").val(cuInfo.org_name);
            $("#addr1").val(cuInfo.addr_1);
            $("#addr2").val(cuInfo.addr_2);
            $("#city").val(cuInfo.city);
            $("#state").val(cuInfo.state);
            $("#zip").val(cuInfo.zip);
            $("#pri_phone").val(cuInfo.pri_phone);
            $("#alt_phone1").val(cuInfo.alt_phone_1);
            $("#alt_phone2").val(cuInfo.alt_phone_2);
            $("#pri_email").val(cuInfo.pri_email);
            $("#alt_email").val(cuInfo.alt_email);
            $("#record_id").val(cuInfo.id);

            $("#edit_so_info").show();
        });
    }

    $.post('/ondemand/shopfloor/new_customer.php', function(data) {
        $("#modalAddCustomer").html(data);
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
        .on("click", "#update_customer", function() {
            var newCuInfo = $("#edit_so").serialize();

            $.post("/ondemand/customer.php?action=update_customer", newCuInfo, function(data) {
                if(data === 'success') {
                    $("#edit_so_info").hide();

                    displayToast("success", "Updated customer information successfully.", "Customer Updated");
                } else {
                    $("body").append(data);
                }
            });
        })
        .on("click", "#btn_add_acct", function() {
            $.post('/ondemand/shopfloor/new_customer.php', function(data) {
                $("#modalAddCustomer").html(data);
            }).done(function() {
                $("#modalAddCustomer").modal('show');

                $("#contractor_code").autocomplete({ // TODO: Why won't this work? JS DOM element timing mismatch?
                    source: "/ondemand/livesearch/general.php?search=dealerid"
                });
            });
        })
        .on("change", "input[name='cu_type']", function() {
            switch($(this).val()) {
                case 'retail':
                    $("#add_retail_customer").show();
                    $("#add_distributor").hide();

                    break;
                case 'distribution':
                    $("#add_retail_customer").hide();
                    $("#add_distributor").show();

                    break;
                case 'cutting':
                    break;
                default:
                    break;
            }
        });

    $("#cu_sales_order_num1")
        .on("keyup", function() { // this is on keyboard change
            updateSearchTable($(this).val(), "sonum", "displaySO");
            $("#edit_so_info").hide();

            $("#cu_project_name1").val("");
            $("#cu_dealer_contractor1").val("");
            $("#cu_project_manager1").val("");
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

            $("#cu_sales_order_num1").val("");
            $("#cu_dealer_contractor1").val("");
            $("#cu_project_manager1").val("");
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

            $("#cu_sales_order_num1").val("");
            $("#cu_project_name1").val("");
            $("#cu_project_manager1").val("");
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

            $("#cu_sales_order_num1").val("");
            $("#cu_project_name1").val("");
            $("#cu_dealer_contractor1").val("");
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