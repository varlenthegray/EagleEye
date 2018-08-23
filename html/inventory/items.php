<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<style>
.image_thumbnail {
  max-width: 30px;
  max-height: 30px;
}

  table {
    outline: none;
  }

  .vert-nav ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    width: 100%;
    background-color: #666666;
    border-radius: 5px;
  }

  .vert-nav ul li {
    border-top: 1px solid #FFF;
  }

  .vert-nav ul li:first-child {
    border-top: none;
  }

  .vert-nav ul li a {
    display: block;
    color: #FFFFFF;
    font-weight: bold;
    text-decoration: none;
    padding: 4px;
    border-radius: 5px;
    font-size: 1.2em;
  }

  .vert-nav ul li a i {
    padding-right: 4px;
  }

  .vert-nav ul li a:hover {
    background-color: #333333;
  }

  #inventory td {
    padding: 3px;
  }

  #inv_actions {
    display: none;
  }

  .qty-input {
    border: 1px solid #AAA;
    border-radius: 5px;
    padding: 2px;
    width: 100%;
    text-align: center;
  }
</style>


<div class="row">
  <div class="col-md-2">
    <div class="card-box">
      <div class="row">
        <div class="col-md-12">
          <nav class="vert-nav">
            <ul>
              <li><a href="#"><i class="fa fa-fw fa-list-alt"></i> Inventory List</a></li>
              <li><a href="#"><i class="fa fa-fw fa-truck"></i> Supplier Management</a></li>
              <li><a href="#"><i class="fa fa-fw fa-pencil-square"></i> Inventory Reports</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>


    <div class="col-md-10">
      <div class="card-box" style="height:80vh;">
        <div class="row">
          <div class="col-md-12">
            <div class="btn-group" id="inv_actions">
              <button type="button" class="btn btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false" style="margin-bottom: 10px;">Actions</button>
              <div class="dropdown-menu dropdown-menu-left">
                <a class="dropdown-item" href="#">Update Quantity</a>
                <a class="dropdown-item" href="#">Mark for Reorder</a>
                <a class="dropdown-item" href="#">Issue to Department</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" onclick="inv.deactivateItem(true)">Deactivate</a>
              </div>
            </div>

            <table style="width:100%;" id="inventory">
              <colgroup>
                <col width="30px">
                <col width="50px">
                <col width="150px">
                <col width="50px">
                <col width="*">
                <col width="150px">
                <col width="150px">
                <col width="75px">
                <col width="75px">
                <col width="50px">
                <col width="40px">
                <col width="75px">
                <col width="100px">
              </colgroup>
              <thead>
              <tr>
                <th></th>
                <th>ID</th>
                <th>SKU</th>
                <th>Image</th>
                <th>Name</th>
                <th>Supplier</th>
                <th>Category</th>
                <th>Cost</th>
                <th>Retail Price</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Alert Qty</th>
                <th>Actions</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td style="text-align:center;"></td>
                <td style="text-align:center;"></td>
                <td></td>
                <td style="text-align:center;"><img class='image_thumbnail' src='/html/inventory/images/placeholder.png' /></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align:center;"><input type="text" class="qty-input" /></td>
                <td style="text-align:center;"></td>
                <td style="text-align:center;"><input type="text" class="qty-input" /></td>
                <td class="position:relative;">
                  <div class="btn-group">
                    <button type="button" class="btn btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions</button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <a class="dropdown-item" href="#">Update Item</a>
                      <a class="dropdown-item" href="#">Mark for Reorder</a>
                      <a class="dropdown-item" href="#">Issue to Department</a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="#" onclick="inv.deactivateItem()">Deactivate</a>
                    </div>
                  </div>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
</div>

<script>
  var inventory = $("#inventory");

  $(function(){
    // Attach the fancytree widget to an existing <div id="tree"> element
    // and pass the tree options as an argument to the fancytree() function:
    inventory.fancytree({
      extensions: ["table"],
      checkbox: true,
      table: {
        indentation: 20,      // indent 20px per node level
        nodeColumnIdx: 4,     // render the node title into the 2nd column
        checkboxColumnIdx: 0  // render the checkboxes into the 1st column
      },
      source: {
        url: "/html/inventory/ajax/ajax-tree-products.json"
      },
      renderColumns: function(event, data) {
        var node = data.node, $tdList = $(node.tr).find(">td");
        // (index #0 is rendered by fancytree by adding the checkbox)
        $tdList.eq(1).text(node.getIndexHier());
        $tdList.eq(2).text(node.data.sku);

        if(node.data.image !== '') {
          $tdList.eq(3).html("<img class='image_thumbnail' src='/html/inventory/images/" + node.data.image + "' />");
        }

        // (index #4 is rendered by fancytree)
        $tdList.eq(5).text(node.data.supplier);
        $tdList.eq(6).text(node.data.category);
        $tdList.eq(7).text(parseFloat(node.data.cost).formatMoney());
        $tdList.eq(8).text(parseFloat(node.data.retail_price).formatMoney());
        $tdList.eq(9).find("input").val(node.data.qty);
        $tdList.eq(10).text(node.data.unit);
        $tdList.eq(11).find("input").val(node.data.alert_qty);
      },
      icon: false,
      select: function(event, data) {
        if(inventory.fancytree("getTree").getSelectedNodes().length > 0) {
          $("#inv_actions").show();
        } else {
          $("#inv_actions").hide();
        }
      }
    });
  });

  if(!inv) {
    var inv = {
      deactivateItem: function(multiple) {
        let content;
        let title;

        if(multiple) {
          title = 'Deactivate all checked items?';
          content = 'You are about to deactivate these items. This will automatically remove the items from all available reports, continue?';
        } else {
          title = 'Deactivate item?';
          content = 'You are about to deactivate this item. This will automatically remove the item from all available reports, continue?';
        }

        $.confirm({ // a confirmation box to ensure they are intending to complete tasks
          title: title,
          content: content,
          type: 'red',
          buttons: {
            continue: function() {},
            cancel: function() {} // we're not doing anything
          }
        });
      }
    };
  }
</script>