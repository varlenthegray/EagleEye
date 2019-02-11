<?php
require '../../../includes/header_start.php';

//outputPHPErrs();

$keys = json_decode($_REQUEST['keys']);

$project_disabled = empty($keys[1]) ? 'disabled' : null;
$room_disabled = empty($keys[2]) ? 'disabled' : null;

//echo "Disabled: $room_disabled";
?>

<div class="container-fluid">
  <!--<div class="row m-t-10 m-b-10" style="font-weight:bold;">
    <div class="col-md-3">SO: 939</div>
    <div class="col-md-3">A01a: Robert Grieves</div>
    <div class="col-md-3">Customer PO: Fennessy_Barkley</div>
    <div class="col-md-3">PM: Robert Grieves</div>
  </div>-->

  <div class="row">
    <div class="col-md-12 m-t-10">
      <ul class="nav nav-tabs sticky" id="crmViewGlobal" role="tablist" style="background-color:#FFF;z-index:2;top:0;">
        <li class="nav-item m-r-5" style="font-size:1.2em;"><strong>View:</strong></li>
        <li class="nav-item">
          <a class="nav-link tab-ajax active" data-ajax="/html/crm/templates/organization.php?org_id=<?php echo $keys[0]; ?>" data-toggle="tab"
             id="company-tab" href="#crmCompany" role="tab" aria-controls="home" aria-expanded="true"><i class="fa fa-building-o m-r-5"></i> Organization</a>
        </li>
        <li class="nav-item">
          <a class="nav-link tab-ajax <?php echo $project_disabled; ?>" data-ajax="/html/crm/templates/project_results.php?so_id=<?php echo $keys[1]; ?>&company_id=<?php echo $company['id']; ?>" data-toggle="tab"
             id="project-tab" href="#crmProject" role="tab" aria-controls="project"><i class="fa fa-folder-o m-r-5"></i> Sales Order</a>
        </li>
        <li class="nav-item ">
          <a class="nav-link tab-ajax <?php echo $room_disabled; ?>" data-ajax="/html/pricing/index_new.php?room_id=<?php echo $keys[2]; ?>" data-toggle="tab"
             id="batch-tab" data-toggle="tab" href="#crmBatch" role="tab" aria-controls="batch"><i class="fa fa-archive m-r-5"></i> Batch</a>
        </li>
      </ul>
      <div class="tab-content" id="crmViewGlobalContent">
        <div class="tab-pane fade in active show" id="crmCompany" role="tabpanel" aria-labelledby="company-tab"></div>
        <div class="tab-pane fade" id="crmProject" role="tabpanel" aria-labelledby="project-tab"></div>
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

    switch(crmNav.activatedType) {
      case 'cID':
        $("#company-tab").trigger("click");
        break;
      case 'soID':
        $("#project-tab").trigger("click");
        break;
      case 'rID':
        $("#batch-tab").trigger("click");
        break;
    }

    // crmCompany.initEditor();
  });
</script>