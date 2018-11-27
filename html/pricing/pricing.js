jQuery.expr.filters.offscreen = function(el) {
  var rect = el.getBoundingClientRect();
  return (
    (rect.x + rect.width) < 0
    || (rect.y + rect.height) < 0
    || (rect.x > window.innerWidth || rect.y > window.innerHeight)
  );
};

function delNoData() {
  let getNegNode = cabinetList.fancytree("getTree").getNodeByKey('-1');

  if(getNegNode !== null) {
    cabinetList.fancytree("getTree").getNodeByKey('-1').remove();
  }
}

function recalcSummary() {
  let global_room_charges = 0.00; // global room charges
  let global_cab_charges = 0.00; // global cabinet charges total
  let line_total = 0.00; // total of the item list
  let cabinet_only_total = 0.00; // cabinet only total
  let cabinet_only_skus = ''; // a list (for transparency report) of cabinets only
  let non_cabinet_total = 0.00; // non-cabinet total (for transparency report)
  let non_cabinet_skus = ''; // non-cabinet SKU's (for transparency report)
  let shipVia = $("#ship_via").val(); // shipping method

  //******************************************************************
  // parse per line
  cabinetList.fancytree("getTree").visit(function(line) {
    let qty = parseInt(line.data.qty);
    let price = parseFloat(line.data.price);
    let $tdList = $(line.tr).find(">td");

    // if there is an additional markup (% right now) defined in the system for this line
    if(line.data.addlMarkup !== undefined && line.data.addlMarkup !== '' && line.data.addlMarkup !== null) {
      let addlMarkup = JSON.parse(line.data.addlMarkup); // grab the information into JSON value

      // for the species and markups available, grab each of them
      $.each(addlMarkup, function(i, v) {
        // this is going to be the main focus of what drives the markup, i.e. species_grade
        let curVal = $("#" + i).val();

        // for every definition such as Cherry, Hickory, Black Walnut, etc, get the key and percent
        $.each(v, function(key, pct) {
          // if the key is the current value that we're working with (species
          if(key === curVal) {
            // take the percent (in float, to ensure no NaN) and multiply it by the price (note, this is the price before quantity but after square foot)
            price = parseFloat(pct) * price;
          }
        });
      });
    }

    let lineTotal = 0.00;

    // now update the last column (total) with the final price for that line item
    if($("#product_type").val() === 'W') {
      $tdList.eq(9).text('$0.00');
    } else {
      if(line.data.customPrice === 1) {

        let cPrice = parseFloat(price.toString().replace('$', ''));

        lineTotal = cPrice;

        $tdList.eq(9).find("input").val(cPrice.formatMoney());
      } else {
        // set the line total equal to the quantity * price
        lineTotal = qty * price;

        $tdList.eq(9).text(lineTotal.formatMoney());
      }
    }

    // if the line item is a cabinet only (excludes tops, accessories, fillers, moldings) then we're adding that to a cabinet only price (inset specific pricing)
    if(line.data.cabinet === '1') {
      // our cabinet only total goes up
      cabinet_only_total += parseFloat(lineTotal);

      // add the SKU to the total cabinet only running list
      cabinet_only_skus += line.title + ", ";
    } else {
      // our non-cabinet total goes up (transparency report)
      non_cabinet_total += parseFloat(lineTotal);

      //add the SKU to the non-cabinet total running list
      non_cabinet_skus += line.title + ", ";
    }

    if(line.data.exclude_markup === '1') {

    }

    // this is the final running total for the system
    line_total += parseFloat(lineTotal);
  });

  // remove the excess comma and space from the cabinet SKU list
  cabinet_only_skus = cabinet_only_skus.slice(0, -2);

  // remove the excess comma and space from the non-cabinet SKU list
  non_cabinet_skus = non_cabinet_skus.slice(0, -2);

  // display the total
  $("#itemListTotal").text(line_total.formatMoney());
  //******************************************************************

  //******************************************************************
  // done parsing per line, moving into the global charges for the page

  //// Glaze Technique
  let gt_pct = $("#gt_pct"), gt_amt = $("#gt_amt");
  let ggard_pct = $("#ggard_pct"), ggard_amt = $("#ggard_amt");
  let fcode_pct = $("#fc_pct"), fcode_amt = $("#fc_amt");
  let sheen_pct = $("#sheen_pct"), sheen_amt = $("#sheen_amt");
  let gt_markup = 0.00, ggard_markup = 0.00, fcode_markup = 0.00, sheen_markup = 0.00; // glaze technique markup

  // grab the glaze technique, determine what the amount to markup is
  switch($("#glaze_technique").val()) {
    case 'G2':
      gt_markup = 0.10; // toss this into the function variable for markup
      break;
    case 'G0':
      gt_amt.text("");
      break;
    default:
      gt_amt.text("ERR");
      break;
  }

  // grab the green gard, determine what the amount to markup is
  switch($("#green_gard").val()) {
    case 'G1':
      ggard_markup = 0.05; // toss this into the function variable for markup
      break;
    case 'G0':
      ggard_amt.text("");
      break;
    default:
      ggard_amt.text("ERR");
      break;
  }

  // grab the green gard, determine what the amount to markup is
  switch($("#sheen").val()) {
    case 'a':
      sheen_markup = 0.05; // toss this into the function variable for markup
      break;
    case 'c':
      sheen_pct.text("");
      break;
    case 'h':
      sheen_markup = 0.05; // toss this into the function variable for markup
      break;
    case 'X':
      sheen_markup = 0.05; // toss this into the function variable for markup
      break;
    default:
      sheen_amt.text("ERR");
      break;
  }

  if(($("#finish_code").val().indexOf('p') >= 0 ||  $("#finish_code").val() === '1cXXXX') && $("#product_type").val() === 'P') {
    fcode_markup = 0.10;
  } else {
    fcode_markup = 0;
    fcode_amt.text("");
  }

  // calculate out the cost of the glaze technique
  let gt_cost = line_total * gt_markup;
  let ggard_cost = line_total * ggard_markup;
  let fcode_cost = line_total * fcode_markup;
  let sheen_cost = line_total * sheen_markup;

  // Glaze Technique fields
  gt_amt.text(gt_cost.formatMoney());
  gt_pct.text((gt_markup * 100).toFixed(2) + "%");

  // Green Gard fields
  ggard_amt.text(ggard_cost.formatMoney());
  ggard_pct.text((ggard_markup * 100).toFixed(2) + "%");

  // Finish Code fields
  fcode_amt.text(fcode_cost.formatMoney());
  fcode_pct.text((fcode_markup * 100).toFixed(2) + "%");

  // Sheen fields
  sheen_amt.text(sheen_cost.formatMoney());
  sheen_pct.text((sheen_markup * 100).toFixed(2) + "%");

  // add the glaze technique to the total amount of global upcharges
  global_cab_charges += gt_cost;
  global_cab_charges += ggard_cost;
  global_cab_charges += fcode_cost;
  global_cab_charges += sheen_cost;

  let shipPrice = 0;

  // Grab the shipping price IF it's not pickup
  if(shipVia !== '4') {
    shipPrice = $("#shipping_cost").attr("data-cost");
    global_room_charges += parseFloat(shipPrice);
  }

  //// Global Cabinet Details
  $("#itemListGlobalCabDetails").text(global_cab_charges.formatMoney());

  //// Subtotal 1
  let subtotal1 = global_cab_charges + line_total;
  $("#itemListSubTotal1").text(subtotal1.formatMoney());

  //// Capture the multiplier for use
  let multiplier = parseFloat($("#itemListMultiplier").text());

  //// Calculate the net price
  let netPrice = (line_total + global_cab_charges) * multiplier;
  $("#itemListNET").text(netPrice.formatMoney());

  // Grab the product type, has to be calculated in jquery because this is a flexible price based on the total of the job
  // THIS PRICE IS NET, MARKUP DOES NOT IMPACT THIS PRICE
  let productTypeOpt = $("#product_type").val();
  let productTypeMarkup = 0.00;

  if(productTypeOpt === 'B') { // markup for inset
    productTypeMarkup = 0.10;
  }

  // IT IS IMPORTANT THAT WE USE THE CABINET ONLY PRICE HERE! THIS ALLOWS US TO SEGREGATE OUT WOOD TOPS AND FILLERS/PANELS!
  let productTypeCost = cabinet_only_total * productTypeMarkup;
  $("#product_type_cost").text(productTypeCost.formatMoney());

  // add the product type cost to the global room charges
  global_room_charges += productTypeCost;

  //// Display the global room detail charges
  $("#itemListGlobalRoomDetails").text(global_room_charges.formatMoney());

  let subtotal = netPrice + global_room_charges;

  // TODO: We're not adding credit card here right now, we need to add that in

  //// Update the subtotal
  $("#finalSubTotal").text(subtotal.formatMoney());

  //// Update the total
  $("#finalTotal").text(subtotal.formatMoney());

  //// Update the required deposit
  let deposit = subtotal * .5;
  $("#finalDeposit").text(deposit.formatMoney());
  //******************************************************************

  //******************************************************************
  // Transparency report
  $("#calcProductType").text('Cabinet Only Total (' + cabinet_only_total + ') * Product Type Markup (' + productTypeMarkup + ')');
  $("#calcProductTypeTotal").text((cabinet_only_total * productTypeMarkup).formatMoney());

  $("#calcLeadTime").text('Lead Time (Green) * Total [Hardcoded]');
  $("#calcLeadTimeTotal").text('$0.00 [Hardcoded]');

  $("#calcShipVIA").text('Cycle Truck [Hardcoded]');
  $("#calcShipVIATotal").text('$0.00 [Hardcoded]');

  let shipMileInfo = JSON.parse(calcShipInfo);

  if(shipVia !== '4') {
    $("#calcShipZone").html('Mileage (' + shipMileInfo.miles + ') - ' + shipMileInfo.zone + '<br /><br />Calculated based on Shipping Zip entered into Ship To, if that is empty, use Dealer Zip.<br /><br />0-100 Ship Zone A, $0.00<br />100-200 Ship Zone B, $150.00<br />200-300 Ship Zone C, $300.00<br />300-400 Ship Zone D, $450.00<br />400-500 Ship Zone E, $600.00');
    $("#calcShipZoneTotal").text(shipMileInfo.cost.formatMoney());
  } else {
    $("#calcShipZone").html('Customer Pickup');
    $("#calcShipZoneTotal").text('$0.00');
  }

  $("#calcGlazeTech").html('Total (' + line_total.toFixed(2) + ') * Glaze Markup (' + gt_markup + ')');
  $("#calcGlazeTechTotal").html(gt_cost.formatMoney());

  $("#calcGreenGard").html('Total (' + line_total.toFixed(2) + ') * Green Gard Markup (' + ggard_markup + ')');
  $("#calcGreenGardTotal").html(ggard_cost.formatMoney());

  $("#calcSheen").html('Total (' + line_total.toFixed(2) + ') * Sheen Markup (' + sheen_markup + ')');
  $("#calcSheenTotal").html(sheen_cost.formatMoney());

  $("#calcFinishCode").html('Total (' + line_total.toFixed(2) + ') * Finish Markup (' + fcode_markup + ')');
  $("#calcFinishCodeTotal").html(fcode_cost.formatMoney());

  $("#calcCabinetLines").html('Cabinets: ' + cabinet_only_skus);
  $("#calcCabinetLinesTotal").html(cabinet_only_total.formatMoney());

  $("#calcNonCabLines").html('Non-Cabinets: ' + non_cabinet_skus);
  $("#calcNonCabLinesTotal").html(non_cabinet_total.formatMoney());
  //******************************************************************
}

// @footCalc() - calculates by square foot or linear foot
function footCalc(node) {
  let $tdList = $(node.tr).find(">td");
  let outprice = node.data.price;

  // noinspection JSCheckFunctionSignatures
  if(node.data.width > 0 && node.data.height > 0) {
    if(parseInt(node.data.sqft) === 1) {
      let line_sqft = (parseFloat(node.data.width) * parseFloat(node.data.height)) / 144;
      let line_total = line_sqft * node.data.singlePrice;

      $tdList.eq(8).text(line_total.formatMoney());

      outprice = line_total;
    } else if(parseInt(node.data.linft) === 1) {
      let line_linft = (parseFloat(node.data.width) / 12) * node.data.singlePrice;

      $tdList.eq(8).text(line_linft.formatMoney());

      outprice = line_linft;
    }
  }

  return outprice;
}

function fullRecalc() {
  cabinetList.fancytree("getTree").visit(function(line) {
    let itemID = line.data.itemID;
    let key = line.key;

    var tree = cabinetList.fancytree("getTree");

    $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: itemID, room_id: active_room_id}, function(data) {
      if(data !== '' && line.data.customPrice !== 1) {
        let itemInfo = JSON.parse(data);
        let fixedPrice = parseFloat(itemInfo.price).toFixed(2);
        let node = tree.getNodeByKey(key);

        if(node.data.cabinet === 1) {
          node.setTitle(itemInfo.sku);
        }

        node.data.price = fixedPrice;
        node.icon = itemInfo.icon;
        node.data.name = itemInfo.title;
        node.data.sqft = itemInfo.sqft;
        node.data.singlePrice = fixedPrice;
        node.data.cabinet = itemInfo.cabinet;
        node.data.addlMarkup = itemInfo.addl_markup;
      }
    }).done(function() {
      recalcSummary();
    });
  });

  displayToast("success", "Successfully recalculated the pricing.", "Pricing Recalculated");
}

function fetchDebug() {
  cabinetList.fancytree("getTree").visit(function(line) {
    console.log(line);
  });
}

function genKey() {
  return new Date().getTime() * Math.random(999);
}

function updateShipDate() {
  let dts = $("#days_to_ship").val();

  $.post("/html/pricing/ajax/global_actions.php?action=calcShipDate", {days_to_ship: dts, room_id: active_room_id}, function(data) {
    let result = JSON.parse(data);

    $("#calcd_ship_date").html(result['ship_date']);
    $("#calcd_del_date").html(result['del_date']);
  });
}

function checkNote(noteBox, noteField, followupDateField, followupOfField) {
  let note = $(noteField).val();

  if(note !== '') {
    console.log('Field Value:' + note);

    let d = new Date();
    let month = d.getMonth() + 1;
    let date = month + '/' + d.getDate() + '/' + d.getFullYear();

    let hours = d.getHours();
    let mins = d.getMinutes();
    let secs = d.getSeconds();
    let am_pm = null;

    if(hours < 12) { am_pm = 'AM'; } else { am_pm = 'PM';}
    if(hours === 0) { hours = 12; }
    if(hours > 12) { hours = hours - 12; }

    mins = mins + '';
    if(mins.length === 1) { mins = "0" + mins; }

    secs = secs + '';
    if(secs.length === 1) { secs = "0" + secs; }

    let time = hours + ':' + mins + ':' + secs + ' ' + am_pm;

    let followup_on = $(followupDateField).val();
    let followup_by = $(followupOfField).find(":selected").text();
    let followup = '';

    if(followup_on !== '') {
      followup = '(Followup by ' + followup_by +' on ' + followup_on + ')';
    }

    let output = '<tr><td width="26px" style="padding-right:5px;"><button class="btn waves-effect btn-primary pull-right reply_to_inquiry" id="10381"> ' +
      '<i class="zmdi zmdi-mail-reply"></i> </button></td>  ' +
      '<td>' + note + ' -- <small><em>' + nameOfUser + ' on ' + date + ' ' + time + ' ' + followup + ' </em></small></td></tr>' +
      '<tr style="height:2px;"><td colspan="2" style="background-color:#000;"></td></tr>' +
      '<tr style="height:5px;"><td colspan="2"></td></tr>';

    let row = 3;

    if(noteBox === '.so_note_box') {
      row = 2;
    }

    $(noteBox).find("table tr:eq(" + row + ")").before(output);

    $(noteField).val('');
    $(followupDateField).val('');
    $(followupOfField).val('null');
  }
}

var mouseX, mouseY;

$(document).mousemove(function(e) {
  mouseX = e.pageX;
  mouseY = e.pageY;
});

var CLIPBOARD = null;
var cabinetList = $("#cabinet_list");
var catalog = $("#catalog_categories");
var itemModifications = $("#item_modifications");

$("body")
  .on("keyup", "#treeFilter", function() { // filters per keystroke on search catalog
    // grab this value and filter it down to the node needed
    catalog.fancytree("getTree").filterNodes($(this).val());

    // TODO: Enable filter dropdown allowing keywords - expected result, type microwave and get nomenclature available under microwave
    // TODO: https://github.com/mar10/fancytree/issues/551
  })
  .on("keyup", "#addItemFilter", function() { // filters per keystroke on search catalog
    // grab this value and filter it down to the node needed
    $("#add_item_categories").fancytree("getTree").filterNodes($(this).val());

    // TODO: Enable filter dropdown allowing keywords - expected result, type microwave and get nomenclature available under microwave
    // TODO: Resolution can be found in CRM tree (for searching companies)
  })

  .on("change", "select", function() {
    checkDropdown();
  })

  .on("click", "#save", function() {
    //<editor-fold desc="Disabled Field Serialize function">
    function disabledSerialize(field) {
      let disabled = field.find(":input:disabled").removeAttr("disabled");
      let info = field.serialize();
      disabled.attr('disabled', 'disabled');

      return info;
    }
    //</editor-fold>

    //<editor-fold desc="Room Data">
    var val_array = {};
    var customVals = null;
    var cab_list = JSON.stringify(getMiniTree(cabinetList));

    // SUPER IMPORTANT to enable and then re-disable select fields during serialization
    var cabinet_specifications = disabledSerialize($("#cabinet_specifications"));
    var accounting_notes = disabledSerialize($("#accounting_notes"));

    $("select").each(function() {
      var ele = $(this);
      var field = $(this).attr('id');
      var custom_fields = ['X', 'Xxx', 'AX', 'DX', 'TX', 'Xx', 'WX', '1cXXXX', '3gXXXX', 'HW', 'KW'];

      if($.inArray(ele.val(), custom_fields) >= 0) {
        val_array[field] = {};

        ele.parent().find('input').each(function() {
          val_array[field][$(this).attr('name')] = $(this).val();
        });
      }
    });

    customVals = JSON.stringify(val_array);

    $.post("/html/pricing/ajax/global_actions.php?action=roomSave&room_id=" + active_room_id, {cabinet_list: cab_list, customVals: customVals, cabinet_specifications: cabinet_specifications, accounting_notes: accounting_notes}, function(data) {
      $('body').append(data);
    });
    //</editor-fold>

    // live notes for Room notes box
    checkNote('.room_note_box', '#room_notes', '#room_inquiry_followup_date', '#room_inquiry_requested_of');

    // live notes for SO notes box
    checkNote('.so_note_box', '#so #inquiry', '#so #inquiry_followup_date', '#so #inquiry_requested_of');

    unsaved = false;
  })
  .on("click", "#cabinet_list_save", function() {
    let cab_list = JSON.stringify(cabinetList.fancytree("getTree").toDict(true));
    let cat_id = $("#catalog").find(":selected").attr("id");

    $.post("/html/pricing/ajax/item_actions.php?action=saveCatalog&room_id=" + active_room_id, {cabinet_list: cab_list, catalog_id: cat_id}, function(data) {
      $("body").append(data);
    });
  })
  .on("click", "#terms_confirm", function() {
    let signature = $(".esig").val();
    let ip = $(".esig_id").html();
    let button = $("#terms_confirm");

    if($.trim(signature) !== '') {
      $.confirm({ // a confirmation box to tell them they are about to legally sign their life away
        title: "Sign & Agree to Quote",
        content: signature + ", you agree to the following:<br /><br />" +
          "Once submitted, the deposit of 50% will be drafted from your account within 24 hours.<br />" +
          "Once the deposit has been processed a shipping/delivery date will be provided.<br />" +
          "Final payment is due prior to delivery.<br />" +
          "You have the authorization to legally sign this document.<br /><br />" + ip,
        type: 'red',
        buttons: {
          'I Agree': function() {
            $.post("/html/pricing/ajax/global_actions.php?action=termsSign&room_id=" + active_room_id, {sig: signature}, function(data) {
              $("body").append(data);
            }).done(function() {
              button.hide();
            });
          },
          'I Disagree': function() {} // we're not doing anything
        }
      });
    }
  })
  .on("click", "#modalBracketSave", function() {
    var active_ops = $(".active_ops_" + active_room_id).map(function() { return $(this).data("opid"); }).get();
    var bracket_status = $("#bracketAdjustments").serialize();

    active_ops = JSON.stringify(active_ops);

    $.post("/html/pricing/ajax/global_actions.php?action=updateBracket&roomID=" + active_room_id, {active_ops: active_ops, bracket_status: bracket_status}, function(data) {
      $('body').append(data);
    });

    $("#modalGeneral").html("").modal("hide");

    unsaved = false;
  })

  .on("click", "#submit_for_quote", function() {
    let button = $(this);
    let cab_list = JSON.stringify(cabinetList.fancytree("getTree").toDict(true));

    $.confirm({ // a confirmation box to ensure they are intending to complete tasks
      title: "Are you sure you want to submit the quote?",
      content: "You are about to submit this quote. Once submitted you will be <strong>unable</strong> to modify the line items. Are you sure you would like to submit?",
      type: 'red',
      buttons: {
        yes: function() {
          $.post("/html/pricing/ajax/item_actions.php?action=submitQuote&room_id=" + active_room_id, {cabinet_list: cab_list}, function(data) {
            $("body").append(data);
          }).done(function() {
            button.val("Submitted").prop("disabled", true);
          });
        },
        no: function() {} // we're not doing anything
      }
    });
  })
  .on("click", "#dl_ord_file", function() {
    $("#dlORDfile").attr("src", "/html/pricing/ajax/make_ord.php?roomID=" + active_room_id);
  })
  .on("click", "#copy_room", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalCopyRoom&roomID=" + active_room_id, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#bracket_management", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalBracketMgmt&roomID=" + active_room_id, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#appliance_ws", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalApplianceWS&roomID=" + active_room_id, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#modalCopyRoom", function() {
    let replaceRoom = $("#modalCopyRoomData select[name='new_room'] :selected").text();

    $.confirm({
      title: "Are you sure you want to overwrite this rooms information?",
      content: "You are about to completely overwrite room information for " + replaceRoom + ". Are you sure?",
      type: 'red',
      buttons: {
        yes: function() {
          let info = $("#modalCopyRoomData").serialize();

          $.post("/html/pricing/ajax/global_actions.php?action=copyRoomInfo", {formInfo: info}, function(data) {
            $('body').append(data);
          });
        },
        no: function() {}
      }
    });
  })
  .on("click", "#catalog_recalculate", function() {
    fullRecalc();
  })
  .on("click", "#production_lock", function() {
    $("select").prop("disabled", false);
    $(this).hide();

    $.get("/html/pricing/ajax/global_actions.php?action=overrideProductionLock&roomID=" + active_room_id);
  })

  .on("change", "#sheet_type", function() {
    $.post("/html/search/appliance_ws_info.php?room_id=" + active_room_id + "&id=" + $(this).val(), function(data) {
      $(".sheet_data").html(data);

      $(":input", "#appliance_info").not(':button, :submit, :reset, :hidden, select').val('');
    });
  })
  .on("click", "#modalAppWSSave", function() {
    var formInfo = $("#appliance_info").serialize();

    $.post("/ondemand/room_actions.php?action=save_app_worksheet&room=" + active_room_id + "&" + formInfo, function(data) {
      if(data !== 'false') {
        $(".print_app_ws").attr("id", data);
        displayToast("success", "Successfully saved worksheet information.", "Worksheet Saved");
      } else {
        displayToast("error", "Unable to save worksheet. Please refresh your page.", "Unable to Save");
      }
    });

    $("#modalGeneral").html("").modal("hide");

    unsaved = false;
  })
  .on("click", ".load_app_worksheet", function(e) {
    e.stopPropagation();

    var id = $(this).attr("id");

    $.post("/ondemand/room_actions.php?action=load_app_worksheet&id=" + id, function(data) {
      var result = JSON.parse(data);
      var values = JSON.parse(result.values);

      $("#sheet_type").val(result.spec).trigger("change");
      $(".print_app_ws").attr("id", result.id);

      setTimeout(function() {
        $.each(values, function(key, value) {
          $("#" + key).val(value);
        });

        $("#notes").val(result.notes);
      }, 150);
    });
  })

  .on("click", ".wrapper", function() {
    if($(".info-popup").is(":visible")) {
      $(".info-popup").fadeOut();
    }
  })

  .on("keyup", ".esig", function() {
    if($.trim($(this).val()) !== '') {
      $("#terms_confirm").prop("disabled", false);
    } else {
      $("#terms_confirm").prop("disabled", true);
    }
  })

  .on("click", "#item_note", function() { // the click of the "Add Item" button
    // delNoData(); // wtc does this do?

    var root = cabinetList.fancytree("getRootNode");

    var node = root.addChildren({
      qty: 1,
      title: 'NOTE',
      price: 0.00,
      key: genKey(),
      icon: 'fa fa-commenting-o',
      name: 'Error',
      sqft: 0,
      singlePrice: 0.00,
      cabinet: 0,
      customNote: 1
    });

    let $tdList = $(node.tr).find(">td");

    $tdList.eq(4).html('<input type="text" class="form-control custom-line-item" placeholder="Custom Description..." data-id="' + node.key + '" >');
  })
  .on("click", "#item_custom_line", function() { // the click of the "Add Item" button
    // delNoData(); // wtc does this do?

    var root = cabinetList.fancytree("getRootNode");

    var node = root.addChildren({
      qty: 1,
      title: ' ',
      price: 0.00,
      key: genKey(),
      icon: 'fa fa-hand-o-right',
      name: 'Error',
      sqft: 0,
      singlePrice: 0.00,
      cabinet: 1,
      customPrice: 1,
      itemID: 1321,
      customNote: 1
    });

    console.log(node);

    let $tdList = $(node.tr).find(">td");

    $tdList.eq(4).html('<input type="text" class="form-control custom-line-item" placeholder="Custom Description..." data-id="' + node.key + '" >');
    $tdList.eq(9).html('<input type="text" class="form-control custom_price" placeholder="Price" data-id="' + node.key + '" >');
  })

  .on("click", ".delete_item", function() { // removes whatever is checked
    var tree = cabinetList.fancytree("getTree"); // get the tree

    tree.getFocusNode().remove(); // remove the focused node

    // re-render the tree deeply so that we can recalculate the line item numbers
    cabinetList.fancytree("getRootNode").render(true,true);

    recalcSummary();
  })
  .on("mouseenter", ".view_item_info", function() {
    // FIXME: Change this so the data isn't loaded on hover
    // FIXME: omg queries... should be able to JSON this data into memory quite easily

    let info = "";
    let thisEle = $(this);
    let infoPopup = $(".info-popup");

    $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: thisEle.data('id'), room_id: active_room_id}, function(data) {
      if(data !== '') {
        let result = JSON.parse(data);

        if(result.image !== null) {
          info += "<div class='image'><img src='/html/pricing/images/" + result.image + "' /></div>";
        }

        info += "<div class='right_content'><div class='header'><h4>" + result.title + "</h4></div>";
        info += "<div class='description'>" + result.description + "</div></div>";
      }
    }).done(function() {
      if(info !== '') {
        let infoHeight = infoPopup.height();
        let infoTop = mouseY - 190;
        let windowOverflow = $(window).scrollTop() + $(window).height();

        if((infoHeight + infoTop + 100) > windowOverflow) {
          infoPopup.css({"bottom": 0, "top": "inherit", "left": (mouseX - 20)});
        } else {
          infoPopup.css({"top": infoTop, "bottom": "inherit", "left": mouseX});
        }

        infoPopup.fadeIn(250).html(info);
      }
    });
  })
  .on("mouseleave", ".view_item_info", function() {
    $(".info-popup").fadeOut(250);
  })
  .on("click", ".add_item_mod", function() {
    $("#modalAddModification").modal('show');
  })
  .on("click", ".item_copy", function() {
    let activeNode = cabinetList.fancytree("getTree").getActiveNode().toDict(true);

    activeNode.key = genKey();

    if(activeNode.children !== null) {
      $.each(activeNode.children, function(key) {
        activeNode.children[key].key = genKey();
      });
    }

    cabinetList.fancytree("getRootNode").addChildren(activeNode);
  })

  .on("focus", ".qty_input", function() { // when clicking or tabbing to quantity
    $(this).select(); // auto-select the text
  })
  .on("keyup", ".qty_input", function() {
    let id = $(this).attr("data-id");

    cabinetList.fancytree("getTree").getNodeByKey(id).data.qty = $(this).val();
  })
  .on("change", ".qty_input", function() {
    recalcSummary();
  })

  .on("click", ".add_item_cabinet_list", function() {
    delNoData();

    var root = cabinetList.fancytree("getRootNode");
    let $tdList = $(root.tr).find(">td");

    $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: $(this).attr('data-id'), room_id: active_room_id}, function(data) {
      let itemInfo = JSON.parse(data);

      let fixedPrice = parseFloat(itemInfo.price).toFixed(2);

      let insertedNode = root.addChildren({
        qty: 1,
        title: itemInfo.sku,
        width: itemInfo.width,
        height: itemInfo.height,
        depth: itemInfo.depth,
        itemID: itemInfo.id,
        price: fixedPrice,
        key: genKey(),
        icon: itemInfo.icon,
        name: itemInfo.title,
        sqft: itemInfo.sqft,
        singlePrice: fixedPrice,
        cabinet: itemInfo.cabinet,
        addlMarkup: itemInfo.addlMarkup
      });



      recalcSummary();
    });
  })
  .on("click", "#editCatalogLock", function() {
    if($(this).hasClass('fa-lock')) {
      $(this).removeClass('fa-lock').addClass('fa-unlock');
    } else {
      $(this).removeClass('fa-unlock').addClass('fa-lock');
    }
  })
  .on("click", "#catalog_add_item", function() {
    $.get("/html/pricing/ajax/modify_item.php", function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })

  .on("change", "#catalog", function() {
    let id = $(this).find(":selected").attr("id");

    let catalogData = {
      url: '/html/pricing/ajax/nav_menu.php',
      type: 'POST',
      data: {
        catalog: id
      },
      dataType: 'json'
    };

    catalog.fancytree('getTree').reload(catalogData);
  })

  .on("change", ".item_hinge", function() {
    let node = cabinetList.fancytree("getActiveNode");

    node.data.hinge = $(this).find(":selected").val();
  })
  .on("keyup", ".itm_width", function() {
    let id = $(this).attr("data-id");
    let node = cabinetList.fancytree("getTree").getNodeByKey(id);

    node.data.width = $(this).val();
    node.data.price = footCalc(node);

    recalcSummary();
  })
  .on("keyup", ".itm_height", function() {
    let id = $(this).attr("data-id");
    let node = cabinetList.fancytree("getTree").getNodeByKey(id);

    node.data.height = $(this).val();
    node.data.price = footCalc(node);

    recalcSummary();
  })
  .on("keyup", ".itm_depth", function() {
    let id = $(this).attr("data-id");
    let node = cabinetList.fancytree("getTree").getNodeByKey(id);

    node.data.depth = $(this).val();
    node.data.price = footCalc(node);

    recalcSummary();
  })

  .on("keyup", "#modificationsFilter", function() { // filters per keystroke on search catalog
    // grab this value and filter it down to the node needed
    itemModifications.fancytree("getTree").filterNodes($(this).val());

    // TODO: Enable filter dropdown allowing keywords - expected result, type microwave and get nomenclature available under microwave
    // TODO: https://github.com/mar10/fancytree/issues/551
  })
  .on("click", "#modificationAddSelected", function() {
    let modifications = itemModifications.fancytree("getTree").getSelectedNodes();
    let cablist = cabinetList.fancytree("getTree").getActiveNode();

    let outputPrice = 0.00;

    $.each(modifications, function(i, v) {
      let addlInfo = '';

      if(v.data.addlInfo !== undefined) {
        addlInfo = " " + v.data.addlInfo;
      }

      if(v.data.percentMarkup === 1) {
        outputPrice = parseFloat(cablist.data.price * (v.data.price / 100)).toFixed(2);
      } else {
        outputPrice = parseFloat(v.data.price).toFixed(2);
      }

      let width = null;
      let height = null;

      if(v.data.sqft === 1) {
        width = parseFloat(cablist.data.width);
        height = parseFloat(cablist.data.height);

        outputPrice = ((width * height) / 144) * outputPrice;
      }

      var $tdList = $(cablist.tr).find(">td");

      switch(parseInt(v.key)) {
        case 90048:
        case 90051:
          $tdList.eq(5).find("input").val(addlInfo.trim());
          cablist.data.width = addlInfo.trim();
          break;
        case 90049:
        case 90052:
          $tdList.eq(6).find("input").val(addlInfo.trim());
          cablist.data.height = addlInfo.trim();
          break;
        case 90050:
        case 90053:
          $tdList.eq(7).find("input").val(addlInfo.trim());
          cablist.data.depth = addlInfo.trim();
          break;
      }

      cablist.addChildren({
        qty: 1,
        title: v.title,
        itemID: v.data.itemID,
        price: outputPrice,
        key: genKey(),
        width: width,
        height: height,
        icon: v.icon,
        name: v.data.description + addlInfo,
        sqft: v.data.sqft,
        linft: v.data.linft,
        singlePrice: outputPrice,
        cabinet: v.data.cabinet,
        addlMarkup: v.data.addlMarkup,
        subLine: true,
        pctMarkup: v.data.percentMarkup
      });
    });

    cablist.setExpanded();

    $("#modalAddModification").modal("hide");
  })
  .on("keyup", ".modAddlInfo", function() {
    let id = $(this).attr("id");

    itemModifications.fancytree("getTree").getNodeByKey(id).data.addlInfo = $(this).val();
  })


  .on("click", "#category_collapse", function() {
    catalog.fancytree("getTree").visit(function(node){
      node.setExpanded(false);
    });
  })

  .on("click", "#overrideShipDate", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalOverrideShipping&roomID=" + active_room_id, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#modalShippingUpdate", function() {
    let shippingInfo = $("#modalShippingOverrideData").serialize();

    $.post("/html/pricing/ajax/global_actions.php?action=overrideShipping&roomID=" + active_room_id, {info: shippingInfo}, function(data) {
      let ship_results = JSON.parse(data);

      $("#calcd_ship_date").text(ship_results.ship_date);
      $("#calcd_del_date").text(ship_results.del_date);

      displayToast("success", "Successfully updated ship and delivery date.", "Ship/Delivery Updated");

      $("#modalGeneral").html("").modal('hide');
    });

    unsaved = false;
  })

  /*.on("click", ".option", function(e) {
    let dropdown_list = $(this).parent().parent();

    if(dropdown_list.attr("data-for") === 'ship_via') {
      let ship_info = $("input[name='ship_to_name']").parent().parent();

      if($(this).attr("data-value") === '4') {
        ship_info.hide();
        ship_info.next("tr").hide();
        ship_info.next("tr").next("tr").hide();
      } else {
        ship_info.show();
        ship_info.next("tr").show();
        ship_info.next("tr").next("tr").show();
      }
    } else if(dropdown_list.attr("data-for") === 'product_type') {
      let species_grade = $("#species_grade");
      let door_design = $("#door_design");
      let construction_method = $("#construction_method");

      if($(this).attr("data-value") === 'L') {
        if(species_grade.val() === '') {
          species_grade.attr("value", "Me").parent().find(".selected").html("Melamine");
        }

        if(door_design.val() === '') {
          door_design.attr("value", "ME0").parent().find(".selected").html("Melamine");
        }

        if(construction_method.val() === '') {
          construction_method.attr("value", "C").parent().find(".selected").html("Closet - Cam");
        }
      }
    } else if(dropdown_list.attr("data-for") === 'order_status') {
      if($(this).attr("data-value") === '#') {
        $(".estimated").text('Est. ');
      } else {
        $(".estimated").text('');
      }

      // if we're switching to production, we need to prompt and see if we should recalculate the ship date
      if($(this).attr("data-value") === '$') {
        $.confirm({
          title: "Update ship date?",
          content: "Do you wish to update the ship date now?",
          type: 'red',
          buttons: {
            yes: function() {
              updateShipDate();
            },
            no: function() {}
          }
        });
      }
    }
  })*/

  .on("blur", ".custom_price", function() {
    let id = $(this).attr("data-id");
    let node = cabinetList.fancytree("getTree").getNodeByKey(id);

    node.data.price = parseFloat($(this).val().replace(/[^0-9-.]/g, ''));

    recalcSummary();
  })
  .on("focus mouseup", ".custom_price", function() {
    $(this).select();
  })
  .on("keyup", ".custom-line-item", function() {
    let id = $(this).attr("data-id");
    let node = cabinetList.fancytree("getTree").getNodeByKey(id);

    node.data.name = $(this).val();
  })
  .on("click", "#detailed_item_summary", function() {
    $("#modalDetailedItemList").modal("show")
  })

  .on("click", "#pricingSpeciesGrade, #pricingDoorDesign", function() {
    let speciesGrade = $("#species_grade").val();
    let doorDesign = $("#door_design").val();

    $.post("/html/pricing/ajax/global_actions.php?action=getPriceGroup&speciesGrade=" + speciesGrade + "&doorDesign=" + doorDesign, function(data) {
      $("#cab_spec_pg").text(data);
      priceGroup = data;
    });

    recalcSummary();
  })

  .on("click", "#modalSaveCatItemSubmit", function() {
    let node = catalog.fancytree("getTree").getActiveNode();
    let fields = $("#catalogAddEditItem").serialize();

    let sku = $("#catalogAddEditItem input[name='title']").val();

    $.post("/html/pricing/ajax/item_actions.php?action=updateItem", {key: node.key, folder: node.folder, update: fields}, function(data, status) {
      $("body").append(data);

      if(status === 'success') {
        let title = sku +
          '<span class="actions">' +
            '<div class="info_container"><i class="fa fa-info-circle primary-color view_item_info" data-id="' + node.key + '"></i></div>' +
            '<i class="fa fa-plus-circle success-color add_item_cabinet_list" data-id="' + node.key + '" title="Add To Cabinet List"></i>' +
          '</span>';

        node.setTitle(title);
        $("#modalGeneral").html(data).modal("hide");
      }
    });

    unsaved = false;
  })
  .on("click", "#modalAddCatItemSubmit", function() {
    let node = catalog.fancytree("getTree").getActiveNode();
    let fields = $("#catalogAddEditItem").serialize();
    let catID = node.key;
    let createFolder = false;
    let folderType = null;

    let sku = $("#catalogAddEditItem input[name='title']").val();

    if($(this).attr("data-type") === 'newSameFolder') {
      createFolder = true;
      folderType = 'alongside';
    } else if($(this).attr('data-type') === 'newSubFolder') {
      createFolder = true;
      folderType = 'child';
    }

    if(!node.isFolder()) {
      catID = node.parent.key;
    }

    $.post("/html/pricing/ajax/item_actions.php?action=createItem", {key: catID, folder: createFolder, folderType: folderType, update: fields}, function(data, status) {
      if(status === 'success') {
        let insertID = data;

        if(createFolder) {
          if(folderType === 'alongside') {
            catalog.fancytree("getTree").getNodeByKey(catID).parent.addChildren({'key': insertID, 'title': sku, 'folder': true});
          } else {
            catalog.fancytree("getTree").getNodeByKey(catID).addChildren({'key': insertID, 'title': sku, 'folder': true});
          }

          displayToast('success', 'Successfully created category', 'Category Created');

          $("#modalGeneral").html(data).modal("hide");
        } else {
          let title = sku +
            '<span class="actions">' +
            '<div class="info_container"><i class="fa fa-info-circle primary-color view_item_info" data-id="' + insertID + '"></i></div>' +
            '<i class="fa fa-plus-circle success-color add_item_cabinet_list" data-id="' + insertID + '" title="Add To Cabinet List"></i>' +
            '</span>';

          catalog.fancytree("getTree").getNodeByKey(catID).addChildren({'key': insertID, 'icon': 'fa fa-magic', 'title': title, 'is_item': true, 'qty': 1});

          displayToast('success', 'Successfully created item.', 'Item Created');

          $("#modalGeneral").html(data).modal("hide");
        }
      } else {
        displayToast('error', 'Unable to create the requested item/category.', 'Error');
      }
    });

    unsaved = false;
  })

  .on("change", "#left_menu_options", function() {
    let result = $(this).val();
    let asearch = $(".action_search");
    let newSourceOption = null;

    switch(result) {
      case 'catalog':
        asearch.show();

        newSourceOption = {
          url: '/html/pricing/ajax/nav_menu.php',
          type: 'POST',
          dataType: 'json'
        };

        catalog.fancytree("getTree").reload(newSourceOption);
        break;
      case 'samples':
        asearch.hide();

        newSourceOption = {
          url: '/html/pricing/ajax/samples_menu.php',
          type: 'POST',
          dataType: 'json'
        };

        catalog.fancytree("getTree").reload(newSourceOption);
        break;
    }
  })

  .on("change", ".sample_checkbox", function() {
    $('.sample_checkbox').not(this).prop('checked', false);
  })

  .on("click", "#ship_date_recalc", function() {
    if($("#order_status").val() === '#') {
      updateShipDate();
    } else {
      displayToast('error', 'Unable to recalculate ship date, this room is not in quote anymore.', 'Unable to Recalculate.');
    }
  })
  .on("click", "#overrideShipCost", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalOverrideShipCost&roomID=" + active_room_id, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#modalShippingCostOverride", function() {
    let shippingInfo = $("#modalShippingOverrideCost").serialize();

    $.post("/html/pricing/ajax/global_actions.php?action=shipCostOverride&roomID=" + active_room_id, {info: shippingInfo}, function(data, response) {
      if(response === 'success') {
        $("#shipping_cost").attr('data-cost', data).text('$' + data);

        displayToast("success", "Successfully overrode ship cost.", "Ship Cost Overriden");

        $("#modalGeneral").html("").modal('hide');

        recalcSummary();
      } else {
        displayToast("error", "Unable to update ship cost. Please contact IT.", "Ship Cost Override Error");
      }
    });

    unsaved = false;
  })
;