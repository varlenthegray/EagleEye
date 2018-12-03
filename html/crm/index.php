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
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/html/crm/js/crmCompany.min.js?v=<?php echo VERSION; ?>"></script>
<script src="/html/crm/js/crmBatch.min.js?v=<?php echo VERSION; ?>"></script>
<script src="/html/crm/js/crmMain.js?v=<?php echo VERSION; ?>"></script>
<script src="/includes/js/window-manager.min.js?v=<?php echo VERSION; ?>"></script>

<script>
  $(function() {
    crmCompany.startListening();
    winMgr.init('crmUID');
    crmMain.widgetInit();

    if(globalFunctions.getURLParams('maximized') === 'true') {
      let win = globalFunctions.getURLParams('win');

      winMgr.newAutoWin(win, true);

      winMgr.setFocus(win);
    } else {
      // default windows
      winMgr.newAutoWin('crm');
      winMgr.newAutoWin('quotes');
      winMgr.newAutoWin('production');
      winMgr.newAutoWin('operations');

      winMgr.setFocus('crm');
    }

    winMgr.getWins().attachEvent("onResizeFinish", function(win) {
      crmCompany.reInitEditor();
    });

    winMgr.getWins().attachEvent("onMaximize", function(win) {
      crmCompany.reInitEditor();
    });

    crmMain.dealerInit(); // initialize dealer listening
  });
</script>