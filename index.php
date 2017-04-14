<?php
require 'includes/header_start.php';
require 'includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<!-- calendar-->
<link href="/assets/plugins/fullcalendar/dist/fullcalendar.min.css" rel="stylesheet" />

<script src="includes/js/searchTab.js"></script>

<script>
    var searchCounter = 2;
</script>

<div class="row">
    <div class="col-md-6" style="min-height: 240px;">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-block">
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
                                                    <button class="btn waves-effect btn-primary pull-right" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Add new customer"> <i class="zmdi zmdi-account-add"></i> </button>
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
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card" id="search_results_card" style="display: none;">
                        <div class="card-block" style="min-height: 294px;">
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
            </div>
        </div>

    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-block">
                <ul class="nav nav-tabs m-b-10" id="actionsTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="email-tab" data-toggle="tab" href="#email" role="tab" aria-controls="email" aria-expanded="true">Email</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="calendar-tab" data-toggle="tab" href="#calendar" role="tab" aria-controls="calendar">Calendar</a>
                    </li>
                </ul>
                <div class="tab-content" id="actionTabContent">
                    <div role="tabpanel" class="tab-pane fade in active" id="email" aria-labelledby="email-tab" style="height: 472px;">
                        This is your email.
                    </div>
                    <div class="tab-pane fade" id="calendar" role="tabpanel" aria-labelledby="calendar-tab" style="height: 472px;">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-box">
                                    <div id="calendar_display"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-block">
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
                            <tbody>
                            <tr>
                                <td>A-Kitchen</td>
                                <td class="job-color-green">260-Sample Approved</td>
                                <td class="job-color-green">520-Bore, Dado, Pocket Hole</td>
                                <td class="job-color-yellow">430-Door Pick Up</td>
                                <td class="job-color-orange">610-Custom</td>
                            </tr>
                            <tr>
                                <td>B-Kitchen Island</td>
                                <td class="job-color-red">260-Sample Approved</td>
                                <td class="job-color-red">510-Cut Plywood Panels to Size</td>
                                <td class="job-color-yellow">427-Door Order</td>
                                <td class="job-color-green">610-Custom</td>
                            </tr>
                            <tr>
                                <td>C-Pantry</td>
                                <td class="job-color-green">260-Sample Approved</td>
                                <td class="job-color-green">503-Pick List for Box</td>
                                <td class="job-color-green">N/A</td>
                                <td class="job-color-orange">605-Pick List for Custom</td>
                            </tr>
                            <tr>
                                <td>D-Fireplace</td>
                                <td class="job-color-yellow">260-Sample Approved</td>
                                <td class="job-color-green">503-Pick List for Box</td>
                                <td class="job-color-green">N/A</td>
                                <td class="job-color-green">N/A</td>
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
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-block">
                <table class="tablesaw table m-b-0" data-tablesaw-mode="swipe" data-tablesaw-sortable data-tablesaw-minimap>
                    <thead>
                    <tr>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="1">Sales</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Design</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Design/Engineering</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Distribution</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Acknowledgement</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Engineering</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Box</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Finishing</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Assembly</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Shipping</th>
                        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Delivery</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>165-Sample Door Approval</td>
                        <td>315-Engineering</td>
                        <td>315.1-Signed & Completed Ack.</td>
                        <td>N/A</td>
                        <td>315.1-Signed & Completed Ack.</td>
                        <td>315.1-Signed & Completed Ack.</td>
                        <td>503-Pick List for Box</td>
                        <td>540-Finishing</td>
                        <td>560-Assembly</td>
                        <td>580-Load All Parts</td>
                        <td>590-Delivery</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Calendar -->
<script src="/assets/plugins/moment/moment.js"></script>
<script src="/assets/plugins/fullcalendar/dist/fullcalendar.min.js"></script>

<!-- Chart -->
<script src="/assets/plugins/amcharts/amcharts.js"></script>
<script src="/assets/plugins/amcharts/serial.js"></script>
<script src="/assets/plugins/amcharts/gantt.js"></script>
<script src="/assets/plugins/amcharts/themes/custom.js"></script>

<script>
    var cu_sales_num_1_field = $("#cu_sales_order_num1");
    var cu_project_name_1_field = $("#cu_project_name1");

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

    $("#search_accordion1").accordion();

    $("#search_add_tab").on("click", function() {
        searchCounter = generateTab(searchCounter);
    });

    $("body").on("click", "[id^=searchTab]", function(e) { // this allows for the automation of search tabs
        var accordion = "search_accordion" + e.target.getAttribute("searchid"); // add more accordions

        setTimeout(function() {
            $("#" + accordion).accordion("refresh"); // refresh the accordion on click of tab
        }, 200);
    });

    cu_sales_num_1_field.on("keyup", function() { // this is on keyboard change
        updateSearchTable(cu_sales_num_1_field.val(), "sonum");
    });

    cu_project_name_1_field.on("keyup", function () { // this is on keyboard change
        updateSearchTable(cu_project_name_1_field.val(), "project");
    });

    cu_sales_num_1_field.autocomplete({
        source: "/ondemand/livesearch/general.php?search=cusonum"
    }).on("autocompleteselect", function(e, ui) {
        updateSearchTable(ui.item.label, "sonum"); // this is on click of the auto-complete
    });

    cu_project_name_1_field.autocomplete({
        source: "/ondemand/livesearch/general.php?search=cuproject"
    }).on("autocompleteselect", function(e, ui) {
        updateSearchTable(ui.item.label, "project"); // this is on click of the auto-complete
    });

    $("#cu_dealer_contractor1").autocomplete({
        source: "/ondemand/livesearch/general.php?search=cucontractor"
    });

    $("#cu_project_manager1").autocomplete({
        source: "/ondemand/livesearch/general.php?search=cupm"
    });

    $("#calendar-tab").on("shown.bs.tab", function() {
        $("#calendar_display").fullCalendar({
            aspectRatio: 1.8,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'listDay,listWeek,month'
            },

            // customize the button names,
            // otherwise they'd all just say "list"
            views: {
                listDay: { buttonText: 'list day' },
                listWeek: { buttonText: 'list week' }
            },

            defaultView: 'listWeek',
            defaultDate: '2017-04-12',
            navLinks: true, // can click day/week names to navigate views
            editable: true,
            eventLimit: true, // allow "more" link when too many events
            events: [
                {
                    title: 'All Day Event',
                    start: '2017-04-01'
                },
                {
                    title: 'Long Event',
                    start: '2017-04-07',
                    end: '2017-04-10'
                },
                {
                    id: 999,
                    title: 'Repeating Event',
                    start: '2017-04-09T16:00:00'
                },
                {
                    id: 999,
                    title: 'Repeating Event',
                    start: '2017-04-16T16:00:00'
                },
                {
                    title: 'Conference',
                    start: '2017-04-11',
                    end: '2017-04-13'
                },
                {
                    title: 'Meeting',
                    start: '2017-04-12T10:30:00',
                    end: '2017-04-12T12:30:00'
                },
                {
                    title: 'Lunch',
                    start: '2017-04-12T12:00:00'
                },
                {
                    title: 'Meeting',
                    start: '2017-04-12T14:30:00'
                },
                {
                    title: 'Happy Hour',
                    start: '2017-04-12T17:30:00'
                },
                {
                    title: 'Dinner',
                    start: '2017-04-12T20:00:00'
                },
                {
                    title: 'Birthday Party',
                    start: '2017-04-13T07:00:00'
                },
                {
                    title: 'Click for Google',
                    url: 'http://google.com/',
                    start: '2017-04-28'
                }
            ]
        });
    });
</script>

<?php 
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>