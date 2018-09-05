<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<link rel="stylesheet" type="text/css" href="/html/css/standard.min.css"/>

<div class="row crm">
  <?php require_once 'includes/left_nav.php'; ?>

  <div class="bottom-container">
    <div class="col-md-10">
      <div class="card-box" style="height:80vh;">
      </div>
    </div>
  </div>
</div>

<script src="/html/crm/js/companies.min.js"></script>

<script>
  $(function() {
    $("body").on("click", "#searchResultTree", function() {
      companies.setContainer('.bottom-container');
      companies.getCompany(2);
    });
  });
</script>