<?php
require '../includes/header_start.php';
require '../includes/header_end.php';

if($_REQUEST['action'] === 'sent') {
    $fname = trim($dbconn->real_escape_string($_REQUEST['first_name']));
    $lname = trim($dbconn->real_escape_string($_REQUEST['last_name']));
    $pri_phone = trim($dbconn->real_escape_string($_REQUEST['pri_phone']));
    $company = trim($dbconn->real_escape_string($_REQUEST['company']));
    $alias = trim($dbconn->real_escape_string($_REQUEST['alias']));
    $add1 = trim($dbconn->real_escape_string($_REQUEST['address_1']));
    $add2 = trim($dbconn->real_escape_string($_REQUEST['address_2']));
    $city = trim($dbconn->real_escape_string($_REQUEST['city']));
    $state = trim($dbconn->real_escape_string($_REQUEST['state']));
    $zip = trim($dbconn->real_escape_string($_REQUEST['zip']));
    $account_type = trim($dbconn->real_escape_string($_REQUEST['account_type']));

    $error = null;

    if((bool)$_REQUEST['search_toggle'] !== true) { // if we're not searching
        function checkIt($var, $common_name, $len) {
            if($len > 1)
                $plu = "s";
            else
                $plu = null;

            if(strlen($var) < $len)
                return "$common_name must be at least $len character$plu long.<br>";
            else
                return null;
        }

        $error .= checkIt($fname, "First Name", 1);
        $error .= checkIt($lname, "Last Name", 1);
        $error .= checkIt($pri_phone, "Primary Phone Number", 14);
        $error .= checkIt($add1, "Address 1", 5);
        $error .= checkIt($city, "City", 3);
        $error .= checkIt($state, "State", 2);
        $error .= checkIt($zip, "Zip Code", 5);
        $error .= checkIt($account_type, "Account Type", 5);

        if(strlen($error) === 0) {
            if($dbconn->query("INSERT INTO contacts (first_name, last_name, pri_phone, company, address_1, address_2, city, state, zip, alias, account_type) VALUES ('$fname', '$lname', '$pri_phone', '$company', '$add1','$add2', '$city', '$state', '$zip', '$alias', '$account_type')")) {
                echo Toast("success", "Contact created successfully.", "Added Contact Record");
                $_REQUEST = array();
            } else {
                dbLogSQLErr($dbconn);
            }
        } else {
            echo $error;
        }
    } else { // otherwise we are searching
        $searchby = null;

        if(strlen($fname) > 0) {
            $searchby .= "first_name LIKE '%$fname%' OR ";
        }

        if(strlen($lname) > 0) {
            $searchby .= "last_name LIKE '%$lname%' OR ";
        }

        if(strlen($pri_phone) > 0) {
            $searchby .= "pri_phone LIKE '%$pri_phone%' OR ";
        }

        if(strlen($company) > 0) {
            $searchby .= "company LIKE '%$company%' OR ";
        }

        if(strlen($alias) > 0) {
            $searchby .= "alias LIKE '%$alias%' OR ";
        }

        if(strlen($add1) > 0) {
            $searchby .= "address_1 LIKE '%$add1%' OR ";
        }

        if(strlen($add2) > 0) {
            $searchby .= "address_2 LIKE '%$add1%' OR ";
        }

        if(strlen($city) > 0) {
            $searchby .= "city LIKE '%$city%' OR ";
        }

        if(strlen($zip) > 0) {
            $searchby .= "zip LIKE '%$zip%' OR ";
        }

        $searchby = rtrim($searchby, " OR ");

        $query = $dbconn->query("SELECT * FROM contacts WHERE $searchby");
    }
}
?>

    <link href="/assets/plugins/bootstrap-tagsinput/css/bootstrap-tagsinput.css" rel="stylesheet"/>
    <link href="/assets/plugins/multiselect/css/multi-select.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>

    <!-- Datatable -->
    <link href="/assets/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <!--<div class="btn-group pull-right m-t-15">
                <button type="button" class="btn btn-custom dropdown-toggle waves-effect waves-light"
                        data-toggle="dropdown" aria-expanded="false">Settings <span class="m-l-5"><i
                            class="fa fa-cog"></i></span></button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Separated link</a>
                </div>

            </div>-->
            <h4 class="page-title">Contact Management</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="card card-block">
                <h4 class="card-title">Adding a Contact Record</h4>
                <p class="card-text">Adding a contact record in here will create the initial point of contact. Once created,
                    you will be able to go in and add additional points of contact as well as other information as desired. This
                    does not automatically enroll anyone into the dashboard system.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <form action="contact_management.php?action=sent" method="post">
            <div class="col-md-6">
                <div class="card-box matchmaker">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 id="contact_header">Search Contacts</h2>
                            <p class="text-muted" id="optional_text">All fields optional.</p>
                        </div>

                        <div class="col-md-6 text-md-right">
                            <fieldset class="form-group">
                                <label for="search_toggle">Add </label>
                                <input type="checkbox" checked data-plugin="switchery" data-color="#039cfd" id="search_toggle" name="search_toggle" value="true" />
                                <label for="search_toggle"> Search</label>
                            </fieldset>
                        </div>
                    </div>

                    <div class="row">
                        <div class="row">
                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input class="form-control" type="text" placeholder="First Name" id="first_name" name="first_name" tabindex="1" value="<?php echo $_REQUEST['first_name']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input class="form-control" type="text" placeholder="Last Name" id="last_name" name="last_name" tabindex="2" value="<?php echo $_REQUEST['last_name']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="pri_phone">Primary Phone</label>
                                    <input class="form-control" type="text" autofocus placeholder="Primary Phone Number" id="pri_phone" name="pri_phone" tabindex="3" value="<?php echo $_REQUEST['pri_phone']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="company" id="label_for_company">Company</label>
                                    <input class="form-control" type="text" placeholder="Company" id="company" name="company" tabindex="4" value="<?php echo $_REQUEST['company']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="contractor" id="label_for_alias">Preferred Name</label>
                                    <input class="form-control" type="text" placeholder="Preferred Name" id="alias" name="alias" tabindex="5" value="<?php echo $_REQUEST['alias']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="account_type" id="label_for_account_type" tabindex="6">Account Type <small class="text-muted">(Excluded from search)</small></label>
                                    <select class="form-control" name="account_type" id="account_type">
                                        <option value="Retail" selected>Retail</option>
                                        <option value="Wholesale">Wholesale</option>
                                        <option value="Distributor">Distributor</option>
                                        <option value="Vendor">Vendor</option>
                                        <option value="Contractor">Contractor</option>
                                    </select>
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <fieldset class="form-group">
                                    <label for="address_1">Street Address</label>
                                    <input class="form-control" type="text" placeholder="Street Address" id="address_1" name="address_1" tabindex="7" value="<?php echo $_REQUEST['address_1']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="city">City</label>
                                    <input class="form-control" type="text" placeholder="City" id="city" name="city" tabindex="9" value="<?php echo $_REQUEST['city']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="state" id="label_for_state">State <small class="text-muted">(Excluded from search)</small></label>
                                    <select class="form-control" id="state" name="state" tabindex="10">
                                        <option value="AL" <?php if($_REQUEST['state'] === "AL") echo "selected"; ?>>Alabama</option>
                                        <option value="AK" <?php if($_REQUEST['state'] === "AK") echo "selected"; ?>>Alaska</option>
                                        <option value="AZ" <?php if($_REQUEST['state'] === "AZ") echo "selected"; ?>>Arizona</option>
                                        <option value="AR" <?php if($_REQUEST['state'] === "AR") echo "selected"; ?>>Arkansas</option>
                                        <option value="CA" <?php if($_REQUEST['state'] === "CA") echo "selected"; ?>>California</option>
                                        <option value="CO" <?php if($_REQUEST['state'] === "CO") echo "selected"; ?>>Colorado</option>
                                        <option value="CT" <?php if($_REQUEST['state'] === "CT") echo "selected"; ?>>Connecticut</option>
                                        <option value="DE" <?php if($_REQUEST['state'] === "DE") echo "selected"; ?>>Delaware</option>
                                        <option value="FL" <?php if($_REQUEST['state'] === "FL") echo "selected"; ?>>Florida</option>
                                        <option value="GA" <?php if($_REQUEST['state'] === "GA") echo "selected"; ?>>Georgia</option>
                                        <option value="HI" <?php if($_REQUEST['state'] === "HI") echo "selected"; ?>>Hawaii</option>
                                        <option value="ID" <?php if($_REQUEST['state'] === "ID") echo "selected"; ?>>Idaho</option>
                                        <option value="IL" <?php if($_REQUEST['state'] === "IL") echo "selected"; ?>>Illinois</option>
                                        <option value="IN" <?php if($_REQUEST['state'] === "IN") echo "selected"; ?>>Indiana</option>
                                        <option value="IA" <?php if($_REQUEST['state'] === "IA") echo "selected"; ?>>Iowa</option>
                                        <option value="KS" <?php if($_REQUEST['state'] === "KS") echo "selected"; ?>>Kansas</option>
                                        <option value="KY" <?php if($_REQUEST['state'] === "KY") echo "selected"; ?>>Kentucky</option>
                                        <option value="LA" <?php if($_REQUEST['state'] === "LA") echo "selected"; ?>>Louisiana</option>
                                        <option value="ME" <?php if($_REQUEST['state'] === "ME") echo "selected"; ?>>Maine</option>
                                        <option value="MD" <?php if($_REQUEST['state'] === "MD") echo "selected"; ?>>Maryland</option>
                                        <option value="MA" <?php if($_REQUEST['state'] === "MA") echo "selected"; ?>>Massachusetts</option>
                                        <option value="MI" <?php if($_REQUEST['state'] === "MI") echo "selected"; ?>>Michigan</option>
                                        <option value="MN" <?php if($_REQUEST['state'] === "MN") echo "selected"; ?>>Minnesota</option>
                                        <option value="MS" <?php if($_REQUEST['state'] === "MS") echo "selected"; ?>>Mississippi</option>
                                        <option value="MO" <?php if($_REQUEST['state'] === "MO") echo "selected"; ?>>Missouri</option>
                                        <option value="MT" <?php if($_REQUEST['state'] === "MT") echo "selected"; ?>>Montana</option>
                                        <option value="NE" <?php if($_REQUEST['state'] === "NE") echo "selected"; ?>>Nebraska</option>
                                        <option value="NV" <?php if($_REQUEST['state'] === "NV") echo "selected"; ?>>Nevada</option>
                                        <option value="NH" <?php if($_REQUEST['state'] === "NH") echo "selected"; ?>>New Hampshire</option>
                                        <option value="NJ" <?php if($_REQUEST['state'] === "NJ") echo "selected"; ?>>New Jersey</option>
                                        <option value="NM" <?php if($_REQUEST['state'] === "NM") echo "selected"; ?>>New Mexico</option>
                                        <option value="NY" <?php if($_REQUEST['state'] === "NY") echo "selected"; ?>>New York</option>
                                        <option value="NC" <?php if($_REQUEST['state'] === "NC") { echo "selected"; } elseif($_REQUEST['state'] === null || $_REQUEST['state'] === '') { echo "selected"; } ?>>North Carolina</option>
                                        <option value="ND" <?php if($_REQUEST['state'] === "ND") echo "selected"; ?>>North Dakota</option>
                                        <option value="OH" <?php if($_REQUEST['state'] === "OH") echo "selected"; ?>>Ohio</option>
                                        <option value="OK" <?php if($_REQUEST['state'] === "OK") echo "selected"; ?>>Oklahoma</option>
                                        <option value="OR" <?php if($_REQUEST['state'] === "OR") echo "selected"; ?>>Oregon</option>
                                        <option value="PA" <?php if($_REQUEST['state'] === "PA") echo "selected"; ?>>Pennsylvania</option>
                                        <option value="RI" <?php if($_REQUEST['state'] === "RI") echo "selected"; ?>>Rhode Island</option>
                                        <option value="SC" <?php if($_REQUEST['state'] === "SC") echo "selected"; ?>>South Carolina</option>
                                        <option value="SD" <?php if($_REQUEST['state'] === "SD") echo "selected"; ?>>South Dakota</option>
                                        <option value="TN" <?php if($_REQUEST['state'] === "TN") echo "selected"; ?>>Tennessee</option>
                                        <option value="TX" <?php if($_REQUEST['state'] === "TX") echo "selected"; ?>>Texas</option>
                                        <option value="UT" <?php if($_REQUEST['state'] === "UT") echo "selected"; ?>>Utah</option>
                                        <option value="VT" <?php if($_REQUEST['state'] === "VT") echo "selected"; ?>>Vermont</option>
                                        <option value="VA" <?php if($_REQUEST['state'] === "VA") echo "selected"; ?>>Virginia</option>
                                        <option value="WA" <?php if($_REQUEST['state'] === "WA") echo "selected"; ?>>Washington</option>
                                        <option value="WV" <?php if($_REQUEST['state'] === "WV") echo "selected"; ?>>West Virginia</option>
                                        <option value="WI" <?php if($_REQUEST['state'] === "WI") echo "selected"; ?>>Wisconsin</option>
                                        <option value="WY" <?php if($_REQUEST['state'] === "WY") echo "selected"; ?>>Wyoming</option>
                                    </select>
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="zip">Zip Code</label>
                                    <input class="form-control" type="text" placeholder="Zip Code" id="zip" name="zip" data-mask="00000-0000" tabindex="11" value="<?php echo $_REQUEST['zip']; ?>">
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <fieldset class="form-group">
                                    <label for="billing_address">Billing Address</label>
                                    <input class="form-control" type="text" placeholder="Billing Address" id="billing_address" name="billing_address" tabindex="7" value="<?php echo $_REQUEST['billing_address']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="billing_city">Billing City</label>
                                    <input class="form-control" type="text" placeholder="Billing City" id="billing_city" name="billing_city" tabindex="9" value="<?php echo $_REQUEST['billing_city']; ?>">
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="billing_state" id="label_for_state">Billing State <small class="text-muted">(Excluded from search)</small></label>
                                    <select class="form-control" id="billing_state" name="billing_state" tabindex="10">
                                        <option value="AL" <?php if($_REQUEST['billing_state'] === "AL") echo "selected"; ?>>Alabama</option>
                                        <option value="AK" <?php if($_REQUEST['billing_state'] === "AK") echo "selected"; ?>>Alaska</option>
                                        <option value="AZ" <?php if($_REQUEST['billing_state'] === "AZ") echo "selected"; ?>>Arizona</option>
                                        <option value="AR" <?php if($_REQUEST['billing_state'] === "AR") echo "selected"; ?>>Arkansas</option>
                                        <option value="CA" <?php if($_REQUEST['billing_state'] === "CA") echo "selected"; ?>>California</option>
                                        <option value="CO" <?php if($_REQUEST['billing_state'] === "CO") echo "selected"; ?>>Colorado</option>
                                        <option value="CT" <?php if($_REQUEST['billing_state'] === "CT") echo "selected"; ?>>Connecticut</option>
                                        <option value="DE" <?php if($_REQUEST['billing_state'] === "DE") echo "selected"; ?>>Delaware</option>
                                        <option value="FL" <?php if($_REQUEST['billing_state'] === "FL") echo "selected"; ?>>Florida</option>
                                        <option value="GA" <?php if($_REQUEST['billing_state'] === "GA") echo "selected"; ?>>Georgia</option>
                                        <option value="HI" <?php if($_REQUEST['billing_state'] === "HI") echo "selected"; ?>>Hawaii</option>
                                        <option value="ID" <?php if($_REQUEST['billing_state'] === "ID") echo "selected"; ?>>Idaho</option>
                                        <option value="IL" <?php if($_REQUEST['billing_state'] === "IL") echo "selected"; ?>>Illinois</option>
                                        <option value="IN" <?php if($_REQUEST['billing_state'] === "IN") echo "selected"; ?>>Indiana</option>
                                        <option value="IA" <?php if($_REQUEST['billing_state'] === "IA") echo "selected"; ?>>Iowa</option>
                                        <option value="KS" <?php if($_REQUEST['billing_state'] === "KS") echo "selected"; ?>>Kansas</option>
                                        <option value="KY" <?php if($_REQUEST['billing_state'] === "KY") echo "selected"; ?>>Kentucky</option>
                                        <option value="LA" <?php if($_REQUEST['billing_state'] === "LA") echo "selected"; ?>>Louisiana</option>
                                        <option value="ME" <?php if($_REQUEST['billing_state'] === "ME") echo "selected"; ?>>Maine</option>
                                        <option value="MD" <?php if($_REQUEST['billing_state'] === "MD") echo "selected"; ?>>Maryland</option>
                                        <option value="MA" <?php if($_REQUEST['billing_state'] === "MA") echo "selected"; ?>>Massachusetts</option>
                                        <option value="MI" <?php if($_REQUEST['billing_state'] === "MI") echo "selected"; ?>>Michigan</option>
                                        <option value="MN" <?php if($_REQUEST['billing_state'] === "MN") echo "selected"; ?>>Minnesota</option>
                                        <option value="MS" <?php if($_REQUEST['billing_state'] === "MS") echo "selected"; ?>>Mississippi</option>
                                        <option value="MO" <?php if($_REQUEST['billing_state'] === "MO") echo "selected"; ?>>Missouri</option>
                                        <option value="MT" <?php if($_REQUEST['billing_state'] === "MT") echo "selected"; ?>>Montana</option>
                                        <option value="NE" <?php if($_REQUEST['billing_state'] === "NE") echo "selected"; ?>>Nebraska</option>
                                        <option value="NV" <?php if($_REQUEST['billing_state'] === "NV") echo "selected"; ?>>Nevada</option>
                                        <option value="NH" <?php if($_REQUEST['billing_state'] === "NH") echo "selected"; ?>>New Hampshire</option>
                                        <option value="NJ" <?php if($_REQUEST['billing_state'] === "NJ") echo "selected"; ?>>New Jersey</option>
                                        <option value="NM" <?php if($_REQUEST['billing_state'] === "NM") echo "selected"; ?>>New Mexico</option>
                                        <option value="NY" <?php if($_REQUEST['billing_state'] === "NY") echo "selected"; ?>>New York</option>
                                        <option value="NC" <?php if($_REQUEST['billing_state'] === "NC") { echo "selected"; } elseif($_REQUEST['billing_state'] === null || $_REQUEST['billing_state'] === '') { echo "selected"; } ?>>North Carolina</option>
                                        <option value="ND" <?php if($_REQUEST['billing_state'] === "ND") echo "selected"; ?>>North Dakota</option>
                                        <option value="OH" <?php if($_REQUEST['billing_state'] === "OH") echo "selected"; ?>>Ohio</option>
                                        <option value="OK" <?php if($_REQUEST['billing_state'] === "OK") echo "selected"; ?>>Oklahoma</option>
                                        <option value="OR" <?php if($_REQUEST['billing_state'] === "OR") echo "selected"; ?>>Oregon</option>
                                        <option value="PA" <?php if($_REQUEST['billing_state'] === "PA") echo "selected"; ?>>Pennsylvania</option>
                                        <option value="RI" <?php if($_REQUEST['billing_state'] === "RI") echo "selected"; ?>>Rhode Island</option>
                                        <option value="SC" <?php if($_REQUEST['billing_state'] === "SC") echo "selected"; ?>>South Carolina</option>
                                        <option value="SD" <?php if($_REQUEST['billing_state'] === "SD") echo "selected"; ?>>South Dakota</option>
                                        <option value="TN" <?php if($_REQUEST['billing_state'] === "TN") echo "selected"; ?>>Tennessee</option>
                                        <option value="TX" <?php if($_REQUEST['billing_state'] === "TX") echo "selected"; ?>>Texas</option>
                                        <option value="UT" <?php if($_REQUEST['billing_state'] === "UT") echo "selected"; ?>>Utah</option>
                                        <option value="VT" <?php if($_REQUEST['billing_state'] === "VT") echo "selected"; ?>>Vermont</option>
                                        <option value="VA" <?php if($_REQUEST['billing_state'] === "VA") echo "selected"; ?>>Virginia</option>
                                        <option value="WA" <?php if($_REQUEST['billing_state'] === "WA") echo "selected"; ?>>Washington</option>
                                        <option value="WV" <?php if($_REQUEST['billing_state'] === "WV") echo "selected"; ?>>West Virginia</option>
                                        <option value="WI" <?php if($_REQUEST['billing_state'] === "WI") echo "selected"; ?>>Wisconsin</option>
                                        <option value="WY" <?php if($_REQUEST['billing_state'] === "WY") echo "selected"; ?>>Wyoming</option>
                                    </select>
                                </fieldset>
                            </div>

                            <div class="col-md-4">
                                <fieldset class="form-group">
                                    <label for="billing_zip">Billing Zip Code</label>
                                    <input class="form-control" type="text" placeholder="Billing Zip Code" id="billing_zip" name="billing_zip" data-mask="00000-0000" tabindex="11" value="<?php echo $_REQUEST['billing_zip']; ?>">
                                </fieldset>
                            </div>
                        </div>

                        <div class="col-md-12 text-md-center">
                            <button type="submit" class="btn btn-primary waves-effect" id="go_button" tabindex="12">Search</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card-box matchmaker">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 id="search_header">Search Results</h2>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Phone</th>
                                    <th>Billing Address</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if((bool)$_REQUEST['search_toggle']) { // since we're searching and the form's submitted...
                                    if(strlen($searchby) > 0) { // we've built the query to run and stuff
                                        while($results = $query->fetch_assoc()) {
                                            echo <<<HEREDOC
<tr onclick="document.location.href='view_contact.php?id={$results['id']}'" class="cursor-hand">
    <td>{$results['account_type']}</td>
    <td>{$results['first_name']} {$results['last_name']}</td>
    <td>{$results['company']}</td>
    <td>{$results['pri_phone']}</td>
    <td>{$results['address_1']}</td>
</tr>
HEREDOC;

                                            $last_id = $results['id'];
                                        }
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script src="/includes/js/jquery.mask.min.js" type="text/javascript"></script>
    <script src="/includes/js/jquery.matchHeight-min.js" type="text/javascript"></script>

    <!-- Required datatable js -->
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>

    <script type="text/javascript">
        var product_type = $("#product_type");
        var del_code = $("#del_code");

        var search = $("#search_toggle");
        var header = $("#contact_header");
        var go_btn = $("#go_button");

        $(function() {
            //$(".matchmaker").matchHeight(); // setup the matchmaker (height protoccol)
            $("#datatable").dataTable(); // create the wonderful datatable

            // product type code (color background
            product_type.change(function() {
                if(product_type.val() === "Sample" || product_type.val() === "Warranty") {
                    del_code.val("Red").css("background", "rgba(255,0,0,.8)");
                } else {
                    del_code.val("Green").css("background", "rgba(0,255,0,.8)");
                }
            });

            // Search toggle code
            search.change(function () {
                if(search.is(":checked")) {
                    header.html("Search Contacts");
                    go_btn.html("Search").removeClass("btn-secondary").addClass("btn-primary");
                    $("#label_for_company").html('Company');
                    $("#label_for_alias").html('Preferred Name');
                    $("#label_for_state").html('State <small class="text-muted">(Excluded from search)</small>');
                    $("#label_for_account_type").html('Account Type <small class="text-muted">(Excluded from search)</small>');
                    $("#optional_text").html("All fields optional.");
                    $("#pri_phone").unmask(); // remove phone number mask
                } else {
                    header.html("Add New Contact");
                    go_btn.html("Create").removeClass("btn-primary").addClass("btn-secondary");
                    $("#label_for_company").html('Company <small class="text-muted">(Optional)</small>');
                    $("#label_for_alias").html('Preferred Name <small class="text-muted">(Optional)</small>');
                    $("#label_for_account_type").html('Account Type');
                    $("#label_for_state").html('State');
                    $("#optional_text").html("Some fields optional.");
                    $("#pri_phone").mask('(999) 999-9999'); // implement phone mask
                }
            });
            <?php
            if($query->num_rows === 0)
                echo '$("#search_toggle").trigger("click");';
            elseif($query->num_rows === 1)
                echo "window.location.replace('view_contact.php?id=$last_id');";
            ?>
        });
    </script>

<?php
require '../includes/footer_start.php';
require '../includes/footer_end.php';
?>