<?php
require '../../includes/header_start.php';

outputPHPErrs();
?>

<div class="card-box">
  <div class="row">
    <div class="col-md-2 pricing_left_nav">
        <?php
/*        $category_qry = $dbconn->query("SELECT * FROM pricing_categories");

        $cat_array = array();
        $output = null;

        if($category_qry->num_rows > 0) {
          while($category = $category_qry->fetch_assoc()) {
            $cat_array[$category['parent']][$category['sort_order']] = array('id' => $category['id'], 'name' => $category['name']);
          }
        }

        function makeTree($parent, $categories) {
          if(isset($categories[$parent]) && count($categories[$parent])) {
            $output = '<ul';

            if($parent === 0)
              $output .= ' class="main_level"';

            $output .= '>';
            ksort($categories[$parent]);

            foreach ($categories[$parent] as $category) {
              $output .= '<li';

              if($parent === 0)
                $output .= ' class="sub_level"';

              $output .= '><span>' . $category['name'] . '</span>';
              $output .= makeTree($category['id'], $categories);
              $output .= '</li>';
            }

            $output .= '</ul>';

            return $output;
          }
        }

        echo makeTree(0, $cat_array);
        */?>







      <ul class="main_level">
        <li class="pricing_menu_item" data-parent="true"><span><i class="zmdi zmdi-chevron-right"></i> Wall</span></li>
        <li class="child">
          <ul class="sub_level">
            <li class="pricing_menu_item" data-parent="true"><span><i class="zmdi zmdi-chevron-right"></i> Wall - 13"</span></li>
            <li class="child">
              <ul class="sub_level">
                <li class="pricing_menu_item" data-parent="true"><span><i class="zmdi zmdi-chevron-right"></i> Single Face</span></li>
                <li class="child">
                  <ul class="sub_level">
                    <li class="pricing_menu_item" data-parent="true"><span><i class="zmdi zmdi-chevron-right"></i> Vertical Lift</span></li>
                    <li class="child">
                      <ul class="sub_level">
                        <li class="pricing_menu_item" data-parent="true"><span><i class="zmdi zmdi-chevron-right"></i> 1 Door - Manual</span></li>
                        <li class="child">
                          <ul class="sub_level">
                            <li class="line_item"><span>1 Door Single Face</span></li>
                            <li class="line_item"><span>1 Door Two Face</span></li>
                          </ul>
                        </li>
                        <li class="pricing_menu_item" data-parent="true"><span><i class="zmdi zmdi-chevron-right"></i> 1 Door - Powered</span></li>
                      </ul>
                    </li>
                    <li class="pricing_menu_item" data-parent="true"><span><i class="zmdi zmdi-chevron-right"></i> Lift & Stay</span></li>
                    <li class="child">
                      <ul class="sub_level">
                        <li class="pricing_menu_item"><span>1 Door</span></li>
                        <li class="pricing_menu_item"><span>Bi-fold Door</span></li>
                      </ul>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
            <li class="pricing_menu_item" ><span>Sub Level 2</span></li>
            <li class="pricing_menu_item" ><span>Sub Level 3</span></li>
          </ul>
        </li>
        <li class="pricing_menu_item"><span>Main Level 2</span></li>
        <li class="pricing_menu_item"><span>Main Level 3</span></li>
      </ul>
    </div>

    <div class="col-md-10">
      <script type="text/javascript">
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
      </script>


      <h1>Cabinet List</h1>
      <h5><i class="zmdi zmdi-plus-circle-o"></i> Add Item</h5>
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
</script>