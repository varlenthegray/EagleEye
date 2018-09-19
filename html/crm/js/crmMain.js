var crmMain = {
  dtSkeleton: function(ajaxURL, customDefs) {
    if(customDefs !== '' || customDefs !== null || customDefs !== undefined) {
      return {
        "ajax": ajaxURL,
        "createdRow": function (row, data, dataIndex) {
          $(row).addClass("cursor-hand view_so_info").attr('data-project-name', data.project_name);
        },
        "paging": false,
        scrollY: '30vh',
        scrollCollapse: true,
        "dom": '<"#quote_header.dt-custom-header">tipr',
        customDefs
      };
    } else {
      return {
        "ajax": ajaxURL,
        "createdRow": function (row, data, dataIndex) {
          $(row).addClass("cursor-hand view_so_info").attr('data-project-name', data.project_name);
        },
        "paging": false,
        scrollY: '30vh',
        scrollCollapse: true,
        "dom": '<"#quote_header.dt-custom-header">tipr',
        "order": [[0, "asc"]]
      };
    }
  },

  initQuote: function() {
    return $("#quote_global_table").DataTable(crmMain.dtSkeleton('/ondemand/display_actions.php?action=display_quotes'));
  },

  initProduction: function() {
    return $("#orders_global_table").DataTable(crmMain.dtSkeleton('/ondemand/display_actions.php?action=display_orders'));
  },

  initActiveOps: function() {
    // active operations has a custom sort order and column definition setup
    let custDefs = {
      "columnDefs": [ { "targets": [0], "orderable": false, className: "nowrap" } ],
      "order": [[1, "desc"]]
    };

    return $("#active_ops_global_table").DataTable(crmMain.dtSkeleton('/ondemand/display_actions.php?action=display_ind_active_jobs', custDefs));
  },

  startCompanyResizeWatcher: function() {
    winMgr.getWins().attachEvent("onResizeFinish", function(win) {
      crmCompany.reInitEditor();
    });

    winMgr.getWins().attachEvent("onMaximize", function(win) {
      crmCompany.reInitEditor();
    });
  }
};