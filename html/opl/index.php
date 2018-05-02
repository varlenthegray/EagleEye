<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<link href="/assets/css/opl.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />

<div class="card-box">
  <div class="row">
    <div class="no-print sticky" id="opl_warning"></div>

    <h1>Open Point List</h1>

    <div class="col-md-8">
      <table id="opl" class="pricing_table_format">
        <colgroup>
          <col width="20px" class="no-print">
          <col width="50px">
          <col width="50px" class="no-print">
          <col width="50px">
          <col width="450px">
          <col width="150px" class="no-print">
          <col width="80px">
          <col width="80px">
          <!--<col width="80px">
          <col width="80px">-->
        </colgroup>
        <thead>
        <tr>
          <td colspan="5" class="no-print" style="padding:5px 0;">
            <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="addOPLFolder" value="Add Folder" />
            <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="addOPLTask" style="display:none;" value="Add Sub-task" />
            <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="oplPrint" value="Print" />
            <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="oplClearSelected" style="display:none;" value="Clear Checked" />
            <input type="button" class="btn btn-danger waves-effect waves-light opl_action" id="completeOPLNodes" style="display:none;" value="Complete" />
            <input type="button" class="btn btn-success waves-effect waves-light opl_action" id="saveOPL" value="Save" />
            <input type="button" class="btn btn-secondary waves-effect waves-light opl_action" id="oplRefresh" value="Refresh" />

            <h4 class="pull-right" id="viewing"></h4>
          </td>
          <td colspan="1" class="no-print">
          <td colspan="2" class="no-print"><input type="text" class="opl_filter" id="findOPL" placeholder="Find..."></td>
        </tr>
        <tr>
          <th class="no-print"></th>
          <th class="text-md-center">#</th>
          <th class="text-md-center no-print">Actions</th>
          <th class="text-md-center">Priority</th>
          <th>Open Points</th>
          <th class="text-md-center no-print">Created</th>
          <th class="text-md-center">Time Left</th>
          <th class="text-md-center">Due Date</th>
          <!--<th>Relies On</th>
          <th>Visibility</th>-->
        </tr>
        </thead>
        <tbody>
        <!-- Define a row template for all invariant markup: -->
        <tr>
          <td class="alignCenter no-print"></td>
          <td class="pad-l5"></td>
          <td class="text-md-center task_actions no-print">
            <i class="fa fa-info-circle no-info view_task_info" title="Task Information"></i>
            <i class="fa fa-plus-circle primary-color add_subtask" title="Add Subtask"></i>
            <i class="fa fa-minus-circle danger-color complete_task" title="Complete Task"></i>
            <!--<i class="fa fa-exclamation-triangle primary-color task_alerts" title="Alerts"></i>-->
          </td>
          <td class="text-md-center"><input type="text" class="OPLPriority" value="" placeholder="" /></td>
          <td></td>
          <td class="pad-l5 no-print"></td>
          <td class="alignCenter">
            <select class="custom-select task_length" style="width: 100%;">
              <option value="???">???</option>
              <option value="< 1 Hr" class="length_green">< 1 Hr</option>
              <option value="1-3 Hrs" class="length_green">1-3 Hrs</option>
              <option value="3-6 Hrs" class="length_yellow">3-6 Hrs</option>
              <option value="1 Day" class="length_yellow">1 Day</option>
              <option value="2-3 Days" class="length_black">2-3 Days</option>
              <option value="3+ Days" class="length_black">3+ Days</option>
            </select>
          </td>
          <td class="alignCenter"><input type="text" class="due_date" value="" placeholder="Empty" /></td>
          <!--<td class="pad-l5">RG, BB</td>-->
          <!--<td>
            <select>
              <option value="public">Public</option>
              <option value="management">Management</option>
              <option value="private">Private</option>
            </select>
          </td>-->
        </tr>
        </tbody>
      </table>
    </div>


    <div class="col-md-2 col-md-offset-1 no-print">
      <h4>History</h4>

      <table id="opl_history" class="pricing_table_format">
        <colgroup>
          <col width="10px">
          <col width="80px">
          <col width="60px">
          <col width="15px">
        </colgroup>
        <thead class="sticky">
        <tr>
          <th class="text-md-center">#</th>
          <th>Updated</th>
          <th>Updated By</th>
          <th>View</th>
        </tr>
        </thead>
        <tbody>
        <!-- Define a row template for all invariant markup: -->
        <tr>
          <td class="alignCenter"></td>
          <td></td>
          <td class="pad-l5"></td>
          <td class="task_actions"><i class="fa fa-eye primary-color view_history"></i></td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- OPL Info modal -->
<div id="modalOPLInfo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalOPLInfoLabel" aria-hidden="true">
  <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<script>
  var opl = $("#opl");
  var opl_history = $("#opl_history");
  var CLIPBOARD = null;
  var opl_usr = null; // the user that the OPL is loaded for, initially none, overwritten on document load
  var disabled = false;

  var curOpl = null;

  // this generates unique keys, put in a function because it's going to be done multiple times, may be updated later
  function generateUniqueKey() {
    return new Date().getTime();
  }

  function calcDueDate(ele) {
    let dueDateField = ele.parent().parent().find(".due_date"); // get the due date of this field
    let timeLeftField = ele.parent().parent().find(".task_length"); // get the time left field
    let timeLeft = dueDateField.parent().parent().find(".task_length").find(":selected").val(); // get the selected element of time left for this row
    let duration = null; // duration default to null (error out)
    let ddTime = moment(dueDateField.val()); // the due date time

    // time to figure out how long we think this is going to take and add it to the due date time
    switch(timeLeft) {
      case '< 1 Hr':
        ddTime.subtract(1, 'hour');
        break;
      case '1-3 Hrs':
        ddTime.subtract(3, 'hours');
        break;
      case '3-6 Hrs':
        ddTime.subtract(6, 'hours');
        break;
      case '1 Day':
        ddTime.subtract(1, 'day');
        break;
      case '2-3 Days':
        ddTime.subtract(3, 'days');
        break;
      case '3+ Days':
        ddTime.subtract(5, 'days');
        break;
      default:
        duration = 0;
        break;
    }

    let tomorrow = null; // tomorrow
    let futureThreeDays = null; // three days from today

    // if the due date is saturday or sunday
    if(ddTime.day() === 6) {
      tomorrow = moment().add(3, 'days'); // add two days, since it's saturday
      futureThreeDays = moment().add(3, 'days'); // three days from today
    } else if(ddTime.day() === 7) {
      tomorrow = moment().add(2, 'days'); // add 1 day since it's sunday
      futureThreeDays = moment().add(2, 'days'); // three days from today
    } else {
      tomorrow = moment().add(1, 'day');
      futureThreeDays = moment().add(2, 'days'); // three days from today
    }

    if(dueDateField.val() !== '') {
      dueDateField.parent().removeClass("length_red length_green length_yellow length_black");

      // if the current time minus
      if(ddTime.isBefore(tomorrow)) {
        dueDateField.parent().addClass("length_red");
      } else if(ddTime.isBetween(tomorrow, futureThreeDays)) {
        dueDateField.parent().addClass("length_yellow");
      } else if(ddTime.isSameOrAfter(futureThreeDays)) {
        dueDateField.parent().addClass("length_green");
      }
    }

    if(timeLeft !== '???') {
      timeLeftField.removeClass("length_red length_green length_yellow length_black");
      timeLeftField.addClass(timeLeftField.find(":selected").attr("class"));
    }
  }

  function updateOPLTree() {
    // update the tree based on that user's information
    opl.fancytree('getTree').reload({url: '/html/opl/ajax/actions.php?action=getOPL'});
    opl_history.fancytree('getTree').reload({url: "/html/opl/ajax/actions.php?action=getOPLHistory"});

    curOpl = JSON.stringify(opl.fancytree("getTree").toDict(true));
  }

  $(function() {
    opl.fancytree({
      select: function(event, data) {
        var selNodes = data.tree.getSelectedNodes();
        // convert to title/key array
        var selKeys = $.map(selNodes, function(node){
          return "[" + node.key + "]: '" + node.title + "'";
        });

        // console.log(selKeys.join(", "));

        if(selKeys.length > 0) {
          $("#completeOPLNodes").show();
          $("#oplClearSelected").show();
          sendOPLEdit(opl_usr);
        } else {
          $("#completeOPLNodes").hide();
          $("#oplClearSelected").hide();
          socket.emit("oplSaved");
        }
      },
      checkbox: true,
      activate: function(event, data) {
        $("#addOPLTask").show();
      },
      deactivate: function(event, data) {
        $("#addOPLTask").hide();
      },
      selectMode: 3,
      titlesTabbable: true,     // Add all node titles to TAB chain
      quicksearch: true,        // Jump to nodes when pressing first character
      source: { url: "/html/opl/ajax/actions.php?action=getOPL" },
        extensions: ["edit", "dnd", "table", "gridnav", "filter", "persist"],
        dnd: {
          preventVoidMoves: true,
          preventRecursiveMoves: true,
          autoExpandMS: 550,
          dragStart: function(node, data) {
            if(!disabled) {
              return true;
            }
          },
          dragEnter: function(node, data) {
            // return ["before", "after"];
            if(!disabled) {
              return true;
            }
          },
          dragDrop: function(node, data) {
            data.otherNode.moveTo(node, data.hitMode);
            sendOPLEdit();
          },
          draggable: {
            scroll: true
          }
        },
        edit: {
          triggerStart: ["f2", "shift+click", "mac+enter"],
          edit: function(event, data) {
            data.input.select();

            // we're now editing a new line, it's time to alert the system that an edit is taking place
            sendOPLEdit(opl_usr);

            socket.emit("getOPLEditingStatus");
          },
          close: function(event, data) {
            // after finishing editing
            let tree = getMiniTree(opl);

            socket.emit("commitOPLChanges", tree);
          }
        },
        table: {
          indentation: 20,
          nodeColumnIdx: 4,
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

          // (Index #2 is the priority textbox)
          let priorityTextbox = $tdList.eq(3).find("input");
          priorityTextbox.val(node.data.priority);

          if(node.data.priority !== undefined) {
            if(node.data.priority.length > 0) {
              priorityTextbox.addClass("white_black");
            }
          }

          // (Index #3 is rendered by fancytree)

          // (Index #4 is the actions column)
          $tdList.eq(2).find(".view_task_info").attr("data-uid", node.key).attr("data-indexHeir", node.getIndexHier()).attr("data-title", node.title);

          if(node.data.hasInfo === true) {
            $tdList.eq(2).find(".view_task_info").removeClass("no-info").addClass("has-info");
          }

          // (Index #5 is the creation date of that element)
          $tdList.eq(5).text(node.data.creation_date);

          // (Index #6 is the time left select box)
          $tdList.eq(6).find("select").val(node.data.time_left);

          calcDueDate($tdList.eq(6).find("select"));

          // (Index #7 is the due date text box)
          $tdList.eq(7).find("input").val(node.data.due_date);

          calcDueDate($tdList.eq(7).find("input"));

          // enable the datepicker for due date
          $(".due_date").datepicker({
            autoclose: true,
            startDate: new Date(),
            daysOfWeekDisabled: [0,6]
          });

          // calculate the due date color
          calcDueDate($(this));
        },
        debugLevel: 0,
        init: function(event, data) {
          curOpl = JSON.stringify(opl.fancytree("getTree").toDict(true));
          $(".fancytree-container").addClass("fancytree-connectors");

          opl.floatThead({
            position: 'auto',
            top: 134
          });
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
          sendOPLEdit();
          break;
        case "addChild":
          $("#addOPLTask").trigger("click");
          sendOPLEdit();
          break;
        case "addSibling":
          node.editCreateNode("after", {
            title: "New Task...",
            creation_date: new Date().toLocaleString(),
            time_left: '???',
            key: generateUniqueKey()
          });
          sendOPLEdit();
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
            sendOPLEdit();
          } else if( CLIPBOARD.mode === "copy" ) {
            node.addChildren(CLIPBOARD.data).setActive();
            sendOPLEdit();
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

                  sendOPLEdit();
                },
                no: function() {}
              }
            });
          }
          break;
        case "addSubFolder":
          $("#addOPLFolder").trigger("click");
          sendOPLEdit();
          break;
        case "addFolder":
          node.editCreateNode("after", {
            title: "New Folder...",
            folder: true,
            creation_date: new Date().toLocaleString(),
            time_left: '???',
            key: generateUniqueKey()
          });
          sendOPLEdit();
          break;
        case "save":
          $("#saveOPL").trigger('click');
          sendOPLEdit();
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
    opl.contextmenu({
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
        opl.contextmenu("enableEntry", "paste", !!CLIPBOARD);
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

    opl_history.fancytree({
      extensions: ["table"],
      icon: false,
      table: { nodeColumnIdx: 1 },     // render the node title into the 2nd column
      source: { url: "/html/opl/ajax/actions.php?action=getOPLHistory" },
      renderColumns: function(event, data) {
        var node = data.node,
          $tdList = $(node.tr).find(">td");
        // (index #0 is index number)
        $tdList.eq(0).text(node.getIndexHier()).addClass("alignRight");
        // (index #1 is rendered by fancytree)

        $tdList.eq(2).text(node.data.updated_by);

        $tdList.eq(3).find("i").attr("id", node.data.id);
      }
    });

    socket.emit("getOPLEditingStatus");
  });
</script>