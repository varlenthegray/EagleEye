<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>
<link rel="stylesheet" type="text/css" href="/assets/plugins/dhtmlxGrid/dhtmlxgrid.css">
<script src="/assets/plugins/dhtmlxGrid/dhtmlxgrid.js"></script>

<link rel="stylesheet" type="text/css" href="/html/css/standard.min.css"/>

<div class="row">
  <div class="col-md-2 col-md-offset-1">
    <div class="card-box tilebox-one tilebox-bg-red">
      <h6 class="text-muted text-uppercase m-b-20">Request to Invoice Client</h6>
      <h2 class="m-b-20">7</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-orange">
      <h6 class="text-muted text-uppercase m-b-20">Pending Invoice Response from Client</h6>
      <h2 class="m-b-20">11</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-yellow">
      <h6 class="text-muted text-uppercase m-b-20">New Vendor Request</h6>
      <h2 class="m-b-20">11</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-orange">
      <h6 class="text-muted text-uppercase m-b-20">Review Invoice</h6>
      <h2 class="m-b-20">12</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-red">
      <h6 class="text-muted text-uppercase m-b-20">Pay Invoice</h6>
      <h2 class="m-b-20">7</h2>
    </div>
  </div>

  <div class="col-md-12">
    <div class="card-box">
      <div class="row">
        <div class="col-md-12">
          <table id="example" class="table table-striped table-bordered" style="width:100%">
            <thead>
            <tr>
              <th>Type</th>
              <th>Number</th>
              <th>Date</th>
              <th>Account</th>
              <th>Amount</th>
            </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(function() {
    $('#example').DataTable( {
      "ajax": "/html/accounting/ajax/accounting.json",
      "columns": [
        { "data": "type" },
        { "data": "number" },
        { "data": "date" },
        { "data": "account" },
        { "data": "amount" }
      ],
      "pageLength": 25
    });
  });
</script>