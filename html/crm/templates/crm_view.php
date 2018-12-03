<?php
require '../../../includes/header_start.php';

$id = sanitizeInput($_REQUEST['id']);
$type = sanitizeInput($_REQUEST['type']);

$project_disabled = $type === 'dID' ? 'disabled' : null;
$room_disabled = $type !== 'rID' ? 'disabled': null;

$soNum = false;

if($type === 'rID') {
  $soNum_qry = $dbconn->query("SELECT so_parent FROM rooms WHERE id = $id");

  if($soNum_qry->num_rows > 0) {
    $soNum = $soNum_qry->fetch_assoc();

    $soNum = $soNum['so_parent'];
  }
} else {
  $soNum_qry = $dbconn->query("SELECT so_num FROM sales_order WHERE id = $id");

  if($soNum_qry->num_rows > 0) {
    $soNum = $soNum_qry->fetch_assoc();

    $soNum = $soNum['so_num'];
  }
}

//echo "Disabled: $room_disabled";
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12 m-t-10">
      <ul class="nav nav-tabs" id="crmViewGlobal" role="tablist">
        <li class="nav-item m-r-5" style="font-size:1.2em;"><strong>View:</strong></li>
        <li class="nav-item">
          <a class="nav-link tab-ajax active" data-ajax="/html/crm/templates/tab_company.php" data-toggle="tab"
             id="home-tab" href="#crmCompany" role="tab" aria-controls="home" aria-expanded="true"><i class="fa fa-building-o m-r-5"></i> Company</a>
        </li>
        <li class="nav-item">
          <a class="nav-link tab-ajax <?php echo $project_disabled; ?>" data-ajax="/html/crm/ajax/project_results.php?so_num=<?php echo $soNum; ?>" data-toggle="tab"
             id="project-tab" href="#crmProject" role="tab" aria-controls="profile"><i class="fa fa-folder-o m-r-5"></i> Project</a>
        </li>
        <li class="nav-item ">
          <a class="nav-link tab-ajax <?php echo $room_disabled; ?>" data-ajax="/html/pricing/index_tabbed.php?room_id=<?php echo $id; ?>" data-toggle="tab"
             id="batch-tab" data-toggle="tab" href="#crmBatch" role="tab" aria-controls="profile"><i class="fa fa-archive m-r-5"></i> Batch</a>
        </li>
      </ul>
      <div class="tab-content" id="crmViewGlobalContent">
        <div class="tab-pane fade in active show" id="crmCompany" role="tabpanel" aria-labelledby="home-tab">
          <?php require_once 'tab_company.php'; ?>
        </div>
        <div class="tab-pane fade" id="crmProject" role="tabpanel" aria-labelledby="profile-tab"></div>
        <div class="tab-pane fade" id="crmBatch" role="tabpanel" aria-labelledby="batch-tab"></div>
      </div>
    </div>
  </div>
</div>


<script>
  $(function() {
    $("#crmViewGlobal .tab-ajax").click(function() {
      let $this = $(this), loadurl = $this.attr('data-ajax'), targ = $this.attr('href');

      if(loadurl !== undefined && targ !== undefined) {
        $.get(loadurl, function(data) {
          $(targ).html(data);
        });
      }
    });

    $(".disabled").click(function() {
      return false;
    });

    crmCompany.initEditor();
  });
</script>