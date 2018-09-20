<?php
require_once '../../includes/header_start.php';
?>

<div style="width:100%;height:100%;overflow:hidden;">
  <div class="col-md-12" style="min-height:42vh;">
    <table class="table table-striped table-bordered active_ops_global_table" width="100%">
      <thead>
      <tr>
        <th style="min-width:20px;">&nbsp;</th>
        <th width="35px">SO#</th>
        <th width="125px">Room</th>
        <th width="125px">Department</th>
        <th width="225px">Operation</th>
        <th width="100px">Activated At</th>
        <th width="90px">Time In</th>
      </tr>
      </thead>
      <tbody id="active_ops_table"></tbody>
    </table>
  </div>
</div>

<script>
  $(function() {
    // active operations has a custom sort order and column definition setup
    let custDefs = {
      "columnDefs": [ { "targets": [0], "orderable": false, className: "nowrap" } ],
      "order": [[1, "desc"]]
    };

    $(".active_ops_global_table").DataTable(crmMain.dtSkeleton('/ondemand/display_actions.php?action=display_ind_active_jobs', custDefs));
  });
</script>