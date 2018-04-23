<?php
require '../../includes/header_start.php';

outputPHPErrs();
?>

<link href="/assets/css/opl.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />

<div class="card-box">
  <div class="row">
    <div id="opl_warning"></div>

    <h1>Open Point List</h1>

    <div class="col-md-8">
      <table id="opl" class="pricing_table_format">
        <colgroup>
          <col width="20px">
          <col width="50px">
          <col width="450px">
          <col width="80px">
          <col width="150px">
          <col width="80px">
          <col width="80px">
          <col width="80px">
          <col width="80px">
        </colgroup>
        <thead class="sticky">
        <tr>
          <td colspan="4" style="padding-bottom:5px;">
            <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="addOPLFolder" value="Add Folder" />
            <input type="button" class="btn btn-primary waves-effect waves-light opl_action" id="addOPLTask" style="display:none;" value="Add Sub-task" />
            <input type="button" class="btn btn-danger waves-effect waves-light opl_action" id="completeOPLNodes" style="display:none;" value="Complete" />
            <input type="button" class="btn btn-success waves-effect waves-light opl_action" id="saveOPL" value="Save" />
            <input type="button" class="btn btn-secondary waves-effect waves-light opl_action" id="oplRefresh" value="Refresh" />
          </td>
          <td><h4 id="viewing"></h4></td>
          <td colspan="2">
            <label for="user">User: </label>
            <select class="custom-select" id="user_id" style="width:80%;">
              <?php
              $usr_qry = $dbconn->query('SELECT * FROM user WHERE account_status = TRUE AND id != 16 ORDER BY name ASC');

              while($usr = $usr_qry->fetch_assoc()) {
                $selected = ($usr['id'] === $_SESSION['shop_user']['id']) ? 'selected' : null;

                echo "<option value='{$usr['id']}' $selected>{$usr['name']}</option>";
              }
              ?>
            </select>
          </td>
          <td colspan="2"><input type="text" class="opl_filter" id="findOPL" placeholder="Find..."></td>
        </tr>
        <tr>
          <th></th>
          <th class="text-md-center">#</th>
          <th>Open Points</th>
          <th class="text-md-center">Actions</th>
          <th class="text-md-center">Created</th>
          <th class="text-md-center">Time Left</th>
          <th class="text-md-center">Due Date</th>
          <th>Relies On</th>
          <th>Visibility</th>
        </tr>
        </thead>
        <tbody>
        <!-- Define a row template for all invariant markup: -->
        <tr>
          <td class="alignCenter"></td>
          <td class="pad-l5"></td>
          <td></td>
          <td class="text-md-center task_actions">
            <i class="fa fa-info-circle primary-color view_task_info" title="Task Information"></i>
            <i class="fa fa-plus-circle primary-color add_subtask" title="Add Subtask"></i>
            <i class="fa fa-check-circle primary-color complete_task" title="Complete Task"></i>
            <i class="fa fa-exclamation-triangle primary-color task_alerts" title="Alerts"></i>
          </td>
          <td class="text-md-center"></td>
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
          <td class="pad-l5">RG, BB</td>
          <td>
            <select>
              <option value="public">Public</option>
              <option value="management">Management</option>
              <option value="private">Private</option>
            </select>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <div class="col-md-2 col-md-offset-1">
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
  // TODO: Figure out how to make it so that externally saved changes by someone else are not overwritten
  /*
  * Upon load of the OPL, we should be able to save the entire thing to a variable
  * Once saved to a variable, when clicking the save button check to see if the database OPL matches what is in the variable
  * If it doesn't match, alert the user, if it does match, save the new OPL
   */

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
    opl.fancytree('getTree').reload({url: '/html/opl/ajax/actions.php?action=getOPL&user_id=' + opl_usr});
    opl_history.fancytree('getTree').reload({url: "/html/opl/ajax/actions.php?action=getOPLHistory&user_id=" + opl_usr});

    curOpl = JSON.stringify(opl.fancytree("getTree").toDict(true));
  }

  oplUpdater = setInterval(function() {
    let changedOpl = JSON.stringify(opl.fancytree("getTree").toDict(true));

    if(changedOpl === curOpl) {
      $("#opl_warning").html('');
      updateOPLTree();
    } else {
      $("#opl_warning").html('<div class="alert alert-danger" role="alert"><strong>Warning!</strong> You have made changes since the last reload and this may not be the latest version!</div>');
    }
  }, 60000);

  $("body")
    .on("change", ".task_length", function() {
      $(this).removeClass("length_red length_green length_yellow length_black");
      $(this).addClass($(this).find(":selected").attr("class"));

      let node = opl.fancytree("getActiveNode");

      node.data.time_left = $(this).find(":selected").val();

      calcDueDate($(this));
    })
    .on("change", ".due_date", function() {
      let node = opl.fancytree("getActiveNode");

      node.data.due_date = $(this).val();

      calcDueDate($(this));
    })
    .on("click", "#saveOPL", function() {
      // capture the OPL tree completely
      let opl_list = JSON.stringify(opl.fancytree("getTree").toDict(true));

      // send it over to the save PHP section
      $.post("/html/opl/ajax/actions.php?action=save", {opl: opl_list, user: opl_usr}, function(data) {
        $("body").append(data); // return a value based on what happened with save
      }).done(function() {
        // now, update tree based on new saved data
        updateOPLTree();
      });

      unsaved = false;
    })
    .on("keyup", "#findOPL", function() {
      opl.fancytree("getTree").filterNodes($(this).val());
    })
    .on("click", "#addOPLFolder", function() {
      let creationPoint = null;

      if(opl.fancytree("getActiveNode") !== null) {
        creationPoint = opl.fancytree("getActiveNode");
      } else {
        creationPoint = opl.fancytree("getRootNode");
      }

      creationPoint.editCreateNode("child", {
        title: "New Folder...",
        folder: true,
        creation_date: new Date().toLocaleString(),
        time_left: '???',
        key: generateUniqueKey()
      });
    })
    .on("click", "#addOPLTask, .add_subtask", function() {
      if(!disabled) opl.fancytree("getActiveNode").editCreateNode("child", {
        title: "New Task...",
        creation_date: new Date().toLocaleString(),
        time_left: '???',
        key: generateUniqueKey()
      });
    })
    .on("click", "#completeOPLNodes", function() {
      var tree = opl.fancytree("getTree"), // get the tree
        selected = tree.getSelectedNodes(true); // define what is selected

      $.map(selected, function (node) {
        if(node !== null) {
          var parent = node.parent;

          if(parent) {
            parent.fixSelection3FromEndNodes();
          }

          node.remove();
        }
      });

      // re-render the tree deeply so that we can recalculate the line item numbers
      opl.fancytree("getRootNode").render(true,true);

      // hide the remove items button, there are no items to remove now
      $(this).hide();
    })
    .on("change", "#user_id", function() {
      // we're changing the user, time to update the OPL user for the global scope
      opl_usr = $(this).find(":selected").val();

      updateOPLTree();
    })
    .on("click", ".view_history", function() {
      let history_id = $(this).attr('id');
      let history_text = $(this).parent().parent().find("td:nth-child(2)").text();

      opl.fancytree('getTree').reload({url: '/html/opl/ajax/actions.php?action=viewOPLHistorical&id=' + history_id + "&user_id=" + opl_usr});

      if($(this).attr('id') !== 'live') {
        $(".opl_action").prop("disabled", true);
        disabled = true;
        $("#viewing").text(history_text);
      } else {
        $(".opl_action").prop("disabled", false);
        disabled = false;
        $("#viewing").text("");
      }
    })
    .on("click", ".complete_task", function() {
      if(!disabled) {
        opl.fancytree("getActiveNode").remove();

        // re-render the tree deeply so that we can recalculate the line item numbers
        opl.fancytree("getRootNode").render(true,true);
      }
    })
    .on("click", ".view_task_info", function() {
      let unique_id = $(this).attr("data-uid");
      let indexHeir = $(this).attr("data-indexHeir");
      let title = $(this).attr("data-title");

      $.post("/html/opl/ajax/row_information.php", {unique_id: unique_id, user_id: opl_usr, indexHeir: indexHeir, title: title}, function(data) {
        $("#modalOPLInfo").html(data).modal('show');
      });
    })
    .on("keyup", "#oplTaskNewNotes", function(e) {
      // this handles auto-display of the save button on the modal popup
      let button = $("#oplTaskInfoSave");

      // keycode chart: https://www.cambiaresearch.com/articles/15/javascript-char-codes-key-codes
      if((e.which >= 48 && e.which <= 90) || (e.which >= 96 && e.which <= 111) || (e.which >= 186 && e.which <= 222) || e.which === 32) {
        if(button.is(":visible") && $(this).val().length === 0) {
          button.hide();
        } else {
          button.show();
        }
      } else {
        if(button.is(":visible") && $(this).val().length === 0) {
            button.hide();
        }
      }
    })
    .on("click", "#oplTaskInfoSave", function() {
      let unique_id = $(this).attr("data-unique-id");
      let note = $("#oplTaskNewNotes").val();

      $.post("/html/opl/ajax/actions.php?action=saveOPLRowInfo", {unique_id: unique_id, note: note, user_id: opl_usr}, function(data) {
        $("body").append(data);
      });

      unsaved = false;
    })
    .on("click", "#oplRefresh", function() {
      updateOPLTree();
    })
  ;

  $(function() {
    opl_usr = $("#user_id").find(":selected").val(); // set the user ID

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
        } else {
          $("#completeOPLNodes").hide();
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
      source: { url: "/html/opl/ajax/actions.php?action=getOPL&user_id=" + opl_usr},
      extensions: ["edit", "dnd", "table", "gridnav", "filter"],
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
        edit: function(event, data) {
          data.input.select();
        },
        close: function(event, data) {
          if( data.save && data.isNew ){
            // Quick-enter: add new nodes until we hit [enter] on an empty title
            $("#tree").trigger("nodeCommand", {cmd: "addSibling"});
          }
        }
      },
      table: {
        indentation: 20,
        nodeColumnIdx: 2,
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
        $tdList.eq(1).text(node.getIndexHier());
        // (Index #2 is rendered by fancytree)

        // (Index #3 is the actions column)
        $tdList.eq(3).find(".view_task_info").attr("data-uid", node.key).attr("data-indexHeir", node.getIndexHier()).attr("data-title", node.title);

        // (Index #4 is the creation date of that element)
        $tdList.eq(4).text(node.data.creation_date);

        // (Index #5 is the time left select box)
        $tdList.eq(5).find("select").val(node.data.time_left);

        calcDueDate($tdList.eq(5).find("select"));

        // (Index #6 is the due date text box)
        $tdList.eq(6).find("input").val(node.data.due_date);

        calcDueDate($tdList.eq(6).find("input"));

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
          node.editCreateNode("child", "");
          break;
        case "addSibling":
          node.editCreateNode("after", "");
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
          break;
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
        {title: "----"},
        {title: "New task <kbd>[Ctrl+N]</kbd>", cmd: "addSibling", uiIcon: "ui-icon-plus" },
        {title: "New sub-task <kbd>[Ctrl+Shift+N]</kbd>", cmd: "addChild", uiIcon: "ui-icon-arrowreturn-1-e" },
        {title: "----"},
        {title: "Complete Task <kbd>[Ctrl-M]</kbd>",  uiIcon: "ui-icon-check"}
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

    opl_history.fancytree({
      extensions: ["table"],
      icon: false,
      table: {
        nodeColumnIdx: 1     // render the node title into the 2nd column
      },
      source: {
        url: "/html/opl/ajax/actions.php?action=getOPLHistory&user_id=" + opl_usr
      },
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
  });
</script>