/*global displayToast*/

var association = {
  addBox: null,

  init: function() {
    $(".assign_contact").click(function() {
      let contact_id = $(this).parents().eq(1).find(".contact_id :selected").val();
      let type_id = $(this).attr('data-type-id');

      association.addBox = $(this).parents().eq(4).find(".contact-box");

      if(contact_id !== '') {
        $.post("/html/modals/add_contact_association.php", {contact_id: contact_id, type_id: type_id}, function(data) {
          $("#modalGlobal").html(data).modal("show");
        });
      } else {
        displayToast('warning', 'Unable to associate a blank contact.', 'Blank Contact');
      }
    });

    $("body")
      .on("click", ".remove_assigned_contact", function() {
        let id = $(this).attr('data-id');
        let type = $(this).attr('data-type');
        let thisClick = this;

        console.log("Removing contact: " + id + ", Type: " + type);

        $.post("/ondemand/contact_actions.php?action=remove_contact_project", {id: id, type: type}, function(data) {
          $(thisClick).parents().eq(1).remove();

          if($(".contact-box .contact-card").length === 0) {
            $(".contact-box").append("<strong>No Contacts</strong>");
          }

          $("body").append(data);
        }).fail(function(header) {
          $("body").append(header.responseText);
        });
      })
      .on("click", ".edit_assigned_contact", function() {
        $.post("/html/modals/add_contact.php?action=edit", {id: $(this).attr('data-id')}, function(data) {
          $("#modalGlobal").html(data).modal('show');
        });
      })
    ;
  },
  customAssociation: function() {
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
  },
  modalManager: function() {
    $("#modalAddContactAssociation").click(function() {
      let formInfo = $("#contactAssociationForm").serialize();
      let contact_id = $(this).attr('data-contact-id');
      let type = $.trim($("#crmViewGlobal").find(".active").text().toLowerCase()); // type = active tab
      let typeID = $(this).attr('data-type-id');

      // TODO: Fix type = active tab text, that's STUPID! I should be able to change the tab text to whatever I want!

      if($("#contact_role :selected").val() !== 'none' || $("#custom_association").is(":checked")) {
        $.post("/ondemand/contact_actions.php?action=add_contact_project", {contact_id: contact_id, type_id: typeID, formInfo: formInfo, type: type}, function(data) {
          let info = JSON.parse(data);
          let displayName;

          //<editor-fold desc="Adding new card">
          info['cell'] = (info['cell'] !== null && $.trim(info['cell']) !== '') ? info['cell'] : '';
          info['email'] = (info['email'] !== null && $.trim(info['email']) !== '') ? info['email'] : '';

          if($.trim(info['first_name']) !== '' && $.trim(info['first_name']) !== null) {
            displayName = info['first_name'] + ' ' + info['last_name'];
          } else {
            displayName = info['company_name'];
          }

          let newCard = '<div class="contact-card">' +
            '<div style="float:right;">' +
            // '<i class="fa fa-bank primary-color cursor-hand assoc_set_commission" data-id="' + info['id'] + '" title="Commission Schedule"></i>' +
            // '<i class="fa fa-pencil-square primary-color cursor-hand edit_assigned_contact" data-id="' + contact_id + '" title="Edit Contact"></i>' +
            '<i class="fa fa-minus-square danger-color cursor-hand remove_assigned_contact" data-id="' + info['uID'] + '" title="Remove Contact"></i>' +
            '</div>' +
            '  <h5><a href="#">' + displayName + '</a></h5>' +
            '  <h6>' + info['associated_as'] + '</h6>' +
            '  <p>' + info['cell'] + '<br>' + info['email'] + '</p>' +
            '</div>';

          if(association.addBox.find(".contact-card").length === 0) {
            // $(".contact-box strong").remove();
            association.addBox.find('strong').remove();
          }

          association.addBox.append(newCard);
          //</editor-fold>

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
};