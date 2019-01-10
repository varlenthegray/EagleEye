// start of OPL functions
$(".due_date").change(function() {
  let node = opl.fancytree("getActiveNode");

  node.data.due_date = $(this).val();

  calcDueDate($(this));
  sendOPLEdit();
});

$("#user_id").change(function() {
  // we're changing the user, time to update the OPL user for the global scope
  opl_usr = $(this).find(":selected").val();

  updateOPLTree();

  socket.emit("getOPLEditingStatus");
});

$("#oplTaskNewNotes").keyup(function(e) {
  // this handles auto-display of the save button on the modal popup
  let button = $("#oplTaskInfoSave");

  // keycode chart: https://www.cambiaresearch.com/articles/15/javascript-char-codes-key-codes
  if ((e.which >= 48 && e.which <= 90) || (e.which >= 96 && e.which <= 111) || (e.which >= 186 && e.which <= 222) || e.which === 32) {
    if (button.is(":visible") && $(this).val().length === 0) {
      button.hide();
    } else {
      button.show();
    }
  } else {
    if (button.is(":visible") && $(this).val().length === 0) {
      button.hide();
    }
  }
});

$("#oplTaskInfoSave").click(function() {
  let unique_id = $(this).attr("data-unique-id");
  let note = $("#oplTaskNewNotes").val();

  $.post("/html/opl/ajax/actions.php?action=saveOPLRowInfo", {
    unique_id: unique_id,
    note: note,
    user_id: opl_usr
  }, function(data) {
    $("body").append(data);
  });

  opl.fancytree("getTree").getNodeByKey(unique_id).data.hasInfo = true; // update that specific key to now have notes (on the table)
  $(opl.fancytree("getTree").getNodeByKey(unique_id).tr).find(">td").eq(2).find(".view_task_info").removeClass("no-info").addClass("has-info"); // set the icon as active

  sendOPLEdit();

  unsaved = false;
});

$("#OPLForceOverride").click(function() {
  sendOPLEdit();
});


// end of OPL functions
var main = {

  // -- Navigation --
  navTimeCardInit: function() {
    $("body").on("click", "#nav_timecard", function() {
      var start = Math.round(new Date().getTime() / 1000);
      var end = Math.round(new Date().getTime() / 1000);

      window.open("/print/timecard.php?start_date=" + start + "&end_date=" + end + "&employee=1", "_blank");
    });
  },
  // -- End Navigation --

  // -- Clicking an SO to view it
  clickingSoInit: function() {
    $(".view_so_info").click(function(e) {
      e.stopPropagation();

      $("#modalGlobal").modal('hide');

      var id = $(this).attr("id");
      $("#global_search").val(id).trigger("keyup");
    });
  },
  // -- End clicking an SO to view it

  // -- Dashboard --
  dashBoardInit: function() {
    $("body")
      .on("change", "#viewing_queue", function() {
        globalFunctions.updateOpQueue();
      })

      .on("click", ".start-operation", function(e) {
        e.stopPropagation();

        opFull = $(this).closest('tr').find('td').eq(4).html();
        op_id = $(this).attr("id");

        if (opFull === 'NB00: Non-Billable' || opFull === 'TF00: On The Fly') {
          $.post("/ondemand/op_actions.php?action=get_start_info", {
            opID: op_id,
            op: opFull
          }, function(data) {
            $("#modalGlobal").html(data);
          }).done(function() {
            $("#modalGlobal").modal();
          }).fail(function() { // if we're receiving a header error
            $("body").append(data); // echo an error and log it
          });
        } else {
          $.post("/ondemand/op_actions.php?action=start_operation", {
            operation: opFull,
            id: op_id
          }, function(data) {
            $('body').append(data);

            socket.emit("updateQueue");

            $.post("/html/view_notes.php", {
              queueID: op_id
            }, function(data) {
              $("#modalGlobal").html(data).modal("show");
            });
          });
        }

        unsaved = false;
      })
      .on("click", "#start_job", function() {
        var other_notes_field = $("#other_notes_field").val(); // non-billable "other" section
        var notes_field = $("#notes_field").val(); // Cabinet Vision task or anything with JUST a notes field

        if ($("#other_subtask").is(":checked")) { // if this is a subtask "other" section then we have to verify the notes
          if (other_notes_field.length >= 3) { // and the length of notes is greater than 3
            $.post("/ondemand/op_actions.php?action=start_operation", {
              id: op_id,
              operation: opFull,
              subtask: "Other",
              notes: other_notes_field
            }, function(data) {
              $("body").append(data);

              socket.emit("updateQueue");

              $("#modalGlobal").modal('hide');
            });
          } else { // otherwise, the notes is less than 3 and they need to enter notes in
            displayToast("error", "Enter notes in before continuing (more than 3 characters).", "Notes Required");
          }
        } else { // this is an OTF (for now?)
          var subtask = $('input[name=nonBillableTask]:checked').val();
          var otf_so_num = $("#otf_so_num").val();
          var otf_room = $("#otf_room").val();
          var otf_op = $("#otf_operation").val();
          var otf_notes = $("#otf_notes").val();
          var otf_iteration = $("#otf_iteration").val();

          $.post("/ondemand/op_actions.php?action=start_operation", {
            id: op_id,
            operation: opFull,
            subtask: subtask,
            notes: notes_field,
            otf_so_num: otf_so_num,
            otf_room: otf_room,
            otf_op: otf_op,
            otf_notes: otf_notes,
            otf_iteration: otf_iteration
          }, function(data) {
            socket.emit("updateQueue");

            $('body').append(data);

            $("#modalGlobal").modal('hide');
          });
        }

        unsaved = false;
      })

      .on("change", "input[name='nonBillableTask']", function() {
        if ($(this).prop("id") === 'other_subtask') {
          $("#other_notes_section").show();
          $("#other_notes_field").focus();
        } else {
          $("#other_notes_section").hide();
        }
      })

      .on("click", ".pause-operation", function(e) {
        e.stopPropagation();

        op_id = $(this).attr("id");

        $.post("/ondemand/op_actions.php?action=get_pause_info", {
          opID: op_id
        }, function(data) {
          $("#modalGlobal").html(data).modal();
        });
      })

      .on("click", "#pause_op", function() {
        $.post("/ondemand/op_actions.php?action=pause_operation", {
          opID: op_id,
          notes: $("#notes").val(),
          qty: $("#qtyCompleted").val()
        }, function(data) {
          socket.emit("updateQueue");

          $("body").append(data);
          $("#modalGlobal").modal('hide');
        });
      })

      .on("click", ".complete-operation", function(e) {
        e.stopPropagation();

        op_id = $(this).attr("id");

        $.post("/ondemand/op_actions.php?action=get_stop_info", {
          opID: op_id
        }, function(data) {
          $("#modalGlobal").html(data).modal();
        });

        unsaved = false;
      })

      .on("change", "#rework_reqd", function() {
        if ($(this).is(":checked")) {
          $(".rework_reason_group").show();
          $("#complete_op").html("Send Back");
        } else {
          $(".rework_reason_group").hide();
          $("#complete_op").html("Complete");
        }
      })

      .on("click", "#complete_op", function() {
        var notes = $("#notes").val();
        var qty_compl = $("#qtyCompleted").val();
        var rework = $("#rework_reqd").is(":checked");
        var rw_reason = $("#rework_reason").find(":selected").val();

        // formdata required because now we're attaching files
        var formData = new FormData();
        formData.append('opID', op_id);
        formData.append('opnum', opFull);
        formData.append('notes', notes);
        formData.append('qty', qty_compl);
        formData.append('rework_reqd', rework);
        formData.append('rework_reason', rw_reason);
        formData.append('attachment', $("input[type='file']")[0].files[0]);

        $.ajax({
          url: "/ondemand/op_actions.php?action=complete_operation",
          //url: "/admin/test.php",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data) {
            $('body').append(data);
            $("#modalGlobal").modal('hide');

            socket.emit("updateQueue");
          }
        });

        unsaved = false;
      })

      .on("click", ".op-notes", function(e) {
        e.stopPropagation();

        $.post("/html/view_notes.php", {
          queueID: $(this).attr("id")
        }, function(data) {
          $("#modalGlobal").html(data).modal("show");
        });
      });
  },
  // -- End Dashboard --

  // -- Workcenter --
  workCenterInit: function() {
    $(".wc-view-queue-so").click(function() {
      var id = $(this).attr("id");
      $("#global_search").val(id).trigger("keyup");
    });
  },
  // -- End Workcenter --

  // -- VIN Page --
  vinInit: function() {
    $("#so_num").blur(function() {
      vin_sonum = $(this).val();

      $.post("/ondemand/livesearch/build_a_vin.php?search=room&so_num=" + vin_sonum, function(data) {
        $("#room").html(data);
      });
    });

    $("#room").blur(function() {
      $.post("/ondemand/livesearch/build_a_vin.php?search=iteration&so_num=" + vin_sonum + "&room=" + $("#room option:selected").val(), function(data) {
        $("#iteration").html(data);
      });
    });

    $(".print-sample").click(function() {
      var room_id = $(this).attr("id");

      globalFunctions.calcVin(room_id);

      var formInfo = $("#room_edit_" + room_id).serialize();

      $.post("/ondemand/room_actions.php?action=update_room&" + formInfo + "&roomid=" + room_id, function(data) {
        $('body').append(data);
      }).done(function() {
        setTimeout(function() {
          window.open("/print/sample.php?room_id=" + room_id);
        }, 500);
      });

      unsaved = false;
    });

    $(".vin_code_calc").keydown(function() {
      globalFunctions.calcVin(active_room_id);
    });

    $(".recalcVin").change(function() {
      globalFunctions.calcVin(active_room_id);
    });

    $("#ext_carcass_same").change(function() {
      if ($(this).is(":checked")) {
        $(".ext_finish_block").hide();
      } else {
        $(".ext_finish_block").show();
      }
    });

    $("#int_carcass_same").change(function() {
      if ($(this).is(":checked")) {
        $(".int_finish_block").hide();
      } else {
        $(".int_finish_block").show();
      }
    });
  },
  // -- End VIN Page --

  // -- Task Page --
  taskPageInit: function() {
    $(".display-task-info").click(function() {
      $.post("/ondemand/admin/tasks.php?action=get_task_info", {
        task_id: $(this).attr("id")
      }, function(data) {
        $("#modalTaskInfo").html(data);
      }).done(function() {
        $("#modalTaskInfo").modal();
      }).fail(function(data) { // if we're receiving a header error
        $("body").append(data); // echo an error and log it
      });
    });

    $("#update_task_btn").click(function() {
      var form_info = $("#task_details").serialize();
      var task_id = $(this).data("taskid");
      var s_text_1 = $("#split-text-1").val();
      var s_text_2 = $("#split-text-2").val();

      $.post("/ondemand/admin/tasks.php?action=update_task", {
        task_id: task_id,
        s_text_1: s_text_1,
        s_text_2: s_text_2,
        form: form_info
      }, function(data) {
        $("body").append(data);
        $("#modalTaskInfo").modal('hide');

        unsaved = false;
      });
    });

    $("#split_task_btn").click(function() {
      $(".task_hide").toggle(100);
      $("#split_body").toggle(250);

      setTimeout(function() {
        if ($("#split_body").is(":visible")) {
          $("#split_task_enabled").val("1");
        } else {
          $("#split_task_enabled").val("0");
        }
      }, 250);
    });

    $("#create_op_btn").click(function() {
      var form_info = $("#task_details").serialize();
      var task_id = $(this).data("taskid");

      $.post("/ondemand/admin/tasks.php?action=create_operation&" + form_info, {
        task_id: task_id
      }, function(data) {
        $("body").append(data);
        $("#modalTaskInfo").modal('hide');

        unsaved = false;
      });
    });
  },
  // -- End Task Page --

  // -- Room Page --
  roomPageInit: function() {
    $("#display_log").change(function() {
      if ($(this).is(":checked")) {
        $(".room_note_log").show();
      } else {
        $(".room_note_log").hide();
      }
    });
  },
  // -- End Room Page --

  // -- Sales List Page --
  salesListInit: function() {
    $("#job_status_lost").change(function() {
      if ($(this).is(":checked")) {
        $(".room_lost").show();
      } else {
        $(".room_lost").hide();
      }
    });

    $("#job_status_quote").change(function() {
      if ($(this).is(":checked")) {
        $(".room_quote").show();
      } else {
        $(".room_quote").hide();
      }
    });

    $("#job_status_job").change(function() {
      if ($(this).is(":checked")) {
        $(".room_job").show();
      } else {
        $(".room_job").hide();
      }
    });

    $("#job_status_completed").change(function() {
      if ($(this).is(":checked")) {
        $(".room_completed").show();
      } else {
        $(".room_completed").hide();
      }
    });

    $(".hide_dealer").change(function() {
      var dealer_id = $(this).data("dealer-id");

      if ($(this).is(":checked")) {
        $(".dealer_" + dealer_id).show();
      } else {
        $(".dealer_" + dealer_id).hide();
      }
    });

    $(".sales_list_visible").click(function(e) {
      e.stopPropagation();

      var hide = $(this).data("identifier");

      $("." + hide).hide();

      $.post("/ondemand/display_actions.php?action=hide_sales_list_id&id=" + hide);
    });

    $(".sales_list_hidden").click(function(e) {
      e.stopPropagation();

      var show = $(this).data("identifier");
      $(this).removeClass('btn-primary-outline sales_list_hidden').addClass('btn-primary sales_list_visible').children('i').removeClass('zmdi-eye').addClass('zmdi-eye-off');

      $.post("/ondemand/display_actions.php?action=show_sales_list_id&id=" + show);
    });
  },
  // -- End Sales List Page --

  // -- Feedback --
  feedbackSubmitInit: function() {
    $("#feedback-submit").click(function() {
      var description = $("#feedback-text").val();
      var feedback_to = $("#feedback_to").val();
      var priority = $("#feedback_priority").val();

      $.post("/ondemand/admin/tasks.php?action=submit_feedback", {
        description: description,
        assignee: feedback_to,
        priority: priority
      }, function(data) {
        $("body").append(data);
        $("#feedback-page").modal('hide');
        unsaved = false;
        $("#feedback-text").val("");
      });
    });
  },
  // -- End Feedback --

  // -- Notifications --
  notificationInit: function() {
    $("#notification_list").click(function() {
      $.post("/ondemand/alerts.php?action=viewed_alerts");
    });
  },
  // -- End Notifications --

  // -- Employees --
  employeeClockOutInit: function() {
    $("body")
      .on("click", ".clock_out", function(e) {
        var id = $(this).data("id");

        e.stopPropagation();

        $.post("/ondemand/account_actions.php?action=clock_out", {
          'clockout_id': id
        }, function(data) {
          $("body").append(data);
        });
      });
  },
  // -- End Employees --

  // -- Add so --
  addSoInit: function() {
    $(".nav_add_so").click(function() {
      $.post('/html/new_customer.php', function(data) {
        $("#modalGlobal").html(data).modal('show');
      });
    });

    $("input[name='cu_type']").change(function() {
      var add_rc = $("#add_retail_customer");
      var add_dist = $("#add_distributor_cc");

      switch ($(this).val()) {
        case 'retail':
          add_rc.show();
          add_dist.hide();

          break;
        case 'distribution':
          add_rc.hide();
          add_dist.show();

          break;
        case 'cutting':
          add_rc.hide();
          add_dist.show();

          break;
        default:
          break;
      }
    });

    $("#submit_new_customer").click(function() {
      var cuData = $("#add_retail_customer").serialize();

      $.post("/ondemand/so_actions.php?action=add_customer", {
        so_num: $("#so_num").val(),
        cu_data: cuData
      }, function(data) {
        $("body").append(data);

        $("#modalGlobal").modal('hide');
      });

      unsaved = false;
    });

    $("#secondary_addr_chk").change(function() {
      $(".secondary_addr_disp").toggle();
    });

    $("#billing_addr_chk").change(function() {
      $(".billing_info_disp").toggle();
    });

    $("#contractor_chk").change(function() {
      $(".contractor_disp").toggle();
    });
  },
  // -- End os Add so --

  // -- Add Project --
  addProjectInit: function() {
    $("#nav_add_project").click(function() {
      $.post('/html/add_project.php?display=dealer', function(data) {
        $("#modalGlobal").html(data).modal('show');
      });
    });

    $("#contractor_chk").change(function() {
      $(".contractor_disp").toggle();
    });

    $("#submit_new_project").click(function() {
      var cuData;

      cuData = $("#add_project").serialize();

      $.post("/ondemand/so_actions.php?action=add_customer", {
        so_num: $("#so_num").val(),
        cu_data: cuData
      }, function(data) {
        $("body").append(data);

        $("#modalGlobal").modal('hide');
      });

      unsaved = false;
    });
  },
  // -- End Add Project --

  // -- View Contacts --
  viewContactsInit: function() {
    $(".nav_add_contact").click(function() {
      var defaultType = $(this).attr('data-default');

      $.post('/html/modals/add_contact.php?default=' + defaultType, function(data) {
        $("#modalGlobal").html(data).modal('show');
      });
    });

    $("body").on("click", "#submit_new_contact", function() {
      var contactData = $("#contact_form").serialize();

      $.post('/ondemand/contact_actions.php?action=save_contact&' + contactData, function(data) {
        $("body").append(data);

        $("#modalGlobal").modal('hide');
      });

      unsaved = false;
    })

    $("#contact_type").change(function() {
      if ($(this).find("option:selected").text() === 'Dealer') {
        $("#dealer_code").show();
      } else {
        $("#dealer_code").hide();
      }
    });

    $("#update_contact").click(function() {
      var contactData = $("#contact_form").serialize();

      $.post('/ondemand/contact_actions.php?action=update_contact&' + contactData, function(data) {
        $("body").append(data);

        $("#modalGlobal").modal('hide');
      });

      unsaved = false;
    });

    $("#delete_contact").click(function() {
      var contact_name = $(this).attr('data-name');
      var contact_id = $(this).attr('data-contact-id');

      $.confirm({
        title: "Delete contact " + contact_name,
        content: "You are about to <strong>permanently</strong> delete " + contact_name + ". Are you sure you would like to do this?",
        buttons: {
          yes: function() {
            $.post("/ondemand/contact_actions.php?action=delete_contact", {
              id: contact_id
            }, function(data) {
              $("body").append(data);
            });
          },
          no: function() {}
        }
      });
    });

    $(".get_customer_info").click(function(e) {
      $.post("/html/modals/add_contact.php?action=edit", {
        id: $(this).attr('data-view-id')
      }, function(data) {
        $("#modalGlobal").html(data).modal('show');
      });

      // stops it from posting to the URL in the browser
      e.preventDefault().stopPropagation();
    });

    $(".nav_add_company").click(function() {
      $.post("/html/modals/add_company.php", function(data) {
        $("#modalGlobal").html(data).modal('show');
      });
    });
  },
  // -- End View Contacts --

  // -- Veiw Break --
  viewBreakInit: function() {
    $(".nav_break").click(function() {
      var thisText = $(this).find('span');

      if ($(this).attr('id') === '201') {
        $.post("/ondemand/account_actions.php?action=start_break", {
          id: 201,
          operation: 'Break'
        }, function(data) {
          $('body').append(data);

          socket.emit("updateQueue");

          $.post("/ondemand/account_actions.php?action=get_break_btn", function(data) {
            var result = JSON.parse(data);
            $(".nav_break").attr('id', result.id);

            thisText.html("Stop Break");
          });

          $(this).addClass('btn-success');
        });
      } else {
        $.post("/ondemand/account_actions.php?action=get_break_btn", function(data) {
          var result = JSON.parse(data);

          $(this).removeClass('btn-success');

          $.post("/ondemand/op_actions.php?action=complete_operation", {
            'opID': result.id,
            'opnum': 'NB00'
          }, function(data) {
            $('body').append(data);

            socket.emit("updateQueue");

            $(".nav_break").attr('id', '201');

            thisText.html("Start Break");
          });
        });
      }
    });

  },
}
// -- End View Break --

$(".post_to_cal").click(function(e) {
  e.stopPropagation();
});


setInterval(function() { // stops the auto-logout
  $.post("/ondemand/session_continue.php");
}, 600000);

globalFunctions.updateBreakButton(); // get the break button initial state