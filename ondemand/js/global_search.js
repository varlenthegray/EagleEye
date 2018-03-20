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

function adjustImgPopups() {
  if($("#show_image_popups").is(":checked")) {
    $(".option_grid").css("display", "flex");
    $(".option_list").hide();
    $(".custom_dropdown .selected img").show();

    $(".dropdown_options").css("width", "60vw");
  } else {
    $(".option_grid").hide();
    $(".option_list").show();
    $(".custom_dropdown .selected img").hide();

    $(".dropdown_options").css("width", "");
  }
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
            $.post("/ondemand/livesearch/search_results.php?search=general", {find: input.val()}, function (data) {
              searchTable.html(data);
              $("#search_results_global_table").trigger("update");

              if (data !== '') {
                $('[data-toggle="tooltip"]').tooltip(); // enable tooltips

                // setup field masks
                $(".mask-zip").mask('00000-0000');
                $(".mask-phone").mask('(000) 000-0000');

                // setup date picker
                $(".delivery_date").datepicker({
                  autoclose: true,
                  todayHighlight: true
                }).mask('00/00/0000');
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
    thisClick = this;

    e.stopPropagation();

    active_room_id = $(thisClick).attr("id");
    active_so_num = $(thisClick).data("sonum");

    checkTransition(function() {
      active_room_id = $(thisClick).attr("id");
      active_so_num = $(thisClick).data("sonum");

      toggleDisplay();

      $.post("/html/search/room_edit.php?room_id=" + active_room_id, function(data) {
        $("#" + active_room_id + ".tr_room_actions").show().find('div').html(data).slideDown(150);
      });

      scrollLocation("#" + active_room_id + ".tr_room_actions");
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

  .on("click", ".add_room_trigger", function(e) {
    thisClick = this;

    e.stopPropagation();

    checkTransition(function() {
      active_so_num = $(thisClick).data('sonum');

      toggleDisplay();

      $.post("/html/search/room_add.php?so_num=" + active_so_num, function(data) {
        $("#" + active_so_num + ".add_room").show().find('div').html(data).slideDown(150);
      });
    });

    scrollLocation("#" + active_so_num + ".add_room");
  })

  /** Deprecated
   .on("click", ".add_room_save", function() {
        var save_info = $("#room_add_" + active_so_num).serialize();

        $.post("/ondemand/room_actions.php?action=insert_new_room&" + save_info, function(data) {
            $("body").append(data);
        });

        unsaved = false;
    })
   .on("change", ".dealer_code", function() {
        $.post("/ondemand/play_fetch.php?action=get_dealer_info&dealer_code=" + $(this).val(), function(data) {
            if(data !== '') {
                var dealer = JSON.parse(data);

                $("#add_room_account_type_" + active_so_num).val(dealer.account_type);
                $("#add_room_dealer_" + active_so_num).val(dealer.dealer_name);
                $("#add_room_contact_" + active_so_num).val(dealer.contact);
                $("#add_room_phone_num_" + active_so_num).val(dealer.phone);
                $("#add_room_email_" + active_so_num).val(dealer.email);
                $("#add_room_salesperson_" + active_so_num).val(dealer.contact);
                $("#add_room_shipping_addr_" + active_so_num).val(dealer.shipping_address);
                $("#add_room_shipping_city_" + active_so_num).val(dealer.shipping_city);
                $("#add_room_shipping_state_" + active_so_num).val(dealer.shipping_state);
                $("#add_room_shipping_zip_" + active_so_num).val(dealer.shipping_zip);
            }
        });
    })
   End Deprecated **/

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
      var custom_fields = ['X', 'Xxx', 'AX', 'DX', 'TX', 'Xx', 'WX', '1cXXXX', '3gXXXX'];

      if($.inArray(ele.val(), custom_fields) >= 0) {
        val_array[field] = {};

        ele.parent().find('.selected').find('input').each(function() {
          val_array[field][$(this).attr('name')] = $(this).val();
        });
      }
    });

    var customVals = JSON.stringify(val_array);

    $.post("/ondemand/room_actions.php?action=update_room&" + edit_info, {active_ops: active_ops, customVals: customVals}, function(data) {
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

      $.post("/ondemand/room_actions.php?action=create_room&" + edit_info, {active_ops: active_ops}, function(data) {
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

    e.stopPropagation();

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

  .on("click", ".custom_dropdown", function(e) {
    adjustImgPopups();

    if(e.target.nodeName !== 'INPUT') {
      $(".custom_dropdown").not(this).children('.dropdown_options').hide();
      $(this).find('.dropdown_options').toggle();

      var clicked = $(this).find('.dropdown_options').attr("data-for");

      if(clicked === 'days_to_ship') {
        var dts = $("#days_to_ship").val();
        var classColor;

        switch(dts) {
          case 'G':
            classColor = 'green';
            break;

          case 'Y':
            classColor = 'yellow';
            break;

          case 'N':
            classColor = 'orange';
            break;

          case 'R':
            classColor = 'red';
            break;

          default:
            classColor = 'gray';
            break;
        }

        $.post("/ondemand/room_actions.php?action=calc_del_date", {days_to_ship: dts}, function(data) {
          $(".delivery_date").val(data).removeClass('job-color-red job-color-green job-color-yellow job-color-orange').addClass('job-color-' + classColor).data("datepicker").setDate(data);
        });
      }

      // grabs the current element in a rectangle box
      var viewPortOffset = this.getBoundingClientRect();

      // calculates the current offset to the top vs the total height and the available space
      var dropdown_height = (window.innerHeight - (viewPortOffset.top + $(this).outerHeight(true))) - 50;

      // if it's going to be too small, go vertical
      if(dropdown_height < 400) {
        $(this).find(".dropdown_options").css({"bottom":"0", "top":"inherit"});
        dropdown_height = 450;
      } else if(dropdown_height > 650) { // if it's too big, set the max size
        $(this).find(".dropdown_options").css({"bottom": "inherit", "top": "20px"});
        dropdown_height = 650;
      } else { // otherwise, just figure out the size
        $(this).find(".dropdown_options").css({"bottom":"inherit", "top":"20px"});
      }

      // set the max size
      $(this).find(".dropdown_options").css('max-height', dropdown_height);

      // stops the scrolling of the window while in the dropdown specifically
      $(".dropdown_options").on('DOMMouseScroll mousewheel', function(ev) {
        var $this = $(this),
          scrollTop = this.scrollTop,
          scrollHeight = this.scrollHeight,
          height = $this.height(),
          delta = (ev.type === 'DOMMouseScroll' ?
            ev.originalEvent.detail * -40 :
            ev.originalEvent.wheelDelta),
          up = delta > 0;

        var prevent = function() {
          ev.stopPropagation();
          ev.preventDefault();
          ev.returnValue = false;
          return false;
        };

        if (!up && -delta > scrollHeight - height - scrollTop) {
          // Scrolling down, but this will take us past the bottom.
          $this.scrollTop(scrollHeight);
          return prevent();
        } else if (up && delta > scrollTop) {
          // Scrolling up, but this will take us past the top.
          $this.scrollTop(0);
          return prevent();
        }
      });
    } else {
      $(".custom_dropdown").find('.dropdown_options').hide();
    }
  })
  .on("click", ".option", function() {
    var field;

    var value = $(this).attr('data-value');

    if($(this).hasClass('sub_option') && $(this).parent().hasClass('grid_element')) {
      field = $(this).parent().parent().parent().attr('data-for');
      $(this).parent().parent().parent().parent().find('.selected').html($(this).html());
    } else {
      field = $(this).parent().parent().attr('data-for');
      $(this).parent().parent().parent().find('.selected').html($(this).html());
    }

    $("#" + field).val(value);

    calcVin(active_room_id);
  })
  .on("change", "#show_image_popups", function() {
    adjustImgPopups();
  })
  .on("click", "#dropdown_p_type", function() {
    productTypeSwitch();
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