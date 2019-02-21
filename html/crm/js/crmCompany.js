/*global unsaved:true*//*global displayToast*/

var crmCompany = {
  editor: null,

  init: function() {
    crmCompany.checkboxCheck('.add_customer_checked', '.customer_data');
    crmCompany.checkboxCheck('.cust_ship_addr_different', '.cust_ship_different');
    crmCompany.checkboxCheck('.add_vendor_check', '.vendor_data');
    crmCompany.checkboxCheck('.vend_receive_addr_different', '.vend_receive_different');
    crmCompany.checkboxCheck('.vend_payment_contact_different', '.vend_payment_contact');
    crmCompany.checkboxCheck('.add_employee_check', '.employee_data');
    crmCompany.checkboxCheck('.emp_personal_info_check', '.emp_personal_different');
    crmCompany.checkboxCheck('.emp_emergency_contact_info_check', '.emp_emergency_contact');

    $(".generate_pin_pw").change(function() {
      let generatePinPW = $(this).parents().eq(4).find(".generate_pin_pw_field");

      if($(this).find(":selected").text() === 'Yes') {
        generatePinPW.hide();
      } else {
        generatePinPW.show();
      }
    });

    $("#add_new_save").click(function() {
      var contactData = $("#add_new_form").serialize();
      let new_type = $("#new_type").find(":selected").text();
      let error = false;

      if(new_type === 'Organization') {
        let oname = $("#org_name");

        if($.trim(oname.val()) === '') {
          displayToast('error', 'Organization Name is required.', 'Field Missing');

          error = true;
          oname.focus();
        }
      } else if(new_type === 'Individual') {
        let fname = $("#first_name");
        let uname = $("#emp_username");

        if($.trim(uname.val()) === '' && $("#add_employee_check").is(":checked")) {
          displayToast('error', 'User Name is required.', 'Field Missing');

          error = true;
          uname.focus();
        }

        if($.trim(fname.val()) === '') {
          displayToast('error', 'First Name is required.', 'Field Missing');

          error = true;
          fname.focus();
        }
      }

      if(!error) {
        $.post('/html/crm/ajax/company.php?action=addNew', {formInfo: contactData}, function(data) {
          $("body").append(data);

          $("#modalGlobal").modal('hide');
        });

        unsaved = false;
      }
    });

    $(".new_type").change(function() {
      let org = $(this).parents().eq(4).find(".add_new_org");
      let ind = $(this).parents().eq(4).find(".add_new_individual");
      let emp_checkbox = $(this).parents().eq(4).find(".employee_info_checkbox");
      let uidField = $(this).parents().eq(4).find(".identifier");
      let uid = uidField.attr('data-uid');

      switch($(this).find(":selected").text()) {
        case 'Organization':
          ind.hide();
          org.show();
          emp_checkbox.hide();
          uidField.val('R' + uid);

          break;
        case 'Individual':
          org.hide();
          ind.show();
          emp_checkbox.show();
          uidField.val('N' + uid);

          break;
      }
    });

    $("#shipping_different").change(function() {
      if($(this).is(":checked")) {
        $(".shipping_empty_hide").show();
      } else {
        $(".shipping_empty_hide").hide();
      }
    });

    $("#billing_different").change(function() {
      if($(this).is(":checked")) {
        $(".billing_empty_hide").show();
      } else {
        $(".billing_empty_hide").hide();
      }
    });

    $("#show_payment_info").change(function() {
      if($(this).is(":checked")) {
        $(".payment_info").show();
      } else {
        $(".payment_info").hide();
      }
    });

    $(".save_company").click(function() {
      let formInfo = $("#company_information").serialize();

      $.post("/html/crm/ajax/company.php?action=updateContact", {formInfo: formInfo}, function(data) {
        $("body").append(data);
      });
    });

    $(".add_project").click(function() {
      let contact_id = $(this).attr('data-id');

      $.post('/html/modals/add_project.php', {id: contact_id}, function(data) {
        $("#modalGlobal").html(data).modal('show');
      });
    });
  },
  checkboxCheck: function(checkbox, hiddenClass) {
    let targetID = $(checkbox);

    targetID.on("change", function() {
      let $this = $(this);

      if($this.is(":checked")) {
        // targetClass.show();
        $this.parents().eq(3).find(hiddenClass).show();
      } else {
        // targetClass.hide();
        $this.parents().eq(3).find(hiddenClass).hide();
      }
    });
  },
  editCustomerDisplay: function(checkbox, hiddenClass) {
    let checkEle = $(".customer_edit");

    if(checkEle.find(checkbox).is(":checked")) {
      checkEle.find(hiddenClass).show();
    } else {
      checkEle.find(hiddenClass).hide();
    }
  },
  getCompany: function() {
    $.post("/html/crm/templates/crm_view.php", function(data) {
      crmMain.body.html(data);
    });
  },
  startListening: function() {
    // bindings
    $('body')
      .on("click", ".get-company", function() {
        crmCompany.getCompany($(this).attr("data-id"));
      })
      .on("click", ".crm_view_company_list", function() {
        crmCompany.getCompanyList();
      })
      .on("click", "#modalAddProjectSave", function() {
        let info = $("#add_project_form").serialize();

        $.post("/html/crm/ajax/company.php?action=addProject", {formInfo: info}, function(data) {
          $("body").append(data);

          $("#modalGlobal").modal('hide');
        });

        unsaved = false;
      })
    ;
  },
  initEditor: function() {
    crmCompany.editor = new dhtmlXEditor({
      parent: "new_note",
      toolbar: true, // force dhtmlxToolbar using
      iconsPath: "/assets/plugins/dhtmlXEditor/imgs/", // path for toolbar icons
      content: ""
    });
  },
  reInitEditor: function() {
    if(crmCompany.editor !== undefined && crmCompany.editor !== null) {
      let curContent = crmCompany.editor.getContent();

      crmCompany.editor.unload();

      crmCompany.editor = new dhtmlXEditor({
        parent: "new_note",
        toolbar: true, // force dhtmlxToolbar using
        iconsPath: "/assets/plugins/dhtmlXEditor/imgs/", // path for toolbar icons
        content: curContent
      });
    }
  },
  checkEmpty: function(input_name_starts_with, checkbox_check, hide_what) {
    let isEmpty = true;

    $.each($("input[name^='" + input_name_starts_with + "']"), function(i, ele) {
      if($(ele).val() !== '') {
        isEmpty = false;
      }
    });

    if(isEmpty) {
      $(checkbox_check).prop("checked", false);
      $(hide_what).hide();
    } else {
      $(checkbox_check).prop("checked", true);
      $(hide_what).show();
    }
  }
};