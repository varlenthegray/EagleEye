<?php
require 'includes/header_start.php';
require 'includes/header_end.php';
?>

<script>
    <?php
        if($_SESSION['userInfo']['justLoggedIn']) {
            echo "displayToast('success', 'Welcome to your dashboard {$_SESSION['userInfo']['name']}!', 'Successfully Logged In', true);";
            $_SESSION['userInfo']['justLoggedIn'] = FALSE;
        }
    ?>
</script>

<div class="col-md-12" id="main_display">
    <div class="row">
        <div class="col-md-12">
            <div id="main_body"></div>
        </div>
    </div>
</div>

<div class="row" id="search_display" style="display: none;">
    <div class="col-md-12">
        <div class="card-box">
            <div class="col-md-12">
                <button class="btn btn-info waves-effect waves-light" id="btn_search_to_main"><i class="zmdi zmdi-arrow-left m-r-5"></i> <span>Return</span></button><br /><br />

                <table class="table table-bordered tablesorter" id="search_results_global_table">
                    <thead>
                    <tr>
                        <th colspan="2">SO#</th>
                        <th>PROJECT/CUSTOMER PO</th>
                        <th>SALESPERSON</th>
                        <th>DEALER/CONTRACTOR</th>
                        <th>PROJECT MANAGER/CONTACT</th>
                    </tr>
                    </thead>
                    <tbody id="search_results_table">
                    <tr>
                        <td colspan="7" class="text-md-center"><span id="global_search_status"><i class="fa fa-3x fa-spin fa-spinner" style="width: auto;margin-right: 10px;"></i></span></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer modal -->
<div id="modalAddCustomer" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddCustomerLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<!-- modal -->
<div id="modalLogin" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLoginLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="modalLoginName">Login As XYZ</h4>
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

<!-- Add Customer modal -->
<div id="modalViewNotes" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalViewNotesLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<!-- Global Search loading, required for global search to work -->
<script src="/ondemand/js/global_search.js?random=<?php echo rand(0,1500); ?>"></script>

<!-- Adding SO to the system -->
<script src="/ondemand/js/add_so.js"></script>

<script>
    var indv_dt_interval;
    var indv_auto_interval;
    var wc_auto_interval;
    var dash_auto_interval;

    var main_body = 'employees';
    var shop_logged_in;

    // -- Dashboard --
    <?php
    if(isset($_SESSION['userInfo']['default_queue']) || !empty($_SESSION['userInfo']['default_queue'])) {
        echo "var queue = '{$_SESSION['userInfo']['default_queue']}';";
    } else {
        echo "var queue = '{$_SESSION['shop_user']['default_queue']}';";
    }
    ?>

    var tr;
    var row;
    var shownData;
    var op_id;
    var op_num;
    // -- EO Dashboard --

    function clearIntervals() {
        clearInterval(indv_dt_interval);
        clearInterval(indv_auto_interval);
        clearInterval(wc_auto_interval);
        clearInterval(dash_auto_interval);
    }

    function backFromSearch() {
        $("#search_display").fadeOut(200);
        $("#global_search").val("");

        setTimeout(function() {
            $("#main_display").fadeIn(200);
        }, 200);
    }

    function loadPage(page) {
        clearIntervals();

        $(".js_loading").show();
        $("#main_body").load("/html/" + page + ".php", function() {
            $(".js_loading").hide();
        });

        backFromSearch();
    }

    $(".js_loading").show();

    $(function() {
        <?php
            if(!empty($_SESSION['shop_user'])) {
                echo "loadPage('dashboard');";
                echo "shop_logged_in = true;";
            } else {
                echo "loadPage('employees');";
                echo "shop_logged_in = false;";
            }
        ?>
    });

    $("body")
        // -- Navigation --
        .on("click", "#nav_dashboard", function() {
            if(shop_logged_in) {
                loadPage('dashboard');
            } else {
                loadPage('employees');
            }
        })
        .on("click", "#nav_pricing", function() {
            loadPage('pricing');
        })
        .on("click", "#nav_workcenter", function() {
            loadPage('workcenter');
        })
        .on("click", "#nav_timecard", function() {
            loadPage('timecard');
        })
        .on("click", "#nav_job-management", function() {
            loadPage('job_management');
        })
        .on("click", "#nav_employees", function() {
            <?php
            if($_SESSION['userInfo']['account_type'] > 4) {
            ?>
                if(shop_logged_in) {
                    $.post("/ondemand/shopfloor/login_actions.php?action=logout", function(data) {
                        if (data === 'success') {
                            loadPage('employees');
                            shop_logged_in = false;

                            displayToast("warning", "You have been logged out of the dashboard.", "Logout Complete");
                        }
                    });
                }
            <?php
            }
            ?>

            loadPage('employees');
        })
        .on("click", "#nav_tasks", function() {
            loadPage('tasks');
        })
        .on("click", "#nav_vin", function() {
            loadPage('build_a_vin');
        })
        // -- End Navigation --

        // -- Dashboard --
        .on("change", "#viewing_queue", function() {
            updateQueuedJobs();
        })
        .on("click", ".view_quote_info", function(e) {
            e.stopPropagation();

            console.log($(this).closest('tr'));
            /*quote_table.rows().eq(0).each(function (idx) {
                var row2 = quote_table.row(idx);

                if(row2.child.isShown()) {
                    row2.child.close();
                }
            });

            tr = $(this).closest('tr');
            row = quote_table.row(tr);

            if(row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                $.post("/ondemand/shopfloor/dashboard.php?action=quote_details", function(data) {
                    shownData = data;

                    row.child(shownData).show();
                    tr.addClass('shown');
                });
            }*/
        })
        .on("click", ".view_so_info", function() {
            var id = $(this).attr("id");
            $("#global_search").val(id).trigger("keyup");
        })

        .on("click", ".start-operation", function(e) {
            e.stopPropagation();

            var opFull = $(this).closest('tr').find('td').eq(3).html();
            op_num = opFull.substr(0,3);
            op_id = $(this).attr("id");

            if(op_num === '000') {
                $.post("/ondemand/shopfloor/dashboard.php?action=get_start_info", {opID: op_id, op: op_num}, function(data) {
                    $("#modalStartJob").html(data);
                }).done(function() {
                    $("#modalStartJob").modal();
                }).fail(function() { // if we're receiving a header error
                    $("body").append(data); // echo an error and log it
                });
            } else {
                $.post("/ondemand/shopfloor/dashboard.php?action=start_operation", {operation: op_num, id: op_id}, function(data) {
                    $('body').append(data);

                    updateQueuedJobs();
                    active_table.ajax.reload(null,false);

                    $.post("/ondemand/shopfloor/view_notes.php", {queueID: op_id}, function(data) {
                        $("#modalViewNotes").html(data).modal("show");
                    });
                });
            }
        })
        .on("click", "#start_job", function() {
            var other_notes_field = $("#other_notes_field").val(); // non-billable "other" section
            var notes_field = $("#notes_field").val(); // Cabinet Vision task or anything with JUST a notes field

            if($("#other_subtask").is(":checked")) { // if this is a subtask "other" section then we have to verify the notes
                if (other_notes_field.length >= 3) { // and the length of notes is greater than 3
                    $.post("/ondemand/shopfloor/dashboard.php?action=start_operation", {id: op_id, operation: op_num, subtask: "Other", notes: other_notes_field}, function (data) {
                        $("body").append(data);

                        active_table.ajax.reload(null,false);
                        updateQueuedJobs();

                        $("#modalStartJob").modal('hide');
                    });
                } else { // otherwise, the notes is less than 3 and they need to enter notes in
                    displayToast("error", "Enter notes in before continuing (more than 3 characters).", "Notes Required");
                }
            } else { // this is an OTF (for now?)
                var subtask = $('input[name=nonBillableTask]:checked').val();
                var otf_so_num = $("#otf_so_num").val();
                var otf_room = $("#otf_room").val();
                var otf_op = $("#otf_operation").val();
                var otf_notes = $("#otf_notes").val();
                var otf_iteration = $("#otf_iteration").val();

                $.post("/ondemand/shopfloor/dashboard.php?action=start_operation", {id: op_id, operation: op_num, subtask: subtask,
                    notes: notes_field, otf_so_num: otf_so_num, otf_room: otf_room, otf_op: otf_op, otf_notes: otf_notes, otf_iteration: otf_iteration}, function(data) {
                    active_table.ajax.reload(null,false);
                    updateQueuedJobs();

                    $('body').append(data);

                    $("#modalStartJob").modal('hide');
                });
            }
        })
        .on("change", "input[name='nonBillableTask']", function() {
            if($(this).prop("id") === 'other_subtask') {
                $("#other_notes_section").show();
                $("#other_notes_field").focus();
            } else {
                $("#other_notes_section").hide();
            }
        })

        .on("click", ".pause-operation", function(e) {
            e.stopPropagation();

            op_id = $(this).attr("id");

            $.post("/ondemand/shopfloor/dashboard.php?action=get_pause_info", {opID: op_id}, function(data) {
                $("#modalUpdateJob").html(data).modal();
            });
        })
        .on("click", "#pause_op", function() {
            $.post("/ondemand/shopfloor/dashboard.php?action=pause_operation", {opID: op_id, notes: $("#notes").val(), qty: $("#qtyCompleted").val()}, function(data) {
                active_table.ajax.reload(null,false);
                updateQueuedJobs();

                $("body").append(data);
                $("#modalUpdateJob").modal('hide');
            });
        })

        .on("click", ".complete-operation", function(e) {
            e.stopPropagation();

            op_id = $(this).attr("id");

            $.post("/ondemand/shopfloor/dashboard.php?action=get_stop_info", {opID: op_id}, function(data) {
                $("#modalUpdateJob").html(data).modal();
            });
        })
        .on("change", "#rework_reqd", function() {
            if($(this).is(":checked")) {
                $(".rework_reason_group").show();
                $("#complete_op").html("Send Back");
            } else {
                $(".rework_reason_group").hide();
                $("#complete_op").html("Complete");
            }
        })
        .on("click", "#complete_op", function() {
            var notes = $("#notes").val();
            var qty_compl = $("#qtyCompleted").val();
            var rework = $("#rework_reqd").is(":checked");
            var rw_reason = $("#rework_reason :selected").val();

            $.post("/ondemand/shopfloor/dashboard.php?action=complete_operation", {opID: op_id, opnum: op_num, notes: notes, qty: qty_compl, rework_reqd: rework, rework_reason: rw_reason}, function(data) {
                $('body').append(data);
                $("#modalUpdateJob").modal('hide');

                updateQueuedJobs();
                active_table.ajax.reload(null,false);
            });
        })

        .on("click", ".op-notes", function(e) {
            e.stopPropagation();

            $.post("/ondemand/shopfloor/view_notes.php", {queueID: $(this).attr("id")}, function(data) {
                $("#modalViewNotes").html(data).modal("show");
            });
        })
        // -- End Dashboard --

        // -- Workcenter --
        .on("click", ".wc-edit-queue", function() {
            var id = $(this).attr("id");
            $("#global_search").val(id).trigger("keyup");
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
        })
        // -- End Workcenter --

        // -- Employees --
        .on("click", ".login", function() {
            userID = $(this).data("login-id");

            <?php
            if($_SESSION['userInfo']['account_type'] > 4) {
            ?>
            // This was tricky, wtf is with the second variable passed to the object? docs do not explain and the demo shows wrong info
            $("#modalLogin").modal("show", $(this));

            <?php
            } else {
            ?>

            $.post("/ondemand/shopfloor/login_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
                if (data === 'success') {
                    loadPage('dashboard');
                    shop_logged_in = true;
                } else {
                    displayToast("error", "Failed to log in, please try again.", "Login Failure");
                    $("#modalLogin").modal('hide');
                }
            });
            <?php
            }
            ?>
        })
        .on("click", "#clock_in", function() {
            $.post("/ondemand/shopfloor/login_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
                if (data === 'success') {
                    loadPage('dashboard');
                    $("#modalLogin").modal('hide');

                    shop_logged_in = true;
                    main_body = 'dashboard';
                } else if(data === 'success - clocked in') {
                    loadPage('dashboard');
                    $("#modalLogin").modal('hide');
                    displayToast("success", "Successfully logged you in for the day.", "Login Successful");

                    shop_logged_in = true;
                    main_body = 'dashboard';
                } else {
                    displayToast("error", "Failed to log in, please try again.", "Login Failure");
                    $("#modalLogin").modal('hide');
                    shop_logged_in = false;
                }
            });
        })
        // -- End Employees --
    ;
</script>

<?php
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>