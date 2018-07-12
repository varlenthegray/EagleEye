<?php
require '../../../includes/header_start.php';
?>

<link href="/assets/css/dealer_dashboard.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />

<div class="row">
  <div class="col-md-6">
    <div class="card-box">
      <div class="row order_box">
        <div class="col-md-12">
          <h3>Quotes</h3>

          <table width="100%" id="quote_table">
            <thead>
            <tr>
              <th>Status</th>
              <th>Order/SO #</th>
              <th>PO</th>
              <th>Ship Date</th>
              <th>Delivery Date</th>
              <th>Total</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row order_box">
        <div class="col-md-12">
          <h3>Open Orders</h3>

          <table width="100%" id="orders_table">
            <thead>
            <tr>
              <th>Status</th>
              <th>Order/SO #</th>
              <th>PO</th>
              <th>Ship Date</th>
              <th>Delivery Date</th>
              <th>Total</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row order_box">
        <div class="col-md-12">
          <h3>Shipped Orders</h3>

          <table width="100%" id="shipped_table">
            <thead>
            <tr>
              <th>Status</th>
              <th>Order/SO #</th>
              <th>PO</th>
              <th>Ship Date</th>
              <th>Delivery Date</th>
              <th>Total</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  $("#quote_table").fancytree({
    extensions: ["table"],
    checkbox: false,
    table: {
      indentation: 20,      // indent 20px per node level
      nodeColumnIdx: 2,     // render the node title into the 2nd column
      checkboxColumnIdx: 0  // render the checkboxes into the 1st column
    },
    source: {
      url: "/html/dashboards/dealer/ajax/ajax-tree-products.json"
    },
    renderColumns: function(event, data) {
      var node = data.node,
        $tdList = $(node.tr).find(">td");
      $tdList.eq(0).text(node.data.status);
      $tdList.eq(1).text(node.data.so);
      // (index #2 is rendered by fancytree, title)
      $tdList.eq(3).text(node.data.ship_date);
      $tdList.eq(4).html(node.data.delivery_date);
      $tdList.eq(5).html(node.data.total.formatMoney());
    }
  });
  
  $("#orders_table").fancytree({
    extensions: ["table"],
    checkbox: false,
    table: {
      indentation: 20,      // indent 20px per node level
      nodeColumnIdx: 2,     // render the node title into the 2nd column
      checkboxColumnIdx: 0  // render the checkboxes into the 1st column
    },
    source: {
      url: "/html/dashboards/dealer/ajax/ajax-tree-products.json"
    },
    renderColumns: function(event, data) {
      var node = data.node,
        $tdList = $(node.tr).find(">td");
      $tdList.eq(0).text(node.data.status);
      $tdList.eq(1).text(node.data.so);
      // (index #2 is rendered by fancytree, title)
      $tdList.eq(3).text(node.data.ship_date);
      $tdList.eq(4).html(node.data.delivery_date);
      $tdList.eq(5).html(node.data.total.formatMoney());
    }
  });
  
  $("#shipped_table").fancytree({
    extensions: ["table"],
    checkbox: false,
    table: {
      indentation: 20,      // indent 20px per node level
      nodeColumnIdx: 2,     // render the node title into the 2nd column
      checkboxColumnIdx: 0  // render the checkboxes into the 1st column
    },
    source: {
      url: "/html/dashboards/dealer/ajax/ajax-tree-products.json"
    },
    renderColumns: function(event, data) {
      var node = data.node,
        $tdList = $(node.tr).find(">td");
      $tdList.eq(0).text(node.data.status);
      $tdList.eq(1).text(node.data.so);
      // (index #2 is rendered by fancytree, title)
      $tdList.eq(3).text(node.data.ship_date);
      $tdList.eq(4).html(node.data.delivery_date);
      $tdList.eq(5).html(node.data.total.formatMoney());
    }
  });
});
</script>