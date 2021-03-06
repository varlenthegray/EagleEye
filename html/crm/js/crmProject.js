/*global active_so_num*//*global displayToast*/

var crmProject = {
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
        }).done(function() {
          $("#modalBracketSave").attr('data-roomid', room_id);
        });

        return false;
      });
    }
  },
  contactMgr: {
    init: function() {
      $(".assign_contact_so").click(function() {
        let contact_id = $(".add_contact_id :selected").val();

        if(contact_id !== '') {
          $.post("/html/modals/add_contact_association.php", {contact_id: contact_id, so: active_so_num}, function(data) {
            $("#modalGlobal").html(data).modal("show");
          });
        } else {
          displayToast('warning', 'Unable to associate a blank contact.', 'Blank Contact');
        }
      });

      $("body")
        .on("click", ".remove_assigned_contact_so", function() {
        let contact_id = $(this).attr('data-id');
        let thisClick = this;

        $.post("/ondemand/contact_actions.php?action=remove_contact_project", {contact_id: contact_id, so: active_so_num}, function(data, response) {
          if(response === 'success') {
            $(thisClick).parents().eq(1).remove();

            if($(".contact-box .contact-card").length === 0) {
              $(".contact-box").append("<strong>No Contacts</strong>");
            }
          }

          $("body").append(data);
        });
      })
        .on("click", ".edit_assigned_contact", function() {
          $.post("/html/modals/add_contact.php?action=edit", {id: $(this).attr('data-id')}, function(data) {
            $("#modalGlobal").html(data).modal('show');
          });
        })
      ;
    }
  }
};