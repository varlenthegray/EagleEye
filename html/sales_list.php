<?php
require '../includes/header_start.php';

if($_REQUEST['win'] === 'true') {
  $top = '0';
} else {
  $top = '84px';
}
?>


<div class="col-md-12">
  <div class="card-box" id="float_container">
    <div class="row">
      <div class="col-md-2 no-print" style="position:fixed;top:10vh;overflow:auto;height:87vh;display:none;">
        <table width="100%" style="background-color:#FFFFFF;">
          <tr>
            <td><div class="checkbox checkbox-primary"><input id="job_status_lost" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_lost">Lost</label></div></td>
          </tr>
          <tr>
            <td><div class="checkbox checkbox-primary"><input id="job_status_quote" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_quote">Quote</label></div></td>
          </tr>
          <tr>
            <td><div class="checkbox checkbox-primary"><input id="job_status_job" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_job">Job</label></div></td>
          </tr>
          <tr>
            <td><div class="checkbox checkbox-primary"><input id="job_status_completed" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_completed">Completed</label></div></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
          <?php
          $dealer_qry = $dbconn->query('SELECT d.dealer_id AS dealerCode, c.* FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id ORDER BY d.dealer_id ASC');

          while($dealer = $dealer_qry->fetch_assoc()) {
            $indent = (strlen($dealer['dealerCode']) > 3) ? "style='margin-left:20px;'" : null;

            $name = (empty($dealer['first_name']) && empty($dealer['last_name'])) ? $dealer['company_name'] : "{$dealer['first_name']} {$dealer['last_name']}";

            echo "<tr style='border:1px solid #000;'><td style='padding:2px;'><div class='checkbox checkbox-primary' $indent><input class='ignoreSaveAlert hide_dealer' id='hide_dealer_{$dealer['dealerCode']}' data-dealer-id='{$dealer['dealerCode']}' type='checkbox' checked><label for='hide_dealer_{$dealer['dealerCode']}'>{$dealer['dealerCode']}: $name</label></div></td></tr>";

            //echo "<option value='{$dealer['dealerCode']}'>$indent {$dealer['dealerCode']} ($name)</option>";
          }
          ?>
        </table>
      </div>

      <div class="col-md-12 sticky" style="background-color:#FFF;top:<?php echo $top; ?>;z-index:2;">
        <table>
          <tr>
            <td style="padding-right:15px;"><div class="checkbox checkbox-custom text-md-center"><input id="show_hidden_sales_list" class="ignoreSaveAlert" type="checkbox"><label for="show_hidden_sales_list">Show Hidden</label></div></td>
            <td style="padding-right:15px;"><div class="checkbox checkbox-custom text-md-center"><input id="job_status_lost" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_lost">Lost</label></div></td>
            <td style="padding-right:15px;"><div class="checkbox checkbox-custom text-md-center"><input id="job_status_quote" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_quote">Quote</label></div></td>
            <td style="padding-right:15px;"><div class="checkbox checkbox-custom text-md-center"><input id="job_status_job" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_job">Job</label></div></td>
            <td style="padding-right:15px;"><div class="checkbox checkbox-custom text-md-center"><input id="job_status_completed" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_completed">Completed</label></div></td>
          </tr>
        </table>
      </div>

      <div class="col-md-4">
        <table class="table table-bordered tablesorter" id="so_global_list">
          <thead>
          <tr>
            <th width="23px">&nbsp;</th>
            <th>SO#</th>
            <th>PROJECT/ROOM</th>
            <th>CONTACT</th>
            <th>STATUS</th>
            <th>DEALER CODE</th>
            <th>IDENTIFIER</th>
          </tr>
          </thead>
          <tbody id="so_global_list_breakdown">
          <tr>
            <td colspan="9">No SO's to display.</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  var table = $("#so_global_list");

  var so_list = table.DataTable({
    "ajax": "/ondemand/so_actions.php?action=get_sales_list",
    "columnDefs": [
      {"visible": false, "targets": [3, 5, 6]},
      {"orderable": false, "targets": [0, 1, 2, 4, 6]}
    ],
    "createdRow": function(row,data,dataIndex) {
      $(row).addClass("cursor-hand view_so_info ignoreSaveAlert dealer_" + data[5] + " " + data[6]);

      switch(data[4]) {
        case 'Lost':
          $(row).addClass("room_lost");
          break;

        case 'Quote':
          $(row).addClass("room_quote");
          break;

        case 'Job':
          $(row).addClass("room_job");
          break;

        case 'Completed':
          $(row).addClass("room_completed");
          break;

        default:
          $(row).addClass("highlight_so");
          break;
      }
    },
    "order": [3,'asc'],
    "dom": 'rti',
    "paging": false,
    "drawCallback": function(settings) {
      var api = this.api();
      var rows = api.rows({page: 'current'}).nodes();
      var last = null;

      api.column(3, {page: 'current'}).data().each(function (group, i) {
        if (last !== group) {

          $(rows).eq(i).before('<tr class="sales_list_dealer ignoreSaveAlert"><td colspan="4" style="padding-left:15px">' + group + '</td></tr>');

          last = group;
        }
      });
    },
    fixedHeader: {
      headerOffset: 100
    }
  });

  $("body")
    .on("change", '#show_hidden_sales_list', function() {
      if($(this).is(":checked")) {
        $(".js_loading").show();

        so_list.ajax.url('/ondemand/so_actions.php?action=get_sales_list&hidden=true').load(function() {
          $(".js_loading").hide();
        });
      } else {
        $(".js_loading").show();

        so_list.ajax.url('/ondemand/so_actions.php?action=get_sales_list').load(function() {
          $(".js_loading").hide();
        });
      }
    })
  ;
</script>