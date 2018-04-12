<?php
require '../../includes/header_start.php';

// TODO: Add lines from the left menu
// TODO: Change catalog replaces the left menu
// TODO: Cabinet list load from database
// TODO: Save cabinet list to the database
// TODO: Display price group price based on database values
// TODO: Import items and line items based on nomenclature
// TODO: Correct header information (above coversheet)
// TODO: Tag based on room ID #
// TODO: Allow information on coversheet to be updated directly from page
// TODO: Update Page Counter if more than 1 page exists (HOW?!)

//outputPHPErrs();

$room_id = 799;

$info_qry = $dbconn->query("SELECT rooms.*, sales_order.*, rooms.order_status AS rOrderStatus FROM rooms LEFT JOIN sales_order ON rooms.so_parent = sales_order.so_num WHERE rooms.id = '$room_id'");
$info = $info_qry->fetch_assoc();

$dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id = '{$info['dealer_code']}'");
$dealer_info = $dealer_qry->fetch_assoc();

$sheen_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'sheen' AND `key` = '{$info['sheen']}'");
$sheen = $sheen_qry->fetch_assoc();

function translateVIN($segment, $key) {
  global $dbconn;
  global $info;

  $output = array();

  $custom_keys = ['X', 'Xxx', 'AX', 'DX', 'TX', 'Xx', 'WX', '1cXXXX', '3gXXXX'];

  if($segment === 'finish_code') {
    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE (segment = 'finish_code') AND `key` = '$key'");
  } else {
    $vin_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = '$segment' AND `key` = '$key'");
  }

  $vin = $vin_qry->fetch_assoc();

  $mfg = '';
  $code = '';
  $name = '';
  $ikey = '';
  $desc = '';

  if(!empty($info['custom_vin_info'])) {
    if(in_array($key, $custom_keys)) {
      $custom_info = json_decode($info['custom_vin_info'], true);

      if(count($custom_info[$segment]) > 1) {
        foreach($custom_info[$segment] as $key => $value) {
          $mfg = (stristr($key, 'mfg')) ? $value : $mfg;
          $code = (stristr($key, 'code')) ? $value : $code;
          $name = (stristr($key, 'name')) ? $value : $name;
        }

        $ikey = "{$mfg}-{$code}";
        $desc = "$name";
      } else {
        $ikey = $key;
        $desc = "Custom - " . array_values($custom_info[$segment])[0];
      }
    } else {
      $ikey = $key;
      $desc = $vin['value'];
    }
  } else {
    $ikey = $key;
    $desc = $vin['value'];
  }

  return "$desc";
}

$note_arr = array();

$notes_qry = $dbconn->query("SELECT * FROM notes WHERE (note_type = 'room_note_delivery' OR note_type = 'room_note_global' OR note_type = 'room_note_fin_sample') AND type_id = '$room_id'");

if($notes_qry->num_rows > 0) {
  while($notes = $notes_qry->fetch_assoc()) {
    $note_arr[$notes['note_type']] = $notes['note'];
  }
}

if($_REQUEST['action'] === 'sample_req' || $_REQUEST['action'] === 'no_totals') {
  $hide .= "$('#sample_confirmation').hide();";
  $hide .= "$('#terms_acceptance').hide();";
  $hide .= "$('#sample_qty').css('margin-top', '30px');";
}
?>

<div class="card-box">
  <div class="row">
    <div class="col-md-2 pricing_left_nav no-print sticky">
      <div class="form-group">
        <label for="catalog">Catalog</label>
        <select class="form-control" name="catalog" id="catalog"><option>SMCM, Inc.</option><option>Touchstone</option></select>
      </div>
      <div class="form-group">
        <label for="treeFilter">Search Catalog</label>
        <input type="text" class="form-control fc-simple ignoreSaveAlert" id="treeFilter" placeholder="Find" width="100%" >
      </div>

      <label for="below">Categories</label>
        <?php
        $category_qry = $dbconn->query("SELECT id, name, parent, sort_order FROM pricing_categories WHERE catalog_id = 1");

        $cat_array = array();
        $item_array = array();
        $output = null;

        if($category_qry->num_rows > 0) {
          while($category = $category_qry->fetch_assoc()) {
            $cat_array[$category['parent']][$category['sort_order']] = array('id' => $category['id'], 'name' => $category['name']);
          }
        }

        $item_qry = $dbconn->query("SELECT id, category_id, sku FROM pricing_nomenclature WHERE catalog_id = 1");

        $item_sort_id = 1;
        $prev_item_cat = null;

        if($item_qry->num_rows > 0) {
          while($item = $item_qry->fetch_assoc()) {
            if($prev_item_cat !== $item['category_id']) {
              $item_sort_id = 1;
              $prev_item_cat = $item['category_id'];
            } else {
              $item_sort_id++;
            }

            $item_array[$item['category_id']][$item_sort_id] = array('id' => $item['id'], 'name' => $item['sku']);
          }
        }

        function makeTree($parent, $categories) {
          global $item_array;

          if(isset($categories[$parent]) && count($categories[$parent])) {
            $output = '<ul>';
            ksort($categories[$parent]);

            foreach ($categories[$parent] as $category) {
              $output .= "<li class='ws-wrap'>{$category['name']}";
              $output .= makeTree($category['id'], $categories);
              $output .= makeTree($category['id'], $item_array);
              $output .= '</li>';
            }

            $output .= '</ul>';

            return $output;
          }
        }

        echo makeTree(0, $cat_array);
        ?>
    </div>

    <div class="col-md-10 pricing_table_format">
      <div class="row">
        <div class="col-md-12" style="margin-top:5px;">
          <table style="max-width:828px;" width="100%">
            <tr>
              <td colspan="8" class="text-md-center"><h2>Pricing Program Cover Sheet</h2></td>
            </tr>
            <tr>
              <td colspan="3" width="33.3%"><h3>Quote</h3></td>
              <td colspan="3" width="33.3%" class="text-md-center" id="page_count"></td>
              <td colspan="2" width="33.3%" class="text-md-right">Production Type: Cabinet</td>
            </tr>
            <tr>
              <td colspan="3"><h4>RTWard_Taylor - Kitchen Perimiter</h4></td>
              <td colspan="3" class="text-md-center">Printed <?php echo date(DATE_DEFAULT); ?></td>
              <td colspan="2" class="text-md-right">Production Status: Green</td>
            </tr>
            <tr>
              <td colspan="3">Sales Order #: <strong>667A-1.01</strong></td>
              <td colspan="3">&nbsp;</td>
              <td colspan="2" class="text-md-right">Ship Date: 4/1/2018</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td colspan="3">&nbsp;</td>
              <td colspan="2" class="text-md-right">Delivery Date: ---</td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <th colspan="3">Design</th>
              <th colspan="3">Finish</th>
              <th colspan="2">Delivery</th>
            </tr>
            <tr>
              <td colspan="3" class='gray_bg'><?php echo ($info['construction_method'] !== 'L') ? "Door/Drawer Head" : null; ?></td>
              <td colspan="3" class='gray_bg'>Door/Drawer</td>
              <td colspan="2" class='gray_bg'>&nbsp;</td>
            </tr>
            <tr class="border_top">
              <td class="border_thin_bottom" width="12%">Species/Grade:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('species_grade', $info['species_grade']); ?></td>
              <td width="3%">&nbsp;</td>
              <td class="border_thin_bottom" width="14%">Finish Code:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['finish_code']); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="finish_code_pm" value="">)</span></td>
              <td width="3%">&nbsp;</td>
              <td width="16%"><strong>Ship VIA:</strong></td>
              <td><input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_via" value="<?php echo $info['vin_ship_via']; ?>"></td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Construction:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('construction_method', $info['construction_method']); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Sheen:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('sheen', $info['sheen']); ?></td>
              <td>&nbsp;</td>
              <td><strong>Ship To:</strong></td>
              <td rowspan="3">
                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_1" value="<?php echo $info['name_1']; ?>"><br />
                <input type="text" style="width:125px;" class="static_width align_left border_thin_bottom" name="ship_to_2" value="<?php echo $info['project_addr']; ?>"><br />
                <input type="text" style="width:76px;" class="static_width align_left border_thin_bottom" name="ship_to_city" value="<?php echo $info['project_city']; ?>"> <input type="text" style="width:15px;" class="static_width align_left border_thin_bottom" name="ship_to_state" value="<?php echo $info['project_state']; ?>"> <input type="text" style="width:30px;" class="static_width align_left border_thin_bottom" name="ship_to_zip" value="<?php echo $info['project_zip']; ?>">
              </td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Door Design:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('door_design', $info['door_design']); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="dd_custom_pm" value="">)</span></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Glaze Color:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['glaze']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Door Panel Raise:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_door']); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom">Glaze Technique:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('glaze_technique', $info['glaze_technique']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Short Drawer Raise:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_sd']); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom"><input type="checkbox" id="antiquing" style="margin-left:20px;" <?php echo ($info['antiquing'] !== 'A0') ? "checked" : null; ?> disabled> <label for="antiquing">Antiquing</label></td>
              <td class="border_thin_bottom"><?php echo translateVIN('antiquing', $info['antiquing']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Tall Drawer Raise:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('panel_raise', $info['panel_raise_td']); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom"><input type="checkbox" id="worn_edges" style="margin-left:20px;" <?php echo ($info['worn_edges'] !== 'W0') ? "checked" : null; ?> disabled> <label for="worn_edges">Worn Edges</label></td>
              <td class="border_thin_bottom"><?php echo translateVIN('worn_edges', $info['worn_edges']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Edge Profile:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('edge_profile', $info['edge_profile']); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom"><input type="checkbox" id="distressing" style="margin-left:20px;" <?php echo ($info['distress_level'] !== 'D0') ? "checked" : null; ?> disabled> <label for="distressing">Distressing</label></td>
              <td class="border_thin_bottom"><?php echo translateVIN('distress_level', $info['distress_level']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Framing Bead:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('framing_bead', $info['framing_bead']); ?></td>
              <td>&nbsp;</td>
              <td colspan="2" class='gray_bg border_thin_bottom'>Carcass</td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Frame Option:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('framing_options', $info['framing_options']); ?></td>
              <td>&nbsp;</td>
              <td class="border_thin_bottom"><strong>Exterior</strong> Species:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('carcass_species', $info['carcass_exterior_species']); ?></td>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Styles/Rails:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('style_rail_width', $info['style_rail_width']); ?></td>
              <td style="border:solid #FFF;">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Finish Code:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['carcass_exterior_finish_code']); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="e_finish_code_pm" value="">)</span></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Glaze Color:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['carcass_exterior_glaze_color']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Glaze Technique:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('glaze_technique', $info['carcass_exterior_glaze_technique']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td class="border_thin_bottom">Drawer Box:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('drawer_boxes', $info['drawer_boxes']); ?></td>
              <td style="border:solid #FFF;">&nbsp;</td>
              <td class="border_thin_bottom"><strong>Interior</strong> Species:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('carcass_species', $info['carcass_interior_species']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Finish Code:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('finish_code', $info['carcass_interior_finish_code']); ?> <span class="pull-right arh_highlight">(<input type="text" style="width:80px;text-align:center;" class="arh_highlight static_width" name="i_finish_code_pm" value="">)</span></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Glaze Color:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('glaze', $info['carcass_interior_glaze_color']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="3">&nbsp;</td>
              <td class="border_thin_bottom"><div style="width:20px;float:left;">&nbsp;</div>Glaze Technique:</td>
              <td class="border_thin_bottom"><?php echo translateVIN('glaze_technique', $info['carcass_interior_glaze_technique']); ?></td>
              <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <th colspan="8">Notes</th>
            </tr>
            <tr>
              <td colspan="3" style="border-right:1px solid #000;" class="gray_bg">Global Notes:</td>
              <td colspan="3" class="gray_bg" style="border-right:1px solid #000;">Finishing/Sample Notes:</td>
              <td colspan="2" class="gray_bg">Delivery Notes:</td>
            </tr>
            <tr id="notes_section">
              <td colspan="3" id="global_notes"><textarea name="global_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_global']; ?></textarea></td>
              <td colspan="3" id="layout_notes_title" style="border-right:1px solid #000;"><textarea name="layout_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_fin_sample']; ?></textarea></td>
              <td colspan="2" id="delivery_notes" style="border:none;"><textarea name="delivery_notes" maxlength="280" class="static_width" rows="7"><?php echo $note_arr['room_note_delivery']; ?></textarea></td>
            </tr>
            <tr>
              <th colspan="8">&nbsp;</th>
            </tr>
          </table>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12" style="margin-top:5px;">
          <h2>Cabinet List</h2>
          <h5 class="no-print" style="margin:10px 0;"><span class="cursor-hand no-select" id="catalog_add_item"><i class="zmdi zmdi-plus-circle-o"></i> Add Item</span> <span class="cursor-hand no-select" style="margin-left:10px;display:none;" id="catalog_remove_checked"><i class="zmdi zmdi-minus-circle-outline"></i> Remove Checked Items</span></h5>
          <table id="cabinet_list">
            <colgroup>
              <col width="30px">
              <col width="50px">
              <col width="500px">
              <col width="50px">
            </colgroup>
            <thead>
            <tr> <th></th> <th>#</th> <th>Line Item</th> <th>Price</th></tr>
            </thead>
            <tbody>
            <!-- Define a row template for all invariant markup: -->
            <tr>
              <td class="alignCenter"></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $("body")
    .on("keyup", "#treeFilter", function() {
      // grab this value and filter it down to the node needed
      $(".pricing_left_nav").fancytree("getTree").filterNodes($(this).val());
    })
    .on("click", "#catalog_add_item", function() {
      var root = cabinetList.fancytree("getRootNode");
      var child = root.addChildren({
        title: "Nomenclature...",
        tooltip: "Type your nomenclature here."
      });
    })
    .on("click", "#catalog_remove_checked", function() {
      var tree = cabinetList.fancytree("getTree"),
        selected = tree.getSelectedNodes();

      selected.forEach(function(node) {
        node.remove();
      });

      cabinetList.fancytree("getRootNode").render(true,true);

      $(this).hide();
    })
  ;

  var CLIPBOARD = null;
  var cabinetList = $("#cabinet_list");

  $(function(){
    cabinetList.fancytree({
      select: function(event, data) {
        // Display list of selected nodes
        var selNodes = data.tree.getSelectedNodes();
        // convert to title/key array
        var selKeys = $.map(selNodes, function(node){
          return "[" + node.key + "]: '" + node.title + "'";
        });
        console.log(selKeys.join(", "));

        if(selKeys.length > 0) {
          $("#catalog_remove_checked").show();
        } else {
          $("#catalog_remove_checked").hide();
        }
      },
      cookieId: "fancytree-cabList",
      idPrefix: "fancytree-cabList-",
      checkbox: true,
      titlesTabbable: true,     // Add all node titles to TAB chain
      quicksearch: true,        // Jump to nodes when pressing first character
      source: { url: "/html/pricing/ajax-tree-products.json"},
      extensions: ["edit", "dnd", "table", "gridnav"],
      debugLevel: 0,
      dnd: { // drag and drop
        preventVoidMoves: true,
        preventRecursiveMoves: true,
        autoExpandMS: 400,
        dragStart: function(node, data) {
          return true;
        },
        dragEnter: function(node, data) {
          // return ["before", "after"];
          return true;
        },
        dragDrop: function(node, data) {
          data.otherNode.moveTo(node, data.hitMode);
        }
      },
      edit: {
        triggerStart: ["f2", "shift+click", "mac+enter"],
        close: function(event, data) {
          if( data.save && data.isNew ){
            // Quick-enter: add new nodes until we hit [enter] on an empty title
            $("#tree").trigger("nodeCommand", {cmd: "addSibling"});
          }
        }
      },
      table: {
        indentation: 20,
        nodeColumnIdx: 2,
        checkboxColumnIdx: 0
      },
      gridnav: {
        autofocusInput: false,
        handleCursorKeys: true
      },

      lazyLoad: function(event, data) {
        data.result = {url: "../demo/ajax-sub2.json"};
      },
      createNode: function(event, data) {
        var node = data.node,
          $tdList = $(node.tr).find(">td");

        // Span the remaining columns if it's a folder.
        // We can do this in createNode instead of renderColumns, because
        // the `isFolder` status is unlikely to change later
        if( node.isFolder() ) {
          $tdList.eq(2)
            .prop("colspan", 6)
            .nextAll().remove();
        }
      },
      renderColumns: function(event, data) {
        var node = data.node, $tdList = $(node.tr).find(">td");

        // (Index #0 is rendered by fancytree by adding the checkbox)
        // Set column #1 info from node data:
        $tdList.eq(1).text(node.getIndexHier());
        // (Index #2 is rendered by fancytree)
        // Set column #3 info from node data:
        $tdList.eq(3).text(node.data.year);

        // Static markup (more efficiently defined as html row template):
        // $tdList.eq(3).html("<input type='input' value='" + "" + "'>");
        // ...
      }
    }).on("nodeCommand", function(event, data){
      // Custom event handler that is triggered by keydown-handler and
      // context menu:
      var refNode, moveMode,
        tree = $(this).fancytree("getTree"),
        node = tree.getActiveNode();

      switch( data.cmd ) {
        case "moveUp":
          refNode = node.getPrevSibling();
          if( refNode ) {
            node.moveTo(refNode, "before");
            node.setActive();
          }
          break;
        case "moveDown":
          refNode = node.getNextSibling();
          if( refNode ) {
            node.moveTo(refNode, "after");
            node.setActive();
          }
          break;
        case "indent":
          refNode = node.getPrevSibling();
          if( refNode ) {
            node.moveTo(refNode, "child");
            refNode.setExpanded();
            node.setActive();
          }
          break;
        case "outdent":
          if( !node.isTopLevel() ) {
            node.moveTo(node.getParent(), "after");
            node.setActive();
          }
          break;
        case "rename":
          node.editStart();
          break;
        case "remove":
          refNode = node.getNextSibling() || node.getPrevSibling() || node.getParent();
          node.remove();
          if( refNode ) {
            refNode.setActive();
          }
          break;
        case "addChild":
          node.editCreateNode("child", "");
          break;
        case "addSibling":
          node.editCreateNode("after", "");
          break;
        case "cut":
          CLIPBOARD = {mode: data.cmd, data: node};
          break;
        case "copy":
          CLIPBOARD = {
            mode: data.cmd,
            data: node.toDict(function(n){
              delete n.key;
            })
          };
          break;
        case "clear":
          CLIPBOARD = null;
          break;
        case "paste":
          if( CLIPBOARD.mode === "cut" ) {
            // refNode = node.getPrevSibling();
            CLIPBOARD.data.moveTo(node, "child");
            CLIPBOARD.data.setActive();
          } else if( CLIPBOARD.mode === "copy" ) {
            node.addChildren(CLIPBOARD.data).setActive();
          }
          break;
        default:
          alert("Unhandled command: " + data.cmd);
          return;
      }
    }).on("keydown", function(e){
      var cmd = null;

      // console.log(e.type, $.ui.fancytree.eventToString(e));
      switch( $.ui.fancytree.eventToString(e) ) {
        case "ctrl+shift+n":
        case "meta+shift+n": // mac: cmd+shift+n
          cmd = "addChild";
          break;
        case "ctrl+c":
        case "meta+c": // mac
          cmd = "copy";
          break;
        case "ctrl+v":
        case "meta+v": // mac
          cmd = "paste";
          break;
        case "ctrl+x":
        case "meta+x": // mac
          cmd = "cut";
          break;
        case "ctrl+n":
        case "meta+n": // mac
          cmd = "addSibling";
          break;
        case "del":
        case "meta+backspace": // mac
          cmd = "remove";
          break;
        // case "f2":  // already triggered by ext-edit pluging
        //   cmd = "rename";
        //   break;
        case "ctrl+up":
          cmd = "moveUp";
          break;
        case "ctrl+down":
          cmd = "moveDown";
          break;
        case "ctrl+right":
        case "ctrl+shift+right": // mac
          cmd = "indent";
          break;
        case "ctrl+left":
        case "ctrl+shift+left": // mac
          cmd = "outdent";
      }
      if( cmd ){
        $(this).trigger("nodeCommand", {cmd: cmd});
        return false;
      }
    });

    /*
     * Context menu (https://github.com/mar10/jquery-ui-contextmenu)
     */
    cabinetList.contextmenu({
      delegate: "span.fancytree-node",
      menu: [
        {title: "Edit <kbd>[F2]</kbd>", cmd: "rename", uiIcon: "ui-icon-pencil" },
        {title: "Delete <kbd>[Del]</kbd>", cmd: "remove", uiIcon: "ui-icon-trash" },
        {title: "----"},
        {title: "New sibling <kbd>[Ctrl+N]</kbd>", cmd: "addSibling", uiIcon: "ui-icon-plus" },
        {title: "New child <kbd>[Ctrl+Shift+N]</kbd>", cmd: "addChild", uiIcon: "ui-icon-arrowreturn-1-e" },
        {title: "----"},
        {title: "Cut <kbd>Ctrl+X</kbd>", cmd: "cut", uiIcon: "ui-icon-scissors"},
        {title: "Copy <kbd>Ctrl-C</kbd>", cmd: "copy", uiIcon: "ui-icon-copy"},
        {title: "Paste as child<kbd>Ctrl+V</kbd>", cmd: "paste", uiIcon: "ui-icon-clipboard", disabled: true }
      ],
      beforeOpen: function(event, ui) {
        var node = $.ui.fancytree.getNode(ui.target);
        $("#tree").contextmenu("enableEntry", "paste", !!CLIPBOARD);
        node.setActive();
      },
      select: function(event, ui) {
        var that = this;
        // delay the event, so the menu can close and the click event does
        // not interfere with the edit control
        setTimeout(function(){
          $(that).trigger("nodeCommand", {cmd: ui.cmd});
        }, 100);
      }
    });
  });

  $(".pricing_left_nav").fancytree({
    icon: false,
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
    }
  });
</script>