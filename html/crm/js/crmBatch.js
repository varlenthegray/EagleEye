var crmBatch = {
  index: {
    init: function() {
      $("#roomTabView .tab-ajax").click(function() {
        let $this = $(this), loadurl = $this.attr('data-ajax'), targ = $this.attr('href');

        if(loadurl !== undefined && targ !== undefined) {
          $.get(loadurl, function(data) {
            $(targ).html(data);
          });
        }
      });
    }
  },
  bracketMgr: {
    init: function() {
      $(".bracket_header").click(function() {
        if($(this).parent().find('.bracket_details').is(":visible")) {
          $(this).parent().find(".bracket_details").hide();
        } else {
          $(this).parent().find(".bracket_details").show();
        }
      });

      $(".room_manage_bracket").click(function() {
        let room_id = $(this).attr('data-roomid');

        $.post("/html/pricing/ajax/global_actions.php?action=modalBracketMgmt&roomID=" + room_id, function(data) {
          $("#modalGlobal").html(data).modal("show");
        });

        return false;
      });
    }
  }
};