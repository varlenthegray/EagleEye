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

    // set the line total equal to the quantity * price
    let lineTotal = qty * price;

    // now update the last column (total) with the final price for that line item
    $tdList.eq(8).text(lineTotal.formatMoney());

    // if the line item is a cabinet only (excludes tops, accessories, fillers, moldings) then we're adding that to a cabinet only price (inset specific pricing)
    if(line.data.cabinet === '1') {
      // our cabinet only total goes up
      cabinet_only_total += parseFloat(lineTotal);
    }

    // this is the final running total for the system
    line_total += parseFloat(lineTotal);
  });

  // display the total
  $("#itemListTotal").text(line_total.formatMoney());
  //******************************************************************


  //******************************************************************
  // done parsing per line, moving into the global charges for the page

  //// Glaze Technique
  let gt_pct = $("#gt_pct");
  let gt_amt = $("#gt_amt");
  let gt_markup = 0.00; // glaze technique markup

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

  // calculate out the cost of the glaze technique
  let gt_cost = line_total * gt_markup;

  // Glaze Technique fields
  gt_amt.text(gt_cost.formatMoney());
  gt_pct.text((gt_markup * 100).toFixed(2) + "%");

  // add the glaze technique to the total amount of global upcharges
  global_cab_charges += gt_cost;

  // Grab the shipping price
  let shipPrice = $("#shipping_cost").attr("data-cost");
  global_room_charges += parseFloat(shipPrice);

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
}

// @footCalc() - calculates by square foot or linear foot
function footCalc(node) {
  let $tdList = $(node.tr).find(">td");
  let outprice = 0.00;

  // noinspection JSCheckFunctionSignatures
  if(parseInt(node.data.sqft) === 1) {
    let line_sqft = (parseFloat(node.data.width) * parseFloat(node.data.depth)) / 144;
    let line_total = line_sqft * node.data.singlePrice;

    $tdList.eq(8).text(line_total.formatMoney());

    outprice =  line_total;
  } else if(parseInt(node.data.linft) === 1) {
    let line_linft = (parseFloat(node.data.width) / 12) * node.data.singlePrice;

    $tdList.eq(8).text(line_linft.formatMoney());

    outprice = line_linft;
  } else {
    outprice = node.data.price;
  }

  return outprice;
}

function fullRecalc() {
  cabinetList.fancytree("getTree").visit(function(line) {
    let itemID = line.data.itemID;
    let key = line.key;

    var tree = cabinetList.fancytree("getTree");

    $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: itemID, room_id: active_room_id}, function(data) {
      let itemInfo = JSON.parse(data);
      let fixedPrice = parseFloat(itemInfo.price).toFixed(2);
      let node = tree.getNodeByKey(key);

      node.title = itemInfo.sku;
      node.data.price = fixedPrice;
      node.icon = itemInfo.icon;
      node.data.name = itemInfo.title;
      node.data.sqft = itemInfo.sqft;
      node.data.singlePrice = fixedPrice;
      node.data.cabinet = itemInfo.cabinet;
      node.data.addlMarkup = itemInfo.addl_markup;
    });
  });
}

function fetchDebug() {
  cabinetList.fancytree("getTree").visit(function(line) {
    console.log(line);
  });
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
  .on("keyup", "#modificationsFilter", function() { // filters per keystroke on search catalog
    // grab this value and filter it down to the node needed
    itemModifications.fancytree("getTree").filterNodes($(this).val());

    // TODO: Enable filter dropdown allowing keywords - expected result, type microwave and get nomenclature available under microwave
    // TODO: https://github.com/mar10/fancytree/issues/551
  })
  .on("click", "#catalog_add_custom", function() { // the click of the "Add Item" button
    delNoData();

    var root = cabinetList.fancytree("getRootNode");
    var child = root.addChildren({
      title: "Nomenclature...",
      tooltip: "Type your nomenclature here.",
      name: '<input type="text" class="form-control qty_input" value="1" placeholder="Qty" />' // FIXME: This doesn't work, need some sort of typeable field
    });
  })
  .on("click", "#catalog_remove_checked", function() { // removes whatever is checked
    var tree = cabinetList.fancytree("getTree"), // get the tree
      selected = tree.getSelectedNodes(); // define what is selected

    // for every selected node
    selected.forEach(function(node) {
      node.remove(); // remove it
    });

    // re-render the tree deeply so that we can recalculate the line item numbers
    cabinetList.fancytree("getRootNode").render(true,true);

    // hide the remove items button, there are no items to remove now
    $(this).hide();

    recalcSummary();
  })
  .on("click", "#save", function() {
    /*//<editor-fold desc="Cabinet List">
    var cab_list = JSON.stringify(getMiniTree(cabinetList));
    var cat_id = $("#catalog").find(":selected").attr("id");

    $.post("/html/pricing/ajax/item_actions.php?action=saveCatalog&room_id=" + active_room_id, {cabinet_list: cab_list, catalog_id: cat_id}, function(data) {
      $("body").append(data);
    });
    //</editor-fold>*/

    //<editor-fold desc="Room Data">
    var val_array = {};
    var cabinet_specifications = $("#cabinet_specifications").serialize();
    var customVals = null;
    var accounting_notes = $("#accounting_notes").serialize();
    var cab_list = JSON.stringify(getMiniTree(cabinetList));

    $("input[type='hidden']").each(function() {
      var ele = $(this);
      var field = $(this).attr('id');
      var custom_fields = ['X', 'Xxx', 'AX', 'DX', 'TX', 'Xx', 'WX', '1cXXXX', '3gXXXX', 'HW', 'KW'];

      if($.inArray(ele.val(), custom_fields) >= 0) {
        val_array[field] = {};

        ele.parent().find('.selected').find('input').each(function() {
          val_array[field][$(this).attr('name')] = $(this).val();
        });
      }
    });

    customVals = JSON.stringify(val_array);

    $.post("/html/pricing/ajax/global_actions.php?action=roomSave&room_id=" + active_room_id, {cabinet_list: cab_list, customVals: customVals, cabinet_specifications: cabinet_specifications, accounting_notes: accounting_notes}, function(data) {
      $('body').append(data);
    });
    //</editor-fold>

    unsaved = false;
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

      root.addChildren({
        qty: 1,
        title: itemInfo.sku,
        width: itemInfo.width,
        height: itemInfo.height,
        depth: itemInfo.depth,
        itemID: itemInfo.id,
        price: fixedPrice,
        key: new Date().getTime() * Math.random(999),
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

    // hide the remove items button, there are no items to remove now
    $(this).hide();
  })
  .on("click", "#cabinet_list_save", function() {
    let cab_list = JSON.stringify(cabinetList.fancytree("getTree").toDict(true));
    let cat_id = $("#catalog").find(":selected").attr("id");

    $.post("/html/pricing/ajax/item_actions.php?action=saveCatalog&room_id=" + active_room_id, {cabinet_list: cab_list, catalog_id: cat_id}, function(data) {
      $("body").append(data);
    });
  })
  .on("mouseenter", ".view_item_info", function() {
    // FIXME: Change this so the data isn't loaded on hover
    // FIXME: omg queries... should be able to JSON this data into memory quite easily

    let info = "";
    let thisEle = $(this);
    let infoPopup = $(".info-popup");

    $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: thisEle.data('id'), room_id: active_room_id}, function(data) {
      let result = JSON.parse(data);

      if(result.image !== null) {
        info += "<div class='image'><img src='/html/pricing/images/" + result.image + "' /></div>";
      }

      info += "<div class='right_content'><div class='header'><h4>" + result.title + "</h4></div>";
      info += "<div class='description'>" + result.description + "</div></div>";
    }).done(function() {
      let infoHeight = infoPopup.height();
      let infoTop = mouseY - 91;
      let windowOverflow = $(window).scrollTop() + $(window).height();

      if((infoHeight + infoTop + 100) > windowOverflow) {
        infoPopup.css({"bottom": 0, "top": "inherit", "left" : mouseX});
      } else {
        infoPopup.css({"top": infoTop, "bottom": "inherit", "left": mouseX});
      }

      infoPopup.fadeIn(250).html(info);

    });
  })
  .on("mouseleave", ".view_item_info", function() {
    $(".info-popup").fadeOut(250);
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
  .on("click", ".wrapper", function() {
    if($(".info-popup").is(":visible")) {
      $(".info-popup").fadeOut();
    }
  })
  .on("change", ".item_hinge", function() {
    let node = cabinetList.fancytree("getActiveNode");

    node.data.hinge = $(this).find(":selected").val();
  })
  .on("change", ".item_finish", function() {
    let node = cabinetList.fancytree("getActiveNode");

    node.data.finish = $(this).find(":selected").val();
  })
  .on("click", "#modificationAddSelected", function() {
    let modifications = itemModifications.fancytree("getTree").getSelectedNodes();
    let cablist = cabinetList.fancytree("getTree").getActiveNode();

    $.each(modifications, function(i, v) {
      let addlInfo = '';

      if(v.data.addlInfo !== undefined) {
        addlInfo = " by " + v.data.addlInfo;
      }

      let fixedPrice = parseFloat(v.data.price).toFixed(2);

      cablist.addChildren({
        qty: 1,
        title: v.title,
        itemID: v.data.id,
        price: fixedPrice,
        key: new Date().getTime() * Math.random(999),
        icon: v.icon,
        name: v.data.description + addlInfo,
        sqft: v.sqft,
        linft: v.data.linft,
        singlePrice: fixedPrice,
        cabinet: v.data.cabinet,
        addlMarkup: v.data.addlMarkup
      });
    });

    cablist.setExpanded();

    $("#modalAddModification").modal("hide");
  })
  .on("change", "#ext_carcass_same", function() {
    if($(this).is(":checked")) {
      $(".ext_finish_block").hide();
    } else {
      $(".ext_finish_block").show();
    }
  })
  .on("change", "#int_carcass_same", function() {
    if($(this).is(":checked")) {
      $(".int_finish_block").hide();
    } else {
      $(".int_finish_block").show();
    }
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
  .on("click", "#category_collapse", function() {
    catalog.fancytree("getTree").visit(function(node){
      node.setExpanded(false);
    });
  })
  .on("click", "#global_info", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalGlobals&roomID=" + active_room_id, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#modalGlobalsUpdate", function() {
    let globalInfo = $("#modalGlobalData").serialize();

    $.post("/html/pricing/ajax/global_actions.php?action=updateGlobals&roomID=" + active_room_id, {globalInfo: globalInfo}, function(data) {
      $("body").append(data);
      $("#modalGeneral").html("").modal('hide');
    });
  })
  .on("click", "#appliance_ws", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalApplianceWS&roomID=" + active_room_id, function(data) {
      $("#modalGeneral").html(data).modal("show");
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
  .on("click", "#bracket_management", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalBracketMgmt&roomID=" + active_room_id, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#modalBracketSave", function() {
    var active_ops = $(".active_ops_" + active_room_id).map(function() { return $(this).data("opid"); }).get();
    var bracket_status = $("#bracketAdjustments").serialize();

    active_ops = JSON.stringify(active_ops);

    $.post("/html/pricing/ajax/global_actions.php?action=updateBracket&roomID=" + active_room_id, {active_ops: active_ops, bracket_status: bracket_status}, function(data) {
      $('body').append(data);
    });

    $("#modalGeneral").html("").modal("hide");
  })
  .on("click", ".option", function() {
    let dropdown_list = $(this).parent().parent();

    if(dropdown_list.attr("data-for") === 'ship_via') {
      let ship_info = $("input[name='ship_to_1']").parent().parent();

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
    }
  })
  .on("click", "#ship_date_recalc", function() {
    let dts = $("#days_to_ship").val();

    $.post("/html/pricing/ajax/global_actions.php?action=calcShipDate", {days_to_ship: dts, room_id: active_room_id}, function(data) {
      let result = JSON.parse(data);

      $("#calcd_ship_date").html(result['ship_date']);
      $("#calcd_del_date").html(result['del_date']);
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
  .on("keyup", ".esig", function() {
    if($.trim($(this).val()) !== '') {
      $("#terms_confirm").prop("disabled", false);
    } else {
      $("#terms_confirm").prop("disabled", true);
    }
  })
  .on("keyup", ".modAddlInfo", function() {
    let id = $(this).attr("id");

    itemModifications.fancytree("getTree").getNodeByKey(id).data.addlInfo = $(this).val();
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
  .on("click", ".option", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=getPriceGroup&room_id=" + active_room_id, function(data) {
      $("#cab_spec_pg").text(data);
      priceGroup = data;
    });

    recalcSummary();
  })
;