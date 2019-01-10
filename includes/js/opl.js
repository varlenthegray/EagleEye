/*global sendOPLEdit*//*global jQuery*/

jQuery.expr.filters.offscreen = function(el) {
  var rect = el.getBoundingClientRect();
  return ((rect.x + rect.width) < 0 || (rect.y + rect.height) < 0 || (rect.x > window.innerWidth || rect.y > window.innerHeight));
};



  var oplFunction = {
    completeTaskInit: function() {
      $("body")
        .on("click", ".complete_task", function() {
          let node = opl.fancytree("getActiveNode");

          if (!disabled) {
            $.confirm({
              title: "Are you sure you want to complete this task?",
              content: "You are about to remove task " + node.getIndexHier() + ": " + node.title + ". Are you sure?",
              type: 'red',
              buttons: {
                yes: function() {
                  node.remove();

                  // re-render the tree deeply so that we can recalculate the line item numbers
                  opl.fancytree("getRootNode").render(true, true);

                  sendOPLEdit();
                },
                no: function() {}
              }
            });
          }
        });
    },
    addOplTaskInit: function() {
      $("body")
        .on("click", "#addOPLTask, .add_subtask", function() {
          if (!disabled) opl.fancytree("getActiveNode").editCreateNode("child", {
            title: "New Task...",
            creation_date: new Date().toLocaleString(),
            time_left: '???',
            key: generateUniqueKey()
          });

          sendOPLEdit();
        });
    },
    taskInfoInit: function() {
      $("body")
        .on("click", ".view_task_info", function() {
          let unique_id = $(this).attr("data-uid");
          let indexHeir = $(this).attr("data-indexHeir");
          let title = $(this).attr("data-title");

          $.post("/html/opl/ajax/row_information.php", {
            unique_id: unique_id,
            user_id: opl_usr,
            indexHeir: indexHeir,
            title: title
          }, function(data) {
            $("#modalOPLInfo").html(data).modal('show');
          });
        });
    },
    completeOplNodesInit: function() {
      $("#completeOPLNodes").click(function() {
        var tree = opl.fancytree("getTree"), // get the tree
          selected = tree.getSelectedNodes(true); // define what is selected, true allows flag for selection type 3

        // part of plural/singular words
        let plural = "";
        let multiple = "this";

        // setting up the plural and singular versions of the sentence
        if (selected.length > 1) {
          plural = "s";
          multiple = "these";
        } else {
          plural = "";
          multiple = "this";
        }

        $.confirm({ // a confirmation box to ensure they are intending to complete tasks
          title: "Are you sure you want to complete " + multiple + " task" + plural + "?",
          content: "You are about to remove " + selected.length + " task" + plural + ". Are you sure?",
          type: 'red',
          buttons: {
            yes: function() {
              $.map(selected, function(node) { // get all selected notes
                var parent = node.parent; // set the parent node

                if (parent) { // if there is a parent, we're gonna fix the selection count
                  parent.fixSelection3FromEndNodes();
                }

                node.remove(); // remove the node

                sendOPLEdit();
              });

              // re-render the tree deeply so that we can recalculate the line item numbers
              opl.fancytree("getRootNode").render(true, true);

              // hide the remove items button, there are no items to remove now
              $(this).hide();
            },
            no: function() {} // we're not doing anything
          }
        });
      });
    },
    saveOplInit: function() {
      $("#saveOPL").click(function() {
        let opl_mini = getMiniTree(opl);

        // capture the OPL tree completely
        let opl_list = JSON.stringify(opl_mini);

        // send it over to the save PHP section
        $.post("/html/opl/ajax/actions.php?action=save", {
          opl: opl_list,
          user: opl_usr
        }, function(data) {
          $("body").append(data); // return a value based on what happened with save
        }).done(function() {
          socket.emit("oplSaved");
          socket.emit("getOPLEditingStatus");
        });

        unsaved = false;
      });
    },
    refreshOplInit: function() {
      $("#oplRefresh").click(function() {
        updateOPLTree();
        socket.emit("getOPLEditingStatus");
      });
    },
    addOplFolderInit: function() {
      $("#addOPLFolder").click(function() {
        let creationPoint = null;

        if (opl.fancytree("getActiveNode") !== null) {
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

        sendOPLEdit();
      });

    },
    clearCheckedInit: function() {
      $("#oplClearSelected").click(function() {
        opl.fancytree("getTree").visit(function(node) {
          node.setSelected(false);
        });

        return false;
      });
    },
    oplPrintInit: function() {
      $("#oplPrint").click(function() {
        if (opl.fancytree("getTree").getSelectedNodes().length > 1) {
          opl.fancytree("getTree").filterNodes(function(node) {
            return node.isSelected();
          });

          window.print();

          opl.fancytree("getTree").clearFilter();
        } else {
          window.print();
        }
      });
    },
    oplPriorityInit: function() {
      $("body")
        .on("change", ".OPLPriority", function() {
          if ($(this).val().length > 0) {
            $(this).addClass("white_black");
          } else {
            $(this).removeClass("white_black");
          }

          let node = opl.fancytree("getActiveNode");

          node.data.priority = $(this).val();
        });
    },
    taskLengthInit: function() {
      $("body")
        .on("change", ".task_length", function() {
          $(this).removeClass("length_red length_green length_yellow length_black");
          $(this).addClass($(this).find(":selected").attr("class"));

          let node = opl.fancytree("getActiveNode");

          node.data.time_left = $(this).find(":selected").val();

          calcDueDate($(this));
          sendOPLEdit();
        });
    },
    findOplInit: function() {
      $("#findOPL").keyup(function() {
        opl.fancytree("getTree").filterNodes($(this).val());
      });
    },
    viewHistoryInit: function() {
      $("body")
        .on("click", ".view_history", function() {
          let history_id = $(this).attr('id');
          let history_text = $(this).parent().parent().find("td:nth-child(2)").text();

          opl.fancytree('getTree').reload({
            url: '/html/opl/ajax/actions.php?action=viewOPLHistorical&id=' + history_id
          });

          if ($(this).attr('id') !== 'live') {
            $(".opl_action").prop("disabled", true);
            disabled = true;
            $("#viewing").text(history_text);
          } else {
            $(".opl_action").prop("disabled", false);
            disabled = false;
            $("#viewing").text("");
          }
        });
    },
    oplCheckoutInit: function() {
      $("body")
        .on("click", "#OPLCheckout", function() {
          sendOPLEdit();
        });
    }
  };




