<style>
  .searchBorderTopContact:first-of-type {
    border-top: 1px solid #000;
  }
</style>

<div class="col-md-2" id="left-nav" style="min-height:80vh;">
  <div class="card-box">
    <div class="row">
      <div class="col-md-12 m-b-10">
        <i class="fa fa-bars fa-2x cursor-hand pull-left" id="display_widgets"></i> <h3 class="pull-left request_header">CRM</h3>
      </div>

      <div class="col-md-12 m-b-10 widget_box" id="widget_box" style="display:none;">
        <div class="col-md-3 cursor-hand widget-item widget-active" data-window="crm"><i class="fa fa-book fa-3x"></i><h5>CRM</h5></div>
        <div class="col-md-3 cursor-hand widget-item widget-active" data-window="production"><i class="fa fa-sitemap fa-3x"></i><h5>Production</h5></div>
        <div class="col-md-3 cursor-hand widget-item widget-active" data-window="pendingOps"><i class="fa fa-hourglass-start fa-3x"></i><h5>Pend. Ops</h5></div>
        <div class="col-md-3 cursor-hand widget-item widget-active" data-window="activeOps"><i class="fa fa-hourglass-end fa-3x"></i><h5>Act. Ops</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="quotes"><i class="fa fa-pencil-square-o fa-3x"></i><h5>Quotes</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="reports"><i class="fa fa-inbox fa-3x"></i><h5>Reports</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="calendar"><i class="fa fa-calendar fa-3x"></i><h5>Calendar</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="inventory"><i class="fa fa-barcode fa-3x"></i><h5>Inventory</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="accounting"><i class="fa fa-bank fa-3x"></i><h5>Accounting</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="email"><i class="fa fa-envelope-o fa-3x"></i><h5>Email</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="feedback"><i class="fa fa-envelope-square fa-3x"></i><h5>Feedback</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="opl"><i class="fa fa-list fa-3x"></i><h5>OPL</h5></div>
        <div class="col-md-3 cursor-hand widget-item" data-window="dealers"><i class="fa fa-share-alt fa-3x"></i><h5>Dealers</h5></div>
<!--        <div class="col-md-3 cursor-hand widget-item" data-window="database"><i class="fa fa-server fa-3x"></i><h5>Database</h5></div>-->
<!--        <div class="col-md-3 cursor-hand widget-item" data-window="setup"><i class="fa fa-cogs fa-3x"></i><h5>Setup</h5></div>-->
<!--        <div class="col-md-3 cursor-hand widget-item" data-window="documents"><i class="fa fa-file-pdf-o fa-3x"></i><h5>Documents</h5></div>-->
      </div>
    </div>

    <div class="row">
      <div class="col-md-12 m-b-10">
        <input type="text" placeholder="Search CRM..." class="form-control ignoreSaveAlert" id="crm_search" name="crm_search" autocomplete="off">
        <a class="cursor-hand" id="clear-search" onclick="crmNav.clearAll();"><i class="zmdi zmdi-close"></i></a>
      </div>

      <div class="col-md-12">
        <nav class="vert-nav">
          <ul>
            <li><a href="#" class="nav_add_new"><i class="fa fa-fw fa-plus-circle"></i> Add New</a></li>
<!--            <li><a href="#" class="nav_add_contact"><i class="fa fa-fw fa-plus-circle"></i> Add Contact</a></li>-->
<!--            <li><a href="#" class="crm_view_company_list"><i class="fa fa-fw fa-building-o"></i> Company List</a></li>-->
          </ul>
        </nav>
      </div>

      <div class="col-md-12 m-t-10 crm_search_results" style="display:none;">
        <h3>Search Results</h3>

        <div class="col-md-12">
          <form id="searchFilter">
            <table width="100%">
              <tr>
                <td><div class="checkbox checkbox-primary"><input id="search_lead" type="checkbox" checked><label for="search_lead"> <i class="fa fa-fw fa-fire"></i> Lead</label></div></td>
                <td><div class="checkbox checkbox-primary"><input id="search_quote" type="checkbox" checked><label for="search_quote"> <i class="fa fa-fw fa-flag-o"></i> Quote</label></div></td>
                <td><div class="checkbox checkbox-primary"><input id="search_prod" type="checkbox" checked><label for="search_prod"> <i class="fa fa-fw fa-sitemap"></i> Production</label></div></td>
              </tr>
              <tr>
                <td><div class="checkbox checkbox-primary"><input id="search_lost" type="checkbox"><label for="search_lost"> <i class="fa fa-fw fa-thumbs-o-down"></i> Lost</label></div></td>
                <td><div class="checkbox checkbox-primary"><input id="search_completed" type="checkbox"><label for="search_completed"> <i class="fa fa-fw fa-thumbs-up"></i> Completed</label></div></td>
                <td><div class="checkbox checkbox-primary"><input id="search_pending" type="checkbox"><label for="search_pending"> <i class="fa fa-fw fa-hourglass-3"></i> Pending</label></div></td>
              </tr>
              <tr>
                <td><div class="checkbox checkbox-primary"><input id="search_hold" type="checkbox"><label for="search_hold"> <i class="fa fa-fw fa-stop-circle"></i> Hold</label></div></td>
                <td><div class="checkbox checkbox-primary"><input id="search_pillar_missing" type="checkbox"><label for="search_pillar_missing"> <i class="fa fa-fw fa-exclamation"></i> Pillar Missing</label></div></td>
                <td><div class="checkbox checkbox-primary"><input id="search_referred" type="checkbox"><label for="search_referred"> <i class="fa fa-fw fa-mail-forward"></i> Referred</label></div></td>
              </tr>
            </table>
          </form>
        </div>

        <div class="col-md-12">
          <div id="searchResultTree" class="m-t-10"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/html/crm/js/crmNav.min.js?v=<?php echo VERSION; ?>"></script>

<script>
  // initialize the tree for searching
  crmNav.initTree();
  crmNav.startListening();
</script>