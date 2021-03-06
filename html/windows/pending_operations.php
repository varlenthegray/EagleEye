<?php
require_once '../../includes/header_start.php';

$header =  "<select class='ignoreSaveAlert' name='viewing_queue' id='viewing_queue'>";
$header .= "<option value='self'>{$_SESSION['shop_user']['name']}</option>";

$departments = json_decode($_SESSION['shop_user']['department']);
$default = $_SESSION['shop_user']['default_queue'];

foreach($departments as $department) {
  if($department === $default) {
    $selected = 'selected';
  } else {
    $selected = '';
  }

  $header .= "<option value='$department' $selected>$department</option>";
}

$header .= '</select>';
?>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h4>Viewing Queue: <?php echo $header; ?></h4>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
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

    crmMain.dataTableContainer.queue_table = $(".queue_ops_global_table").DataTable({
      "ajax": "/ondemand/display_actions.php?action=display_ind_job_queue&queue=" + queue,
      "createdRow": function(row,data,dataIndex) {
        $(row).addClass("cursor-hand view_so_info").attr('data-weight', data.weight).attr('data-project-name', data.project_name);
      },
      "paging": false,
      scrollCollapse: true,
      "dom": 'tipr',
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
  });
</script>