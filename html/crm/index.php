<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<link rel="stylesheet" type="text/css" href="/html/css/standard.min.css"/>

<div class="row crm">
  <?php require_once 'includes/left_nav.php'; ?>

  <div class="bottom-container">
    <div class="col-md-10">
      <h6 style="color:#FFF;">CRM</h6>
    </div>

    <div class="col-md-2">
      <div class="card-box tilebox-one tilebox-bg-red">
        <i class="icon-clock pull-right text-muted"></i>
        <h6 class="text-muted text-uppercase m-b-20">Leads</h6>
        <h2 class="m-b-20">7</h2>
      </div>
    </div>

    <div class="col-md-2">
      <div class="card-box tilebox-one tilebox-bg-orange">
        <i class="icon-location-pin pull-right text-muted"></i>
        <h6 class="text-muted text-uppercase m-b-20">To Process</h6>
        <h2 class="m-b-20">4</h2>
      </div>
    </div>

    <div class="col-md-2">
      <div class="card-box tilebox-one tilebox-bg-yellow">
        <i class="icon-clock pull-right text-muted"></i>
        <h6 class="text-muted text-uppercase m-b-20">Pending Response</h6>
        <h2 class="m-b-20">3</h2>
      </div>
    </div>

    <div class="col-md-2">
      <div class="card-box tilebox-one tilebox-bg-green">
        <i class="icon-drawar pull-right text-muted"></i>
        <h6 class="text-muted text-uppercase m-b-20">In Progress</h6>
        <h2 class="m-b-20">6</h2>
      </div>
    </div>

    <div class="col-md-2">
      <div class="card-box tilebox-one tilebox-bg-red">
        <i class="icon-check pull-right text-muted"></i>
        <h6 class="text-muted text-uppercase m-b-20">Backorders</h6>
        <h2 class="m-b-20">4</h2>
      </div>
    </div>

    <div class="col-md-10">
      <div class="card-box" style="height:77vh;">
      </div>
    </div>
  </div>
</div>

<script src="/html/crm/js/companies.min.js"></script>

<script>
  $(function() {
    $("body").on("click", "#searchResultTree", function() {
      companies.setContainer('.bottom-container');
      companies.getCompany(2);
    });
  });
</script>