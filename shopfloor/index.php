<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<?php if(!$_SESSION['shop_active']) { ?>
<div class="row" id="default_login_form">
    <div class="col-md-6">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <table class="tablesaw table">
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-priority="persist">Employee</th>
                        </tr>
                        </thead>
                        <tbody id="room_search_table">
                        <?php
                            $qry = $dbconn->query("SELECT * FROM user");

                            while($result = $qry->fetch_assoc()) {
                                echo "<tr class='cursor-hand' data-toggle='modal' data-target='#modalLogin' data-login-id='{$result['id']}' data-login-name='{$result['name']}'>";
                                echo "<td>{$result['name']}</td>";
                                echo "</tr>";
                            }
                        ?>
                        </tbody>
                    </table>

                    <!-- modal -->
                    <div id="modalLogin" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLoginLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h4 class="modal-title" id="modalLoginName">Login As Ben</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 text-md-center">
                                            <h4>Enter PIN Code</h4>

                                            <input type="password" autocomplete="off" name="pin" placeholder="PIN" maxlength="4" id="loginPin" class="text-md-center">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary waves-effect waves-light" id="clock_in">Clock In</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<div class="row" id="next_section" <?php if(!$_SESSION['shop_active']) echo 'style="display: none;"'; ?>>
    <!-- Left column -->
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
                                        echo "<tr class='cursor-hand' data-toggle='modal' data-target='#modalUpdateJob' data-id='{$result['id']}'>";
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
                        <tbody id="room_search_table">
                            <?php
                                $qry = $dbconn->query("SELECT * FROM jobs WHERE NOT active AND assigned_to = {$_SESSION['shop_user']['id']}");

                                if($qry->num_rows > 0) {
                                    while($result = $qry->fetch_assoc()) {
                                        echo "<tr class='cursor-hand queue-job-start' data-job-id='{$result['id']}'>";
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
                    <h4 class="modal-title" id="modalUpdateJobHeader">Update 734A_A01_DOORS</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <fieldset class="form-group">
                                <label for="qtyCompleted">Quantity Completed</label>
                                <input type="text" class="form-control" id="qtyCompleted" name="qtyComplete" placeholder="1">
                            </fieldset>

                            <fieldset class="form-group">
                                <input type="radio" name="completionCode" id="completion_code1" value="Complete" checked>
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
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="save_job_update">Save</button>
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
    var userID;
    var jobInterval;
    var employeeName;
    var jobInfo;

    function doLogin() {
        $.post("/ondemand/shopfloor/login_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
            if (data === 'success') {
                $("#modalLogin").modal('hide').on('hidden.bs.modal', function() {
                    $("#default_login_form").remove(); // remove the ability to log in (completely)

                    $("#next_section").show(); // show the main page after login
                    loadCalendarPane(); // load the calendar
                    $("#shop_employee_name").text(employeeName);
                });
            } else {
                displayToast("error", "Failed to log in, please try again.", "Login Failure");
                $("#modalLogin").modal('hide');
            }
        });
    }

    $("#modalLogin").on("show.bs.modal", function(e) { // when we're triggering the show event
        var userLine = $(e.relatedTarget); // grab the related line and information associated with it
        var modal = $(this); // set the modal to this specific element

        employeeName = userLine.data("login-name");

        modal.find('.modal-title').text('Hello ' + userLine.data("login-name")); // find and update the text to the login name from the data line
        userID = userLine.data("login-id"); // grab the user ID and prep it for sending to the login handler

        $("#loginPin").val(""); // clear out any previous entries/attempts
    }).on("shown.bs.modal", function() { // once the modal form is completely shown
        $("#loginPin").focus(); // set the focus (once the modal is fully painted on the canvas)
    });

    $("body")
        .on("click", ".queue-job-start", function() {
        $.post("/ondemand/shopfloor/job_actions.php?action=start_job", {jobID: $(this).data("job-id")}, function(data) {
            if(data !== '') {
                jobInfo = $.parseJSON(data);

                $("#modalStartJob").modal();
            }
        });
    })
        .on("click", "#start_job", function() {
            $.post("/ondemand/shopfloor/job_actions.php?action=update_start_job", {id: jobInfo.id}, function(data) {
                if(data === 'success') {
                    displayToast("success", "Successfully started job.", "Job Started");
                } else {
                    displayToast("error", "Unable to start job.", "Job Error");
                }
            });
    });

    $("#modalStartJob").on("show.bs.modal", function() {
        var modal = $(this);

        $("#start_job_time").html(moment().format("LT"));

        modal.find('#modalStartJobInnerTitle').text(jobInfo.job_id);
        modal.find("#start_job_qty").text("Quantity to complete: " + jobInfo.qty_requested);
        modal.find("#start_job_operation").text("Operation: " + jobInfo.operation);
        modal.find("#start_job_partid").text("Job ID: " + jobInfo.part_id);

        $("#start_job").attr("data-startid", jobInfo.id);

        jobInterval = setInterval(function() {
            $("#start_job_time").html(moment().format("LT"));
        }, 1000);
    }).on("hidden.bs.modal", function() {
        clearInterval(jobInterval);
    });

    $("#clock_in").on("click", function() { // if you click the button, do login
        doLogin();
    });

    $("#loginPin").on("keypress", function(e) { // each time you press a key in the PIN field
        if(e.keyCode === 13) // if hitting the enter key, do login
            doLogin();
    });

    $("#date-time").html(moment().format("LLLL"));

    setInterval(function() {
        $("#date-time").html(moment().format("LLLL"));
    }, 1000);

    <?php if($_SESSION['shop_active']) echo 'loadCalendarPane();'; ?>

    $("#shop_logout_link").on("click", function() {
        $.post("/ondemand/shopfloor/login_actions.php?action=logout", function() {
            window.location.href = ("/shopfloor/index.php");
        });
    });
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>