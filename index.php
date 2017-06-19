<?php
require 'includes/header_start.php';
require 'includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<script src="includes/js/searchTab.js"></script>

<script>
    var searchCounter = 2;
</script>

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
                                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="persist">SO#</th>
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
    <div class="col-md-8">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <table class="tablesaw table" data-tablesaw-mode="swipe" data-tablesaw-sortable data-tablesaw-minimap>
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="persist">Room</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Sample</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Main</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Door/Drawer</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Customs</th>
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
                <div class="col-md-12 text-md-right">
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Collapse</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Print</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Export</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="gantt_chart_row" style="display: none;">
    <div class="col-md-12">
        <div class="card-box">
            <div id="job_status_gantt" style="height: 200px;"></div>
        </div>
    </div>
</div>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Chart -->
<script src="/assets/plugins/amcharts/amcharts.js"></script>
<script src="/assets/plugins/amcharts/serial.js"></script>
<script src="/assets/plugins/amcharts/gantt.js"></script>
<script src="/assets/plugins/amcharts/themes/custom.js"></script>
<script src="/assets/plugins/amcharts/plugins/dataloader/dataloader.min.js" type="text/javascript"></script>

<!-- Loading page content -->
<script src="/ondemand/js/page_content_functions.js"></script>

<script>
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
        $.post("/ondemand/livesearch/search_results.php?search=room", {find: sonum}, function(data) {
            $("#room_search_table").html(data);
            $("#room_results_row").show();
        });
    }

    function displayRoomInfo(room_id) {
        $("#gantt_chart_row").show();
    }

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

    var ganttChart = AmCharts.makeChart( "job_status_gantt", {
        "type": "gantt",
        "theme": "custom",
        "marginRight": 20,
        "marginTop": 10,
        "marginBottom": 10,
        "period": "mm",
        "dataDateFormat":"YYYY-MM-DD",
        "balloonDateFormat": "JJ:NN",
        "columnWidth": 0.5,
        "valueAxis": {
            "type": "date"
        },
        "brightnessStep": 10,
        "graph": {
            "fillAlphas": 1,
            "balloonText": "<b>[[task]]</b>: [[open]]<br /> <small style='text-align: center;'>([[duration]] <i>mins</i>)</small>",
            "cornerRadiusTop": 10,
            "labelText": "[[task]]",
            "labelPosition": "middle"
        },
        "rotate": true,
        "categoryField": "category",
        "segmentsField": "segments",
        "colorField": "color",
        "startDate": "2016-04-19",
        "startField": "start",
        "endField": "end",
        "durationField": "duration",
        "dataLoader": {
            "url": "/ondemand/gantt_chart.php"
        },
        "valueScrollbar": {
            "autoGridCount":true,
            "color": "#132882"
        },
        "chartCursor": {
            "cursorColor":"#55bb76",
            "valueBalloonsEnabled": false,
            "cursorAlpha": 0,
            "valueLineAlpha":0.5,
            "valueLineBalloonEnabled": true,
            "valueLineEnabled": true,
            "zoomable":false,
            "valueZoomable":true
        },
        "export": {
            "enabled": true
        },
        "balloon": {
            "fixedPosition": false
        }
    } );
</script>

<?php 
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>