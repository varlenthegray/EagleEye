<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- Multi-select -->
<link href="/assets/plugins/multiselect/css/multi-select.css" rel="stylesheet" type="text/css"/>

<!-- tabulator -->
<script type="text/javascript" src="/assets/plugins/tablesorter/jquery.tablesorter.min.js"></script>
<link href="/assets/plugins/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css"/>

<!-- Search results box -->
<div class="row">
    <div class="col-md-12">
        <div class="card-box" id="search_results_card">
            <div class="row">
                <div class="col-md-12">
                    <form class="form-inline">
                        <div class="form-group">
                            <label for="global_search">Lookup: </label>
                            <input class="form-control" type="text" placeholder="Search..." id="global_search" name="global_search" style="width: 250px;" />
                            <button class="btn waves-effect btn-primary" id="btn_add_acct" style="margin: 0 0 0 6px;"> <i class="zmdi zmdi-account-add"></i> </button>
                        </div>
                    </form>
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

    $("#search_accordion1").accordion();

    $("#search_add_tab").on("click", function() {
        searchCounter = generateTab(searchCounter);
    });

    $("body")
        .on("click", "[id^=searchTab]", function(e) { // this allows for the automation of search tabs
            var accordion = "search_accordion" + e.target.getAttribute("searchid"); // add more accordions

            setTimeout(function() {
                $("#" + accordion).accordion("refresh"); // refresh the accordion on click of tab
            }, 200);
        })
        .on("click", "#submit_new_customer", function() {
            var cuData = $("#add_new_customer").serialize();

            $.post("/ondemand/customer.php?action=add_new", cuData, function(data) {
                if(data === 'success') {
                    displayToast("success", "Inserted new customer information successfully!", "Added Customer");

                    $("[id^='new_']").val("");
                    $("#new_state").val("NC").change();

                    $("#modalAddCustomer").modal('hide');
                } else {
                    $("body").append(data);
                }
            });
        })
        .on("change", "#assigned_bracket", function() { // hey uh, this... i'm sorry... this one is bad - it assigns all bracket information dynamically...
            recalculateBrackets();
            $("#manage_bracket").hide();
        })
        .on("click", "#add_room", function() {
            $.post("/html/shopfloor/job_management.php?action=add", {so_id: active_so_num, roomid: active_room_id}, function(data) {
                $("#room_info_display").html(data);
            }).done(function() {
                $("#sales_bracket_adjustments").multiSelect({
                    selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
                    selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
                });

                $("#sample_bracket_adjustments").multiSelect({
                    selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
                    selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
                });

                $("#preprod_bracket_adjustments").multiSelect({
                    selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
                    selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
                });

                $("#doordrawer_bracket_adjustments").multiSelect({
                    selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
                    selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
                });

                $("#laminate_bracket_adjustments").multiSelect({
                    selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
                    selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
                });

                $("#box_bracket_adjustments").multiSelect({
                    selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
                    selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
                });

                $("#custom_bracket_adjustments").multiSelect({
                    selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
                    selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
                });

                $("#install_bracket_adjustments").multiSelect({
                    selectableHeader: "<div class='bracket-adjustment-header'>Active Operations</div>",
                    selectionHeader: "<div class='bracket-adjustment-header'>Inactive Operations</div>"
                });

                $("#individual_room_info").show();
            });
        })
        .on("click", "#room_save", function() {
            saveRoomInfo();
        })
        .on("click", "#manage_brackets", function() {
            saveRoomInfo();

            displayBracketInfo(active_room);
        })
        .on("click", "#bracket_adjustment_save", function() {
            var salesBracketAdjusted = $("#sales_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var preprodBracketAdjusted = $("#pre_prod_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var sampleBracketAdjusted = $("#sample_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var doordrawerBracketAdjusted = $("#door_drawer_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var customBracketAdjusted = $("#custom_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();
            var boxBracketAdjusted = $("#box_bracket_adjustments").find('option').not(':selected').map(function () { return this.value; }).get();

            var salesPublished = $("#sales_published").is(":checked");
            var preProdPublished = $("#pre_prod_published").is(":checked");
            var samplePublished = $("#sample_published").is(":checked");
            var doordrawerPublished = $("#door_drawer_published").is(":checked");
            var customPublished = $("#custom_published").is(":checked");
            var boxPublished = $("#box_published").is(":checked");

            var fullBracketAdjusted = [salesBracketAdjusted, preprodBracketAdjusted, sampleBracketAdjusted, doordrawerBracketAdjusted, customBracketAdjusted, boxBracketAdjusted];

            fullBracketAdjusted = [].concat.apply([], fullBracketAdjusted);

            var fullBracketPayload = JSON.stringify(fullBracketAdjusted);

            console.log(fullBracketPayload);

            var publishedString = [salesPublished, preProdPublished, samplePublished, doordrawerPublished, customPublished, boxPublished];

            var publishedPayload = JSON.stringify(publishedString);

            $.post("/ondemand/shopfloor/job_actions.php?action=update_individual_bracket", {payload: fullBracketPayload, sonum: active_so_num, room: active_room, published: publishedPayload}, function(data) {
                if(data === 'success') {
                    displayToast("success", "Successfully updated bracket for room " + active_room + " on SO# " + active_so_num + ".", "Updated Bracket")
                } else {
                    $("body").append(data);
                }
            });
        })
        .on("change", "#box_bracket", function() {
            $.post("/ondemand/shopfloor/job_actions.php?action=update_in_queue", {roomID: active_room_id, opID: $(this).val()}, function(data) {
                if(data === "success") {
                    displayToast("success", "Updated assigned operation.", "Operation Updated");
                } else {
                    $("body").append(data);
                }
            })
        })
        .on("change", "input[name='cu_type']", function() {
            switch($(this).val()) {
                case 'retail':
                    $("#add_retail_customer").show();
                    $("#add_distributor").hide();

                    break;
                case 'distribution':
                    $("#add_retail_customer").hide();
                    $("#add_distributor").show();

                    break;
                case 'cutting':
                    break;
                default:
                    break;
            }
        })
        .on("change", "#add_notes", function() {
            if($(this).is(":checked")) {
                $("#room_note_visible").show();
            } else {
                $("#room_note_visible").hide();
            }
        })
        .on("change", "input[name='viewBracket']", function() {
            var publish = $('[id$=topublish]');

            switch($(this).val()) {
                case 'Sales':
                    publish.hide();
                    $("#sales_bracket_topublish").show();

                    break;
                case 'Sample':
                    publish.hide();
                    $("#sample_bracket_topublish").show();

                    break;
                case 'Pre-Production':
                    publish.hide();
                    $("#preprod_bracket_topublish").show();

                    break;
                case 'Door/Drawer':
                    publish.hide();
                    $("#doordrawer_bracket_topublish").show();

                    break;
                case 'Laminate':
                    publish.hide();
                    $("#laminate_bracket_topublish").show();

                    break;
                case 'Box':
                    publish.hide();
                    $("#box_bracket_topublish").show();

                    break;
                case 'Custom':
                    publish.hide();
                    $("#custom_bracket_topublish").show();

                    break;
                case 'Install':
                    publish.hide();
                    $("#install_bracket_topublish").show();

                    break;
                default:
                    publish.hide();

                    break;
            }
        })
        .on("click", "#save_publish", function() {
            switch($("input[name='viewBracket']").val()) {
                case 'Sales':


                    break;
                case 'Sample':

                    break;
                case 'Pre-Production':

                    break;
                case 'Door/Drawer':

                    break;
                case 'Laminate':

                    break;
                case 'Box':

                    break;
                case 'Custom':

                    break;
                case 'Install':

                    break;
                default:

                    break;
            }
        })
        .on("click", "[id^=edit_]", function(e) {
            e.stopPropagation();

            active_so_num = $(this).attr("id").replace('edit_', '');

            $("[id^=tr_single_room_]").hide(100);
            $("[id^=tr_room_]").hide(100);

            $("[id^=tr_edit_so_]").not("#tr_edit_so_" + active_so_num).hide(100);
            $("[id^=div_edit_so_]").not("#div_edit_so_" + active_so_num).hide();

            $("[id^=show_room_]").not("#show_room_" + active_so_num).removeClass("active_room_line");
            $("[id^=show_single_room_]").removeClass("active_room_line");

            $("#show_room_" + active_so_num).addClass("active_room_line");

            $("#tr_edit_so_" + active_so_num).show();
            $("#div_edit_so_" + active_so_num).slideDown(250);
        })
        .on("click", "[id^=show_room_]", function() {
            $("[id^=show_room_]").removeClass("active_room_line");
            $(this).addClass("active_room_line");
            $("[id^=show_single_room_]").removeClass("active_room_line");
            $("[id^=tr_single_room_]").hide(250);
            $("[id^=div_single_room_]").hide(100);

            active_so_num = $(this).attr("id").replace('show_room_', '');

            $("[id^=tr_room_]").not("#tr_room_" + active_so_num).hide(100);

            $("[id^=tr_edit_so_]").hide(100);
            $("[id^=div_edit_so_]").hide();

            $("#tr_room_" + active_so_num).show();
            $("#div_room_" + active_so_num).slideDown(250);
        })
        .on("click", "[id^=show_single_room_]", function() {
            $("[id^=show_single_room_]").removeClass("active_room_line");
            $(this).addClass("active_room_line");

            active_room_id = $(this).attr("id").replace('show_single_room_', '');

            $("[id^=tr_single_room_]").not("#tr_single_room_" + active_room_id).hide(100);

            $("[id^=tr_room_bracket_]").hide(250);
            $("[id^=div_room_bracket_]").hide(100);

            $("#tr_single_room_" + active_room_id).show();
            $("#div_single_room_" + active_room_id).slideDown(250);
        })
        .on("click", "[id^=manage_bracket_]", function(e) {
            e.stopPropagation();

            active_room_id = $(this).attr("id").replace('manage_bracket_', '');

            $("[id^=show_single_room_]").removeClass("active_room_line");
            $("#show_single_room_" + active_room_id).addClass("active_room_line");

            $("[id^=tr_single_room_]").hide(250);
            $("[id^=div_single_room_]").hide(100);

            $("[id^=tr_room_bracket_]").not("#tr_room_bracket_" + active_room_id).hide(250);
            $("[id^=div_room_bracket_]").not("#div_room_bracket_" + active_room_id).hide(100);

            $("#sales_bracket_adjustments_" + active_room_id).multiSelect();
            $("#pre_prod_bracket_adjustments_" + active_room_id).multiSelect();
            $("#sample_bracket_adjustments_" + active_room_id).multiSelect();
            $("#door_drawer_bracket_adjustments_" + active_room_id).multiSelect();
            $("#custom_bracket_adjustments_" + active_room_id).multiSelect();
            $("#main_bracket_adjustments_" + active_room_id).multiSelect();
            $("#shipping_bracket_adjustments_" + active_room_id).multiSelect();
            $("#install_bracket_adjustments_" + active_room_id).multiSelect();

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
        });

    $("#global_search").on("keyup", function() {
        if($(this).val().length >= 1) {
            $.post("/ondemand/livesearch/search_results.php?search=general", {find: $(this).val()}, function(data) {
                $("#search_results_table").html(data);
                $("#search_results_global_table").trigger("update");

                if(data !== '') {
                    $("#search_results_global_table").show();
                } else {
                    $("#search_results_global_table").hide();
                }
            });

//            $("#search_results_table").tabulator("setData", "/ondemand/livesearch/search_results.php?search=gen_json&find=" + $(this).val());
//
//            $("#search_results_card").show();
        } else {
            $("#search_results_global_table").hide();
        }
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