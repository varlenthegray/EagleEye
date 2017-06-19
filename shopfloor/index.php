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
                    <h4 class="pull-left"><label for="active_ops_view">Active Operations for</label>
                        <select id="active_ops_view" name="active_ops_view">
                            <?php
                                echo $_SESSION['shop_user']['name'];

                                $depts = json_decode($_SESSION['shop_user']['department']);

                                foreach($depts as $department) {
                                    echo "<option value='$department'>$department</option>";
                                }
                            ?>
                        </select>
                    </h4>

                    <h4 id="date-time" class="pull-right"></h4>

                    <table class="tablesaw table" data-tablesaw-mode="swipe" data-tablesaw-sortable>
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Sales Order ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Department</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Operation</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Start/Resumed Time</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Active Time</th>
                        </tr>
                        </thead>
                        <tbody id="active_jobs_table">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h4><label for="viewing_queue">Queue for</label>
                        <select name="viewing_queue" id="viewing_queue">
                            <?php
                            $deptartments = json_decode($_SESSION['shop_user']['department']);
                            $default = $_SESSION['shop_user']['default_queue'];

                            foreach($deptartments as $department) {
                                if($department === $default) {
                                    $selected = 'selected';
                                } else {
                                    $selected = '';
                                }

                                echo "<option value='$department' $selected>$department</option>";
                            }
                            ?>
                        </select>
                    </h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="tablesaw table" data-tablesaw-mode="swipe" data-tablesaw-sortable>
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Sales Order ID</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Department</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Operation</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Release Date</th>
                            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Operation Time</th>
                        </tr>
                        </thead>
                        <tbody id="job_queue_table">
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
        <!-- for flexibility, I'm going to display this via AJAX data return -->
    </div>
    <!-- /.modal -->

    <!-- modal -->
    <div id="modalUpdateJob" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalUpdateJob" aria-hidden="true">
        <!-- same here, displayed via AJAX -->
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
    var opInfo;

    function updateActiveJobs() {
        var active = $("#active_ops_view").val();

        $.post("/ondemand/shopfloor/job_actions.php?action=display_active_jobs", {view: active}, function(data) { // grab the latest information
            $("#active_jobs_table").html(data); // display that information in the table
        });
    }

    function updateQueuedJobs() {
        var queue = $('#viewing_queue').val();

        $.post("/ondemand/shopfloor/job_actions.php?action=display_job_queue", {queue: queue} , function(data) { // grab the latest information
            if(data !== '')
                $("#job_queue_table").html(data); // display that information in the table
            else
                $("#job_queue_table").html("<tr><td colspan='4'>No jobs in queue</td></tr>");
        });
    }

    updateActiveJobs();
    updateQueuedJobs();

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
            queueJobID = $(this).data("op-id");
            opInfo = $(this).data("op-info");

            $.post("/ondemand/shopfloor/job_actions.php?action=get_op_info", {opID: queueJobID, opInfo: opInfo}, function(data) {
                $("#modalStartJob").html(data);
            }).done(function() {
                $("#modalStartJob").modal();
            }).fail(function() { // if we're receiving a header error
                $("body").append(data); // echo an error and log it
            });
        })
        .on("click", "#start_job", function() {
            var other_notes_field = $("#other_notes_field").val();
            var notes_field = $("#notes_field").val();

            if($("#other_subtask").is(":checked")) {
                if(other_notes_field.length >= 3) {
                    $.post("/ondemand/shopfloor/job_actions.php?action=update_start_job", {id: queueJobID, opInfo: opInfo, subtask: "Other", notes: other_notes_field}, function(data) {
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
                } else {
                    displayToast("error", "Enter notes in before continuing.", "Notes Required");
                }
            } else {
                var subtask = $('input[name=nonBillableTask]:checked').val();

                $.post("/ondemand/shopfloor/job_actions.php?action=update_start_job", {id: queueJobID, opInfo: opInfo, subtask: subtask, notes: notes_field}, function(data) {
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
            }
    })
        .on("click", ".update-active-job", function() {
            activeJobID = $(this).data("op-id");

            $.post("/ondemand/shopfloor/job_actions.php?action=get_active_job", {opID: activeJobID}, function(data) {
                $("#modalUpdateJob").html(data);
            }).done(function() {
                $("#modalUpdateJob").modal();
            });
    })
        .on("click", "#save_job_update", function() {
            var nb = $(this).data("nb");

            if(nb) {
                $.post("/ondemand/shopfloor/job_actions.php?action=update_active_job", {opID: activeJobID, notes: $("#notes").val(), nb: 'true'}, function(data) {
                    updateActiveJobs();
                    updateQueuedJobs();

                    $("body").append(data);
                    $("#modalUpdateJob").modal('hide');
                });
            } else {
                if(!$("input[name='completionCode']").is(':checked')) {
                    displayToast("error", "Please select a completion code before submission.", "No completion code");
                    $("input[name='completionCode']").focus();
                } else {
                    $.post("/ondemand/shopfloor/job_actions.php?action=update_active_job", {opID: activeJobID, nb: 'false', notes: $("#notes").val(), qty: $("#qtyCompleted").val(), status: $("input[name='completionCode']:checked").val()}, function(data) {
                        updateActiveJobs();
                        updateQueuedJobs();

                        $("body").append(data);
                        console.log(data);
                        $("#modalUpdateJob").modal('hide');
                    });
                }
            }
        })
        .on("change", "#viewing_queue", function() {
            updateQueuedJobs();
        })
        .on("change", "input[name='nonBillableTask']", function() {
            if($(this).prop("id") === 'other_subtask') {
                $("#other_notes_section").show();
                $("#other_notes_field").focus();
            } else {
                $("#other_notes_section").hide();
            }
        })
        .on("keyup", "#other_notes_field", function() {
            if($(this).val().length >= 3) {
                $("#other_notes_field").removeClass("form-control-danger").addClass("form-control-success");
                $("#other_notes_section").removeClass("has-danger").addClass("has-success");
            } else {
                $("#other_notes_field").removeClass("form-control-success").addClass("form-control-danger");
                $("#other_notes_section").removeClass("has-success").addClass("has-danger");
            }
        })
        .on("change", "#active_ops_view", function() {
            updateActiveJobs();
        })
        .on("click", "#add_me", function() {
            var id = $(this).data("taskid");

            $.post("/ondemand/shopfloor/job_actions.php?action=add_me", {id: id}, function(data) {
                $('body').append(data);
                $("#modalUpdateJob").modal('hide');
            });
        });

    // just the modal timer, we're recording this upon execution of job so this is for "show" only
    $("#modalStartJob").on("show.bs.modal", function() {
        jobInterval = setInterval(function() {
            $("#start_job_time").html(moment().format("LT"));
        }, 1000);
    }).on("hidden.bs.modal", function() {
        clearInterval(jobInterval);
    });

    $("#date-time").html(moment().format("LLLL"));

    // global time, it's a clock... what do you expect it to do?
    setInterval(function() {
        $("#date-time").html(moment().format("LLLL"));
    }, 1000);

    // display the calendar... or... load it... like it says
    loadCalendarPane();

    // real-time updating of the jobs and active queue, node.js feels like overkill right now but could be migrated to that at some point
    // note: attempting to keep codebase available to standard "web" objects only, even if that means additional refreshes sent to the server/strain
    // yes, it's archaic, but it's totally viable and really not that bad, right? RIGHT?!
    setInterval(function() {
        updateQueuedJobs();
        updateActiveJobs();
    }, 5000);
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>