<?php
require_once '../../includes/header_start.php';

//outputPHPErrs();

$find = sanitizeInput($_REQUEST['find']);
$exact = (bool)sanitizeInput($_REQUEST['exact']);
$rid = sanitizeInput($_REQUEST['rID']);

function determineColor($room, $bracket) {
  global $dbconn;

  if ($room['order_status'] === '+' || $room['order_status'] === '-') {
    return 'job-color-gray';
  } else {
    $op_qry = $dbconn->query("SELECT * FROM operations WHERE id = '{$room[$bracket . "_bracket"]}'");
    $op = $op_qry->fetch_assoc();

    if ($op['job_title'] === 'Bracket Completed' || $op['job_title'] === 'N/A') {
      return 'job-color-gray';
    } elseif ((bool)$room[$bracket . '_published']) {
      return 'job-color-green';
    }
  }
}

function getBracketInfo($bracket, $opID, $room) {
  global $dbconn;

  if(!empty($opID)) {
    $bracket_info = $dbconn->query("SELECT id, op_id, job_title, bracket FROM operations WHERE id = $opID")->fetch_assoc();

    if((bool)$room[$bracket . '_published']) {
      if((stristr($bracket_info['job_title'], 'Bracket Completed') === FALSE) && (stristr($bracket_info['job_title'], 'N/A') === FALSE)) {
        $opacity = null;
        $outline = "btn-primary";
      } else {
        $opacity = "style='color:rgba(80,80,80,.6);'";
        $outline = "btn-primary-outline";
      }

      return "<table class='table-custom-nb' $opacity><tr><td style='padding-left:2px;line-height:1em;'>{$bracket_info['job_title']}</td></tr></table>";
    }
  }
}

function displayBracketOpsMgmt($bracket, $room, $individual_bracket) {
  global $dbconn;

  $bracket_def = null;

  switch($bracket) {
    case 'Sales':
      $bracket_def = 'sales_bracket';
      break;

    case 'Sample':
      $bracket_def = 'sample_bracket';
      break;

    case 'Pre-Production':
      $bracket_def = 'preproduction_bracket';
      break;

    case 'Drawer & Doors':
      $bracket_def = 'doordrawer_bracket';
      break;

    case 'Main':
      $bracket_def = 'main_bracket';
      break;

    case 'Custom':
      $bracket_def = 'custom_bracket';
      break;

    case 'Shipping':
      $bracket_def = 'shipping_bracket';
      break;

    case 'Installation':
      $bracket_def = 'install_bracket';
      break;

    case 'Pick & Materials':
      $bracket_def = 'pick_materials_bracket';
      break;

    case 'Edgebanding':
      $bracket_def = 'edgebanding_bracket';
      break;

    default:
      $bracket_def = null;
  }

  $op_qry = $dbconn->query("SELECT * FROM operations WHERE bracket = '$bracket' AND always_visible = FALSE ORDER BY op_id ASC");

  $left_info = '';
  $right_info = '';

  while($op = $op_qry->fetch_assoc()) {
    $op_room_id = "op_{$op['id']}_room_{$room['id']}";

    if(in_array($op['id'], $individual_bracket)) {
      if($op['id'] === $room[$bracket_def]) {
        $selected = "checked='checked'";
      } else {
        $selected = '';
      }

      if((int)substr($op['op_id'], -2) !== 98) {
        $deactivate = "<span class=\"pull-right cursor-hand text-md-center deactivate_op\" data-opid=\"{$op['id']}\" data-roomid=\"{$room['id']}\" data-soid=\"{$room['so_parent']}\"> <i class=\"fa fa-arrow-circle-right\" style=\"width: 18px;\"></i> </button>";
      } else {
        $deactivate = null;
      }

      $left_info .= <<<HEREDOC
            <li class="active_ops_{$room['id']}" data-opnum="{$op['op_id']}" data-opid="{$op['id']}" data-roomid="{$room['id']}">
                <input type="radio" name="{$bracket_def}" id="$op_room_id" value="{$op['id']}" $selected>
                <label for="$op_room_id">{$op['op_id']}-{$op['job_title']}</label>
                $deactivate
            </li>
HEREDOC;
    } else {
      $right_info .= <<<HEREDOC
                <li class="inactive_ops_{$room['id']}" data-opnum="{$op['op_id']}" data-opid="{$op['id']}" data-roomid="{$room['id']}">
                    <span class="pull-left cursor-hand activate_op" style="height:18px;width:18px;" data-opid="{$op['id']}" data-roomid="{$room['id']}" data-soid="{$room['so_parent']}"> <i class="fa fa-arrow-circle-left pull-left" style="margin:5px;"></i></span>
                    {$op['op_id']}-{$op['job_title']}
                </li>
HEREDOC;
    }
  }
  ?>

  <div class="col-md-12">
    <div class="row">
      <div class="col-md-6 custom_ul" style="border-right: 2px solid #000;">
        <h3 class="text-md-center">Active</h3>
        <ul class="radio" class="activeops_<?php echo "{$room['id']}"; ?>" id="activeops_<?php echo "{$room['id']}_$bracket_def"; ?>" data-bracket="<?php echo $bracket_def; ?>">
          <?php echo $left_info; ?>
        </ul>
      </div>

      <div class="col-md-6 custom_ul">
        <h3 class="text-md-center">Inactive</h3>
        <ul style="padding: 0;" class="inactiveops_<?php echo "{$room['id']}"; ?>" id="inactiveops_<?php echo "{$room['id']}_$bracket_def"; ?>" data-bracket="<?php echo $bracket_def; ?>">
          <?php echo $right_info; ?>
        </ul>
      </div>
    </div>
  </div>
  <?php
}

if((bool)$_SESSION['userInfo']['dealer']) {
  $dealer_filter = "AND dealer_code LIKE '{$_SESSION['userInfo']['dealer_code']}%'";
} else {
  $dealer_filter = null;
}

// obtain the VIN database table and commit to memory for this query (MAJOR reduction in DB query count)
$vin_qry = $dbconn->query("SELECT * FROM vin_schema ORDER BY FIELD(`value`, 'Custom/Other', 'TBD', 'N/A', 'Completed', 'Job', 'Quote', 'Lost') DESC, segment, `key` ASC");

while($vin = $vin_qry->fetch_assoc()) {
  $vin_schema[$vin['segment']][$vin['key']] = $vin['value'];
}

if($exact) {
  $qry = $dbconn->query("SELECT * FROM sales_order WHERE id = $find $dealer_filter ORDER BY so_num DESC");

  $so_display_immediately = 'style="display:block;"';
} else {
  $qry = $dbconn->query("SELECT * FROM sales_order WHERE (so_num LIKE '%$find%' OR LOWER(dealer_code) LIKE LOWER('%$find%') 
    OR LOWER(project_name) LIKE LOWER('%$find%') OR LOWER(project_mgr) LIKE LOWER('%$find%') OR LOWER(name_1) LIKE LOWER('%$find%') 
    OR LOWER(name_2) LIKE LOWER('%$find%')) $dealer_filter ORDER BY so_num DESC");

  $so_display_immediately = 'style="display:none;"';
}

if($qry->num_rows > 0) {
  while($result = $qry->fetch_assoc()) {
    $soColor = 'job-color-green';

    $dealer_qry = $dbconn->query("SELECT d.*, c.first_name, c.last_name, c.company_name FROM dealers d LEFT JOIN contact c ON d.id = c.dealer_id WHERE d.dealer_id LIKE '%{$result['dealer_code']}%'");
    $dealer = $dealer_qry->fetch_assoc();

    //<editor-fold desc="SO List">
    /** BEGIN LISTING OF SO'S */
    if($bouncer->validate('view_so')) {
      echo "  <tr class='cursor-hand' id='show_room_{$result['so_num']}'>";

      $btn_add_room = $bouncer->validate('add_room') ? "<button class='btn btn-primary-outline waves-effect add_room_trigger' data-sonum='{$result['so_num']}' data-toggle='tooltip' data-placement='top' title='' data-original-title='Add additional room' style='font-size:10px;width:23px;height:22px;margin-top:1px;padding:0;'> +X</button></td>" : null;
      $btn_edit_room = $bouncer->validate('edit_so') ? "<button class='btn waves-effect btn-primary' id='edit_so_{$result['so_num']}'> <i class='zmdi zmdi-edit'></i> </button>" : null;

      if (!empty($_SESSION['userInfo'])) {
        echo "    <td class='nowrap' style='width:50px;'>$btn_edit_room $btn_add_room";
      }

      echo "    <td>{$result['so_num']}</td>";
      echo "    <td>{$result['dealer_code']}: {$dealer['first_name']} {$dealer['last_name']}</td>";
      echo "    <td>{$result['project_name']}</td>";
      echo "    <td>{$dealer['contact']}</td>";
      echo '  </tr>';

      //<editor-fold desc="Edit SO">
      if ($bouncer->validate('edit_so')) {

        /** BEGIN EDIT SO DISPLAY */
        echo "<tr id='tr_edit_so_{$result['so_num']}' $so_display_immediately>";
        echo "  <td colspan='8'><div id='div_edit_so_{$result['so_num']}' $so_display_immediately>";
        ?>

        <div class="col-md-12">
          <form id="form_so_<?php echo $result['so_num']; ?>">
            <div class="row">
              <div class="col-md-3">
                <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
                  <?php if((bool)$_SESSION['userInfo']['dealer']) {
                    $dealer_code = ucwords($_SESSION['userInfo']['username']);

                    echo "<input type='hidden' name='dealer_code' id='dealer_code' value='$dealer_code'>";
                    ?>
                    <tr>
                      <td colspan="3">
                        <input type="text" name="project_name" class="form-control pull-left" placeholder="Project Name" id="project_name" value="<?php echo $result['project_name']; ?>" style="width:50%;" />
                        <input type="text" name="project_addr" class="form-control pull-left" placeholder="Job Site Address" id="project_addr"value="<?php echo $result['project_addr']; ?>" style="width:50%;">
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <table style="width: 100%;">
                          <tr>
                            <td style="width: 33.3%;"><input type="text" name="project_city" class="form-control" placeholder="Job Site City" value="<?php echo $result['project_city']; ?>" id="project_city"></td>
                            <td style="width: 33.3%;"><select class="form-control" id="project_state" name="project_state">
                                <option value="AL" <?php echo ($result['project_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                <option value="AK" <?php echo ($result['project_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                <option value="AR" <?php echo ($result['project_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                <option value="CA" <?php echo ($result['project_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                <option value="CO" <?php echo ($result['project_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                <option value="CT" <?php echo ($result['project_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                <option value="DE" <?php echo ($result['project_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                <option value="FL" <?php echo ($result['project_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                <option value="GA" <?php echo ($result['project_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                <option value="HI" <?php echo ($result['project_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                <option value="ID" <?php echo ($result['project_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                <option value="IL" <?php echo ($result['project_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                <option value="IN" <?php echo ($result['project_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                <option value="IA" <?php echo ($result['project_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                <option value="KS" <?php echo ($result['project_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                <option value="KY" <?php echo ($result['project_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                <option value="LA" <?php echo ($result['project_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                <option value="ME" <?php echo ($result['project_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                <option value="MD" <?php echo ($result['project_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                <option value="MA" <?php echo ($result['project_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                <option value="MI" <?php echo ($result['project_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                <option value="MN" <?php echo ($result['project_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                <option value="MS" <?php echo ($result['project_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                <option value="MO" <?php echo ($result['project_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                <option value="MT" <?php echo ($result['project_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                <option value="NE" <?php echo ($result['project_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                <option value="NV" <?php echo ($result['project_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                <option value="NH" <?php echo ($result['project_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                <option value="NJ" <?php echo ($result['project_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                <option value="NM" <?php echo ($result['project_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                <option value="NY" <?php echo ($result['project_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                <option value="NC" <?php echo ($result['project_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                <option value="ND" <?php echo ($result['project_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                <option value="OH" <?php echo ($result['project_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                <option value="OK" <?php echo ($result['project_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                <option value="OR" <?php echo ($result['project_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                <option value="PA" <?php echo ($result['project_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                <option value="RI" <?php echo ($result['project_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                <option value="SC" <?php echo ($result['project_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                <option value="SD" <?php echo ($result['project_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                <option value="TN" <?php echo ($result['project_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                <option value="TX" <?php echo ($result['project_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                <option value="UT" <?php echo ($result['project_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                <option value="VT" <?php echo ($result['project_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                <option value="VA" <?php echo ($result['project_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                <option value="WA" <?php echo ($result['project_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                <option value="WV" <?php echo ($result['project_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                <option value="WI" <?php echo ($result['project_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                <option value="WY" <?php echo ($result['project_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                              </select></td>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['project_zip']; ?>" name="project_zip" class="form-control" placeholder="Job Site Zip" id="project_zip"></td>
                          </tr>
                        </table>
                      </td>
                      <td><input type="text" name="project_landline" class="form-control" placeholder="Job Site Landline" value="<?php echo $result['project_landline']; ?>" id="project_landline"></td>
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

                        $name = (!empty($contact['first_name'])) ? "{$contact['first_name']} {$contact['last_name']}" : $contact['company_name'];

                        $contact_dropdown .= "<option value='{$contact['id']}'>$name</option>";
                      }

                      $contact_dropdown .= "</optgroup></select>";
                    }

                    echo "<tr><td><div class='form-group'><label for='add_contact' class='pull-left' style='line-height:28px;padding-right:10px;'>Add Contact</label> $contact_dropdown <button type='button' class='btn waves-effect waves-light btn-primary assign_contact_so' style='margin:2px 0 0 10px;'> <i class='zmdi zmdi-plus-circle-o'></i> </button></div></td></tr>";

                    // displaying existing contact relationships
                    $so_contacts_qry = $dbconn->query("SELECT c.*, c2.description FROM sales_order_contacts soc LEFT JOIN contact c ON soc.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE so_id = '{$result['id']}' ORDER BY c.first_name, c.last_name ASC");

                    if($so_contacts_qry->num_rows > 0) {
                      while($so_contacts = $so_contacts_qry->fetch_assoc()) {
                        $name = (!empty($so_contacts['first_name'])) ? "{$so_contacts['first_name']} {$so_contacts['last_name']}" : $so_contacts['company_name'];

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
                            $selected = ($dealer['dealer_id'] === $result['dealer_code']) ? "selected" : NULL;

                            $name = (empty($dealer['first_name']) && empty($dealer['last_name'])) ? $dealer['company_name'] : "{$dealer['first_name']} {$dealer['last_name']}";

                            echo "<option value='{$dealer['dealer_id']}' $selected>{$dealer['dealer_id']} ($name)</option>";
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr style="height: 5px;">
                      <td colspan="3"></td>
                    </tr>
                    <tr>
                      <td colspan="3">
                        <input type="text" value="<?php echo $result['project_name']; ?>" name="project_name" class="form-control pull-left" placeholder="Project Name" id="project_name" style="width:50%;"/>
                        <input type="text" value="<?php echo $result['project_addr']; ?>" name="project_addr" class="form-control pull-left" placeholder="Project Address" id="project_addr" style="width:50%;">
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <table style="width: 100%;">
                          <tr>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['project_city']; ?>" name="project_city" class="form-control" placeholder="Project City" id="project_city"></td>
                            <td style="width: 33.3%;"><select class="form-control" id="project_state" name="project_state">
                                <option value="AL" <?php echo ($result['project_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                <option value="AK" <?php echo ($result['project_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                <option value="AR" <?php echo ($result['project_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                <option value="CA" <?php echo ($result['project_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                <option value="CO" <?php echo ($result['project_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                <option value="CT" <?php echo ($result['project_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                <option value="DE" <?php echo ($result['project_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                <option value="FL" <?php echo ($result['project_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                <option value="GA" <?php echo ($result['project_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                <option value="HI" <?php echo ($result['project_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                <option value="ID" <?php echo ($result['project_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                <option value="IL" <?php echo ($result['project_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                <option value="IN" <?php echo ($result['project_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                <option value="IA" <?php echo ($result['project_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                <option value="KS" <?php echo ($result['project_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                <option value="KY" <?php echo ($result['project_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                <option value="LA" <?php echo ($result['project_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                <option value="ME" <?php echo ($result['project_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                <option value="MD" <?php echo ($result['project_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                <option value="MA" <?php echo ($result['project_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                <option value="MI" <?php echo ($result['project_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                <option value="MN" <?php echo ($result['project_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                <option value="MS" <?php echo ($result['project_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                <option value="MO" <?php echo ($result['project_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                <option value="MT" <?php echo ($result['project_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                <option value="NE" <?php echo ($result['project_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                <option value="NV" <?php echo ($result['project_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                <option value="NH" <?php echo ($result['project_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                <option value="NJ" <?php echo ($result['project_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                <option value="NM" <?php echo ($result['project_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                <option value="NY" <?php echo ($result['project_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                <option value="NC" <?php echo ($result['project_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                <option value="ND" <?php echo ($result['project_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                <option value="OH" <?php echo ($result['project_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                <option value="OK" <?php echo ($result['project_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                <option value="OR" <?php echo ($result['project_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                <option value="PA" <?php echo ($result['project_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                <option value="RI" <?php echo ($result['project_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                <option value="SC" <?php echo ($result['project_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                <option value="SD" <?php echo ($result['project_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                <option value="TN" <?php echo ($result['project_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                <option value="TX" <?php echo ($result['project_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                <option value="UT" <?php echo ($result['project_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                <option value="VT" <?php echo ($result['project_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                <option value="VA" <?php echo ($result['project_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                <option value="WA" <?php echo ($result['project_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                <option value="WV" <?php echo ($result['project_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                <option value="WI" <?php echo ($result['project_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                <option value="WY" <?php echo ($result['project_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                              </select></td>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['project_zip']; ?>" name="project_zip" class="form-control" placeholder="Project Zip" id="project_zip"></td>
                          </tr>
                        </table>
                      </td>
                      <td><input type="text" value="<?php echo $result['project_landline']; ?>" name="project_landline" class="form-control" placeholder="Project Landline" id="project_landline"></td>
                    </tr>
                    <tr>
                      <td colspan="3">
                        <div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div>
                      </td>
                    </tr>
                    <tr>
                      <td><input type="text" value="<?php echo $result['name_1']; ?>" name="name_1" class="form-control" placeholder="Name 1" id="name_1"></td>
                      <td><input type="text" value="<?php echo $result['cell_1']; ?>" name="cell_1" class="form-control" placeholder="Cell Phone" id="cell_1"></td>
                      <td><input type="text" value="<?php echo $result['business_1']; ?>" name="business_1" class="form-control" placeholder="Secondary Phone" id="business_1"></td>
                    </tr>
                    <tr>
                      <td><input type="text" value="<?php echo $result['email_1']; ?>" name="email_1" class="form-control" placeholder="Email Address" id="email_1"></td>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="3">
                        <div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div>
                      </td>
                    </tr>
                    <tr>
                      <td><input type="text" value="<?php echo $result['name_2']; ?>" name="name_2" class="form-control" placeholder="Name 2" id="name_2"></td>
                      <td><input type="text" value="<?php echo $result['cell_2']; ?>" name="cell_2" class="form-control" placeholder="Cell Phone" id="cell_2"></td>
                      <td><input type="text" value="<?php echo $result['business_2']; ?>" name="business_2" class="form-control" placeholder="Secondary Phone" id="business_2"></td>
                    </tr>
                    <tr>
                      <td><input type="text" value="<?php echo $result['email_2']; ?>" name="email_2" class="form-control" placeholder="Email Address" id="email_2"></td>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr class="s_addr_empty">
                      <td colspan="3">
                        <div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div>
                      </td>
                    </tr>
                    <tr class="s_addr_empty">
                      <td colspan="3">
                        <?php
                        if (!empty($result['secondary_addr']) || !empty($result['secondary_city']) || !empty($result['secondary_zip']) || !empty($result['secondary_landline'])) {
                          $secondary_checked = " checked";
                          echo "<script>$('.secondary_addr_disp').show();</script>";
                        } else
                          $secondary_checked = null;
                        ?>

                        <div class="checkbox"><input id="secondary_addr_chk" type="checkbox" <?php echo $secondary_checked; ?>><label for="secondary_addr_chk"> Customer Secondary Address</label></div>
                      </td>
                    </tr>
                    <tr style="display:none;" class="secondary_addr_disp">
                      <td colspan="2"><input type="text" value="<?php echo $result['secondary_addr']; ?>" name="secondary_addr" class="form-control" placeholder="Secondary Address" id="secondary_addr"></td>
                      <td><input type="text" value="<?php echo $result['secondary_landline']; ?>" name="secondary_landline" class="form-control" placeholder="Secondary Landline" id="secondary_landline"></td>
                    </tr>
                    <tr style="display:none;" class="secondary_addr_disp">
                      <td colspan="2">
                        <table style="width: 100%;">
                          <tr>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['secondary_city']; ?>" name="secondary_city" class="form-control" placeholder="Secondary City" id="secondary_city"></td>
                            <td style="width: 33.3%;"><select class="form-control" id="secondary_state" name="secondary_state">
                                <option value="AL" <?php echo ($result['secondary_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                <option value="AK" <?php echo ($result['secondary_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                <option value="AR" <?php echo ($result['secondary_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                <option value="CA" <?php echo ($result['secondary_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                <option value="CO" <?php echo ($result['secondary_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                <option value="CT" <?php echo ($result['secondary_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                <option value="DE" <?php echo ($result['secondary_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                <option value="FL" <?php echo ($result['secondary_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                <option value="GA" <?php echo ($result['secondary_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                <option value="HI" <?php echo ($result['secondary_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                <option value="ID" <?php echo ($result['secondary_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                <option value="IL" <?php echo ($result['secondary_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                <option value="IN" <?php echo ($result['secondary_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                <option value="IA" <?php echo ($result['secondary_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                <option value="KS" <?php echo ($result['secondary_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                <option value="KY" <?php echo ($result['secondary_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                <option value="LA" <?php echo ($result['secondary_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                <option value="ME" <?php echo ($result['secondary_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                <option value="MD" <?php echo ($result['secondary_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                <option value="MA" <?php echo ($result['secondary_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                <option value="MI" <?php echo ($result['secondary_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                <option value="MN" <?php echo ($result['secondary_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                <option value="MS" <?php echo ($result['secondary_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                <option value="MO" <?php echo ($result['secondary_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                <option value="MT" <?php echo ($result['secondary_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                <option value="NE" <?php echo ($result['secondary_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                <option value="NV" <?php echo ($result['secondary_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                <option value="NH" <?php echo ($result['secondary_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                <option value="NJ" <?php echo ($result['secondary_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                <option value="NM" <?php echo ($result['secondary_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                <option value="NY" <?php echo ($result['secondary_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                <option value="NC" <?php echo ($result['secondary_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                <option value="ND" <?php echo ($result['secondary_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                <option value="OH" <?php echo ($result['secondary_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                <option value="OK" <?php echo ($result['secondary_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                <option value="OR" <?php echo ($result['secondary_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                <option value="PA" <?php echo ($result['secondary_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                <option value="RI" <?php echo ($result['secondary_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                <option value="SC" <?php echo ($result['secondary_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                <option value="SD" <?php echo ($result['secondary_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                <option value="TN" <?php echo ($result['secondary_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                <option value="TX" <?php echo ($result['secondary_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                <option value="UT" <?php echo ($result['secondary_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                <option value="VT" <?php echo ($result['secondary_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                <option value="VA" <?php echo ($result['secondary_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                <option value="WA" <?php echo ($result['secondary_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                <option value="WV" <?php echo ($result['secondary_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                <option value="WI" <?php echo ($result['secondary_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                <option value="WY" <?php echo ($result['secondary_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                              </select></td>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['secondary_zip']; ?>" name="secondary_zip" class="form-control" placeholder="Secondary Zip" id="secondary_zip"></td>
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
                        if (!empty($result['contractor_name']) || !empty($result['contractor_business']) || !empty($result['contractor_cell']) || !empty($result['contractor_email']) || !empty($result['contractor_zip']) ||
                          !empty($result['contractor_city']) || !empty($result['contractor_addr'])) {

                          $contractor_checked = " checked";
                          echo "<script>$('.contractor_disp').show();</script>";
                        } else
                          $contractor_checked = null;
                        ?>

                        <div class="checkbox"><input id="contractor_chk" type="checkbox" <?php echo $contractor_checked; ?>><label for="contractor_chk"> Contractor</label></div>
                      </td>
                    </tr>
                    <tr style="display:none;" class="contractor_disp">
                      <td><input type="text" value="<?php echo $result['contractor_name']; ?>" name="contractor_name" class="form-control" placeholder="Contractor Name" id="contractor_name"></td>
                      <td><input type="text" value="<?php echo $result['contractor_business']; ?>" name="contractor_business" class="form-control" placeholder="Contractor Business Number" id="contractor_business"></td>
                      <td><input type="text" value="<?php echo $result['contractor_cell']; ?>" name="contractor_cell" class="form-control" placeholder="Contractor Cell Number" id="contractor_cell"></td>
                    </tr>
                    <tr style="display:none;" class="contractor_disp">
                      <td><input type="text" value="<?php echo $result['contractor_addr']; ?>" name="contractor_addr" class="form-control" placeholder="Contractor Address" id="contractor_addr"></td>
                      <td colspan="2">
                        <table style="width: 100%;">
                          <tr>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['contractor_city']; ?>" name="contractor_city" class="form-control" placeholder="Contractor City" id="contractor_city"></td>
                            <td style="width: 33.3%;"><select class="form-control" id="contractor_state" name="contractor_state">
                                <option value="AL" <?php echo ($result['contractor_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                <option value="AK" <?php echo ($result['contractor_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                <option value="AR" <?php echo ($result['contractor_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                <option value="CA" <?php echo ($result['contractor_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                <option value="CO" <?php echo ($result['contractor_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                <option value="CT" <?php echo ($result['contractor_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                <option value="DE" <?php echo ($result['contractor_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                <option value="FL" <?php echo ($result['contractor_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                <option value="GA" <?php echo ($result['contractor_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                <option value="HI" <?php echo ($result['contractor_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                <option value="ID" <?php echo ($result['contractor_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                <option value="IL" <?php echo ($result['contractor_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                <option value="IN" <?php echo ($result['contractor_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                <option value="IA" <?php echo ($result['contractor_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                <option value="KS" <?php echo ($result['contractor_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                <option value="KY" <?php echo ($result['contractor_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                <option value="LA" <?php echo ($result['contractor_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                <option value="ME" <?php echo ($result['contractor_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                <option value="MD" <?php echo ($result['contractor_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                <option value="MA" <?php echo ($result['contractor_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                <option value="MI" <?php echo ($result['contractor_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                <option value="MN" <?php echo ($result['contractor_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                <option value="MS" <?php echo ($result['contractor_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                <option value="MO" <?php echo ($result['contractor_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                <option value="MT" <?php echo ($result['contractor_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                <option value="NE" <?php echo ($result['contractor_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                <option value="NV" <?php echo ($result['contractor_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                <option value="NH" <?php echo ($result['contractor_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                <option value="NJ" <?php echo ($result['contractor_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                <option value="NM" <?php echo ($result['contractor_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                <option value="NY" <?php echo ($result['contractor_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                <option value="NC" <?php echo ($result['contractor_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                <option value="ND" <?php echo ($result['contractor_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                <option value="OH" <?php echo ($result['contractor_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                <option value="OK" <?php echo ($result['contractor_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                <option value="OR" <?php echo ($result['contractor_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                <option value="PA" <?php echo ($result['contractor_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                <option value="RI" <?php echo ($result['contractor_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                <option value="SC" <?php echo ($result['contractor_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                <option value="SD" <?php echo ($result['contractor_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                <option value="TN" <?php echo ($result['contractor_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                <option value="TX" <?php echo ($result['contractor_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                <option value="UT" <?php echo ($result['contractor_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                <option value="VT" <?php echo ($result['contractor_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                <option value="VA" <?php echo ($result['contractor_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                <option value="WA" <?php echo ($result['contractor_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                <option value="WV" <?php echo ($result['contractor_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                <option value="WI" <?php echo ($result['contractor_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                <option value="WY" <?php echo ($result['contractor_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                              </select></td>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['contractor_zip']; ?>" name="contractor_zip" class="form-control" placeholder="Contractor Zip" id="contractor_zip"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr style="display:none;" class="contractor_disp">
                      <td><input type="text" value="<?php echo $result['contractor_email']; ?>" name="contractor_email" class="form-control" placeholder="Contractor Email Address" id="contractor_email"></td>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr style="display:none;" class="contractor_disp">
                      <td><input type="text" value="<?php echo $result['project_mgr']; ?>" name="project_mgr" class="form-control" placeholder="Project Manager" id="project_mgr"></td>
                      <td><input type="text" value="<?php echo $result['project_mgr_cell']; ?>" name="project_mgr_cell" class="form-control" placeholder="Project Manager Cell" id="project_mgr_cell"></td>
                      <td><input type="text" value="<?php echo $result['project_mgr_email']; ?>" name="project_mgr_email" class="form-control" placeholder="Project Manager Email" id="project_mgr_email"></td>
                    </tr>
                    <tr class="billing_empty">
                      <td colspan="3">
                        <div style="width:100%;height:3px;border:2px solid #132882;margin:5px 0;border-radius:5px;"></div>
                      </td>
                    </tr>
                    <tr class="billing_empty">
                      <td>
                        <?php
                        if (!empty($result['bill_to']) || !empty($result['billing_contact']) || !empty($result['billing_landline']) || !empty($result['billing_cell']) || !empty($result['billing_addr']) ||
                          !empty($result['billing_city']) || !empty($result['billing_zip']) || !empty($result['billing_account']) || !empty($result['billing_routing'])
                          || !empty($result['billing_cc_num']) || !empty($result['billing_cc_exp']) || !empty($result['billing_cc_ccv'])) {

                          $billing_checked = " checked";
                          echo "<script>$('.billing_info_disp').show();</script>";
                        } else
                          $billing_checked = null;

                        $b_homeowner = null;
                        $b_contractor = null;

                        if ($result['bill_to'] === 'homeowner')
                          $b_homeowner = " checked";
                        elseif ($result['bill_to'] === 'contractor')
                          $b_contractor = " checked";
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
                      <td><input type="text" value="<?php echo $result['billing_contact']; ?>" name="billing_contact" class="form-control" placeholder="Billing Contact" id="billing_contact"></td>
                      <td><input type="text" value="<?php echo $result['billing_landline']; ?>" name="billing_landline" class="form-control" placeholder="Billing Landline" id="billing_landline"></td>
                      <td><input type="text" value="<?php echo $result['billing_cell']; ?>" name="billing_cell" class="form-control" placeholder="Billing Cell" id="billing_cell"></td>
                    </tr>
                    <tr style="display:none;" class="billing_info_disp">
                      <td><input type="text" value="<?php echo $result['billing_addr']; ?>" name="billing_addr" class="form-control" placeholder="Billing Address" id="billing_addr"></td>
                      <td colspan="2">
                        <table style="width: 100%;">
                          <tr>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['billing_city']; ?>" name="billing_city" class="form-control" placeholder="Billing City" id="billing_city"></td>
                            <td style="width: 33.3%;"><select class="form-control" id="billing_state" name="billing_state">
                                <option value="AL" <?php echo ($result['billing_state'] === 'AL') ? "selected" : null; ?>>Alabama</option>
                                <option value="AK" <?php echo ($result['billing_state'] === 'AK') ? "selected" : null; ?>>Alaska</option>
                                <option value="AR" <?php echo ($result['billing_state'] === 'AR') ? "selected" : null; ?>>Arkansas</option>
                                <option value="CA" <?php echo ($result['billing_state'] === 'CA') ? "selected" : null; ?>>California</option>
                                <option value="CO" <?php echo ($result['billing_state'] === 'CO') ? "selected" : null; ?>>Colorado</option>
                                <option value="CT" <?php echo ($result['billing_state'] === 'CT') ? "selected" : null; ?>>Connecticut</option>
                                <option value="DE" <?php echo ($result['billing_state'] === 'DE') ? "selected" : null; ?>>Delaware</option>
                                <option value="FL" <?php echo ($result['billing_state'] === 'FL') ? "selected" : null; ?>>Florida</option>
                                <option value="GA" <?php echo ($result['billing_state'] === 'GA') ? "selected" : null; ?>>Georgia</option>
                                <option value="HI" <?php echo ($result['billing_state'] === 'HI') ? "selected" : null; ?>>Hawaii</option>
                                <option value="ID" <?php echo ($result['billing_state'] === 'ID') ? "selected" : null; ?>>Idaho</option>
                                <option value="IL" <?php echo ($result['billing_state'] === 'IL') ? "selected" : null; ?>>Illinois</option>
                                <option value="IN" <?php echo ($result['billing_state'] === 'IN') ? "selected" : null; ?>>Indiana</option>
                                <option value="IA" <?php echo ($result['billing_state'] === 'IA') ? "selected" : null; ?>>Iowa</option>
                                <option value="KS" <?php echo ($result['billing_state'] === 'KS') ? "selected" : null; ?>>Kansas</option>
                                <option value="KY" <?php echo ($result['billing_state'] === 'KY') ? "selected" : null; ?>>Kentucky</option>
                                <option value="LA" <?php echo ($result['billing_state'] === 'LA') ? "selected" : null; ?>>Louisiana</option>
                                <option value="ME" <?php echo ($result['billing_state'] === 'ME') ? "selected" : null; ?>>Maine</option>
                                <option value="MD" <?php echo ($result['billing_state'] === 'MD') ? "selected" : null; ?>>Maryland</option>
                                <option value="MA" <?php echo ($result['billing_state'] === 'MA') ? "selected" : null; ?>>Massachusetts</option>
                                <option value="MI" <?php echo ($result['billing_state'] === 'MI') ? "selected" : null; ?>>Michigan</option>
                                <option value="MN" <?php echo ($result['billing_state'] === 'MN') ? "selected" : null; ?>>Minnesota</option>
                                <option value="MS" <?php echo ($result['billing_state'] === 'MS') ? "selected" : null; ?>>Mississippi</option>
                                <option value="MO" <?php echo ($result['billing_state'] === 'MO') ? "selected" : null; ?>>Missouri</option>
                                <option value="MT" <?php echo ($result['billing_state'] === 'MT') ? "selected" : null; ?>>Montana</option>
                                <option value="NE" <?php echo ($result['billing_state'] === 'NE') ? "selected" : null; ?>>Nebraska</option>
                                <option value="NV" <?php echo ($result['billing_state'] === 'NV') ? "selected" : null; ?>>Nevada</option>
                                <option value="NH" <?php echo ($result['billing_state'] === 'NH') ? "selected" : null; ?>>New Hampshire</option>
                                <option value="NJ" <?php echo ($result['billing_state'] === 'NJ') ? "selected" : null; ?>>New Jersey</option>
                                <option value="NM" <?php echo ($result['billing_state'] === 'NM') ? "selected" : null; ?>>New Mexico</option>
                                <option value="NY" <?php echo ($result['billing_state'] === 'NY') ? "selected" : null; ?>>New York</option>
                                <option value="NC" <?php echo ($result['billing_state'] === 'NC') ? "selected" : null; ?>>North Carolina</option>
                                <option value="ND" <?php echo ($result['billing_state'] === 'ND') ? "selected" : null; ?>>North Dakota</option>
                                <option value="OH" <?php echo ($result['billing_state'] === 'OH') ? "selected" : null; ?>>Ohio</option>
                                <option value="OK" <?php echo ($result['billing_state'] === 'OK') ? "selected" : null; ?>>Oklahoma</option>
                                <option value="OR" <?php echo ($result['billing_state'] === 'OR') ? "selected" : null; ?>>Oregon</option>
                                <option value="PA" <?php echo ($result['billing_state'] === 'PA') ? "selected" : null; ?>>Pennsylvania</option>
                                <option value="RI" <?php echo ($result['billing_state'] === 'RI') ? "selected" : null; ?>>Rhode Island</option>
                                <option value="SC" <?php echo ($result['billing_state'] === 'SC') ? "selected" : null; ?>>South Carolina</option>
                                <option value="SD" <?php echo ($result['billing_state'] === 'SD') ? "selected" : null; ?>>South Dakota</option>
                                <option value="TN" <?php echo ($result['billing_state'] === 'TN') ? "selected" : null; ?>>Tennessee</option>
                                <option value="TX" <?php echo ($result['billing_state'] === 'TX') ? "selected" : null; ?>>Texas</option>
                                <option value="UT" <?php echo ($result['billing_state'] === 'UT') ? "selected" : null; ?>>Utah</option>
                                <option value="VT" <?php echo ($result['billing_state'] === 'VT') ? "selected" : null; ?>>Vermont</option>
                                <option value="VA" <?php echo ($result['billing_state'] === 'VA') ? "selected" : null; ?>>Virginia</option>
                                <option value="WA" <?php echo ($result['billing_state'] === 'WA') ? "selected" : null; ?>>Washington</option>
                                <option value="WV" <?php echo ($result['billing_state'] === 'WV') ? "selected" : null; ?>>West Virginia</option>
                                <option value="WI" <?php echo ($result['billing_state'] === 'WI') ? "selected" : null; ?>>Wisconsin</option>
                                <option value="WY" <?php echo ($result['billing_state'] === 'WY') ? "selected" : null; ?>>Wyoming</option>
                              </select></td>
                            <td style="width: 33.3%;"><input type="text" value="<?php echo $result['billing_zip']; ?>" name="billing_zip" class="form-control" placeholder="Billing Zip" id="billing_zip"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr style="display:none;" class="billing_info_disp">
                      <td style="height:8px"></td>
                    </tr>
                    <tr style="display:none;" class="billing_info_disp">
                      <td colspan="3">
                        <input type="text" value="<?php echo $result['billing_account']; ?>" name="billing_account" autocomplete="off" class="form-control pull-left" placeholder="ACH Account #" id="billing_account" style="width: 50%;">
                        <input type="text" value="<?php echo $result['billing_routing']; ?>" name="billing_routing" autocomplete="off" class="form-control pull-right" placeholder="ACH Routing #" id="billing_routing" style="width: 50%;">
                      </td>
                    </tr>
                    <tr style="display:none;" class="billing_info_disp">
                      <td><input type="text" value="<?php echo $result['billing_cc_num']; ?>" name="billing_cc_num" class="form-control" placeholder="Credit Card #" id="billing_cc_num"></td>
                      <td><input type="text" value="<?php echo $result['billing_cc_exp']; ?>" name="billing_cc_exp" class="form-control" placeholder="Exp. Date" id="billing_cc_exp"></td>
                      <td><input type="text" value="<?php echo $result['billing_cc_ccv']; ?>" name="billing_cc_ccv" class="form-control" placeholder="CCV Code" id="billing_cc_ccv"></td>
                    </tr>
                    <tr>
                      <td colspan="3"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
                    </tr>
                    <tr>
                      <td colspan="3"><h5>Contacts</h5></td>
                    </tr>
                    <?php
                    $contact_dropdown = null;

                    $contact_qry = $dbconn->query("SELECT c.id, c.first_name, c.last_name, c.company_name, c2.description FROM contact c LEFT JOIN contact_types c2 ON c.type = c2.id LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id ORDER BY c2.description, c.first_name, c.last_name ASC");

                    if($contact_qry->num_rows > 0) {
                      $contact_dropdown = "<select class='form-control pull-left add_contact_id ignoreSaveAlert' name='add_contact' style='width:50%;'>";

                      $last_group = null;

                      while($contact = $contact_qry->fetch_assoc()) {
                        if($contact['description'] !== $last_group) {
                          $contact_dropdown .= "</optgroup><optgroup label='{$contact['description']}'>";
                          $last_group = $contact['description'];
                        }

                        $name = (!empty($contact['first_name'])) ? "{$contact['first_name']} {$contact['last_name']}" : $contact['company_name'];

                        $contact_dropdown .= "<option value='{$contact['id']}'>$name</option>";
                      }

                      $contact_dropdown .= "</optgroup></select>";
                    }

                    echo "<tr><td colspan='3'><div class='form-group'><label for='add_contact' class='pull-left' style='line-height:28px;padding-right:10px;'>Add Contact</label> $contact_dropdown <button type='button' class='btn waves-effect waves-light btn-primary assign_contact_so' style='margin:2px 0 0 10px;'> <i class='zmdi zmdi-plus-circle-o'></i> </button></div></td></tr>";

                    // displaying existing contact relationships
                    $so_contacts_qry = $dbconn->query("SELECT c.*, c2.description FROM sales_order_contacts soc LEFT JOIN contact c ON soc.contact_id = c.id LEFT JOIN contact_types c2 ON c.type = c2.id WHERE so_id = '{$result['id']}' ORDER BY c.first_name, c.last_name ASC");

                    if($so_contacts_qry->num_rows > 0) {
                      while($so_contacts = $so_contacts_qry->fetch_assoc()) {
                        $name = (!empty($so_contacts['first_name'])) ? "{$so_contacts['first_name']} {$so_contacts['last_name']}" : $so_contacts['company_name'];

                        echo "<tr><td colspan='3'><button type='button' class='btn waves-effect waves-light btn-danger remove_assigned_contact_so' style='margin:2px 0;' data-id='{$so_contacts['id']}'> <i class='zmdi zmdi-minus-circle-outline'></i> </button> <a href='#' class='get_customer_info' data-view-id='{$so_contacts['id']}''>$name ({$so_contacts['description']})</a></td></tr>";
                      }
                    } else {
                      echo "<tr><td colspan='3'><strong>No Contacts Assigned</strong></td></tr>";
                    }
                    ?>
                  <?php } ?>
                </table>
              </div>

              <div class="col-md-3">
                <div class="row">
                  <div class="col-md-12" style="height:304px;overflow-y:auto;">
                    <p style="font-weight:bold;">Inquiries/Notes:</p>

                    <table class="table table-custom-nb table-v-top" width="100%">
                      <?php
                      if((bool)$_SESSION['userInfo']['dealer']) {
                        $dealer = strtolower($_SESSION['userInfo']['dealer_code']);
                        $where = "AND user.username LIKE '$dealer%'";
                      } else {
                        $where = null;
                      }

                      $so_inquiry_qry = $dbconn->query("SELECT notes.timestamp AS NTimestamp, notes.id AS nID, notes.*, user.name, cal_followup.* FROM notes LEFT JOIN user ON notes.user = user.id LEFT JOIN cal_followup ON cal_followup.type_id = notes.id WHERE (note_type = 'so_inquiry' OR note_type = 'so_note_log') AND notes.type_id = '{$result['id']}' $where ORDER BY notes.timestamp DESC;");

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

                        $notes = str_replace("  ", "&nbsp;&nbsp;", $so_inquiry['note']);

                        echo "<tr>";
                        echo "  <td width='26px' style='padding-right:5px;'><button class='btn waves-effect btn-primary pull-right reply_to_inquiry' id='{$so_inquiry['nID']}'> <i class='zmdi zmdi-mail-reply'></i> </button></td>";
                        echo "  <td>$notes -- <small><em>{$so_inquiry['name']} on $time $followup</em></small></td>";
                        echo "</tr>";

                        echo "<tr id='inquiry_reply_line_{$so_inquiry['nID']}' style='display:none;'>";
                        echo "  <td colspan='2'>
                                        <textarea class='form-control' name='inquiry_reply' id='inquiry_reply_{$so_inquiry['nID']}' placeholder='Reply to inquiry...'></textarea>
                                        <button type='button' style='margin-top:5px;' class='btn btn-primary waves-effect waves-light w-xs inquiry_reply_btn' id='{$so_inquiry['nID']}'>Reply</button>
                                    </td>";
                        echo "</tr>";

                        echo $inquiry_replies;

                        echo "<tr style='height:2px;'><td colspan='2' style='background-color:#000;'></td></tr>";
                      }
                      ?>
                    </table>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="row">
                  <div class="col-md-12">
                    <textarea class="form-control" name="inquiry" id="inquiry" placeholder="New Inquiry/Note" style="width:100%;height:215px;"></textarea>
                    <input type="text" name="inquiry_followup_date" id="inquiry_followup_date" class="form-control" placeholder="Followup Date" style="width:30%;float:left;">
                    <label for="inquiry_requested_of" style="float:left;padding:4px;"> requested of </label>
                    <select name="inquiry_requested_of" id="inquiry_requested_of" class="form-control" style="width:50%;float:left;">
                      <option value="null" selected disabled></option>
                      <?php
                      $user_qry = $dbconn->query("SELECT * FROM user WHERE account_status = 1 ORDER BY name ASC");

                      while ($user = $user_qry->fetch_assoc()) {
                        echo "<option value='{$user['id']}'>{$user['name']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <button type="button" class="btn btn-primary waves-effect waves-light w-xs save_so" data-sonum="<?php echo $result['so_num']; ?>">Save</button>
              </div>
            </div>
          </form>
        </div>

        <?php
        echo "</div></td>";
        echo "</tr>";
        /** END EDIT SO DISPLAY */

      }
      //</editor-fold>

      //<editor-fold desc="Add Room">
      /** ADD ROOM TO SO */
      echo $bouncer->validate('add_room') ? "<tr class='add_room' id='{$result['so_num']}' style='display:none;'><td colspan='8'><div style='display:none;'></div></td></tr>" : null;
      /** END ADD ROOM TO SO */
      //</editor-fold>

      //<editor-fold desc="View Rooms">
      /** BEGIN ROOM INFORMATION */
      if ($bouncer->validate('view_rooms')) {
        echo "  <tr id='tr_room_{$result['so_num']}'>";
        echo "    <td colspan='8'><div id='div_room_{$result['so_num']}'>"; ?>

        <div class="col-md-12">
          <div class="row">
            <table class="table pull-right" style="width:99%">
              <thead>
              <tr>
                <th colspan="2">ROOM</th>
                <?php if ($bouncer->validate('view_brackets')) { ?>
                  <th>SALES</th>
                  <th>SAMPLE</th>
                  <th>PRE-PRODUCTION</th>
                  <th>DOOR/DRAWER</th>
                  <th>MAIN</th>
                  <th>CUSTOM</th>
                  <th>SHIPPING</th>
                  <th>INSTALLATION</th>
                  <th>PICK/MATERIALS</th>
                  <th>EDGEBANDING</th>
                <?php } else { ?>
                  <th>&nbsp;</th>
                <?php } ?>
              </tr>
              </thead>
              <tbody>
              <?php
              $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '{$result['so_num']}' ORDER BY room, iteration ASC");

              $prev_room = null;
              $prev_seq = null;

              if ($room_qry->num_rows > 0) {
                while ($room = $room_qry->fetch_assoc()) {
                  $add_iteration_btn = null;
                  $add_seq_btn = null;

                  $individual_bracket = json_decode($room['individual_bracket_buildout']);

                  $iteration = explode('.', number_format($room['iteration'], 2));

                  if (empty($prev_seq)) {
                    $prev_seq = $iteration[0];
                  }

                  if ($room['room'] === $prev_room) {
                    if ($iteration[0] === $prev_seq) {
                      $room_name = ".{$iteration[1]}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}: {$room['room_name']}";
                      $tab = "<div class='pull-left' style='width:15px;'>&nbsp</div>";

                      $seq_visible = "hidden";
                      $iteration_visible = "hidden";
                    } else {
                      $room_name = "{$room['iteration']}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}: {$room['room_name']}";
                      $prev_seq = $iteration[0];
                      $tab = "<div class='pull-left' style='width:8px;'>&nbsp</div>";

                      $seq_visible = "hidden";
                      $iteration_visible = "visible";
                    }

                    $add_seq_btn = null;
                  } else {
                    $room_name = "{$room['room']}{$room['iteration']}-{$room['product_type']}{$room['order_status']}{$room['days_to_ship']}: {$room['room_name']}";
                    $prev_room = $room['room'];

                    $tab = null;

                    $seq_visible = "visible";
                    $iteration_visible = "visible";
                  }

                  if ($bouncer->validate('view_brackets')) {
                    $salesColor = determineColor($room, 'sales');
                    $preprodColor = determineColor($room, 'preproduction');
                    $sampleColor = determineColor($room, 'sample');
                    $doorColor = determineColor($room, 'doordrawer');
                    $customsColor = determineColor($room, 'custom');
                    $mainColor = determineColor($room, 'main');
                    $shippingColor = determineColor($room, 'shipping');
                    $installColor = determineColor($room, 'install_bracket');
                    $pickmatColor = determineColor($room, 'pick_materials');
                    $ebColor = determineColor($room, 'edgebanding');

                    $sales_published_display = getBracketInfo('sales', $room['sales_bracket'], $room);
                    $sample_published_display = getBracketInfo('sample', $room['sample_bracket'], $room);
                    $preprod_published_display = getBracketInfo('preproduction', $room['preproduction_bracket'], $room);
                    $door_published_display = getBracketInfo('doordrawer', $room['doordrawer_bracket'], $room);
                    $main_published_display = getBracketInfo('main', $room['main_bracket'], $room);
                    $customs_published_display = getBracketInfo('custom', $room['custom_bracket'], $room);
                    $shipping_published_display = getBracketInfo('shipping', $room['shipping_bracket'], $room);
                    $install_published_display = getBracketInfo('install_bracket', $room['install_bracket'], $room);
                    $pickmat_published_display = getBracketInfo('pick_materials', $room['pick_materials_bracket'], $room);
                    $edgebanding_published_display = getBracketInfo('edgebanding', $room['edgebanding_bracket'], $room);
                  }

                  $target_dir = SITE_ROOT . "/attachments/";
                  $attachment_dir = "{$target_dir}{$room['so_parent']}/{$room['room']}/{$room['iteration']}";
                  $http_base = "/attachments/{$room['so_parent']}/{$room['room']}/{$room['iteration']}/";

                  if (file_exists($attachment_dir)) {
                    $attachment_code = "btn-primary";
                  } else {
                    $attachment_code = "btn_secondary disabled";
                  }

                  switch ($room['order_status']) {
                    case '$':
                      $order_status = '<strong>Production</strong>';
                      break;
                    case '#':
                      $order_status = '<strong>Quote</strong>';
                      break;
                    case '+':
                      $order_status = '<strong>Completed</strong>';
                      break;
                    case '-':
                      $order_status = '<strong>Lost</strong>';
                      break;
                    case 'H':
                      $order_status = '<strong>Hold</strong>';
                      break;
                    case 'P':
                      $order_status = '<strong>Pending</strong>';
                      break;
                    case 'R':
                      $order_status = '<strong>Referred</strong>';
                      break;
                    case 'N':
                      $order_status = '<strong>Inquiry</strong>';
                      break;
                    case '!':
                      $order_status = '<strong>Pillar Missing</strong>';
                      break;
                  }

                  echo "<tr class='cursor-hand room_line' id='{$room['id']}'>";

                  echo "<td class='nowrap' style='width:50px;'>";
                  echo $bouncer->validate('edit_room') ? " <button class='btn waves-effect btn-primary edit_room' id='{$room['id']}' data-sonum='{$room['so_parent']}'><i class='zmdi zmdi-edit'></i></button>" : null;
                  echo $bouncer->validate('view_attachments') ? " <button class='btn waves-effect $attachment_code' id='show_attachments_room_{$room['id']}'><i class='zmdi zmdi-attachment-alt'></i></button>" : null;
                  echo $bouncer->validate('add_sequence') ? " <button class='btn btn-primary-outline waves-effect add_iteration' data-roomid='{$room['id']}' data-sonum='{$result['so_num']}' data-addto='sequence' data-iteration='{$room['iteration']}' data-toggle='tooltip' data-placement='top' title='' data-original-title='Add additional sequence' style='font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;visibility:$seq_visible;'> S +1</button>" : null;
                  echo $bouncer->validate('add_iteration') ? " <button class='btn btn-primary-outline waves-effect add_iteration' data-roomid='{$room['id']}' data-sonum='{$result['so_num']}' data-addto='iteration' data-iteration='{$room['iteration']}' data-toggle='tooltip' data-placement='top' title='' data-original-title='Add additional iteration' style='font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;visibility:$iteration_visible;'> I +.01</button>" : null;
                  echo '</td>';

                  echo "  <td class='nowrap'><span class='pull-left'>{$tab}{$room_name}</span> <span class='pull-right' style='margin-right:5px;'>$order_status</span></td>";

                  if ($bouncer->validate('view_brackets')) {
                    echo "  <td class='$salesColor' style='width:9%'>$sales_published_display</td>";
                    echo "  <td class='$sampleColor' style='width:9%'>$sample_published_display</td>";
                    echo "  <td class='$preprodColor' style='width:9%'>$preprod_published_display</td>";
                    echo "  <td class='$doorColor' style='width:9%'>$door_published_display</td>";
                    echo "  <td class='$mainColor' style='width:9%'>$main_published_display</td>";
                    echo "  <td class='$customsColor' style='width:9%'>$customs_published_display</td>";
                    echo "  <td class='$shippingColor' style='width:9%'>$shipping_published_display</td>";
                    echo "  <td class='$installColor' style='width:9%'>$install_published_display</td>";
                    echo "  <td class='$pickmatColor' style='width:9%'>$pickmat_published_display</td>";
                    echo "  <td class='$ebColor' style='width:9%'>$edgebanding_published_display</td>";
                  } else {
                    echo "  <td style='width:81%'>&nbsp;</td>";
                  }

                  echo "</tr>";

                  echo $bouncer->validate('edit_room') ? "<tr class='tr_room_actions' id='{$room['id']}' style='display:none;'><td colspan='12'><div style='display:none;'></div></td></tr>" : null;

                  //<editor-fold desc="Attachments">
                  /** BEGIN DISPLAY OF ATTACHMENTS */
                  if ($bouncer->validate('view_attachments')) {
                    echo "<tr id='tr_attachments_{$room['id']}' style='display: none;'>";
                    echo "  <td colspan='10'><div id='div_attachments_{$room['id']}' style='display: none;'>";
                    ?>

                    <div class="col-md-12">
                      <?php
                      $scanned_directory = array_diff(scandir($attachment_dir), array('..', '.'));

                      foreach ($scanned_directory as $file) {
                        echo "<a href='{$http_base}$file' target='_blank'>$file</a><br />";
                      }
                      ?>
                    </div>

                    <?php echo "</div></td>";
                    echo "</tr>";
                  }
                  /** END DISPLAY OF ATTACHMENTS */
                  //</editor-fold>
                }
              }
              ?>
              </tbody>
            </table>
          </div>
        </div>

        <?php echo "    </td>";
        echo "  </tr>";
      }
      /** END ROOM INFORMATION */
      //</editor-fold>
    }
    /** END LISTING OF SO'S */
    //</editor-fold>
  }
}
?>