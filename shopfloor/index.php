<?php
require '../includes/header_start.php';
require '../ondemand/shopfloor/job_functions.php';

$_SESSION['shop_active'] ? null : header('Location: /shopfloor/login.php');

require '../includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<?php
if(!empty($_GET['action']))
    if($_GET['action'] === 'cltrue') {
        echo displayToast("success", "You have been clocked in for the day.", "Clocked In");
    }
?>

<div class="row" id="next_section">
    <div class="col-md-6" id="main_window">
        <div class="card-box" style="min-height: 511px;">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="pull-left">Active Operations for <span id="shop_employee_name"><?php echo $_SESSION['shop_user']['name']; ?></span></h4><h4 id="date-time" class="pull-right"></h4>

                    <table class="tablesaw table">
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-priority="persist">Sales Order ID</th>
                            <th scope="col">Department</th>
                            <th scope="col">Operation</th>
                            <th scope="col">Release Date</th>
                            <th scope="col">Operation Time</th>
                        </tr>
                        </thead>
                        <tbody id="active_jobs_table">
                        <?php
                        activeJobGeneration();
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6"><h4>Queue</h4></div>

                <div class="col-md-6 text-md-right">
                    <!--<label for="viewing_queue">Viewing:</label>
                    <select name="viewing_queue" id="viewing_queue">
                        <?php
                            $qry = $dbconn->query("SELECT department FROM user WHERE id = '{$_SESSION['shop_user']['id']}'");
                            $dpt_result = $qry->fetch_assoc();

                            $deptartments = json_decode($dpt_result['department']);

                            foreach($deptartments as $department) {
                                echo "<option value='$department'>$department</option>";
                            }
                        ?>
                    </select>-->
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="tablesaw table" id="queue_jobs_table">
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-priority="persist">Sales Order ID</th>
                            <th scope="col">Department</th>
                            <th scope="col">Operation</th>
                            <th scope="col">Release Date</th>
                            <th scope="col">Operation Time</th>
                        </tr>
                        </thead>
                        <tbody id="job_queue_table">
                        <?php
                        queuedJobGeneration();
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-md-right">
                    <button type="button" id="clock_out" class="btn btn-primary waves-effect waves-light w-xs">Clock Out</button>
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
                    <h4 class="modal-title" id="modalStartJobTitle">Start operation <span id="modalStartJobInnerTitle"></span> at <span id="start_job_time"></span>?</h4>
                </div>
                <div class="modal-body">
                    <p>
                        <span id="start_job_originally_started">Originally Started: ?</span>
                    </p>

                    <p>
                        <!--<span id="start_job_qty">Quantity to Complete: ?</span><br/>-->
                        <span id="start_job_operation">Operation: ???</span><br />
                        <span id="start_job_partid">Part ID: ???</span><br />
                        <span id="start_job_status">Status: ???</span><br />
                        <span id="start_job_notes">Notes: ???</span>
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
    var activeJobID;
    var queueJobID;

    function updateActiveJobs() {
        $.post("/ondemand/shopfloor/job_actions.php?action=display_active_jobs", function(data) { // grab the latest information
            $("#active_jobs_table").html(data); // display that information in the table
        });
    }

    function updateQueuedJobs(filter) {
        var filterLine = '';

        if(filter !== undefined) {
            filterLine = "&filter=" + filter;
        }

        $.post("/ondemand/shopfloor/job_actions.php?action=display_job_queue" + filterLine, function(data) { // grab the latest information
            if(data !== '')
                $("#job_queue_table").html(data); // display that information in the table
            else
                $("#job_queue_table").html("<tr><td colspan='4'>No jobs in queue</td></tr>");
        });
    }

    $("#clock_out").on("click", function() {
        $.post("/ondemand/shopfloor/login_actions.php?action=clock_out", function(data) {
            if(data === 'success') {
                displayToast("success", "Successfully clocked out.", "Clocked Out");

                setTimeout(function() {
                    window.location.href = "/shopfloor/login.php";
                }, 500);
            } else
                $("body").append(data);
        });
    });

    $("body")
        .on("click", ".queue-op-start", function() {
            var opClicked = $(this);
            queueJobID = $(this).data("op-id");

            $.post("/ondemand/shopfloor/job_actions.php?action=get_op_info", {opID: queueJobID}, function(data) {
                if(data !== '') {
                    var finalNotes;
                    var jobInfo = JSON.parse(data);
                    var opInfo = opClicked.data("op-info");
                    var longOpID = opClicked.data("long-op-id");
                    var longPartID = opClicked.data("long-part-id");

                    if(jobInfo.department === 'Admin') {
                        var opTitle = jobInfo.op_id + "-" + jobInfo.job_title;

                        $("#start_job_time").html(moment().format("LT"));
                        $('#modalStartJobInnerTitle').html(opTitle);
                        $("#start_job_status").html("Admin Operation");
                        $("#start_job_originally_started").html("");
                        $("#start_job_operation").html("Operation: <b>" + opTitle + "</b>");
                        $("#start_job_partid").html("Part ID: <b>" + opTitle + "</b>");

                        $("#start_job_notes").html("This is a task unrelated to a job specifically.");

                        $("#modalStartJob").modal();
                    } else {
                        $("#start_job_time").html(moment().format("LT"));

                        if(jobInfo.start_time !== null) {
                            var rework;

                            if(jobInfo.rework === 1) {
                                rework = "<b>Yes</b>";
                            } else
                                rework = "No";

                            $("#start_job_status").html("Rework: <b>" + rework + "</b>");
                            $("#start_job_originally_started").html("Originally started <b>" + moment(jobInfo.start_time * 1000).format("LLLL") + "</b>");
                        } else {
                            $("#start_job_status").html("Status: <b>New</b>");
                            $("#start_job_originally_started").html("New Operation");
                        }

                        $('#modalStartJobInnerTitle').html(longOpID);
                        //("#start_job_qty").html("Quantity to complete: <b>" + jobInfo.qty_requested + "</b>"); // NOT SETTABLE FOR NOW, DO NOT DISPLAY!
                        $("#start_job_operation").html("Operation: <b>" + opInfo.job_title + "</b>");
                        $("#start_job_partid").html("Part ID: <b>" + longPartID + "</b>");


                        if(jobInfo.notes === '' || jobInfo.notes === null ) {
                            finalNotes = "None";
                        } else {
                            finalNotes = jobInfo.notes;
                        }

                        $("#start_job_notes").html("Notes: <b>" + finalNotes + "</b>");

                        $("#start_job").attr("data-startid", jobInfo.id);

                        $("#modalStartJob").modal();
                    }
                }
            });
        })
        .on("click", "#start_job", function() {
            $.post("/ondemand/shopfloor/job_actions.php?action=update_start_job", {id: queueJobID}, function(data) {
                if(data === 'success') {
                    updateActiveJobs();
                    updateQueuedJobs();

                    displayToast("success", "Successfully started operation.", "Operation Started");

                    $("#modalStartJob").modal('hide');
                } else if(data === 'success - resumed') {
                    updateActiveJobs();
                    updateQueuedJobs();

                    displayToast("success", "Successfully resumed operation.", "Operation Resumed");

                    $("#modalStartJob").modal('hide');
                } else {
                    $("body").append(data);
                }
            });
    })
        .on("click", ".update-active-job", function() {
            var opClicked = $(this);

            activeJobID = $(this).data("op-id");

            $.post("/ondemand/shopfloor/job_actions.php?action=get_op_info", {opID: activeJobID}, function(data) {
                var jobInfo = $.parseJSON(data);
                var opInfo = opClicked.data("op-info");
                var longOpID = opClicked.data("long-op-id");
                var longPartID = opClicked.data("long-part-id");

                $("#modalUpdateJobHeader").html("Update operation " + longOpID + " <i>(" + opInfo.job_title + ")</i>");
                //$("#qtyCompleted").val(jobInfo.qty_requested).attr("data-original-title", "Requested qty: " + jobInfo.qty_requested); // Don't have a quantity requested yet!
                $("#qtyCompleted").val("1");
                $("input[name='completionCode']").prop("checked", false);
                $("#notes").val("");

                $("#modalUpdateJob").modal();
            })
    })
        .on("click", "#save_job_update", function() {
            if(!$("input[name='completionCode']").is(':checked')) {
                alert("Please select the completion code before marking this operation complete.");
            } else {
                $.post("/ondemand/shopfloor/job_actions.php?action=update_active_job", {opID: activeJobID, notes: $("#notes").val(), qty: $("#qtyCompleted").val(), status: $("input[name='completionCode']:checked").val()}, function(data) {
                    if(data === 'success' ) {
                        displayToast("success", "Operation has been closed.", "Operation closed");
                        $("#modalUpdateJob").modal('hide');

                        updateActiveJobs();
                        updateQueuedJobs();
                    } else if(data === 'success - partial') {
                        displayToast("info", "Operation has been marked as partially completed.", "Partially Closed Operation");
                        $("#modalUpdateJob").modal('hide');

                        updateActiveJobs();
                        updateQueuedJobs();
                    } else if(data === 'success - rework') {
                        displayToast("info", "Operation has been sent to the previous department.", "Operation flagged for Rework");
                        $("#modalUpdateJob").modal('hide');

                        updateActiveJobs();
                        updateQueuedJobs();
                    } else {
                        $("body").append(data);
                    }
                });
            }
        })
        .on("change", "#viewing_queue", function() {

        });

    $("#modalStartJob").on("show.bs.modal", function() {
        jobInterval = setInterval(function() {
            $("#start_job_time").html(moment().format("LT"));
        }, 1000);
    }).on("hidden.bs.modal", function() {
        clearInterval(jobInterval);
    });

    $("#date-time").html(moment().format("LLLL"));

    setInterval(function() {
        $("#date-time").html(moment().format("LLLL"));
    }, 1000);

    loadCalendarPane();

    setInterval(function() { // reatime(ish) updating of job information, perhaps migrate this to Node.js at some point
        updateQueuedJobs();
        updateActiveJobs();
    }, 10000);
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>