<?php
require '../includes/header_start.php';
require '../includes/header_end.php';

require("../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/

use Carbon\Carbon; // prep carbon
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<div class="row" id="default_login_form">
    <div class="col-md-6">
        <div class="card-box">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <table id="individual_login" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Logged In Time</th>
                                <th>Current Operations</th>
                                <th>Time Logged In</th>
                            </tr>
                            </thead>
                            <tbody id="room_search_table">
                            <?php
                            $qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE;");

                            while($result = $qry->fetch_assoc()) {
                                if($result['id'] !== '16') {
                                    $last_login_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = {$result['id']} ORDER BY time_in DESC LIMIT 0,1");

                                    if($last_login_qry->num_rows > 0) {
                                        $last_login = $last_login_qry->fetch_assoc();

                                        if($last_login['time_in'] > strtotime("today")) {
                                            if($result['id'] === '7' || $result['id'] === '8') {
                                                $time_in_display = "";
                                            } else {
                                                $time = date(TIME_ONLY, $last_login['time_in']);
                                            }
                                        } else {
                                            if($result['id'] === '7' || $result['id'] === '8') {
                                                $time = '';
                                            } else {
                                                $time = date(DATE_DEFAULT, $last_login['time_in']);
                                            }
                                        }

                                        $time_unix = $last_login['time_in'];
                                    } else {
                                        $time = "Never";
                                        $time_unix = null;
                                    }

                                    if(!empty($time_unix)) {
                                        $today = mktime(0,0);

                                        if($time_unix >= $today) {
                                            if($result['id'] === '7' || $result['id'] === '8') {
                                                $time_in_display = "";
                                            } else {
                                                $carbon_time = Carbon::createFromTimestamp($time_unix);
                                                $time_in_display = $carbon_time->diffForHumans(null, true);
                                            }
                                        } else {
                                            if($result['id'] === '7' || $result['id'] === '8') {
                                                $time_in_display = "";
                                            } else {
                                                $time_in_display = "Hasn't logged in today";
                                            }
                                        }
                                    } else {
                                        $time_in_display = "Never logged in";
                                    }

                                    $ops_qry = $dbconn->query("SELECT * FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE active_employees LIKE '%\"{$result['id']}\"%'");

                                    $final_ops = '';

                                    if($ops_qry->num_rows > 0) {
                                        while($ops = $ops_qry->fetch_assoc()) {
                                            if(!empty($ops['so_parent']) && !empty($ops['room'])) {
                                                $so_info = "{$ops['so_parent']}{$ops['room']} - ";
                                            } else {
                                                $so_info = null;
                                            }

                                            $operation = $so_info . $ops['op_id'] . ": " . $ops['job_title'];
                                            $final_ops .= $operation . ", ";
                                        }
                                    } else {
                                        $final_ops = "None";
                                    }

                                    $final_ops = rtrim($final_ops, ", ");

                                    echo "<tr class='cursor-hand' data-toggle='modal' data-target='#modalLogin' data-login-id='{$result['id']}' data-login-name='{$result['name']}'>";
                                    echo "<td>{$result['name']}</td>";
                                    echo "<td>$time</td>";
                                    echo "<td>$final_ops</td>";
                                    echo "<td>$time_in_display</td>";
                                    echo "</tr>";
                                }
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
</div>

<script>
    var userID;

    function doLogin() {
        $.post("/ondemand/shopfloor/login_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
            if (data === 'success - clocked in') {
                window.location.href = "/shopfloor/index.php?action=cltrue";
            } else if(data === 'success') {
                window.location.href = "/shopfloor/index.php";
            } else {
                displayToast("error", "Failed to log in, please try again.", "Login Failure");
                $("#modalLogin").modal('hide');
            }
        });
    }

    $("#modalLogin").on("show.bs.modal", function(e) { // when we're triggering the show event
        var userLine = $(e.relatedTarget); // grab the related line and information associated with it
        var modal = $(this); // set the modal to this specific element

        modal.find('.modal-title').text('Hello ' + userLine.data("login-name")); // find and update the text to the login name from the data line

        userID = userLine.data("login-id");

        $("#loginPin").val(""); // clear out any previous entries/attempts
    }).on("shown.bs.modal", function() { // once the modal form is completely shown
        $("#loginPin").focus(); // set the focus (once the modal is fully painted on the canvas)
    });

    $("#clock_in").on("click", function() { // if you click the button, do login
        doLogin();
    });

    $("#loginPin").on("keypress", function(e) { // each time you press a key in the PIN field
        if(e.keyCode === 13) // if hitting the enter key, do login
            doLogin();
    });
</script>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>