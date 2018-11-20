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

function determineOpts() {

}

$("body")
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

    determineOpts();
  })
  .on("click", ".option", function() {
    var field;
    var display;
    var addl_info = '';

    var value = $(this).attr('data-value');

    if($(this).attr("data-addl-info") !== '') {
      addl_info = $(this).attr("data-addl-info") + " - ";
    }

    if($(this).html() === '') {
      display = addl_info + $(this).attr('data-display-text');
    } else {
      display = addl_info + $(this).html();
    }

    if($(this).hasClass('sub_option') && $(this).parent().hasClass('grid_element')) {
      field = $(this).parent().parent().parent().attr('data-for');

      $(this).parent().parent().parent().parent().find('.selected').html(display);
    } else {
      field = $(this).parent().parent().attr('data-for');

      $(this).parent().parent().parent().find('.selected').html(display);
    }

    $("#" + field).val(value);

    if($("[id^='vin_code']").length > 0) {
      calcVin(active_room_id);
    }

    determineOpts();

    productTypeSwitch();
  })
  .on("change", "#show_image_popups", function() {
    adjustImgPopups();
  })
  .keydown(function(e) {
    if(e.key === 'Escape') {
      $(".custom_dropdown").find('.dropdown_options').hide();
    }
  })
;