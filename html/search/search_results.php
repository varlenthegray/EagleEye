<?php
require_once '../../includes/header_start.php';

//outputPHPErrs();

$find = sanitizeInput($_REQUEST['find']);
$exact = (bool)sanitizeInput($_REQUEST['exact']);
$rid = sanitizeInput($_REQUEST['rID']);

?>

<style>
  .search .header {
    width: 100%;
    font-size: 1.5em;
    font-weight: bold;
    border-bottom: 1px solid #000;
    display: table;
  }

  .search .header .row {
    display: table-row;
  }

  .search .header .cell {
    display: table-cell;
  }
</style>


<div class="card-box search">
  <div class="row">
    <div class="col-md-12">
      <button class="btn btn-primary waves-effect waves-light" id="btn_search_to_main"><i class="zmdi zmdi-arrow-left m-r-5"></i> <span>GO BACK</span></button><br><br>

      <div class="header">
        <div class="row">
          <div class="cell">SO: 924</div>
          <div class="cell">Dealer/Contractor: B23: Jennings</div>
          <div class="cell">Customer PO/Project Name: Jennings/Fennessy_Black</div>
          <div class="cell">Project Manager: Jennings</div>
        </div>
      </div>

      <table id="room_info" class="display" style="width:100%">
        <thead>
        <tr>
          <th>Room</th>
          <th>Sales</th>
          <th>Sample</th>
          <th>Pre-production</th>
          <th>Door/Drawer</th>
          <th>Main</th>
          <th>Custom</th>
          <th>Shipping</th>
          <th>Installation</th>
          <th>Pick/Materials</th>
          <th>Edgebanding</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<script>
$(function() {
  $("#room_info").DataTable({
    "ajax": "/html/search/ajax/room_list.php"
  });
});
</script>