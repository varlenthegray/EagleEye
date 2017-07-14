<?php
require '../includes/header_start.php';
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

                                    echo "<tr class='cursor-hand login' data-login-id='{$result['id']}'>";
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
    $("body").on("click", ".login", function() {
        userID = $(this).data("login-id");

        $.post("/ondemand/shopfloor/login_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
            if (data === 'success') {
                window.location.href = "/index.php";
            } else {
                displayToast("error", "Failed to log in, please try again.", "Login Failure");
                $("#modalLogin").modal('hide');
            }
        });
    });
</script>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>