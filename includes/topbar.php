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
    <div class="custom-logo hidden-print" style="margin-right:130px;position:reliatve;z-index:2;margin-top:-2px;">
        <img src="../assets/images/logo_new.png" height="135px" />
    </div>

    <!-- fake fields are a workaround for chrome autofill getting the wrong fields (such as search) -->
    <input class="ignoreSaveAlert" type="text" name="ausernameidontcareabout" style="display:none;">
    <input class="ignoreSaveAlert" type="password" name="apasswordidontcareabout" style="display:none;">

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

                    <li class="nav-item dropdown notification-list" id="notification_list">
                        <!-- AJAX -->
                    </li>
                </ul>
            </div> <!-- end menu-extras -->

            <div id="clock" style="z-index:999;color:#000;position:absolute;top:40px;right:-110px;"></div>

            <div class="clearfix"></div>
        </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->

    <div class="navbar-custom">
        <div class="container">
            <div id="navigation">
                <!-- Navigation Menu-->
                <ul class="navigation-menu">
                    <div role="search" class="navbar-left app-search pull-left hidden-xs" _lpchecked="1">
                        <input type="text" placeholder="Search..." class="form-control ignoreSaveAlert" id="global_search" name="global_search_2" autocomplete="off"><a id="global_search_button"><i class="fa fa-search"></i></a>
                    </div>
                    <li style="border: 1px dotted rgba(0,0,0,.25);height: 42px;"><span></span></li>
                    <?php
                        echo ($_SESSION['userInfo']['account_type'] !== '6') ? "<li id='nav_add_so'><a><i class='zmdi zmdi-account-add m-r-5'></i><span>" . NAV_ADD_SO . "</span></a></li>" : null;
                        //echo ($_SESSION['userInfo']['account_type'] !== '6') ? "<li id='nav_quick_add'><a><i class='zmdi zmdi-plus-circle-o m-r-5'></i><span>" . NAV_QUICKADD . "</span></a></li>" : null;

                        echo ($_SESSION['userInfo']['account_type'] !== '6') ? "<li class='nav-separator'><span></span></li>" : null;

                        echo "<li id='nav_dashboard'><a><i class='zmdi zmdi-view-dashboard m-r-5'></i><span>" . NAV_DASHBOARD . "</span></a></li>";
                        echo "<li id='nav_logout'><a class='clock_out' data-id='{$_SESSION['shop_user']['id']}'><i class='zmdi zmdi-time-countdown m-r-5'></i><span>" . NAV_CLOCKOUT . "</span></a></li>";
                        echo "<li id='nav_logout'><a href='/login.php?logout=true'><i class='fa fa-sign-out m-r-5'></i><span>" . NAV_LOGOUT . "</span></a></li>";

                        echo "<li class='nav-separator'><span></span></li>";

                        echo "<li id='nav_feedback'><a data-toggle='modal' data-target='#feedback-page'><i class='fa fa-comment-o m-r-5'></i><span>" . NAV_FEEDBACK . "</span></a></li>";
                        echo ($_SESSION['userInfo']['account_type'] !== '6') ? "<li id='nav_tasks'><a onclick='unloadPage(\"tasks\")'><i class='zmdi zmdi-check-circle-u m-r-5'></i><span>" . NAV_TASKS . "</span></a></li>" : null;

                        echo "<li class='nav-separator'><span></span></li>";

                        echo ($_SESSION['userInfo']['account_type'] !== '6') ? "<li id='nav_workcenter'><a onclick='unloadPage(\"workcenter\")'><i class='zmdi zmdi-receipt m-r-5'></i><span>" . NAV_WORKCENTER . "</span></a></li>" : null;

                        echo ($_SESSION['userInfo']['account_type'] !== '6') ? "<li class='has-submenu'>
                                        <a><i class='zmdi zmdi-assignment'></i>" . NAV_REPORTS . "</a>
                                        <ul class='submenu'>
                                            <li id='nav_so_list'><a onclick='unloadPage(\"so_list\")'><i class='zmdi zmdi-accounts-list m-r-5'></i>" . NAV_SOLIST . "</a></li>
                                            <li id='nav_sales_list'><a onclick='unloadPage(\"sales_list\")'><i class='zmdi zmdi-accounts-list m-r-5'></i>" . NAV_SALES_LIST . "</a></li>
                                        </ul>
                                    </li>"
                            : null;

                        echo ($_SESSION['userInfo']['account_type'] !== '6') ? "<li id='nav_employees'><a><i class='zmdi zmdi-account-circle m-r-5'></i><span>" . NAV_EMPLOYEELOGIN . "</span></a></li>" : null;
                        echo ($_SESSION['userInfo']['account_type'] !== '6') ? "<li id='nav_timecard'><a><i class='zmdi zmdi-time m-r-5'></i><span>" . NAV_ACCOUNTING_TIMECARDS . "</span></a></li>" : null;

                        echo ($_SESSION['userInfo']['account_type'] === '6') ? "<li id='nav_employees'><a><i class='zmdi zmdi-account-circle m-r-5'></i><span>" . NAV_SHOP_LOGOUT . "</span></a></li>" : null;
                    ?>
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
