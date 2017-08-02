// Goal: to ensure the loading of Global Search input field
var timer;

$("body")
    .on("keyup", "#global_search", function(e) {
        if(e.keyCode === 13) {
            e.preventDefault();
            return false;
        }

        clearIntervals();

        var searchDisplay = $("#search_display");
        var mainDisplay = $("#main_display");
        var input = $("#global_search");
        var searchTable = $("#search_results_table");
        var loadingResults = '<tr><td colspan="7" class="text-md-center"><span id="global_search_status"><i class="fa fa-3x fa-spin fa-spinner" style="width: auto;margin-right: 10px;"></i></span></td></tr>';
        var searchEmpty = '<tr><td colspan="7">No results found.</td></tr>';

        searchTable.html(loadingResults);

        if(input.val().length > 0) {
            mainDisplay.hide();
            searchDisplay.show();

            clearTimeout(timer);

            timer = setTimeout(function () {
                if (input.val().length >= 1) {
                    $.post("/ondemand/livesearch/search_results.php?search=general", {find: input.val()}, function (data) {
                        searchTable.html(data);
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
                            searchTable.html(searchEmpty);
                        }
                    });
                } else {
                    mainDisplay.show();
                    searchDisplay.hide();
                }
            }, 100);
        } else {
            mainDisplay.show();
            searchDisplay.hide();
        }
    })
    .on("click", "#btn_search_to_main", function() {
        backFromSearch();
    })
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
    .on("click", "[id^=show_single_room_]", function(e) {
        e.stopPropagation();

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

        setTimeout(function() {
            $(window).scrollTo($("#show_single_room_" + active_room_id), 800, {offset: -125});
        }, 300);
    })
    .on("click", "[id^=manage_bracket_]", function(e) {
        e.stopPropagation();

        console.log("Managing the bracket");

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

        setTimeout(function() {
            $(window).scrollTo($("#show_single_room_" + active_room_id), 800, {offset: -100});
        }, 300);
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
    })
    .on("click", "#global_search_button", function() {
        $("#global_search").trigger("keyup");
    });