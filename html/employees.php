<?php
require '../includes/header_start.php';
require("../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/

use Carbon\Carbon; // prep carbon
?>

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
                                <th>Clocked In Time</th>
                                <th>Current Operations</th>
                                <th>Time Clocked In</th>
                            </tr>
                            </thead>
                            <tbody id="room_search_table">
                            <?php
                            if((int)$_SESSION['userInfo']['id'] === 1 || (int)$_SESSION['userInfo']['id'] === 7 || (int)$_SESSION['userInfo']['id'] === 8 || (int)$_SESSION['userInfo']['id'] === 9) {
                                $qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE ORDER BY name ASC;");
                            } else {
                                $qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE AND id != 7 AND id != 8 AND id != 1 ORDER BY name ASC;");
                            }

                            while($result = $qry->fetch_assoc()) {
                                if($result['id'] !== '16') {
                                    $last_login_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = {$result['id']} ORDER BY time_in DESC LIMIT 0,1");

                                    if($last_login_qry->num_rows > 0) {
                                        $last_login = $last_login_qry->fetch_assoc();

                                        if($last_login['time_in'] > strtotime("today")) {
                                            $time = date(TIME_ONLY, $last_login['time_in']);
                                        } else {
                                            $time = date(DATE_DEFAULT, $last_login['time_in']);
                                        }

                                        $time_unix = $last_login['time_in'];
                                    } else {
                                        $time = "Never";
                                        $time_unix = null;
                                    }

                                    if(!empty($time_unix)) {
                                        $today = mktime(0,0);

                                        if($time_unix >= $today) {
                                            $carbon_time = Carbon::createFromTimestamp($time_unix);
                                            $time_in_display = $carbon_time->diffForHumans(null, true);
                                        } else {
                                            $time_in_display = "Hasn't logged in today";
                                        }
                                    } else {
                                        $time_in_display = "Never logged in";
                                    }

                                    $ops_qry = $dbconn->query("SELECT * FROM op_queue JOIN operations ON op_queue.operation_id = operations.id WHERE active_employees LIKE '%\"{$result['id']}\"%' AND active = TRUE");

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

                                    echo "<tr class='cursor-hand login' data-login-id='{$result['id']}' data-login-name='{$result['name']}'>";
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $("#modalLogin").on("show.bs.modal", function(e) { // when we're triggering the show event
        var userLine = $(e.relatedTarget); // grab the related line and information associated with it
        var modal = $(this); // set the modal to this specific element

        modal.find('.modal-title').text('Hello ' + userLine.data("login-name")); // find and update the text to the login name from the data line

        userID = userLine.data("login-id");

        $("#loginPin").val(""); // clear out any previous entries/attempts
    }).on("shown.bs.modal", function() { // once the modal form is completely shown
        $("#loginPin").focus(); // set the focus (once the modal is fully painted on the canvas)
    });

    $("#loginPin").on("keypress", function(e) { // each time you press a key in the PIN field
        if(e.keyCode === 13) // if hitting the enter key, do login
            $("#clock_in").trigger("click"); // trigger the clockin button actions
    });
</script>