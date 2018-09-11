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
        <div class="row crm-main">
          <!-- Handled via AJAX -->
          <div id="objId" style="display: none;">
            <div class="col-md-12">
              <div class="row">
                <div class="col-md-2 col-md-offset-1">
                  <div class="card-box tilebox-one tilebox-bg-red">
                    <h6 class="text-muted text-uppercase m-b-20">Leads</h6>
                    <h2 class="m-b-20">7</h2>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card-box tilebox-one tilebox-bg-orange">
                    <h6 class="text-muted text-uppercase m-b-20">To Process</h6>
                    <h2 class="m-b-20">4</h2>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card-box tilebox-one tilebox-bg-yellow">
                    <h6 class="text-muted text-uppercase m-b-20">Pending</h6>
                    <h2 class="m-b-20">3</h2>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card-box tilebox-one tilebox-bg-green">
                    <h6 class="text-muted text-uppercase m-b-20">In Progress</h6>
                    <h2 class="m-b-20">6</h2>
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="card-box tilebox-one tilebox-bg-red">
                    <h6 class="text-muted text-uppercase m-b-20">Backorders</h6>
                    <h2 class="m-b-20">4</h2>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div id="objId2" style="display: none;">
            <div style="margin: 5px 8px;">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </div>
          </div>

          <div id="objId3" style="display: none;">
            <div style="margin: 5px 8px;">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </div>
          </div>

          <div id="objId4" style="display: none;">
            <div style="margin: 5px 8px;">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
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

    w1.setText("CRM");
    w1.attachObject("objId");

    w2.setText("Widget 2");
    w2.attachObject("objId2");

    w3.setText("Widget 3");
    w3.attachObject("objId3");

    w4.setText("Widget 4");
    w4.attachObject("objId4");

    wins.attachViewportTo('crmUID');

    wins.window('w1').keepInViewport(true);
    wins.window('w2').keepInViewport(true);
    wins.window('w3').keepInViewport(true);
    wins.window('w4').keepInViewport(true);
  });
</script>