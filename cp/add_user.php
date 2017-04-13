<?php require '../includes/header_start.php';
require '../includes/header_end.php';

if($_REQUEST["action"] === "create") {
    $username = trim($dbconn->real_escape_string($_REQUEST['username']));
    $password = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
    $email = trim($dbconn->real_escape_string($_REQUEST['email_address']));
    $acc_type = $dbconn->real_escape_string($_REQUEST['account_type']);
    $ip = $_SERVER['REMOTE_ADDR'];


    if(strlen($username) <= 3 || strlen($username) >= 25) {
        Toast("error", "Username presented was not long enough or too long to process. Please try again.", "Error");
    } elseif(strlen($email) <= 8 || strlen($email) >= 150) {
        Toast("error", "Email presented was not long enough or too long to process. Please try again.", "Error");
    } elseif(strlen($acc_type) > 2) {
        Toast("error", "Account type did not match a valid function. Please try again.", "Error");
    } else {
        $qry = $dbconn->query("SELECT * FROM user WHERE username = '$username' OR email = '$email'");

        if($qry->num_rows > 0) {
            Toast("error", "A user with that email address or username already exists.", "Error");
        } else {
            $sql = "INSERT INTO user (username, password, email, last_ip_address, last_login, session_id, account_type, account_status) VALUES ('$username', '$password', '$email', '$ip', NOW(), '', '$acc_type', '1')";
            if($dbconn->query($sql) === true) {
                Toast("success", "The account has been created successfully and the user can log in promptly.", "Created Successfully");
            } else {
                dbLogSQLErr($dbconn);
            }
        }
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
        <h4 class="page-title">Add User</h4>
    </div>
</div>

<script type="text/javascript">
    function validateInput(input, min, max, email) {
        input.keydown(function() {
            if(!email) {
                if(input.val().length >= min && input.val().length <= max) {
                    input.addClass("form-control-success").closest("fieldset").addClass("has-success");
                    input.removeClass("form-control-danger").closest("fieldset").removeClass("has-danger");

                    if($(".has-danger").length === 0) {
                        $("#submit_fieldset").prop('disabled', false);
                    }
                } else {
                    input.removeClass("form-control-succcess").closest("fieldset").removeClass("has-success");
                    input.addClass("form-control-danger").closest("fieldset").addClass("has-danger");

                    $("#submit_fieldset").prop('disabled', true);
                }
            } else {
                var email_regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

                if(input.val().length >= min && input.val().length <= max) {
                    if(email_regex.test(input.val())) {
                        input.addClass("form-control-success").closest("fieldset").addClass("has-success");
                        input.removeClass("form-control-danger").closest("fieldset").removeClass("has-danger");

                        if($(".has-danger").length === 0) {
                            $("#submit_fieldset").prop('disabled', false);
                        }
                    } else {
                        input.removeClass("form-control-succcess").closest("fieldset").removeClass("has-success");
                        input.addClass("form-control-danger").closest("fieldset").addClass("has-danger");

                        $("#submit_fieldset").prop('disabled', true);
                    }
                } else {
                    input.removeClass("form-control-succcess").closest("fieldset").removeClass("has-success");
                    input.addClass("form-control-danger").closest("fieldset").addClass("has-danger");

                    $("#submit_fieldset").prop('disabled', true);
                }
            }
        });
    }
</script>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="card card-block">
            <h4 class="card-title">Add User Page</h4>
            <p class="card-text">This page adds a basic user to the system. This is a global access account to the system.
            From here you will be able to tie additional records such as contacts, permissions, and other information to the
            account that is created here. The permission level that you assign is a global permission level and used as the
            primary factor, this can be overridden per module and in certain areas as needed.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="card-box">
            <form method="post" action="add_user.php?action=create">
                <fieldset class="form-group has-danger">
                    <label for="username">Username</label>
                    <input type="text" name="username" class="form-control" id="username" placeholder="Username" autocomplete="off" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="right" data-content="The username is case insensitive. You may use A-Z and 0-9. The username must be between 3 and 25 characters long." data-original-title="Username Guidelines">
                </fieldset>

                <fieldset class="form-group has-success">
                    <label for="password">Password <a href="https://xkcd.com/936/" target="_blank"><i class="zmdi zmdi-help"></i></a></label>

                    <script type="text/javascript">
                        $(function() {
                            $("#password").keypress(function() { // on the first keypress
                                $("#password").attr({type:"password"}); // convert the password field to password instead of plain text
                            });
                        });
                    </script>

                    <input type="text" name="password" class="form-control" id="password" autocomplete="off" value="<?php echo substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()<>?{}[],./') , 0 , 10 ); // generate a random password off of the approved characters ?>" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="right" data-content="The password must be between 6 and 70 characters long containing at least 1 lower case, 1 upper case, and 1 symbol. There are no banned characters." data-original-title="Password Guidelines">
                </fieldset>

                <fieldset class="form-group has-danger">
                    <label for="email">Email Address</label>
                    <input type="email" name="email_address" class="form-control" id="email" autocomplete="off" placeholder="Email Address" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="right" data-content="A standard email address should be used. The maximum number of characters to be used is 150." data-original-title="Email Guidelines">

                    <script type="text/javascript">

                    </script>
                </fieldset>

                <fieldset class="form-group">
                    <label for="account_type">Account Type</label>
                    <select class="form-control" name="account_type" id="account_type">
                        <?php
                        $atype = $dbconn->query("SELECT * FROM account_type ORDER BY id DESC"); // grab all account types

                        while($row = $atype->fetch_assoc()) { // iterate through them
                            echo "<option value='{$row['id']}'>{$row['name']}</option>"; // display each of them
                        }
                        ?>
                    </select>
                </fieldset>

                <fieldset class="form-group text-md-center" id="submit_fieldset" disabled>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </fieldset>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    validateInput($("#username"), 3, 25);
    validateInput($("#password"), 6, 70);
    validateInput($("#email"), 9, 150, true);
</script>

<?php require '../includes/footer_start.php';
require '../includes/footer_end.php' ?>
