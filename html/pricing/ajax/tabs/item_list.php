<?php
require '../../../../includes/header_start.php';

$room_id = sanitizeInput($_REQUEST['room_id']);

$vin_schema = getVINSchema();

$room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id ORDER BY room, iteration ASC;");
$room = $room_qry->fetch_assoc();

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '{$room['so_parent']}'");
$so = $so_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$so['dealer_code']}'");
$dealer = $dealer_qry->fetch_assoc();

//<editor-fold desc="Determining the price group (for JavaScript)">
$pg_qry = $dbconn->query("SELECT vs1.id AS species_grade_id, vs2.id AS door_design_id FROM rooms r
  LEFT JOIN vin_schema vs1 ON r.species_grade = vs1.key
  LEFT JOIN vin_schema vs2 ON r.door_design = vs2.key
WHERE r.id = $room_id AND vs1.segment = 'species_grade' AND vs2.segment = 'door_design'");

if($pg_qry->num_rows > 0) {
  $pg = $pg_qry->fetch_assoc();

  if ($pg['door_design_id'] !== '1544' && $pg['species_grade_id'] !== '11') {
    $price_group_qry = $dbconn->query("SELECT * FROM pricing_price_group_map WHERE door_style_id = {$pg['door_design_id']} AND species_id = {$pg['species_grade_id']}");
    $price_group = $price_group_qry->fetch_assoc();
    $price_group = $price_group['price_group_id'];
  } else {
    $price_group = '0';
  }
} else {
  $price_group = '0';
}
//</editor-fold>
?>

<div class="container-fluid pricing_table_format">
  <div class="row">
    <div class="col-md-2 pricing_left_nav no-print sticky">
      <div class="sticky nav_filter">
        <table width="100%" style="margin-bottom:10px;">
          <tr>
            <td><label for="left_menu_options">Library:</label></td>
            <td>
              <select id="left_menu_options" class="c_input ignoreSaveAlert">
                <option value="catalog">Catalog</option>
                <option value="samples">Samples</option>
              </select>
            </td>
          </tr>
          <tr>
            <td><label for="treeFilter">Search:</label></td>
            <td><input type="text" class="form-control fc-simple ignoreSaveAlert border_thin_bottom" id="treeFilter" placeholder="Search..." width="100%" ></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td><span class="pull-left cursor-hand"><i id="category_collapse" class="fa fa-fw fa-level-up" title="Collapse all categories"></i> Collapse</span></td>
            <td><?php echo $bouncer->validate('pricing_change_catalog') ? '<span class="pull-right"><i id="editCatalogLock" class="fa fa-fw fa-lock cursor-hand" title="Lock/Unlock Catalog for Changes"></i></span>' : null ?></td>
          </tr>
        </table>
      </div>

      <div id="action_container"></div>
    </div>

    <!--<editor-fold desc="Item List">-->
    <div class="col-md-10 itemListWrapper" style="margin-top:5px;">
      <div class="item_list_header sticky">
        <h5><u>Item List</u></h5>

        <input type="button" class="btn btn-danger waves-effect waves-light no-print" style="display:none;" id="catalog_remove_checked" value="Delete" />
        <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="item_note"><span class="btn-label"><i class="fa fa-commenting-o"></i> </span>Custom Note</button>
        <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="item_custom_line"><span class="btn-label"><i class="fa fa-plus"></i> </span>Custom Item</button>
        <button type="button" class="btn btn-secondary waves-effect waves-light no-print" id="detailed_item_summary"><span class="btn-label"><i class="fa fa-list"></i> </span>Detailed Report</button>

        <div class="clearfix"></div>
      </div>

      <table class="sticky" style="width:100%;top:82px;">
        <colgroup>
          <col width="40px">
          <col width="35px">
          <col width="50px">
          <col width="150px">
          <col width="350px">
          <col width="50px">
          <col width="50px">
          <col width="50px">
          <col width="50px">
          <col width="50px">
        </colgroup>
        <thead>
        <tr>
          <th>#</th>
          <th class="text-md-center">Qty</th>
          <th class="text-md-center">Actions</th>
          <th>Nomenclature</th>
          <th>Description</th>
          <th class="text-md-center">Width</th>
          <th class="text-md-center">Height</th>
          <th class="text-md-center">Depth</th>
          <th class="text-md-center">Hinge</th>
          <th class="text-md-center pricing_value">Price</th>
        </tr>
        </thead>
      </table>

      <table id="cabinet_list" style="width:100%;">
        <colgroup>
          <col width="40px">
          <col width="40px">
          <col width="50px">
          <col width="150px">
          <col width="350px">
          <col width="50px">
          <col width="50px">
          <col width="50px">
          <col width="50px">
          <col width="50px">
        </colgroup>

        <tbody>
        <!-- Define a row template for all invariant markup: -->
        <tr>
          <td></td>
          <td><input type="text" class="form-control qty_input" value="1" placeholder="Qty" /> </td>
          <td class="text-md-center">
            <i class="fa fa-info-circle primary-color view_item_info cursor-hand" data-id=""></i>
            <i class="fa fa-minus-circle danger-color delete_item cursor-hand" title="Delete line"></i>
            <i class="fa fa-plus-circle secondary-color add_item_mod cursor-hand" title="Add modification"></i>
            <i class="fa fa-copy secondary-color item_copy cursor-hand" title="Copy line"></i>
          </td>
          <td style="white-space:nowrap;"></td>
          <td></td>
          <td class="text-md-center"><input type="text" class="form-control itm_width text-md-center" placeholder="W" /></td>
          <td class="text-md-center"><input type="text" class="form-control itm_height text-md-center" placeholder="H" /></td>
          <td class="text-md-center"><input type="text" class="form-control itm_depth text-md-center" placeholder="D" /></td>
          <td class="text-md-center">
            <select class="item_hinge custom-select">
              <option value="L">Left</option>
              <option value="R">Right</option>
              <option value="P">Pair</option>
              <option value="N" selected>None</option>
            </select>
          </td>
          <td class="text-md-right cab-price pricing_value"></td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
          <td colspan="12">
            <div class="row no_global_info" style="display:none;"><i class="fa fa-exclamation-triangle" style="font-size:2em;"></i>Unable to price with the current information.<br />Any price displayed is not a reflection of the final price until this quote has been processed by SMCM.<i class="fa fa-exclamation-triangle pull-right" style="font-size:2em;"></i></div>
          </td>
        </tr>
        </tfoot>
      </table>
    </div>
    <!--</editor-fold>-->

    <!--<editor-fold desc="Summary of Charges">-->
    <div class="row pricing_value">
      <div class="col-sm-3 col-sm-offset-9 summary_of_charges">
        <div class="left_header"><h5>Summary of Charges:</h5></div>

        <table align="right" width="100%">
          <tr class="border_thin_bottom">
            <td width="65%" class="total_text">Item List:</td>
            <td width="80px">&nbsp;</td>
            <td class="text-md-right gray_bg total_text" id="itemListTotal">$0.00</td>
          </tr>
          <tr class="border_thin_bottom">
            <td class="total_text">Global: Cabinet Details:</td>
            <td class="total_text">&nbsp;</td>
            <td class="text-md-right total_text" id="itemListGlobalCabDetails">$0.00</td>
          </tr>
          <tr class="border_thin_bottom">
            <td class="total_text">Total:</td>
            <td class="total_text">&nbsp;</td>
            <td class="text-md-right total_text" id="itemListSubTotal1">$0.00</td>
          </tr>
          <tr class="border_thin_bottom">
            <td class="total_text">Multiplier:</td>
            <td class="total_text">&nbsp;</td>
            <td class="text-md-right total_text" id="itemListMultiplier"><?php echo $dealer['multiplier']; ?></td>
          </tr>
          <tr class="border_thin_bottom">
            <td class="total_text">NET:</td>
            <td class="total_text">&nbsp;</td>
            <td class="text-md-right total_text" id="itemListNET">$0.00</td>
          </tr>
          <tr class="border_thin_bottom">
            <td class="total_text">Global: Room Details/Shipping:</td>
            <td class="total_text">&nbsp;</td>
            <td class="text-md-right total_text" id="itemListGlobalRoomDetails">$0.00</td>
          </tr>
          <tr class="border_thin_bottom">
            <td class="total_text">Credit Card:</td>
            <td class="total_text">3.5%</td>
            <td class="text-md-right total_text">$0.00</td>
          </tr>
          <tr class="em_box">
            <td class="total_text">Sub Total:</td>
            <td class="total_text">&nbsp;</td>
            <td class="text-md-right total_text" id="finalSubTotal">$0.00</td>
          </tr>
          <tr class="header em_box">
            <td class="total_text">Total Amount</td>
            <td class="total_text">&nbsp;</td>
            <td class="text-md-right total_text" id="finalTotal">$0.00</td>
          </tr>
          <tr id="deposit_line">
            <td colspan="2" class="em_box" style="padding-left:20px;">50% Deposit due to start production</td>
            <td class="text-md-right em_box" id="finalDeposit">$0.00</td>
          </tr>
        </table>

        <div class="clearfix"></div>
      </div>
    </div>
    <!--</editor-fold>-->
  </div>
</div>

<script>
  <?php
  $shipZone = !empty($room['ship_zip']) ? $room['ship_zip'] : $dealer['shipping_zip'];
  $ship_zone_info = calcShipZone($shipZone);
  $shipInfo = json_encode($ship_zone_info, true);

  echo !empty($price_group) ? "var priceGroup = $price_group;" : null;
  echo "var calcShipZip = '{$room['ship_zip']}';";
  echo "var calcDealerShipZip = '{$dealer['shipping_zip']}';";
  echo "var calcShipInfo = '$shipInfo';";
  ?>

  var CLIPBOARD = null,

    editCatalog = $("#editCatalogLock"),
    cabinetList = $("#cabinet_list"),
    catalog = $("#action_container"),
    itemModifications = $("#item_modifications");

  $(function() {
    //<editor-fold desc="Cabinet List">
    cabinetList.fancytree({
      imagePath: "/assets/images/cabinet_icons/",
      titlesTabbable: true,     // Add all node titles to TAB chain
      quicksearch: true,        // Jump to nodes when pressing first character
      source: { url: "/html/pricing/ajax/item_actions.php?action=getCabinetList&room_id=" + active_room_id },
      extensions: ["dnd", "table", "gridnav", "persist"],
      dnd: { // drag and drop
        preventVoidMoves: true,
        preventRecursiveMoves: true,
        autoExpandMS: 600,
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
      table: {
        indentation: 20,
        nodeColumnIdx: 3
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

        // Index #0 => Line Numbering
        $tdList.eq(0).text(node.getIndexHier());

        // Index #1 => Quantity
        $tdList.eq(1).find("input").attr("data-id", node.key).val(node.data.qty);

        // Index #2 => Update all of the buttons to this specific node
        $tdList.eq(2).find(".view_item_info").attr("data-id", node.data.itemID);

        // Index #3 => Nomenclature (SKU) - generated by node.title

        // Index #4 => Description
        if(node.data.customNote === 1) {
          $tdList.eq(4).html('<input type="text" class="form-control custom-line-item" placeholder="Custom Description..." value="' + node.data.name + '" data-id="' + node.key + '" >');
        } else {
          $tdList.eq(4).text(node.data.name);
        }

        // Index #4 => Width
        // $tdList.eq(5).text(node.data.width);
        $tdList.eq(5).find("input").attr("data-id", node.key).val(node.data.width);

        // Index #6 => Height
        // $tdList.eq(6).text(node.data.height);
        $tdList.eq(6).find("input").attr("data-id", node.key).val(node.data.height);

        // Index #7 => Depth
        // $tdList.eq(7).text(node.data.depth);
        $tdList.eq(7).find("input").attr("data-id", node.key).val(node.data.depth);

        // Index #8 => Hinge
        if(node.data.hinge !== undefined) {
          $tdList.eq(8).find(".item_hinge").val(node.data.hinge);
        }

        // Index #9 => Price (individual)
        if(node.data.customPrice === 1) {
          $tdList.eq(9).html('<input type="text" class="form-control custom_price" placeholder="Price" data-id="' + node.key + '" value="' + parseFloat(node.data.price).formatMoney() + '" >');
        } else {
          if (!isNaN(price)) {
            $tdList.eq(9).text(price.formatMoney()).removeAttr("style title"); // price column

            $(".no_global_info").css("display", "none");

            if (!already_submitted) {
              $("#submit_for_quote").attr("disabled", false).attr("title", "");
            }
          } else {
            $tdList.eq(9).css("background-color", "#FF0000").attr("title", "Unknown global attributes, unable to find price.");

            $("#submit_for_quote").attr("disabled", true).attr("title", "Unknown global attributes, unable to submit.");

            $(".no_global_info").css("display", "block");
          }
        }

        //** Adjustments based on the line items being added

        // lining and styles for main lines
        if(node.isTopLevel()) {
          $tdList.addClass("main-level");
        }

        // calculation of how many pages are going to print (total)
        numPages = Math.ceil($(".wrapper").outerHeight() / 980);

        $("#num_of_pgs").text(numPages);

        // update of Global: Cabinet Details pricing
        // TODO: Short Drawer Raise, Tall Drawer Raise, Frame Option, Drawer Box

        // Glaze Technique:
        $("#gt_amt").text()
      },
      modifyChild: function(event, data) {
        pricingFunction.recalcSummary();
      },
      init: function() {
        pricingFunction.recalcSummary();

        // automatically expand all sub-lines
        cabinetList.fancytree("getTree").visit(function(node){
          let $tdList = $(node.tr).find(">td"); // get the columns of the item list

          node.setExpanded(); // set the node as expanded

          if(!node.isTopLevel()) { // if it's not a top level item
            $tdList.eq(2).find('.add_item_mod, .item_copy').css('visibility', 'hidden'); // set the visibility of both item copy and item mod as hidden
          }
        });
      }
    }).on("nodeCommand", function(event, data) {
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
          recalcTotal();
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
    //</editor-fold>

    //<editor-fold desc="Navigation menu">
    // this is the navigation menu on the left side
    catalog.fancytree({
      extensions: ["dnd", "filter"],
      source: { url: "/html/pricing/ajax/nav_menu.php" },
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
      },
      dnd: {
        autoExpandMS: 800,
        focusOnClick: true,
        preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
        preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
        dragStart: function(node, data) {
          /** This function MUST be defined to enable dragging for the tree.
           *  Return false to cancel dragging of node.
           */
          return pricingFunction.catalogCanEdit(editCatalog);
        },
        dragEnter: function(node, data) {
          /** data.otherNode may be null for non-fancytree droppables.
           *  Return false to disallow dropping on node. In this case
           *  dragOver and dragLeave are not called.
           *  Return 'over', 'before, or 'after' to force a hitMode.
           *  Return ['before', 'after'] to restrict available hitModes.
           *  Any other return value will calc the hitMode from the cursor position.
           */
          // Prevent dropping a parent below another parent (only sort
          // nodes under the same parent)
          /*           if(node.parent !== data.otherNode.parent){
                      return false;
                    }
                    // Don't allow dropping *over* a node (would create a child)
                    return ["before", "after"];
          */
          // return true;
          if(node.isFolder()) {
            return true;
          } else {
            return ["before", "after"];
          }
        },
        dragDrop: function(node, data) {
          /** This function MUST be defined to enable dropping of items on
           *  the tree.
           */
          data.otherNode.moveTo(node, data.hitMode);
        },
        dragStop: function(node, data) {
          var neworder = [];
          var i = 0;
          var all_drop_data = node.getParent();

          if(node.isFolder()) {
            all_drop_data.visit(function(all_drop_data) {
              if(all_drop_data.isFolder()) {
                neworder[i] = all_drop_data.key;
                i++;
              }
            });
          } else {
            all_drop_data.visit(function(all_drop_data) {
              if(!all_drop_data.isFolder()) {
                neworder[i] = all_drop_data.key;
                i++;
              }
            });
          }

          neworder = JSON.stringify(neworder);

          $.post("/html/pricing/ajax/item_actions.php?action=updateCategoryOrder", {newOrder: neworder, parent: node.parent.key, curCat: node.key, isFolder: node.isFolder()}, function(data) {
            $("body").append(data);
          });
        }
      }
    }).on("nodeCommand", function(event, data){
      // Custom event handler that is triggered by keydown-handler and
      // context menu:
      var refNode, moveMode,
        tree = $(this).fancytree("getTree"),
        node = tree.getActiveNode(),
        addType = null;

      if(editCatalog.hasClass("fa-unlock")) {
        switch (data.cmd) {
          case "moveUp":
            refNode = node.getPrevSibling();
            if (refNode) {
              node.moveTo(refNode, "before");
              node.setActive();
            }
            break;
          case "moveDown":
            refNode = node.getNextSibling();
            if (refNode) {
              node.moveTo(refNode, "after");
              node.setActive();
            }
            break;
          case "indent":
            refNode = node.getPrevSibling();
            if (refNode) {
              node.moveTo(refNode, "child");
              refNode.setExpanded();
              node.setActive();
            }
            break;
          case "outdent":
            if (!node.isTopLevel()) {
              node.moveTo(node.getParent(), "after");
              node.setActive();
            }
            break;
          case "addChild":
            if(node.isFolder()) {
              addType = 'child';
            } else {
              addType = 'after';
            }

            $.get("/html/pricing/ajax/modify_item.php", {type: 'addItem', id: node.key}, function(data) {
              $("#modalGlobal").html(data).modal("show");
            });
            break;
          case "cut":
            CLIPBOARD = {mode: data.cmd, data: node};
            break;
          case "copy":
            CLIPBOARD = {
              mode: data.cmd,
              data: node.toDict(function (n) {
                delete n.key;
              })
            };
            break;
          case "clear":
            CLIPBOARD = null;
            break;
          case "paste":
            if (CLIPBOARD.mode === "cut") {
              // refNode = node.getPrevSibling();
              CLIPBOARD.data.moveTo(node, "child");
              CLIPBOARD.data.setActive();
            } else if (CLIPBOARD.mode === "copy") {
              node.addChildren(CLIPBOARD.data).setActive();
            }
            break;
          case "deselect":
            if (node !== null)
              node.setActive(false);
            break;
          case "delete":
            $.confirm({
              title: "Are you sure you want to remove this item?",
              content: "You are about to remove " + node.title + ". Are you sure?",
              type: 'red',
              buttons: {
                yes: function() {
                  node.remove();
                },
                no: function() {}
              }
            });
            break;
          case "addSubFolder":
            $.get("/html/pricing/ajax/modify_item.php", {type: 'newSubFolder', id: node.key}, function(data) {
              $("#modalGlobal").html(data).modal("show");
            });
            break;
          case "addFolder":
            $.get("/html/pricing/ajax/modify_item.php", {type: 'newSameFolder', id: node.key}, function(data) {
              $("#modalGlobal").html(data).modal("show");
            });
            break;
          case "save":
            $("#saveOPL").trigger('click');
            break;
          case "edit":
            let type = null;

            if(node.isFolder()) {
              type = 'folder';
            } else {
              type = 'item';
            }

            $.get("/html/pricing/ajax/modify_item.php", {type: type, id: node.key}, function(data) {
              $("#modalGlobal").html(data).modal("show");
            });
            break;
          default:
            alert("Unhandled command: " + data.cmd);
            return;
        }
      }
    }).on("keydown", function(e){
      var cmd = null;

      switch( $.ui.fancytree.eventToString(e) ) {
        case "ctrl+shift+n":
        case "meta+shift+n": // mac: cmd+shift+n
          cmd = "addChild";
          break;
        case "ctrl+shift+e":
        case "meta+shift+e":
          cmd = "addSibling";
          break;
        case "ctrl+shift+f":
        case "meta+shift+f":
          cmd = "addFolder";
          break;
        case "ctrl+r": // beacause this is refresh and I lost my changes :(
        case "meta+r":
        case "ctrl+shift+r":
        case "meta+shift+r":
          e.preventDefault();
          break;
        case "ctrl+f":
        case "meta+f":
          e.preventDefault();
          cmd = "addSubFolder";
          break;
        case "ctrl+e":
        case "meta+e":
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
        case "ctrl+s":
        case "meta+s":
          e.preventDefault();
          cmd = "save";
          break;
        case "ctrl+o":
        case "meta+o":
          // TODO: Assign an SO # to lines, we're gonna show operations and edit SO's from here
          break;
        case "ctrl+shift+up":
        case "ctrl+up":
          cmd = "moveUp";
          break;
        case "ctrl+shift+down":
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
          break;
        case "ctrl+del":
          cmd = "delete";
          break;
        case "esc":
          cmd = "deselect";
          break;
      }

      if(cmd) {
        $(this).trigger("nodeCommand", {cmd: cmd});
        return false;
      }
    });

    catalog.contextmenu({
      delegate: "span.fancytree-node",
      menu: [
        {title: "Edit <kbd>[F2]</kbd>", cmd: "edit", uiIcon: "ui-icon-pencil" },
        {title: "----"},
        {title: "New Item <kbd>[Ctrl+E]</kbd>", cmd: "addChild", uiIcon: "ui-icon-plus"},
        {title: "----"},
        {title: "New Same Level Category <kbd>[Ctrl+Shift+F]</kbd>", cmd: "addFolder", uiIcon: "ui-icon-folder-collapsed"},
        {title: "New Sub-Category <kbd>[Ctrl+F]</kbd>", cmd: "addSubFolder", uiIcon: "ui-icon-folder-open"},
        {title: "----"},
        {title: "Delete <kbd>[Ctrl+Del]</kbd>", cmd: "delete", uiIcon: "ui-icon-circle-minus"}
      ],
      beforeOpen: function(event, ui) {
        var node = $.ui.fancytree.getNode(ui.target);
        catalog.contextmenu("enableEntry", "paste", !!CLIPBOARD);
        node.setActive();

        ui.menu.css('zIndex', 1001);

        // check each sub-node for the active node
        catalog.fancytree("getTree").getActiveNode().visit(function(e) {
          if(e.isFolder()) { // if there is a folder, we're disabling add new item
            catalog.contextmenu('updateEntry', 'addChild', {disabled: true}); // update addchild (item) menu item to be disabled
            return false; // no need to continue, we've found a folder
          } else { // otherwise, there are no folders, we can proceed with adding items but cannot add folders
            catalog.contextmenu('updateEntry', 'addChild', {disabled: false});
          }
        });

        return pricingFunction.catalogCanEdit(editCatalog);
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
    //</editor-fold>



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
</script>