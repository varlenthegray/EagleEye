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

  if(oplUpdater !== undefined) {
    clearInterval(oplUpdater);
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
  var product_type = $("#product_type").val();
  var order_status = $("#order_status").val();
  var days_to_ship = $("#days_to_ship").val();
  var dealer_code = $("#vin_dealer_code_" + room_id).val();

  var species_grade = $("#species_grade").val();
  var construction_method = $("#construction_method").val();
  var door_design = $("#door_design").val();
  var panel_raise_door = $("#panel_raise_door").val();
  var panel_raise_sd = $("#panel_raise_sd").val();
  var panel_raise_td = $("#panel_raise_td").val();
  var edge_profile = $("#edge_profile").val();
  var framing_bead = $("#framing_bead").val();
  var framing_options = $("#framing_options").val();
  var style_rail_width = $("#style_rail_width").val();
  var finish_code = $("#finish_code").val();
  var sheen = $("#sheen").val();
  var glaze = $("#glaze").val();
  var glaze_technique = $("#glaze_technique").val();
  var antiquing = $("#antiquing").val();
  var worn_edges = $("#worn_edges").val();
  var distress_level = $("#distress_level").val();
  var carcass_exterior_species = $("#carcass_exterior_species").val();
  var carcass_exterior_finish_code = $("#carcass_exterior_finish_code").val();
  var carcass_exterior_glaze_color = $("#carcass_exterior_glaze_color").val();
  var carcass_exterior_glaze_technique = $("#carcass_exterior_glaze_technique").val();
  var carcass_interior_species = $("#carcass_interior_species").val();
  var carcass_interior_finish_code = $("#carcass_interior_finish_code").val();
  var carcass_interior_glaze_color = $("#carcass_interior_glaze_color").val();
  var carcass_interior_glaze_technique = $("#carcass_interior_glaze_technique").val();
  var drawer_boxes = $("#drawer_boxes").val();

  $("#vin_code_" + room_id).val(active_so_num + room + "-" + iteration + "-" + product_type + order_status + days_to_ship + "_" + dealer_code + "_" + species_grade + construction_method + door_design + "-" + panel_raise_door + panel_raise_sd + panel_raise_td + "-" + edge_profile +
    framing_bead + framing_options + style_rail_width + "_" + finish_code + sheen + "-" + glaze + glaze_technique + antiquing + worn_edges + distress_level + "_" + carcass_exterior_species + carcass_exterior_finish_code +
    carcass_exterior_glaze_color + carcass_exterior_glaze_technique + "-" + carcass_interior_species + carcass_interior_finish_code + carcass_interior_glaze_color + carcass_interior_glaze_technique + "_" + drawer_boxes);

  if($("#vin_code_" + room_id).val().indexOf("?") > -1) {
    $("#submit_quote").hide();
  } else {
    $("#submit_quote").show();
  }
}

function loadPage(page) {
  var mainBody = $("#main_body");
  var explodedPage = page.split("?");

  $(".js_loading").show();

  if(mainBody.length > 0) {
    clearIntervals();

    mainBody.load("/html/" + explodedPage[0] + ".php?" + explodedPage[1], function() {
      $(".js_loading").hide();
      $("#main_display").attr("data-showing", explodedPage[0]);
    });

    backFromSearch();
  } else {
    window.location.replace("main.php?page=" + explodedPage[0]);
  }

}

function updateOpQueue() {
  queue_table.ajax.url("/ondemand/display_actions.php?action=display_ind_job_queue&queue=" + $('#viewing_queue').val()).load(null,false);
  active_table.ajax.reload(null,false);
}

function getLocalTime() {
  var time = new Date();
  return time.toLocaleTimeString();
}

function productTypeSwitch() {
  function setPcts(obj) {
    let gPct = 2;
    let yPct = 2;
    let nPct = 2;
    let rPct = 2;

    if(obj.green.unavailable !== true) {
      $(".dts_pct_g").html("[" + obj.green.ship_days + " Days] (" + (obj.green.pct * 100) + "%)");
      gPct = obj.green.pct;
    } else {
      $(".dts_pct_g").html("[Not Available]");
    }

    if(obj.yellow.unavailable !== true) {
      $(".dts_pct_y").html("[" + obj.yellow.ship_days + " Days] (" + (obj.yellow.pct * 100) + "%)");
      yPct = obj.yellow.pct;
    } else {
      $(".dts_pct_y").html("[Not Available]");
    }

    if(obj.orange.unavailable !== true) {
      $(".dts_pct_n").html("[" + obj.orange.ship_days + " Days] (" + (obj.orange.pct * 100) + "%)");
      nPct = obj.orange.pct;
    } else {
      $(".dts_pct_n").html("[Not Available]");
    }

    if(obj.red.unavailable !== true) {
      $(".dts_pct_r").html("[" + obj.red.ship_days + " Days] (" + (obj.red.pct * 100) + "%)");
      rPct = obj.red.pct;
    } else {
      $(".dts_pct_r").html("[Not Available]");
    }

    let dts_id = $("#days_to_ship");

    switch($("#product_type").val()) {
      case 'G':
        dts_id.attr("data-pct", gPct);
        break;
      case 'N':
        dts_id.attr("data-pct", nPct);
        break;
      case 'Y':
        dts_id.attr("data-pct", yPct);
        break;
      case 'R':
        dts_id.attr("data-pct", rPct);
        break;
    }
  }

  switch($("#product_type").val()) {
    case 'C':
      setPcts({'green': {'ship_days': 26, 'pct': 0}, 'yellow': {'ship_days': 19, 'pct': 0.25}, 'orange': {'ship_days': 13, 'pct': 0.5}, 'red': {'unavailable': true}});
      break;

    case 'L':
      setPcts({'green': {'ship_days': 10, 'pct': 0}, 'yellow': {'ship_days': 6, 'pct': 0.25}, 'orange': {'unavailable': true}, 'red': {'ship_days': 3, 'pct': 0.5}});
      break;

    case 'S':
      setPcts({'green': {'unavailable': true}, 'yellow': {'unavailable': true}, 'orange': {'ship_days': 13, 'pct': 0}, 'red': {'ship_days': 6, 'pct': 0}});
      break;

    case 'D':
      setPcts({'green': {'ship_days': 26, 'pct': 0}, 'yellow': {'unavailable': true}, 'orange': {'unavailable': true}, 'red': {'unavailable': true}});
      break;

    case 'A':
      setPcts({'green': {'unavailable': true}, 'yellow': {'ship_days': 19, 'pct': 0}, 'orange': {'ship_days': 13, 'pct': 0.25}, 'red': {'ship_days': 6, 'pct': 0.25}});
      break;

    case 'W':
      setPcts({'green': {'unavailable': true}, 'yellow': {'ship_days': 19, 'pct': 0}, 'orange': {'ship_days': 13, 'pct': 0}, 'red': {'ship_days': 6, 'pct': 0}});
      break;

    case 'H':
      setPcts({'green': {'unavailable': true}, 'yellow': {'unavailable': true}, 'orange': {'ship_days': 13, 'pct': 0}, 'red': {'unavailable': true}});
      break;

    case 'N':
      setPcts({'green': {'unavailable': true}, 'yellow': {'unavailable': true}, 'orange': {'ship_days': 13, 'pct': 0}, 'red': {'unavailable': true}});
      break;

    case 'R':
      setPcts({'green': {'unavailable': true}, 'yellow': {'unavailable': true}, 'orange': {'unavailable': true}, 'red': {'unavailable': true}});
      break;
  }
}

function addCommas(x) {
  var parts = x.toString().split(".");
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return parts.join(".");
}

function updateBreakButton() {
  $.post("/ondemand/account_actions.php?action=get_break_btn", function(data) {
    var result = JSON.parse(data);

    $(".nav_break").attr('id', result.id).find('span').html(result.display);
  });
}

Number.prototype.formatMoney = function(c, d, t){
  var n = this,
    c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "." : d,
    t = t == undefined ? "," : t,
    s = n < 0 ? "-" : "",
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
    j = (j = i.length) > 3 ? j % 3 : 0;
  return "$" + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};



/*
function getMileage($zip1, $zip2){
  // This function returns Longitude & Latitude from zip code.
  function getLnt($zip){
    $url = "http://maps.googleapis.com/maps/api/geocode/json?address=" + urlencode($zip) +"&sensor=false";
    $result_string = file_get_contents($url);
    $result = json_decode($result_string, true);
    $result1[]=$result['results'][0];
    $result2[]=$result1[0]['geometry'];
    $result3[]=$result2[0]['location'];
    return $result3[0];
  }

  $first_lat = getLnt($zip1);
  $next_lat = getLnt($zip2);
  $lat1 = $first_lat['lat'];
  $lon1 = $first_lat['lng'];
  $lat2 = $next_lat['lat'];
  $lon2 = $next_lat['lng'];
  $theta=$lon1-$lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
    cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
    cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;

  return ($miles * 0.8684);
}*/
