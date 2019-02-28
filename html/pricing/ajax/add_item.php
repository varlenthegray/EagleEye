<?php
require '../../../includes/header_start.php';
?>

<style>
  .add_item_table {
    margin-top: 15px;
  }

  .add_item_table td {
    padding: 1px 4px;
  }
</style>

<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title">Add New Catalog Item</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <form id="catalogAddItem" action="#">
          <div class="col-md-6 pricing_left_nav" style="height:75vh;">
            <div class="sticky nav_filter">
              <div class="form-group">
                <label for="treeFilter">Search Catalog</label>
                <input type="text" class="form-control fc-simple ignoreSaveAlert" id="addItemFilter" placeholder="Find" width="100%" >
              </div>

              <label for="below" id="category_collapse">Categories</label>

              <input type="hidden" id="addItemCategory" name="add_to_category" />
            </div>


            <div id="add_item_categories"></div>
          </div>

          <div class="col-md-6">
            <table class="add_item_table">
              <tr>
                <td><label>SKU:</label></td>
                <td><input type="text" class="form-control" name="sku" placeholder="SKU" /> </td>
              </tr>
              <tr>
                <td colspan="2" style="padding:0;">
                  <table>
                    <tr>
                      <td><label>Width:</label></td>
                      <td><label>Height:</label></td>
                      <td><label>Depth:</label></td>
                    </tr>
                    <tr>
                      <td><input type="text" class="form-control" name="width" placeholder="Width" /> </td>
                      <td><input type="text" class="form-control" name="height" placeholder="Height" /> </td>
                      <td><input type="text" class="form-control" name="depth" placeholder="Depth" /> </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td><label for="ai_hinge">Hinge:</label></td>
                <td>
                  <select class="form-control" id="ai_hinge" name="hinge">
                    <option value="L">Left</option>
                    <option value="R">Right</option>
                    <option value="P">Pair</option>
                    <option value="N" selected>None</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td><label>Description:</label></td>
                <td><textarea class="form-control" name="description" placeholder="Description" rows="5"></textarea></td>
              </tr>
              <tr>
                <td><label>Image:</label></td>
                <td><input type="file" class="form-control" name="image" placeholder="Image" /> </td>
              </tr>
            </table>

            <table class="add_item_table">
              <tr>
                <td width="10%"><label for="ai_pg_1">PG1:</label></td>
                <td width="40%"><input type="text" id="ai_pg_1" class="form-control" name="pg1" /> </td>
                <td width="10%"><label for="ai_pg_2">PG2:</label></td>
                <td width="40%"><input type="text" id="ai_pg_2" class="form-control" name="pg2" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_3">PG3:</label></td>
                <td><input type="text" id="ai_pg_3" class="form-control" name="pg3" /> </td>
                <td><label for="ai_pg_4">PG4:</label></td>
                <td><input type="text" id="ai_pg_4" class="form-control" name="pg4" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_5">PG5:</label></td>
                <td><input type="text" id="ai_pg_5" class="form-control" name="pg5" /> </td>
                <td><label for="ai_pg_6">PG6:</label></td>
                <td><input type="text" id="ai_pg_6" class="form-control" name="pg6" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_7">PG7:</label></td>
                <td><input type="text" id="ai_pg_7" class="form-control" name="pg7" /> </td>
                <td><label for="ai_pg_8">PG8:</label></td>
                <td><input type="text" id="ai_pg_8" class="form-control" name="pg8" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_9">PG9:</label></td>
                <td><input type="text" id="ai_pg_9" class="form-control" name="pg9" /> </td>
                <td><label for="ai_pg_10">PG10:</label></td>
                <td><input type="text" id="ai_pg_10" class="form-control" name="pg10" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_11">PG11:</label></td>
                <td><input type="text" id="ai_pg_11" class="form-control" name="pg11" /> </td>
                <td><label for="ai_pg_12">PG12:</label></td>
                <td><input type="text" id="ai_pg_12" class="form-control" name="pg12" /> </td>
              </tr>
              <tr>
                <td><label for="ai_pg_13">PG13:</label></td>
                <td><input type="text" id="ai_pg_13" class="form-control" name="pg13" /> </td>
                <td><label for="ai_pg_14">PG14:</label></td>
                <td><input type="text" id="ai_pg_14" class="form-control" name="pg14" /> </td>
              </tr>
            </table>
          </div>
        </form>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary waves-effect waves-light" id="modalAddCatItemSubmit">Add</button>
    </div>
  </div>
</div>

<script>
  $(function() {
    // this is the navigation menu on the left side
    $("#add_item_categories").fancytree({
      source: { url: "/html/pricing/ajax/nav_menu.php" },
      extensions: ["filter"],
      debugLevel: 0,
      filter: {
        autoApply: true,   // Re-apply last filter if lazy data is loaded
        autoExpand: true, // Expand all branches that contain matches while filtered
        counter: true,     // Show a badge with number of matching child nodes near parent icons
        fuzzy: true,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
        hideExpandedCounter: true,  // Hide counter badge if parent is expanded
        hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
        highlight: true,   // Highlight matches by wrapping inside <mark> tags
        leavesOnly: false, // Match end nodes only
        nodata: false,      // Display a 'no data' status node if result is empty
        mode: "hide"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
      },
      renderNode: function(event, data) {
        var node = data.node;

        $(node.li).attr("data-id", node.key);
      },
      activate: function(event, data) {
        let node = data.node;

        if(data.node.isFolder()) {
          $("#addItemCategory").val(node.key);
        } else {
          let parent = node.getParent();

          $("#addItemCategory").val(parent.key);
        }
      }
    });
  });
</script>