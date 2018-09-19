<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<link rel="stylesheet" type="text/css" href="/html/css/standard.min.css?v=<?php echo VERSION; ?>"/>
<link rel="stylesheet" type="text/css" href="/html/crm/css/crm.min.css?v=<?php echo VERSION; ?>" />

<div class="row crm">
  <?php require_once 'includes/left_nav.php'; ?>

  <div class="bottom-container">
    <div class="col-md-10">
      <div id="crmUID" style="min-height:86vh;">
        <div class="crm-main">
          <!-- Handled via AJAX -->
          <div id="objId" style="display:none;width:100%;height:100%;overflow:auto;">
            <div class="col-md-12">
              <div class="row" style="width:100%">
                <div class="col-md-2 col-md-offset-1">
                  <div class="card-box tilebox-one tilebox-bg-red">
                    <h6 class="text-muted text-uppercase m-b-10">Leads</h6>
                    <div class="summary_info">
                      <ul>
                        <li>John: <strong>7</strong></li>
                        <li>Bill: <strong>10</strong></li>
                        <li>Joe: <strong>3</strong></li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card-box tilebox-one tilebox-bg-orange">
                    <h6 class="text-muted text-uppercase m-b-10">To Process</h6>
                    <div class="summary_info">
                      <ul>
                        <li>John: <strong>1</strong></li>
                        <li>Bill: <strong>3</strong></li>
                        <li>Joe: <strong>0</strong></li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card-box tilebox-one tilebox-bg-yellow">
                    <h6 class="text-muted text-uppercase m-b-10">Pending</h6>
                    <div class="summary_info">
                      <ul>
                        <li>John: <strong>2</strong></li>
                        <li>Bill: <strong>2</strong></li>
                        <li>Joe: <strong>3</strong></li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card-box tilebox-one tilebox-bg-green">
                    <h6 class="text-muted text-uppercase m-b-10">In Progress</h6>
                    <div class="summary_info">
                      <ul>
                        <li>John: <strong>6</strong></li>
                        <li>Bill: <strong>4</strong></li>
                        <li>Joe: <strong>2</strong></li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card-box tilebox-one tilebox-bg-red">
                    <h6 class="text-muted text-uppercase m-b-10">Backorders</h6>
                    <div class="summary_info">
                      <ul>
                        <li>John: <strong>1</strong></li>
                        <li>Bill: <strong>1</strong></li>
                        <li>Joe: <strong>0</strong></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row" id="crmBody" style="width:100%">

              </div>
            </div>
          </div>

          <div id="objId2" style="display: none;">
            <div class="row">
              <div class="col-md-12">
                <div class="card-box table-responsive" style="min-height:42vh;">
                  <table id="quote_global_table" class="table table-striped table-bordered" width="100%">
                    <thead>
                    <tr>
                      <th width="5%">SO#</th>
                      <th width="40%">Project</th>
                      <th>Sales</th>
                      <th>Sample</th>
                    </tr>
                    </thead>
                    <tbody id="quote_table"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div id="objId3" style="display: none;">
            <div class="row">
              <div class="col-md-12">
                <div class="card-box table-responsive" style="min-height:42vh;">
                  <table id="orders_global_table" class="table table-striped table-bordered" width="100%">
                    <thead>
                    <tr>
                      <th width="5%">SO#</th>
                      <th width="40%">Project</th>
                      <th>Pre-Production</th>
                      <th>Main</th>
                    </tr>
                    </thead>
                    <tbody id="orders_table"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div id="objId4" style="display: none;">
            <div class="row">
              <div class="col-md-12">
                <div class="card-box table-responsive" style="min-height:42vh;">
                  <table id="active_ops_global_table" class="table table-striped table-bordered" width="100%">
                    <thead>
                    <tr>
                      <th style="min-width:20px;">&nbsp;</th>
                      <th width="35px">SO#</th>
                      <th width="125px">Room</th>
                      <th width="125px">Department</th>
                      <th width="225px">Operation</th>
                      <th width="100px">Activated At</th>
                      <th width="90px">Time In</th>
                    </tr>
                    </thead>
                    <tbody id="active_ops_table"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/html/crm/js/crmCompany.min.js"></script>
<script src="/html/crm/js/crmMain.js"></script>
<script src="/includes/js/window-manager.min.js"></script>

<script>
  crmCompany.startListening();

  $("#left-nav").resizable();

  $(function() {
    winMgr.init();

    winMgr.newWin('crm', 1, 'CRM', 'objId');

    // setup windows and then init them (datatables initialization)
    winMgr.newWin('quotes', 2, 'Quotes', 'objId2'); crmMain.initQuote();
    winMgr.newWin('production', 3, 'Production', 'objId3'); crmMain.initProduction();
    winMgr.newWin('activeOps', 4, 'Active Operations', 'objId4'); crmMain.initActiveOps();

    winMgr.setFocus('crm');

    winMgr.getWins().attachEvent("onFocus", function(win) {
      $(".request_header").text(win.getText());
    });

    crmMain.startCompanyResizeWatcher();

    /*w1.button('popout').attachEvent("onClick", function() {
      // TODO: Make functional

      return false;
    });*/
  });
</script>