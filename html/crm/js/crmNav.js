/*global active_room_id:true*//*global active_so_num:true*/

var crmNav = {
  tree: $("#searchResultTree"),
  getTree: null, // assigned in init
  search: null, // what's being searched for,
  searchAjax: null, // the search AJAX results
  activatedType: null, // the current selected type
  companyID: null, // company id # (if available)
  projectID: null, // project id # (if available)
  batchID: null, // batch id # (if available)
  navKeys: null,

  initTree: function() {
    this.tree.fancytree({
      extensions: ["filter"],
      filter: {
        counter: false,
        mode: "hide",
        highlight: false,
        autoExpand: false
      },
      source: { url: "/html/crm/ajax/cached_result_tree.json" },
      renderNode: function(event, data) {
        let node = data.node, $span = $(node.span);

        switch(node.data.orderStatus) {
          case 'N':
            $span.addClass("crmSearchLead");

            if(!$("#search_lead").is(":checked")) { $span.hide(); }

            break;
          case '#':
            $span.addClass("crmSearchQuote");

            if(!$("#search_quote").is(":checked")) { $span.hide(); }

            break;
          case '$':
            $span.addClass("crmSearchProd");

            if(!$("#search_prod").is(":checked")) { $span.hide(); }

            break;
          case '-':
            $span.addClass("crmSearchLost");

            if(!$("#search_lost").is(":checked")) { $span.hide(); }

            break;
          case '+':
            $span.addClass("crmSearchCompleted");

            if(!$("#search_completed").is(":checked")) { $span.hide(); }

            break;
          case 'P':
            $span.addClass("crmSearchPending");

            if(!$("#search_pending").is(":checked")) { $span.hide(); }

            break;
        }
      },
      activate: function(event, data) {
        let thisClick = $(this);

        setTimeout(function() {
          let node = data.node; // capture the node

          crmCompany.getCompany(thisClick.attr("data-id")); // get the company information for that listing and display it

          /********************************************************
           * Begin management of clicking on SO or Room
           *******************************************************/
          crmNav.activatedType = node.data.keyType;

          let keyPath = node.getKeyPath();
          let keys = keyPath.substring(1, keyPath.length).split('/');

          crmNav.companyID = keys[0];
          active_so_num = keys[1];
          active_room_id = keys[2];

          crmNav.navKeys = keys;

          console.log(keys); // schrodinger's code; without this batch doesn't always load...

          $.post("/html/crm/templates/crm_view.php", {'keys': JSON.stringify(keys)}, function(data) { // pull the data for the main tab of the CRM
            crmMain.body.html(data); // insert it into the body
          });
        }, 250);
      },
      expand: function() {
        crmNav.checkFilters();
      }
    });

    crmNav.getTree = crmNav.tree.fancytree("getTree");
  },
  clearMain: function() {
    crmMain.body.html("");
  },
  clearAll: function() {
    crmNav.clearMain();
    $("#crm_search").val('');
    crmNav.getTree.clearFilter();

    crmNav.tree.fancytree("getRootNode").visit(function(node) {
      node.setExpanded(false);
    });

    $(".crm_search_results").hide();
  },
  checkFilters: function() {
    crmNav.showHideCheckbox('search_lead', '.crmSearchLead');
    crmNav.showHideCheckbox('search_quote', '.crmSearchQuote');
    crmNav.showHideCheckbox('search_prod', '.crmSearchProd');
    crmNav.showHideCheckbox('search_lost', '.crmSearchLost');
    crmNav.showHideCheckbox('search_completed', '.crmSearchCompleted');
    crmNav.showHideCheckbox('search_pending', '.crmSearchPending');
  },
  startListening: function() {
    $("#crm_search").keyup(function() {
      let search = $(this).val();

      if(search.length > 0) {
        crmNav.getTree.clear();

        $(".crm_search_results").show();

        crmNav.searchAjax = $.ajax({
          type: 'POST',
          data: 'search=' + search,
          url: '/html/crm/ajax/results_tree.php',
          beforeSend: function() {
            if(crmNav.searchAjax !== null) {
              crmNav.searchAjax.abort();
            }
          },
          success: function(data) {
            crmNav.getTree.reload(JSON.parse(data));

            crmNav.searchAjax = null;
          }
        });

        /*// begin custom match for FancyTree data based on altData
        let cSearch = new RegExp($(this).val(), 'i'); // create a new insensitive regex on the search value

        // filter the nodes with a custom function
        crmNav.getTree.filterNodes(function(node) {
          return cSearch.test(node.data.altData); // return the match
        });*/
      } else {
        $(".crm_search_results").hide();
        crmNav.search = null;
        crmNav.getTree.clearFilter();
      }

      crmNav.clearMain();
    });

    $("#display_widgets").click(function() {
      $("#widget_box").toggle();
    });

    $("#searchFilter").change(function() {
      crmNav.checkFilters();
    });
  },
  showHideCheckbox: function(checkbox, element) {
    if($("#" + checkbox).is(":checked")) {
      $(element).show();
    } else {
      $(element).hide();
    }
  }
};