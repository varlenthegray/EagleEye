var crmMain = {
  body: null, // the main body to insert ALL CRM data into

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
  widgetInit: function() {
    $("body")
      .on("click", ".widget-item", function() {
        let windowID = $(this).attr('data-window');

        if(winMgr.wins[windowID] === undefined) {
          if(winMgr.newAutoWin(windowID)) {
            $(this).addClass("widget-active");
          }
        } else {
          winMgr.window.window(windowID).close();
        }
      })
    ;
  },
  setBody: function(container) {
    /**********************************************
     * Sets the body container
     * ****************************************** *
     * @container = the container of the main body, this is to update the variable name
     ********************************************/
    crmMain.body = container;
  }
};