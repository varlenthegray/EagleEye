<?php
require 'includes/header_start.php';
require 'includes/header_end.php';

/** TODO: Implement search results based on search */
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<!-- Date & Clock -->
<link href="/assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
        <ul class="nav nav-tabs m-b-10" id="mainTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="production-tab" data-toggle="tab" href="#production" role="tab" aria-controls="production" aria-expanded="true">Production</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="calendar-tab" data-toggle="tab" href="#calendar" role="tab" aria-controls="calendar">Calendar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="email-tab" data-toggle="tab" href="#email" role="tab" aria-controls="email">Email</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="text-tab" data-toggle="tab" href="#text" role="tab" aria-controls="text">Text</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="workcenter-tab" data-toggle="tab" href="#workcenter" role="tab" aria-controls="workcenter">Workcenter</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="wall-tab" data-toggle="tab" href="#wall" role="tab" aria-controls="wall">Wall</a>
            </li>
        </ul>
        <div class="tab-content" id="mainTabContent">
            <div role="tabpanel" class="tab-pane fade in active" id="production" aria-labelledby="home-tab">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card-box table-responsive">
                                <div class="col-md-12 workcenter-table">
                                    <h4>Jobs in Queue</h4>

                                    <table id="jobs_in_queue_global_table" class="table table-striped table-bordered" width="100%">
                                        <thead>
                                        <tr>
                                            <th>SO#</th>
                                            <th>Bracket</th>
                                            <th>Operation</th>
                                            <th>Release Date</th>
                                        </tr>
                                        </thead>
                                        <tbody id="jiq_table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card-box table-responsive">
                                <div class="col-md-12">
                                    <h4>Active Jobs</h4>

                                    <table id="active_jobs_global_table" class="table table-striped table-bordered" width="100%">
                                        <thead>
                                        <tr>
                                            <th style="width: 10%;">SO#</th>
                                            <th style="width: 20%;">Bracket</th>
                                            <th style="width: 30%;">Operation</th>
                                            <th style="width: 20%;">Individual</th>
                                            <th style="width: 20%;">Started/Resumed</th>
                                        </tr>
                                        </thead>
                                        <tbody id="active_jobs_table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card-box table-responsive">
                                <div class="col-md-12">
                                    <h4>Recently Completed Jobs</h4>

                                    <table id="recently_completed_jobs_global_table" class="table table-striped table-bordered" width="100%">
                                        <thead>
                                        <tr>
                                            <th>SO#</th>
                                            <th>Bracket</th>
                                            <th>Operation</th>
                                            <th>Completed</th>
                                        </tr>
                                        </thead>
                                        <tbody id="recently_completed_table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- modal -->
                        <div id="viewJobInfo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewJobInfoLabel" aria-hidden="true">

                        </div>
                        <!-- /.modal -->
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="calendar" role="tabpanel" aria-labelledby="profile-tab">
                <p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla
                    single-origin coffee squid. Exercitation +1 labore velit, blog sartorial
                    PBR leggings next level wes anderson artisan four loko farm-to-table
                    craft beer twee. Qui photo booth letterpress, commodo enim craft beer
                    mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                    organic, assumenda labore aesthetic magna delectus mollit. Keytar
                    helvetica VHS salvia yr, vero magna velit sapiente labore stumptown.
                    Vegan fanny pack odio cillum wes anderson 8-bit, sustainable jean shorts
                    beard ut DIY ethical culpa terry richardson biodiesel. Art party
                    scenester stumptown, tumblr butcher vero sint qui sapiente accusamus
                    tattooed echo park.</p>
            </div>
            <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                <p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out
                    mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade.
                    Messenger bag gentrify pitchfork tattooed craft beer, iphone skateboard
                    locavore carles etsy salvia banksy hoodie helvetica. DIY synth PBR
                    banksy irony. Leggings gentrify squid 8-bit cred pitchfork. Williamsburg
                    banh mi whatever gluten-free, carles pitchfork biodiesel fixie etsy
                    retro mlkshk vice blog. Scenester cred you probably haven't heard of
                    them, vinyl craft beer blog stumptown. Pitchfork sustainable tofu synth
                    chambray yr.</p>
            </div>
            <div class="tab-pane fade" id="text" role="tabpanel" aria-labelledby="text-tab">
                <p>Trust fund seitan letterpress, keytar raw denim keffiyeh etsy art party
                    before they sold out master cleanse gluten-free squid scenester freegan
                    cosby sweater. Fanny pack portland seitan DIY, art party locavore wolf
                    cliche high life echo park Austin. Cred vinyl keffiyeh DIY salvia PBR,
                    banh mi before they sold out farm-to-table VHS viral locavore cosby
                    sweater. Lomo wolf viral, mustache readymade thundercats keffiyeh craft
                    beer marfa ethical. Wolf salvia freegan, sartorial keffiyeh echo park
                    vegan.</p>
            </div>
            <div class="tab-pane fade" id="workcenter" role="tabpanel" aria-labelledby="workcenter-tab">
                <p>Workcenter tab.</p>
            </div>
            <div class="tab-pane fade" id="wall" role="tabpanel" aria-labelledby="wall-tab">
                <p>Wall tab.</p>
            </div>
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

<!-- Date & Clock picker -->
<script src="/assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<script>
    $("body")
        .on("click", ".wc-edit-queue", function() {
            /*
            var id = $(this).attr("id");

            window.location.replace("/shopfloor/job_management.php?lookup=" + id);*/

            window.location.replace("")
        })
        .on("click", "#wc-jiq-update", function() {
            if(!$("#published").is(":checked")) {
                var id = $(this).data("id");

                $.post("/ondemand/shopfloor/workcenter.php?action=update_queued_job", {id: id}, function(data) {
                    $('body').append(data);
                });
            } else {
                displayToast("info", "Nothing to change.", "No changes");
            }

            $("#viewJobInfo").modal('hide');

            jiq_table.ajax.reload(null,false);
        });

    $("#job_started").datetimepicker({
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    });

    $("#job_completed").datetimepicker({
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    });

    var jiq_table = $("#jobs_in_queue_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/workcenter.php?action=display_jiq",
        "pageLength": 25,
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand wc-edit-queue");
        }
    });

    var active_table = $("#active_jobs_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/workcenter.php?action=display_active_jobs",
        "pageLength": 25
    });

    var completed_table = $("#recently_completed_jobs_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/workcenter.php?action=display_recently_completed",
        "pageLength": 25
    });

    setInterval(function() {
        jiq_table.ajax.reload(null,false);
        active_table.ajax.reload(null,false);
        completed_table.ajax.reload(null,false);
    }, 5000);

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