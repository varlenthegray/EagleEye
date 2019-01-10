<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card-box table-responsive">
            <div class="col-md-12 workcenter-table">
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
    currentPage = 'workcenter';

    var jiq_table = $("#jobs_in_queue_global_table").DataTable({
        "ajax": "/ondemand/display_actions.php?action=display_full_job_queue",
        "pageLength": 25,
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand wc-view-queue-so");
        },
        "paging": false,
        scrollY: '68vh',
        scrollCollapse: true,
        "order": [[4, "asc"]],
        "dom": '<"#jiq_header.dt-custom-header"><?php echo ((bool)$_SESSION['userInfo']['pref_filters']) ? "ftipr" : "tipr"; ?>',
        "oLanguage": {
            "sSearch": "Filter: "
        }
    });

    var active_table = $("#active_jobs_global_table").DataTable({
        "ajax": "/ondemand/display_actions.php?action=display_full_active_jobs",
        "pageLength": 25,
        scrollY: '25.65vh',
        scrollCollapse: true,
        "dom": '<"#active_header.dt-custom-header"><?php echo ((bool)$_SESSION['userInfo']['pref_filters']) ? "ftipr" : "tipr"; ?>',
        "oLanguage": {
            "sSearch": "Filter: "
        }
    });

    var completed_table = $("#recently_completed_jobs_global_table").DataTable({
        "ajax": "/ondemand/display_actions.php?action=display_full_recently_completed",
        "pageLength": 25,
        "order": [[4, "desc"]],
        scrollY: '25.65vh',
        scrollCollapse: true,
        "dom": '<"#completed_header.dt-custom-header"><?php echo ((bool)$_SESSION['userInfo']['pref_filters']) ? "ftipr" : "tipr"; ?>',
        "oLanguage": {
            "sSearch": "Filter: "
        }
    });


    $("div.dataTables_filter input").addClass("ignoreSaveAlert");

    $("#jiq_header").html("<h4>Ops in Queue</h4>");
    $("#active_header").html("<h4>Active Ops</h4>");
    $("#completed_header").html("<h4>Completed Ops</h4>");
</script>