var crmCompany = {
  container: $("#crmBody"),
  editor: null,

  getCompany: function(id) {
    $.post("/html/crm/includes/view_company.php", function(data) {
      crmCompany.container.html(data);
    });
  },

  getCompanyList: function() {
    // get the company list
    $.post("/html/crm/includes/view_company_list.php", function(data) {
      crmCompany.container.html(data); // load it into the container
    }).done(function() {
      $('#companies').DataTable( { // init datatables
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
        "createdRow": function(row,data,dataIndex) {
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
    let curContent = crmCompany.editor.getContent();

    crmCompany.editor.unload();

    crmCompany.editor = new dhtmlXEditor({
      parent: "new_note",
      toolbar: true, // force dhtmlxToolbar using
      iconsPath: "/assets/plugins/dhtmlXEditor/imgs/", // path for toolbar icons
      content: curContent
    });
  }
};