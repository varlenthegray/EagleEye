<?php
require '../../includes/header_start.php';

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

  return "$ikey = $desc";
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
    <div class="col-md-2 pricing_left_nav no-print">
      <input type="text" class="form-control fc-simple ignoreSaveAlert" id="treeFilter" placeholder="Find" >
        <?php
        $category_qry = $dbconn->query("SELECT * FROM pricing_categories");

        $cat_array = array();
        $output = null;

        if($category_qry->num_rows > 0) {
          while($category = $category_qry->fetch_assoc()) {
            $cat_array[$category['parent']][$category['sort_order']] = array('id' => $category['id'], 'name' => $category['name']);
          }
        }

        function makeTree($parent, $categories) {
          if(isset($categories[$parent]) && count($categories[$parent])) {
            $output = '<ul>';
            ksort($categories[$parent]);

            foreach ($categories[$parent] as $category) {
              $output .= "<li class='ws-wrap'>{$category['name']}";
              $output .= makeTree($category['id'], $categories);
              $output .= '</li>';
            }

            $output .= '</ul>';

            return $output;
          }
        }

        echo makeTree(0, $cat_array);
        ?>
    </div>

    <div class="col-md-10">
      <div class="row">
        <div class="col-md-12" style="margin-top:5px;">
          <table style="max-width:828px;" width="100%">
            <tr>
              <td colspan="8" class="text-md-center"><h2>Pricing Program Cover Sheet</h2></td>
            </tr>
            <tr>
              <td colspan="3" width="33.3%"><h3>Quote</h3></td>
              <td colspan="3" width="33.3%" class="text-md-center">Page 1/1</td>
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
          <h5 class="no-print"><i class="zmdi zmdi-plus-circle-o"></i> Add Item</h5>
          <table id="tree">
            <colgroup>
              <col width="30px">
              <col width="50px">
              <col width="350px">
              <col width="50px">
              <col width="50px">
              <col width="30px">
              <col width="30px">
              <col width="50px">
            </colgroup>
            <thead>
            <tr> <th></th> <th>#</th> <th></th> <th>Ed1</th> <th>Ed2</th> <th>Rb1</th> <th>Rb2</th> <th>Cb</th></tr>
            </thead>
            <tbody>
            <!-- Define a row template for all invariant markup: -->
            <tr>
              <td class="alignCenter"></td>
              <td></td>
              <td></td>
              <td><input name="input1" type="input"></td>
              <td><input name="input2" type="input"></td>
              <td class="alignCenter"><input name="cb1" type="checkbox"></td>
              <td class="alignCenter"><input name="cb2" type="checkbox"></td>
              <td>
                <select name="sel1" id="">
                  <option value="a">A</option>
                  <option value="b">B</option>
                </select>
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
  $("body").on("click", ".pricing_menu_item", function() {
    if($(this).attr('data-parent') === 'true') {
      if($(this).next('li').is(":visible")) {
        $(this).next('li').hide();
        $(this).find('i').removeClass("zmdi-chevron-down").addClass("zmdi-chevron-right");
      } else {
        $(this).next('li').show();
        $(this).find('i').removeClass("zmdi-chevron-right").addClass("zmdi-chevron-down");
      }
    }
  });

  var CLIPBOARD = null;
  /*
    SOURCE = [
      {title: "node 1", folder: true, expanded: true, children: [
        {title: "node 1.1", foo: "a"},
        {title: "node 1.2", foo: "b"}
       ]},
      {title: "node 2", folder: true, expanded: false, children: [
        {title: "node 2.1", foo: "c"},
        {title: "node 2.2", foo: "d"}
       ]}
    ];
  */

  $(function(){

    $("#tree").fancytree({
      checkbox: true,
      titlesTabbable: true,     // Add all node titles to TAB chain
      quicksearch: true,        // Jump to nodes when pressing first character
      // source: SOURCE,
      source: { url: "/html/pricing/ajax-tree-products.json"},

      extensions: ["edit", "dnd", "table", "gridnav"],

      dnd: {
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
        var node = data.node,
          $tdList = $(node.tr).find(">td");

        // (Index #0 is rendered by fancytree by adding the checkbox)
        // Set column #1 info from node data:
        $tdList.eq(1).text(node.getIndexHier());
        // (Index #2 is rendered by fancytree)
        // Set column #3 info from node data:
        $tdList.eq(3).find("input").val(node.key);
        $tdList.eq(4).find("input").val(node.data.foo);

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

      // }).on("click dblclick", function(e){
      //   console.log( e, $.ui.fancytree.eventToString(e) );

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
        // e.preventDefault();
        // e.stopPropagation();
        return false;
      }
    });

    /*
     * Tooltips
     */
    // $("#tree").tooltip({
    //   content: function () {
    //     return $(this).attr("title");
    //   }
    // });

    /*
     * Context menu (https://github.com/mar10/jquery-ui-contextmenu)
     */
    $("#tree").contextmenu({
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
    icon: false
  });
</script>