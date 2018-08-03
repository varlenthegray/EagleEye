<?php
require_once '../../includes/header_start.php';

$find = sanitizeInput($_REQUEST['find']);
?>

<style>
  td.details-control {
    background: url('/assets/plugins/datatables/resources/details_open.png') no-repeat center center;
    cursor: pointer;
  }
  tr.shown td.details-control {
    background: url('/assets/plugins/datatables/resources/details_close.png') no-repeat center center;
  }
</style>

<link href="/html/search/css/so_list.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />

<div class="card-box">
  <div class="row">
    <div class="col-md-12">
      <table id="search_results" style="width:100%;">
        <colgroup>
          <col width="20px">
          <col width="55px">
          <col>
          <col width="250px">
          <col width="250px">
        </colgroup>
        <thead>
        <tr>
          <th></th>
          <th>SO #</th>
          <th>PO</th>
          <th>Project Manager</th>
          <th>Dealer/Contractor</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<script>
  var soTable;
  var roomTable;

  function getRoom(so_id) {
    return '<table id="' + so_id + '" class="room_listing" style="width:100%;">' +
      '<colgroup>' +
        '<col width="20px">' +
        '<col>' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
        '<col width="8.6%">' +
      '</colgroup>' +
      '<thead>' +
        '<tr>' +
          '<th></th>' +
          '<th>Room</th>' +
          '<th>Sales</th>' +
          '<th>Sample</th>' +
          '<th>Pre-Production</th>' +
          '<th>Door/Drawer</th>' +
          '<th>Main</th>' +
          '<th>Custom</th>' +
          '<th>Shipping</th>' +
          '<th>Installation</th>' +
          '<th>Pick/Materials</th>' +
          '<th>Edgebanding</th>' +
        '</tr>' +
      '</thead>' +
      '</table>';
  }

  $(document).ready(function() {
    soTable = $('#search_results').DataTable({
      "ajax": { "url": "/html/search/ajax/so_list.php?find=<?php echo $find; ?>", "dataSrc": ""}, // telling it dataSrc of null converts it from an object search to array
      "columns": [
        {
          "className":      'details-control',
          "orderable":      false,
          "data":           null,
          "defaultContent": ''
        },
        { "data": "so_num" },
        { "data": "project_name" },
        { "data": "contact" },
        { "data": function(data) { return data.dealer_id + ": " + data.dealer_name} }
      ],
      "order": [[1, 'asc']],
      "createdRow": function(row, data, index) {
        soTable.row(':eq(' + index + ')').child(getRoom(data.soID)).show();

        setTimeout(function() {
          roomTable = $("#" + data.soID).DataTable({
            "ajax": { "url": "/html/search/ajax/room_list.php?so_id=" + data.soID, "dataSrc": ""},
            "columns": [
              { "data": "", "defaultContent": "" },
              { "data": function(data) { return data.room + data.iteration + "-" + data.product_type + data.order_status + data.days_to_ship + ": " + data.room_name; }, "defaultContent": "" },
              { "data": "sales_bracket.job_title", "defaultContent": "" },
              { "data": "sample_bracket.job_title", "defaultContent": "" },
              { "data": "preproduction_bracket.job_title", "defaultContent": "" },
              { "data": "doordrawer_bracket.job_title", "defaultContent": "" },
              { "data": "main_bracket.job_title", "defaultContent": "" },
              { "data": "custom_bracket.job_title", "defaultContent": "" },
              { "data": "shipping_bracket.job_title", "defaultContent": "" },
              { "data": "install_bracket.job_title", "defaultContent": "" },
              { "data": "pick_materials_bracket.job_title", "defaultContent": "" },
              { "data": "edgebanding_bracket.job_title", "defaultContent": "" },
            ],
            paging: false,
            "ordering": false,
            "searching": false,
            "bInfo": false
          });
        }, 50);
      },
      "searching": false,
      paging: false,
      "bInfo": false,
      fixedHeader: true
    });

    // Add event listener for opening and closing details
    $('#search_results tbody').on('click', 'td.details-control', function () {
      var tr = $(this).closest('tr');
      var row = soTable.row( tr );

      if (row.child.isShown()) {
        // This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
      } else {
        // Open this row
        row.child(format(row.data())).show();
        tr.addClass('shown');
      }
    });
  });
</script>