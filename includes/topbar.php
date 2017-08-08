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
    <div class="custom-logo">
        <img src="../assets/images/logo.png" height="80px" />
    </div>

    <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
    <input type="text" name="ausernameidontcareabout" style="display:none;">
    <input type="password" name="apasswordidontcareabout" style="display:none;">

    <div class="topbar-main hidden-print">
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
                <ul class="nav navbar-nav pull-left">
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

                    <li class="nav-item notification-list">
                        <a class="nav-link arrow-none waves-light waves-effect" href="#" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi-email noti-icon"></i></a>
                    </li>

                    <li class="nav-item notification-list">
                        <a class="nav-link arrow-none waves-light waves-effect dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi zmdi-calendar noti-icon"></i></a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-lg" aria-labelledby="Preview">
                            This is a test.
                        </div>
                    </li>

                    <li class="nav-item notification-list">
                        <a class="nav-link arrow-none waves-light waves-effect" href="#" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi zmdi-comments noti-icon"></i></a>
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
                        <li id="nav_dashboard"><a><i class="zmdi zmdi-view-dashboard m-r-5"></i><span><?php echo NAV_DASHBOARD; ?></span></a></li>
                        <li id="nav_employees"><a><i class="zmdi zmdi-account-circle m-r-5"></i><span><?php echo NAV_SHOP_LOGOUT; ?></span></a></li>
                    <?php
                        } else {
                    ?>
                    <div role="search" class="navbar-left app-search pull-left hidden-xs" _lpchecked="1">
                        <input type="text" placeholder="Search..." class="form-control" id="global_search" name="global_search_2" autocomplete="off"><a id="global_search_button"><i class="fa fa-search"></i></a>
                    </div>
                    <li style="border: 1px dotted rgba(0,0,0,.25);height: 42px;"><span></span></li>
                    <li id="nav_dashboard"><a><i class="zmdi zmdi-view-dashboard m-r-5"></i><span><?php echo NAV_DASHBOARD; ?></span></a></li>
                    <li id="nav_workcenter"><a><i class="zmdi zmdi-receipt m-r-5"></i><span><?php echo NAV_WORKCENTER; ?></span></a></li>
                    <li id="nav_employees"><a><i class="zmdi zmdi-account-circle m-r-5"></i><span><?php echo NAV_EMPLOYEELOGIN; ?></span></a></li>
                    <li id="nav_timecard"><a><i class="zmdi zmdi-time m-r-5"></i><span><?php echo NAV_ACCOUNTING_TIMECARDS; ?></span></a></li>
                    <li id="nav_tasks"><a><i class="zmdi zmdi-check-circle-u m-r-5"></i><span><?php echo NAV_TASKS; ?></span></a></li>
                    <li id="nav_add_so"><a><i class="zmdi zmdi-account-add m-r-5"></i><span><?php echo NAV_ADD_SO; ?></span></a></li>
                    <?php
                        if((int)$_SESSION['userInfo']['account_type'] <= 1) {
                    ?>
                    <li style="border: 1px dotted rgba(0,0,0,.25);height: 42px;"><span></span></li>
                    <li class="has-submenu">
                        <a><i class="zmdi zmdi-accounts-list-alt"></i>Admin</a>
                        <ul class="submenu">
                            <li id="nav_kpi"><a><i class="zmdi zmdi-time-interval m-r-5"></i><?php echo NAV_KPI; ?></a></li>
                            <li id="nav_pbp"><a><i class="zmdi zmdi-trending-up m-r-5"></i><?php echo NAV_PBP; ?></a></li>
                            <li id="nav_reports"><a><i class="zmdi zmdi-assignment m-r-5"></i><?php echo NAV_REPORTS; ?></a></li>
                            <li id="nav_adduser"><a><i class="zmdi zmdi-accounts-add m-r-5"></i><?php echo NAV_ADDUSER; ?></a></li>
                        </ul>
                    </li>

                    <li class="has-submenu">
                        <a><i class="zmdi zmdi zmdi-code-setting"></i>WIP</a>
                        <ul class="submenu">
                            <li id="nav_inventory"><a><i class="zmdi zmdi-dropbox m-r-5"></i><?php echo NAV_INVENTORY; ?></a></li>
                            <li id="nav_pricing"><a><i class="zmdi zmdi-store m-r-5"></i><?php echo NAV_PRICINGPROGRAM; ?></a></li>
                        </ul>
                    </li>
                    <?php
                            }
                        }
                    ?>
                    <li style="border: 1px dotted rgba(0,0,0,.25);height: 42px;"><span></span></li>
                    <li id="nav_feedback"><a data-toggle="modal" data-target="#feedback-page"><i class="fa fa-comment-o m-r-5"></i><span><?php echo NAV_FEEDBACK; ?></span></a></li>
                    <li style="border: 1px dotted rgba(0,0,0,.25);height: 42px;"><span></span></li>
                    <li id="nav_logout"><a href="/login.php?logout=true"><i class="fa fa-sign-out m-r-5"></i><span><?php echo NAV_LOGOUT; ?></span></a></li>
                </ul>
                <!-- End navigation menu  -->


            </div>
        </div>
    </div>

    <div class="js_loading" style="display: none;"><i class='fa fa-3x fa-spin fa-spinner'></i></div>
</header>
<!-- End Navigation Bar-->


<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->

<div class="wrapper">
    <div class="container">
