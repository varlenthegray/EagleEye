jQuery.expr.filters.offscreen = function(el) {
  var rect = el.getBoundingClientRect();
  return (
    (rect.x + rect.width) < 0
    || (rect.y + rect.height) < 0
    || (rect.x > window.innerWidth || rect.y > window.innerHeight)
  );
};

var total = 0; // define initial total

function delNoData() {
  let getNegNode = cabinetList.fancytree("getTree").getNodeByKey('-1');

  if(getNegNode !== null) {
    cabinetList.fancytree("getTree").getNodeByKey('-1').remove();
  }
}

function recalcTotal() {
  let totalTree = cabinetList.fancytree("getTree");
  let newTotal = 0.00;

  totalTree.visit(function(line) {
    let qty = parseInt(line.data.qty);
    let price = parseFloat(line.data.price);
    let lineTotal = qty * price;

    newTotal += parseFloat(lineTotal);

    let node = cabinetList.fancytree("getTree").getNodeByKey(line.key), $tdList = $(node.tr).find(">td");

    console.log("Updated " + node.key + " with total: " + newTotal);

    // update the line item quantity?

    // update the total column with the correct total
    node.data.total = newTotal.formatMoney();
    $tdList.eq(11).text(newTotal.formatMoney());
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

    recalcTotal();
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
    let id = $(this).attr("id");

    cabinetList.fancytree("getTree").getNodeByKey(id).data.qty = $(this).val();
  })
  .on("change", ".qty_input", function() {
    recalcTotal();
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
        name: itemInfo.title
      });

      recalcTotal();
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
  .on("focus", ".qty_input", function() { // when clicking or tabbing to quantity
    $(this).select(); // auto-select the text
  })
  .on("keyup", ".qty_input", function() {
    let id = $(this).attr("id");

    cabinetList.fancytree("getTree").getNodeByKey(id).data.qty = $(this).val();
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
      let addlInfo = null;

      if(v.data.addlInfo !== undefined) {
        addlInfo = " by " + v.data.addlInfo;
      }

      cablist.addChildren({
        title: v.title,
        name: v.data.description + addlInfo,
        qty: 1,
        icon: v.icon,
        price: v.data.price
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
;