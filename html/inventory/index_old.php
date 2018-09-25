<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<link rel="stylesheet" type="text/css" href="/html/css/standard.min.css"/>

<div class="row">
  <div class="col-md-2">
    <div class="card-box">
      <div class="row">
        <div class="col-md-12">
          <nav class="vert-nav">
            <ul>
              <li><a href="#"><i class="fa fa-fw fa-pie-chart"></i> Summary</a></li>
              <li><a href="#"><i class="fa fa-fw fa-industry"></i> Reconcile</a></li>
              <li><a href="#"><i class="fa fa-fw fa-exclamation-triangle"></i> Alerts</a></li>
              <li><a href="#"><i class="fa fa-fw fa-line-chart"></i> Trends</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-red">
      <i class="icon-clock pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Requests</h6>
      <h2 class="m-b-20">7</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-orange">
      <i class="icon-note pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Tickets to Submit</h6>
      <h2 class="m-b-20">11</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-yellow">
      <i class="icon-drawar pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Pending Acknowledgements</h6>
      <h2 class="m-b-20">11</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-green">
      <i class="icon-location-pin pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Pending Delivery</h6>
      <h2 class="m-b-20">12</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-red">
      <i class="icon-check pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Backorders</h6>
      <h2 class="m-b-20">7</h2>
    </div>
  </div>

  <div class="col-md-10">
    <div class="card-box">
      <div class="row">
        <div class="col-md-2 inv_folder"></div>

        <div class="col-md-10">
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