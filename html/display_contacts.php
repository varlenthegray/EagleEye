<?php require '../includes/header_start.php'; ?>

<div class="row">
  <div class="col-md-6">
    <div class="card-box">
      <h1>Contacts</h1>

      <div id="contact_tree">
        <input type="text" class="form-control ignoreSaveAlert" id="treeFilter" placeholder="Search..." style="width:30%;" /><br />

        <ul>
          <?php
          $contype_qry = $dbconn->query("SELECT * FROM contact_types WHERE permission_level <= '{$_SESSION['permissions']['contact_permission']}' ORDER BY description ASC;");

          if($contype_qry->num_rows > 0) {
            while($contype = $contype_qry->fetch_assoc()) {
              $children = null;

              $personal_filter = ($contype['description'] === 'Personal') ? "AND created_by = {$_SESSION['userInfo']['id']}" : null;

              $dealer = substr($_SESSION['userInfo']['dealer_code'], 0, 3);

              $dealer_filter = ((bool)$_SESSION['userInfo']['dealer']) ? "AND d.dealer_id LIKE '%$dealer%'" : null;

              $company_qry = $dbconn->query("SELECT DISTINCT(company_name) FROM contact c LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id WHERE type = '{$contype['id']}' $personal_filter $dealer_filter ORDER BY company_name ASC");

              if($company_qry->num_rows > 0) {
                $children = '<ul>';

                while($company = $company_qry->fetch_assoc()) {
                  $subchild_qry = $dbconn->query("SELECT c.* FROM contact c LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id WHERE company_name = '{$company['company_name']}' AND type = '{$contype['id']}' $personal_filter $dealer_filter ORDER BY first_name, last_name ASC");

                  if($subchild_qry->num_rows > 0) {
                    $subchildren = '<ul>';

                    while($subchild = $subchild_qry->fetch_assoc()) {
                      $name = (empty($subchild['first_name']) && empty($subchild['last_name'])) ? $subchild['company_name'] : "{$subchild['first_name']} {$subchild['last_name']}";

                      $subchildren .= "<li id='{$subchild['id']}' data-icon='fa fa-user'>$name</li>";
                    }

                    $subchildren .= '</ul>';
                  } else {
                    $subchildren = null;
                  }

                  $company_name = empty($company['company_name']) ? '<em>Individuals</em>' : $company['company_name'];


                  $children .= "<li class='folder' data-icon='fa fa-group'>$company_name $subchildren</li>";
                }

                $children .= "</ul>";
              }

              echo "<li class='folder'>{$contype['description']} $children</li>";
            }
          }
          ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  $(function() {
    $("#contact_tree").fancytree({
      extensions: ["filter"],
      filter: {
        counter: false,
        mode: "hide",
        autoExpand: true,
        fuzzy: true
      },
      autoScroll: true,
      click: function(e, data) {
        var node = data.node;

        if(!node.isFolder()) {
          $.post("/html/add_contact.php?action=edit", {id: node.key}, function(data) {
            $("#modalGlobal").html(data).modal('show');
          });
        }
      },
      beforeSelect: function(e, data) {
        if(data.node.isFolder()) {
          return false;
        }
      }
    });

    $.ui.fancytree.debugLevel = 0; // shutting FancyTree up
  });

  // filters the view on keyup
  $("body")
    .on("keyup", "#treeFilter", function() {
      // grab this value and filter it down to the node needed
      $("#contact_tree").fancytree("getTree").filterNodes($(this).val());
    })
    .on("change", "#contact_type", function() {
      if($(this).find("option:selected").text() === 'Dealer') {
        $("#dealer_code").show();
      } else {
        $("#dealer_code").hide();
      }
    })
  ;
</script>