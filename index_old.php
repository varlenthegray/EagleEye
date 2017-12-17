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
            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-primary waves-effect waves-light" id="btn_search_to_main"><i class="zmdi zmdi-arrow-left m-r-5"></i> <span>GO BACK</span></button><br /><br />

                    <table class="table table-bordered tablesorter" id="search_results_global_table">
                        <thead>
                        <tr>
                            <th colspan="2">SO#</th>
                            <th>PROJECT/CUSTOMER PO</th>
                            <th>PROJECT MANAGER</th>
                            <th>DEALER/CONTRACTOR</th>
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

    var userID;

    // -- Dashboard --
    <?php
    if(isset($_SESSION['userInfo']['default_queue']) || !empty($_SESSION['userInfo']['default_queue'])) {
        echo "var queue = '{$_SESSION['userInfo']['default_queue']}';";
    } else {
        echo "var queue = '{$_SESSION['shop_user']['default_queue']}';";
    }
    ?>

    var op_id;
    var opFull;
    // -- EO Dashboard --

    // -- Build a VIN --
    var vin_sonum;
    // -- End Build a VIN --

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

    function calcVin(room_id) {
        var room;

        if($("select[name='room']").val() === undefined) {
            room = $("input[name='room']").val();
        } else {
            room = $("select[name='room']").val();
        }

        var iteration = $("input[name='iteration']").val();
        var product_type = $("select[name='product_type']").val();
        var order_status = $("select[name='order_status']").val();
        var days_to_ship = $("select[name='days_to_ship']").val();
        var dealer_code = $("#vin_dealer_code_" + room_id).val();

        var species_grade = $("#species_grade_" + room_id).find(":selected").val();
        var construction_method = $("#construction_method_" + room_id).find(":selected").val();
        var door_design = $("#door_design_" + room_id).find(":selected").val();
        var panel_raise_door = $("#panel_raise_door_" + room_id).find(":selected").val();
        var panel_raise_sd = $("#panel_raise_sd_" + room_id).find(":selected").val();
        var panel_raise_td = $("#panel_raise_td_" + room_id).find(":selected").val();
        var edge_profile = $("#edge_profile_" + room_id).find(":selected").val();
        var framing_bead = $("#framing_bead_" + room_id).find(":selected").val();
        var framing_options = $("#framing_options_" + room_id).find(":selected").val();
        var style_rail_width = $("#style_rail_width_" + room_id).find(":selected").val();
        var finish_code = $("#finish_code_" + room_id).find(":selected").val();
        var sheen = $("#sheen_" + room_id).find(":selected").val();
        var glaze = $("#glaze_" + room_id).find(":selected").val();
        var glaze_technique = $("#glaze_technique_" + room_id).find(":selected").val();
        var antiquing = $("#antiquing_" + room_id).find(":selected").val();
        var worn_edges = $("#worn_edges_" + room_id).find(":selected").val();
        var distress_level = $("#distress_level_" + room_id).find(":selected").val();
        var carcass_exterior_species = $("#carcass_exterior_species_" + room_id).find(":selected").val();
        var carcass_exterior_finish_code = $("#carcass_exterior_finish_code_" + room_id).find(":selected").val();
        var carcass_exterior_glaze_color = $("#carcass_exterior_glaze_color_" + room_id).find(":selected").val();
        var carcass_exterior_glaze_technique = $("#carcass_exterior_glaze_technique_" + room_id).find(":selected").val();
        var carcass_interior_species = $("#carcass_interior_species_" + room_id).find(":selected").val();
        var carcass_interior_finish_code = $("#carcass_interior_finish_code_" + room_id).find(":selected").val();
        var carcass_interior_glaze_color = $("#carcass_interior_glaze_color_" + room_id).find(":selected").val();
        var carcass_interior_glaze_technique = $("#carcass_interior_glaze_technique_" + room_id).find(":selected").val();
        var drawer_boxes = $("#drawer_boxes_" + room_id).find(":selected").val();

        $("#vin_code_" + room_id).val(active_so_num + room + "-" + iteration + "-" + product_type + order_status + days_to_ship + "_" + dealer_code + "_" + species_grade + construction_method + door_design + "-" + panel_raise_door + panel_raise_sd + panel_raise_td + "-" + edge_profile +
            framing_bead + framing_options + style_rail_width + "_" + finish_code + sheen + "-" + glaze + glaze_technique + antiquing + worn_edges + distress_level + "_" + carcass_exterior_species + carcass_exterior_finish_code +
            carcass_exterior_glaze_color + carcass_exterior_glaze_technique + "-" + carcass_interior_species + carcass_interior_finish_code + carcass_interior_glaze_color + carcass_interior_glaze_technique + "_" + drawer_boxes);
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
                unloadPage('dashboard');
            } else {
                loadPage('employees');
            }
        })
        .on("click", "#nav_pricing", function() {
            unloadPage('pricing');
        })
        .on("click", "#nav_workcenter", function() {
            unloadPage('workcenter');
        })
        .on("click", "#nav_timecard", function() {
            //unloadPage('timecard');

            var start = Math.round(new Date().getTime()/1000);
            var end = Math.round(new Date().getTime()/1000);

            window.open("/print/timecard.php?start_date=" + start + "&end_date=" + end + "&employee=23", "_blank");
        })
        .on("click", "#nav_job-management", function() {
            unloadPage('job_management');
        })
        .on("click", "#nav_employees", function() {
            <?php
            if($_SESSION['userInfo']['account_type'] > 4) {
            ?>
                if(shop_logged_in) {
                    $.post("/ondemand/account_actions.php?action=logout", function(data) {
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

            unloadPage('employees');
        })
        .on("click", "#nav_tasks", function() {
            unloadPage('tasks');
        })
        .on("click", "#nav_vin", function() {
            unloadPage('build_a_vin');
        })
        .on("click", "#nav_so_list", function() {
            unloadPage('so_list');
        })
        .on("click", "#nav_sales_list", function() {
            unloadPage('sales_list');
        })
        // -- End Navigation --

        // -- Dashboard --
        .on("change", "#viewing_queue", function() {
            updateQueuedJobs();
        })
        .on("click", ".view_quote_info", function(e) {
            e.stopPropagation();
        })
        .on("click", ".view_so_info", function() {
            var id = $(this).attr("id");
            $("#global_search").val(id).trigger("keyup");
        })

        .on("click", ".start-operation", function(e) {
            e.stopPropagation();

            opFull = $(this).closest('tr').find('td').eq(4).html();
            op_id = $(this).attr("id");

            if(opFull === '000: Non-Billable' || opFull === '000: On The Fly') {
                $.post("/ondemand/op_actions.php?action=get_start_info", {opID: op_id, op: opFull}, function(data) {
                    $("#modalStartJob").html(data);
                }).done(function() {
                    $("#modalStartJob").modal();
                }).fail(function() { // if we're receiving a header error
                    $("body").append(data); // echo an error and log it
                });
            } else {
                $.post("/ondemand/op_actions.php?action=start_operation", {operation: opFull, id: op_id}, function(data) {
                    $('body').append(data);

                    updateQueuedJobs();

                    $.post("/html/view_notes.php", {queueID: op_id}, function(data) {
                        $("#modalViewNotes").html(data).modal("show");
                    });
                });
            }

            unsaved = false;
        })
        .on("click", "#start_job", function() {
            var other_notes_field = $("#other_notes_field").val(); // non-billable "other" section
            var notes_field = $("#notes_field").val(); // Cabinet Vision task or anything with JUST a notes field

            if($("#other_subtask").is(":checked")) { // if this is a subtask "other" section then we have to verify the notes
                if (other_notes_field.length >= 3) { // and the length of notes is greater than 3
                    $.post("/ondemand/op_actions.php?action=start_operation", {id: op_id, operation: opFull, subtask: "Other", notes: other_notes_field}, function (data) {
                        $("body").append(data);

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

                $.post("/ondemand/op_actions.php?action=start_operation", {id: op_id, operation: opFull, subtask: subtask,
                notes: notes_field, otf_so_num: otf_so_num, otf_room: otf_room, otf_op: otf_op, otf_notes: otf_notes, otf_iteration: otf_iteration}, function(data) {
                    updateQueuedJobs();

                    $('body').append(data);

                    $("#modalStartJob").modal('hide');
                });
            }

            unsaved = false;
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

            $.post("/ondemand/op_actions.php?action=get_pause_info", {opID: op_id}, function(data) {
                $("#modalUpdateJob").html(data).modal();
            });
        })
        .on("click", "#pause_op", function() {
            $.post("/ondemand/op_actions.php?action=pause_operation", {opID: op_id, notes: $("#notes").val(), qty: $("#qtyCompleted").val()}, function(data) {
                updateQueuedJobs();

                $("body").append(data);
                $("#modalUpdateJob").modal('hide');
            });
        })

        .on("click", ".complete-operation", function(e) {
            e.stopPropagation();

            op_id = $(this).attr("id");

            $.post("/ondemand/op_actions.php?action=get_stop_info", {opID: op_id}, function(data) {
                $("#modalUpdateJob").html(data).modal();
            });

            unsaved = false;
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

            // formdata required because now we're attaching files
            var formData = new FormData();
            formData.append('opID', op_id);
            formData.append('opnum', opFull);
            formData.append('notes', notes);
            formData.append('qty', qty_compl);
            formData.append('rework_reqd', rework);
            formData.append('rework_reason', rw_reason);
            formData.append('attachment', $("input[type='file']")[0].files[0]);

            $.ajax({
                url: "/ondemand/op_actions.php?action=complete_operation",
                //url: "/admin/test.php",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('body').append(data);
                    $("#modalUpdateJob").modal('hide');

                    active_table.ajax.reload(null,false);
                }
            });

            unsaved = false;
        })

        .on("click", ".op-notes", function(e) {
            e.stopPropagation();

            $.post("/html/view_notes.php", {queueID: $(this).attr("id")}, function(data) {
                $("#modalViewNotes").html(data).modal("show");
            });
        })
        // -- End Dashboard --

        // -- Workcenter --
        .on("click", ".wc-view-queue-so", function() {
            var id = $(this).attr("id");
            $("#global_search").val(id).trigger("keyup");
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

            $.post("/ondemand/account_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
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
            $.post("/ondemand/account_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
                console.log(data);

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
        .on("click", ".clock_out", function(e) {
            var id = $(this).data("id");

            console.log("Clicked to clock out!");

            e.stopPropagation();

            $.post("/ondemand/account_actions.php?action=clock_out", {user_id: id}, function(data) {
                $("body").append(data);
            });
        })
        // -- End Employees --

        // -- VIN Page --
        .on("blur", "#so_num", function() {
            vin_sonum = $(this).val();

            $.post("/ondemand/livesearch/build_a_vin.php?search=room&so_num=" + vin_sonum, function(data) {
                $("#room").html(data);
            });
        })
        .on("click blur", "#room", function() {
            $.post("/ondemand/livesearch/build_a_vin.php?search=iteration&so_num=" + vin_sonum + "&room=" + $("#room option:selected").val(), function (data) {
                $("#iteration").html(data);
            });
        })

        .on("click", ".print-sample", function() {
            var room_id = $(this).attr("id");

            calcVin(room_id);

            var formInfo = $("#room_edit_" + room_id).serialize();

            $.post("/ondemand/room_actions.php?action=update_room&" + formInfo + "&roomid=" + room_id, function(data) {
                $('body').append(data);
            }).done(function() {
                setTimeout(function() {
                    window.open("/print/sample.php?room_id=" + room_id);
                }, 500);
            });

            unsaved = false;
        })
        .on("change keydown", ".vin_code_calc", function() {
            calcVin(active_room_id);
        })
        // -- End VIN Page --

        // -- Task Page --
        .on("click", ".display-task-info", function() {
            $.post("/ondemand/admin/tasks.php?action=get_task_info", {task_id: $(this).attr("id")}, function(data) {
                $("#modalTaskInfo").html(data);
            }).done(function() {
                $("#modalTaskInfo").modal();
            }).fail(function(data) { // if we're receiving a header error
                $("body").append(data); // echo an error and log it
            });
        })
        .on("click", "#update_task_btn", function() {
            var form_info = $("#task_details").serialize();
            var task_id = $(this).data("taskid");
            var s_text_1 = $("#split-text-1").val();
            var s_text_2 = $("#split-text-2").val();

            $.post("/ondemand/admin/tasks.php?action=update_task&" + form_info, {task_id: task_id, s_text_1: s_text_1, s_text_2: s_text_2}, function(data) {
                $("body").append(data);
                $("#modalTaskInfo").modal('hide');

                unsaved = false;
            });
        })
        .on("click", "#split_task_btn", function() {
            $(".task_hide").toggle(100);
            $("#split_body").toggle(250);

            setTimeout(function() {
                if($("#split_body").is(":visible")) {
                    $("#split_task_enabled").val("1");
                } else {
                    $("#split_task_enabled").val("0");
                }
            }, 250);
        })
        .on("click", "#create_op_btn", function() {
            var form_info = $("#task_details").serialize();
            var task_id = $(this).data("taskid");

            $.post("/ondemand/admin/tasks.php?action=create_operation&" + form_info, {task_id: task_id}, function(data) {
                $("body").append(data);
                $("#modalTaskInfo").modal('hide');

                unsaved = false;
            });
        })
        // -- End Task Page --

        // -- Room Page --
        .on("change", "#display_log", function() {
            if($(this).is(":checked")) {
                $(".room_note_log").show();
            } else {
                $(".room_note_log").hide();
            }
        })
        // -- End Room Page --

        // -- Sales List Page --
        .on("change", "#job_status_lost", function() {
            if($(this).is(":checked")) {
                $(".room_lost").show();
            } else {
                $(".room_lost").hide();
            }
        })
        .on("change", "#job_status_quote", function() {
            if($(this).is(":checked")) {
                $(".room_quote").show();
            } else {
                $(".room_quote").hide();
            }
        })
        .on("change", "#job_status_job", function() {
            if($(this).is(":checked")) {
                $(".room_job").show();
            } else {
                $(".room_job").hide();
            }
        })
        .on("change", "#job_status_completed", function() {
            if($(this).is(":checked")) {
                $(".room_completed").show();
            } else {
                $(".room_completed").hide();
            }
        })
        .on("change", ".hide_dealer", function() {
            var dealer_id = $(this).data("dealer-id");

            if($(this).is(":checked")) {
                $(".dealer_" + dealer_id).show();
            } else {
                $(".dealer_" + dealer_id).hide();
            }
        })
        .on("click", ".sales_list_visible", function(e) {
            e.stopPropagation();

            var hide = $(this).data("identifier");

            $("." + hide).hide();

            $.post("/ondemand/display_actions.php?action=hide_sales_list_id&id=" + hide);
        })
        .on("click", ".sales_list_hidden", function(e) {
            e.stopPropagation();

            var show = $(this).data("identifier");
            $(this).removeClass('btn-primary-outline sales_list_hidden').addClass('btn-primary sales_list_visible').children('i').removeClass('zmdi-eye').addClass('zmdi-eye-off');

            $.post("/ondemand/display_actions.php?action=show_sales_list_id&id=" + show);
        })
        // -- End Sales List Page --
    ;

    var clockInterval = setInterval(function() {
        var time = new Date();
        time = time.toLocaleTimeString();

        $("#clock").html(time);
    }, 1000);
</script>

<?php
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>