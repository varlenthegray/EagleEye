<?php
require_once '../../includes/header_start.php';

$find = sanitizeInput($_REQUEST['find']);
?>

<link href="/html/search/css/so_list.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />

<div class="card-box">
  <div class="row">
    <div class="col-md-12">
      <table id="search_results" class="table table-striped table-bordered dataTable no-footer" style="width:100%;">
        <colgroup>
          <col width="15px">
          <col width="60px">
          <col>
          <col width="250px">
          <col width="250px">
        </colgroup>
        <thead>
        <tr>
          <th width="15px"></th>
          <th>SO #</th>
          <th>Dealer/Contractor</th>
          <th>Customer PO/Project Name</th>
          <th>Project Manager</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<script>
  var soTable;
  var roomTable;

  // @getRoom(so_id) - creates the framework for each room table, singular table under the SO as a child
  function getRoom(so_id) {
    return '<table id="' + so_id + '" class="table table-striped table-bordered dataTable no-footer room_listing child_table" style="width:100%;">' +
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

  function getSOButtons(so_id) {
    return '<i class="fa fa-pencil-square primary-color cursor-hand edit_so" data-id="' + so_id + '" title="Edit SO Details"></i> <i class="fa fa-plus-square primary-color cursor-hand add_room" data-id="' + so_id + '" title="Add Room to SO"></i>';
  }

  $(document).ready(function() {
    soTable = $('#search_results').DataTable({
      "ajax": { "url": "/html/search/ajax/so_list.php?find=<?php echo $find; ?>", "dataSrc": ""}, // telling it dataSrc of null converts it from an object search to array
      "columns": [
        {
          "className": 'no-wrap so_buttons',
          "orderable": false,
          "data": function(data) { return getSOButtons(data.soID) }
        },
        { "data": "so_num" },
        { "data": "project_name" },
        { "data": "contact" },
        { "data": function(data) { return data.dealer_id + ": " + data.dealer_name} }
      ],
      "order": [[1, 'asc']],
      "createdRow": function(row, data, index) {
        // on creation of the row, add a child to it with a table that contains the information related to it
        soTable.row(':eq(' + index + ')').child(getRoom(data.soID)).show();

        // generate the room datatable, on a delay for loading of the data
        setTimeout(function() {
          // create the datatable
          roomTable = $("#" + data.soID).DataTable({
            "ajax": { "url": "/html/search/ajax/room_list.php?so_id=" + data.soID, "dataSrc": ""}, // fetch the rooms
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
            paging: false, // pagination
            "ordering": false, // sorting
            "searching": false, // search box
            "bInfo": false // bottom info
          });
        }, 10);
      },
      "searching": false, // search box
      paging: false, // pagination
      "bInfo": false, // bottom info
      fixedHeader: { "headerOffset": 84 } // set the header offset to 84px from the top, accounting for the page header
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