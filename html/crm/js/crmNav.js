var crmNav = {
  tree: $("#searchResultTree"),
  getTree: null, // assigned in init
  mainContainer: $("#crmBody"),
  search: null, // what's being searched for

  initTree: function() {
    this.tree.fancytree({
      extensions: ["filter"],
      filter: {
        counter: false,
        mode: "hide",
        highlight: false,
        autoExpand: false
      },
      source: { url: "/html/crm/ajax/cached_result_tree.json?v=2" },
      renderNode: function(event, data) {
        let node = data.node, $span = $(node.span);

        switch(node.data.orderStatus) {
          case 'N':
            $span.addClass("crmSearchLead");
            break;
          case '#':
            $span.addClass("crmSearchQuote");
            break;
          case '$':
            $span.addClass("crmSearchProd");
            break;
          case '-':
            $span.addClass("crmSearchLost");
            break;
          case '+':
            $span.addClass("crmSearchCompleted");
            break;
        }
      },
      activate: function(event, data) {
        let node = data.node;

        if(node.isFolder()) {
          crmNav.tree.fancytree("getTree").filterBranches(node.title);
        } else {
          crmNav.tree.fancytree("getTree").filterBranches(node.getParentList()[0].title);
        }

        crmCompany.getCompany($(this).attr("data-id"));
      },
      expand: function(event, data) {
        crmNav.checkFilters();
      }
    });

    crmNav.getTree = crmNav.tree.fancytree("getTree");
  },

  clearMain: function() {
    crmNav.mainContainer.html("");
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
    function showHide(checkbox, element) {
      if($("#" + checkbox).is(":checked")) {
        $(element).show();
      } else {
        $(element).hide();
      }
    }

    showHide('search_lead', '.crmSearchLead');
    showHide('search_quote', '.crmSearchQuote');
    showHide('search_prod', '.crmSearchProd');
    showHide('search_lost', '.crmSearchLost');
    showHide('search_completed', '.crmSearchCompleted');
  },

  startListening: function() {
    $("body")
      .on("keyup", "#crm_search", function() {
        if($(this).val().length > 0) {
          $(".crm_search_results").show();

          // begin custom match for FancyTree data based on altData
          let cSearch = new RegExp($(this).val(), 'i'); // create a new insensitive regex on the search value

          // filter the nodes with a custom function
          crmNav.getTree.filterNodes(function(node) {
            return cSearch.test(node.data.altData); // return the match
          });
        } else {
          $(".crm_search_results").hide();
          crmNav.search = null;
          crmNav.getTree.clearFilter();
        }

        crmNav.clearMain();
      })
      .on("click", "#display_widgets", function() {
        $("#widget_box").toggle();
      })
      .on("change", "#searchFilter", function() {
        crmNav.checkFilters();
      })
    ;
  }
};