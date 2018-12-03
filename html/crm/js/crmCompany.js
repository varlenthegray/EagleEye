var crmCompany = {
  editor: null,

  getCompany: function(id) {
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
  }
};