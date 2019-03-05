<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 2/6/2019
 * Time: 10:01 AM
 */

namespace customer;

use DropdownOpts\dropdown_options;

class customer {
  private $sales_users = [];
  private $drop_opts;

  public function __construct() {
    global $dbconn;

    // list of all sales people for commission structure
    $sales_qry = $dbconn->query("SELECT id, IF(company_name != '', company_name, CONCAT(first_name, ' ', last_name)) AS name FROM contact ORDER BY first_name, last_name, company_name ASC");

    while($sales = $sales_qry->fetch_assoc()) {
      $this->sales_users[$sales['id']] = $sales['name'];
    }

    $this->drop_opts = new dropdown_options();
  }

  public function getAddOpts($field, $value = null) {
    global $dbconn;
    $opt_out = '';

    $opt_qry = $dbconn->query("SELECT * FROM contact_add_options WHERE `field` = '$field' AND enabled = TRUE ORDER BY `option` ASC;");

    while($opt = $opt_qry->fetch_assoc()) {
      if($opt['value'] === $value) {
        $sel = 'selected';
      } elseif($opt['id'] === $value) {
        $sel = 'selected';
      } else {
        $sel = '';
      }

      $opt_out .= "<option value='{$opt['id']}' $sel>{$opt['option']}</option>";
    }
    
    return $opt_out;
  }

  public function displaySalesCommission($displayName, $fieldName, $fieldID, $sel = null, $commission = null) {
    if(empty($sel)) {
      $sale_user_opts = '<option selected disabled>Default</option>';
    }

    foreach($this->sales_users AS $id => $name) {
      if((int)$sel === $id) {
        $selected = 'selected';
      } else {
        $selected = '';
      }

      $sale_user_opts .= "<option value='$id' $selected>$name</option>";
    }

    return <<<HEREDOC
  <table width="100%">
    <tr>
      <td width="5%"><i class="fa fa-question-circle cursor-hand {$fieldName}_commission_history" title="{$displayName} History"></i></td>
      <td width="*"><select class="c_input" name="{$fieldName}_commission_user" id="{$fieldName}_commission_user" title="{$displayName} Assigned">$sale_user_opts</select></td>
      <td width="10%" style="padding-left:4px;"><input type="text" value="$commission" maxlength="3" name="{$fieldName}_commission" class="c_input" placeholder="%" id="{$fieldID}_commission"></td>
      <td width="5%" style="padding-left:4px;">%</td>
    </tr>
  </table>
HEREDOC;
  }

  public function displayFields($contact_id = null) {
    global $dbconn;

    $rnum = mt_rand(1,9999999) * (microtime() / 2);

    //<editor-fold desc="Contact information">
    $contact = [];
    $type = null;
    $generate_pin = [];

    if(!empty($contact_id)) {
      $contact_qry = $dbconn->query("SELECT *, 
       c.id AS conID,
       ce.id AS empID,
       cv.id AS vendID,
       cc.id AS custID,
       cv.established_date AS vendEstDate,
       cv.status AS vendStatus,
       cv.federal_id AS vendFedID,
       cc.established_date AS custEstDate,
       cc.status AS custStatus,
       cc.federal_id AS custFedID
      FROM contact c 
        LEFT JOIN contact_employee ce on c.id = ce.contact_id 
        LEFT JOIN contact_vendor cv on c.id = cv.contact_id 
        LEFT JOIN contact_customer cc on c.id = cc.contact_id 
      WHERE c.id = $contact_id");

      if($contact_qry && $contact_qry->num_rows > 0) {
        $contact = $contact_qry->fetch_assoc();
        $uid = $contact['unique_id'];

        $generate_pin = ['no' => 'selected', 'yes' => null];

        $hire_date = !empty($contact['hire_date']) ? date(DATE_DEFAULT, $contact['hire_date']) : '';
        $birthday = !empty($contact['personal_birthday']) ? date(DATE_DEFAULT, $contact['personal_birthday']) : '';
        $vend_est_date = !empty($contact['vendEstDate']) ? date(DATE_DEFAULT, $contact['vendEstDate']) : '';
        $cust_est_date = !empty($contact['custEstDate']) ? date(DATE_DEFAULT, $contact['custEstDate']) : '';

        // determine which checkboxes should be checked
        $emp_checked = !empty($contact['empID']) ? 'checked' : null;
        $vend_checked = !empty($contact['vendID']) ? 'checked' : null;
        $cust_checked = !empty($contact['custID']) ? 'checked' : null;

        $cust_ship_different = !empty(array_filter([$contact['ship_address'], $contact['ship_city'], $contact['ship_zip']])) ? 'checked' : null;
        $vend_rec_different = !empty(array_filter([$contact['receive_address'], $contact['receive_city'], $contact['receive_zip']])) ? 'checked' : null;
        $vend_payment_different = !empty(array_filter([$contact['payment_contact'], $contact['payment_address'], $contact['payment_city'], $contact['payment_zip'],
          $contact['payment_primary_phone'], $contact['payment_secondary_phone'], $contact['payment_other_phone'], $contact['payment_fax']])) ? 'checked' : null;
        $emp_personal_different = !empty(array_filter([$contact['personal_address'], $contact['personal_city'], $contact['personal_zip'], $contact['personal_email'],
          $contact['personal_phone'], $contact['personal_birthday']])) ? 'checked' : null;
        $emp_emergency_different = !empty(array_filter([$contact['emergency_name'], $contact['emergency_relationship'], $contact['emergency_address'], $contact['emergency_city'],
          $contact['emergency_zip'], $contact['emergency_pri_phone'], $contact['emergency_secondary_phone'], $contact['emergency_other_phone'], $contact['emergency_email']])) ? 'checked' : null;

        $contact_type = !empty(trim($contact['company_name'])) ? 'organization' : 'individual';
      }
    } else {
      $type = "<tr><td><label for='{$rnum}_new_type'>Type:</label></td><td><select class='c_input new_type' name='new_type' id='{$rnum}_new_type'><option>Organization</option><option>Individual</option></select></td></tr>";
      $generate_pin = ['no' => null, 'yes' => 'selected'];

      $vend_est_date = date(DATE_DEFAULT);

      //<editor-fold desc="Get unique identifier">
      $uid_qry = $dbconn->query('SELECT MAX(id) AS id FROM contact');
      $uid = $uid_qry->fetch_assoc();

      $uid_num = $uid['id'] + 1;
      $uid = 'R' . $uid_num;
      //</editor-fold>
    }
    //</editor-fold>

    //<editor-fold desc="Available departments">
    $available_depts = '';

    $dept_qry = $dbconn->query("SELECT id, responsible_dept FROM operations WHERE responsible_dept != 'N/A' GROUP BY responsible_dept ORDER BY responsible_dept ASC");

    while($dept = $dept_qry->fetch_assoc()) {
      $selected = $dept['responsible_dept'] === $contact['department'] ? 'selected' : null;

      $available_depts .= "<option value='{$dept['responsible_dept']}' $selected>{$dept['responsible_dept']}</option>";
    }
    //</editor-fold>

    //<editor-fold desc="Available permission groups">
    $available_perms = '';

    $perm_groups = $dbconn->query('SELECT * FROM permission_groups;');

    while($perm = $perm_groups->fetch_assoc()) {
      $selected = $perm['id'] === $contact['user_access'] ? 'selected' : null;

      $available_perms .= "<option value='{$perm['id']}' $selected>{$perm['name']}</option>";
    }
    //</editor-fold>

    echo /** @lang HTML */
    <<<HEREDOC
      <!--<editor-fold desc="Global information">-->
      <input type="hidden" name="contactType" class="contactType" value="$contact_type" />
      <input type="hidden" name="contactID" class="contactID" />
      
      <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
        <colgroup>
          <col width="41%">
          <col width="*">
        </colgroup>
        $type
        
        <tr>
          <td><label for="{$rnum}_identifier">ID:</label></td>
          <td><input type="text" id="{$rnum}_identifier" value="$uid" data-uid="$uid_num" autocomplete="no" name="identifier" class="c_input identifier" placeholder="Unique ID" /></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr class="add_new_org">
          <td><label for="{$rnum}_org_name">Name:</label></td>
          <td><input type="text" value="{$contact['company_name']}" name="org_name" class="c_input" placeholder="Organization Name" id="{$rnum}_org_name" /></td>
        </tr>
        <tr class="add_new_individual">
          <td><label for="{$rnum}_first_name">First Name:</label></td>
          <td><input type="text" class="c_input" id="{$rnum}_first_name" name="first_name" value="{$contact['first_name']}" placeholder="First Name"></td>
        </tr>
        <tr class="add_new_individual">
          <td><label for="{$rnum}_last_name">Last Name:</label></td>
          <td><input type="text" class="c_input" id="{$rnum}_last_name" name="last_name" value="{$contact['last_name']}" placeholder="Last Name"></td>
        </tr>
        <tr class="add_new_individual">
          <td><label for="{$rnum}_title">Title:</label></td>
          <td><input type="text" class="c_input" id="{$rnum}_title" name="title" value="{$contact['title']}" placeholder="Title"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_country">Country:</label></td>
          <td><select class="c_input" name="country" id="{$rnum}_country">{$this->drop_opts->getCountryOpts($contact['country'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_address">Address:</label></td>
          <td><input type="text" value="{$contact['address']}" name="address" class="c_input" placeholder="Address" id="{$rnum}_address" /></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_city">City:</label></td>
          <td><input type="text" value="{$contact['city']}" name="city" class="c_input" placeholder="City" id="{$rnum}_city"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_state">State:</label></td>
          <td><select class="c_input" id="{$rnum}_state" name="state">{$this->drop_opts->getStateOpts($contact['state'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_zip">Zip:</label></td>
          <td><input type="text" value="{$contact['zip']}" name="zip" class="c_input" placeholder="Zip" id="{$rnum}_zip"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_email">Email:</label></td>
          <td><input type="text" value="{$contact['email']}" name="email" class="c_input" placeholder="Email" id="{$rnum}_email"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_primary_phone">Primary Phone:</label></td>
          <td><input type="text" value="{$contact['primary_phone']}" name="primary_phone" class="c_input" placeholder="Primary Phone" id="{$rnum}_primary_phone"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_secondary_phone">Secondary Phone:</label></td>
          <td><input type="text" value="{$contact['secondary_phone']}" name="secondary_phone" class="c_input" placeholder="Secondary Phone" id="{$rnum}_secondary_phone"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_other_phone">Other Phone:</label></td>
          <td><input type="text" value="{$contact['other_phone']}" name="other_phone" class="c_input" placeholder="Other Phone" id="{$rnum}_other_phone"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_fax">Fax:</label></td>
          <td><input type="text" value="{$contact['fax']}" name="fax" class="c_input" placeholder="Fax" id="{$rnum}_fax"></td>
        </tr>
        <tr>
          <td colspan="2"><div style="width:100%;height:3px;border:2px solid #BBB;margin:5px 0;border-radius:5px;"></div></td>
        </tr>
      </table>
      <!--</editor-fold>-->

      <!--<editor-fold desc="Customer Information">-->
      <div class="clearfix"><div class="checkbox pull-left"><input id="{$rnum}_add_customer_checked" name="add_customer_checked" class="ignoreSaveAlert add_customer_checked" type="checkbox" value="1" $cust_checked><label for="{$rnum}_add_customer_checked"> <b><u>Customer</u></b></label></div></div>

      <table class="table table-custom-nb table-indented customer_data" style="display:none;">
        <colgroup>
          <col width="41%">
          <col width="*">
        </colgroup>
        <tr>
          <td><label for="{$rnum}_cust_established_date">Established Date:</label></td>
          <td><input type="text" value="$cust_est_date" name="cust_established_date" class="c_input" placeholder="Established Date" id="{$rnum}_cust_established_date"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_status">Customer Status:</label></td>
          <td><select name="cust_status" id="{$rnum}_cust_status" class="c_input">{$this->getAddOpts('cust_status', $contact['custStatus'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_group">Customer Group:</label></td>
          <td><select name="cust_group" id="{$rnum}_cust_group" class="c_input">{$this->getAddOpts('cust_group', $contact['group'])}</select></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_max_commission">Maximum Commission:</label></td>
          <td><input style="width:15%;" type="text" value="{$contact['max_commission']}" name="cust_max_commission" class="c_input pull-left" placeholder="Max" id="{$rnum}_cust_max_commission"> %</td>
        </tr>
        <tr>
          <td colspan="2">
            <table width="95%" class="pull-right">
              <colgroup>
                <col width="35%">
                <col width="*">
              </colgroup>
              <tr>
                <td><label for="{$rnum}_salesman_commission">Salesman Commission:</label></td>
                <td>{$this->displaySalesCommission('Salesman', 'cust_salesman', "{$rnum}_salesman", $contact['salesman_commission_id'], $contact['salesman_commission_percent'])}</td>
              </tr>
              <tr>
                <td><label for="{$rnum}_referral_commission">Referral Commission:</label></td>
                <td>{$this->displaySalesCommission('Referral', 'cust_referral', "{$rnum}_referral", $contact['referral_commission_id'], $contact['referral_commission_percent'])}</td>
              </tr>
              <tr>
                <td><label for="{$rnum}_sales_group_commission">Sales Group Commission:</label></td>
                <td>{$this->displaySalesCommission('Sales Group', 'cust_sales_group', "{$rnum}_sales_group", $contact['sales_group_commission_id'], $contact['sales_group_commission_percent'])}</td>
              </tr>
              <tr>
                <td><label for="{$rnum}_other_commission">Other Commission:</label></td>
                <td>{$this->displaySalesCommission('Other', 'cust_other', "{$rnum}_other", $contact['other_commission_id'], $contact['other_commission_percent'])}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_ship_method">Ship Method:</label></td>
          <td><select name="cust_ship_method" id="{$rnum}_cust_ship_method" class="c_input">{$this->getAddOpts('cust_ship_method', $contact['ship_method'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_ship_billto">Ship Bill To:</label></td>
          <td><select name="cust_ship_billto" id="{$rnum}_cust_ship_billto" class="c_input">{$this->getAddOpts('cust_ship_billto', $contact['ship_bill_to'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_ship_account_num">Ship Account #:</label></td>
          <td><input type="text" value="{$contact['ship_account']}" name="cust_ship_account_num" class="c_input" placeholder="Shipping Account #" id="{$rnum}_cust_ship_account_num"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_residential_delivery">Residential Delivery:</label></td>
          <td><select name="cust_residential_delivery" id="{$rnum}_cust_residential_delivery" class="c_input">{$this->getAddOpts('cust_residential_delivery', $contact['residential_delivery'])}</select></td>
        </tr>
        <tr>
          <td colspan="2"><div class="checkbox pull-left"><input id="{$rnum}_cust_ship_addr_different" class="ignoreSaveAlert cust_ship_addr_different" type="checkbox" value="1" $cust_ship_different><label for="{$rnum}_cust_ship_addr_different"> <b>Shipping Address Different</b></label></div></td>
        </tr>
        <tr class="cust_ship_different">
          <td><label for="{$rnum}_cust_ship_country">Country:</label></td>
          <td><select class="c_input" name="cust_ship_country" id="{$rnum}_cust_ship_country">{$this->drop_opts->getCountryOpts($contact['ship_country'])}</select></td>
        </tr>
        <tr class="cust_ship_different">
          <td><label for="{$rnum}_cust_ship_address">Address:</label></td>
          <td><input type="text" value="{$contact['ship_address']}" name="cust_ship_address" class="c_input " placeholder="Address" id="{$rnum}_cust_ship_address" /></td>
        </tr>
        <tr class="cust_ship_different">
          <td><label for="{$rnum}_cust_ship_city">City:</label></td>
          <td><input type="text" value="{$contact['ship_city']}" name="cust_ship_city" class="c_input" placeholder="City" id="{$rnum}_cust_ship_city"></td>
        </tr>
        <tr class="cust_ship_different">
          <td><label for="{$rnum}_cust_ship_state">State:</label></td>
          <td><select class="c_input" id="{$rnum}_cust_ship_state" name="cust_ship_state">{$this->drop_opts->getStateOpts($contact['ship_state'])}</select></td>
        </tr>
        <tr class="cust_ship_different">
          <td><label for="{$rnum}_cust_ship_zip">Zip:</label></td>
          <td><input type="text" value="{$contact['ship_zip']}" name="cust_ship_zip" class="c_input" placeholder="Zip" id="{$rnum}_cust_ship_zip"></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_billing_type">Billing Type:</label></td>
          <td><select id="{$rnum}_cust_billing_type" name="cust_billing_type" class="c_input">{$this->getAddOpts('cust_billing_type', $contact['billing_type'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_multiplier">Multiplier:</label></td>
          <td><input type="text" value="{$contact['multiplier']}" maxlength="5" name="cust_multiplier" autocomplete="no" class="c_input" placeholder="Multiplier" id="{$rnum}_cust_multiplier"></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_payment_method">Payment Method:</label></td>
          <td><select class="c_input" id="{$rnum}_cust_payment_method" name="cust_payment_method">{$this->getAddOpts('cust_payment_method', $contact['payment_method'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_payment_terms">Payment Terms:</label></td>
          <td><select class="c_input" id="{$rnum}_cust_payment_terms" name="cust_payment_terms">{$this->getAddOpts('cust_payment_terms', $contact['payment_terms'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_fed_id">Federal ID:</label></td>
          <td><input type="text" value="{$contact['custFedID']}" name="cust_fed_id" autocomplete="no" class="c_input" placeholder="Federal ID" id="{$rnum}_cust_fed_id"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_cust_fed_exempt_reason">Federal Exempt Reason:</label></td>
          <td><select class="c_input" id="{$rnum}_cust_fed_exempt_reason" name="cust_fed_exempt_reason">{$this->getAddOpts('cust_fed_exempt_reason', $contact['federal_exempt_reason'])}</select></td>
        </tr>
      </table>
      <!--</editor-fold>-->

      <!--<editor-fold desc="Vendor Information">-->
      <div class="clearfix"><div class="checkbox pull-left"><input id="{$rnum}_add_vendor_check" name="add_vendor_check" class="ignoreSaveAlert add_vendor_check" type="checkbox" value="1" $vend_checked><label for="{$rnum}_add_vendor_check"> <b><u>Vendor</u></b></label></div></div>

      <table class="table table-custom-nb table-indented vendor_data" style="display:none;">
        <colgroup>
          <col width="41%">
          <col width="*">
        </colgroup>
        <tr>
          <td><label for="{$rnum}_vend_established_date">Established Date:</label></td>
          <td><input type="text" value="$vend_est_date" name="vend_established_date" class="c_input" placeholder="Established Date" id="{$rnum}_vend_established_date"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_vend_status">Vendor Status:</label></td>
          <td><select name="vend_status" id="{$rnum}_vend_status" class="c_input">{$this->getAddOpts('vend_status', $contact['vendStatus'])}</select></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><label for="{$rnum}_vend_receive_method">Receive Method:</label></td>
          <td><select name="vend_receive_method" id="{$rnum}_vend_receive_method" class="c_input">{$this->getAddOpts('vend_receive_method', $contact['receive_method'])}</select></td>
        </tr>
        <tr>
          <td colspan="2"><div class="checkbox pull-left"><input id="{$rnum}_vend_receive_addr_different" class="ignoreSaveAlert vend_receive_addr_different" type="checkbox" value="1" $vend_rec_different><label for="{$rnum}_vend_receive_addr_different"> <b>Receiving Address Different</b></label></div></td>
        </tr>
        <tr class="vend_receive_different">
          <td><label for="{$rnum}_vend_receive_country">Country:</label></td>
          <td><select class="c_input" name="vend_receive_country" id="{$rnum}_vend_receive_country">{$this->drop_opts->getCountryOpts($contact['receive_country'])}</select></td>
        </tr>
        <tr class="vend_receive_different">
          <td><label for="{$rnum}_vend_receive_address">Address:</label></td>
          <td><input type="text" value="{$contact['receive_address']}" name="vend_receive_address" class="c_input " placeholder="Address" id="{$rnum}_vend_receive_address" /></td>
        </tr>
        <tr class="vend_receive_different">
          <td><label for="{$rnum}_vend_receive_city">City:</label></td>
          <td><input type="text" value="{$contact['receive_city']}" name="vend_receive_city" class="c_input" placeholder="City" id="{$rnum}_vend_receive_city"></td>
        </tr>
        <tr class="vend_receive_different">
          <td><label for="{$rnum}_vend_receive_state">State:</label></td>
          <td><select class="c_input" id="{$rnum}_vend_receive_state" name="vend_receive_state">{$this->drop_opts->getStateOpts($contact['receive_state'])}</select></td>
        </tr>
        <tr class="vend_receive_different">
          <td><label for="{$rnum}_vend_receive_zip">Zip:</label></td>
          <td><input type="text" value="{$contact['receive_zip']}" name="vend_receive_zip" class="c_input" placeholder="Zip" id="{$rnum}_vend_receive_zip"></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        <tr>
        <tr>
          <td><label for="{$rnum}_vend_payment_terms">Payment Terms:</label></td>
          <td><select class="c_input" id="{$rnum}_vend_payment_terms" name="vend_payment_terms">{$this->getAddOpts('vend_payment_terms', $contact['payment_terms'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_vend_fed_id">Federal ID:</label></td>
          <td><input type="text" value="{$contact['vendFedID']}" name="vend_fed_id" autocomplete="no" class="c_input" placeholder="Federal ID" id="{$rnum}_vend_fed_id"></td>
        </tr>
        <tr>
          <td colspan="2"><div class="checkbox pull-left"><input id="{$rnum}_vend_payment_contact_different" class="ignoreSaveAlert vend_payment_contact_different" type="checkbox" value="1" $vend_payment_different><label for="{$rnum}_vend_payment_contact_different"> <b>Payment Contact Different</b></label></div></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_contact_name">Contact Name:</label></td>
          <td><input type="text" value="{$contact['payment_contact']}" name="vend_payment_contact_name" class="c_input" placeholder="Contact Name" id="{$rnum}_vend_payment_contact_name"></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_contact_country">Country:</label></td>
          <td><select class="c_input" name="vend_payment_contact_country" id="{$rnum}_vend_payment_contact_country">{$this->drop_opts->getCountryOpts($contact['payment_country'])}</select></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_contact_address">Address:</label></td>
          <td><input type="text" value="{$contact['payment_address']}" name="vend_payment_contact_address" class="c_input " placeholder="Address" id="{$rnum}_vend_payment_contact_address" /></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_contact_city">City:</label></td>
          <td><input type="text" value="{$contact['payment_city']}" name="vend_payment_contact_city" class="c_input" placeholder="City" id="{$rnum}_vend_payment_contact_city"></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_contact_state">State:</label></td>
          <td><select class="c_input" id="{$rnum}_vend_payment_contact_state" name="vend_payment_contact_state">{$this->drop_opts->getStateOpts($contact['payment_state'])}</select></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_contact_zip">Zip:</label></td>
          <td><input type="text" value="{$contact['payment_zip']}" name="vend_payment_contact_zip" class="c_input" placeholder="Zip" id="{$rnum}_vend_payment_contact_zip"></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_primary_phone">Primary Phone:</label></td>
          <td><input type="text" value="{$contact['payment_primary_phone']}" name="vend_payment_primary_phone" class="c_input" placeholder="Primary Phone" id="{$rnum}_vend_payment_primary_phone"></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_secondary_phone">Secondary Phone:</label></td>
          <td><input type="text" value="{$contact['payment_secondary_phone']}" name="vend_payment_secondary_phone" class="c_input" placeholder="Secondary Phone" id="{$rnum}_vend_payment_secondary_phone"></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_other_phone">Other Phone:</label></td>
          <td><input type="text" value="{$contact['payment_other_phone']}" name="vend_payment_other_phone" class="c_input" placeholder="Other Phone" id="{$rnum}_vend_payment_other_phone"></td>
        </tr>
        <tr class="vend_payment_contact">
          <td><label for="{$rnum}_vend_payment_fax">Fax:</label></td>
          <td><input type="text" value="{$contact['payment_fax']}" name="vend_payment_fax" class="c_input" placeholder="Fax" id="{$rnum}_vend_payment_fax"></td>
        </tr>
      </table>
      <!--</editor-fold>-->

      <!--<editor-fold desc="Employee Information">-->
      <div class="clearfix employee_info_checkbox"><div class="checkbox pull-left"><input id="{$rnum}_add_employee_check" name="add_employee_check" class="ignoreSaveAlert add_employee_check" type="checkbox" value="1" $emp_checked><label for="{$rnum}_add_employee_check"> <b><u>Employee</u></b></label></div></div>

      <table class="table table-custom-nb table-indented employee_data" style="display:none;">
        <colgroup>
          <col width="41%">
          <col width="*">
        </colgroup>
        <tr>
          <td><label for="{$rnum}_emp_hire_date">Hire Date:</label></td>
          <td><input type="text" value="$hire_date" name="emp_hire_date" class="c_input" placeholder="Hire Date" id="{$rnum}_emp_hire_date"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_languages">Languages:</label></td>
          <td><input type="text" value="{$contact['languages']}" name="emp_languages" class="c_input" placeholder="Languages" id="{$rnum}_emp_languages"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_timezone">Timezone:</label></td>
          <td><select name="emp_timezone" id="{$rnum}_emp_timezone" class="c_input">{$this->drop_opts->getTimezoneOpts($contact['timezone'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_shift">Shift:</label></td>
          <td><select name="emp_shift" id="{$rnum}_emp_shift" class="c_input">{$this->getAddOpts('emp_shift', $contact['shift'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_facility">Facility:</label></td>
          <td><select name="emp_facility" id="{$rnum}_emp_facility" class="c_input">{$this->getAddOpts('emp_facility', $contact['facility'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_department">Department:</label></td>
          <td><select name="emp_department" id="{$rnum}_emp_department" class="c_input">$available_depts</select></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_status">Employee Status:</label></td>
          <td><select name="emp_status" id="{$rnum}_emp_status" class="c_input">{$this->getAddOpts('emp_status', $contact['employee_status'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_user_access">User Access:</label></td>
          <td><select name="emp_user_access" id="{$rnum}_emp_user_access" class="c_input">$available_perms</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_username">Username:</label></td>
          <td><input type="text" value="{$contact['username']}" name="emp_username" autocomplete="no" class="c_input" placeholder="Login Username" id="{$rnum}_emp_username"></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_generate_pin_pw">Generate & Email PIN/PW:</label></td>
          <td>
            <select name="generate_pin_pw" id="{$rnum}_generate_pin_pw" class="c_input generate_pin_pw">
              <option value="1" {$generate_pin['yes']}>Yes</option>
              <option value="0" {$generate_pin['no']}>No</option>
            </select>
          </td>
        </tr>
        <tr class="generate_pin_pw_field">
          <td><label for="{$rnum}_emp_pin">PIN:</label></td>
          <td><input type="text" value="{$contact['pin']}" name="emp_pin" autocomplete="no" class="c_input" placeholder="PIN Code" id="{$rnum}_emp_pin"></td>
        </tr>
        <tr class="generate_pin_pw_field">
          <td><label for="{$rnum}_emp_password">Password:</label></td>
          <td><input type="text" value="" name="emp_password" autocomplete="no" class="c_input" placeholder="Password" id="{$rnum}_emp_password"></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_pay_schedule">Pay Schedule:</label></td>
          <td><select name="emp_pay_schedule" id="{$rnum}_emp_pay_schedule" class="c_input">{$this->getAddOpts('emp_pay_schedule', $contact['pay_schedule'])}</select></td>
        </tr>
        <tr>
          <td><label for="{$rnum}_emp_federal_id">Federal ID:</label></td>
          <td><input type="text" value="{$contact['federal_id']}" name="emp_federal_id" autocomplete="no" class="c_input" placeholder="Federal ID" id="{$rnum}_emp_federal_id"></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2"><div class="checkbox pull-left"><input id="{$rnum}_emp_personal_info_check" class="ignoreSaveAlert emp_personal_info_check" type="checkbox" value="1" $emp_personal_different><label for="{$rnum}_emp_personal_info_check"> <b>Personal Information</b></label></div></td>
        </tr>
        <tr class="emp_personal_different">
          <td><label for="{$rnum}_emp_personal_country">Country:</label></td>
          <td><select class="c_input" name="emp_personal_country" id="{$rnum}_emp_personal_country">{$this->drop_opts->getCountryOpts($contact['personal_country'])}</select></td>
        </tr>
        <tr class="emp_personal_different">
          <td><label for="{$rnum}_emp_personal_address">Address:</label></td>
          <td><input type="text" value="{$contact['personal_address']}" name="emp_personal_address" class="c_input " placeholder="Address" id="{$rnum}_emp_personal_address" /></td>
        </tr>
        <tr class="emp_personal_different">
          <td><label for="{$rnum}_emp_personal_city">City:</label></td>
          <td><input type="text" value="{$contact['personal_city']}" name="emp_personal_city" class="c_input" placeholder="City" id="{$rnum}_emp_personal_city"></td>
        </tr>
        <tr class="emp_personal_different">
          <td><label for="{$rnum}_emp_personal_state">State:</label></td>
          <td><select class="c_input" id="{$rnum}_emp_personal_state" name="emp_personal_state">{$this->drop_opts->getStateOpts($contact['personal_state'])}</select></td>
        </tr>
        <tr class="emp_personal_different">
          <td><label for="{$rnum}_emp_personal_zip">Zip:</label></td>
          <td><input type="text" value="{$contact['personal_zip']}" name="emp_personal_zip" class="c_input" placeholder="Zip" id="{$rnum}_emp_personal_zip"></td>
        </tr>
        <tr class="emp_personal_different">
          <td><label for="{$rnum}_emp_personal_email">Email:</label></td>
          <td><input type="text" value="{$contact['personal_email']}" name="emp_personal_email" class="c_input" placeholder="Email" id="{$rnum}_emp_personal_email"></td>
        </tr>
        <tr class="emp_personal_different">
          <td><label for="{$rnum}_emp_personal_phone">Phone:</label></td>
          <td><input type="text" value="{$contact['personal_phone']}" name="emp_personal_phone" class="c_input" placeholder="Phone" id="{$rnum}_emp_personal_phone"></td>
        </tr>
        <tr class="emp_personal_different">
          <td><label for="{$rnum}_emp_personal_birthday">Birthday:</label></td>
          <td><input type="text" value="$birthday" name="emp_personal_birthday" class="c_input" placeholder="Birthday" id="{$rnum}_emp_personal_birthday"></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        <tr>
        <tr>
          <td colspan="2"><div class="checkbox pull-left"><input id="{$rnum}_emp_emergency_contact_info_check" class="ignoreSaveAlert emp_emergency_contact_info_check" type="checkbox" value="1" $emp_emergency_different><label for="{$rnum}_emp_emergency_contact_info_check"> <b>Emergency Contact Information</b></label></div></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_contact_name">Contact Name:</label></td>
          <td><input type="text" value="{$contact['emergency_name']}" name="emp_emergency_contact_name" class="c_input" placeholder="Contact Name" id="{$rnum}_emp_emergency_contact_name"></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_relationship">Relationship:</label></td>
          <td><input type="text" value="{$contact['emergency_relationship']}" name="emp_emergency_relationship" class="c_input" placeholder="Relationship" id="{$rnum}_emp_emergency_relationship"></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_contact_country">Country:</label></td>
          <td><select class="c_input" name="emp_emergency_contact_country" id="{$rnum}_emp_emergency_contact_country">{$this->drop_opts->getCountryOpts($contact['emergency_country'])}</select></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_contact_address">Address:</label></td>
          <td><input type="text" value="{$contact['emergency_address']}" name="emp_emergency_contact_address" class="c_input " placeholder="Address" id="{$rnum}_emp_emergency_contact_address" /></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_contact_city">City:</label></td>
          <td><input type="text" value="{$contact['emergency_city']}" name="emp_emergency_contact_city" class="c_input" placeholder="City" id="{$rnum}_emp_emergency_contact_city"></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_contact_state">State:</label></td>
          <td><select class="c_input" id="{$rnum}_emp_emergency_contact_state" name="emp_emergency_contact_state">{$this->drop_opts->getStateOpts($contact['emergency_state'])}</select></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_contact_zip">Zip:</label></td>
          <td><input type="text" value="{$contact['emergency_zip']}" name="emp_emergency_contact_zip" class="c_input" placeholder="Zip" id="{$rnum}_emp_emergency_contact_zip"></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_primary_phone">Primary Phone:</label></td>
          <td><input type="text" value="{$contact['emergency_pri_phone']}" name="emp_emergency_primary_phone" class="c_input" placeholder="Primary Phone" id="{$rnum}_emp_emergency_primary_phone"></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_secondary_phone">Secondary Phone:</label></td>
          <td><input type="text" value="{$contact['emergency_secondary_phone']}" name="emp_emergency_secondary_phone" class="c_input" placeholder="Secondary Phone" id="{$rnum}_emp_emergency_secondary_phone"></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_other_phone">Other Phone:</label></td>
          <td><input type="text" value="{$contact['emergency_other_phone']}" name="emp_emergency_other_phone" class="c_input" placeholder="Other Phone" id="{$rnum}_emp_emergency_other_phone"></td>
        </tr>
        <tr class="emp_emergency_contact">
          <td><label for="{$rnum}_emp_emergency_email">Email:</label></td>
          <td><input type="text" value="{$contact['emergency_email']}" name="emp_emergency_email" class="c_input" placeholder="Email" id="{$rnum}_emp_emergency_email"></td>
        </tr>
      </table>
      <!--</editor-fold>-->
HEREDOC;

  }
}