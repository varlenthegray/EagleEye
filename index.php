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



<div class="row" id="room_results_row" style="display: none;">
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
</div>

<div class="row" id="gantt_chart_row" style="display: none;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-block">
                <div id="job_status_gantt"></div>
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

    function displaySO(sonum) {
        $.post("/ondemand/livesearch/search_results.php?search=room", {find: sonum}, function(data) {
            $("#room_search_table").html(data);
            $("#room_results_row").show();
        });
    }

    function displayGantt(room_id) {
        $("#gantt_chart_row").show();
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

    AmCharts.makeChart( "job_status_gantt", {
        "type": "gantt",
        "theme": "custom",
        "marginRight": 20,
        "marginTop": 10,
        "marginBottom": 10,
        "period": "hh",
        "dataDateFormat":"YYYY-MM-DD",
        "balloonDateFormat": "JJ:NN",
        "columnWidth": 0.5,
        "valueAxis": {
            "type": "date"
        },
        "brightnessStep": 10,
        "graph": {
            "fillAlphas": 1,
            "balloonText": "<b>[[task]]</b>: [[open]] [[value]]"
        },
        "rotate": true,
        "categoryField": "category",
        "segmentsField": "segments",
        "colorField": "color",
        "startDate": "2015-01-01",
        "startField": "start",
        "endField": "end",
        "durationField": "duration",
        "dataProvider": [ {
            "category": "John",
            "segments": [ {
                "start": 7,
                "duration": 2,
                "color": "#46615e",
                "task": "Task #1"
            }, {
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 2,
                "color": "#8dc49f",
                "task": "Task #3"
            } ]
        }, {
            "category": "Smith",
            "segments": [ {
                "start": 10,
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 1,
                "color": "#8dc49f",
                "task": "Task #3"
            }, {
                "duration": 4,
                "color": "#46615e",
                "task": "Task #1"
            } ]
        }, {
            "category": "Ben",
            "segments": [ {
                "start": 12,
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "start": 16,
                "duration": 2,
                "color": "#FFE4C4",
                "task": "Task #4"
            } ]
        }, {
            "category": "Mike",
            "segments": [ {
                "start": 9,
                "duration": 6,
                "color": "#46615e",
                "task": "Task #1"
            }, {
                "duration": 4,
                "color": "#727d6f",
                "task": "Task #2"
            } ]
        }, {
            "category": "Lenny",
            "segments": [ {
                "start": 8,
                "duration": 1,
                "color": "#8dc49f",
                "task": "Task #3"
            }, {
                "duration": 4,
                "color": "#46615e",
                "task": "Task #1"
            } ]
        }, {
            "category": "Scott",
            "segments": [ {
                "start": 15,
                "duration": 3,
                "color": "#727d6f",
                "task": "Task #2"
            } ]
        }, {
            "category": "Julia",
            "segments": [ {
                "start": 9,
                "duration": 2,
                "color": "#46615e",
                "task": "Task #1"
            }, {
                "duration": 1,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 8,
                "color": "#8dc49f",
                "task": "Task #3"
            } ]
        }, {
            "category": "Bob",
            "segments": [ {
                "start": 9,
                "duration": 8,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 7,
                "color": "#8dc49f",
                "task": "Task #3"
            } ]
        }, {
            "category": "Kendra",
            "segments": [ {
                "start": 11,
                "duration": 8,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "start": 16,
                "duration": 2,
                "color": "#FFE4C4",
                "task": "Task #4"
            } ]
        }, {
            "category": "Tom",
            "segments": [ {
                "start": 9,
                "duration": 4,
                "color": "#46615e",
                "task": "Task #1"
            }, {
                "duration": 3,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 5,
                "color": "#8dc49f",
                "task": "Task #3"
            } ]
        }, {
            "category": "Kyle",
            "segments": [ {
                "start": 6,
                "duration": 3,
                "color": "#727d6f",
                "task": "Task #2"
            } ]
        }, {
            "category": "Anita",
            "segments": [ {
                "start": 12,
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "start": 16,
                "duration": 2,
                "color": "#FFE4C4",
                "task": "Task #4"
            } ]
        }, {
            "category": "Jack",
            "segments": [ {
                "start": 8,
                "duration": 10,
                "color": "#46615e",
                "task": "Task #1"
            }, {
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            } ]
        }, {
            "category": "Kim",
            "segments": [ {
                "start": 12,
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 3,
                "color": "#8dc49f",
                "task": "Task #3"
            } ]
        }, {
            "category": "Aaron",
            "segments": [ {
                "start": 18,
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 2,
                "color": "#FFE4C4",
                "task": "Task #4"
            } ]
        }, {
            "category": "Alan",
            "segments": [ {
                "start": 17,
                "duration": 2,
                "color": "#46615e",
                "task": "Task #1"
            }, {
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 2,
                "color": "#8dc49f",
                "task": "Task #3"
            } ]
        }, {
            "category": "Ruth",
            "segments": [ {
                "start": 13,
                "duration": 2,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "duration": 1,
                "color": "#8dc49f",
                "task": "Task #3"
            }, {
                "duration": 4,
                "color": "#46615e",
                "task": "Task #1"
            } ]
        }, {
            "category": "Simon",
            "segments": [ {
                "start": 10,
                "duration": 3,
                "color": "#727d6f",
                "task": "Task #2"
            }, {
                "start": 17,
                "duration": 4,
                "color": "#FFE4C4",
                "task": "Task #4"
            } ]
        } ],
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
        }
    } );
</script>

<?php 
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>