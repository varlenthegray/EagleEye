<?php
require '../../../includes/header_start.php';

$id = sanitizeInput($_REQUEST['id']);
?>

<div class="col-md-12 m-t-10">
  <ul class="nav nav-tabs" id="crmViewGlobal" role="tablist">
    <li class="nav-item m-r-5" style="font-size:1.2em;"><strong>View:</strong></li>
    <li class="nav-item">
      <a class="nav-link tab-ajax active" data-ajax="/html/crm/templates/tab_company.php" data-toggle="tab"
         id="home-tab" href="#crmCompany" role="tab" aria-controls="home" aria-expanded="true"><i class="fa fa-building-o m-r-5"></i> Company</a>
    </li>
    <li class="nav-item">
      <a class="nav-link tab-ajax disabled" data-ajax="/html/crm/templates/tab_company.php" data-toggle="tab"
         id="project-tab" href="#crmProject" role="tab" aria-controls="profile"><i class="fa fa-folder-o m-r-5"></i> Project</a>
    </li>
    <li class="nav-item ">
      <a class="nav-link tab-ajax disabled" id="batch-tab" data-toggle="tab" href="#crmBatch" role="tab" aria-controls="profile"><i class="fa fa-archive m-r-5"></i> Batch</a>
    </li>
  </ul>
  <div class="tab-content" id="crmViewGlobalContent">
    <div class="tab-pane fade in active show" id="crmCompany" role="tabpanel" aria-labelledby="home-tab">
      <?php require_once 'tab_company.php'; ?>
    </div>
    <div class="tab-pane fade" id="crmProject" role="tabpanel" aria-labelledby="profile-tab"></div>
    <div class="tab-pane fade" id="crmBatch" role="tabpanel" aria-labelledby="dropdown1-tab"></div>
  </div>
</div>

<script>
  $(function() {
    $("body")
      .on("click", ".tab-ajax", function() {
        let $this = $(this), loadurl = $this.attr('data-ajax'), targ = $this.attr('href');

        if(loadurl !== undefined && targ !== undefined) {
          $.get(loadurl, function(data) {
            $(targ).html(data);
          });
        }
      })
      .on("click", ".disabled", function() {
        return false;
      })
    ;

    crmCompany.initEditor();
  });
</script>