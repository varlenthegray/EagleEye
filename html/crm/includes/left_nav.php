<div class="col-md-2" style="min-height:80vh;">
  <div class="card-box">
    <div class="row">
      <div class="col-md-12 m-b-10">
        <input type="text" placeholder="Search CRM..." class="form-control ignoreSaveAlert" id="crm_search" name="crm_search" autocomplete="off">
      </div>

      <div class="col-md-12">
        <nav class="vert-nav">
          <ul>
            <li><a href="#"><i class="fa fa-fw fa-user-plus"></i> Add Company</a></li>
            <li><a href="#" class="nav_add_contact"><i class="fa fa-fw fa-plus-circle"></i> Add Contact</a></li>
            <li><a href="/main.php?page=crm/company_list" onClick="companies.getCompanyList('.bottom-container');"><i class="fa fa-fw fa-building-o"></i> Company List</a></li>
          </ul>
        </nav>
      </div>

      <div class="col-md-12 m-t-10 crm_search_results" style="display:none;">
        <h3>Search Results</h3>
        <div id="searchResultTree">
          <ul id="searchResultTreeData" style="display: none;">
            <li id="id3" class="folder">Distinctive Cabinetry
              <ul>
                <li id="id3.1"><strong>907 - Miller</strong>
                  <ul>
                    <li id="id3.1.1">A1.01 - Bath 1
                    <li id="id3.1.2">A1.02 - Bath 2 Vanity
                    <li id="id3.1.3">B1.01 - Kitchen
                  </ul>
                <li id="id3.2"><strong>923 - Donnely</strong>
                  <ul>
                    <li id="id3.2.1">A1.01 - Plane Wood
                  </ul>
              </ul>
            </li>
            <li id="id3" class="folder">Distinctive Vision
              <ul>
                <li id="id3.1"><strong>907 - Miller</strong>
                  <ul>
                    <li id="id3.1.1">A1.01 - Bath 1
                    <li id="id3.1.2">A1.02 - Bath 2 Vanity
                    <li id="id3.1.3">B1.01 - Kitchen
                  </ul>
                <li id="id3.2"><strong>923 - Donnely</strong>
                  <ul>
                    <li id="id3.2.1">A1.01 - Plane Wood
                  </ul>
              </ul>
            </li>
            <li id="id3" class="folder">Distinctive Radio
              <ul>
                <li id="id3.1"><strong>907 - Miller</strong>
                  <ul>
                    <li id="id3.1.1">A1.01 - Bath 1
                    <li id="id3.1.2">A1.02 - Bath 2 Vanity
                    <li id="id3.1.3">B1.01 - Kitchen
                  </ul>
                <li id="id3.2"><strong>923 - Donnely</strong>
                  <ul>
                    <li id="id3.2.1">A1.01 - Plane Wood
                  </ul>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $("#searchResultTree").fancytree({
    extensions: ["filter"],
    filter: {
      counter: false,
      mode: "hide",
      highlight: false
    },
    activate: function(event, data) {
      let node = data.node;
      let tree = $("#searchResultTree").fancytree("getTree");

      if(node.isFolder()) {
        tree.filterBranches(node.title);
      }
    }
  });

  $("body").on("keyup", "#crm_search", function() {
    if($(this).val().length > 0) {
      $(".crm_search_results").show();
    } else {
      $(".crm_search_results").hide();
    }
  });
</script>