<ul class="navigation-menu">
    <div role="search" class="navbar-left app-search pull-left hidden-xs" _lpchecked="1">
        <input type="text" placeholder="Search..." class="form-control ignoreSaveAlert" id="global_search" name="global_search_2" autocomplete="off"><a id="global_search_button"><i class="fa fa-search"></i></a>
    </div>
    <li style="border: 1px dotted rgba(0,0,0,.25);height: 42px;"><span></span></li>
    <?php
    $non_shop_1 = '';
    $non_shop_2 = '';
    $non_shop_3 = '';
    $non_shop_4 = '';

    if((int)$_SESSION['userInfo']['account_type'] !== 6) {
        $non_shop_1 = <<<HEREDOC
<li id='nav_add_so'><a><i class='zmdi zmdi-account-add m-r-5'></i><span>Add SO</span></a></li>
<li class='nav-separator'><span></span></li>
HEREDOC;

        $non_shop_2 = "<li id='nav_tasks'><a onclick='unloadPage(\"tasks\")'><i class='zmdi zmdi-check-circle-u m-r-5'></i><span>Tasks</span></a></li>";

        $non_shop_3 = <<<HEREDOC
<li class='has-submenu'>
    <a><i class='zmdi zmdi-assignment'></i>Reports</a>
    <ul class='submenu'>
        <li id='nav_workcenter'><a onclick='unloadPage("workcenter")'><i class='zmdi zmdi-receipt m-r-5'></i>Workcenter</a></li>
        <li id='nav_so_list'><a onclick='unloadPage("so_list")'><i class='zmdi zmdi-accounts-list m-r-5'></i>SO List</a></li>
        <li id='nav_sales_list'><a onclick='unloadPage("sales_list")'><i class='zmdi zmdi-accounts-list m-r-5'></i>Sales List</a></li>
        <li id='nav_timecard'><a><i class='zmdi zmdi-time m-r-5'></i>Timecards</a></li>
    </ul>
</li>
HEREDOC;
    }

    /** @var string $a1 - Admin operations and availability */
    if((int)$_SESSION['userInfo']['account_type'] <= 4) $a1 = "<li id='nav_employee_ops'><a href='?page=user_op_mgmt'><i class='zmdi zmdi-assignment-account m-r-5'></i><span>Employee Ops</span></a></li>";

    echo <<<HEREDOC
$non_shop_1
<li id='nav_dashboard'><a href='/index.php'><i class='zmdi zmdi-view-dashboard m-r-5'></i><span>Dashboard</span></a></li>
<li class='has-submenu'>
    <a><i class='zmdi zmdi-account'></i>Account</a>
    <ul class='submenu'>
        <li id='nav_logout'><a class='clock_out' data-id='{$_SESSION['shop_user']['id']}'><i class='zmdi zmdi-time-countdown m-r-5'></i>Clock Out</a></li>
        <li id='nav_logout'><a href='/login.php?logout=true'><i class='fa fa-sign-out m-r-5'></i>Log Out</a></li>
    </ul>
</li>
<li class='nav-separator'><span></span></li>
<li id='nav_feedback'><a data-toggle='modal' data-target='#feedback-page'><i class='fa fa-comment-o m-r-5'></i><span>Feedback</span></a></li>
$non_shop_2
<li class='nav-separator'><span></span></li>
$non_shop_3
<li id='nav_employees'><a href='employees.php'><i class='zmdi zmdi-account-circle m-r-5'></i><span>Employees</span></a></li>
$a1
HEREDOC;
    ?>
</ul>