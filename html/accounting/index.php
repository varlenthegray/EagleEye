<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>
<link rel="stylesheet" type="text/css" href="/assets/plugins/dhtmlxGrid/dhtmlxgrid.css">
<script src="/assets/plugins/dhtmlxGrid/dhtmlxgrid.js"></script>

<style>
  .vert-nav ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    width: 100%;
    background-color: #666666;
    border-radius: 5px;
  }

  .vert-nav ul li {
    border-top: 1px solid #FFF;
  }

  .vert-nav ul li:first-child {
    border-top: none;
  }

  .vert-nav ul li a {
    display: block;
    color: #FFFFFF;
    font-weight: bold;
    text-decoration: none;
    padding: 4px;
    border-radius: 5px;
    font-size: 1.2em;
  }

  .vert-nav ul li a i {
    padding-right: 4px;
  }

  .vert-nav ul li a:hover {
    background-color: #333333;
  }

  .tilebox-bg-light-blue {
    /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#e9f6fd+0,d3eefb+100;Blue+3D+%233 */
    background: #e9f6fd; /* Old browsers */
    background: -moz-linear-gradient(top, #e9f6fd 0%, #d3eefb 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, #e9f6fd 0%,#d3eefb 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, #e9f6fd 0%,#d3eefb 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#e9f6fd', endColorstr='#d3eefb',GradientType=0 ); /* IE6-9 */
  }

  .tilebox-bg-light-green {
    /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#efffed+0,d7efcb+100 */
    background: #efffed; /* Old browsers */
    background: -moz-linear-gradient(top, #efffed 0%, #d7efcb 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, #efffed 0%,#d7efcb 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, #efffed 0%,#d7efcb 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#efffed', endColorstr='#d7efcb',GradientType=0 ); /* IE6-9 */
  }

  .tilebox-bg-light-yellow {
    /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#ffffed+0,f2f2dc+100 */
    background: #ffffed; /* Old browsers */
    background: -moz-linear-gradient(top, #ffffed 0%, #f2f2dc 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, #ffffed 0%,#f2f2dc 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, #ffffed 0%,#f2f2dc 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffed', endColorstr='#f2f2dc',GradientType=0 ); /* IE6-9 */
  }

  .tilebox-bg-light-red {
    /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#ffeded+0,ead3d3+100 */
    background: #ffeded; /* Old browsers */
    background: -moz-linear-gradient(top, #ffeded 0%, #ead3d3 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, #ffeded 0%,#ead3d3 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, #ffeded 0%,#ead3d3 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffeded', endColorstr='#ead3d3',GradientType=0 ); /* IE6-9 */
  }

  .tilebox-bg-gray {
    /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#c9ccbf+0,b0b2a7+100 */
    background: #c9ccbf; /* Old browsers */
    background: -moz-linear-gradient(top, #c9ccbf 0%, #b0b2a7 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top, #c9ccbf 0%,#b0b2a7 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom, #c9ccbf 0%,#b0b2a7 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#c9ccbf', endColorstr='#b0b2a7',GradientType=0 ); /* IE6-9 */
  }
</style>

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