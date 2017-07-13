<?php
require 'includes/header_start.php';
require 'includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<!-- Date & Clock -->
<link href="/assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>

<!-- date picker -->
<link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
<script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

<!-- tinysort -->
<script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

<!-- input masking -->
<script type="text/javascript" src="/assets/plugins/jquery.mask.min.js"></script>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
        <ul class="nav nav-tabs m-b-10" id="mainTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="production-tab" data-toggle="tab" href="#production" role="tab" aria-controls="production" aria-expanded="true">Production</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="calendar-tab" data-toggle="tab" href="#calendar" role="tab" aria-controls="calendar">Calendar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="email-tab" data-toggle="tab" href="#email" role="tab" aria-controls="email">Email</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="text-tab" data-toggle="tab" href="#text" role="tab" aria-controls="text">Text</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="wall-tab" data-toggle="tab" href="#wall" role="tab" aria-controls="wall">Wall</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="search-results-tab" data-toggle="tab" href="#search-results" role="tab" aria-controls="search-results" style="display: none;">Search Results...</a>
            </li>
        </ul>
        <div class="tab-content" id="mainTabContent">
            <div role="tabpanel" class="tab-pane fade in active" id="production" aria-labelledby="home-tab">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card-box table-responsive">
                                <div class="col-md-12 workcenter-table">
                                    <h4>Jobs in Queue</h4>

                                    <table id="jobs_in_queue_global_table" class="table table-striped table-bordered" width="100%">
                                        <thead>
                                        <tr>
                                            <th>SO#</th>
                                            <th>Room Name</th>
                                            <th>Bracket</th>
                                            <th>Operation</th>
                                            <th>Release Date</th>
                                        </tr>
                                        </thead>
                                        <tbody id="jiq_table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card-box table-responsive">
                                <div class="col-md-12">
                                    <h4>Active Jobs</h4>

                                    <table id="active_jobs_global_table" class="table table-striped table-bordered" width="100%">
                                        <thead>
                                        <tr>
                                            <th>SO#</th>
                                            <th>Room Name</th>
                                            <th>Bracket</th>
                                            <th>Operation</th>
                                            <th>Individual</th>
                                            <th>Started/Resumed</th>
                                        </tr>
                                        </thead>
                                        <tbody id="active_jobs_table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card-box table-responsive">
                                <div class="col-md-12">
                                    <h4>Recently Completed Jobs</h4>

                                    <table id="recently_completed_jobs_global_table" class="table table-striped table-bordered" width="100%">
                                        <thead>
                                        <tr>
                                            <th>SO#</th>
                                            <th>Room Name</th>
                                            <th>Bracket</th>
                                            <th>Operation</th>
                                            <th>Completed</th>
                                        </tr>
                                        </thead>
                                        <tbody id="recently_completed_table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- modal -->
                        <div id="viewJobInfo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewJobInfoLabel" aria-hidden="true">

                        </div>
                        <!-- /.modal -->
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="calendar" role="tabpanel" aria-labelledby="profile-tab">
                <p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla
                    single-origin coffee squid. Exercitation +1 labore velit, blog sartorial
                    PBR leggings next level wes anderson artisan four loko farm-to-table
                    craft beer twee. Qui photo booth letterpress, commodo enim craft beer
                    mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                    organic, assumenda labore aesthetic magna delectus mollit. Keytar
                    helvetica VHS salvia yr, vero magna velit sapiente labore stumptown.
                    Vegan fanny pack odio cillum wes anderson 8-bit, sustainable jean shorts
                    beard ut DIY ethical culpa terry richardson biodiesel. Art party
                    scenester stumptown, tumblr butcher vero sint qui sapiente accusamus
                    tattooed echo park.</p>
            </div>
            <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                <p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out
                    mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade.
                    Messenger bag gentrify pitchfork tattooed craft beer, iphone skateboard
                    locavore carles etsy salvia banksy hoodie helvetica. DIY synth PBR
                    banksy irony. Leggings gentrify squid 8-bit cred pitchfork. Williamsburg
                    banh mi whatever gluten-free, carles pitchfork biodiesel fixie etsy
                    retro mlkshk vice blog. Scenester cred you probably haven't heard of
                    them, vinyl craft beer blog stumptown. Pitchfork sustainable tofu synth
                    chambray yr.</p>
            </div>
            <div class="tab-pane fade" id="text" role="tabpanel" aria-labelledby="text-tab">
                <p>Trust fund seitan letterpress, keytar raw denim keffiyeh etsy art party
                    before they sold out master cleanse gluten-free squid scenester freegan
                    cosby sweater. Fanny pack portland seitan DIY, art party locavore wolf
                    cliche high life echo park Austin. Cred vinyl keffiyeh DIY salvia PBR,
                    banh mi before they sold out farm-to-table VHS viral locavore cosby
                    sweater. Lomo wolf viral, mustache readymade thundercats keffiyeh craft
                    beer marfa ethical. Wolf salvia freegan, sartorial keffiyeh echo park
                    vegan.</p>
            </div>
            <div class="tab-pane fade" id="wall" role="tabpanel" aria-labelledby="wall-tab">
                <p>Wall tab.</p>
            </div>
            <div class="tab-pane fade" id="search-results" role="tabpanel" aria-labelledby="search-results-tab">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered tablesorter" id="search_results_global_table">
                                <thead>
                                <tr>
                                    <th colspan="2">SO#</th>
                                    <th>PROJECT/CUSTOMER PO</th>
                                    <th>SALESPERSON</th>
                                    <th>DEALER/CONTRACTOR</th>
                                    <th>ACCOUNT TYPE</th>
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
        </div>
    </div>
</div>
</div>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Chart -->
<script src="/assets/plugins/amcharts/amcharts.js"></script>
<script src="/assets/plugins/amcharts/serial.js"></script>
<script src="/assets/plugins/amcharts/gantt.js"></script>
<script src="/assets/plugins/amcharts/themes/custom.js"></script>
<script src="/assets/plugins/amcharts/plugins/dataloader/dataloader.min.js" type="text/javascript"></script>

<!-- Date & Clock picker -->
<script src="/assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<script>
    var timer;

    $("body")
        .on("click", ".wc-edit-queue", function() {
            $("#global_search").val($(this).attr("id")).trigger("keyup");
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
        .on("keyup", "#global_search", function() {
            var searchTab = $("#search-results-tab");
            var input = $("#global_search");
            var searchDefaults = '<tr><td colspan="7" class="text-md-center"><i class="fa fa-3x fa-spin fa-spinner" style="width: auto;margin-right: 10px;"></i></td></tr>';
            var searchEmpty = '<tr><td colspan="7">No results found.</td></tr>';

            if(input.val().length > 0) {
                searchTab.show().tab("show");

                clearTimeout(timer);

                timer = setTimeout(function () {
                    if (input.val().length >= 1) {
                        $.post("/ondemand/livesearch/search_results.php?search=general", {find: input.val()}, function (data) {
                            $("#search_results_table").html(data);
                            $("#search_results_global_table").trigger("update");

                            if (data !== '') {
                                $('[data-toggle="tooltip"]').tooltip(); // enable tooltips

                                // setup field masks
                                $(".mask-zip").mask('00000-0000');
                                $(".mask-phone").mask('(000) 000-0000');

                                // setup date picker
                                $(".delivery_date").datepicker({
                                    autoclose: true,
                                    todayHighlight: true
                                }).mask('00/00/0000');
                            } else {
                                $("#search_results_table").html(searchEmpty);
                            }
                        });
                    } else {
                        if ($("#mainTab").find(".active").text() === 'Search Results...') {
                            $("#production-tab").tab("show");
                        }

                        searchTab.hide();
                        $("#search_results_table").html(searchDefaults);
                    }
                }, 400);
            } else {
                if ($("#mainTab").find(".active").text() === 'Search Results...') {
                    $("#production-tab").tab("show");
                }

                searchTab.hide();
                $("#search_results_table").html(searchDefaults);
            }
        })
        .on("click", "#submit_new_customer", function() {
            var cuData;

            if($("input[name='cu_type']:checked").val() === 'retail') {
                cuData = $("#add_retail_customer").serialize();
            } else {
                cuData = $("#add_distributor_cc").serialize();
            }

            $.post("/ondemand/shopfloor/job_actions.php?action=add_customer&" + cuData, {new_so_num: $("#new_so_num").val()}, function(data) {
                $("body").append(data);

                $("#modalAddCustomer").modal('hide');
            });
        })
        .on("change", "input[name='cu_type']", function() {
            var add_rc = $("#add_retail_customer");
            var add_dist = $("#add_distributor_cc");

            switch($(this).val()) {
                case 'retail':
                    add_rc.show();
                    add_dist.hide();

                    break;
                case 'distribution':
                    add_rc.hide();
                    add_dist.show();

                    break;
                case 'cutting':
                    add_rc.hide();
                    add_dist.show();

                    break;
                default:
                    break;
            }
        })
        .on("click", "[id^=edit_so_]", function(e) {
            e.stopPropagation();

            active_so_num = $(this).attr("id").replace('edit_so_', '');

            $("[id^=tr_single_room_]").hide(100);
            $("[id^=tr_room_]").hide(100);

            $("[id^=tr_edit_so_]").not(this).hide(100);
            $("[id^=div_edit_so_]").not(this).hide();

            $("[id^=show_room_]").not(this).removeClass("active_room_line");
            $("[id^=show_single_room_]").removeClass("active_room_line");

            $("#show_room_" + active_so_num).addClass("active_room_line");

            $("#tr_edit_so_" + active_so_num).show();
            $("#div_edit_so_" + active_so_num).slideDown(250);
        })
        .on("click", "[id^=show_room_]", function() {
            $("[id^=show_room_]").removeClass("active_room_line");
            $("[id^=show_single_room_]").removeClass("active_room_line");
            $(".add_room_trigger").removeClass("active_room_line");

            $(this).addClass("active_room_line");

            active_so_num = $(this).attr("id").replace('show_room_', '');

            $("[id^=tr_single_room_]").finish().hide(250);
            $("[id^=div_single_room_]").finish().hide(100);
            $("[id^=tr_edit_so_]").finish().hide(250);
            $("[id^=div_edit_so_]").finish().hide(100);
            $("[id^=tr_add_single_room_info_]").finish().hide(250);
            $("[id^=div_add_single_room_info_]").finish().hide(100);
            $("[id^=tr_room_]").not(this).finish().hide(100);
            $("[id^=div_room_]").finish().hide(250);

            $("#tr_room_" + active_so_num).show();
            $("#div_room_" + active_so_num).slideDown(250);
        })
        .on("click", "[id^=show_single_room_]", function() {
            $("[id^=show_single_room_]").removeClass("active_room_line");
            $(".add_room_trigger").removeClass("active_room_line");

            $(this).addClass("active_room_line");

            active_room_id = $(this).attr("id").replace('show_single_room_', '');

            $("[id^=tr_single_room_]").not(this).hide(100);

            $("[id^=tr_room_bracket_]").hide(250);
            $("[id^=div_room_bracket_]").hide(100);
            $("[id^=tr_add_single_room_info_]").hide(250);
            $("[id^=div_add_single_room_info_]").hide(100);

            $("#tr_single_room_" + active_room_id).show();
            $("#div_single_room_" + active_room_id).slideDown(250);
        })
        .on("click", "[id^=manage_bracket_]", function(e) {
            e.stopPropagation();

            active_room_id = $(this).attr("id").replace('manage_bracket_', '');

            $("[id^=show_single_room_]").removeClass("active_room_line");
            $(".add_room_trigger").removeClass("active_room_line");
            $("#show_single_room_" + active_room_id).addClass("active_room_line");

            $("[id^=tr_single_room_]").hide(250);
            $("[id^=div_single_room_]").hide(100);
            $("[id^=tr_add_single_room_info_]").hide(250);
            $("[id^=div_add_single_room_info_]").hide(100);

            $("[id^=tr_room_bracket_]").not(this).hide(250);
            $("[id^=div_room_bracket_]").not(this).hide(100);

            $("#tr_room_bracket_" + active_room_id).show();
            $("#div_room_bracket_" + active_room_id).slideDown(250);
        })
        .on("click", "#btn_add_acct", function() {
            $.post('/ondemand/shopfloor/new_customer.php', function(data) {
                $("#modalAddCustomer").html(data).modal('show');
            });
        })
        .on("click", ".activate_op", function() {
            var opid = $(this).data("opid");
            var roomid = $(this).data("roomid");
            var soid = $(this).data("soid");
            var opnum = $(this).parent().data("opnum");
            var bracket = $(this).closest('ul').data("bracket");
            var info;
            var deactivate = '';

            if(String(opnum).slice(-2) !== '98') {
                deactivate = '<span class="pull-right cursor-hand text-md-center deactivate_op" data-opid="' + opid + '" data-roomid="' + roomid + '" data-soid="' + soid + '"> <i class="fa fa-arrow-circle-right" style="width: 18px;"></i></span>';
            }

            info = '<li class="active_ops_' + roomid + '" id="active_ops_' + roomid +'" data-opnum="' + opnum + '" data-opid="' + opid + '">';
            info += '<input type="radio" name="' + bracket + '" id="op_' + opid + '_room_' + roomid +'" value="' + opid + '">';
            info += '<label for="op_' + opid + '_room_' + roomid + '">' + $(this).parent().text().trim() + '</label>';
            info += deactivate;
            info += "</li>";

            $("#activeops_" + roomid + "_" + bracket).append(info);
            tinysort("ul#activeops_" + roomid + "_" + bracket + ">li",{data:'opnum'});

            $(this).parent().remove();
        })
        .on("click", ".deactivate_op", function() {
            var opid = $(this).data("opid");
            var roomid = $(this).data("roomid");
            var soid = $(this).data("soid");
            var opnum = $(this).parent().data("opnum");
            var bracket = $(this).closest('ul').data("bracket");
            var info;

            info = '<li class="inactive_ops_' + roomid + '" id="inactive_ops_' + roomid +'" data-opnum="' + opnum + '" data-opid="' + opid + '">';
            info += '<span class="pull-left cursor-hand text-md-center activate_op" data-opid="' + opid + '" data-roomid="' + roomid + '" data-soid="' + soid + '" style="height:18px;width:18px;"> <i class="fa fa-arrow-circle-left" style="margin:5px;"></i></span>';
            info += '<label for="op_' + opid + '_room_' + roomid + '">' + $(this).parent().text().trim() + '</label>';
            info += "</li>";

            $("#inactiveops_" + roomid + "_" + bracket).append(info);
            tinysort("ul#inactiveops_" + roomid + "_" + bracket + ">li",{data:'opnum'});

            $(this).parent().remove();
        })
        .on("click", ".add_room_trigger", function() {
            $("[id^=show_single_room_]").removeClass("active_room_line");
            $(".add_room_trigger").removeClass("active_room_line");

            $(this).addClass("active_room_line");

            active_so_num = $(this).data('sonum');

            $("[id^=tr_room_bracket_]").hide(100);
            $("[id^=tr_single_room_]").hide(100);
            $("[id^=tr_add_single_room_info_]").hide(250);
            $("[id^=div_add_single_room_info_]").hide(100);

            $("#tr_add_single_room_info_" + active_so_num).show();
            $("#div_add_single_room_info_" + active_so_num).slideDown(250);

        })
        .on("click", "[id^=add_room_save_]", function() {
            var save_info = $("#form_add_room_" + active_so_num).serialize();

            $.post("/ondemand/shopfloor/gen_actions.php?action=insert_new_room&" + save_info, function(data) {
                $("body").append(data);
            });
        })
        .on("change click", ".days-to-ship", function() {
            var dts = $(this).val();
            var type = $(this).data('type');
            var room_letter = $(this).data('room');

            $.post("/ondemand/shopfloor/gen_actions.php?action=calc_del_date", {days_to_ship: dts}, function(data) {
                if(type === 'add') {
                    $("#delivery_date_add_" + active_so_num).val(data).removeClass('job-color-red job-color-green job-color-yellow job-color-orange').addClass('job-color-' + dts.toLowerCase()).data("datepicker").setDate(data);
                } else if(type === 'iteration') {
                    $("#iteration_del_date_" + room_letter + "_so_" + active_so_num).val(data).removeClass('job-color-red job-color-green job-color-yellow job-color-orange').addClass('job-color-' + dts.toLowerCase()).data("datepicker").setDate(data);
                } else {
                    $("#edit_del_date_" + room_letter + "_so_" + active_so_num).val(data).removeClass('job-color-red job-color-green job-color-yellow job-color-orange').addClass('job-color-' + dts.toLowerCase()).data("datepicker").setDate(data);
                }
            });
        })
        .on("change", ".dealer_code", function() {
            $.post("/ondemand/shopfloor/gen_actions.php?action=get_dealer_info&dealer_code=" + $(this).val(), function(data) {
                if(data !== '') {
                    var dealer = JSON.parse(data);

                    $("#add_room_account_type_" + active_so_num).val(dealer.account_type);
                    $("#add_room_dealer_" + active_so_num).val(dealer.dealer_name);
                    $("#add_room_contact_" + active_so_num).val(dealer.contact);
                    $("#add_room_phone_num_" + active_so_num).val(dealer.phone);
                    $("#add_room_email_" + active_so_num).val(dealer.email);
                    $("#add_room_salesperson_" + active_so_num).val(dealer.contact);
                    $("#add_room_shipping_addr_" + active_so_num).val(dealer.shipping_address);
                    $("#add_room_shipping_city_" + active_so_num).val(dealer.shipping_city);
                    $("#add_room_shipping_state_" + active_so_num).val(dealer.shipping_state);
                    $("#add_room_shipping_zip_" + active_so_num).val(dealer.shipping_zip);
                }
            });
        })
        .on("click", ".edit_room_save", function(e) {
            e.stopPropagation();

            var edit_info = $("#room_edit_" + active_room_id).serialize();

            $.post("/ondemand/shopfloor/gen_actions.php?action=update_room&" + edit_info, function(data) {
                $('body').append(data);
            });
        })
        .on("click", ".add_iteration", function(e) {
            e.stopPropagation();

            active_room_id = $(this).data('roomid');

            $("[id^=show_single_room_]").removeClass("active_room_line");
            $(".add_room_trigger").removeClass("active_room_line");
            $("#show_single_room_" + active_room_id).addClass("active_room_line");

            $("[id^=tr_single_room_]").hide(250);
            $("[id^=div_single_room_]").hide(100);
            $("[id^=tr_add_single_room_info_]").hide(250);
            $("[id^=div_add_single_room_info_]").hide(100);

            $("[id^=tr_iteration_]").not(this).hide(250);
            $("[id^=div_iteration_]").not(this).hide(100);

            $("#tr_iteration_" + active_room_id).show();
            $("#div_iteration_" + active_room_id).slideDown(250);
        })
        .on("click", ".save_bracket", function() {
            var active_ops = $(".active_ops_" + active_room_id).map(function() { return $(this).data("opid"); }).get();
            var selected_ops = $("#form_bracket_" + active_room_id).serialize();

            active_ops = JSON.stringify(active_ops);

            console.log(selected_ops);

            $.post("/ondemand/shopfloor/gen_actions.php?action=save_active_ops&" + selected_ops, {active_ops: active_ops, roomid: active_room_id}, function(data) {
                $('body').append(data);
            });
        })
        .on("click", ".save_so", function() {
            var so_info = $("#form_so_" + active_so_num).serialize();

            $.post('/ondemand/shopfloor/gen_actions.php?action=save_so&' + so_info, function(data) {
                $("body").append(data);
            });
        })
        .on("click", ".iteration_save", function(e) {
            e.stopPropagation();

            var iteration_info = $("#room_add_iteration_" + active_room_id).serialize();

            $.post("/ondemand/shopfloor/gen_actions.php?action=add_iteration&" + iteration_info, function(data) {
                $('body').append(data);
            });
        });

    $("#job_started").datetimepicker({
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    });

    $("#job_completed").datetimepicker({
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    });

    var jiq_table = $("#jobs_in_queue_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/workcenter.php?action=display_jiq",
        "pageLength": 25,
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand wc-edit-queue");
        },
        "paging": false
    });

    var active_table = $("#active_jobs_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/workcenter.php?action=display_active_jobs",
        "pageLength": 25,
        "paging": false
    });

    var completed_table = $("#recently_completed_jobs_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/workcenter.php?action=display_recently_completed",
        "pageLength": 50,
        "order": [[4, "desc"]]
    });

    setInterval(function() {
        jiq_table.ajax.reload(null,false);
        active_table.ajax.reload(null,false);
        completed_table.ajax.reload(null,false);
    }, 5000);

    var ganttChart = AmCharts.makeChart( "job_status_gantt", {
        "type": "gantt",
        "theme": "custom",
        "marginRight": 20,
        "marginTop": 10,
        "marginBottom": 10,
        "period": "mm",
        "dataDateFormat":"YYYY-MM-DD",
        "balloonDateFormat": "JJ:NN",
        "columnWidth": 0.5,
        "valueAxis": {
            "type": "date"
        },
        "brightnessStep": 10,
        "graph": {
            "fillAlphas": 1,
            "balloonText": "<b>[[task]]</b>: [[open]]<br /> <small style='text-align: center;'>([[duration]] <i>mins</i>)</small>",
            "cornerRadiusTop": 10,
            "labelText": "[[task]]",
            "labelPosition": "middle"
        },
        "rotate": true,
        "categoryField": "category",
        "segmentsField": "segments",
        "colorField": "color",
        "startDate": "2016-04-19",
        "startField": "start",
        "endField": "end",
        "durationField": "duration",
        "dataLoader": {
            "url": "/ondemand/gantt_chart.php"
        },
        "valueScrollbar": {
            "autoGridCount":true,
            "color": "#132882"
        },
        "chartCursor": {
            "cursorColor":"#55bb76",
            "valueBalloonsEnabled": false,
            "cursorAlpha": 0,
            "valueLineAlpha":0.5,
            "valueLineBalloonEnabled": true,
            "valueLineEnabled": true,
            "zoomable":false,
            "valueZoomable":true
        },
        "export": {
            "enabled": true
        },
        "balloon": {
            "fixedPosition": false
        }
    } );
</script>

<?php 
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>