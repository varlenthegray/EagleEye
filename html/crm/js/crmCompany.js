/*global unsaved:true*/

var crmCompany = {
  editor: null,

  init: function() {
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

      $.post("/html/crm/ajax/company.php?action=saveCompany", {formInfo: formInfo}, function(data) {
        $("body").append(data);
      });
    });

    $(".add_project").click(function() {
      let company_id = $(this).attr('data-id');

      $.post('/html/modals/add_project.php', {id: company_id}, function(data) {
        $("#modalGlobal").html(data).modal('show');
      });
    });
  },
  getCompany: function() {
    $.post("/html/crm/templates/crm_view.php", function(data) {
      crmMain.body.html(data);
    });
  },
  getCompanyList: function() {
    // get the company list
    $.post("/html/crm/templates/view_company_list.php", function(data) {
      crmMain.body.html(data); // load it into the container
    }).done(function() {
      $('.crmCompanies').DataTable( { // init datatables
        "ajax": "/html/crm/ajax/company.php?action=getCompanyList",
        "columns": [
          { "data": "name" },
          { "data": "phone_number" },
          { "data": "annual_revenue" },
          { "data": "industry" },
          { "data": "last_contact"},
          { "data": "created"}
        ],
        "pageLength": 25,
        "createdRow": function(row,data) {
          $(row).addClass("cursor-hand get-company").attr("data-id", data.id);
        }
      });
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