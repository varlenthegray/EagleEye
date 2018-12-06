<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title">Add Modifications</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <label for="modificationsFilter">Search Modifications</label>
            <input type="text" class="form-control fc-simple ignoreSaveAlert" id="modificationsFilter" placeholder="Find" width="100%" >
          </div>

          <table id="item_modifications" width="100%">
            <colgroup>
              <col width="35%">
              <col width="40%">
              <col width="25%">
            </colgroup>
            <thead>
            <tr>
              <th></th>
              <th>Modification</th>
              <th>Additional Info</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary waves-effect waves-light" id="modificationAddSelected">Add Selected</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  let modificationTree = {
    url: "/html/pricing/ajax/modifications.php?priceGroup=" + priceGroup + "&itemID=" + cabinetList.fancytree("getTree").getActiveNode().data.itemID,
    type: "POST",
    dataType: 'json'
  };

  //<editor-fold desc="Modification Modal">
  $(function() {
    $("#item_modifications").fancytree({
      source: modificationTree,
      extensions: ["filter", "table"],
      keyboard: false,
      table: {
        indentation: 20,
        nodeColumnIdx: 0
      },
      filter: {
        autoApply: true,   // Re-apply last filter if lazy data is loaded
        autoExpand: true, // Expand all branches that contain matches while filtered
        counter: true,     // Show a badge with number of matching child nodes near parent icons
        fuzzy: true,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
        hideExpandedCounter: true,  // Hide counter badge if parent is expanded
        hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
        highlight: true,   // Highlight matches by wrapping inside <mark> tags
        leavesOnly: false, // Match end nodes only
        nodata: true,      // Display a 'no data' status node if result is empty
        mode: "hide"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
      },
      renderColumns: function(event, data) {
        // this section handles the column data itself
        var node = data.node, $tdList = $(node.tr).find(">td");

        // Index #0 => Checkbox

        // Index #1 => SKU/Description
        let extraMod = '';

        if(node.data.description !== undefined) {
          extraMod += node.data.description;
        }

        if(node.data.info !== undefined) {
          extraMod += node.data.info;
        }

        $tdList.eq(1).html(extraMod);

        // Index #2 => Additional Info Box
        // Addl Info is a JSON element encoded in a database, we need to parse each of them
        if(node.data.addl_info !== undefined && node.data.addl_info !== null && node.data.addl_info !== '') {
          let ai = JSON.parse(node.data.addl_info);
          let addlInfoOut = "<input type='" + ai.type + "' class='modAddlInfo' id='" + node.key + "' name='addlInfo[]' placeholder='" + ai.placeholder + "' />";

          $tdList.eq(2).html(addlInfoOut);
        }
      }
    });
  });
  //</editor-fold>
</script>