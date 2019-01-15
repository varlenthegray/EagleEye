/*global globalFunctions*//*global getMiniTree*//*global jQuery*//*global document*//*global cabinetList*//*global calcShipInfo*//*global displayToast*//*global active_room_id*//*global catalog*//*global unsaved:true*//*global itemModifications*//*global priceGroup:true*//*global FormData*/

jQuery.expr.filters.offscreen = function(el) {
  var rect = el.getBoundingClientRect();
  return ((rect.x + rect.width) < 0 || (rect.y + rect.height) < 0 || (rect.x > window.innerWidth || rect.y > window.innerHeight));
};

var pricingVars = {
  nameOfUser: null,
  roomQry: null,
  vinInfo: null,
  shipCost: null
};

var pricingFunction = {
  productTypeSwitch: function() {
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
        setPcts({'green': {'ship_days': 26, 'pct': 0}, 'yellow': {'ship_days': 19, 'pct': 0.25}, 'orange': {'ship_days': 13, 'pct': 0.5}, 'red': {'ship_days': 6, 'pct': 0.5}});
        break;

      case 'P':
        setPcts({'green': {'ship_days': 26, 'pct': 0}, 'yellow': {'ship_days': 19, 'pct': 0.25}, 'orange': {'ship_days': 13, 'pct': 0.5}, 'red': {'ship_days': 6, 'pct': 0.5}});
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
  },
  delNoData: function() {
    let getNegNode = cabinetList.fancytree("getTree").getNodeByKey('-1');

    if(getNegNode !== null) {
      cabinetList.fancytree("getTree").getNodeByKey('-1').remove();
    }
  },
  recalcSummary: function() {
    let global_room_charges = 0.00, // global room charges
      global_cab_charges = 0.00, // global cabinet charges total
      line_total = 0.00, // total of the item list
      cabinet_only_total = 0.00, // cabinet only total
      cabinet_only_skus = '', // a list (for transparency report) of cabinets only
      non_cabinet_total = 0.00, // non-cabinet total (for transparency report)
      non_cabinet_skus = '', // non-cabinet SKU's (for transparency report)
      shipVia = pricingVars.roomQry['ship_via']; // shipping method

    //<editor-fold desc="Parse per line of cabinet list">
    cabinetList.fancytree("getTree").visit(function(line) {
      let qty = parseInt(line.data.qty),
        price = parseFloat(line.data.price),
        $tdList = $(line.tr).find(">td");

      // if there is an additional markup (% markup based on species/grade inside of database for nomenclature) defined in the system for this line (wood tops right now)
      if(line.data.addlMarkup !== undefined && line.data.addlMarkup !== '' && line.data.addlMarkup !== null) {
        let addlMarkup = JSON.parse(line.data.addlMarkup); // grab the information into JSON value

        // for the species and markups available, grab each of them
        $.each(addlMarkup, function(i, v) {
          // this is going to be the main focus of what drives the markup, i.e. species_grade
          let curVal = pricingVars.roomQry[i];

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

      //<editor-fold desc="Calculate per line price">
      if(pricingVars.roomQry['product_type'] === 'W') {
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
      //</editor-fold>

      //<editor-fold desc="Determine what kind of total to add to and what the list of items are">
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
      //</editor-fold>

      // this is the final running total for the system
      line_total += parseFloat(lineTotal);
    });

    // remove the excess comma and space from the cabinet SKU list
    cabinet_only_skus = cabinet_only_skus.slice(0, -2);

    // remove the excess comma and space from the non-cabinet SKU list
    non_cabinet_skus = non_cabinet_skus.slice(0, -2);

    // display the total
    $("#itemListTotal").text(line_total.formatMoney());
    //</editor-fold>

    //// Glaze Technique
    let gt_pct = $("#gt_pct"), gt_amt = $("#gt_amt");
    let ggard_pct = $("#ggard_pct"), ggard_amt = $("#ggard_amt");
    let fcode_pct = $("#fc_pct"), fcode_amt = $("#fc_amt");
    let sheen_pct = $("#sheen_pct"), sheen_amt = $("#sheen_amt");

    // gets the markup of Glaze Technique based on the room glaze technique variable
    let gt_markup = pricingVars.vinInfo['glaze_technique'][pricingVars.roomQry['glaze_technique']].markup;
    let ggard_markup = pricingVars.vinInfo['green_gard'][pricingVars.roomQry['green_gard']].markup;
    let fcode_markup = pricingVars.vinInfo['finish_code'][pricingVars.roomQry['finish_code']].markup;
    let sheen_markup = pricingVars.vinInfo['sheen'][pricingVars.roomQry['sheen']].markup;

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

    // Grab the shipping price IF it's not pickup
    if(shipVia !== '4') {
      global_room_charges += parseFloat(pricingVars.shipCost);
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

    if($("#jobsite_delivery").is(":checked") && !$("#multi_room_ship").is(":checked")) {
      global_room_charges += 150.00;
    }

    let subtotal = netPrice + global_room_charges;

    // TODO: We're not adding credit card here right now, we need to add that in

    //// Update the subtotal
    $("#finalSubTotal").text(subtotal.formatMoney());

    //// Update the total
    $("#finalTotal").text(subtotal.formatMoney());

    //// Update the required deposit
    let deposit = subtotal * 0.5;
    $("#finalDeposit").text(deposit.formatMoney());
    //******************************************************************

    //<editor-fold desc="Transparency report">
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
    //</editor-fold>
  },
  footCalc: function(node) {
    // @footCalc() - calculates by square foot or linear foot
    let $tdList = $(node.tr).find(">td");
    let outprice = node.data.price;

    // noinspection JSCheckFunctionSignatures
    if(node.data.width > 0 && node.data.height > 0) {
      if(parseInt(node.data.sqft) === 1) {
        let line_sqft = (parseFloat(node.data.width) * parseFloat(node.data.height)) / 144;
        let line_total = line_sqft * node.data.singlePrice;

        $tdList.eq(9).text(line_total.formatMoney());

        outprice = line_total;
      } else if(parseInt(node.data.linft) === 1) {
        let line_linft = (parseFloat(node.data.width) / 12) * node.data.singlePrice;

        $tdList.eq(9).text(line_linft.formatMoney());

        outprice = line_linft;
      }
    }

    return outprice;
  },
  fullRecalc: function() {
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
        pricingFunction.recalcSummary();
      });
    });

    displayToast("success", "Successfully recalculated the pricing.", "Pricing Recalculated");
  },
  fetchDebug: function() {
    cabinetList.fancytree("getTree").visit(function(line) {
      console.log(line);
    });
  },
  genKey: function() {
    return new Date().getTime() * Math.random(999);
  },
  updateShipDate: function() {
    let dts = $("#days_to_ship").val();

    $.post("/html/pricing/ajax/global_actions.php?action=calcShipDate", {days_to_ship: dts, room_id: active_room_id}, function(data) {
      let result = JSON.parse(data);

      $("#calcd_ship_date").html(result['ship_date']);
      $("#calcd_del_date").html(result['del_date']);
    });
  },
  checkNote: function(noteBox, noteField, followupDateField, followupOfField) {
    let note = $(noteField).val();

    if(note !== '') {
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

      let output = '<tr><td>' + note + ' -- <small><em>' + pricingVars.nameOfUser + ' on ' + date + ' ' + time + ' ' + followup + ' </em></small></td></tr>' +
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
  },
  catalogCanEdit: function(editCatalog) {
    if(editCatalog.hasClass("fa-unlock")) {
      return true;
    } else {
      return false;
    }
  },
  disabledSerialize: function(field) {
    let disabled = field.find(":input:disabled").removeAttr("disabled");
    let info = field.serialize();
    disabled.attr('disabled', 'disabled');

    return info;
  },
  disableInput: function() {
    let productionLock = $("#production_lock");

    if(productionLock.attr("data-clicked") !== 'true') {
      $("#crmBatch").find("input, select").prop("disabled", true);
      productionLock.show();
    }
  }
};

var mouseX, mouseY;

$(document).mousemove(function(e) {
  mouseX = e.pageX;
  mouseY = e.pageY;
});

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
    globalFunctions.checkDropdown();
  })

  .on("click", "#save", function() {
    // variable for storing the form data
    let formData = pricingFunction.disabledSerialize($("#batch_info"));
    let val_array = {};
    let inputSelect = $("select");
    let cab_list = JSON.stringify(getMiniTree(cabinetList));

    //<editor-fold desc="Room Data">
    inputSelect.each(function() {
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

    let customVals = JSON.stringify(val_array);

    $.post("/html/pricing/ajax/global_actions.php?action=roomSave&room_id=" + active_room_id, {cabinet_list: cab_list, customVals: customVals, formData: formData}, function(data) {
      $('body').append(data);
    });
    //</editor-fold>

    // live notes for Room notes box
    pricingFunction.checkNote('.room_note_box', '#room_notes', '#room_inquiry_followup_date', '#room_inquiry_requested_of');

    // live notes for SO notes box
    pricingFunction.checkNote('.so_note_box', '#so #inquiry', '#so #inquiry_followup_date', '#so #inquiry_requested_of');

    // based on what we're saving, we're going to take an action
   /* switch(curTab) {
      case 'room-notes-tab': // saving notes
        formData = $("#batch_notes").serialize();

        $.post("/html/pricing/ajax/global_actions.php?action=saveBatchNotes", {room_id: active_room_id, notes: formData}, function(data, response) {
          if(response === 'success') {
            $('body').append(data);

            pricingFunction.checkNote('.so_note_box', '#project_note_input', '#project_followup_date', '#project_followup_requested_of');
            pricingFunction.checkNote('.room_note_box', '#batch_note_input', '#batch_inquiry_followup_date', '#batch_inquiry_requested_of');
          } else {
            displayToast('error', 'Something went wrong with notes. Please try again.', 'Note Error');
          }
        });

        break;
      case 'room-details-tab': // saving room details
        formData = pricingFunction.disabledSerialize($("#batch_details"));

        $.post("/html/pricing/ajax/global_actions.php?action=saveBatchDetails", {room_id: active_room_id, formData: formData}, function(data, response) {
          if(response === 'success') {
            $('body').append(data);
          } else {
            displayToast('error', 'Something went wrong saving Batch Details. Please try again.', 'Batch Details Error');
          }
        });

        break;
      case 'room-cabinet-details-tab':
        val_array = {};

        formData = pricingFunction.disabledSerialize($("#cabinet_details"));

        inputSelect.each(function() {
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

        $.post("/html/pricing/ajax/global_actions.php?action=saveCabinetDetails", {room_id: active_room_id, formData: formData, customVals: customVals}, function(data, response) {
          if(response === 'success') {
            $('body').append(data);
          } else {
            displayToast('error', 'Something went wrong saving Batch Details. Please try again.', 'Batch Details Error');
          }
        });

        break;
      case 'room-item-list-tab':
        cab_list = JSON.stringify(getMiniTree(cabinetList));

        $.post("/html/pricing/ajax/global_actions.php?action=saveItemList", {room_id: active_room_id, cabinet_list: cab_list}, function(data) {
          $('body').append(data);
        });

        break;
      default: // no tabs selected at all, we must be in the old interface, run the old save code
        //<editor-fold desc="Room Data">
        var cabinet_specifications = $("#cabinet_specifications").serialize();
        var accounting_notes = $("#accounting_notes").serialize();
        cab_list = JSON.stringify(getMiniTree(cabinetList));

        inputSelect.each(function() {
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
        pricingFunction.checkNote('.room_note_box', '#room_notes', '#room_inquiry_followup_date', '#room_inquiry_requested_of');

        // live notes for SO notes box
        pricingFunction.checkNote('.so_note_box', '#so #inquiry', '#so #inquiry_followup_date', '#so #inquiry_requested_of');

        break;
    }*/

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

    $("#modalGlobal").modal("hide");

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
      $("#modalGlobal").html(data).modal("show");
    });
  })
  .on("click", "#appliance_ws", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalApplianceWS&roomID=" + active_room_id, function(data) {
      $("#modalGlobal").html(data).modal("show");
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
    pricingFunction.fullRecalc();
  })
  .on("click", "#production_lock", function() {
    $("#crmBatch").find("input, select").prop("disabled", false);
    $(this).attr("data-clicked", "true").hide();

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

    $("#modalGlobal").modal("hide");

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
    pricingFunction.delNoData();

    var root = cabinetList.fancytree("getRootNode");

    var node = root.addChildren({
      qty: 1,
      title: 'NOTE',
      price: 0.00,
      key: pricingFunction.genKey(),
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
    pricingFunction.delNoData();

    var root = cabinetList.fancytree("getRootNode");

    var node = root.addChildren({
      qty: 1,
      title: ' ',
      price: 0.00,
      key: pricingFunction.genKey(),
      icon: 'fa fa-hand-o-right',
      name: 'Error',
      sqft: 0,
      singlePrice: 0.00,
      cabinet: 1,
      customPrice: 1,
      itemID: 1321,
      customNote: 1
    });


    let $tdList = $(node.tr).find(">td");

    $tdList.eq(4).html('<input type="text" class="form-control custom-line-item" placeholder="Custom Description..." data-id="' + node.key + '" >');
    $tdList.eq(9).html('<input type="text" class="form-control custom_price" placeholder="Price" data-id="' + node.key + '" >');
  })

  .on("click", ".delete_item", function() { // removes whatever is checked
    var tree = cabinetList.fancytree("getTree"); // get the tree

    tree.getFocusNode().remove(); // remove the focused node

    // re-render the tree deeply so that we can recalculate the line item numbers
    cabinetList.fancytree("getRootNode").render(true,true);

    pricingFunction.recalcSummary();
  })
  .on("mouseover", ".view_item_info, .info-popup", function(e) {
    // FIXME: Change this so the data isn't loaded on hover, should be able to JSON this data into memory quite easily
    e.stopPropagation();

    let info = "";
    let thisEle = $(this);
    let infoPopup = $(".info-popup");
    let position = thisEle.offset();

    $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: thisEle.data('id'), room_id: active_room_id}, function(data) {
      if(data !== '') {
        let result = JSON.parse(data);

        if(result.image !== null) {
          info += "<div class='image'><img style='max-width:300px;max-height:600px;' src='/html/pricing/images/" + result.image + "' /></div>";
        }

        info += "<div class='right_content'><div class='header'><h4>" + result.title + "</h4></div>";
        info += "<div class='description'>" + result.description + "</div></div>";
      }
    }).done(function() {
      if(info !== '') {
        let infoHeight = infoPopup.height();
        let infoTop = position.top - 85;
        let windowOverflow = $(window).scrollTop() + $(window).height();

        if((infoHeight + infoTop + 100) > windowOverflow) {
          infoPopup.css({"bottom": 0, "top": "inherit", "left": (position.left + 20)});
        } else {
          infoPopup.css({"top": infoTop, "bottom": "inherit", "left": position.left});
        }

        infoPopup.fadeIn(250).html(info);
      }
    });
  })
  .on("mouseleave", ".view_item_info, .info-popup", function() {
    $(".info-popup").fadeOut(250);
  })
  .on("click", ".add_item_mod", function() {
    $.post("/html/modals/item_list_add_modification.php", function(data) {
      $("#modalGlobal").html(data).modal('show');
    });
  })
  .on("click", ".item_copy", function() {
    let activeNode = cabinetList.fancytree("getTree").getActiveNode().toDict(true);

    activeNode.key = pricingFunction.genKey();

    if(activeNode.children !== null) {
      $.each(activeNode.children, function(key) {
        activeNode.children[key].key = pricingFunction.genKey();
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
    pricingFunction.recalcSummary();
  })

  .on("click", ".add_item_cabinet_list", function() {
    pricingFunction.delNoData();

    var root = cabinetList.fancytree("getRootNode");

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
        key: pricingFunction.genKey(),
        icon: itemInfo.icon,
        name: itemInfo.title,
        sqft: itemInfo.sqft,
        singlePrice: fixedPrice,
        cabinet: itemInfo.cabinet,
        addlMarkup: itemInfo.addlMarkup
      });

      pricingFunction.recalcSummary();
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
      $("#modalGlobal").html(data).modal("show");
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

.on("change",".item_hinge", function() {
  let node = cabinetList.fancytree("getActiveNode");

  node.data.hinge = $(this).find(":selected").val();
})
.on("keyup", ".itm_width", function() {
  let id = $(this).attr("data-id");
  let node = cabinetList.fancytree("getTree").getNodeByKey(id);

  node.data.width = $(this).val();
  node.data.price = pricingFunction.footCalc(node);

  pricingFunction.recalcSummary();
})
.on("keyup", ".itm_height", function() {
  let id = $(this).attr("data-id");
  let node = cabinetList.fancytree("getTree").getNodeByKey(id);

  node.data.height = $(this).val();
  node.data.price = pricingFunction.footCalc(node);

  pricingFunction.recalcSummary();
})
.on("keyup", ".itm_depth", function() {
  let id = $(this).attr("data-id");
  let node = cabinetList.fancytree("getTree").getNodeByKey(id);

  node.data.depth = $(this).val();
  node.data.price = pricingFunction.footCalc(node);

  pricingFunction.recalcSummary();
})
  .on("keyup", "#modificationsFilter", function() { // filters per keystroke on search catalog
    // grab this value and filter it down to the node needed
    itemModifications.fancytree("getTree").filterNodes($(this).val());

    // TODO: Enable filter dropdown allowing keywords - expected result, type microwave and get nomenclature available under microwave
    // TODO: https://github.com/mar10/fancytree/issues/551
  })
  .on("click", "#modificationAddSelected", function() {
    let modifications = $("#item_modifications").fancytree("getTree").getSelectedNodes();
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
        key: pricingFunction.genKey(),
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

    $("#modalGlobal").modal("hide");
  })
  .on("keyup", ".modAddlInfo", function() {
    let id = $(this).attr("id");

    $("#item_modifications").fancytree("getTree").getNodeByKey(id).data.addlInfo = $(this).val();
  })

  .on("click", "#category_collapse", function() {
    catalog.fancytree("getTree").visit(function(node){
      node.setExpanded(false);
    });
  })

  .on("click", "#overrideShipDate", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalOverrideShipping&roomID=" + active_room_id, function(data) {
      $("#modalGlobal").html(data).modal("show");
    });
  })
  .on("click", "#modalShippingUpdate", function() {
    let shippingInfo = $("#modalShippingOverrideData").serialize();

    $.post("/html/pricing/ajax/global_actions.php?action=overrideShipping&roomID=" + active_room_id, {info: shippingInfo}, function(data) {
      let ship_results = JSON.parse(data);

      $("#calcd_ship_date").text(ship_results.ship_date);
      $("#calcd_del_date").text(ship_results.del_date);

      displayToast("success", "Successfully updated ship and delivery date.", "Ship/Delivery Updated");

      $("#modalGlobal").modal('hide');
    });

    unsaved = false;
  })

  .on("blur", ".custom_price", function() {
    let id = $(this).attr("data-id");
    let node = cabinetList.fancytree("getTree").getNodeByKey(id);

    node.data.price = parseFloat($(this).val().replace(/[^0-9-.]/g, ''));

    pricingFunction.recalcSummary();
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
    $.post("/html/modals/detailed_report_item_list.php", {room_id: active_room_id}, function(data) {
      $("#modalGlobal").html(data);
    }).done(function() {
      pricingFunction.recalcSummary();

      $("#modalGlobal").modal("show");
    });
  })

  .on("click", "#pricingSpeciesGrade, #pricingDoorDesign", function() {
    let speciesGrade = $("#species_grade").val();
    let doorDesign = $("#door_design").val();

    $.post("/html/pricing/ajax/global_actions.php?action=getPriceGroup&speciesGrade=" + speciesGrade + "&doorDesign=" + doorDesign, function(data) {
      $("#cab_spec_pg").text(data);
      priceGroup = data;
    });

    pricingFunction.recalcSummary();
  })

  .on("keyup", "input[name='pg4']", function() {
    let markup_calc = 0.0684;
    let pg4 = parseFloat($(this).val());

    let pg3 = Math.round(pg4 - (pg4 * markup_calc));
    let pg2 = Math.round(pg3 - (pg3 * markup_calc));
    let pg1 = Math.round(pg2 - (pg2 * markup_calc));

    let pg5 = Math.round(pg4 * (1 + markup_calc));
    let pg6 = Math.round(pg5 * (1 + markup_calc));
    let pg7 = Math.round(pg6 * (1 + markup_calc));
    let pg8 = Math.round(pg7 * (1 + markup_calc));
    let pg9 = Math.round(pg8 * (1 + markup_calc));
    let pg10 = Math.round(pg9 * (1 + markup_calc));
    let pg11 = Math.round(pg10 * (1 + markup_calc));
    let pg12 = Math.round(pg11 * (1 + markup_calc));
    let pg13 = Math.round(pg12 * (1 + markup_calc));
    let pg14 = Math.round(pg13 * (1 + markup_calc));

    $("#pg1").val(pg1);
    $("#pg2").val(pg2);
    $("#pg3").val(pg3);
    $("#pg5").val(pg5);
    $("#pg6").val(pg6);
    $("#pg7").val(pg7);
    $("#pg8").val(pg8);
    $("#pg9").val(pg9);
    $("#pg10").val(pg10);
    $("#pg11").val(pg11);
    $("#pg12").val(pg12);
    $("#pg13").val(pg13);
    $("#pg14").val(pg14);
  })
  .on("click", "#modalSaveCatItemSubmit", function() {
    let node = catalog.fancytree("getTree").getActiveNode();
    let formInfo = document.querySelector("#catalogAddEditItem");

    let disabled = $("#catalogAddEditItem").find(":input:disabled").removeAttr("disabled");
    let fieldData = new FormData(formInfo);
    disabled.attr('disabled', 'disabled');

    let name = $("#catalogAddEditItem input[name='name']").val();
    let sku = $("#catalogAddEditItem input[name='sku']").val();
    let title = null;

    fieldData.append("key", node.key);
    // noinspection JSCheckFunctionSignatures
    fieldData.append("folder", node.folder);

    $.ajax({
      url: "/html/pricing/ajax/item_actions.php?action=updateItem",
      data: fieldData,
      cache: false,
      contentType: false,
      processData: false,
      type: 'POST',
      success: function(data) {
        $("body").append(data);

        if(node.folder) {
          title = name;
        } else {
          title = sku +
          ' <span class="actions">' +
          '<div class="info_container"><i class="fa fa-info-circle primary-color view_item_info" data-id="' + node.key + '"></i></div>&nbsp;' +
          '<i class="fa fa-plus-circle success-color add_item_cabinet_list" data-id="' + node.key + '" title="Add To Cabinet List"></i>' +
          '</span>';
        }

        node.setTitle(title);
        $("#modalGlobal").html(data).modal("hide");
      },
      fail: function(xhr) {
        $("body").append(xhr.responseText);
      }
    });

    unsaved = false;
  })
  .on("click", "#modalAddCatItemSubmit", function() {
    let node = catalog.fancytree("getTree").getActiveNode(); // current node (where are we adding this?)
    // let fields = $("#catalogAddEditItem").serialize(); // get all of the form information
    let formInfo = document.querySelector("#catalogAddEditItem");

    let disabled = $("#catalogAddEditItem").find(":input:disabled").removeAttr("disabled");
    let fieldData = new FormData(formInfo);
    disabled.attr('disabled', 'disabled');

    let catID = node.key; // current key is the category ID unless we modify it later
    let createFolder = false; // are we creating a folder?
    let folderType = null; // what type of folder is it, main? sub?

    let sku = $("#catalogAddEditItem input[name='sku']").val(); // get the SKU so that we can auto-display it in the tree
    let name = $("#catalogAddEditItem input[name='name']").val(); // get the SKU so that we can auto-display it in the tree

    if($(this).attr("data-type") === 'newSameFolder') { // if we're creating a new folder on the same level
      createFolder = true; // creating a folder
      folderType = 'alongside'; // alongside the current selection
    } else if($(this).attr('data-type') === 'newSubFolder') { // otherwise, if we're creating a sub-folder
      createFolder = true; // creating a folder
      folderType = 'child'; // under the current selection
    }

    if(!node.isFolder()) { // if the current selection is an item
      catID = node.parent.key; // get the current parent for category ID
    }

    fieldData.append("key", catID);
    // noinspection JSCheckFunctionSignatures
    fieldData.append("folder", createFolder);
    fieldData.append("folderType", folderType);

    $.ajax({
      url: "/html/pricing/ajax/item_actions.php?action=createItem",
      data: fieldData,
      cache: false,
      contentType: false,
      processData: false,
      type: 'POST',
      success: function(data) {
        let insertID = data; // current ID that was created (for clicking the + sign on the catalog)

        if(createFolder) { // if we're creating a folder
          if(folderType === 'alongside') { // check to see if it's a main or sub category
            // noinspection JSCheckFunctionSignatures
            catalog.fancytree("getTree").getNodeByKey(catID).parent.addChildren({'key': insertID, 'title': name, 'folder': true}); // add category alongside
          } else {
            // noinspection JSCheckFunctionSignatures
            catalog.fancytree("getTree").getNodeByKey(catID).addChildren({'key': insertID, 'title': name, 'folder': true}); // add category under parent
          }

          displayToast('success', 'Successfully created category', 'Category Created'); // notify user we've created the category

          $("#modalGlobal").html(data).modal("hide"); // poof, it's gone
        } else {
          // create the title structure for the new item
          let title = sku +
            '<span class="actions">' +
            '<div class="info_container"><i class="fa fa-info-circle primary-color view_item_info" data-id="' + insertID + '"></i></div>' +
            '<i class="fa fa-plus-circle success-color add_item_cabinet_list" data-id="' + insertID + '" title="Add To Cabinet List"></i>' +
            '</span>';

          // add the item to the tree
          // noinspection JSCheckFunctionSignatures
          catalog.fancytree("getTree").getNodeByKey(catID).addChildren({'key': insertID, 'icon': 'fa fa-magic', 'title': title, 'is_item': true, 'qty': 1});

          displayToast('success', 'Successfully created item.', 'Item Created'); // let the user know it was successfully created

          $("#modalGlobal").html(data).modal("hide"); // poof, gone!
        }
      },
      fail: function(xhr) {
        $("body").append(xhr.responseText);
      }
    });

    unsaved = false;
  })

  .on("change", "#catalogAddEditItem input[name='image_type']:checked", function() {
    let container = $("#catalogAddEditItem");
    let display = $(".displayImage");

    switch($(this).val()) {
      case 'current':
        display.hide();
        container.find('#displayCurrentImage').show();
        break;
      case 'recent':
        display.hide();
        container.find('#displayRecentImage').show();
        break;
      case 'new':
        display.hide();
        container.find('#displayNewImageUpload').show();
        break;
      case 'existing':
        display.hide();
        container.find('#displayImageLibrary').show();
        break;
    }
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
      pricingFunction.updateShipDate();
    } else {
      displayToast('error', 'Unable to recalculate ship date, this room is not in quote anymore.', 'Unable to Recalculate.');
    }
  })
  .on("click", "#overrideShipCost", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalOverrideShipCost&roomID=" + active_room_id, function(data) {
      $("#modalGlobal").html(data).modal("show");
    });
  })
  .on("click", "#modalShippingCostOverride", function() {
    let shippingInfo = $("#modalShippingOverrideCost").serialize();

    $.post("/html/pricing/ajax/global_actions.php?action=shipCostOverride&roomID=" + active_room_id, {info: shippingInfo}, function(data, response) {
      if(response === 'success') {
        $("#shipping_cost").attr('data-cost', data).text('$' + data);

        displayToast("success", "Successfully overrode ship cost.", "Ship Cost Overriden");

        $("#modalGlobal").modal('hide');

        pricingFunction.recalcSummary();
      } else {
        displayToast("error", "Unable to update ship cost. Please contact IT.", "Ship Cost Override Error");
      }
    });

    unsaved = false;
  })

  .on("change", "#jobsite_delivery", function() {
    let jobnode = cabinetList.fancytree("getTree").getNodeByKey('jobsitedelivery');

    if(jobnode !== null) {
      jobnode.remove();
    }

    if($(this).is(":checked")) {
      cabinetList.fancytree("getRootNode").addChildren({
        qty: 1,
        title: 'GLOBALNOTE',
        price: 0.00,
        key: 'jobsitedelivery',
        icon: 'fa fa-truck',
        name: 'Jobsite Delivery',
        sqft: 0,
        singlePrice: 0.00,
        cabinet: 0,
        customNote: 0
      });

      $("#itemListJobsiteDelivery").parent().show();
    } else {
      $("#itemListJobsiteDelivery").parent().hide();
    }
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
              pricingFunction.updateShipDate();
            },
            no: function() {}
          }
        });
      }
    }
  })*/
  /*.on("click", "#bracket_management", function() {
  $.post("/html/pricing/ajax/global_actions.php?action=modalBracketMgmt&roomID=" + active_room_id, function(data) {
    $("#modalGlobal").html(data).modal("show");
  });
})*/
;