<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 1/28/2019
 * Time: 10:17 AM
 */

namespace associations;


class associations {
  private function getContactCard($contact, $association_type) {
    return <<<HEREDOC
    <div class="contact-card">
      <div style="float:right;">
        <!--<i class="fa fa-bank primary-color cursor-hand assoc_set_commission" data-id="{$contact['id']}" title="Commission Schedule"></i>-->
        <!--<i class="fa fa-pencil-square primary-color cursor-hand edit_assigned_contact" data-id="{$contact['cID']}" title="Edit Contact"></i>-->
        <i class="fa fa-minus-square danger-color cursor-hand remove_assigned_contact" data-id="{$contact['uID']}" data-type='$association_type' title="Remove Contact"></i>
      </div>
      
      <h5><a href="#">{$contact['name']}</a></h5>
      <h6>{$contact['associated_as']}</h6>
    
      <p>{$contact['cell']}<br>{$contact['email']}</p>
    </div>
HEREDOC;
  }

  /*
   * @existing_account_id = the ID that we're trying to reference in DB:contact_associations
   * @association_type = the type of ID that we're sending across
   */
  public function displayContactAssociations($association_id, $association_type) {
    global $dbconn;

    //<editor-fold desc="Initiating the dropdown for contacts">
    $contact_dropdown = null;

    // TODO: Eliminate existing associations and self from this list

    $contact_qry = $dbconn->query("SELECT 
       c.id, 
       IF(TRIM(c.company_name) != '', c.company_name, CONCAT(c.first_name, ' ', c.last_name)) AS name,
       IF(TRIM(c.company_name) != '', 'Organization', 'Individual') AS type
    FROM contact c 
      LEFT JOIN user u ON c.created_by = u.id 
      LEFT JOIN dealers d ON u.dealer_id = d.id 
    ORDER BY FIELD(type, 'Organization', 'Individual'), c.first_name, c.last_name ASC");

    $prev_type = null;

    if($contact_qry->num_rows > 0) {
      $contact_dropdown = "<select class='c_input pull-left contact_id ignoreSaveAlert' style='width:100%;' name='add_contact'><option value='' disabled selected>Select</option>";

      while($contact = $contact_qry->fetch_assoc()) {
        if($contact['type'] !== $prev_type) {
          $contact_dropdown .= "</optgroup><optgroup label='{$contact['type']}'>";

          $prev_type = $contact['type'];
        }

        $contact_dropdown .= "<option value='{$contact['id']}'>{$contact['name']}</option>";
      }

      $contact_dropdown .= '</select>';
    }

    echo "<table class='m-b-10' width='100%'>
            <tr>
              <td width='120px'><label for='add_contact'>Associate Contact</label></td>
              <td>$contact_dropdown</td>
              <td width='40px'>
                <i class='fa fa-chain assign_contact primary-color cursor-hand' data-type='$association_type' data-type-id='$association_id' title='Associate contact'></i>
                <i class='fa fa-plus-square association_add_new primary-color cursor-hand' title='Add New Contact'></i>
              </td>
            </tr>
          </table>";
    //</editor-fold>

    //<editor-fold desc="Existing contacts">
    if($association_type === 'contact') {
      $assoc_qry = $dbconn->query("SELECT 
        '$association_id' AS id,
        ctc.id AS uID,
        c.id AS cID,
        IF(TRIM(c.company_name != ''), c.company_name, CONCAT(c.first_name, ' ', c.last_name)) AS name,
        ctc.associated_as,
        c.primary_phone,
        c.email
      FROM contact_to_contact ctc
        LEFT JOIN contact c on ctc.contact_to = c.id
      WHERE contact_from = $association_id OR contact_to = $association_id"); // remove "contact_to" if you only want one way communication
    } elseif($association_type === 'project') {
      $assoc_qry = $dbconn->query("SELECT 
        '$association_id' AS id,
        c.id AS cID,
        ctso.id AS uID,
        IF(TRIM(c.company_name != ''), c.company_name, CONCAT(c.first_name, ' ', c.last_name)) AS name,
        ctso.associated_as,
        c.primary_phone,
        c.email
      FROM contact_to_sales_order ctso 
        LEFT JOIN contact c on ctso.contact_id = c.id
      WHERE sales_order_id = $association_id");
    }

    echo "<div class='contact-box'>";

    if($assoc_qry->num_rows > 0) {
      while($assoc = $assoc_qry->fetch_assoc()) {
        echo $this->getContactCard($assoc, $association_type);
      }
    } else {
      echo '<strong>No Contacts</strong>';
    }

    echo '</div>';
    //</editor-fold>
  }
}