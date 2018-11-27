<?php
require_once '../../../includes/header_start.php';

$so_num = sanitizeInput($_REQUEST['so_num']);

$so_qry = $dbconn->query("SELECT * FROM sales_order WHERE so_num = '$so_num'");
$so = $so_qry->fetch_assoc();
?>

<script>
  function checkEmptyFields(group) {
    let i = 0;

    $(group + " input").each(function() {
      if($(this).val() !== '') {
        i++;
      }
    });

    if(i === 0) {
      $(group).hide();
    }
  }
</script>

<div class="container-fluid">
  <form id="form_so_<?php echo $so['so_num']; ?>">
    <div class="row">
      <div class="col-md-3">
        <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
          <?php if((bool)$_SESSION['userInfo']['dealer']) {
            $dealer_code = ucwords($_SESSION['userInfo']['username']);

            echo "<input type='hidden' name='dealer_code' id='dealer_code' value='$dealer_code'>";
            ?>
            <tr>
              <td colspan="3">
                <input type="text" name="project_name" class="form-control pull-left" placeholder="Project Name" id="project_name" value="<?php echo $so['project_name']; ?>" style="width:50%;" />
                <input type="text" name="project_addr" class="form-control pull-left" placeholder="Job Site Address" id="project_addr"value="<?php echo $so['project_addr']; ?>" style="width:50%;">
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <table style="width: 100%;">
                  <tr>
                    <td style="width: 33.3%;"><input type="text" name="project_city" class="form-control" placeholder="Job Site City" value="<?php echo $so['project_city']; ?>" id="project_city"></td>
                    <td style="width: 33.3%;"><select class="form-control" id="project_state" name="project_state">
                        <option value="AL" <?php echo ($so['project_state'] === 'AL') ? 'selected' : null; ?>>Alabama</option>
                        <option value="AK" <?php echo ($so['project_state'] === 'AK') ? 'selected' : null; ?>>Alaska</option>
                        <option value="AR" <?php echo ($so['project_state'] === 'AR') ? 'selected' : null; ?>>Arkansas</option>
                        <option value="CA" <?php echo ($so['project_state'] === 'CA') ? 'selected' : null; ?>>California</option>
                        <option value="CO" <?php echo ($so['project_state'] === 'CO') ? 'selected' : null; ?>>Colorado</option>
                        <option value="CT" <?php echo ($so['project_state'] === 'CT') ? 'selected' : null; ?>>Connecticut</option>
                        <option value="DE" <?php echo ($so['project_state'] === 'DE') ? 'selected' : null; ?>>Delaware</option>
                        <option value="FL" <?php echo ($so['project_state'] === 'FL') ? 'selected' : null; ?>>Florida</option>
                        <option value="GA" <?php echo ($so['project_state'] === 'GA') ? 'selected' : null; ?>>Georgia</option>
                        <option value="HI" <?php echo ($so['project_state'] === 'HI') ? 'selected' : null; ?>>Hawaii</option>
                        <option value="ID" <?php echo ($so['project_state'] === 'ID') ? 'selected' : null; ?>>Idaho</option>
                        <option value="IL" <?php echo ($so['project_state'] === 'IL') ? 'selected' : null; ?>>Illinois</option>
                        <option value="IN" <?php echo ($so['project_state'] === 'IN') ? 'selected' : null; ?>>Indiana</option>
                        <option value="IA" <?php echo ($so['project_state'] === 'IA') ? 'selected' : null; ?>>Iowa</option>
                        <option value="KS" <?php echo ($so['project_state'] === 'KS') ? 'selected' : null; ?>>Kansas</option>
                        <option value="KY" <?php echo ($so['project_state'] === 'KY') ? 'selected' : null; ?>>Kentucky</option>
                        <option value="LA" <?php echo ($so['project_state'] === 'LA') ? 'selected' : null; ?>>Louisiana</option>
                        <option value="ME" <?php echo ($so['project_state'] === 'ME') ? 'selected' : null; ?>>Maine</option>
                        <option value="MD" <?php echo ($so['project_state'] === 'MD') ? 'selected' : null; ?>>Maryland</option>
                        <option value="MA" <?php echo ($so['project_state'] === 'MA') ? 'selected' : null; ?>>Massachusetts</option>
                        <option value="MI" <?php echo ($so['project_state'] === 'MI') ? 'selected' : null; ?>>Michigan</option>
                        <option value="MN" <?php echo ($so['project_state'] === 'MN') ? 'selected' : null; ?>>Minnesota</option>
                        <option value="MS" <?php echo ($so['project_state'] === 'MS') ? 'selected' : null; ?>>Mississippi</option>
                        <option value="MO" <?php echo ($so['project_state'] === 'MO') ? 'selected' : null; ?>>Missouri</option>
                        <option value="MT" <?php echo ($so['project_state'] === 'MT') ? 'selected' : null; ?>>Montana</option>
                        <option value="NE" <?php echo ($so['project_state'] === 'NE') ? 'selected' : null; ?>>Nebraska</option>
                        <option value="NV" <?php echo ($so['project_state'] === 'NV') ? 'selected' : null; ?>>Nevada</option>
                        <option value="NH" <?php echo ($so['project_state'] === 'NH') ? 'selected' : null; ?>>New Hampshire</option>
                        <option value="NJ" <?php echo ($so['project_state'] === 'NJ') ? 'selected' : null; ?>>New Jersey</option>
                        <option value="NM" <?php echo ($so['project_state'] === 'NM') ? 'selected' : null; ?>>New Mexico</option>
                        <option value="NY" <?php echo ($so['project_state'] === 'NY') ? 'selected' : null; ?>>New York</option>
                        <option value="NC" <?php echo ($so['project_state'] === 'NC') ? 'selected' : null; ?>>North Carolina</option>
                        <option value="ND" <?php echo ($so['project_state'] === 'ND') ? 'selected' : null; ?>>North Dakota</option>
                        <option value="OH" <?php echo ($so['project_state'] === 'OH') ? 'selected' : null; ?>>Ohio</option>
                        <option value="OK" <?php echo ($so['project_state'] === 'OK') ? 'selected' : null; ?>>Oklahoma</option>
                        <option value="OR" <?php echo ($so['project_state'] === 'OR') ? 'selected' : null; ?>>Oregon</option>
                        <option value="PA" <?php echo ($so['project_state'] === 'PA') ? 'selected' : null; ?>>Pennsylvania</option>
                        <option value="RI" <?php echo ($so['project_state'] === 'RI') ? 'selected' : null; ?>>Rhode Island</option>
                        <option value="SC" <?php echo ($so['project_state'] === 'SC') ? 'selected' : null; ?>>South Carolina</option>
                        <option value="SD" <?php echo ($so['project_state'] === 'SD') ? 'selected' : null; ?>>South Dakota</option>
                        <option value="TN" <?php echo ($so['project_state'] === 'TN') ? 'selected' : null; ?>>Tennessee</option>
                        <option value="TX" <?php echo ($so['project_state'] === 'TX') ? 'selected' : null; ?>>Texas</option>
                        <option value="UT" <?php echo ($so['project_state'] === 'UT') ? 'selected' : null; ?>>Utah</option>
                        <option value="VT" <?php echo ($so['project_state'] === 'VT') ? 'selected' : null; ?>>Vermont</option>
                        <option value="VA" <?php echo ($so['project_state'] === 'VA') ? 'selected' : null; ?>>Virginia</option>
                        <option value="WA" <?php echo ($so['project_state'] === 'WA') ? 'selected' : null; ?>>Washington</option>
                        <option value="WV" <?php echo ($so['project_state'] === 'WV') ? 'selected' : null; ?>>West Virginia</option>
                        <option value="WI" <?php echo ($so['project_state'] === 'WI') ? 'selected' : null; ?>>Wisconsin</option>
                        <option value="WY" <?php echo ($so['project_state'] === 'WY') ? 'selected' : null; ?>>Wyoming</option>
                      </select></td>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['project_zip']; ?>" name="project_zip" class="form-control" placeholder="Job Site Zip" id="project_zip"></td>
                  </tr>
                </table>
              </td>
              <td><input type="text" name="project_landline" class="form-control" placeholder="Job Site Landline" value="<?php echo $so['project_landline']; ?>" id="project_landline"></td>
            </tr>
            <tr>
              <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
            </tr>
            <tr>
              <td colspan="3"><h5>Contacts</h5></td>
            </tr>
          <?php
          // TODO: Clean the duplicate up between this and a normal SO (non-dealer)
          $contact_dropdown = null;

          $dealer = substr($_SESSION['userInfo']['dealer_code'], 0, 3);

          $contact_qry = $dbconn->query("SELECT c.id, c.first_name, c.last_name, c.company_name, c2.description FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id WHERE d.dealer_id LIKE '%$dealer%' ORDER BY c2.description, c.first_name, c.last_name ASC");

          if($contact_qry->num_rows > 0) {
            $contact_dropdown = "<select class='form-control pull-left add_contact_id ignoreSaveAlert' name='add_contact' style='width:50%;'>";

            $last_group = null;

            while($contact = $contact_qry->fetch_assoc()) {
              if($contact['description'] !== $last_group) {
                $contact_dropdown .= "</optgroup><optgroup label='{$contact['description']}'>";
                $last_group = $contact['description'];
              }

              $name = !empty($contact['first_name']) ? "{$contact['first_name']} {$contact['last_name']}" : $contact['company_name'];

              $contact_dropdown .= "<option value='{$contact['id']}'>$name</option>";
            }

            $contact_dropdown .= '</optgroup></select>';
          }

          echo "<tr><td><div class='form-group'><label for='add_contact' class='pull-left' style='line-height:28px;padding-right:10px;'>Add Contact</label> $contact_dropdown <button type='button' class='btn waves-effect waves-light btn-primary assign_contact_so' style='margin:2px 0 0 10px;'> <i class='zmdi zmdi-plus-circle-o'></i> </button></div></td></tr>";

          // displaying existing contact relationships
          $so_contacts_qry = $dbconn->query("SELECT c.*, c2.description FROM sales_order_contacts soc LEFT JOIN contact c ON soc.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE so_id = '{$so['id']}' ORDER BY c.first_name, c.last_name ASC");

          if($so_contacts_qry->num_rows > 0) {
            while($so_contacts = $so_contacts_qry->fetch_assoc()) {
              $name = !empty($so_contacts['first_name']) ? "{$so_contacts['first_name']} {$so_contacts['last_name']}" : $so_contacts['company_name'];

              echo "<tr><td colspan='3'><button type='button' class='btn waves-effect waves-light btn-danger remove_assigned_contact_so' style='margin:2px 0;' data-id='{$so_contacts['id']}'> <i class='zmdi zmdi-minus-circle-outline'></i> </button> <a href='#' class='get_customer_info' data-view-id='{$so_contacts['id']}''>$name ({$so_contacts['description']})</a></td></tr>";
            }
          } else {
            echo "<tr><td colspan='3'><strong>No Contacts Assigned</strong></td></tr>";
          }
          ?>
          <?php } else { ?>
            <tr>
              <td style="width: 33.3%;">
                <select class="form-control" id="dealer_code" name="dealer_code">
                  <?php
                  if($_SESSION['userInfo']['dealer']) {
                    $where_clause = "WHERE dealer_id LIKE '{$_SESSION['userInfo']['dealer_code']}%'";
                  } else {
                    $where_clause = null;
                  }

                  $dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id $where_clause ORDER BY dealer_id ASC;");

                  while ($dealer = $dealer_qry->fetch_assoc()) {
                    $selected = ($dealer['dealer_id'] === $so['dealer_code']) ? 'selected' : NULL;

                    $name = (empty($dealer['first_name']) && empty($dealer['last_name'])) ? $dealer['company_name'] : "{$dealer['first_name']} {$dealer['last_name']}";

                    echo "<option value='{$dealer['dealer_id']}' $selected>{$dealer['dealer_id']} ($name)</option>";
                  }
                  ?>
                </select>
              </td>
              <td colspan="2"><div style="margin-left:10px;" class="checkbox"><input id="show_all_fields" onchange="showAllFields();" class="ignoreSaveAlert" type="checkbox" value="1"><label for="show_all_fields"> Show All Fields</label></div></td>

              <script>
                function showAllFields() {
                  if($("#show_all_fields").is(":checked")) {
                    $(".name1group").show();
                    $(".name2group").show();
                    $('.s_addr_empty').show();
                    $('.con_empty').show();
                    $('.billing_empty').show();
                  } else {
                    checkEmptyFields(".name1group");
                    checkEmptyFields(".name2group");
                    $('.s_addr_empty').hide();
                    $('.con_empty').hide();
                    $('.billing_empty').hide();
                  }
                }
              </script>
            </tr>
            <tr style="height: 5px;">
              <td colspan="3"></td>
            </tr>
            <tr>
              <td colspan="3">
                <input type="text" value="<?php echo $so['project_name']; ?>" name="project_name" class="form-control pull-left" placeholder="Project Name" id="project_name" style="width:50%;"/>
                <input type="text" value="<?php echo $so['project_addr']; ?>" name="project_addr" class="form-control pull-left" placeholder="Project Address" id="project_addr" style="width:50%;">
              </td>
            </tr>
            <tr>
              <td colspan="2" width="80%">
                <table style="width: 100%;">
                  <tr>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['project_city']; ?>" name="project_city" class="form-control" placeholder="Project City" id="project_city"></td>
                    <td style="width: 33.3%;"><select class="form-control" id="project_state" name="project_state">
                        <option value="AL" <?php echo ($so['project_state'] === 'AL') ? 'selected' : null; ?>>Alabama</option>
                        <option value="AK" <?php echo ($so['project_state'] === 'AK') ? 'selected' : null; ?>>Alaska</option>
                        <option value="AR" <?php echo ($so['project_state'] === 'AR') ? 'selected' : null; ?>>Arkansas</option>
                        <option value="CA" <?php echo ($so['project_state'] === 'CA') ? 'selected' : null; ?>>California</option>
                        <option value="CO" <?php echo ($so['project_state'] === 'CO') ? 'selected' : null; ?>>Colorado</option>
                        <option value="CT" <?php echo ($so['project_state'] === 'CT') ? 'selected' : null; ?>>Connecticut</option>
                        <option value="DE" <?php echo ($so['project_state'] === 'DE') ? 'selected' : null; ?>>Delaware</option>
                        <option value="FL" <?php echo ($so['project_state'] === 'FL') ? 'selected' : null; ?>>Florida</option>
                        <option value="GA" <?php echo ($so['project_state'] === 'GA') ? 'selected' : null; ?>>Georgia</option>
                        <option value="HI" <?php echo ($so['project_state'] === 'HI') ? 'selected' : null; ?>>Hawaii</option>
                        <option value="ID" <?php echo ($so['project_state'] === 'ID') ? 'selected' : null; ?>>Idaho</option>
                        <option value="IL" <?php echo ($so['project_state'] === 'IL') ? 'selected' : null; ?>>Illinois</option>
                        <option value="IN" <?php echo ($so['project_state'] === 'IN') ? 'selected' : null; ?>>Indiana</option>
                        <option value="IA" <?php echo ($so['project_state'] === 'IA') ? 'selected' : null; ?>>Iowa</option>
                        <option value="KS" <?php echo ($so['project_state'] === 'KS') ? 'selected' : null; ?>>Kansas</option>
                        <option value="KY" <?php echo ($so['project_state'] === 'KY') ? 'selected' : null; ?>>Kentucky</option>
                        <option value="LA" <?php echo ($so['project_state'] === 'LA') ? 'selected' : null; ?>>Louisiana</option>
                        <option value="ME" <?php echo ($so['project_state'] === 'ME') ? 'selected' : null; ?>>Maine</option>
                        <option value="MD" <?php echo ($so['project_state'] === 'MD') ? 'selected' : null; ?>>Maryland</option>
                        <option value="MA" <?php echo ($so['project_state'] === 'MA') ? 'selected' : null; ?>>Massachusetts</option>
                        <option value="MI" <?php echo ($so['project_state'] === 'MI') ? 'selected' : null; ?>>Michigan</option>
                        <option value="MN" <?php echo ($so['project_state'] === 'MN') ? 'selected' : null; ?>>Minnesota</option>
                        <option value="MS" <?php echo ($so['project_state'] === 'MS') ? 'selected' : null; ?>>Mississippi</option>
                        <option value="MO" <?php echo ($so['project_state'] === 'MO') ? 'selected' : null; ?>>Missouri</option>
                        <option value="MT" <?php echo ($so['project_state'] === 'MT') ? 'selected' : null; ?>>Montana</option>
                        <option value="NE" <?php echo ($so['project_state'] === 'NE') ? 'selected' : null; ?>>Nebraska</option>
                        <option value="NV" <?php echo ($so['project_state'] === 'NV') ? 'selected' : null; ?>>Nevada</option>
                        <option value="NH" <?php echo ($so['project_state'] === 'NH') ? 'selected' : null; ?>>New Hampshire</option>
                        <option value="NJ" <?php echo ($so['project_state'] === 'NJ') ? 'selected' : null; ?>>New Jersey</option>
                        <option value="NM" <?php echo ($so['project_state'] === 'NM') ? 'selected' : null; ?>>New Mexico</option>
                        <option value="NY" <?php echo ($so['project_state'] === 'NY') ? 'selected' : null; ?>>New York</option>
                        <option value="NC" <?php echo ($so['project_state'] === 'NC') ? 'selected' : null; ?>>North Carolina</option>
                        <option value="ND" <?php echo ($so['project_state'] === 'ND') ? 'selected' : null; ?>>North Dakota</option>
                        <option value="OH" <?php echo ($so['project_state'] === 'OH') ? 'selected' : null; ?>>Ohio</option>
                        <option value="OK" <?php echo ($so['project_state'] === 'OK') ? 'selected' : null; ?>>Oklahoma</option>
                        <option value="OR" <?php echo ($so['project_state'] === 'OR') ? 'selected' : null; ?>>Oregon</option>
                        <option value="PA" <?php echo ($so['project_state'] === 'PA') ? 'selected' : null; ?>>Pennsylvania</option>
                        <option value="RI" <?php echo ($so['project_state'] === 'RI') ? 'selected' : null; ?>>Rhode Island</option>
                        <option value="SC" <?php echo ($so['project_state'] === 'SC') ? 'selected' : null; ?>>South Carolina</option>
                        <option value="SD" <?php echo ($so['project_state'] === 'SD') ? 'selected' : null; ?>>South Dakota</option>
                        <option value="TN" <?php echo ($so['project_state'] === 'TN') ? 'selected' : null; ?>>Tennessee</option>
                        <option value="TX" <?php echo ($so['project_state'] === 'TX') ? 'selected' : null; ?>>Texas</option>
                        <option value="UT" <?php echo ($so['project_state'] === 'UT') ? 'selected' : null; ?>>Utah</option>
                        <option value="VT" <?php echo ($so['project_state'] === 'VT') ? 'selected' : null; ?>>Vermont</option>
                        <option value="VA" <?php echo ($so['project_state'] === 'VA') ? 'selected' : null; ?>>Virginia</option>
                        <option value="WA" <?php echo ($so['project_state'] === 'WA') ? 'selected' : null; ?>>Washington</option>
                        <option value="WV" <?php echo ($so['project_state'] === 'WV') ? 'selected' : null; ?>>West Virginia</option>
                        <option value="WI" <?php echo ($so['project_state'] === 'WI') ? 'selected' : null; ?>>Wisconsin</option>
                        <option value="WY" <?php echo ($so['project_state'] === 'WY') ? 'selected' : null; ?>>Wyoming</option>
                      </select></td>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['project_zip']; ?>" name="project_zip" class="form-control" placeholder="Project Zip" id="project_zip"></td>
                  </tr>
                </table>
              </td>
              <td width="20%"><input type="text" value="<?php echo $so['project_landline']; ?>" name="project_landline" class="form-control" placeholder="Project Landline" id="project_landline"></td>
            </tr>
            <tr class="name1group">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="name1group">
              <td><input type="text" value="<?php echo $so['name_1']; ?>" name="name_1" class="form-control" placeholder="Name 1" id="name_1"></td>
              <td><input type="text" value="<?php echo $so['cell_1']; ?>" name="cell_1" class="form-control" placeholder="Cell Phone" id="cell_1"></td>
              <td><input type="text" value="<?php echo $so['business_1']; ?>" name="business_1" class="form-control" placeholder="Secondary Phone" id="business_1"></td>
            </tr>
            <tr class="name1group">
              <td><input type="text" value="<?php echo $so['email_1']; ?>" name="email_1" class="form-control" placeholder="Email Address" id="email_1"></td>
              <td colspan="2">&nbsp;</td>
            </tr>

            <script>checkEmptyFields('.name1group');</script>

            <tr class="name2group">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="name2group">
              <td><input type="text" value="<?php echo $so['name_2']; ?>" name="name_2" class="form-control" placeholder="Name 2" id="name_2"></td>
              <td><input type="text" value="<?php echo $so['cell_2']; ?>" name="cell_2" class="form-control" placeholder="Cell Phone" id="cell_2"></td>
              <td><input type="text" value="<?php echo $so['business_2']; ?>" name="business_2" class="form-control" placeholder="Secondary Phone" id="business_2"></td>
            </tr>
            <tr class="name2group">
              <td><input type="text" value="<?php echo $so['email_2']; ?>" name="email_2" class="form-control" placeholder="Email Address" id="email_2"></td>
              <td colspan="2">&nbsp;</td>
            </tr>

            <script>checkEmptyFields('.name2group');</script>

            <tr class="s_addr_empty">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="s_addr_empty">
              <td colspan="3">
                <?php
                if (!empty($so['secondary_addr']) || !empty($so['secondary_city']) || !empty($so['secondary_zip']) || !empty($so['secondary_landline'])) {
                  $secondary_checked = ' checked';
                  echo "<script>$('.secondary_addr_disp').show();</script>";
                } else
                  $secondary_checked = null;
                echo "<script>$('.s_addr_empty').hide();</script>";
                ?>

                <div class="checkbox"><input id="secondary_addr_chk" type="checkbox" <?php echo $secondary_checked; ?>><label for="secondary_addr_chk"> Customer Secondary Address</label></div>
              </td>
            </tr>
            <tr style="display:none;" class="secondary_addr_disp">
              <td colspan="2"><input type="text" value="<?php echo $so['secondary_addr']; ?>" name="secondary_addr" class="form-control" placeholder="Secondary Address" id="secondary_addr"></td>
              <td><input type="text" value="<?php echo $so['secondary_landline']; ?>" name="secondary_landline" class="form-control" placeholder="Secondary Landline" id="secondary_landline"></td>
            </tr>
            <tr style="display:none;" class="secondary_addr_disp">
              <td colspan="2">
                <table style="width: 100%;">
                  <tr>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['secondary_city']; ?>" name="secondary_city" class="form-control" placeholder="Secondary City" id="secondary_city"></td>
                    <td style="width: 33.3%;"><select class="form-control" id="secondary_state" name="secondary_state">
                        <option value="AL" <?php echo ($so['secondary_state'] === 'AL') ? 'selected' : null; ?>>Alabama</option>
                        <option value="AK" <?php echo ($so['secondary_state'] === 'AK') ? 'selected' : null; ?>>Alaska</option>
                        <option value="AR" <?php echo ($so['secondary_state'] === 'AR') ? 'selected' : null; ?>>Arkansas</option>
                        <option value="CA" <?php echo ($so['secondary_state'] === 'CA') ? 'selected' : null; ?>>California</option>
                        <option value="CO" <?php echo ($so['secondary_state'] === 'CO') ? 'selected' : null; ?>>Colorado</option>
                        <option value="CT" <?php echo ($so['secondary_state'] === 'CT') ? 'selected' : null; ?>>Connecticut</option>
                        <option value="DE" <?php echo ($so['secondary_state'] === 'DE') ? 'selected' : null; ?>>Delaware</option>
                        <option value="FL" <?php echo ($so['secondary_state'] === 'FL') ? 'selected' : null; ?>>Florida</option>
                        <option value="GA" <?php echo ($so['secondary_state'] === 'GA') ? 'selected' : null; ?>>Georgia</option>
                        <option value="HI" <?php echo ($so['secondary_state'] === 'HI') ? 'selected' : null; ?>>Hawaii</option>
                        <option value="ID" <?php echo ($so['secondary_state'] === 'ID') ? 'selected' : null; ?>>Idaho</option>
                        <option value="IL" <?php echo ($so['secondary_state'] === 'IL') ? 'selected' : null; ?>>Illinois</option>
                        <option value="IN" <?php echo ($so['secondary_state'] === 'IN') ? 'selected' : null; ?>>Indiana</option>
                        <option value="IA" <?php echo ($so['secondary_state'] === 'IA') ? 'selected' : null; ?>>Iowa</option>
                        <option value="KS" <?php echo ($so['secondary_state'] === 'KS') ? 'selected' : null; ?>>Kansas</option>
                        <option value="KY" <?php echo ($so['secondary_state'] === 'KY') ? 'selected' : null; ?>>Kentucky</option>
                        <option value="LA" <?php echo ($so['secondary_state'] === 'LA') ? 'selected' : null; ?>>Louisiana</option>
                        <option value="ME" <?php echo ($so['secondary_state'] === 'ME') ? 'selected' : null; ?>>Maine</option>
                        <option value="MD" <?php echo ($so['secondary_state'] === 'MD') ? 'selected' : null; ?>>Maryland</option>
                        <option value="MA" <?php echo ($so['secondary_state'] === 'MA') ? 'selected' : null; ?>>Massachusetts</option>
                        <option value="MI" <?php echo ($so['secondary_state'] === 'MI') ? 'selected' : null; ?>>Michigan</option>
                        <option value="MN" <?php echo ($so['secondary_state'] === 'MN') ? 'selected' : null; ?>>Minnesota</option>
                        <option value="MS" <?php echo ($so['secondary_state'] === 'MS') ? 'selected' : null; ?>>Mississippi</option>
                        <option value="MO" <?php echo ($so['secondary_state'] === 'MO') ? 'selected' : null; ?>>Missouri</option>
                        <option value="MT" <?php echo ($so['secondary_state'] === 'MT') ? 'selected' : null; ?>>Montana</option>
                        <option value="NE" <?php echo ($so['secondary_state'] === 'NE') ? 'selected' : null; ?>>Nebraska</option>
                        <option value="NV" <?php echo ($so['secondary_state'] === 'NV') ? 'selected' : null; ?>>Nevada</option>
                        <option value="NH" <?php echo ($so['secondary_state'] === 'NH') ? 'selected' : null; ?>>New Hampshire</option>
                        <option value="NJ" <?php echo ($so['secondary_state'] === 'NJ') ? 'selected' : null; ?>>New Jersey</option>
                        <option value="NM" <?php echo ($so['secondary_state'] === 'NM') ? 'selected' : null; ?>>New Mexico</option>
                        <option value="NY" <?php echo ($so['secondary_state'] === 'NY') ? 'selected' : null; ?>>New York</option>
                        <option value="NC" <?php echo ($so['secondary_state'] === 'NC') ? 'selected' : null; ?>>North Carolina</option>
                        <option value="ND" <?php echo ($so['secondary_state'] === 'ND') ? 'selected' : null; ?>>North Dakota</option>
                        <option value="OH" <?php echo ($so['secondary_state'] === 'OH') ? 'selected' : null; ?>>Ohio</option>
                        <option value="OK" <?php echo ($so['secondary_state'] === 'OK') ? 'selected' : null; ?>>Oklahoma</option>
                        <option value="OR" <?php echo ($so['secondary_state'] === 'OR') ? 'selected' : null; ?>>Oregon</option>
                        <option value="PA" <?php echo ($so['secondary_state'] === 'PA') ? 'selected' : null; ?>>Pennsylvania</option>
                        <option value="RI" <?php echo ($so['secondary_state'] === 'RI') ? 'selected' : null; ?>>Rhode Island</option>
                        <option value="SC" <?php echo ($so['secondary_state'] === 'SC') ? 'selected' : null; ?>>South Carolina</option>
                        <option value="SD" <?php echo ($so['secondary_state'] === 'SD') ? 'selected' : null; ?>>South Dakota</option>
                        <option value="TN" <?php echo ($so['secondary_state'] === 'TN') ? 'selected' : null; ?>>Tennessee</option>
                        <option value="TX" <?php echo ($so['secondary_state'] === 'TX') ? 'selected' : null; ?>>Texas</option>
                        <option value="UT" <?php echo ($so['secondary_state'] === 'UT') ? 'selected' : null; ?>>Utah</option>
                        <option value="VT" <?php echo ($so['secondary_state'] === 'VT') ? 'selected' : null; ?>>Vermont</option>
                        <option value="VA" <?php echo ($so['secondary_state'] === 'VA') ? 'selected' : null; ?>>Virginia</option>
                        <option value="WA" <?php echo ($so['secondary_state'] === 'WA') ? 'selected' : null; ?>>Washington</option>
                        <option value="WV" <?php echo ($so['secondary_state'] === 'WV') ? 'selected' : null; ?>>West Virginia</option>
                        <option value="WI" <?php echo ($so['secondary_state'] === 'WI') ? 'selected' : null; ?>>Wisconsin</option>
                        <option value="WY" <?php echo ($so['secondary_state'] === 'WY') ? 'selected' : null; ?>>Wyoming</option>
                      </select></td>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['secondary_zip']; ?>" name="secondary_zip" class="form-control" placeholder="Secondary Zip" id="secondary_zip"></td>
                  </tr>
                </table>
              </td>
              <td>&nbsp;</td>
            </tr>

            <tr class="con_empty">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="con_empty">
              <td colspan="3">
                <?php
                if (!empty($so['contractor_name']) || !empty($so['contractor_business']) || !empty($so['contractor_cell']) || !empty($so['contractor_email']) || !empty($so['contractor_zip']) ||
                  !empty($so['contractor_city']) || !empty($so['contractor_addr'])) {

                  $contractor_checked = ' checked';
                  echo "<script>$('.contractor_disp').show();</script>";
                } else
                  $contractor_checked = null;
                echo "<script>$('.con_empty').hide();</script>";
                ?>

                <div class="checkbox"><input id="contractor_chk" type="checkbox" <?php echo $contractor_checked; ?>><label for="contractor_chk"> Contractor</label></div>
              </td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><input type="text" value="<?php echo $so['contractor_name']; ?>" name="contractor_name" class="form-control" placeholder="Contractor Name" id="contractor_name"></td>
              <td><input type="text" value="<?php echo $so['contractor_business']; ?>" name="contractor_business" class="form-control" placeholder="Contractor Business Number" id="contractor_business"></td>
              <td><input type="text" value="<?php echo $so['contractor_cell']; ?>" name="contractor_cell" class="form-control" placeholder="Contractor Cell Number" id="contractor_cell"></td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><input type="text" value="<?php echo $so['contractor_addr']; ?>" name="contractor_addr" class="form-control" placeholder="Contractor Address" id="contractor_addr"></td>
              <td colspan="2">
                <table style="width: 100%;">
                  <tr>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['contractor_city']; ?>" name="contractor_city" class="form-control" placeholder="Contractor City" id="contractor_city"></td>
                    <td style="width: 33.3%;"><select class="form-control" id="contractor_state" name="contractor_state">
                        <option value="AL" <?php echo ($so['contractor_state'] === 'AL') ? 'selected' : null; ?>>Alabama</option>
                        <option value="AK" <?php echo ($so['contractor_state'] === 'AK') ? 'selected' : null; ?>>Alaska</option>
                        <option value="AR" <?php echo ($so['contractor_state'] === 'AR') ? 'selected' : null; ?>>Arkansas</option>
                        <option value="CA" <?php echo ($so['contractor_state'] === 'CA') ? 'selected' : null; ?>>California</option>
                        <option value="CO" <?php echo ($so['contractor_state'] === 'CO') ? 'selected' : null; ?>>Colorado</option>
                        <option value="CT" <?php echo ($so['contractor_state'] === 'CT') ? 'selected' : null; ?>>Connecticut</option>
                        <option value="DE" <?php echo ($so['contractor_state'] === 'DE') ? 'selected' : null; ?>>Delaware</option>
                        <option value="FL" <?php echo ($so['contractor_state'] === 'FL') ? 'selected' : null; ?>>Florida</option>
                        <option value="GA" <?php echo ($so['contractor_state'] === 'GA') ? 'selected' : null; ?>>Georgia</option>
                        <option value="HI" <?php echo ($so['contractor_state'] === 'HI') ? 'selected' : null; ?>>Hawaii</option>
                        <option value="ID" <?php echo ($so['contractor_state'] === 'ID') ? 'selected' : null; ?>>Idaho</option>
                        <option value="IL" <?php echo ($so['contractor_state'] === 'IL') ? 'selected' : null; ?>>Illinois</option>
                        <option value="IN" <?php echo ($so['contractor_state'] === 'IN') ? 'selected' : null; ?>>Indiana</option>
                        <option value="IA" <?php echo ($so['contractor_state'] === 'IA') ? 'selected' : null; ?>>Iowa</option>
                        <option value="KS" <?php echo ($so['contractor_state'] === 'KS') ? 'selected' : null; ?>>Kansas</option>
                        <option value="KY" <?php echo ($so['contractor_state'] === 'KY') ? 'selected' : null; ?>>Kentucky</option>
                        <option value="LA" <?php echo ($so['contractor_state'] === 'LA') ? 'selected' : null; ?>>Louisiana</option>
                        <option value="ME" <?php echo ($so['contractor_state'] === 'ME') ? 'selected' : null; ?>>Maine</option>
                        <option value="MD" <?php echo ($so['contractor_state'] === 'MD') ? 'selected' : null; ?>>Maryland</option>
                        <option value="MA" <?php echo ($so['contractor_state'] === 'MA') ? 'selected' : null; ?>>Massachusetts</option>
                        <option value="MI" <?php echo ($so['contractor_state'] === 'MI') ? 'selected' : null; ?>>Michigan</option>
                        <option value="MN" <?php echo ($so['contractor_state'] === 'MN') ? 'selected' : null; ?>>Minnesota</option>
                        <option value="MS" <?php echo ($so['contractor_state'] === 'MS') ? 'selected' : null; ?>>Mississippi</option>
                        <option value="MO" <?php echo ($so['contractor_state'] === 'MO') ? 'selected' : null; ?>>Missouri</option>
                        <option value="MT" <?php echo ($so['contractor_state'] === 'MT') ? 'selected' : null; ?>>Montana</option>
                        <option value="NE" <?php echo ($so['contractor_state'] === 'NE') ? 'selected' : null; ?>>Nebraska</option>
                        <option value="NV" <?php echo ($so['contractor_state'] === 'NV') ? 'selected' : null; ?>>Nevada</option>
                        <option value="NH" <?php echo ($so['contractor_state'] === 'NH') ? 'selected' : null; ?>>New Hampshire</option>
                        <option value="NJ" <?php echo ($so['contractor_state'] === 'NJ') ? 'selected' : null; ?>>New Jersey</option>
                        <option value="NM" <?php echo ($so['contractor_state'] === 'NM') ? 'selected' : null; ?>>New Mexico</option>
                        <option value="NY" <?php echo ($so['contractor_state'] === 'NY') ? 'selected' : null; ?>>New York</option>
                        <option value="NC" <?php echo ($so['contractor_state'] === 'NC') ? 'selected' : null; ?>>North Carolina</option>
                        <option value="ND" <?php echo ($so['contractor_state'] === 'ND') ? 'selected' : null; ?>>North Dakota</option>
                        <option value="OH" <?php echo ($so['contractor_state'] === 'OH') ? 'selected' : null; ?>>Ohio</option>
                        <option value="OK" <?php echo ($so['contractor_state'] === 'OK') ? 'selected' : null; ?>>Oklahoma</option>
                        <option value="OR" <?php echo ($so['contractor_state'] === 'OR') ? 'selected' : null; ?>>Oregon</option>
                        <option value="PA" <?php echo ($so['contractor_state'] === 'PA') ? 'selected' : null; ?>>Pennsylvania</option>
                        <option value="RI" <?php echo ($so['contractor_state'] === 'RI') ? 'selected' : null; ?>>Rhode Island</option>
                        <option value="SC" <?php echo ($so['contractor_state'] === 'SC') ? 'selected' : null; ?>>South Carolina</option>
                        <option value="SD" <?php echo ($so['contractor_state'] === 'SD') ? 'selected' : null; ?>>South Dakota</option>
                        <option value="TN" <?php echo ($so['contractor_state'] === 'TN') ? 'selected' : null; ?>>Tennessee</option>
                        <option value="TX" <?php echo ($so['contractor_state'] === 'TX') ? 'selected' : null; ?>>Texas</option>
                        <option value="UT" <?php echo ($so['contractor_state'] === 'UT') ? 'selected' : null; ?>>Utah</option>
                        <option value="VT" <?php echo ($so['contractor_state'] === 'VT') ? 'selected' : null; ?>>Vermont</option>
                        <option value="VA" <?php echo ($so['contractor_state'] === 'VA') ? 'selected' : null; ?>>Virginia</option>
                        <option value="WA" <?php echo ($so['contractor_state'] === 'WA') ? 'selected' : null; ?>>Washington</option>
                        <option value="WV" <?php echo ($so['contractor_state'] === 'WV') ? 'selected' : null; ?>>West Virginia</option>
                        <option value="WI" <?php echo ($so['contractor_state'] === 'WI') ? 'selected' : null; ?>>Wisconsin</option>
                        <option value="WY" <?php echo ($so['contractor_state'] === 'WY') ? 'selected' : null; ?>>Wyoming</option>
                      </select></td>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['contractor_zip']; ?>" name="contractor_zip" class="form-control" placeholder="Contractor Zip" id="contractor_zip"></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><input type="text" value="<?php echo $so['contractor_email']; ?>" name="contractor_email" class="form-control" placeholder="Contractor Email Address" id="contractor_email"></td>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr style="display:none;" class="contractor_disp">
              <td><input type="text" value="<?php echo $so['project_mgr']; ?>" name="project_mgr" class="form-control" placeholder="Project Manager" id="project_mgr"></td>
              <td><input type="text" value="<?php echo $so['project_mgr_cell']; ?>" name="project_mgr_cell" class="form-control" placeholder="Project Manager Cell" id="project_mgr_cell"></td>
              <td><input type="text" value="<?php echo $so['project_mgr_email']; ?>" name="project_mgr_email" class="form-control" placeholder="Project Manager Email" id="project_mgr_email"></td>
            </tr>
            <tr class="billing_empty">
              <td colspan="3">
                <div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div>
              </td>
            </tr>
            <tr class="billing_empty">
              <td>
                <?php
                if (!empty($so['bill_to']) || !empty($so['billing_contact']) || !empty($so['billing_landline']) || !empty($so['billing_cell']) || !empty($so['billing_addr']) ||
                  !empty($so['billing_city']) || !empty($so['billing_zip']) || !empty($so['billing_account']) || !empty($so['billing_routing'])
                  || !empty($so['billing_cc_num']) || !empty($so['billing_cc_exp']) || !empty($so['billing_cc_ccv'])) {

                  $billing_checked = ' checked';
                  echo "<script>$('.billing_info_disp').show();</script>";
                } else {
                  $billing_checked = null;
                  echo "<script>$('.billing_empty').hide();</script>";
                }

                $b_homeowner = null;
                $b_contractor = null;

                if ($so['bill_to'] === 'homeowner')
                  $b_homeowner = ' checked';
                elseif ($so['bill_to'] === 'contractor')
                  $b_contractor = ' checked';
                ?>

                <div class="checkbox"><input id="billing_addr_chk" type="checkbox" <?php echo $billing_checked; ?>><label for="billing_addr_chk"> Billing Information</label></div>
              </td>
              <td style="display:none;" class="billing_info_disp"><label class="c-input c-radio"><input id="bill_homeowner" <?php echo $b_homeowner; ?> name="bill_to" type="radio" value="homeowner"><span class="c-indicator"></span>Bill
                  Homeowner</label></td>
              <td style="display:none;" class="billing_info_disp"><label class="c-input c-radio"><input id="bill_contractor" <?php echo $b_contractor; ?> name="bill_to" type="radio" value="contractor"><span
                    class="c-indicator"></span>Bill
                  Contractor</label></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><input type="text" value="<?php echo $so['billing_contact']; ?>" name="billing_contact" class="form-control" placeholder="Billing Contact" id="billing_contact"></td>
              <td><input type="text" value="<?php echo $so['billing_landline']; ?>" name="billing_landline" class="form-control" placeholder="Billing Landline" id="billing_landline"></td>
              <td><input type="text" value="<?php echo $so['billing_cell']; ?>" name="billing_cell" class="form-control" placeholder="Billing Cell" id="billing_cell"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><input type="text" value="<?php echo $so['billing_addr']; ?>" name="billing_addr" class="form-control" placeholder="Billing Address" id="billing_addr"></td>
              <td colspan="2">
                <table style="width: 100%;">
                  <tr>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['billing_city']; ?>" name="billing_city" class="form-control" placeholder="Billing City" id="billing_city"></td>
                    <td style="width: 33.3%;"><select class="form-control" id="billing_state" name="billing_state">
                        <option value="AL" <?php echo ($so['billing_state'] === 'AL') ? 'selected' : null; ?>>Alabama</option>
                        <option value="AK" <?php echo ($so['billing_state'] === 'AK') ? 'selected' : null; ?>>Alaska</option>
                        <option value="AR" <?php echo ($so['billing_state'] === 'AR') ? 'selected' : null; ?>>Arkansas</option>
                        <option value="CA" <?php echo ($so['billing_state'] === 'CA') ? 'selected' : null; ?>>California</option>
                        <option value="CO" <?php echo ($so['billing_state'] === 'CO') ? 'selected' : null; ?>>Colorado</option>
                        <option value="CT" <?php echo ($so['billing_state'] === 'CT') ? 'selected' : null; ?>>Connecticut</option>
                        <option value="DE" <?php echo ($so['billing_state'] === 'DE') ? 'selected' : null; ?>>Delaware</option>
                        <option value="FL" <?php echo ($so['billing_state'] === 'FL') ? 'selected' : null; ?>>Florida</option>
                        <option value="GA" <?php echo ($so['billing_state'] === 'GA') ? 'selected' : null; ?>>Georgia</option>
                        <option value="HI" <?php echo ($so['billing_state'] === 'HI') ? 'selected' : null; ?>>Hawaii</option>
                        <option value="ID" <?php echo ($so['billing_state'] === 'ID') ? 'selected' : null; ?>>Idaho</option>
                        <option value="IL" <?php echo ($so['billing_state'] === 'IL') ? 'selected' : null; ?>>Illinois</option>
                        <option value="IN" <?php echo ($so['billing_state'] === 'IN') ? 'selected' : null; ?>>Indiana</option>
                        <option value="IA" <?php echo ($so['billing_state'] === 'IA') ? 'selected' : null; ?>>Iowa</option>
                        <option value="KS" <?php echo ($so['billing_state'] === 'KS') ? 'selected' : null; ?>>Kansas</option>
                        <option value="KY" <?php echo ($so['billing_state'] === 'KY') ? 'selected' : null; ?>>Kentucky</option>
                        <option value="LA" <?php echo ($so['billing_state'] === 'LA') ? 'selected' : null; ?>>Louisiana</option>
                        <option value="ME" <?php echo ($so['billing_state'] === 'ME') ? 'selected' : null; ?>>Maine</option>
                        <option value="MD" <?php echo ($so['billing_state'] === 'MD') ? 'selected' : null; ?>>Maryland</option>
                        <option value="MA" <?php echo ($so['billing_state'] === 'MA') ? 'selected' : null; ?>>Massachusetts</option>
                        <option value="MI" <?php echo ($so['billing_state'] === 'MI') ? 'selected' : null; ?>>Michigan</option>
                        <option value="MN" <?php echo ($so['billing_state'] === 'MN') ? 'selected' : null; ?>>Minnesota</option>
                        <option value="MS" <?php echo ($so['billing_state'] === 'MS') ? 'selected' : null; ?>>Mississippi</option>
                        <option value="MO" <?php echo ($so['billing_state'] === 'MO') ? 'selected' : null; ?>>Missouri</option>
                        <option value="MT" <?php echo ($so['billing_state'] === 'MT') ? 'selected' : null; ?>>Montana</option>
                        <option value="NE" <?php echo ($so['billing_state'] === 'NE') ? 'selected' : null; ?>>Nebraska</option>
                        <option value="NV" <?php echo ($so['billing_state'] === 'NV') ? 'selected' : null; ?>>Nevada</option>
                        <option value="NH" <?php echo ($so['billing_state'] === 'NH') ? 'selected' : null; ?>>New Hampshire</option>
                        <option value="NJ" <?php echo ($so['billing_state'] === 'NJ') ? 'selected' : null; ?>>New Jersey</option>
                        <option value="NM" <?php echo ($so['billing_state'] === 'NM') ? 'selected' : null; ?>>New Mexico</option>
                        <option value="NY" <?php echo ($so['billing_state'] === 'NY') ? 'selected' : null; ?>>New York</option>
                        <option value="NC" <?php echo ($so['billing_state'] === 'NC') ? 'selected' : null; ?>>North Carolina</option>
                        <option value="ND" <?php echo ($so['billing_state'] === 'ND') ? 'selected' : null; ?>>North Dakota</option>
                        <option value="OH" <?php echo ($so['billing_state'] === 'OH') ? 'selected' : null; ?>>Ohio</option>
                        <option value="OK" <?php echo ($so['billing_state'] === 'OK') ? 'selected' : null; ?>>Oklahoma</option>
                        <option value="OR" <?php echo ($so['billing_state'] === 'OR') ? 'selected' : null; ?>>Oregon</option>
                        <option value="PA" <?php echo ($so['billing_state'] === 'PA') ? 'selected' : null; ?>>Pennsylvania</option>
                        <option value="RI" <?php echo ($so['billing_state'] === 'RI') ? 'selected' : null; ?>>Rhode Island</option>
                        <option value="SC" <?php echo ($so['billing_state'] === 'SC') ? 'selected' : null; ?>>South Carolina</option>
                        <option value="SD" <?php echo ($so['billing_state'] === 'SD') ? 'selected' : null; ?>>South Dakota</option>
                        <option value="TN" <?php echo ($so['billing_state'] === 'TN') ? 'selected' : null; ?>>Tennessee</option>
                        <option value="TX" <?php echo ($so['billing_state'] === 'TX') ? 'selected' : null; ?>>Texas</option>
                        <option value="UT" <?php echo ($so['billing_state'] === 'UT') ? 'selected' : null; ?>>Utah</option>
                        <option value="VT" <?php echo ($so['billing_state'] === 'VT') ? 'selected' : null; ?>>Vermont</option>
                        <option value="VA" <?php echo ($so['billing_state'] === 'VA') ? 'selected' : null; ?>>Virginia</option>
                        <option value="WA" <?php echo ($so['billing_state'] === 'WA') ? 'selected' : null; ?>>Washington</option>
                        <option value="WV" <?php echo ($so['billing_state'] === 'WV') ? 'selected' : null; ?>>West Virginia</option>
                        <option value="WI" <?php echo ($so['billing_state'] === 'WI') ? 'selected' : null; ?>>Wisconsin</option>
                        <option value="WY" <?php echo ($so['billing_state'] === 'WY') ? 'selected' : null; ?>>Wyoming</option>
                      </select></td>
                    <td style="width: 33.3%;"><input type="text" value="<?php echo $so['billing_zip']; ?>" name="billing_zip" class="form-control" placeholder="Billing Zip" id="billing_zip"></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td style="height:8px"></td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td colspan="3">
                <input type="text" value="<?php echo $so['billing_account']; ?>" name="billing_account" autocomplete="off" class="form-control pull-left" placeholder="ACH Account #" id="billing_account" style="width: 50%;">
                <input type="text" value="<?php echo $so['billing_routing']; ?>" name="billing_routing" autocomplete="off" class="form-control pull-right" placeholder="ACH Routing #" id="billing_routing" style="width: 50%;">
              </td>
            </tr>
            <tr style="display:none;" class="billing_info_disp">
              <td><input type="text" value="<?php echo $so['billing_cc_num']; ?>" name="billing_cc_num" class="form-control" placeholder="Credit Card #" id="billing_cc_num"></td>
              <td><input type="text" value="<?php echo $so['billing_cc_exp']; ?>" name="billing_cc_exp" class="form-control" placeholder="Exp. Date" id="billing_cc_exp"></td>
              <td><input type="text" value="<?php echo $so['billing_cc_ccv']; ?>" name="billing_cc_ccv" class="form-control" placeholder="CCV Code" id="billing_cc_ccv"></td>
            </tr>
            <tr>
              <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
            </tr>
            <tr>
              <td colspan="3"><h5>Associations</h5></td>
            </tr>
            <?php
            $contact_dropdown = null;

            $contact_qry = $dbconn->query('SELECT c.id, c.first_name, c.last_name, c.company_name, c2.description FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id ORDER BY c2.description, c.first_name, c.last_name ASC');

            if($contact_qry->num_rows > 0) {
              $contact_dropdown = "<select class='form-control pull-left add_contact_id ignoreSaveAlert' name='add_contact' style='width:50%;'><option value=''>Select</option>";

              $last_group = null;

              while($contact = $contact_qry->fetch_assoc()) {
                if($contact['description'] !== $last_group) {
                  $contact_dropdown .= "</optgroup><optgroup label='{$contact['description']}'>";
                  $last_group = $contact['description'];
                }

                $name = !empty($contact['first_name']) ? "{$contact['first_name']} {$contact['last_name']}" : $contact['company_name'];

                $contact_dropdown .= "<option value='{$contact['id']}'>$name</option>";
              }

              $contact_dropdown .= '</optgroup></select>';
            }

            echo "<tr><td colspan='3'><div class='form-group'><label for='add_contact' class='pull-left' style='line-height:28px;padding-right:10px;'>Add Association</label> $contact_dropdown <button type='button' class='btn waves-effect waves-light btn-primary assign_contact_so' style='margin:2px 0 0 10px;'> <i class='zmdi zmdi-plus-circle'></i> </button></div></td></tr>";

            // displaying existing contact relationships
            $so_contacts_qry = $dbconn->query("SELECT c.*, c2.description FROM sales_order_contacts soc LEFT JOIN contact c ON soc.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE so_id = '{$so['id']}' ORDER BY c.first_name, c.last_name ASC");

            if($so_contacts_qry->num_rows > 0) {
              while($so_contacts = $so_contacts_qry->fetch_assoc()) {
                $name = !empty($so_contacts['first_name']) ? "{$so_contacts['first_name']} {$so_contacts['last_name']}" : $so_contacts['company_name'];

                echo "<tr><td colspan='3'><button type='button' class='btn waves-effect waves-light btn-danger remove_assigned_contact_so' style='margin:2px 0;' data-id='{$so_contacts['id']}'> <i class='zmdi zmdi-minus-circle'></i> </button> <a href='#' class='get_customer_info' data-view-id='{$so_contacts['id']}''>$name ({$so_contacts['description']})</a></td></tr>";
              }
            } else {
              echo "<tr><td colspan='3'><strong>No Associations</strong></td></tr>";
            }
            ?>
          <?php } ?>
        </table>
      </div>

      <script>nameOfUser = '<?php echo $_SESSION['userInfo']['name']; ?>';</script>

      <div class="col-md-2 no-print">
        <div class="row">
          <div class="col-md-12">
            <textarea class="form-control" name="inquiry" id="inquiry" placeholder="New Inquiry/Note" style="width:100%;height:215px;"></textarea>
            <input type="text" name="inquiry_followup_date" id="inquiry_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
            <label for="inquiry_requested_of" style="float:left;padding:4px;"> requested of </label>
            <select name="inquiry_requested_of" id="inquiry_requested_of" class="form-control" style="width:45%;float:left;">
              <option value="null" selected disabled></option>
              <?php
              $user_qry = $dbconn->query('SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC');

              while ($user = $user_qry->fetch_assoc()) {
                echo "<option value='{$user['id']}'>{$user['name']}</option>";
              }
              ?>
            </select>
          </div>
        </div>

        <div class="row" style="margin-top:5px;">
          <div class="col-md-12">
            <div class="so_note_box">
              <table class="table table-custom-nb table-v-top" width="100%">
                <tr>
                  <td colspan="2" class="bracket-border-top" style="padding: 2px 7px;"><h5 class="pull-left">SO Notes</h5></td>
                </tr>
                <tr style="height:5px;"><td colspan="2"></td></tr>
                <?php
                if((bool)$_SESSION['userInfo']['dealer']) {
                  $dealer = strtolower($_SESSION['userInfo']['dealer_code']);
                  $where = "AND user.username LIKE '$dealer%'";
                } else {
                  $where = null;
                }

                $so_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'so_inquiry' OR note_type = 'so_note_log') AND notes.type_id = '{$so['id']}' $where ORDER BY notes.timestamp DESC;");

                while ($so_inquiry = $so_inquiry_qry->fetch_assoc()) {
                  $inquiry_replies = null;

                  $time = date(DATE_TIME_ABBRV, $so_inquiry['NTimestamp']);

                  if (!empty($so_inquiry['followup_time'])) {
                    $followup_usr_qry = $dbconn->query("SELECT name FROM user WHERE id = {$so_inquiry['user_to']}");
                    $followup_usr = $followup_usr_qry->fetch_assoc();

                    $followup_time = date(DATE_TIME_ABBRV, $so_inquiry['followup_time']);

                    $followup = " (Followup by {$followup_usr['name']} on $followup_time)";
                  } else {
                    $followup = null;
                  }

                  $inquiry_reply_qry = $dbconn->query("SELECT notes.*, user.name FROM notes LEFT JOIN user ON notes.user = user.id WHERE note_type = 'inquiry_reply' AND type_id = '{$so_inquiry['nID']}' ORDER BY timestamp DESC");

                  if ($inquiry_reply_qry->num_rows > 0) {
                    while ($inquiry_reply = $inquiry_reply_qry->fetch_assoc()) {
                      $ireply_time = date(DATE_TIME_ABBRV, $inquiry_reply['timestamp']);

                      $inquiry_replies .= "<tr><td colspan='2' style='padding-left:30px;'><i class='fa fa-level-up fa-rotate-90' style='margin-right:5px;'></i> {$inquiry_reply['note']} -- <small><em>{$inquiry_reply['name']} on $ireply_time</em></small></td></tr>";
                    }
                  } else {
                    $inquiry_replies = null;
                  }

                  $notes = str_replace('  ', '&nbsp;&nbsp;', $so_inquiry['note']);

                  echo '<tr>';
                  echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$so_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                  echo "  <td>$notes -- <small><em>{$so_inquiry['name']} on $time $followup</em></small></td>";
                  echo '</tr>';

                  echo "<tr id='inquiry_reply_line_{$so_inquiry['nID']}' style='display:none;'>";
                  echo "  <td colspan='2'>
                                    <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$so_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                                    <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='{$so_inquiry['nID']}'>Reply</button>
                                </td>";
                  echo '</tr>';

                  echo $inquiry_replies;

                  echo "<tr style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                }
                ?>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <button type="button" class="btn btn-primary waves-effect waves-light w-xs save_so" data-sonum="<?php echo $so['so_num']; ?>">Save</button>
      </div>
    </div>
  </form>
</div>
