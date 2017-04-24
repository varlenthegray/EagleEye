<?php
require '../includes/header_start.php';

$_SESSION['shop_active'] ? null : header('Location: /shopfloor/login.php');

require '../includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<div class="row" id="next_section">
    <div class="col-md-6" id="main_window">
        <div class="card-box" style="min-height: 511px;">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="pull-left">Active Jobs for <span id="shop_employee_name"><?php echo $_SESSION['shop_user']['name']; ?></span></h4><h4 id="date-time" class="pull-right"></h4>

                    <table class="tablesaw table">
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-priority="persist">Job ID</th>
                            <th scope="col">Operation</th>
                            <th scope="col">Started</th>
                        </tr>
                        </thead>
                        <tbody id="active_jobs_table">
                        <?php
                        $qry = $dbconn->query("SELECT * FROM jobs WHERE active AND assigned_to = {$_SESSION['shop_user']['id']}");

                        if($qry->num_rows > 0) {
                            while($result = $qry->fetch_assoc()) {
                                echo "<tr class='cursor-hand update-active-job' id='job_id_{$result['id']}' data-id='{$result['id']}'>";
                                echo "  <td>{$result['job_id']}</td>";
                                echo "  <td>{$result['operation']}</td>";

                                $startTime = date(DATE_TIME_DEFAULT, $result['started']);

                                echo "  <td id='startTime' data-toggle='tooltip' data-placement='top' title='$startTime'>
                            <span id='startTime{$result['id']}'></span>
                            <script>
                                $('#startTime{$result['id']}').html(moment({$result['started']} * 1000).fromNow());
                                
                                setInterval(function() {
                                    $('#startTime{$result['id']}').html(moment({$result['started']} * 1000).fromNow());
                                }, 1000);
                            </script>
                        </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr>";
                            echo "  <td colspan='4'>No active jobs</td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>

                    <h4 class="text-md-center">Queue</h4>

                    <table class="tablesaw table" id="queue_jobs_table">
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-priority="persist">Job ID</th>
                            <th scope="col">Operation</th>
                            <th scope="col">Part ID</th>
                        </tr>
                        </thead>
                        <tbody id="job_queue_table">
                        <?php
                        $qry = $dbconn->query("SELECT * FROM jobs WHERE NOT active AND assigned_to = {$_SESSION['shop_user']['id']} AND completed IS NULL");

                        if($qry->num_rows > 0) {
                            while($result = $qry->fetch_assoc()) {
                                echo "<tr class='cursor-hand queue-job-start' id='job_id_{$result['id']}' data-job-id='{$result['id']}'>";
                                echo "  <td>{$result['job_id']}</td>";
                                echo "  <td>{$result['operation']}</td>";
                                echo "  <td>{$result['part_id']}</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr>";
                            echo "  <td colspan='3'>No jobs in queue</td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- End left column -->

    <!-- Right column -->
    <div class="col-md-6">
        <div class="card-box" id="cal_email_tasks" style="min-height: 511px;">
            <!-- Loaded in by /ondemand/js/page_content_functions.js and /html/right_panel.php -->
        </div>
    </div>

    <!-- modal -->
    <div id="modalStartJob" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalStartJobLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="modalStartJobTitle">Start Job <span id="modalStartJobInnerTitle"></span> at <span id="start_job_time"></span>?</h4>
                </div>
                <div class="modal-body">
                    <p>
                        <span id="start_job_qty">Quantity to Complete: ?</span><br/>
                        <span id="start_job_operation">Operation: ???</span><br />
                        <span id="start_job_partid">Part ID: ???</span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="start_job" data-startid="?">Start Job</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

    <!-- modal -->
    <div id="modalUpdateJob" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalUpdateJob" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="modalUpdateJobHeader">Update ???</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <fieldset class="form-group">
                                <label for="qtyCompleted">Quantity Completed</label>
                                <input type="text" class="form-control" id="qtyCompleted" name="qtyComplete" placeholder="???"  data-toggle='tooltip' data-placement='top'>
                            </fieldset>

                            <fieldset class="form-group">
                                <input type="radio" name="completionCode" id="completion_code1" value="Complete">
                                <label for="completion_code1">Completed</label>
                                <br />
                                <input type="radio" name="completionCode" id="completion_code2" value="Partially Complete">
                                <label for="completion_code2">Partially Completed</label>
                                <br />
                                <input type="radio" name="completionCode" id="completion_code3" value="Rework">
                                <label for="completion_code3">Rework</label>
                            </fieldset>
                        </div>

                        <div class="col-md-9">
                            <fieldset class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" style="height: 107px" placeholder="Any notes related to the job?"></textarea>
                            </fieldset>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="save_job_update">Complete</button>
                </div>
            </div>
        </div>
    </div><!-- /.modal -->
</div>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Loading page content -->
<script src="/ondemand/js/page_content_functions.js"></script>

<script>
    var jobInterval;
    var jobInfo;

    function updateActiveJobs() {
        $.post("/ondemand/shopfloor/job_actions.php?action=display_active_jobs", function(data) { // grab the latest information
            $("#active_jobs_table").html(data); // display that information in the table
        });
    }

    function updateQueuedJobs() {
        $.post("/ondemand/shopfloor/job_actions.php?action=display_job_queue", function(data) { // grab the latest information
            $("#job_queue_table").html(data); // display that information in the table
        });
    }

    $("body")
        .on("click", ".queue-job-start", function() {
        $.post("/ondemand/shopfloor/job_actions.php?action=get_job_info", {jobID: $(this).data("job-id")}, function(data) {
            if(data !== '') {
                jobInfo = $.parseJSON(data);

                $("#modalStartJob").modal();
            }
        });
    })
        .on("click", "#start_job", function() {
            $.post("/ondemand/shopfloor/job_actions.php?action=update_start_job", {id: jobInfo.id}, function(data) {
                if(data === 'success') {
                    updateActiveJobs();
                    updateQueuedJobs();

                    displayToast("success", "Successfully started job.", "Job Started");

                    $("#modalStartJob").modal('hide');
                } else {
                    displayToast("error", "Unable to start job.", "Job Error");
                }
            });
    })
        .on("click", ".update-active-job", function() {
            $.post("/ondemand/shopfloor/job_actions.php?action=get_job_info", {jobID: $(this).data("id")}, function(data) {
                jobInfo = $.parseJSON(data);

                $("#modalUpdateJob").modal();
            })
    })
        .on("click", "#save_job_update", function() {
            if(!$("input[name='completionCode']").is(':checked')) {
                alert("Please select the completion code before marking this job complete.");
            } else {
                $.post("/ondemand/shopfloor/job_actions.php?action=update_active_job", {jobID: jobInfo.id, notes: $("#notes").val(), qty: $("#qtyCompleted").val(), status: $("input[name='completionCode']:checked").val()}, function(data) {
                    if(data === 'success' ) {
                        displayToast("success", "Job has been closed.", "Job closed");
                        $("#modalUpdateJob").modal('hide');

                        updateActiveJobs();
                    } else {
                        displayToast("error", "Unable to close the job", "Job error");
                    }
                });
            }
        });

    $("#modalStartJob").on("show.bs.modal", function() {
        $("#start_job_time").html(moment().format("LT"));

        $('#modalStartJobInnerTitle').html(jobInfo.job_id);
        $("#start_job_qty").html("Quantity to complete: <b>" + jobInfo.qty_requested + "</b>");
        $("#start_job_operation").html("Operation: <b>" + jobInfo.operation + "</b>");
        $("#start_job_partid").html("Job ID: <b>" + jobInfo.part_id + "</b>");

        $("#start_job").attr("data-startid", jobInfo.id);

        jobInterval = setInterval(function() {
            $("#start_job_time").html(moment().format("LT"));
        }, 1000);
    }).on("hidden.bs.modal", function() {
        clearInterval(jobInterval);
    });

    $("#modalUpdateJob").on("show.bs.modal", function() {
        $("#modalUpdateJobHeader").html("Update job " + jobInfo.job_id + " <i>(" + jobInfo.operation + ")</i>");
        $("#qtyCompleted").val(jobInfo.qty_requested).attr("data-original-title", "Requested qty: " + jobInfo.qty_requested);
        $("input[name='completionCode']").prop("checked", false);
        $("#notes").val("");
    });

    $("#date-time").html(moment().format("LLLL"));

    setInterval(function() {
        $("#date-time").html(moment().format("LLLL"));
    }, 1000);

    loadCalendarPane();

    $("#shop_logout_link").on("click", function() {
        $.post("/ondemand/shopfloor/login_actions.php?action=logout", function() {
            window.location.href = ("/shopfloor/login.php");
        });
    });

    setInterval(function() { // reatime(ish) updating of job information, perhaps migrate this to Node.js at some point
        updateQueuedJobs();
        updateActiveJobs();
    }, 10000)
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>