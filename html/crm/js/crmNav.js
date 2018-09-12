var crmNav = {
  tree: $("#searchResultTree"),
  getTree: null, // assigned in init
  mainContainer: $("#crmBody"),

  initTree: function() {
    this.tree.fancytree({
      extensions: ["filter"],
      filter: {
        counter: false,
        mode: "hide",
        highlight: false
      },
      activate: function(event, data) {
        let node = data.node;

        if(node.isFolder()) {
          crmNav.tree.fancytree("getTree").filterBranches(node.title);
        }
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

  startListening: function() {
    $("body")
      .on("keyup", "#crm_search", function() {
        if($(this).val().length > 0) {
          $(".crm_search_results").show();
        } else {
          $(".crm_search_results").hide();
        }

        crmNav.clearMain();
      })
    ;
  }
};