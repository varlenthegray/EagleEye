<?php
switch($_SERVER['SCRIPT_NAME']) {
    case '/index.php':
        $title_name = "Dashboard";
        break;

    default:
        $title_name = "Dashboard";
        break;
}
?>

<!-- Navigation Bar-->
<header id="topnav">
    <div class="topbar-main">
        <div class="container">

            <!-- LOGO -->
            <div class="topbar-left">
                <a href="/index.php" class="logo">
                    <i class="zmdi zmdi-group-work icon-c-logo"></i>
                    <span><?php echo LOGO_TEXT . " - " . $title_name; ?></span>
                </a>
            </div>
            <!-- End Logo container-->

            <div class="menu-extras">
                <ul class="nav navbar-nav pull-right">
                    <li class="nav-item">
                        <!-- Mobile menu toggle-->
                        <a class="navbar-toggle">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                        <!-- End mobile menu toggle-->
                    </li>

                    <li class="nav-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <i class="zmdi zmdi-notifications-none noti-icon"></i>
                            <span class="noti-icon-badge"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-lg" aria-labelledby="Preview">
                            <!-- item-->
                            <div class="dropdown-item noti-title">
                                <h5><small><span class="label label-danger pull-xs-right">7</span>Notification</small></h5>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-success"><i class="icon-bubble"></i></div>
                                <p class="notify-details">Robert S. Taylor commented on Admin<small class="text-muted">1min ago</small></p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-info"><i class="icon-user"></i></div>
                                <p class="notify-details">New user registered.<small class="text-muted">1min ago</small></p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-danger"><i class="icon-like"></i></div>
                                <p class="notify-details">Carlos Crouch liked <b>Admin</b><small class="text-muted">1min ago</small></p>
                            </a>

                            <!-- All-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item notify-all">
                                View All
                            </a>

                        </div>
                    </li>

                    <li class="nav-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <img src="/assets/images/users/avatar-1.jpg" alt="user" class="img-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-arrow profile-dropdown " aria-labelledby="Preview">
                            <!-- item-->
                            <div class="dropdown-item noti-title">
                                <h5 class="text-overflow"><small>Welcome <?php echo ucwords($_SESSION['userInfo']['username']); ?>!</small> </h5>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="zmdi zmdi-account-circle"></i> <span>Profile</span>
                            </a>

                            <!-- item-->
                            <a href="/login.php?logout=true" class="dropdown-item notify-item">
                                <i class="zmdi zmdi-power"></i> <span>Logout</span>
                            </a>

                        </div>
                    </li>
                </ul>
            </div> <!-- end menu-extras -->

            <div class="clearfix"></div>

        </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->


    <div class="navbar-custom">
        <div class="container">
            <div id="navigation">
                <!-- Navigation Menu-->
                <ul class="navigation-menu">
                    <?php
                        if($_SESSION['userInfo']['account_type'] === '6') {
                    ?>
                        <li>
                            <a href="/shopfloor/index.php"><i class="zmdi zmdi-assignment"></i>
                                <span><?php echo NAV_INDIVIDUAL; ?></span></a>
                        </li>
                        <li>
                            <a href="/ondemand/shopfloor/login_actions.php?action=logout"
                               id="shop_logout_link"><i class="zmdi zmdi-time-restore-setting"></i>
                                <span><?php echo NAV_SHOP_LOGOUT; ?></span></a></li>
                    <?php
                        } else {
                    ?>

                    <li>
                        <a href="/index.php"><i class="zmdi zmdi-view-dashboard"></i>
                            <span> <?php echo NAV_DASHBOARD; ?> </span> </a>
                    </li>
                    <li>
                        <a href="/pricing/index.php"><i class="zmdi zmdi-store"></i>
                            <span> <?php echo NAV_PRICINGPROGRAM; ?> </span> </a>
                    </li>
                    <li class="has-submenu">
                        <a href="#"><i class="zmdi zmdi-assignment"></i> <?php echo NAV_SHOPFLOOR; ?> </a>
                        <ul class="submenu">
                            <li><a href="/shopfloor/index.php"><?php echo NAV_INDIVIDUAL; ?></a></li>
                            <li><a href="/shopfloor/workcenter.php"><?php echo NAV_WORKCENTER; ?></a></li>
                            <li class="has-submenu">
                                <a href="#"><?php echo NAV_BRACKET_MGMT; ?></a>
                                <ul class="submenu">
                                    <li><a href="/shopfloor/bracket_builder.php"><?php echo NAV_BRACKET_NEW; ?></a></li>
                                    <li><a href="#"><?php echo NAV_BRACKET_LIST; ?></a></li>
                                </ul>
                            </li>
                            <li><a href="/shopfloor/job_management.php">Job Management</a></li>
                            <li><a href="/shopfloor/sales_order.php">Sales Orders</a></li>
                            <li><a href="/ondemand/shopfloor/login_actions.php?action=logout"
                                   id="shop_logout_link"><?php echo NAV_SHOP_LOGOUT; ?></a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="zmdi zmdi-dropbox"></i> <?php echo NAV_INVENTORY; ?> </a>
                    </li>
                    <li>
                        <a href="#"><i class="zmdi zmdi-money-box"></i> <?php echo NAV_ACCOUNTING; ?> </a>
                    </li>
                    <li class="has-submenu">
                        <a href="#"><i class="zmdi zmdi-accounts-list-alt"></i> <?php echo NAV_ADMIN; ?> </a>
                        <ul class="submenu">
                            <li><a href="#"><?php echo NAV_KPI; ?></a></li>
                            <li><a href="#"><?php echo NAV_PBP; ?></a></li>
                            <li><a href="#"><?php echo NAV_REPORTS; ?></a></li>
                        </ul>
                    </li>
                    <li class="has-submenu">
                        <a href="#"><i class="zmdi zmdi-globe-lock"></i> <?php echo NAV_CPANEL; ?> </a>
                        <ul class="submenu">
                            <li><a href="/cp/add_user.php"><?php echo NAV_ADDUSER; ?></a></li>
                        </ul>
                    </li>
                    <?php
                        }
                    ?>
                </ul>
                <!-- End navigation menu  -->
            </div>
        </div>
    </div>
</header>
<!-- End Navigation Bar-->


<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->

<div class="wrapper">
    <div class="container">
