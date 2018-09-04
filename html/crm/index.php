<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<link rel="stylesheet" type="text/css" href="/html/css/standard.min.css"/>

<div class="row crm">
  <?php require_once 'includes/left_nav.php'; ?>

  <div class="bottom-container"></div>
</div>

<script src="/html/crm/js/companies.min.js"></script>

<script>
  $(function() {
    companies.initCompanyList(".bottom-container");
  });
</script>

