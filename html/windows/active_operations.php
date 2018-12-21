<?php
require_once '../../includes/header_start.php';
?>

<div style="width:100%;height:100%;overflow:hidden;">
  <div class="col-md-12" style="min-height:42vh;">
    <table class="table table-striped table-bordered active_ops_global_table" width="100%">
      <colgroup>
        <col width="120px">
        <col width="35px">
        <col width="125px">
        <col width="125px">
        <col width="225px">
        <col width="100px">
        <col width="90px">
      </colgroup>
      <thead>
      <tr>
        <th>&nbsp;</th>
        <th>SO#</th>
        <th>Room</th>
        <th>Department</th>
        <th>Operation</th>
        <th>Activated At</th>
        <th>Time In</th>
      </tr>
      </thead>
      <tbody id="active_ops_table"></tbody>
    </table>
  </div>
</div>

<script>
  $(function() {
    $("#active_header").html("<h4>Operations (Active) for <?php echo $_SESSION['shop_user']['name']; ?></h4>");

    crmMain.dataTableContainer.active_table = $(".active_ops_global_table").DataTable({
      "ajax": "/ondemand/display_actions.php?action=display_ind_active_jobs",
      "createdRow": function(row,data,dataIndex) {
        $(row).addClass("cursor-hand view_so_info").attr('data-project-name', data.project_name);
      },
      "paging": false,
      scrollY: '31.5vh',
      scrollCollapse: true,
      "dom": '<"#active_header.dt-custom-header">tipr',
      "columnDefs": [
        {"targets": [0], "orderable": false, className: "nowrap"}
      ],
      "order": [[1, "desc"]]
    });
  });
</script>