/*jshint strict: false*/
/*global displayToast*//*global checkTransition*//*global unsaved:true*//*global tinysort*//*global scrollPosition:true*//*global globalFunctions*//*global document*//*global nameOfUser*//*global searchTable*//*global FormData*/

// Goal: to ensure the loading of Global Search input field
var timer;
var active_so_num;
var active_room_id;
var thisClick;

/*function scrollLocation(container) {
  setTimeout(function() {
    $(window).scrollTo($(container), 800, {offset: -125});
  }, 300);
}*/

// TODO: Lock down separeate sections based on bouncer results
$("body")
  .on("keyup", "#global_search", function() {
    checkTransition(function() {
      let searchDisplay = $("#search_display");
      let input = $("#global_search");
      let mainDisplay = $("#main_display");
      let loading_spinner = '<div class="text-md-center" style="color:#FFF;"><span id="global_search_status"><i class="fa fa-3x fa-spin fa-spinner" style="width: auto;margin-right: 10px;"></i></span></div>';

      mainDisplay.attr("data-search", "true");
      searchDisplay.html(loading_spinner);

      if(input.val().length >= 3) {
        scrollPosition = $(window).scrollTop();

        mainDisplay.hide();
        searchDisplay.show();

        clearTimeout(timer);

        timer = setTimeout(function () {
          if (input.val().length >= 1) {
            $.post("/html/search/search_results.php", {find: input.val()}, function (data) {
              searchDisplay.html(data);
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
      globalFunctions.backFromSearch();
    });
  })

  .on("click", ".wc-edit-queue", function() {
    $("#global_search").val($(this).attr("id")).trigger("keyup");
  })

  .on("click", "#edit_so", function(e) {
    $(this).find('i').removeClass('zmdi zmdi-edit').addClass('fa fa-spin fa-spinner');

    let thisClick = this;

    active_so_num = $(this).attr('data-sonum');

    let container = $(this).parents().eq(3).find('.edit_so');

    e.stopPropagation();

    checkTransition(function() {
      if(container.is(":visible")) {
        container.html('').hide();
        $(thisClick).find('i').removeClass('fa fa-spin fa-spinner').addClass('zmdi zmdi-edit');
      } else {
        $(".edit_so").html('').hide();

        $.get('/html/search/ajax/edit_so.php', {so_num: active_so_num}, function(data) {
          container.html(data).show();
        }).done(function() {
          $(thisClick).find('i').removeClass('fa fa-spin fa-spinner').addClass('zmdi zmdi-edit');
        });
      }
    });
  })
  .on("click", "[id^=show_room_]", function() {
    thisClick = this;

    checkTransition(function() {
      active_so_num = $(thisClick).attr("id").replace('show_room_', '');

      $("#tr_room_" + active_so_num).show();
      $("#div_room_" + active_so_num).slideDown(250);
    });
  })
  .on("click", ".edit_room", function(e) {
    $(this).find('i').removeClass('zmdi zmdi-edit').addClass('fa fa-spin fa-spinner');

    let thisClick = this;

    active_room_id = $(this).attr("id");
    active_so_num = $(this).attr("data-sonum");

    let roomSearchTable = searchTable[active_so_num];

    let tr = $(this).closest('tr');
    let row = roomSearchTable.row(tr);

    e.stopPropagation();

    checkTransition(function() {
      if(row.child.isShown()) {
        // This row is already open - close it
        row.child('').hide();
        tr.removeClass('shown');

        $(thisClick).find('i').removeClass('fa fa-spin fa-spinner').addClass('zmdi zmdi-edit');
      } else {
        // collapses all rows
        roomSearchTable.rows().every(function() {
          // If row has details expanded
          if(this.child.isShown()) {
            // Collapse row details
            this.child('').hide();
            $(this.node()).removeClass('shown');
          }
        });

        // Open this row
        $.get("/html/pricing/index.php", {room_id: active_room_id}, function(data) {
          row.child(data, 'no-row-hover').show();
          tr.addClass('shown');
        }).done(function() {
          $(thisClick).find('i').removeClass('fa fa-spin fa-spinner').addClass('zmdi zmdi-edit');
        });
      }
    });
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

  .on("click", ".save_so", function() {
    var so_info = $("#project_form").serialize();

    $.post('/ondemand/so_actions.php?action=save_so&so_num=' + active_so_num, {formInfo: so_info}, function(data) {
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

  .on("click", "[id^=show_attachments_room_]", function(e) {
    thisClick = this;

    e.stopPropagation();

    checkTransition(function() {
      active_room_id = $(thisClick).attr("id").replace('show_attachments_room_', '');

      $("#tr_attachments_" + active_room_id).show();
      $("#div_attachments_" + active_room_id).slideDown(250);

      setTimeout(function() {
        $(window).scrollTo($("#show_single_room_" + active_room_id), 800, {offset: -100});
      }, 300);
    });
  })

  .on("click", "#add_attachment", function() {
    $.post("/html/modals/room_attachments.php", {room_id: active_room_id}, function(data) {
      $("#modalGlobal").html(data).modal("show");
    });
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

  .on("click", "#generate_code", function() {
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

  .on("click", ".add_room_trigger", function(e) {
    e.stopPropagation();

    active_so_num = $(this).attr('data-sonum');

    $.post("/html/search/modal/add_room.php", {addType: 'room', so_num: active_so_num}, function(data) {
      $("#modalGlobal").html(data).modal("show");
    });
  })
  .on("click", "#modalAddRoomCreate", function() {
    let room_data = $("#modalAddRoomData").serialize();

    if($("#modalAddRoomData input[name='room_name']").val() === '') {
      $.confirm({
        title: "Unable to Save",
        content: "Cannot save with no room name!",
        buttons: {
          ok: function() {}
        }
      });
    } else {
      $.post("/html/search/ajax/room_actions.php?action=add_new_room", {data: room_data}, function (data) {
        $("body").append(data);
      });

      $("#modalGlobal").modal("hide");

      unsaved = false;
    }
  })

  .on("click", ".add_iteration", function(e) {
    thisClick = this;

    e.stopPropagation();

    checkTransition(function() {
      active_room_id = $(thisClick).attr('data-roomid');
      active_so_num = $(thisClick).attr('data-sonum');
      let addTo = $(thisClick).attr('data-addto');

      $.post("/html/search/modal/add_room.php", {so_num: active_so_num, addType: addTo, room_id: active_room_id}, function(data) {
        $("#modalGlobal").html(data).modal("show");
      });
    });

    return false;
  })

  /*.on("click", ".iteration_save", function(e) {
    e.stopPropagation();

    if($("#modalAddRoomData input[name='room_name']").val() === '') {
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
  })*/
  /* .on("click", ".edit_room_save", function(e) {
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
  })*/
  /*.on("click", "#copy_vin", function() {
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


})*/
  /*.on("click", "[id^=print_]", function(e) {
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
})*/
  /*.on("click", "[id^=show_vin_]", function(e) {
  thisClick = this;

  e.stopPropagation();

  checkTransition(function() {
    active_so_num = $(thisClick).attr("id").replace('show_vin_room_', '');

    toggleDisplay();

    $("#tr_vin_" + active_so_num).show();
    $("#div_vin_" + active_so_num).slideDown(250);
  });
})*/
;