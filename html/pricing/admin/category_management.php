<?php
require '../../../includes/header_start.php';

?>

<link href="/assets/css/pricing_admin.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />

<div class="card-box">
  <div class="row">
    <div class="col-md-12">
      <div class="nav_panel">
        <h1>Category Management</h1>
        <h6>Pricing Program > Admin > Category Management</h6>

        <table id="category_management" class="pricing_table_format">
          <colgroup>
            <col width="20px" class="no-print">
            <col width="50px">
            <col width="50px" class="no-print">
            <col width="450px">
          </colgroup>
          <thead class="sticky">
          <tr>
            <td colspan="4" class="no-print" style="padding-bottom:5px;">
              <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="addOPLFolder" value="Add Category" />
              <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="oplPrint" value="Print" />
              <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="oplClearSelected" style="display:none;" value="Clear Checked" />
              <input type="button" class="btn btn-success waves-effect waves-light opl_action" id="saveOPL" value="Save" />
              <input type="button" class="btn btn-secondary waves-effect waves-light opl_action" id="oplRefresh" value="Refresh" />

              <input type="text" class="opl_filter pull-right" id="findOPL" placeholder="Find...">
            </td>
          </tr>
          <tr>
            <th class="no-print"></th>
            <th class="pad-l5">#</th>
            <th class="text-md-center no-print">Actions</th>
            <th class="pad-l5">Category</th>
          </tr>
          </thead>
          <tbody>
          <!-- Define a row template for all invariant markup: -->
          <tr>
            <td class="alignCenter no-print"></td>
            <td class="pad-l5"></td>
            <td class="text-md-center task_actions no-print"></td>
            <td></td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  var CLIPBOARD = null;
  var cat_mgmt = $("#category_management");

  cat_mgmt.fancytree({
    select: function(event, data) {
      var selNodes = data.tree.getSelectedNodes();
      // convert to title/key array
      var selKeys = $.map(selNodes, function(node){
        return "[" + node.key + "]: '" + node.title + "'";
      });

      // console.log(selKeys.join(", "));
    },
    checkbox: true,
    selectMode: 2,
    titlesTabbable: true,     // Add all node titles to TAB chain
    quicksearch: true,        // Jump to nodes when pressing first character
    source: { url: "/html/pricing/ajax/admin/category_actions.php?action=getCategoryList"},
    extensions: ["edit", "dnd", "table", "gridnav", "filter", "persist"],
    dnd: {
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
      triggerStart: ["f2", "shift+click", "mac+enter"],
      close: function(event, data) {
        if( data.save && data.isNew ){
          // Quick-enter: add new nodes until we hit [enter] on an empty title
          cat_mgmt.trigger("nodeCommand", {cmd: "addSibling"});
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
    filter: {
      autoExpand: true,
      mode: 'hide'
    },
    renderColumns: function(event, data) {
      var node = data.node,
        $tdList = $(node.tr).find(">td");

      // (Index #0 is rendered by fancytree by adding the checkbox)
      // Set column #1 info from node data:

      // (Index #1 is the index heir level)
      $tdList.eq(1).text(node.getIndexHier());

      // (Index #3 is rendered by fancytree)

    },
    debugLevel: 0,
    lazyLoad: function(event, data) {
      var node = data.node;

      // return children or any other node source
      // data.result = {url: "/html/opl/ajax/actions.php?action=getOPL&user_id=" + node.data.user_id};
    },
    persist: {
      expandLazy: true
    }
  }).on("nodeCommand", function(event, data){
    // Custom event handler that is triggered by keydown-handler and
    // context menu:
    var refNode, moveMode,
      tree = $(this).fancytree("getTree"),
      node = tree.getActiveNode();

    switch(data.cmd) {
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
      case "addChild":
        $("#addOPLTask").trigger("click");
        break;
      case "addSibling":
        node.editCreateNode("after", {
          title: "New Task...",
          creation_date: new Date().toLocaleString(),
          time_left: '???',
          key: generateUniqueKey()
        });
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
      case "deselect":
        if(node !== null)
          node.setActive(false);
        break;
      case "completeTask":
        if(!disabled) {
          $.confirm({
            title: "Are you sure you want to complete this task?",
            content: "You are about to remove task " + node.getIndexHier() + ": " + node.title + ". Are you sure?",
            type: 'red',
            buttons: {
              yes: function() {
                node.remove();

                // re-render the tree deeply so that we can recalculate the line item numbers
                opl.fancytree("getRootNode").render(true,true);
              },
              no: function() {}
            }
          });
        }
        break;
      case "addSubFolder":
        $("#addOPLFolder").trigger("click");
        break;
      case "addFolder":
        node.editCreateNode("after", {
          title: "New Folder...",
          folder: true,
          creation_date: new Date().toLocaleString(),
          time_left: '???',
          key: generateUniqueKey()
        });
        break;
      case "save":
        $("#saveOPL").trigger('click');
        break;
      default:
        alert("Unhandled command: " + data.cmd);
        return;
    }
  }).on("keydown", function(e){
    var cmd = null;

    // console.log($.ui.fancytree.eventToString(e));

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
      case "esc":
        cmd = "deselect";
        break;
    }

    if(cmd) {
      $(this).trigger("nodeCommand", {cmd: cmd});
      return false;
    }
  });

  /* Context menu (https://github.com/mar10/jquery-ui-contextmenu) */
  cat_mgmt.contextmenu({
    delegate: "span.fancytree-node",
    menu: [
      {title: "Edit <kbd>[F2]</kbd>", cmd: "rename", uiIcon: "ui-icon-pencil" },
      {title: "Save <kbd>[Ctrl+S]</kbd>", cmd: "save", uiIcon: "ui-icon-disk" },
      // {title: "Undo <kbd>[Ctrl+Z]</kbd>", uiIcon: "ui-icon-arrowreturnthick-1-w" },
      {title: "----"},
      {title: "Modify SO <kbd>[Ctrl+O]</kbd>", cmd: "editSO", uiIcon: "ui-icon-extlink" },
      {title: "----"},
      {title: "New task <kbd>[Ctrl+Shift+E]</kbd>", cmd: "addSibling", uiIcon: "ui-icon-plus" },
      {title: "New sub-task <kbd>[Ctrl+E]</kbd>", cmd: "addChild", uiIcon: "ui-icon-arrowreturn-1-e" },
      {title: "----"},
      {title: "New Same Level Folder <kbd>[Ctrl+Shift+F]</kbd>", cmd: "addFolder", uiIcon: "ui-icon-folder-collapsed"},
      {title: "New Sub-Folder <kbd>[Ctrl+F]</kbd>", cmd: "addSubFolder", uiIcon: "ui-icon-folder-open"},
      {title: "----"},
      {title: "Complete Task", cmd: "completeTask", uiIcon: "ui-icon-circle-minus"}
    ],
    beforeOpen: function(event, ui) {
      var node = $.ui.fancytree.getNode(ui.target);
      cat_mgmt.contextmenu("enableEntry", "paste", !!CLIPBOARD);
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
</script>