/**
 * Created by Ben on 3/18/2017.
 */

// This displays a general toast, if no progress bar then set it to false by default
function displayToast(type, message, subject) {
    toastr[type](message, subject);
}

function notifyMe() {
    // Let's check if the browser supports notifications
    if (!("Notification" in window)) {
        window.alert("This browser does not support desktop notification");
    }

    // Let's check whether notification permissions have already been granted
    else if (Notification.permission === "granted") {
        setTimeout(function () {
            // If it's okay let's create a notification
            var notification = new Notification("Hi there!");
        }, 2000);
    }

    // Otherwise, we need to ask the user for permission
    else if (Notification.permission !== "denied") {
        Notification.requestPermission(function (permission) {
            // If the user accepts, let's create a notification
            if (permission === "granted") {
                var notification = new Notification("Hi there!");
            }
        });
    }

    // At last, if the user has denied notifications, and you
    // want to be respectful there is no need to bother them any more.
}

function clearIntervals() {
    if(indv_dt_interval !== undefined) {
        clearInterval(indv_dt_interval);
    }

    if(indv_auto_interval !== undefined) {
        clearInterval(indv_auto_interval);
    }

    if(wc_auto_interval !== undefined) {
        clearInterval(wc_auto_interval);
    }

    if(dash_auto_interval !== undefined) {
        clearInterval(dash_auto_interval);
    }
}

function backFromSearch() {
    $("#search_display").fadeOut(200);
    $("#global_search").val("");

    switch($("#main_display").attr("data-showing")) {
        case 'sales_list':
            so_list.ajax.url('/ondemand/so_actions.php?action=get_sales_list').load();

            break;
    }

    setTimeout(function() {
        var mainDisplay = $("#main_display");

        mainDisplay.fadeIn(200);
        mainDisplay.attr("data-search", "false");

        $("html").scrollTop(scrollPosition);
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
    var mainBody = $("#main_body");

    $(".js_loading").show();

    if(mainBody.length > 0) {
        clearIntervals();

        mainBody.load("/html/" + page + ".php", function() {
            $(".js_loading").hide();
            $("#main_display").attr("data-showing", page);
        });

        backFromSearch();
    } else {
        window.location.replace("index.php?page=" + page);
    }

}

function updateOpQueue() {
    queue_table.ajax.url("/ondemand/display_actions.php?action=display_ind_job_queue&queue=" + $('#viewing_queue').val()).load(null,false);
    active_table.ajax.reload(null,false);
}