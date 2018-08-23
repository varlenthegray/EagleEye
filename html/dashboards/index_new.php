<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<style>
  .ee-center {
    height: 88vh;
    text-align: center;
  }

  .ee-center-placeholder {
    position: relative;
    top: 50%;
    transform: translateY(-50%);
  }
</style>

<div class="ee-center">
  <div class="ee-center-placeholder"><img class="ee-center-img" src="/html/dashboards/images/ee-center.png" usemap="#ee_center" /></div>

  <map name="ee_center">
    <area shape="circle" coords="400,102,99" href="#dashboard" onclick="index.goDashboard()" />
    <area shape="circle" coords="599,201,99" href="#CRM" onclick="index.goCRM()" />
    <area shape="circle" coords="698,400,99" href="#calendar" onclick="index.goCalendar()" />
    <area shape="circle" coords="599,599,99" href="#shop_floor" onclick="index.goShopFloor()" />
    <area shape="circle" coords="400,699,99" href="#inventory" onclick="index.goInventory()" />
    <area shape="circle" coords="201,599,99" href="#accounting" onclick="index.goAccounting()" />
    <area shape="circle" coords="102,400,99" href="#email" onclick="index.goEmail()" />
    <area shape="circle" coords="201,201,99" href="#reporting" onclick="index.goReporting()" />
  </map>
</div>

<script>
  if(!index) {
    var index = {
      goDashboard: function() {
        window.open("index.php", "_self");
      },
      goCRM: function() {
        window.open("index.php?page=display_contacts", "_self");
      },
      goCalendar: function() {
        window.open("index.php?page=calendar/index", "_self");
      },
      goShopFloor: function() {
        window.open("index.php?page=workcenter", "_self");
      },
      goInventory: function() {
        window.open("index.php?page=inventory/index", "_self");
      },
      goAccounting: function() {
        window.open("index.php?page=accounting/index", "_self");
      },
      goEmail: function() {
        window.open("index.php?page=mail/cross_page", "_self");
      },
      goReporting: function() {
        window.open("index.php?page=sales_list", "_self");
      }
    };
  }

  $(function() {
    $(".ee-center").maphilight({
      stroke: false
    });
  });
</script>