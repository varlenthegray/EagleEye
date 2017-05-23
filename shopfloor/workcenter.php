<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<!-- Date & Clock -->
<link href="/assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>

<div class="row">
    <div class="col-md-4">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12 workcenter-table">
                    <h4>Jobs in Queue</h4>

                     <table class="tablesaw table m-b-0 tablesaw-sortable" data-tablesaw-mode="swipe" data-tablesaw-sortable data-tablesaw-minimap id="jobs_in_queue_mt">
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="persist">SO ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Department</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Operation</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Release Date</th>
                        </tr>
                        </thead>
                        <tbody id="jiq_table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h4>Active Jobs</h4>

                    <table class="tablesaw table m-b-0" data-tablesaw-mode="swipe" data-tablesaw-sortable data-tablesaw-minimap>
                        <thead>
                        <tr>
                            <th style="width: 10%;" scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="persist">SO ID</th>
                            <th style="width: 20%;" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Department</th>
                            <th style="width: 30%;" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Operation</th>
                            <th style="width: 20%;" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Individual</th>
                            <th style="width: 20%;" scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Started</th>
                        </tr>
                        </thead>
                        <tbody id="active_jobs_table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h4>Recently Completed Jobs</h4>

                    <table class="tablesaw table m-b-0" data-tablesaw-mode="swipe" data-tablesaw-sortable data-tablesaw-minimap>
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-sortable-default-col data-tablesaw-priority="persist">SO ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="1">Department</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Operation</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Completed</th>
                        </tr>
                        </thead>
                        <tbody id="recently_completed_table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- modal -->
    <div id="viewJobInfo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewJobInfoLabel" aria-hidden="true">

    </div>
    <!-- /.modal -->
</div>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Date & Clock picker -->
<script src="/assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>


<!-- Loading page content -->
<script src="/ondemand/js/page_content_functions.js"></script>

<script>
    function updateJIQ() {
        $.post("/ondemand/shopfloor/workcenter.php?action=display_jiq", function(data) {
            $("#jiq_table").html(data);
        });
    }

    function updateRecentlyCompleted() {
        $.post("/ondemand/shopfloor/workcenter.php?action=display_recently_completed", function(data) {
            $("#recently_completed_table").html(data);
        });
    }

    function updateActiveJobs() {
        $.post("/ondemand/shopfloor/workcenter.php?action=display_active_jobs", function(data) {
            $("#active_jobs_table").html(data);
        });
    }

    updateJIQ();
    updateRecentlyCompleted();
    updateActiveJobs();

    $("body")
        .on("click", ".wc-edit-queue", function() {
            var id = $(this).data("op-id");

            $.post("/ondemand/shopfloor/workcenter.php?action=view_job_in_queue", {id: id}, function(data) {
                $("#viewJobInfo").html(data).modal('show');
            }).fail(function() {
                $("body").append(data);
            });
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

            updateJIQ();
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

    setInterval(function() {
        updateJIQ();
        updateRecentlyCompleted();
        updateActiveJobs();
    }, 10000)
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>