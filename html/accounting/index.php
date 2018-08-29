<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>
<link rel="stylesheet" type="text/css" href="/assets/plugins/dhtmlxGrid/dhtmlxgrid.css">
<script src="/assets/plugins/dhtmlxGrid/dhtmlxgrid.js"></script>

<link rel="stylesheet" type="text/css" href="/html/css/standard.min.css"/>

<div class="row">
  <div class="col-md-2">
    <div class="card-box">
      <div class="row">
        <div class="col-md-12">
          <nav class="vert-nav">
            <ul>
              <li><a href="#"><i class="fa fa-fw fa-pie-chart"></i> Summary</a></li>
              <li><a href="#"><i class="fa fa-fw fa-line-chart"></i> Expenses</a></li>
              <li><a href="#"><i class="fa fa-fw fa-shopping-cart"></i> Vendors</a></li>
              <li><a href="#"><i class="fa fa-fw fa-file-text"></i> Invoices</a></li>
              <li><a href="#"><i class="fa fa-fw fa-group"></i> Payroll</a></li>
              <li><a href="#"><i class="fa fa-fw fa-money"></i> Taxes</a></li>
              <li><a href="#"><i class="fa fa-fw fa-list"></i> Reports</a></li>
              <li><a href="#"><i class="fa fa-fw fa-cog"></i> Management</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-light-blue">
      <i class="icon-clock pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Quotes</h6>
      <h2 class="m-b-20">7</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-light-green">
      <i class="icon-note pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Orders</h6>
      <h2 class="m-b-20">11</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-light-yellow">
      <i class="icon-drawar pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Open Invoices</h6>
      <h2 class="m-b-20">11</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-light-red">
      <i class="icon-ban pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Overdue Invoices</h6>
      <h2 class="m-b-20">12</h2>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card-box tilebox-one tilebox-bg-light-green">
      <i class="icon-check pull-right text-muted"></i>
      <h6 class="text-muted text-uppercase m-b-20">Paid Invoices</h6>
      <h2 class="m-b-20">7</h2>
    </div>
  </div>

  <div class="col-md-10">
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