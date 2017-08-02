<?php
require '../includes/header_start.php';
?>

<!-- Date & Clock -->
<link href="/assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>

<div class="row">
    <div class="col-md-6">
        <div class="card-box table-responsive">
            <div class="col-md-12 workcenter-table">
                <h4>Jobs in Queue</h4>

                <table id="jobs_in_queue_global_table" class="table table-striped table-bordered" width="100%">
                    <thead>
                    <tr>
                        <th>SO#</th>
                        <th>Room Name</th>
                        <th>Department</th>
                        <th>Operation</th>
                        <th>Release Date</th>
                        <th>Assignee</th>
                    </tr>
                    </thead>
                    <tbody id="jiq_table"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive">
                        <h4>Active Jobs</h4>

                        <table id="active_jobs_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th>SO#</th>
                                <th>Room Name</th>
                                <th>Bracket</th>
                                <th>Operation</th>
                                <th>Individual</th>
                                <th>Started/Resumed</th>
                            </tr>
                            </thead>
                            <tbody id="active_jobs_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive">
                        <h4>Recently Completed Jobs</h4>

                        <table id="recently_completed_jobs_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th>SO#</th>
                                <th>Room Name</th>
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
        </div>
    </div>

    <!-- modal -->
    <div id="viewJobInfo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewJobInfoLabel" aria-hidden="true">

    </div>
    <!-- /.modal -->
</div>

<script>
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
        },
        "paging": false,
        scrollY: '68vh',
        scrollCollapse: true,
        "order": [[4, "asc"]],
        "dom": "tipr"
    });

    var active_table = $("#active_jobs_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/workcenter.php?action=display_active_jobs",
        "pageLength": 25,
        scrollY: '25.65vh',
        scrollCollapse: true,
        "dom": "tipr"
    });

    var completed_table = $("#recently_completed_jobs_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/workcenter.php?action=display_recently_completed",
        "pageLength": 25,
        "order": [[3, "desc"]],
        scrollY: '25.65vh',
        scrollCollapse: true,
        "dom": "tipr"
    });

    wc_auto_interval = setInterval(function() {
        jiq_table.ajax.reload(null,false);
        active_table.ajax.reload(null,false);
        completed_table.ajax.reload(null,false);
    }, 5000);
</script>