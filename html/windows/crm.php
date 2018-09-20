<?php
require_once '../../includes/header_start.php';
?>

<div style="width:100%;height:100%;overflow:auto;">
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

    <div class="row crmBodyClass" id="crmBody" style="width:100%">

    </div>
  </div>
</div>

<script>
  crmCompany.startListening();

  $(function() {
    crmMain.setBody($('.crmBodyClass'));
  });
</script>