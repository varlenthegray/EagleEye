<?php
require '../includes/header_start.php';

outputPHPErrs();

?>

<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="A fully functional ERP designed to manage cabinetry and automation.">
  <meta name="author" content="Stone Mountain Cabinetry & Millwork">

  <!-- App Favicon -->
  <link rel="shortcut icon" href="/assets/images/favicon.ico">

  <!-- App title -->
  <title><?php echo TAB_TEXT; ?></title>

  <!-- JQuery & JQuery UI -->
  <script src="/assets/js/jquery.min.js"></script>
  <script src="/includes/js/jquery-ui.min.js"></script>
  <link href="/includes/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>

  <!-- Global JS functions -->
  <script src="/includes/js/functions.min.js?v=<?php echo VERSION; ?>"></script>
  <link href="https://fonts.googleapis.com/css?family=Patua+One" rel="stylesheet">

  <!-- App CSS -->
  <link href="/assets/css/style.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
  <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
  <![endif]-->
  <!-- Modernizr js -->
  <script src="/assets/js/modernizr.min.js"></script>

  <!-- SocketIO -->
  <script src="/server/node_modules/socket.io-client/dist/socket.io.js"></script>

  <!-- Toastr setup -->
  <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

  <!-- Fancytree -->
  <link rel="stylesheet" type="text/css" href="/assets/plugins/fancytree/skin-win8-n/ui.fancytree.css"/>

  <!-- Datatables -->
  <link href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.0/css/rowReorder.dataTables.min.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.1.3/css/fixedHeader.dataTables.min.css"/>
  <link href="/assets/plugins/datatables/datatables.paginate.fix.css" rel="stylesheet" type="text/css"/>

  <!-- Date Picker -->
  <link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

  <!-- Alert Windows -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">

  <!-- Select2 -->
  <link href="/assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
  <script src="/assets/plugins/select2/js/select2.min.js"></script>

  <!-- DHTMLX -->
  <link rel="stylesheet" href="/assets/css/dhtmlx/dhtmlx.min.css" type="text/css">
  <script src="https://cdn.dhtmlx.com/edge/dhtmlx.js" type="text/javascript"></script>

  <?php
  $server = explode('.', $_SERVER['HTTP_HOST']);

  if(false !== stripos($_SERVER['REQUEST_URI'], 'inset_sizing.php')) {
    echo '<link href="/assets/css/inset_sizing.css" rel="stylesheet">';
  }
  ?>
</head>
<body>

<div id="catalog_categories">

</div>

<!-- jQuery  -->
<script src="/assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/waves.js"></script>
<script src="/assets/js/jquery.nicescroll.js"></script>

<!-- custom dropdown -->
<script src="/includes/js/custom_dropdown.min.js?v=<?php echo VERSION; ?>"></script>

<!-- Toastr setup -->
<script src="/assets/plugins/toastr/toastr.min.js"></script>
<link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

<!-- Datatables -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.2/b-html5-1.5.2/b-print-1.5.2/fh-3.1.4/rg-1.0.3/sc-1.5.0/sl-1.2.6/datatables.min.js"></script>

<!-- Moment.js for Timekeeping -->
<script src="/assets/plugins/moment/moment.js"></script>

<!-- Alert Windows -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>

<!-- Mask -->
<!--  <script src="/assets/plugins/jquery.mask.min.js"></script> 9/21/18-->

<!-- Counter Up  -->
<script src="/assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
<script src="/assets/plugins/counterup/jquery.counterup.min.js"></script>

<!-- App js -->
<script src="/assets/js/jquery.core.js"></script>
<script src="/assets/js/jquery.app.js"></script>

<!-- Tinysort -->
<script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

<!-- Input Masking -->
<!--  <script type="text/javascript" src="/assets/plugins/jquery.mask.min.js"></script> 9/21/18-->

<!-- Datepicker -->
<!--  <script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script> 9/21/18-->

<!-- JScroll -->
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js"></script>

<!-- Math, fractions and more -->
<script src="/assets/plugins/math.min.js"></script>

<!-- Pricing program -->
<script src="/html/pricing/pricing.min.js?v=<?php echo VERSION; ?>"></script>

<!-- Adding Room -->
<script src="/html/search/add_room.min.js?v=<?php echo VERSION; ?>"></script>

<!-- Fancytree -->
<script src="/assets/plugins/fancytree/jquery.fancytree.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.filter.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.dnd.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.edit.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.gridnav.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.table.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.persist.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.fixed.js"></script>

<!-- MapHilight - for Area Maps on images, dashboard circle display mostly -->
<script src="/assets/plugins/maphilight/jquery.maphilight.min.js"></script>

<!-- Float TableHead -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/2.1.2/jquery.floatThead.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/ui-contextmenu/jquery.ui-contextmenu.min.js"></script>

<!-- Unsaved Changes -->
<script src="/assets/js/unsaved_alert.js?v=<?php echo VERSION; ?>"></script>

<!-- Sticky table header -->
<script src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<script src="/assets/plugins/dhtmlxScheduler/dhtmlxscheduler.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" href="/assets/plugins/dhtmlxScheduler/dhtmlxscheduler_material.css" type="text/css"  title="no title" charset="utf-8">

<script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_limit.js" type="text/javascript" charset="utf-8"></script>
<script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_tooltip.js" type="text/javascript" charset="utf-8"></script>
<script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_recurring.js" type="text/javascript"></script>

<link rel="stylesheet" href="/html/calendar/ajax/events.php?action=getEventCSS&v=<?php echo VERSION; ?>" type="text/css" charset="utf-8">

<script>
  var catalog = $("#catalog_categories"),
    CLIPBOARD = null;

  catalog.fancytree({
    extensions: ["dnd", "edit", "filter"],
    source: { url: "/html/pricing/ajax/nav_menu.php?disable_btns=true" },
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
        return true;
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
        return true;
      },
      dragDrop: function(node, data) {
        /** This function MUST be defined to enable dropping of items on
         *  the tree.
         */
        data.otherNode.moveTo(node, data.hitMode);
      }
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
        node.editCreateNode("child", {
          title: "",
          creation_date: new Date().toLocaleString()
        });
        break;
      case "addSibling":
        node.editCreateNode("after", {
          title: "SKU",
          creation_date: new Date().toLocaleString()
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
      case "delete":
        $.confirm({
          title: "Are you sure you want to remove this item?",
          content: "You are about to remove " + node.title + ". Are you sure?",
          type: 'red',
          buttons: {
            yes: function() {
              node.remove();

              // re-render the tree deeply so that we can recalculate the line item numbers
              catalog.fancytree("getRootNode").render(true,true);
            },
            no: function() {}
          }
        });
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

  catalog.contextmenu({
    delegate: "span.fancytree-node",
    menu: [
      {title: "Edit <kbd>[F2]</kbd>", cmd: "rename", uiIcon: "ui-icon-pencil" },
      {title: "----"},
      {title: "New Item <kbd>[Ctrl+E]</kbd>", cmd: "addChild", uiIcon: "ui-icon-plus" },
      {title: "----"},
      {title: "New Same Level Category <kbd>[Ctrl+Shift+F]</kbd>", cmd: "addFolder", uiIcon: "ui-icon-folder-collapsed"},
      {title: "New Sub-Category <kbd>[Ctrl+F]</kbd>", cmd: "addSubFolder", uiIcon: "ui-icon-folder-open"},
      {title: "----"},
      {title: "Delete", cmd: "delete", uiIcon: "ui-icon-circle-minus"}
    ],
    beforeOpen: function(event, ui) {
      var node = $.ui.fancytree.getNode(ui.target);
      $("#tree").contextmenu("enableEntry", "paste", !!CLIPBOARD);
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
</body>
</html>