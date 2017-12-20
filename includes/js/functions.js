/*jshint strict: false*/

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

    setTimeout(function() {
        $("#main_display").fadeIn(200);
    }, 200);
}

function calcVin(room_id) {
    var room;

    var section = $("input[name='section']").val();
    var construction_method = $("select[name='construction_method']").find(":selected").val();
    var rework = $("select[name='rework']").find(":selected").val();
    var addon = $("select[name='addon']").find(":selected").val();
    var warranty = $("select[name='warranty']").find(":selected").val();
    var billing_type = $("select[name='billing_type']").find(":selected").val();
    var days_to_ship = $("select[name='days_to_ship']").val();
    var order_status = $("select[name='order_status']").val();

    if($("select[name='room']").val() === undefined) {
        room = $("input[name='room']").val();
    } else {
        room = $("select[name='room']").val();
    }

    var iteration = $("input[name='iteration']").val();
    var dealer_code = $("input[name='vin_dealer_code']").val();

    var species_grade = $("select[name='species_grade']").find(":selected").val();

    var door_design = $("select[name='door_design']").find(":selected").val();
    var panel_raise_door = $("select[name='panel_raise_door']").find(":selected").val();
    var panel_raise_sd = $("select[name='panel_raise_sd']").find(":selected").val();
    var panel_raise_td = $("select[name='panel_raise_td']").find(":selected").val();
    var edge_profile = $("select[name='edge_profile']").find(":selected").val();
    var framing_bead = $("select[name='framing_bead']").find(":selected").val();
    var framing_options = $("select[name='framing_options']").find(":selected").val();
    var style_rail_width = $("select[name='style_rail_width']").find(":selected").val();
    var finish_code = $("select[name='finish_code']").find(":selected").val();
    var sheen = $("select[name='sheen']").find(":selected").val();
    var glaze = $("select[name='glaze']").find(":selected").val();
    var glaze_technique = $("select[name='glaze_technique']").find(":selected").val();
    var antiquing = $("select[name='antiquing']").find(":selected").val();
    var worn_edges = $("select[name='worn_edges']").find(":selected").val();
    var distress_level = $("select[name='distress_level']").find(":selected").val();
    var carcass_exterior_species = $("select[name='carcass_exterior_species']").find(":selected").val();
    var carcass_exterior_finish_code = $("select[name='carcass_exterior_finish_code']").find(":selected").val();
    var carcass_exterior_glaze_color = $("select[name='carcass_exterior_glaze_color']").find(":selected").val();
    var carcass_exterior_glaze_technique = $("select[name='carcass_exterior_glaze_technique']").find(":selected").val();
    var carcass_interior_species = $("select[name='carcass_interior_species']").find(":selected").val();
    var carcass_interior_finish_code = $("select[name='carcass_interior_finish_code']").find(":selected").val();
    var carcass_interior_glaze_color = $("select[name='carcass_interior_glaze_color']").find(":selected").val();
    var carcass_interior_glaze_technique = $("select[name='carcass_interior_glaze_technique']").find(":selected").val();
    var drawer_boxes = $("select[name='drawer_boxes']").find(":selected").val();

    var vin_string = active_so_num + room + "-" + section + "." + construction_method + "." + rework + "." + addon + "." + warranty + "." + billing_type + days_to_ship +
        order_status + "_" + dealer_code + "_" + species_grade + door_design + "-" + panel_raise_door + panel_raise_sd +
        panel_raise_td + "-" + edge_profile + framing_bead + framing_options + style_rail_width + "_" + finish_code + sheen + "-" + glaze + glaze_technique +
        antiquing + worn_edges + distress_level + "_" + carcass_exterior_species + carcass_exterior_finish_code + carcass_exterior_glaze_color +
        carcass_exterior_glaze_technique + "-" + carcass_interior_species + carcass_interior_finish_code + carcass_interior_glaze_color +
        carcass_interior_glaze_technique + "_" + drawer_boxes;

    $("input[name='vin_code']").val(vin_string);
}

function loadPage(page) {
    var mainBody = $("#main_body");

    $(".js_loading").show();

    if(mainBody.length > 0) {
        clearIntervals();

        mainBody.load("/html/" + page + ".php", function() {
            $(".js_loading").hide();
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