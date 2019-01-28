<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 1/28/2019
 * Time: 10:17 AM
 */

namespace associations;


class associations {
  private function getContactCard($contact) {
    return <<<HEREDOC
    <div class="contact-card">
      <div style="float:right;">
        <i class="fa fa-pencil-square primary-color cursor-hand edit_assigned_contact" data-id="{$contact['cID']}" title="Edit Contact"></i>
        <i class="fa fa-minus-square danger-color cursor-hand remove_assigned_contact" data-id="{$contact['id']}" title="Remove Contact"></i>
      </div>
      
      <h5><a href="#">{$contact['first_name']} {$contact['last_name']}</a></h5>
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

    $contact_dropdown = null;

    $contact_qry = $dbconn->query('SELECT c.id, c.first_name, c.last_name FROM contact c LEFT JOIN user u ON c.created_by = u.id LEFT JOIN dealers d ON u.dealer_id = d.id ORDER BY c.first_name, c.last_name ASC');

    if($contact_qry->num_rows > 0) {
      $contact_dropdown = "<select class='c_input pull-left contact_id ignoreSaveAlert' style='width:100%;' name='add_contact'><option value='' disabled selected>Select</option>";

      while($contact = $contact_qry->fetch_assoc()) {
        $contact_dropdown .= "<option value='{$contact['id']}'>{$contact['first_name']} {$contact['last_name']}</option>";
      }

      $contact_dropdown .= '</select>';
    }

    echo "<table class='m-b-10' width='100%'>
            <tr>
              <td width='90px'><label for='add_contact'>Add Contact</label></td>
              <td>$contact_dropdown</td>
              <td width='20px'><i class='fa fa-plus-square assign_contact primary-color cursor-hand' data-type='$association_type' data-type-id='$association_id'></i></td>
            </tr>
          </table>";

    // displaying existing contact relationships
    $so_contacts_qry = $dbconn->query("SELECT c.*, a.id AS id, a.associated_as FROM contact_associations a LEFT JOIN contact c ON a.contact_id = c.id WHERE a.type_id = '$association_id' AND a.type = '$association_type' ORDER BY c.first_name, c.last_name ASC");

    echo "<div class='contact-box'>";

    if($so_contacts_qry->num_rows > 0) {
      while($so_contacts = $so_contacts_qry->fetch_assoc()) {
        echo $this->getContactCard($so_contacts);
      }
    } else {
      echo '<strong>No Contacts</strong>';
    }

    echo '</div>';
  }
}