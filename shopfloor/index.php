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
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="pull-left">Active Operations for <?php echo $_SESSION['shop_user']['name']; ?></h4>

                        <h4 id="date-time" class="pull-right"></h4>

                        <table id="active_jobs_individual_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="5%">SO#</th>
                                <th width="5%">Room</th>
                                <th width="20%">Department</th>
                                <th width="37%">Operation</th>
                                <th width="18%">Start/Resumed Time</th>
                                <th width="15%">Active Time</th>
                            </tr>
                            </thead>
                            <tbody id="active_jobs_table">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row" style="margin-top: 15px;">
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
                        <table id="jiq_individual_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="8%">SO#</th>
                                <th width="17%">Department</th>
                                <th width="31%">Operation</th>
                                <th width="13%">Release Date</th>
                                <th width="13%">Delivery Date</th>
                                <th width="13%">Operation Time</th>
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
    var queueID;
    var operation;

    var queue = $('#viewing_queue').val();

    function updateActiveJobs() {
        active_table.ajax.reload(null,false);
    }

    function updateQueuedJobs() {
        queue = $('#viewing_queue').val();
        jiq_table.ajax.url("/ondemand/shopfloor/job_actions.php?action=display_job_queue&queue=" + queue).load();
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

    var jiq_table = $("#jiq_individual_table").DataTable({
        "ajax": "/ondemand/shopfloor/job_actions.php?action=display_job_queue&queue=" + queue,
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand display-queued-job-info");
        },
        "order": [[0,"desc"]],
        "dom": 'rti',
        "pageLength": 25
    });

    var active_table = $("#active_jobs_individual_table").DataTable({
        "ajax": "/ondemand/shopfloor/job_actions.php?action=display_active_jobs",
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand display-active-job-info");
        },
        "order": [[0,"desc"]],
        "dom": 'rti',
        "pageLength": 25
    });

    $("body")
        .on("click", ".display-queued-job-info", function() {
            queueID = $(this).attr("id");
            operation = $(this).find('td:nth-child(3)').text();

            $.post("/ondemand/shopfloor/job_actions.php?action=get_op_info", {opID: queueID, op: operation}, function(data) {
                $("#modalStartJob").html(data);
            }).done(function() {
                $("#modalStartJob").modal();
            }).fail(function() { // if we're receiving a header error
                $("body").append(data); // echo an error and log it
            });
        })
        .on("click", "#start_job", function() {
            var other_notes_field = $("#other_notes_field").val(); // non-billable "other" section
            var notes_field = $("#notes_field").val(); // Cabinet Vision task or anything with JUST a notes field

            if($("#other_subtask").is(":checked")) { // if this is a subtask "other" section then we have to verify the notes
                if(other_notes_field.length >= 3) { // and the length of notes is greater than 3
                    $.post("/ondemand/shopfloor/job_actions.php?action=update_start_job", {id: queueID, operation: operation, subtask: "Other", notes: other_notes_field}, function(data) {
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
                } else { // otherwise, the notes is less than 3 and they need to enter notes in
                    displayToast("error", "Enter notes in before continuing (more than 3 characters).", "Notes Required");
                }
            } else { // we do not have to verify the "other" subtask notes
                var subtask = $('input[name=nonBillableTask]:checked').val();
                var otf = 'no';
                var otf_so_num = $("#otf_so_num").val();
                var otf_room = $("#otf_room").val();
                var otf_op = $("#otf_operation").val();
                var otf_notes = $("#otf_notes").val();
                var otf_iteration = $("#otf_iteration").val();

                if($(this).data("otf") === true) { // if it's an on-the-fly operation
                    otf = 'yes';
                }

                $.post("/ondemand/shopfloor/job_actions.php?action=update_start_job", {id: queueID, operation: operation, subtask: subtask,
                    notes: notes_field, otf: otf, otf_so_num: otf_so_num, otf_room: otf_room, otf_op: otf_op, otf_notes: otf_notes, otf_iteration: otf_iteration}, function(data) {
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
                    } else if(data === 'success - otf') {
                        updateActiveJobs();
                        updateQueuedJobs();

                        displayToast("success", "Successfully created an On The Fly operation.", "OTF Created");

                        $("#modalStartJob").modal('hide');
                    } else {
                        $("body").append(data);
                    }
                });
            }
    })
        .on("click", ".display-active-job-info", function() {
            activeJobID = $(this).attr("id");

            $.post("/ondemand/shopfloor/job_actions.php?action=get_active_job", {opID: activeJobID}, function(data) {
                $("#modalUpdateJob").html(data);
            }).done(function() {
                $("#modalUpdateJob").modal();
            });
    })
        .on("click", "#save_job_update", function() {
            var nb = $(this).data("nb"); // is this a non-billable job?

            if(nb) { // if this is non-billable, lets process it as such
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
        })
        .on("keyup", "#otf_room", function() {
            $(this).val($(this).val().toUpperCase());
        })
        .on("click", ".completion_code3", function() {
            $(".rework_reason_group").show();
            $("#rework_reason").focus();
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