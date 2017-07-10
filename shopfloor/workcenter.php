<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- Date & Clock -->
<link href="/assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>

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
                            <th style="width: 15%;">SO#</th>
                            <th style="width: 20%;">Bracket</th>
                            <th style="width: 30%;">Operation</th>
                            <th style="width: 20%;">Individual</th>
                            <th style="width: 15%;">Started/Resumed</th>
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

<!-- Date & Clock picker -->
<script src="/assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<!-- Loading page content -->
<script src="/ondemand/js/page_content_functions.js"></script>

<script>
    $("body")
        .on("click", ".wc-edit-queue", function() {
            var id = $(this).attr("id");

            window.location.replace("/shopfloor/job_management.php?lookup=" + id);

            /*$.post("/ondemand/shopfloor/workcenter.php?action=view_job_in_queue", {id: id}, function(data) {
                $("#viewJobInfo").html(data).modal('show');
            }).fail(function() {
                $("body").append(data);
            });*/
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
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>