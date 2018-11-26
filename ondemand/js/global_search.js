/*jshint strict: false*/
/*global productTypeSwitch*//*global calcVin*//*global displayToast*//*global checkTransition*//*global unsaved:true*//*global tinysort*//*global backFromSearch*//*global clearIntervals*//*global scrollPosition:true*/

// Goal: to ensure the loading of Global Search input field
var timer;
var active_so_num;
var active_room_id;
var thisClick;

function toggleDisplay(x) {
  $(".room_line").removeClass("active_room_line");
  $("#" + active_room_id).addClass("active_room_line");

  $("[id^=tr_attachments_]").finish().hide(250);
  $("[id^=div_attachments_]").finish().hide(100);
  $("[id^=tr_edit_so_]").finish().hide(250);
  $("[id^=div_edit_so_]").finish().hide(100);

  $(".tr_room_actions").hide(300).find('div').slideUp(150).html('');
  $(".add_room").hide(300).find('div').slideUp(150).html('');
}

function scrollLocation(container) {
  setTimeout(function() {
    $(window).scrollTo($(container), 800, {offset: -125});
  }, 300);
}

// TODO: Lock down separeate sections based on bouncer results
$("body")
  .on("keyup", "#global_search", function() {
    checkTransition(function() {
      clearIntervals();

      var searchDisplay = $("#search_display");
      var mainDisplay = $("#main_display");
      var input = $("#global_search");
      var searchTable = $("#search_results_table");
      var loadingResults = '<tr><td colspan="7" class="text-md-center"><span id="global_search_status"><i class="fa fa-3x fa-spin fa-spinner" style="width: auto;margin-right: 10px;"></i></span></td></tr>';
      var searchEmpty = '<tr><td colspan="7">No results found.</td></tr>';

      mainDisplay.attr("data-search", "true");

      searchTable.html(loadingResults);

      if(input.val().length >= 3) {
        scrollPosition = $(window).scrollTop();

        mainDisplay.hide();
        searchDisplay.show();

        clearTimeout(timer);

        timer = setTimeout(function () {
          if (input.val().length >= 1) {
            $.post("/html/search/so_list.php", {find: input.val()}, function (data) {
              searchTable.html(data);
              $("#search_results_global_table").trigger("update");

              if (data !== '') {
                $('[data-toggle="tooltip"]').tooltip(); // enable tooltips

                // setup date picker
                $(".delivery_date").datepicker({
                  autoclose: true,
                  todayHighlight: true
                });
              } else {
                searchTable.html(searchEmpty);
              }
            });
          } else {
            mainDisplay.show();
            searchDisplay.hide();
          }
        }, 100);
      } else {
        mainDisplay.show();
        searchDisplay.hide();
      }
    });
  })
  .on("keyup keypress", "#global_search", function(e) {
    if(e.keyCode === 13) {
      e.preventDefault();

      $("#global_search").trigger("keyup");

      return false;
    }
  })
  .on("click", "#global_search_button", function() {
    $("#global_search").trigger("keyup");
  })

  .on("click", "#btn_search_to_main", function() {
    checkTransition(function() {
      backFromSearch();
    });
  })

  .on("click", ".wc-edit-queue", function() {
    $("#global_search").val($(this).attr("id")).trigger("keyup");
  })

  .on("click", "[id^=edit_so_]", function(e) {
    thisClick = this;

    e.stopPropagation();

    checkTransition(function() {
      active_so_num = $(thisClick).attr("id").replace('edit_so_', '');

      toggleDisplay();

      $("#tr_edit_so_" + active_so_num).show();
      $("#div_edit_so_" + active_so_num).slideDown(250);
    });

    scrollLocation("#show_room_" + active_so_num);
  })
  .on("click", "[id^=show_room_]", function() {
    thisClick = this;

    checkTransition(function() {
      active_so_num = $(thisClick).attr("id").replace('show_room_', '');

      toggleDisplay();

      $("#tr_room_" + active_so_num).show();
      $("#div_room_" + active_so_num).slideDown(250);
    });
  })
  .on("click", ".edit_room", function(e) {
    active_room_id = $(this).attr("id");

    var tr = $(this).closest('tr');
    var row = searchTable.row( tr );

    if ( row.child.isShown() ) {
      // This row is already open - close it
      row.child('').hide();
      tr.removeClass('shown');
    } else {
      // Open this row
      // $.get("/html/pricing/index.php", {room_id: active_room_id}, function(data) {
      $.get("/html/search/ajax/edit_room.php", {room_id: active_room_id}, function(data) {
        row.child(data).show();
        tr.addClass('shown');
      });
    }

    /*thisClick = this;

    e.stopPropagation();

    checkTransition(function() {
      active_room_id = $(thisClick).attr("id");
      active_so_num = $(thisClick).data("sonum");

      // toggleDisplay();

      // $.post("/html/pricing/index.php?room_id=" + active_room_id, function(data) {
      //   $("#" + active_room_id + ".tr_room_actions").show().find('div').html(data).slideDown(150);
      // });
      //
      // scrollLocation("#" + active_room_id + ".tr_room_actions");

      $.get("/html/pricing/index.php", {room_id: active_room_id}, function(data) {
        $("#searchModal .modal-body").html(data).modal('show');
      });
    });*/
  })
  .on("click", ".activate_op", function() {
    var opid = $(this).data("opid");
    var roomid = $(this).data("roomid");
    var soid = $(this).data("soid");
    var opnum = $(this).parent().data("opnum");
    var bracket = $(this).closest('ul').data("bracket");
    var info;
    var deactivate = '';

    if(String(opnum).slice(-2) !== '98') {
      deactivate = '<span class="pull-right cursor-hand text-md-center deactivate_op" data-opid="' + opid + '" data-roomid="' + roomid + '" data-soid="' + soid + '"> <i class="fa fa-arrow-circle-right" style="width: 18px;"></i></span>';
    }

    info = '<li class="active_ops_' + roomid + '" id="active_ops_' + roomid +'" data-opnum="' + opnum + '" data-opid="' + opid + '">';
    info += '<input type="radio" name="' + bracket + '" id="op_' + opid + '_room_' + roomid +'" value="' + opid + '">';
    info += '<label for="op_' + opid + '_room_' + roomid + '">' + $(this).parent().text().trim() + '</label>';
    info += deactivate;
    info += "</li>";

    $("#activeops_" + roomid + "_" + bracket).append(info);
    tinysort("ul#activeops_" + roomid + "_" + bracket + ">li",{data:'opnum'});

    $(this).parent().remove();
  })
  .on("click", ".deactivate_op", function() {
    var opid = $(this).data("opid");
    var roomid = $(this).data("roomid");
    var soid = $(this).data("soid");
    var opnum = $(this).parent().data("opnum");
    var bracket = $(this).closest('ul').data("bracket");
    var info;

    info = '<li class="inactive_ops_' + roomid + '" id="inactive_ops_' + roomid +'" data-opnum="' + opnum + '" data-opid="' + opid + '">';
    info += '<span class="pull-left cursor-hand text-md-center activate_op" data-opid="' + opid + '" data-roomid="' + roomid + '" data-soid="' + soid + '" style="height:18px;width:18px;"> <i class="fa fa-arrow-circle-left" style="margin:5px;"></i></span>';
    info += '<label for="op_' + opid + '_room_' + roomid + '">' + $(this).parent().text().trim() + '</label>';
    info += "</li>";

    $("#inactiveops_" + roomid + "_" + bracket).append(info);
    tinysort("ul#inactiveops_" + roomid + "_" + bracket + ">li",{data:'opnum'});

    $(this).parent().remove();
  })

  .on("click", ".edit_room_save", function(e) {
    e.stopPropagation();

    var thisClick = this;
    var val_array = {};

    $(thisClick).removeClass('edit_room_save');

    var edit_info = $("#room_edit_" + active_room_id).serialize();
    var active_ops = $(".active_ops_" + active_room_id).map(function() { return $(this).data("opid"); }).get();

    active_ops = JSON.stringify(active_ops);

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

    $.post("/ondemand/room_actions.php?action=update_room", {active_ops: active_ops, customVals: customVals, editInfo: edit_info}, function(data) {
      $('body').append(data);
    }).done(function() {
      $(thisClick).addClass('edit_room_save');
      $("#room_notes").val('');
    });

    unsaved = false;
  })
  .on("click", ".add_iteration", function(e) {
    thisClick = this;
    var next_iteration;

    e.stopPropagation();

    checkTransition(function() {
      active_room_id = $(thisClick).data('roomid');
      active_so_num = $(thisClick).data('sonum');

      var seqAjax;
      var iterationAjax;
      var header;

      if($(thisClick).data('addto') === 'sequence') {
        seqAjax = $.post("/ondemand/play_fetch.php?action=get_next_iteration&output=sequence&roomid=" + active_room_id, function(data) {
          next_iteration = data;
        });

        header = "Adding Sequence";
      } else {
        iterationAjax = $.post("/ondemand/play_fetch.php?action=get_next_iteration&output=iteration&roomid=" + active_room_id, function(data) {
          next_iteration = data;
        });

        header = "Adding Iteration";
      }

      $.when(seqAjax, iterationAjax).done(function() {
        toggleDisplay();

        $.post("/html/search/room_add_section.php?room_id=" + active_room_id, function(data) {
          $("#" + active_room_id + ".tr_room_actions").show().find('div').html(data).slideDown(150);

          $("#add_iteration_header_" + active_room_id).html(header);
          $("#edit_iteration_" + active_room_id).attr('value', next_iteration);
          $("#vin_iteration_" + active_room_id).val(next_iteration);
        });
      });
    });

    scrollLocation("#" + active_room_id + ".tr_room_actions");
  })
  .on("click", ".iteration_save", function(e) {
    e.stopPropagation();

    if($("input[name='room_name']").val() === '') {
      $.confirm({
        title: "Unable to Save",
        content: "Cannot save with no room name!",
        buttons: {
          ok: function() {}
        }
      });
    } else {
      var edit_info = $("#add_iteration").serialize();
      var active_ops = $(".active_ops").map(function() { return $(this).data("opid"); }).get();

      active_ops = JSON.stringify(active_ops);

      $.post("/ondemand/room_actions.php?action=create_room", {active_ops: active_ops, editInfo: edit_info}, function(data) {
        $('body').append(data);
      });

      unsaved = false;
    }
  })

  .on("click", ".save_so", function() {
    var so_info = $("#form_so_" + active_so_num).serialize();

    $.post('/ondemand/so_actions.php?action=save_so&' + so_info + '&so_num=' + active_so_num, function(data) {
      $("body").append(data);
    });

    let note = $("#inquiry").val();

    if(note !== '') {
      let d = new Date();
      let month = d.getMonth() + 1;
      let date = month + '/' + d.getDate() + '/' + d.getFullYear();

      let hours = d.getHours();
      let mins = d.getMinutes();
      let secs = d.getSeconds();
      let am_pm = null;

      if (hours < 12) {
        am_pm = 'AM';
      } else {
        am_pm = 'PM';
      }
      if (hours === 0) {
        hours = 12;
      }
      if (hours > 12) {
        hours = hours - 12;
      }

      mins = mins + '';
      if (mins.length === 1) {
        mins = "0" + mins;
      }

      secs = secs + '';
      if (secs.length === 1) {
        secs = "0" + secs;
      }

      let time = hours + ':' + mins + ':' + secs + ' ' + am_pm;

      let followup_on = $("#inquiry_followup_date").val();
      let followup_by = $("#inquiry_requested_of :selected").text();
      let followup = '';

      if (followup_on !== '') {
        followup = '(Followup by ' + followup_by + ' on ' + followup_on + ')';
      }

      let output = '<tr><td width="26px" style="padding-right:5px;"><button class="btn waves-effect btn-primary pull-right reply_to_inquiry" id="10381"> ' +
        '<i class="zmdi zmdi-mail-reply"></i> </button></td>  ' +
        '<td>' + note + ' -- <small><em>' + nameOfUser + ' on ' + date + ' ' + time + ' ' + followup + ' </em></small></td></tr>' +
        '<tr style="height:2px;"><td colspan="2" style="background-color:#000;"></td></tr>' +
        '<tr style="height:5px;"><td colspan="2"></td></tr>';

      $(".so_note_box table tr:eq(2)").before(output);

      $("#inquiry").val('');
      $("#inquiry_followup_date").val('');
      $("#inquiry_requested_of").val('null');
    }

    unsaved = false;
  })

  .on("click", ".reply_to_inquiry", function(e) {
    e.preventDefault();

    var reply_id = $(this).attr('id');

    $("[id^=inquiry_reply_line_]").not("#inquiry_reply_line_" + reply_id).hide(100);
    $("#inquiry_reply_line_" + reply_id).toggle(250);
  })
  .on("click", ".inquiry_reply_btn", function() {
    var reply_id = $(this).attr("id");
    var reply_text = $("#inquiry_reply_" + reply_id).val();

    $.post("/ondemand/so_actions.php?action=reply_inquiry", {reply: reply_text, id: reply_id}, function(data) {
      $("body").append(data);
      $("#inquiry_reply_line_" + reply_id).val("").hide(100);
    });

    unsaved = false;
  })

  .on("click", "[id^=show_vin_]", function(e) {
    thisClick = this;

    e.stopPropagation();

    checkTransition(function() {
      active_so_num = $(thisClick).attr("id").replace('show_vin_room_', '');

      toggleDisplay();

      $("#tr_vin_" + active_so_num).show();
      $("#div_vin_" + active_so_num).slideDown(250);
    });
  })
  .on("click", "[id^=show_attachments_room_]", function(e) {
    thisClick = this;

    e.stopPropagation();

    checkTransition(function() {
      active_room_id = $(thisClick).attr("id").replace('show_attachments_room_', '');

      toggleDisplay();

      $("#tr_attachments_" + active_room_id).show();
      $("#div_attachments_" + active_room_id).slideDown(250);

      setTimeout(function() {
        $(window).scrollTo($("#show_single_room_" + active_room_id), 800, {offset: -100});
      }, 300);
    });
  })

  .on("click", "[id^=print_]", function(e) {
    thisClick = this;

    e.stopPropagation();

    checkTransition(function() {
      active_room_id = $(thisClick).attr("id").replace('print_', '');

      toggleDisplay();

      $("#tr_print_" + active_room_id).show();
      $("#div_print_" + active_room_id).slideDown(250);

      setTimeout(function() {
        $(window).scrollTo($("#show_single_room_" + active_room_id), 800, {offset: -100});
      }, 300);
    });
  })
  .on("click", "#add_attachment", function() {
    $("#modalAddAttachment").modal("show");
  })
  .on("click", "#submit_attachments", function(e) {
    e.stopPropagation();

    var footer = $("#r_attachments_footer");
    var button = $(this).parent().html();

    footer.html(""); // remove the button completely, this prevents any accidental double clicks

    // formdata required because now we're attaching files
    var formData = new FormData($("#room_attachments")[0]);

    formData.append('roomid', active_room_id);

    $.ajax({
      url: "/ondemand/room_actions.php?action=upload_attachment",
      xhr: function() {
        var xhr = new window.XMLHttpRequest();

        xhr.upload.addEventListener("progress", function(evt) {
          if (evt.lengthComputable) {
            var percentComplete = evt.loaded / evt.total;

            percentComplete = parseInt(percentComplete * 100);

            footer.html(percentComplete + "%");

            if (percentComplete === 100) {
              footer.html(button);
              $("#modalAddAttachment").modal('hide');
            }
          }
        }, false);

        return xhr;
      },
      type: 'POST',
      data: formData,
      cache: false,
      processData: false,
      contentType: false,
      success: function(data) {
        $('body').append(data);
      }
    });

    unsaved = false;
  })

  .on("click", "#copy_vin", function() {
    var copy_to_title = $("#copy_vin_target").find(":selected").text();
    var thisClick = $(this);

    $.confirm({
      title: "Are you sure you want to copy this VIN?",
      content: "You are overwriting <strong>ALL</strong> VIN information located at " + copy_to_title + " - please confirm you wish to proceed.",
      buttons: {
        confirm: function() {
          var copy_from = $(thisClick).data("roomid");
          var copy_to = $("#copy_vin_target").find(":selected").val();

          console.log("Copy From: " + copy_from + ", Copy To: " + copy_to);

          $.post("/ondemand/room_actions.php?action=copy_vin", {copy_from: copy_from, copy_to: copy_to}, function(data) {
            $('body').append(data);
          });
        },
        cancel: function() {}
      }
    });


  })

  .on("change", "input[name='note_type']", function() {
    var room_notes = $("#room_notes");
    var note_id = $("#note_id");

    switch($(this).val()) {
      case 'room_note':
        room_notes.val('').focus();
        note_id.val('');

        break;

      case 'delivery_note':
        $.post("/ondemand/play_fetch.php?action=get_note&room_id=" + active_room_id + "&note_type=" + 'room_note_delivery', function(data) {
          if(data !== '') {
            var room_info = JSON.parse(data);

            note_id.val(room_info.id);
            room_notes.val(room_info.note).focus();
          } else {
            room_notes.val('').focus();
            note_id.val('');
          }
        });

        break;

      case 'global_note':
        $.post("/ondemand/play_fetch.php?action=get_note&room_id=" + active_room_id + "&note_type=" + 'room_note_global', function(data) {
          if(data !== '') {
            var room_info = JSON.parse(data);

            note_id.val(room_info.id);
            room_notes.val(room_info.note).focus();
          } else {
            room_notes.val('').focus();
            note_id.val('');
          }
        });

        break;

      case 'fin_sample_note':
        $.post("/ondemand/play_fetch.php?action=get_note&room_id=" + active_room_id + "&note_type=" + 'room_note_fin_sample', function(data) {
          if(data !== '') {
            var room_info = JSON.parse(data);

            note_id.val(room_info.id);
            room_notes.val(room_info.note).focus();
          } else {
            room_notes.val('').focus();
            note_id.val('');
          }
        });

        break;

      default:
        room_notes.val('').focus();
        note_id.val('');

        break;
    }
  })

  .on("change", "#hide_empty_fields", function() {
    if($(this).is(":checked")) {
      $("input[value='']").show();
      $(".s_addr_empty").show();
      $(".con_empty").show();
      $(".billing_empty").show();
    } else {
      $("input[value='']").hide();

      if(!$("#secondary_addr_chk").is(":checked")) {
        $(".s_addr_empty").hide();
      }

      if(!$("#contractor_chk").is(":checked")) {
        $(".con_empty").hide();
      }

      if(!$("#billing_addr_chk").is(":checked")) {
        $(".billing_empty").hide();
      }
    }
  })

  .on("click", "#appliance_worksheets", function(e) {
    thisClick = this;

    checkTransition(function() {
      active_room_id = $(thisClick).data("roomid");

      toggleDisplay();

      setTimeout(function() {
        $.post("/html/search/appliance_ws.php?room_id=" + active_room_id + "&id=1", function(data) {
          $("#" + active_room_id + ".tr_room_actions").show().find('div').html(data).slideDown(150);
        });

        scrollLocation("#" + active_room_id + ".tr_room_actions");
      }, 350);
    });
  })
  .on("change", "#sheet_type", function() {
    $.post("/html/search/appliance_ws_info.php?room_id=" + active_room_id + "&id=" + $(this).val(), function(data) {
      $(".sheet_data").html(data);

      $(":input", "#appliance_info").not(':button, :submit, :reset, :hidden, select').val('');
    });
  })
  .on("click", ".appliance_ws_save", function(e) {
    e.stopPropagation();

    var formInfo = $("#appliance_info").serialize();

    $.post("/ondemand/room_actions.php?action=save_app_worksheet&room=" + active_room_id + "&" + formInfo, function(data) {
      if(data !== 'false') {
        $(".print_app_ws").attr("id", data);
        displayToast("success", "Successfully saved worksheet information.", "Worksheet Saved");
      } else {
        displayToast("error", "Unable to save worksheet. Please refresh your page.", "Unable to Save");
      }
    });

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
  .on("click", ".print_app_ws", function(e) {
    e.stopPropagation();

    var ws_id = $(this).attr("id");

    $(".appliance_ws_save").trigger("click");

    window.open("/print/appliance_spec.php?ws_id=" + ws_id, "_blank");
  })

  .on("click", "#generate_code", function(e) {
    var so = $(this).data("so");

    $.ajax({
      url: '/ondemand/so_actions.php?action=generate_code&so_num=' + so,
      dataType: 'text',
      async: false,
      processData: false,
      contentType: false,
      type: 'POST',
      success: function(data){
        let copyFrom = document.createElement("textarea");
        document.body.appendChild(copyFrom);
        copyFrom.textContent = "https://dev.3erp.us/dealer/index.php?key=" + data;
        copyFrom.select();
        document.execCommand("copy");
        copyFrom.remove();

        displayToast("success", "Copied code to clipboard!", "Code Copied");
      }
    });
  })

  .on("click", "#submit_quote", function(e) {
    e.stopPropagation();

    $(".edit_room_save").trigger("click");

    if($("#vin_code_" + active_room_id).val().indexOf("?") > -1) {
      displayToast("error", "Unable to submit while there are still TBD VIN items.", "Unable to Submit");
    } else {
      $.post("/ondemand/room_actions.php?action=submit_quote", {roomid: active_room_id}, function(data) {
        $("body").append(data);
      });
    }
  })
;

$(document).on("scroll", function() {
  $(".dropdown_options").hide();
});