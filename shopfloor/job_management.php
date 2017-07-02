<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- Multi-select -->
<link href="/assets/plugins/multiselect/css/multi-select.css" rel="stylesheet" type="text/css"/>

<!-- tabulator -->
<script type="text/javascript" src="/assets/plugins/tablesorter/jquery.tablesorter.min.js"></script>
<link href="/assets/plugins/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css"/>

<!-- tinysort -->
<script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

<!-- matchheight -->
<script type="text/javascript" src="/assets/plugins/jquery.matchHeight-min.js"></script>

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
                            <button class="btn waves-effect btn-primary" id="btn_add_acct" style="margin: 0 0 0 6px;"> <i class="zmdi zmdi-account-add"></i> </button>
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

<!-- Multi-select -->
<script type="text/javascript" src="/assets/plugins/multiselect/js/jquery.multi-select.js"></script>

<!-- Loading page content -->
<script src="/ondemand/js/page_content_functions.js"></script>

<script>
    var active_so_num = null;
    var active_room_id = null;
    var timer;

    loadCalendarPane(); // found in page_content_functions

    function updateSearchTable(field, search, functn) {
        if(field.length >= 1) {
            $.post("/ondemand/livesearch/search_results.php?search=" + search, {find: field, functn: functn}, function(data) {
                $("#search_results_table").html(data);

                if(data !== '') {
                    $("#search_results_card").show();
                }
            });
        } else {
            $("#search_results_card").hide();
        }
    }

    function recalculateBrackets(overrideSelected) {
        $.post("/ondemand/shopfloor/job_actions.php?action=update_brackets", {room: active_room, sonum: active_so_num}, function(data) {
            // This got much simpler, LUCKY YOU!

            var input = $.parseJSON(data); // grab the JSON data returned, this is a 3D array, objects inside of objects
            var ops = input.ops;

            function generateOptions(department) { // generates the options based on the department provided
                var outputOptions = '';

                $.each(ops, function(key, value) { // for each MULTIDIMENSIONAL result inside of SALES concatenate INFORMATION
                    if(value.department === department) {
                        outputOptions += "<option value='" + value.id + "'>" + value.op_id + "-" + value.job_title + "</option>"; // value is the object inside of the object
                    }
                }); // end

                return outputOptions; // send back the final output
            }

            // grab each of the departments and their related options
            var salesOptions = generateOptions("Sales");
            var preprodOptions = generateOptions("Pre-Production");
            var sampleOptions = generateOptions("Sample");
            var doordrawerOptions = generateOptions("Drawer & Doors");
            var customOptions = generateOptions("Custom");
            var boxOptions = generateOptions("Box");

            // find the options inside of the SALES bracket, REMOVE them, wait for that DOM update to FINISH, then ADD the options again
            $("#sales_bracket").find("option").remove().end().append(salesOptions);
            
            $("#pre_prod_bracket").find("option").remove().end().append(preprodOptions); //FIND ALLOWS YOU TO PICK THE SUB!
            $("#sample_bracket").find("option").remove().end().append(sampleOptions);
            $("#door_drawer_bracket").find("option").remove().end().append(doordrawerOptions);
            $("#custom_bracket").find("option").remove().end().append(customOptions);
            $("#box_bracket").find("option").remove().end().append(boxOptions);

            if(overrideSelected === undefined) {
                // select the second option inside of the list
                $("#sales_bracket option:nth-child(1)").attr("selected", "selected"); //TODO: find a different way to select the second option, "Inefficient"
                $("#pre_prod_bracket option:nth-child(1)").attr("selected", "selected");
                $("#sample_bracket option:nth-child(1)").attr("selected", "selected");
                $("#door_drawer_bracket option:nth-child(1)").attr("selected", "selected");
                $("#custom_bracket option:nth-child(1)").attr("selected", "selected");
                $("#box_bracket option:nth-child(1)").attr("selected", "selected");
            } else {
                $("#sales_bracket").val(overrideSelected.sales_bracket);
                $("#pre_prod_bracket").val(overrideSelected.preproduction_bracket);
                $("#sample_bracket").val(overrideSelected.sample_bracket);
                $("#door_drawer_bracket").val(overrideSelected.doordrawer_bracket);
                $("#custom_bracket").val(overrideSelected.custom_bracket);
                $("#box_bracket").val(overrideSelected.box_bracket);
            }
        });
    }

    function saveRoomInfo() {
        var room_info = $("#room_adjustment").serialize();
        active_room = $("#room").val();

        $.post("/ondemand/shopfloor/job_actions.php?action=save_room", room_info, function(data) {
            if(data === 'success') {
                displayToast("success", "Added new room to SO# " + active_so_num, "New Room Added");
            } else if(data === 'success - update') {
                displayToast("info", "Updated room on existing SO#", "Updated room");
            } else {
                $("body").append(data);
            }
        });
        
        displaySO(active_so_num);
    }

    var term = '<?php echo $_GET['lookup']; ?>';

    // on document load
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

    $("#search_accordion1").accordion();

    $("#search_add_tab").on("click", function() {
        searchCounter = generateTab(searchCounter);
    });

    $("body")
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
                $("#modalAddCustomer").html(data);
            }).done(function() {
                $("#modalAddCustomer").modal('show');

                $("#contractor_code").autocomplete({ // TODO: Why won't this work? JS DOM element timing mismatch?
                    source: "/ondemand/livesearch/general.php?search=dealerid"
                });
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
            info += '<input type="radio" name="sales_bracket" id="op_' + opid + '_room_' + roomid +'" value="' + opid + '">';
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
        var input = $(this);

        clearTimeout(timer);

        timer = setTimeout(function() {
            if(input.val().length >= 1) {
                $.post("/ondemand/livesearch/search_results.php?search=general", {find: input.val()}, function(data) {
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
        }, 250);
    });

    $("#cu_sales_order_num1")
        .on("keyup", function() { // this is on keyboard change
            updateSearchTable($(this).val(), "sonum", "displaySO");
            $("#edit_so_info").hide();

            $("#cu_project_name1").val("");
            $("#cu_dealer_contractor1").val("");
            $("#cu_project_manager1").val("");
        });

    $("#cu_project_name1")
        .on("keyup", function () { // this is on keyboard change
            updateSearchTable($(this).val(), "project", "displaySO");
            $("#edit_so_info").hide();

            $("#cu_sales_order_num1").val("");
            $("#cu_dealer_contractor1").val("");
            $("#cu_project_manager1").val("");
        });

    $("#cu_dealer_contractor1")
        .on("keyup", function () { // this is on keyboard change
            updateSearchTable($(this).val(), "contractor", "displaySO");
            $("#edit_so_info").hide();

            $("#cu_sales_order_num1").val("");
            $("#cu_project_name1").val("");
            $("#cu_project_manager1").val("");
        });

    $("#cu_project_manager1")
        .on("keyup", function () { // this is on keyboard change
            updateSearchTable($(this).val(), "project_manager", "displaySO");
            $("#edit_so_info").hide();

            $("#cu_sales_order_num1").val("");
            $("#cu_project_name1").val("");
            $("#cu_dealer_contractor1").val("");
        });

//    $("#search_results_table").tabulator({
//        fitColumns: true,
//        placeholder: "No data available",
//        columns: [
//            {formatter: editIcon, width: 28, align: "center", tooltip: "Edit"},
//            {title: "SO#", field: "sales_order_num", sorter: "number"},
//            {title: "Project/Customer PO", field: "purchase_order", sorter: "string"},
//            {title: "Salesperson", field: "salesperson", sorter: "string"},
//            {title: "Dealer/Contractor", field: "dealer_contractor", sorter: "string"},
//            {title: "Account Type", field: "account_type", sorter: "string"},
//            {title: "Project Manager/Contact", field: "project_mgr_contact", sorter: "string"}
//        ]
//    });
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>