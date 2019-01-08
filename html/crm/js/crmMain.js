/*global $:false*//*global dhtmlXPopup*/

var crmMain = {
  body: null, // the main body to insert ALL CRM data into

  dataTableContainer: {
    production_table: null,
    queue_table: null,
    active_table: null
  },

  // datatable skeleton setup for production, active ops, quotes
  dtSkeleton: function(ajaxURL, customDefs) {
    if(customDefs !== '' || customDefs !== null || customDefs !== undefined) {
      return {
        "ajax": ajaxURL,
        "createdRow": function (row, data) {
          $(row).addClass("cursor-hand view_so_info").attr('data-project-name', data.project_name);
        },
        "paging": false,
        scrollY: '30vh',
        scrollCollapse: true,
        customDefs
      };
    } else {
      return {
        "ajax": ajaxURL,
        "createdRow": function (row, data) {
          $(row).addClass("cursor-hand view_so_info").attr('data-project-name', data.project_name);
        },
        "paging": false,
        scrollY: '30vh',
        scrollCollapse: true,
        searching: false,
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
  },
  dealerInit: function() {
    var current_value = null;

    $("body")
      .on("focus", ".dealer_input", function() {
        current_value = $(this).val();
      })
      .on("blur", ".dealer_input", function() {
        let dealer_id = $(this).attr('data-id');
        let column = $(this).attr('data-col');
        let value = $(this).val();

        if(value !== current_value) {
          console.log("Going to update id " + dealer_id + " on column " + column + " with " + value);

          $.post("/html/assets/update_dealers.php", {id: dealer_id, col: column, val: value}, function(data) {
            $("body").append(data);
          });
        }
      });
  },
  initProjectHover: function() {
    let projectName = new dhtmlXPopup();

    $("body").on("mouseenter mouseleave", ".view_so_info", function() {
      projectName.attachHTML($(this).attr("data-project-name"));

      if (projectName.isVisible()) {
        projectName.hide();
      } else {
        var x = window.dhx4.absLeft(this) - 150; // returns left position related to window
        var y = window.dhx4.absTop(this); // returns top position related to window
        var w = this.offsetWidth;
        var h = this.offsetHeight;
        projectName.show(x,y,w,h);
      }
    });
  }
};