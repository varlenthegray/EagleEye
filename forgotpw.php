<?php require 'includes/header_account.php'; ?>

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
                            <h6 class="text-muted text-uppercase m-b-0 m-t-0">Reset Password</h6>
                            <p class="text-muted m-b-0 font-13 m-t-20">Enter your email address and we'll send you an email with instructions to reset your password.  </p>
                        </div>
                    </div>
                    <form class="m-t-30" action="old/index_old.php">
                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" type="email" required="" placeholder="Enter email">
                            </div>
                        </div>

                        <div class="form-group row text-center m-t-20 m-b-0">
                            <div class="col-xs-12">
                                <button class="btn btn-success btn-block waves-effect waves-light" type="submit">Send Email</button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
        <!-- end card-box-->

        <div class="m-t-20">
            <div class="text-xs-center">
                <p class="text-white">Back to<a href="login.php" class="text-white m-l-5"><b>Log In</b></p>
            </div>
        </div>

    </div>
    <!-- end wrapper page -->


<?php require 'includes/footer_account.php'; ?>