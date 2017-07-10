<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- tinysort -->
<script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

<!-- input masking -->
<script type="text/javascript" src="/assets/plugins/jquery.mask.min.js"></script>

<!-- date picker -->
<link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
<script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

<!-- Search results box -->
<div class="row">
    <div class="col-md-12">
        <div class="card-box" id="search_results_card">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 form-inline">
                        <div class="form-group">
                            <label for="global_search">Lookup: </label>
                            <input class="form-control" type="text" placeholder="Search..." id="global_search" name="global_search" style="width: 250px;" value="<?php echo $_GET['lookup']; ?>" />
                            <button class="btn waves-effect btn-primary" id="btn_add_acct" style="margin: 0 0 0 6px;"> <i class="zmdi zmdi-account-add m-r-5"></i><span>Add SO</span></button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <table style="display: none;" class="table table-bordered tablesorter" id="search_results_global_table">
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
                                <td colspan="7">No results to display</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End search results box -->

<!-- Add Customer modal -->
<div id="modalAddCustomer" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddCustomerLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<script>
    var active_so_num = null;
    var active_room_id = null;
    var timer;
    var term = '<?php echo $_GET['lookup']; ?>';

    // check for default search term
    if(term !== '') {
        // if there is a lookup code provided
        $.post("/ondemand/livesearch/search_results.php?search=general", {find: term}, function(data) {
            $("#search_results_table").html(data);
            $("#search_results_global_table").trigger("update");

            if(data !== '') {
                $("#search_results_global_table").show();
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
                $("#search_results_global_table").hide();
            }
        });
    }

    $("body")
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

    $("#global_search").on("keyup", function() {
        console.log("Global Search is triggering.");

        var input = $(this);

        clearTimeout(timer);

        timer = setTimeout(function() {
            if(input.val().length >= 1) {
                $.post("/ondemand/livesearch/search_results.php?search=general", {find: input.val()}, function(data) {
                    console.log(data);

                    $("#search_results_table").html(data);
                    $("#search_results_global_table").trigger("update");

                    if(data !== '') {
                        $("#search_results_global_table").show();
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
                        $("#search_results_global_table").hide();
                    }
                });
            } else {
                $("#search_results_global_table").hide();
            }
        }, 500);
    });
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>