<?php
require_once '../../includes/header_start.php';

//outputPHPErrs();

$find = sanitizeInput($_REQUEST['find']);
$exact = (bool)sanitizeInput($_REQUEST['exact']);
$rid = sanitizeInput($_REQUEST['rID']);

$so_qry = $dbconn->query("SELECT * FROM sales_order so 
  LEFT JOIN dealers d ON so.dealer_code = d.dealer_id
WHERE so.so_num LIKE '%$find%' OR LOWER(so.dealer_code) LIKE LOWER('%$find%') OR LOWER(so.project_name) LIKE LOWER('%$find%') 
      OR LOWER(so.project_mgr) LIKE LOWER('%$find%') OR LOWER(so.name_1) LIKE LOWER('%$find%') OR LOWER(so.name_2) LIKE LOWER('%$find%') 
      OR LOWER(d.dealer_name) LIKE LOWER('%$find%')
ORDER BY so_num DESC");

if($so_qry->num_rows > 0) {
  $so = $so_qry->fetch_assoc();
}
?>

<style>
  .search .header {
    width: 100%;
    font-size: 1.5em;
    font-weight: bold;
    border-bottom: 1px solid #000;
    display: table;
    background-color: #FFF;
    z-index: 2;
  }

  .search .header .row {
    display: table-row;
  }

  .search .header .cell {
    display: table-cell;
  }

  table.dataTable tbody td {
    padding: 2px;
  }

  .table-row-hover tbody tr:hover {
    background-color: rgba(128, 128, 128, .25);
  }

  .col-red {
    background-color: rgba(255,0,0,.75);
  }

  .col-gray {
    background-color: rgba(192,192,192,.75);
  }

  .col-green {
    background-color: rgba(0,255,0,.6);
  }
</style>

<div class="card-box search">
  <div class="row">
    <div class="col-md-12">
      <button class="btn btn-primary waves-effect waves-light" id="btn_search_to_main"><i class="zmdi zmdi-arrow-left m-r-5"></i> <span>GO BACK</span></button><br><br>

      <div class="header sticky" style="top:85px;">
        <div class="row">
          <div class="cell">SO: <?php echo $so['so_num']; ?></div>
          <div class="cell">Dealer/Contractor: <?php echo "{$so['dealer_code']}: {$so['dealer_name']}" ?></div>
          <div class="cell">Customer PO/Project Name: <?php echo $so['project_name']; ?></div>
          <div class="cell">Project Manager: <?php echo $so['contact']; ?></div>
        </div>
      </div>

      <table id="room_info" class="display compact table-row-hover" style="width:100%">
        <thead>
        <tr>
          <th></th>
          <th>Room</th>
          <th>Status</th>
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
var searchTable = null;

$(function() {
  searchTable = $("#room_info").DataTable({
    ajax: {
      "url": "/html/search/ajax/room_list.php?so_id=<?php echo $so['so_num']; ?>",
      "dataSrc": ""
    },
    columns: [
      {data: 'room_actions'},
      {data: 'room_details'},
      {data: 'order_status'},
      {data: 'sales_bracket.job_title'},
      {data: 'sample_bracket.job_title'},
      {data: 'preproduction_bracket.job_title'},
      {data: 'doordrawer_bracket.job_title'},
      {data: 'main_bracket.job_title'},
      {data: 'custom_bracket.job_title'},
      {data: 'shipping_bracket.job_title'},
      {data: 'install_bracket.job_title'},
      {data: 'pick_materials_bracket.job_title'},
      {data: 'edgebanding_bracket.job_title'}
    ],
    createdRow: function(row, data, index) {
      // determines all bracket coloring
      function checkBrackets() {
        function checkCol(colName, colNum) { // actual function to determine coloring for each bracket set
          if(data[colName]['published'] === true) { // if it's published
            $('td', row).eq(colNum).addClass('col-green'); // mark the column as green
          }
        }

        // checks each column
        checkCol('sales_bracket', 3);
        checkCol('sample_bracket', 4);
        checkCol('preproduction_bracket', 5);
        checkCol('doordrawer_bracket', 6);
        checkCol('main_bracket', 7);
        checkCol('custom_bracket', 8);
        checkCol('shipping_bracket', 9);
        checkCol('install_bracket', 10);
        checkCol('pick_materials_bracket', 11);
        checkCol('edgebanding_bracket', 12);
      }

      // for the order status, check and color based on that
      switch(data['order_status']) {
        case 'Quote': // if quote
        case 'Production': // or production
        case 'Inquiry': // or inquiry
        case 'Pending': // or pending
          checkBrackets(); // simply color the brackets
          break;
        case 'Pillar Missing': // if it's pillar missing
          $('td', row).addClass('col-red'); // add red to the row
          checkBrackets(); // then color the brackets
          break;
        default: // otherwise we're setting it to gray
          $('td', row).addClass('col-gray');
          break;
      }

      // set the status column to bold
      $('td', row).eq(2).addClass('font-weight-bold');
    },
    paging: false,
    info: false,
    searching: false,
    ordering: false,
    scrollY: "74.7vh",
    scrollCollapse: true
  });


});
</script>