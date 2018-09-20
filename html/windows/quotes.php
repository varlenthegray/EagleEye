<?php
require_once '../../includes/header_start.php';
?>

<div style="width:100%;height:100%;overflow:hidden;">
  <div class="row">
    <div class="col-md-12">
      <div class="card-box table-responsive" style="min-height:42vh;">
        <table class="table table-striped table-bordered quote_global_table" width="100%">
          <thead>
          <tr>
            <th width="5%">SO#</th>
            <th width="40%">Project</th>
            <th>Sales</th>
            <th>Sample</th>
          </tr>
          </thead>
          <tbody id="quote_table"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  $(function() {
    $(".quote_global_table").DataTable(crmMain.dtSkeleton('/ondemand/display_actions.php?action=display_quotes'));
  });
</script>