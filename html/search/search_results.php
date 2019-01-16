<?php
require_once '../../includes/header_start.php';

//outputPHPErrs();

$find = sanitizeInput($_REQUEST['find']);

if((bool)$_SESSION['userInfo']['dealer']) {
  $dealer_filter = "AND dealer_code LIKE '{$_SESSION['userInfo']['dealer_code']}%'";
} else {
  $dealer_filter = null;
}

$so_qry = $dbconn->query("SELECT * FROM sales_order so 
  LEFT JOIN dealers d ON so.dealer_code = d.dealer_id
WHERE (so.so_num LIKE '%$find%' OR LOWER(so.dealer_code) LIKE LOWER('%$find%') OR LOWER(so.project_name) LIKE LOWER('%$find%') 
      OR LOWER(so.project_mgr) LIKE LOWER('%$find%') OR LOWER(so.name_1) LIKE LOWER('%$find%') OR LOWER(so.name_2) LIKE LOWER('%$find%') 
      OR LOWER(d.dealer_name) LIKE LOWER('%$find%')) $dealer_filter
ORDER BY so_num DESC");
?>

<style>
  .so_result > .header {
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

  .table-row-hover > tbody > tr:not(.no-row-hover):hover  {
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

  table.dataTable td {
    box-sizing: border-box;
  }

  .so_result {
    margin-bottom: 30px;
    border: 1px solid #000;
  }

  .edit_so {
    border-bottom: 1px solid #000;
    display: none;
  }
</style>

<div class="card-box search">
  <div class="row">
    <div class="col-md-12">
      <button class="btn btn-primary waves-effect waves-light" id="btn_search_to_main"><i class="zmdi zmdi-arrow-left m-r-5"></i> <span>GO BACK</span></button><br><br>

      <?php
      if($so_qry->num_rows > 0) {
        while($so = $so_qry->fetch_assoc()) {
          echo <<<HEREDOC
          <div class="so_result">
            <div class="header sticky" style="top:85px;">
              <div class="row">
                <div class="cell">
                  <button class="btn waves-effect btn-primary" id="edit_so" data-sonum="{$so['so_num']}"> <i class="zmdi zmdi-edit"></i> </button> 
                  <button class="btn btn-primary-outline waves-effect add_room_trigger" data-sonum="{$so['so_num']}" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add additional room" style="font-size:10px;width:23px;height:22px;margin-top:1px;padding:0;"> +X</button>
                </div>
                <div class="cell">SO: {$so['so_num']}</div>
                <div class="cell">Dealer/Contractor: {$so['dealer_code']}: {$so['contact']}</div>
                <div class="cell">Customer PO/Project Name: {$so['project_name']}</div>
                <div class="cell">Project Manager: {$so['contact']}</div>
              </div>
            </div>
            
            <div class="edit_so"></div>
      
            <table class="display compact table-row-hover room_results" id="room_results_{$so['so_num']}" data-so-num="{$so['so_num']}" style="width:100%">
              <colgroup>
                <col width="120px">
                <col width="*">
                <col width="5%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
                <col width="8%">
              </colgroup>
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
HEREDOC;
        }
      }
      ?>
    </div>
  </div>
</div>

<!-- Add Room modal -->
<div id="modalAddRoom" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddRoomLabel" aria-hidden="true">
  <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<script>
var searchTable = [];

$(function() {
  var room_table = $(".room_results");

  room_table.each(function(i) {
    let so = $(this).attr('data-so-num');

    searchTable[so] = $("#room_results_" + so).DataTable({
      ajax: {
        "url": "/html/search/ajax/room_list.php?so_id=" + so,
        "dataSrc": ""
      },
      columns: [
        {data: 'room_actions'},
        {data: 'room_details'},
        {data: 'order_status'},
        {data: 'sales_marketing_bracket.job_title'},
        {data: 'shop_bracket.job_title'},
        {data: 'preproduction_bracket.job_title'},
        {data: 'press_bracket.job_title'},
        {data: 'paint_bracket.job_title'},
        {data: 'custom_bracket.job_title'},
        {data: 'shipping_bracket.job_title'},
        {data: 'assembly_bracket.job_title'},
        {data: 'welding_bracket.job_title'}
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
          checkCol('sales_marketing_bracket', 3);
          checkCol('shop_bracket', 4);
          checkCol('preproduction_bracket', 5);
          checkCol('press_bracket', 6);
          checkCol('paint_bracket', 7);
          checkCol('custom_bracket', 8);
          checkCol('shipping_bracket', 9);
          checkCol('assembly_bracket', 10);
          checkCol('welding_bracket', 11);
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
});
</script>