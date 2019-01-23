<ul class="navigation-menu">
    <?php
    $nav_out = '';

    if($bouncer->validate('search')) {
        $nav_out .= <<<HEREDOC
<div role="search" class="navbar-left app-search pull-left hidden-xs" _lpchecked="1">
    <input type="text" placeholder="Search..." class="form-control ignoreSaveAlert" id="global_search" name="global_search_2" autocomplete="off"><a id="global_search_button"><i class="fa fa-search"></i></a>
</div>
<li style="border: 1px dotted rgba(0,0,0,.25);height: 42px;"><span></span></li>
HEREDOC;
    }

    $nav_out .= <<<HEREDOC
        <li class='has-submenu'>
            <a><i class='zmdi zmdi-collection-plus'></i>New</a>
            <ul class='submenu'>
HEREDOC;

//    $nav_out .= $bouncer->validate('add_so') ? "<li class='nav_add_so'><a><i class='zmdi zmdi-file-plus m-r-5'></i>New SO</a></li>" : null;
    $nav_out .= $bouncer->validate('view_contacts') ? "<li class='nav_add_company'><a><i class='zmdi zmdi-city-alt m-r-5'></i>New Bill To</a></li>" : null;
    $nav_out .= $bouncer->validate('view_contacts') ? "<li class='nav_add_contact'><a><i class='zmdi zmdi-account-add m-r-5'></i>New Contact</a></li>" : null;
    $nav_out .= $bouncer->validate('add_project') ? "<li id='nav_add_project'><a><i class='zmdi zmdi-plus-square m-r-5'></i>New Project</a></li>" : null;

    $nav_out .= <<<HEREDOC
            </ul>
        </li>
HEREDOC;

    $nav_out .= "<li class='nav-separator'></li><li id='nav_dashboard'><a href='/main.php'><i class='zmdi zmdi-view-dashboard m-r-5'></i><span>Dashboard</span></a></li>";

    if($bouncer->validate('clock_out')) {
        $nav_out .= <<<HEREDOC
<li class='has-submenu'>
    <a><i class='zmdi zmdi-account'></i>Account</a>
    <ul class='submenu'>
        <li id='nav_logout'><a class='clock_out' data-id='{$_SESSION['shop_user']['id']}'><i class='zmdi zmdi-time-countdown m-r-5'></i>Clock Out</a></li>
        <li id='nav_logout'><a href='/login.php?logout=true'><i class='fa fa-sign-out m-r-5'></i>Log Out</a></li>
    </ul>
</li>
HEREDOC;
    } else {
        $nav_out .= "<li id='nav_logout'><a href='/login.php?logout=true'><i class='fa fa-sign-out m-r-5'></i>Log Out</a></li>";
    }

    $nav_out .= $bouncer->validate('view_break') ? "<li class='nav_break'><a><i class='zmdi zmdi-hourglass-alt m-r-5'></i><span></span></a></li>" : null;

    $nav_out .= "<li class='nav-separator'><span></span></li>";

    $nav_out .= $bouncer->validate('add_feedback') ? "<li id='nav_feedback'><a data-toggle='modal' data-target='#feedback-page'><i class='fa fa-comment-o m-r-5'></i><span>Feedback</span></a></li>" : null;
    $nav_out .= $bouncer->validate('view_tasks') ? "<li id='nav_tasks'><a onclick='unloadPage(\"tasks\")'><i class='zmdi zmdi-check-circle-u m-r-5'></i><span>Tasks</span></a></li>" : null;
    $nav_out .= $bouncer->validate('view_opl') ? "<li id='nav_opl'><a onclick='unloadPage(\"opl/index\")'><i class='zmdi zmdi-collection-text m-r-5'></i><span>OPL</span></a></li>" : null;

    if($bouncer->validate('add_feedback') || $bouncer->validate('view_tasks')) {
        $nav_out .= "<li class='nav-separator'><span></span></li>";
    }

    $nav_out .= $bouncer->validate('view_contacts') ? "<li id='nav_contacts'><a onclick='unloadPage(\"display_contacts\")'><i class='zmdi zmdi-account-box-mail m-r-5'></i>Contacts</a></li>" : null;

    if($bouncer->validate('view_workcenter') || $bouncer->validate('view_so_list') || $bouncer->validate('view_sales_list') || $bouncer->validate('view_timecards')) {
        $nav_out .= "<li class='has-submenu'>
                        <a><i class='zmdi zmdi-assignment'></i>Reports</a>
                        <ul class='submenu'>";

        $nav_out .= $bouncer->validate('view_workcenter') ? "<li id='nav_workcenter'><a onclick='unloadPage(\"workcenter\")'><i class='zmdi zmdi-receipt m-r-5'></i>Workcenter</a></li>" : null;
        $nav_out .= $bouncer->validate('view_so_list') ? "<li id='nav_so_list'><a onclick='unloadPage(\"so_list\")'><i class='zmdi zmdi-accounts-list m-r-5'></i>SO List</a></li>" : null;
        $nav_out .= $bouncer->validate('view_sales_list') ? "<li id='nav_sales_list'><a onclick='unloadPage(\"sales_list\")'><i class='zmdi zmdi-accounts-list m-r-5'></i>Sales List</a></li>" : null;
        $nav_out .= $bouncer->validate('view_timecards') ? "<li id='nav_timecard'><a><i class='zmdi zmdi-time m-r-5'></i>Timecards</a></li>" : null;

        $nav_out .= '</ul></li>';
    }

    $nav_out .= $bouncer->validate('view_employees') ? "<li id='nav_employees'><a href='employees.php'><i class='zmdi zmdi-account-circle m-r-5'></i><span>Employees</span></a></li>" : null;
    $nav_out .= $bouncer->validate('view_employee_ops') ? "<li id='nav_employee_ops'><a href='?page=user_op_mgmt'><i class='zmdi zmdi-assignment-account m-r-5'></i><span>Employee Ops</span></a></li>" : null;

    if($bouncer->validate('view_docs')) {
      $nav_out .= <<<HEREDOC
        <li class='has-submenu'>
            <a><i class='zmdi zmdi-collection-pdf'></i>Documents</a>
            <ul class='submenu'>
                <li id='#'><a href='/assets/pdf/vin_sheet.pdf' target="_blank"><i class='zmdi zmdi-globe-alt m-r-5'></i>VIN Sheet</a></li>
                <li id='#'><a href='/assets/pdf/dealer_sheet.pdf' target="_blank"><i class='zmdi zmdi-pin-account m-r-5'></i>Dealer Sheet</a></li>
                <!--<li id='#'><a href='/assets/pdf/preprod_checklist.pdf' target="_blank"><i class='fa fa-list m-r-5'></i>Pre-production Checklist</a></li>-->
                <li id='#'><a href='/assets/pdf/global_upcharges.pdf' target="_blank"><i class='fa fa-dollar m-r-5'></i>Global Upcharges</a></li>
                <li id='#'><a href='/assets/pdf/rsi_doc.pdf' target="_blank"><i class='fa fa-home m-r-5'></i>Room/Sequence/Iteration</a></li>
                <li id='#'><a href='/assets/pdf/warranty.pdf' target="_blank"><i class='fa fa-dropbox m-r-5'></i>Warranty</a></li>
            </ul>
        </li>
HEREDOC;
    }

    echo $nav_out;
    ?>
</ul>