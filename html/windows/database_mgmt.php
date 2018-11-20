<?php
require_once '../../includes/header_start.php';
?>

<div class="col-md-10 col-md-offset-1 m-b-10 widget_box">
  <div class="col-md-1 cursor-hand widget-item widget-active"><i class="ion-log-in fa-3x"></i><h5>Doors</h5></div>
</div>

<div class="col-md-4 database_main_window">
  <table id="table_door_mgmt" style="width:100%;">
    <colgroup>
      <col width="30px">
      <col width="100px">
      <col width="50px">
      <col width="40px">
    </colgroup>
    <thead>
    <tr>
      <th>#</th>
      <th class="text-md-center">Species</th>
      <th class="text-md-center">Door Style</th>
      <th>Price Group</th>
    </tr>
    </thead>
    <tbody>
    <!-- Define a row template for all invariant markup: -->
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
    </tbody>
  </table>
</div>

<script>
  $("#table_door_mgmt").fancytree({
    titlesTabbable: true,     // Add all node titles to TAB chain
    quicksearch: true,        // Jump to nodes when pressing first character
    source: { url: "/html/assets/db_doors.php?action=getDoorList" },
    extensions: ["table", "gridnav", "persist"],
    debugLevel: 0,
    table: {
      indentation: 20,
      nodeColumnIdx: 1,
      checkboxColumnIdx: 0
    },
    gridnav: {
      autofocusInput: false,
      handleCursorKeys: true
    },
    renderColumns: function(event, data) {
      // this section handles the column data itself
      var node = data.node, $tdList = $(node.tr).find(">td");

      // Index #0 => Line Numbering
      $tdList.eq(0).text(node.getIndexHier());

      // Index #1 => Auto-assigned based on table, title value

      // Index #2 => Door Style
      $tdList.eq(2).text(node.data.door_design);

      // Index #3 => Price Group
      $tdList.eq(3).text(node.data.price_group);
    }
  });
</script>