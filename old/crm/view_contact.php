<?php
require '../includes/header_start.php';
require '../includes/header_end.php';

$id = $dbconn->real_escape_string($_REQUEST['id']);

$query = $dbconn->query("SELECT * FROM contacts WHERE id = '$id'");

if($query->num_rows === 1) {
    $result = $query->fetch_assoc();
} else {
    echo '<script type="text/javascript">window.location.replace("contact_management.php");</script>';
}

if($_REQUEST['action'] === 'update') {
    $fname = sanitizeInput($_REQUEST['first_name'], $dbconn);
    $lname = sanitizeInput($_REQUEST['last_name'], $dbconn);
    $pri_phone = sanitizeInput($_REQUEST['pri_phone'], $dbconn);
    $company = sanitizeInput($_REQUEST['company'], $dbconn);
    $alias = sanitizeInput($_REQUEST['alias'], $dbconn);
    $add1 = sanitizeInput($_REQUEST['address_1'], $dbconn);
    $city = sanitizeInput($_REQUEST['city'], $dbconn);
    $state = sanitizeInput($_REQUEST['state'], $dbconn);
    $zip = sanitizeInput($_REQUEST['zip'], $dbconn);
    $directions = sanitizeInput($_REQUEST['directions'], $dbconn);
    $account_type = sanitizeInput($_REQUEST['account_type'], $dbconn);
    $email = sanitizeInput($_REQUEST['email'], $dbconn);

    $error = null;

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
    $error .= checkIt($email, "Email Address", 6);

    if(strlen($error) === 0) {
        if($dbconn->query("UPDATE contacts SET first_name = '$fname', last_name = '$lname', pri_phone = '$pri_phone', company = '$company', address_1 = '$add1', city = '$city', state = '$state', zip = '$zip', alias = '$alias', account_type = '$account_type', directions = '$directions', email = '$email' WHERE id = $id")) {
            echo displayToast("success", "Contact updated successfully.", "Updated Record Successfully");
            $_REQUEST = array();
        } else {
            dbLogSQLErr($dbconn);
        }
    } else {
        echo displayToast("error", "$error", "Error");
    }
}
?>

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
        <h4 class="page-title">Viewing Contact - <?php echo $result['first_name'] . " " . $result['last_name']; ?></h4>
    </div>
</div>

<div class="row">
    <form action="view_contact.php?action=update&id=<?php echo $_GET['id']; ?>" method="post">
        <div class="col-md-6">
            <div class="card-box">
                <div class="row">
                    <div class="col-md-12">
                        <h2 id="contact_header">Contact Information</h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="first_name">First Name</label>
                            <input class="form-control" type="text" placeholder="First Name" id="first_name" name="first_name" tabindex="1" value="<?php echo $result['first_name']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="last_name">Last Name</label>
                            <input class="form-control" type="text" placeholder="Last Name" id="last_name" name="last_name" tabindex="2" value="<?php echo $result['last_name']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="pri_phone">Primary Phone</label>
                            <input class="form-control" type="text" autofocus placeholder="Primary Phone Number" id="pri_phone" name="pri_phone" tabindex="3" value="<?php echo $result['pri_phone']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="company" id="label_for_company">Company</label>
                            <input class="form-control" type="text" placeholder="Company" id="company" name="company" tabindex="4" value="<?php echo $result['company']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="contractor" id="label_for_alias">Preferred Name</label>
                            <input class="form-control" type="text" placeholder="Preferred Name" id="alias" name="alias" tabindex="5" value="<?php echo $result['alias']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="account_type" tabindex="6">Account Type</label>
                            <select class="form-control" name="account_type" id="account_type">
                                <option value="Retail" <?php if($result['account_type'] === "Retail") echo "selected"; ?>>Retail</option>
                                <option value="Wholesale" <?php if($result['account_type'] === "Wholesale") echo "selected"; ?>>Wholesale</option>
                                <option value="Distributor" <?php if($result['account_type'] === "Distributor") echo "selected"; ?>>Distributor</option>
                                <option value="Vendor" <?php if($result['account_type'] === "Vendor") echo "selected"; ?>>Vendor</option>
                                <option value="Contractor" <?php if($result['account_type'] === "Contractor") echo "selected"; ?>>Contractor</option>
                            </select>
                        </fieldset>
                    </div>

                    <div class="col-md-12">
                        <fieldset class="form-group">
                            <label for="email_address">Email Address</label>
                            <input class="form-control" type="text" placeholder="Email Address" id="email_address" name="email_address" tabindex="6" value="<?php echo $result['email']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-12">
                        <fieldset class="form-group">
                            <label for="address_1">Street Address</label>
                            <input class="form-control" type="text" placeholder="Street Address" id="address_1" name="address_1" tabindex="6" value="<?php echo $result['address_1']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="city">City</label>
                            <input class="form-control" type="text" placeholder="City" id="city" name="city" tabindex="8" value="<?php echo $result['city']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="state" id="label_for_state">State</label>
                            <select class="form-control" id="state" name="state" tabindex="9">
                                <option value="AL" <?php if($result['state'] === "AL") echo "selected"; ?>>Alabama</option>
                                <option value="AK" <?php if($result['state'] === "AK") echo "selected"; ?>>Alaska</option>
                                <option value="AZ" <?php if($result['state'] === "AZ") echo "selected"; ?>>Arizona</option>
                                <option value="AR" <?php if($result['state'] === "AR") echo "selected"; ?>>Arkansas</option>
                                <option value="CA" <?php if($result['state'] === "CA") echo "selected"; ?>>California</option>
                                <option value="CO" <?php if($result['state'] === "CO") echo "selected"; ?>>Colorado</option>
                                <option value="CT" <?php if($result['state'] === "CT") echo "selected"; ?>>Connecticut</option>
                                <option value="DE" <?php if($result['state'] === "DE") echo "selected"; ?>>Delaware</option>
                                <option value="FL" <?php if($result['state'] === "FL") echo "selected"; ?>>Florida</option>
                                <option value="GA" <?php if($result['state'] === "GA") echo "selected"; ?>>Georgia</option>
                                <option value="HI" <?php if($result['state'] === "HI") echo "selected"; ?>>Hawaii</option>
                                <option value="ID" <?php if($result['state'] === "ID") echo "selected"; ?>>Idaho</option>
                                <option value="IL" <?php if($result['state'] === "IL") echo "selected"; ?>>Illinois</option>
                                <option value="IN" <?php if($result['state'] === "IN") echo "selected"; ?>>Indiana</option>
                                <option value="IA" <?php if($result['state'] === "IA") echo "selected"; ?>>Iowa</option>
                                <option value="KS" <?php if($result['state'] === "KS") echo "selected"; ?>>Kansas</option>
                                <option value="KY" <?php if($result['state'] === "KY") echo "selected"; ?>>Kentucky</option>
                                <option value="LA" <?php if($result['state'] === "LA") echo "selected"; ?>>Louisiana</option>
                                <option value="ME" <?php if($result['state'] === "ME") echo "selected"; ?>>Maine</option>
                                <option value="MD" <?php if($result['state'] === "MD") echo "selected"; ?>>Maryland</option>
                                <option value="MA" <?php if($result['state'] === "MA") echo "selected"; ?>>Massachusetts</option>
                                <option value="MI" <?php if($result['state'] === "MI") echo "selected"; ?>>Michigan</option>
                                <option value="MN" <?php if($result['state'] === "MN") echo "selected"; ?>>Minnesota</option>
                                <option value="MS" <?php if($result['state'] === "MS") echo "selected"; ?>>Mississippi</option>
                                <option value="MO" <?php if($result['state'] === "MO") echo "selected"; ?>>Missouri</option>
                                <option value="MT" <?php if($result['state'] === "MT") echo "selected"; ?>>Montana</option>
                                <option value="NE" <?php if($result['state'] === "NE") echo "selected"; ?>>Nebraska</option>
                                <option value="NV" <?php if($result['state'] === "NV") echo "selected"; ?>>Nevada</option>
                                <option value="NH" <?php if($result['state'] === "NH") echo "selected"; ?>>New Hampshire</option>
                                <option value="NJ" <?php if($result['state'] === "NJ") echo "selected"; ?>>New Jersey</option>
                                <option value="NM" <?php if($result['state'] === "NM") echo "selected"; ?>>New Mexico</option>
                                <option value="NY" <?php if($result['state'] === "NY") echo "selected"; ?>>New York</option>
                                <option value="NC" <?php if($result['state'] === "NC") echo "selected"; ?>>North Carolina</option>
                                <option value="ND" <?php if($result['state'] === "ND") echo "selected"; ?>>North Dakota</option>
                                <option value="OH" <?php if($result['state'] === "OH") echo "selected"; ?>>Ohio</option>
                                <option value="OK" <?php if($result['state'] === "OK") echo "selected"; ?>>Oklahoma</option>
                                <option value="OR" <?php if($result['state'] === "OR") echo "selected"; ?>>Oregon</option>
                                <option value="PA" <?php if($result['state'] === "PA") echo "selected"; ?>>Pennsylvania</option>
                                <option value="RI" <?php if($result['state'] === "RI") echo "selected"; ?>>Rhode Island</option>
                                <option value="SC" <?php if($result['state'] === "SC") echo "selected"; ?>>South Carolina</option>
                                <option value="SD" <?php if($result['state'] === "SD") echo "selected"; ?>>South Dakota</option>
                                <option value="TN" <?php if($result['state'] === "TN") echo "selected"; ?>>Tennessee</option>
                                <option value="TX" <?php if($result['state'] === "TX") echo "selected"; ?>>Texas</option>
                                <option value="UT" <?php if($result['state'] === "UT") echo "selected"; ?>>Utah</option>
                                <option value="VT" <?php if($result['state'] === "VT") echo "selected"; ?>>Vermont</option>
                                <option value="VA" <?php if($result['state'] === "VA") echo "selected"; ?>>Virginia</option>
                                <option value="WA" <?php if($result['state'] === "WA") echo "selected"; ?>>Washington</option>
                                <option value="WV" <?php if($result['state'] === "WV") echo "selected"; ?>>West Virginia</option>
                                <option value="WI" <?php if($result['state'] === "WI") echo "selected"; ?>>Wisconsin</option>
                                <option value="WY" <?php if($result['state'] === "WY") echo "selected"; ?>>Wyoming</option>
                            </select>
                        </fieldset>
                    </div>

                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="zip">Zip Code</label>
                            <input class="form-control" type="text" placeholder="Zip Code" id="zip" name="zip" data-mask="00000-0000" tabindex="10" value="<?php echo $result['zip']; ?>">
                        </fieldset>
                    </div>

                    <div class="col-md-12" id="directions_group">
                        <fieldset class="form-group">
                            <label for="directions" class="align-top">Directions <small class="text-muted">(Optional)</small></label>
                            <textarea id="directions" name="directions" placeholder="Directions to Location" rows="3" class="form-control"><?php echo $result['directions']; ?></textarea>
                        </fieldset>
                    </div>

                    <div class="col-md-12 text-md-center">
                        <button type="submit" class="btn btn-primary waves-effect" id="go_button">Update</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-box">
                <div class="row">
                    <div class="col-md-12">
                        <h2 id="contact_header">Interaction Details</h2>
                    </div>
                </div>

                <div class="row">
                    <ul class="nav nav-tabs m-b-10" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="job-tab" data-toggle="tab" href="#log_notes" role="tab" aria-controls="log_notes" aria-expanded="true">Notes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="job-tab" data-toggle="tab" href="#job_information" role="tab" aria-controls="job_information" aria-expanded="true">Jobs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="alt-contaccts-tab" data-toggle="tab" href="#alt_contact_info" role="tab" aria-controls="alt_controls">Contacts</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <div role="tabpanel" class="tab-pane fade in active" id="log_notes" aria-labelledby="log_notes">
                            <div id="insert_new_note" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <fieldset class="form-group">
                                            <label for="caller_name">Caller Name</label>
                                            <input class="form-control" type="text" placeholder="Caller Name" id="caller_name" name="caller_name" value="<?php echo $result['first_name']; ?>">
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <fieldset class="form-group">
                                            <label for="note_log" class="align-top">Log Notes</label>
                                            <textarea id="note_log" name="note_log" placeholder="Log Notes" rows="5" class="form-control"></textarea>
                                        </fieldset>
                                    </div>

                                    <div class="col-md-12 text-md-center">
                                        <button type="button" class="btn btn-primary waves-effect" id="submit" tabindex="12" onclick="$('#view_current_notes').show();$('#insert_new_note').hide();">Save</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="view_current_notes">
                                <div class="col-md-12">
                                    <table id="note_table" class="table table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Note Date</th>
                                            <th>Logged By</th>
                                            <th>Caller Name</th>
                                            <th>Note Start</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class="cursor-hand" data-toggle="model_id_1" data-target="#model_id_1">
                                            <td>3/28/17 8:46AM CST</td>
                                            <td>Ben</td>
                                            <td>Mary</td>
                                            <td>CU called in and...</td>
                                        </tr>
                                        <tr class="cursor-hand">
                                            <td colspan="4" onclick="$('#view_current_notes').hide();$('#insert_new_note').show();">Add new note...</td>
                                            <td style="display: none;"></td>
                                            <td style="display: none;"></td>
                                            <td style="display: none;"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div id="model_id_1" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="model_id_1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                <h4 class="modal-title" id="myModalLabel">Modal Heading</h4>
                                            </div>
                                            <div class="modal-body">
                                                <h4>Text in a modal</h4>
                                                <p>Duis mollis, est non commodo luctus, nisi erat porttitor ligula.</p>
                                                <hr>
                                                <h4>Overflowing text to show scroll behavior</h4>
                                                <p>Cras mattis consectetur purus sit amet fermentum. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Morbi leo risus, porta ac consectetur ac, vestibulum at eros.</p>
                                                <p>Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.</p>
                                                <p>Aenean lacinia bibendum nulla sed consectetur. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec sed odio dui. Donec ullamcorper nulla non metus auctor fringilla.</p>
                                                <p>Cras mattis consectetur purus sit amet fermentum. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Morbi leo risus, porta ac consectetur ac, vestibulum at eros.</p>
                                                <p>Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.</p>
                                                <p>Aenean lacinia bibendum nulla sed consectetur. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec sed odio dui. Donec ullamcorper nulla non metus auctor fringilla.</p>
                                                <p>Cras mattis consectetur purus sit amet fermentum. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Morbi leo risus, porta ac consectetur ac, vestibulum at eros.</p>
                                                <p>Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.</p>
                                                <p>Aenean lacinia bibendum nulla sed consectetur. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec sed odio dui. Donec ullamcorper nulla non metus auctor fringilla.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-primary waves-effect waves-light">Save changes</button>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->

                            </div>
                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="job_information" aria-labelledby="job-tab">
                            <table id="jobtable" class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Service Order</th>
                                    <th>Project Manager</th>
                                    <th>Operation</th>
                                    <th>4P Date</th>
                                    <th>Install Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="cursor-hand">
                                    <td>Active</td>
                                    <td>676</td>
                                    <td>Robert</td>
                                    <td>103 - Basic Design</td>
                                    <td>01/17/2017</td>
                                    <td>04/22/2017</td>
                                </tr>
                                <tr onclick="document.location.href='create_so.php?id=<?php echo $id; ?>'" class="cursor-hand">
                                    <td colspan="6">Add new service order...</td>
                                    <td style="display: none;"></td>
                                    <td style="display: none;"></td>
                                    <td style="display: none;"></td>
                                    <td style="display: none;"></td>
                                    <td style="display: none;"></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="alt_contact_info" role="tabpanel" aria-labelledby="alt-contacts-tab">
                            <div class="col-sm-4">
                                <div class="card bg-lightgray">
                                    <h3 class="card-header">John Doe</h3>
                                    <div class="card-block">
                                        <h4>904-701-8348</h4>
                                        <p class="card-text">1129 Natures Way<br>Pensacola CA, 11475</p>
                                        <a href="#" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="card bg-lightgray">
                                    <h3 class="card-header">John Doe</h3>
                                    <div class="card-block">
                                        <h4>904-701-8348</h4>
                                        <p class="card-text">1129 Natures Way<br>Pensacola CA, 11475</p>
                                        <a href="#" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="card bg-lightgray">
                                    <h3 class="card-header">John Doe</h3>
                                    <div class="card-block">
                                        <h4>904-701-8348</h4>
                                        <p class="card-text">1129 Natures Way<br>Pensacola CA, 11475</p>
                                        <a href="#" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="card bg-lightgray">
                                    <h3 class="card-header">John Doe</h3>
                                    <div class="card-block">
                                        <h4>904-701-8348</h4>
                                        <p class="card-text">1129 Natures Way<br>Pensacola CA, 11475</p>
                                        <a href="#" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="card bg-lightgray">
                                    <h3 class="card-header">New Contact...</h3>
                                    <div class="card-block">
                                        <h4>Add a new contact</h4>
                                        <p class="card-text">Click here to add <br>a new contact</p>
                                        <a href="#" class="btn btn-success">Add New</a>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    <!-- Modal-Effect -->
    <script src="/assets/plugins/custombox/js/custombox.min.js"></script>
    <script src="/assets/plugins/custombox/js/legacy.min.js"></script>


<script type="text/javascript">
    $(function() {
        var jobinfo = $("#job_information");
        var alt_cont = $("#alternate_contacts");

        $("#jobtable").dataTable();
        $("#note_table").dataTable();

        $("#job_info_btn").click(function() {
            setTimeout(function () {
                if($("#job_info_btn").hasClass("active")) {
                    jobinfo.show();
                    alt_cont.hide();
                }
            }, 50);
        });

        $("#alt_contact_btn").click(function () {
            setTimeout(function() {
                if($("#alt_contact_btn").hasClass("active")) {
                    jobinfo.hide();
                    alt_cont.show();
                }
            }, 50);
        });
    });
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>