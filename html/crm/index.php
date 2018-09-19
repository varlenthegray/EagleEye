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

<script>
  crmCompany.startListening();

  $("#left-nav").resizable();

  var wins, w1, w2, w3, w4;
  var winHolder = $("#crmUID");
  var winPos = {};

  $(function() {
    let containerWidth;
    let containerHeight;

    wins = new dhtmlXWindows();

    if(winHolder.width() > 320 && winHolder.height() > 240) {
      containerWidth = winHolder.outerWidth() / 2;
      containerHeight = winHolder.outerHeight() / 2;

      winPos.w1 = {};
      winPos.w2 = {};
      winPos.w3 = {};
      winPos.w4 = {};

      winPos.w1.x = 0;
      winPos.w1.y = 0;

      winPos.w2.x = containerWidth;
      winPos.w2.y = 0;

      winPos.w3.x = 0;
      winPos.w3.y = containerHeight;

      winPos.w4.x = containerWidth;
      winPos.w4.y = containerHeight;
    } else {
      containerWidth = 320;
      containerHeight = 240;
    }

    w1 = wins.createWindow("w1", winPos.w1.x, winPos.w1.y, containerWidth, containerHeight);
    w2 = wins.createWindow("w2", winPos.w2.x, winPos.w2.y, containerWidth, containerHeight);
    w3 = wins.createWindow("w3", winPos.w3.x, winPos.w3.y, containerWidth, containerHeight);
    w4 = wins.createWindow("w4", winPos.w4.x, winPos.w4.y, containerWidth, containerHeight);

    wins.window('w1').addUserButton('popout', 0, 'Pop Out');
    wins.window('w2').addUserButton('popout', 0, 'Pop Out');
    wins.window('w3').addUserButton('popout', 0, 'Pop Out');
    wins.window('w4').addUserButton('popout', 0, 'Pop Out');

    w1.button('popout').attachEvent("onClick", function() {
      // TODO: Make functional

      return false;
    });

    w1.setText("CRM");
    w1.attachObject("objId");

    w2.setText("Quotes");
    w2.attachObject("objId2");

    w3.setText("Production");
    w3.attachObject("objId3");

    w4.setText("Active Operations");
    w4.attachObject("objId4");

    wins.attachViewportTo('crmUID');

    wins.window('w1').keepInViewport(true);
    wins.window('w2').keepInViewport(true);
    wins.window('w3').keepInViewport(true);
    wins.window('w4').keepInViewport(true);

    wins.attachEvent("onResizeFinish", function(win) {
      crmCompany.reInitEditor();
    });

    wins.attachEvent("onMaximize", function(win) {
      crmCompany.reInitEditor();
    });

    wins.attachEvent("onFocus", function(win) {
      $(".request_header").text(win.getText());
    });

    wins.window('w1').bringToTop();

    var quote_table = $("#quote_global_table").DataTable({
      "ajax": "/ondemand/display_actions.php?action=display_quotes",
      "createdRow": function (row, data, dataIndex) {
        $(row).addClass("cursor-hand view_so_info").attr('data-project-name', data.project_name);
      },
      "paging": false,
      scrollY: '30vh',
      scrollCollapse: true,
      "dom": '<"#quote_header.dt-custom-header">tipr',
      "order": [[0, "asc"]]
    });

    var order_table = $("#orders_global_table").DataTable({
      "ajax": "/ondemand/display_actions.php?action=display_orders",
      "createdRow": function (row, data, dataIndex) {
        $(row).addClass("cursor-hand view_so_info").attr('data-project-name', data.project_name);
      },
      "paging": false,
      scrollY: '30vh',
      scrollCollapse: true,
      "dom": '<"#order_header.dt-custom-header">tipr',
      "order": [[0, "asc"]]
    });

    var active_table = $("#active_ops_global_table").DataTable({
      "ajax": "/ondemand/display_actions.php?action=display_ind_active_jobs",
      "createdRow": function(row,data,dataIndex) {
        $(row).addClass("cursor-hand view_so_info").attr('data-project-name', data.project_name);
      },
      "paging": false,
      scrollY: '31.5vh',
      scrollCollapse: true,
      "dom": '<"#active_header.dt-custom-header">tipr',
      "columnDefs": [
        {"targets": [0], "orderable": false, className: "nowrap"}
      ],
      "order": [[1, "desc"]]
    });
  });
</script>