/*jshint strict: false*/

// Goal: to ensure the loading of Global Search input field
var timer;
var active_so_num;
var active_room_id;
var thisClick;

function toggleDisplay(roomid) {
    $("[id^=manage_bracket_]").removeClass("active_room_line");
    $("#manage_bracket_" + active_room_id).addClass("active_room_line");

    $("[id^=tr_iteration_]").not("#tr_iteration_" + roomid).hide(250);
    $("[id^=div_iteration_]").not("#div_iteration_" + roomid).hide(100);
    $("[id^=tr_single_room_]").hide(250);
    $("[id^=div_single_room_]").hide(100);
    $("[id^=tr_add_single_room_info_]").hide(250);
    $("[id^=div_add_single_room_info_]").hide(100);
    $("[id^=tr_attachments_]").finish().hide(250);
    $("[id^=div_attachments_]").finish().hide(100);
    $("[id^=tr_edit_so_]").finish().hide(250);
    $("[id^=div_edit_so_]").finish().hide(100);

    $(".tr_room_actions").hide(300).find('div').slideUp(150).html('');
    $(".add_room").hide(300).find('div').slideUp(150).html('');
}

function scrollLocation(container) {
    setTimeout(function() {
        $(window).scrollTo($(container), 800, {offset: -125});
    }, 300);
}

$("body")
    .on("keyup", "#global_search", function() {
        checkTransition(function() {
            clearIntervals();

            var searchDisplay = $("#search_display");
            var mainDisplay = $("#main_display");
            var input = $("#global_search");
            var searchTable = $("#search_results_table");
            var loadingResults = '<tr><td colspan="7" class="text-md-center"><span id="global_search_status"><i class="fa fa-3x fa-spin fa-spinner" style="width: auto;margin-right: 10px;"></i></span></td></tr>';
            var searchEmpty = '<tr><td colspan="7">No results found.</td></tr>';

            searchTable.html(loadingResults);

            if(input.val().length >= 3) {
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
        });
    })
    .on("keyup keypress", "#global_search", function(e) {
        if(e.keyCode === 13) {
            e.preventDefault();

            $("#global_search").trigger("keyup");


            return false;
        }
    })
    .on("click", "#global_search_button", function() {
        $("#global_search").trigger("keyup");
    })

    .on("click", "#btn_search_to_main", function() {
        checkTransition(function() {
            backFromSearch();
        });
    })

    .on("click", ".wc-edit-queue", function() {
        $("#global_search").val($(this).attr("id")).trigger("keyup");
    })

    .on("click", "[id^=edit_so_]", function(e) {
        thisClick = this;

        e.stopPropagation();

        checkTransition(function() {
            active_so_num = $(thisClick).attr("id").replace('edit_so_', '');

            toggleDisplay();

            $("#tr_edit_so_" + active_so_num).show();
            $("#div_edit_so_" + active_so_num).slideDown(250);
        });

        scrollLocation("#show_room_" + active_so_num);
    })
    .on("click", "[id^=show_room_]", function() {
        thisClick = this;

        checkTransition(function() {
            active_so_num = $(thisClick).attr("id").replace('show_room_', '');

            toggleDisplay();

            $("#tr_room_" + active_so_num).show();
            $("#div_room_" + active_so_num).slideDown(250);
        });
    })
    .on("click", ".edit_room", function(e) {
        thisClick = this;

        e.stopPropagation();

        checkTransition(function() {
            active_room_id = $(thisClick).attr("id");

            toggleDisplay();

            $.post("/html/search/room_edit.php?room_id=" + active_room_id, function(data) {
                $("#" + active_room_id + ".tr_room_actions").show().find('div').html(data).slideDown(150);
            });

            scrollLocation("#" + active_room_id + ".tr_room_actions");
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

    .on("click", ".add_room_trigger", function(e) {
        thisClick = this;


        e.stopPropagation();

        checkTransition(function() {
            active_so_num = $(thisClick).data('sonum');

            toggleDisplay();

            $.post("/html/search/room_add.php?so_num=" + active_so_num, function(data) {
                $("#" + active_so_num + ".add_room").show().find('div').html(data).slideDown(150);
            });
        });

        scrollLocation("#" + active_so_num + ".add_room");
    })
    .on("click", "[id^=add_room_save_]", function() {
        var save_info = $("#form_add_room_" + active_so_num).serialize();

        $.post("/ondemand/room_actions.php?action=insert_new_room&" + save_info, function(data) {
            $("body").append(data);
        });

        unsaved = false;
    })
    .on("change click", ".days-to-ship", function() {
        var dts = $(this).find(":selected").val();
        var classColor;

        switch(dts) {
            case 'G':
                classColor = 'green';
                break;

            case 'Y':
                classColor = 'yellow';
                break;

            case 'N':
                classColor = 'orange';
                break;

            case 'R':
                classColor = 'red';
                break;

            default:
                classColor = 'gray';
                break;
        }

        $.post("/ondemand/room_actions.php?action=calc_del_date", {days_to_ship: dts}, function(data) {
            $(".delivery_date").val(data).removeClass('job-color-red job-color-green job-color-yellow job-color-orange').addClass('job-color-' + classColor).data("datepicker").setDate(data);
        });
    })
    .on("change", ".dealer_code", function() {
        $.post("/ondemand/play_fetch.php?action=get_dealer_info&dealer_code=" + $(this).val(), function(data) {
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
        var active_ops = $(".active_ops_" + active_room_id).map(function() { return $(this).data("opid"); }).get();

        active_ops = JSON.stringify(active_ops);

        $.post("/ondemand/room_actions.php?action=update_room&" + edit_info, {active_ops: active_ops}, function(data) {
            $('body').append(data);
        });

        unsaved = false;
    })
    .on("click", ".add_iteration", function(e) {
        thisClick = this;
        var next_iteration;

        e.stopPropagation();

        checkTransition(function() {
            active_room_id = $(thisClick).data('roomid');
            var seqAjax;
            var iterationAjax;
            var header;

            if($(thisClick).data('addto') === 'sequence') {
                seqAjax = $.post("/ondemand/play_fetch.php?action=get_next_iteration&output=sequence&roomid=" + active_room_id, function(data) {
                    next_iteration = data;
                });

                header = "Adding Sequence";
            } else {
                iterationAjax = $.post("/ondemand/play_fetch.php?action=get_next_iteration&output=iteration&roomid=" + active_room_id, function(data) {
                    next_iteration = data;
                });

                header = "Adding Iteration";
            }

            $.when(seqAjax, iterationAjax).done(function() {
                toggleDisplay();

                $.post("/html/search/room_add_section.php?room_id=" + active_room_id, function(data) {
                    $("#" + active_room_id + ".tr_room_actions").show().find('div').html(data).slideDown(150);

                    $("#add_iteration_header_" + active_room_id).html(header);
                    $("#edit_iteration_" + active_room_id).val(next_iteration);
                    $("#vin_iteration_" + active_room_id).val(next_iteration);
                });
            });
        });

        scrollLocation("#" + active_room_id + ".tr_room_actions");
    })
    .on("click", ".iteration_save", function(e) {
        e.stopPropagation();

        if($("input[name='room_name']").val() === '') {
            $.alert({
                title: "Unable to Save",
                content: "Cannot save with no room name!"
            });
        } else {
            var edit_info = $("#add_iteration").serialize();
            var active_ops = $(".active_ops").map(function() { return $(this).data("opid"); }).get();

            active_ops = JSON.stringify(active_ops);

            $.post("/ondemand/room_actions.php?action=create_room&" + edit_info, {active_ops: active_ops}, function(data) {
                $('body').append(data);
            });

            unsaved = false;
        }
    })

    .on("click", ".save_so", function() {
        var so_info = $("#form_so_" + active_so_num).serialize();

        $.post('/ondemand/so_actions.php?action=save_so&' + so_info + '&so_num=' + active_so_num, function(data) {
            $("body").append(data);
        });

        unsaved = false;
    })

    .on("click", ".reply_to_inquiry", function(e) {
        e.preventDefault();

        var reply_id = $(this).attr('id');

        $("[id^=inquiry_reply_line_]").not("#inquiry_reply_line_" + reply_id).hide(100);
        $("#inquiry_reply_line_" + reply_id).toggle(250);
    })
    .on("click", ".inquiry_reply_btn", function() {
        var reply_id = $(this).attr("id");
        var reply_text = $("#inquiry_reply_" + reply_id).val();

        $.post("/ondemand/so_actions.php?action=reply_inquiry", {reply: reply_text, id: reply_id}, function(data) {
            $("body").append(data);
            $("#inquiry_reply_line_" + reply_id).val("").hide(100);
        });

        unsaved = false;
    })

    .on("click", "[id^=show_vin_]", function(e) {
        thisClick = this;

        e.stopPropagation();

        checkTransition(function() {
            active_so_num = $(thisClick).attr("id").replace('show_vin_room_', '');

            toggleDisplay();

            $("#tr_vin_" + active_so_num).show();
            $("#div_vin_" + active_so_num).slideDown(250);
        });
    })
    .on("click", "[id^=show_attachments_room_]", function(e) {
        thisClick = this;

        e.stopPropagation();

        checkTransition(function() {
            active_room_id = $(thisClick).attr("id").replace('show_attachments_room_', '');

            toggleDisplay();

            $("#tr_attachments_" + active_room_id).show();
            $("#div_attachments_" + active_room_id).slideDown(250);

            setTimeout(function() {
                $(window).scrollTo($("#show_single_room_" + active_room_id), 800, {offset: -100});
            }, 300);
        });
    })

    .on("click", "[id^=print_]", function(e) {
        thisClick = this;

        e.stopPropagation();

        checkTransition(function() {
            active_room_id = $(thisClick).attr("id").replace('print_', '');

            toggleDisplay();

            $("#tr_print_" + active_room_id).show();
            $("#div_print_" + active_room_id).slideDown(250);

            setTimeout(function() {
                $(window).scrollTo($("#show_single_room_" + active_room_id), 800, {offset: -100});
            }, 300);
        });
    })
;