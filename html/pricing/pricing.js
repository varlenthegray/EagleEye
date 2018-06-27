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
  .on("click", "#cabinet_list_save", function() {
    // var cab_list = JSON.stringify(cabinetList.fancytree("getTree").toDict(true));
    var cab_list = JSON.stringify(getMiniTree(cabinetList));
    var cat_id = $("#catalog").find(":selected").attr("id");

    $.post("/html/pricing/ajax/item_actions.php?action=saveCatalog&room_id=" + roomID, {cabinet_list: cab_list, catalog_id: cat_id}, function(data) {
      $("body").append(data);
    });

    var thisClick = this;
    var val_array = {};
    var edit_info = $("#pricing_global_attributes").serialize();

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

    var customVals = JSON.stringify(val_array);

    $.post("/ondemand/room_actions.php?action=update_room", {customVals: customVals, editInfo: edit_info, roomid: roomID}, function(data) {
      $('body').append(data);
    }).done(function() {
      $(thisClick).addClass('edit_room_save');
      $("#room_notes").val('');
    });

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

    $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: $(this).attr('data-id'), room_id: roomID}, function(data) {
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

    $.post("/html/pricing/ajax/item_actions.php?action=saveCatalog&room_id=" + roomID, {cabinet_list: cab_list, catalog_id: cat_id}, function(data) {
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

    $.post("/html/pricing/ajax/item_actions.php?action=getItemInfo", {id: thisEle.data('id'), room_id: roomID}, function(data) {
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

    cabinetList.fancytree("getTree").getActiveNode().addChildren(modifications);

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
          $.post("/html/pricing/ajax/item_actions.php?action=submitQuote&room_id=" + roomID, {cabinet_list: cab_list}, function(data) {
            $("body").append(data);
          }).done(function() {
            button.val("Submitted").prop("disabled", true);
          });
        },
        no: function() {} // we're not doing anything
      }
    });
  })
  .on("click", "#save_globals", function() {
    console.log("Saving globals.");

    let thisClick = this;
    let val_array = {};

    $(thisClick).removeClass('edit_room_save');

    let edit_info = $("#pricing_global_attributes").serialize();

    $("input[type='hidden']").each(function() {
      let ele = $(this);
      let field = $(this).attr('id');
      let custom_fields = ['X', 'Xxx', 'AX', 'DX', 'TX', 'Xx', 'WX', '1cXXXX', '3gXXXX'];

      // console.log(field);

      if($.inArray(ele.val(), custom_fields) >= 0) {
        val_array[field] = {};

        ele.parent().find('.selected').find('input').each(function() {
          val_array[field][$(this).attr('name')] = $(this).val();
        });
      }
    });

    let customVals = JSON.stringify(val_array); // this won't have a value if we don't have custom values

    console.log(edit_info);

    /*$.post("/ondemand/room_actions.php?action=update_room", {customVals: customVals, editInfo: edit_info}, function(data) {
      $('body').append(data);
    }).done(function() {
      $(thisClick).addClass('edit_room_save');
      $("#room_notes").val('');
    });*/

    unsaved = false;
  })
  .on("click", "#category_collapse", function() {
    catalog.fancytree("getTree").visit(function(node){
      node.setExpanded(false);
    });
  })
  .on("click", "#global_info", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalGlobals&roomID=" + roomID, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#modalGlobalsUpdate", function() {
    let globalInfo = $("#modalGlobalData").serialize();

    $.post("/html/pricing/ajax/global_actions.php?action=updateGlobals&roomID=" + roomID, {globalInfo: globalInfo}, function(data) {
      $("body").append(data);
      $("#modalGeneral").html("").modal('hide');
    });
  })
  .on("click", "#appliance_ws", function() {
    $.post("/html/pricing/ajax/global_actions.php?action=modalApplianceWS&roomID=" + roomID, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
  .on("click", "#modalAppWSSave", function() {
    var formInfo = $("#appliance_info").serialize();

    $.post("/ondemand/room_actions.php?action=save_app_worksheet&room=" + roomID + "&" + formInfo, function(data) {
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
    $.post("/html/pricing/ajax/global_actions.php?action=modalBracketMgmt&roomID=" + roomID, function(data) {
      $("#modalGeneral").html(data).modal("show");
    });
  })
;

$("#modalAddModification").on("show.bs.modal", function() {
  $("#modificationsFilter").val('');
  itemModifications.fancytree("getTree").clearFilter();

  let modificationTree = {
    url: "/html/pricing/ajax/modifications.php?itemID=" + cabinetList.fancytree("getTree").getActiveNode().data.itemID,
    type: "POST",
    dataType: 'json'
  };

  itemModifications.fancytree("getTree").reload(modificationTree);
});

$(function() {
  /******************************************************************************
   *  Cabinet List
   ******************************************************************************/
  cabinetList.fancytree({
    select: function(event, data) { // TODO: Determine if this is valuable
      // Display list of selected nodes
      var selNodes = data.tree.getSelectedNodes();
      // convert to title/key array
      var selKeys = $.map(selNodes, function(node){
        return "[" + node.key + "]: '" + node.title + "'";
      });

      // console.log(selKeys.join(", "));

      if(selKeys.length > 0) {
        $("#catalog_remove_checked").show();
      } else {
        $("#catalog_remove_checked").hide();
      }
    },
    imagePath: "/assets/images/cabinet_icons/",
    cookieId: "fancytree-cabList",
    idPrefix: "fancytree-cabList-",
    checkbox: true,
    titlesTabbable: true,     // Add all node titles to TAB chain
    quicksearch: true,        // Jump to nodes when pressing first character
    source: { url: "/html/pricing/ajax/item_actions.php?action=getCabinetList&room_id=" + roomID },
    extensions: ["edit", "dnd", "table", "gridnav", "persist"],
    debugLevel: 0,
    dnd: { // drag and drop
      preventVoidMoves: true,
      preventRecursiveMoves: true,
      autoExpandMS: 400,
      dragStart: function(node, data) {
        return true;
      },
      dragEnter: function(node, data) {
        // return ["before", "after"];
        return true;
      },
      dragDrop: function(node, data) {
        data.otherNode.moveTo(node, data.hitMode);
      }
    },
    edit: {
      triggerStart: ["clickActive", "f2", "shift+click", "mac+enter"],
      close: function(event, data) {
        if( data.save && data.isNew ){
          // Quick-enter: add new nodes until we hit [enter] on an empty title
          cabinetList.trigger("nodeCommand", {cmd: "addSibling"});
        }
      }
    },
    table: {
      indentation: 20,
      nodeColumnIdx: 3,
      checkboxColumnIdx: 0
    },
    gridnav: {
      autofocusInput: false,
      handleCursorKeys: true
    },
    renderColumns: function(event, data) {
      // this section handles the column data itself
      var node = data.node, $tdList = $(node.tr).find(">td");

      // lets begin by getting the quantity and the total and multiplying them
      let qty = parseInt(node.data.qty);
      let price = parseFloat(node.data.price);
      let line_total = qty * price;

      // (Index #0 is rendered by fancytree by adding the checkbox)
      // Set column #1 info from node data:
      $tdList.eq(1).text(node.getIndexHier());
      // (Index #2 is the quantity input field)
      $tdList.eq(2).find("input").attr("id", node.key).val(node.data.qty);
      // (Index #3 is rendered by fancytree in child table under nodeColumnIdx)
      // (Index #4 is the width)

      $tdList.eq(4).text(node.data.name);

      $tdList.eq(5).text(node.data.width);
      // (Index #5 is the height)
      $tdList.eq(6).text(node.data.height);
      // (Index #6 is the depth)
      $tdList.eq(7).text(node.data.depth);
      // (Index #7 is price, calculated below)

      if(node.data.hinge !== undefined) {
        $tdList.eq(8).find(".item_hinge").val(node.data.hinge);
      }

      if(node.data.finish !== undefined) {
        $tdList.eq(9).find(".item_finish").val(node.data.finish);
      }

      if(!isNaN(price)) {
        // (Index #7)
        $tdList.eq(10).text(price.formatMoney()).removeAttr("style title"); // price column

        $(".no_global_info").css("display", "none");
        
        if(!already_submitted) {
          $("#submit_for_quote").attr("disabled", false).attr("title", "");
        }
      } else {
        $tdList.eq(10).css("background-color", "#FF0000").attr("title", "Unknown global attributes, unable to find price.");
        $tdList.eq(11).css("background-color", "#FF0000").attr("title", "Unknown global attributes, unable to properly calculate total.");

        $("#submit_for_quote").attr("disabled", true).attr("title", "Unknown global attributes, unable to submit.");

        $(".no_global_info").css("display", "block");
      }

      // (Index #8)
      $tdList.eq(11).text(node.data.total);
    },
    modifyChild: function(event, data) {
      recalcTotal();
    }
  }).on("nodeCommand", function(event, data){
    // Custom event handler that is triggered by keydown-handler and
    // context menu:
    var refNode, moveMode,
      tree = $(this).fancytree("getTree"),
      node = tree.getActiveNode();

    switch( data.cmd ) {
      case "moveUp":
        refNode = node.getPrevSibling();
        if( refNode ) {
          node.moveTo(refNode, "before");
          node.setActive();
        }
        break;
      case "moveDown":
        refNode = node.getNextSibling();
        if( refNode ) {
          node.moveTo(refNode, "after");
          node.setActive();
        }
        break;
      case "indent":
        refNode = node.getPrevSibling();
        if( refNode ) {
          node.moveTo(refNode, "child");
          refNode.setExpanded();
          node.setActive();
        }
        break;
      case "outdent":
        if( !node.isTopLevel() ) {
          node.moveTo(node.getParent(), "after");
          node.setActive();
        }
        break;
      case "rename":
        node.editStart();
        break;
      case "remove":
        refNode = node.getNextSibling() || node.getPrevSibling() || node.getParent();
        node.remove();
        if( refNode ) {
          refNode.setActive();
        }
        break;
      case "addModifications":
        $("#modalAddModification").modal('show');
        break;
      case "cut":
        CLIPBOARD = {mode: data.cmd, data: node};
        break;
      case "copy":
        CLIPBOARD = {
          mode: data.cmd,
          data: node.toDict(function(n){
            delete n.key;
          })
        };
        break;
      case "clear":
        CLIPBOARD = null;
        break;
      case "paste":
        if( CLIPBOARD.mode === "cut" ) {
          // refNode = node.getPrevSibling();
          CLIPBOARD.data.moveTo(node, "child");
          CLIPBOARD.data.setActive();
        } else if( CLIPBOARD.mode === "copy" ) {
          node.addChildren(CLIPBOARD.data).setActive();
        }
        break;
      default:
        alert("Unhandled command: " + data.cmd);
        return;
    }
  }).on("keydown", function(e){
    var cmd = null;

    // console.log(e.type, $.ui.fancytree.eventToString(e));
    switch( $.ui.fancytree.eventToString(e) ) {
      case "ctrl+shift+n":
      case "meta+shift+n": // mac: cmd+shift+n
        cmd = "addChild";
        break;
      case "ctrl+c":
      case "meta+c": // mac
        cmd = "copy";
        break;
      case "ctrl+v":
      case "meta+v": // mac
        cmd = "paste";
        break;
      case "ctrl+x":
      case "meta+x": // mac
        cmd = "cut";
        break;
      case "ctrl+n":
      case "meta+n": // mac
        cmd = "addSibling";
        break;
      case "del":
      case "meta+backspace": // mac
        cmd = "remove";
        break;
      // case "f2":  // already triggered by ext-edit pluging
      //   cmd = "rename";
      //   break;
      case "ctrl+up":
        cmd = "moveUp";
        break;
      case "ctrl+down":
        cmd = "moveDown";
        break;
      case "ctrl+right":
      case "ctrl+shift+right": // mac
        cmd = "indent";
        break;
      case "ctrl+left":
      case "ctrl+shift+left": // mac
        cmd = "outdent";
    }
    if( cmd ){
      $(this).trigger("nodeCommand", {cmd: cmd});
      return false;
    }
  });

  // Context menu (https://github.com/mar10/jquery-ui-contextmenu)
  cabinetList.contextmenu({
    delegate: "span.fancytree-node",
    menu: [
      {title: "Delete <kbd>[Del]</kbd>", cmd: "remove", uiIcon: "ui-icon-trash" },
      {title: "----"},
      {title: "Add Modifications <kbd>[Ctrl+M]</kbd>", cmd: "addModifications", uiIcon: "ui-icon-plus" },
      {title: "----"},
      {title: "Cut <kbd>Ctrl+X</kbd>", cmd: "cut", uiIcon: "ui-icon-scissors"},
      {title: "Copy <kbd>Ctrl-C</kbd>", cmd: "copy", uiIcon: "ui-icon-copy"},
      {title: "Paste<kbd>Ctrl+V</kbd>", cmd: "paste", uiIcon: "ui-icon-clipboard", disabled: true }
    ],
    beforeOpen: function(event, ui) {
      var node = $.ui.fancytree.getNode(ui.target);
      cabinetList.contextmenu("enableEntry", "paste", !!CLIPBOARD);
      node.setActive();
    },
    select: function(event, ui) {
      var that = this;
      // delay the event, so the menu can close and the click event does
      // not interfere with the edit control
      setTimeout(function(){
        $(that).trigger("nodeCommand", {cmd: ui.cmd});
      }, 100);
    }
  });

  /******************************************************************************
   *  Navigation menu
   ******************************************************************************/

  // this is the navigation menu on the left side
  catalog.fancytree({
    source: { url: "/html/pricing/ajax/nav_menu.php" },
    extensions: ["filter"],
    debugLevel: 0,
    filter: {
      autoApply: true,   // Re-apply last filter if lazy data is loaded
      autoExpand: true, // Expand all branches that contain matches while filtered
      counter: true,     // Show a badge with number of matching child nodes near parent icons
      fuzzy: true,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
      hideExpandedCounter: true,  // Hide counter badge if parent is expanded
      hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
      highlight: true,   // Highlight matches by wrapping inside <mark> tags
      leavesOnly: false, // Match end nodes only
      nodata: false,      // Display a 'no data' status node if result is empty
      mode: "hide"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
    }
  });

  // this is the modifications modal popup
  itemModifications.fancytree({
    source: { url: "/html/pricing/ajax/modifications.php" },
    extensions: ["filter"],
    debugLevel: 0,
    filter: {
      autoApply: true,   // Re-apply last filter if lazy data is loaded
      autoExpand: true, // Expand all branches that contain matches while filtered
      counter: true,     // Show a badge with number of matching child nodes near parent icons
      fuzzy: true,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
      hideExpandedCounter: true,  // Hide counter badge if parent is expanded
      hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
      highlight: true,   // Highlight matches by wrapping inside <mark> tags
      leavesOnly: false, // Match end nodes only
      nodata: true,      // Display a 'no data' status node if result is empty
      mode: "hide"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
    }
  });

  if($("#ext_carcass_same").is(":checked")) {
    $(".ext_finish_block").hide();
  } else {
    $(".ext_finish_block").show();
  }

  if($("#int_carcass_same").is(":checked")) {
    $(".int_finish_block").hide();
  } else {
    $(".int_finish_block").show();
  }

  if($("#submit_for_quote").prop("disabled")) {
    $.confirm({
      title: "Item List Submitted.",
      content: "You are unable to save this form. It has already been submitted. Please check with your representative if you require any modifications.",
      type: 'red',
      buttons: {
        ok: function() {}
      }
    });
  }
});