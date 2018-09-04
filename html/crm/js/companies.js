var companies = (function() {
  let container;

  var getCompany = function(id) {
    $.post("/html/crm/includes/view_company.php", function(data) {
      container.html(data);
    }).done(function() {

    });
  };

  var getCompanyList = function() {
    // get the company list
    $.post("/html/crm/includes/view_company_list.php", function(data) {
      container.html(data); // load it into the container
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
          $(row).addClass("cursor-hand get-company");
          $(row).attr("data-id", data.id);
        }
      });
    });
  };

  var initCompanyList = function(mainContainer) {
    container = $(mainContainer); // initialize the main container
    companies.getCompanyList(mainContainer); // get the company list

    // bindings
    container.on("click", ".get-company", function() {
      companies.getCompany($(this).attr("data-id"));
    });
  };

  return {
    initCompanyList: initCompanyList,
    getCompany: getCompany,
    getCompanyList: getCompanyList
  };
})();