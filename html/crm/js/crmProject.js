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
    },
    contactAssociation: function() {
      $("#custom_association").change(function() {
        let stdRole = $("#displayStdRole");
        let cstRole = $("#displayCustomRole");

        if($(this).is(":checked")) {
          stdRole.hide();
          cstRole.show();
        } else {
          stdRole.show();
          cstRole.hide();
        }
      });

      $("#modalAddContactAssociation").click(function() {
        let formInfo = $("#contactAssociationForm").serialize();
        let contact_id = $(".add_contact_id :selected").val();

        console.log("Old code runnin.");

        if($("#contact_role :selected").val() !== 'none' || $("#custom_association").is(":checked")) {
          $.post("/ondemand/contact_actions.php?action=add_contact_project", {contact_id: contact_id, so: active_so_num, formInfo: formInfo}, function(data) {
            let info = JSON.parse(data);

            info['cell'] = (info['cell'] !== null) ? info['cell'] : '';
            info['email'] = (info['email'] !== null) ? info['email'] : '';

            let newCard = '<div class="contact-card">' +
              '<div style="float:right;"><i class="fa fa-minus-square danger-color cursor-hand remove_assigned_contact_so" data-id="' + info['id'] + '" title="Remove Contact"></i></div>' +
              '  <h5><a href="#">' + info['first_name'] + ' ' + info['last_name'] + '</a></h5>' +
              '  <h6>' + info['associated_as'] + '</h6>' +
              '  <p>' + info['cell'] + '<br>' + info['email'] + '</p>' +
              '</div>';

            if($(".contact-box .contact-card").length === 0) {
              $(".contact-box strong").remove();
            }

            $(".contact-box").append(newCard);

            displayToast('success', 'Successfully added contact to project.', 'Added Contact');

            $("#modalGlobal").modal("hide");
          }).fail(function(header) {
            $("body").append(header.responseText);
          });
        } else {
          displayToast('warning', 'You must define a role.', 'No Role Defined');
        }
      });
    }
  }
};