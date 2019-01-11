<?php
require_once '../../includes/header_start.php';
?>

<div style="width:100%;height:100%;overflow:hidden;">
  <div class="table-responsive" style="min-height:42vh;">
    <table class="table table-striped table-bordered queue_ops_global_table" width="100%">
      <thead>
      <tr>
        <th width="23px" class="nowrap">&nbsp;</th>
        <th width="8px">#</th>
        <th width="50px">SO#</th>
        <th width="220px">Room</th>
        <th width="215px">Operation</th>
        <th width="80px">Release Date</th>
        <th width="100px">Operation Time</th>
        <th width="100px">Weight</th>
      </tr>
      </thead>
      <tbody id="queue_ops_table"></tbody>
    </table>
  </div>
</div>

<script>
  $(function() {
    // define the queue for display
    let queue = null;

    <?php
    if(isset($_SESSION['userInfo']['default_queue']) || !empty($_SESSION['userInfo']['default_queue'])) {
      echo "queue = '{$_SESSION['userInfo']['default_queue']}';";
    } else {
      echo "queue = '{$_SESSION['shop_user']['default_queue']}';";
    }
    ?>

    //<editor-fold desc="TODO: Fix this to be AJAX">
    var op_queue_list = "<?php
      echo "<select class='ignoreSaveAlert' name='viewing_queue' id='viewing_queue'>";
      echo "<option value='self'>{$_SESSION['shop_user']['name']}</option>";

      $departments = json_decode($_SESSION['shop_user']['department']);
      $default = $_SESSION['shop_user']['default_queue'];

      foreach($departments as $department) {
        if($department === $default) {
          $selected = 'selected';
        } else {
          $selected = '';
        }

        echo "<option value='$department' $selected>$department</option>";
      }
      echo '</select>';
      ?>";
    //</editor-fold>

    crmMain.dataTableContainer.queue_table = $(".queue_ops_global_table").DataTable({
      "ajax": "/ondemand/display_actions.php?action=display_ind_job_queue&queue=" + queue,
      "createdRow": function(row,data,dataIndex) {
        $(row).addClass("cursor-hand view_so_info").attr('data-weight', data.weight).attr('data-project-name', data.project_name);
      },
      "paging": false,
      scrollY: '30.5vh',
      scrollCollapse: true,
      "dom": '<"#queue_header.dt-custom-header">tipr',
      "columnDefs": [
        {"targets": [0], "orderable": false},
        {"targets": [6, 7], "visible": false},
        {"targets": [7], "searchable": false, "type": "num-html"}
      ],
      "order": [[7, "desc"]],
      "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        // this displays the numbering of priorities, we already sort based on weight which is an arbitrary number
        var index = iDisplayIndexFull + 1;
        $('td:eq(1)', nRow).html(index);
        return nRow;

      },
      "initComplete": function() {
        globalFunctions.updateOpQueue();
      }
    });

    $("#queue_header").html("<h4 class='pull-left'>Operations for " + op_queue_list + "</h4>");
  });
</script>