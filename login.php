<?php
session_start();

require 'includes/header_account.php'; // initial header for the page

if($_REQUEST['action'] === 'login') { // if we're trying to log in
    $username = $dbconn->real_escape_string($_REQUEST['username']); // get username

    $user = $dbconn->query("SELECT * FROM user WHERE username = '$username' AND account_status = TRUE"); // fetch the username from the database

    if($user->num_rows === 1) { // grab the number of rows found, if it's 1, then we're good
        $result = $user->fetch_assoc(); // grab the user data itself

        if(password_verify($_REQUEST['password'],$result['password'])) { // seems the password is valid too
            $_SESSION['valid'] = true; // set the session as valid
            $_SESSION['userInfo'] = $result;

            if($result['id'] !== '16') {
                $_SESSION['shop_user'] = $result;
                $_SESSION['shop_active'] = true;

                $dbconn->query("UPDATE user SET last_login = UNIX_TIMESTAMP() WHERE id = {$result['id']}");
                $_SESSION['userInfo']['justLoggedIn'] = true;

                echo "<script type='text/javascript'>window.location.replace('index.php');</script>";
            } else {
                echo "<script type='text/javascript'>window.location.replace('shopfloor/login.php');</script>";
            }

            ?>

            <?php
        } else {
            ?>
            <script type="text/javascript">
                displayToast("error", "There was a problem with the username or password. Please try again.", "Username/Password Error");
            </script>
            <?php
        }
    } else {
        ?>
        <script type="text/javascript">
            displayToast("error", "There was a problem with the username or password. Please try again.", "Username/Password Error");
        </script>
        <?php
    }
} elseif ($_REQUEST['pli']) {
    ?>
    <script type="text/javascript">
        displayToast("info", "Before accessing any information, please log in to your account.", "Please Login");
    </script>
    <?php
} elseif ($_REQUEST['logout']) {
    session_destroy();
    session_regenerate_id();
    ?>
    <script type="text/javascript">
        displayToast("info", "You have successfully logged out.", "Logout Successful");
    </script>
    <?php
}
?>

    <div class="account-pages"></div>
    <div class="clearfix"></div>
    <div class="wrapper-page">

        <div class="account-bg">
            <div class="card-box m-b-0">
                <div class="text-xs-center m-t-20">
                    <a href="old/index_old.php" class="logo">
                        <i class="zmdi zmdi-group-work icon-c-logo"></i>
                        <span><?php echo LOGO_TEXT; ?></span>
                    </a>
                </div>
                <div class="m-t-10 p-20">
                    <div class="row">
                        <div class="col-xs-12 text-xs-center">
                            <h6 class="text-muted text-uppercase m-b-0 m-t-0">Log In</h6>
                        </div>
                    </div>

                    <form class="m-t-20" action="login.php?action=login" method="post">
                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required="" placeholder="Username" name="username">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" type="password" required="" placeholder="Password" name="password">
                            </div>
                        </div>

                        <div class="form-group text-center row m-t-10">
                            <div class="col-xs-12">
                                <button class="btn btn-success btn-block waves-effect waves-light" type="submit">Submit</button>
                            </div>
                        </div>

                        <div class="form-group row m-t-30 m-b-0">
                            <div class="col-sm-12">
                                <a href="forgotpw.php" class="text-muted"><i class="fa fa-lock m-r-5"></i> Forgot your password?</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- end card-box-->
    </div>
    <!-- end wrapper page -->


<?php require 'includes/footer_account.php'; ?>