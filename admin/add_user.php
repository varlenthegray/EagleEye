<?php
require ("../includes/header_start.php");

if((bool)$_REQUEST['add_usr']) {
    if($_SESSION['userInfo']['account_type'] === '1') {
        $username = strtolower(sanitizeInput($_REQUEST['username']));
        $password = $_REQUEST['password'];
        $email = sanitizeInput($_REQUEST['email']);
        $phone = sanitizeInput($_REQUEST['phone']);
        $account_type = sanitizeInput($_REQUEST['account_type']);
        $name = sanitizeInput($_REQUEST['name']);
        $pin = sanitizeInput($_REQUEST['pin']);
        $default_queue = sanitizeInput($_REQUEST['default_queue']);
        $dealer = sanitizeInput((bool)$_REQUEST['dealer']);

        $usr_qry = $dbconn->query("SELECT * FROM user WHERE username = '$username'");

        if($usr_qry->num_rows === 0) {
            $pw = password_hash($password, PASSWORD_DEFAULT);

            switch($account_type) {
                case 5:
                    $dept_list = '["Assembly","Box","Custom","Design","Finishing","Production Administrator","Shop Foreman","Shipping"]';
                    $perms = 'FALSE,FALSE,FALSE,TRUE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,TRUE,FALSE,TRUE,FALSE,FALSE,FALSE,FALSE,TRUE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,TRUE,TRUE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE,FALSE';
                    break;

                default:
                    $dept_list = '["Accounting","Assembly","Box","Custom","Design","Engineering","Finishing","Production Administrator","Project Manager","Sales Administrator","Shop Foreman","Shipping"]';
                    $perms = 'TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,FALSE,FALSE,FALSE';
                    break;
            }

            if($dealer) {
                $perms = 'FALSE,TRUE,TRUE,FALSE,TRUE,TRUE,TRUE,
                TRUE,TRUE,TRUE,FALSE,FALSE,FALSE,FALSE,TRUE,FALSE,
                TRUE,FALSE,TRUE,FALSE,TRUE,TRUE,TRUE,FALSE,FALSE,FALSE,
                FALSE,TRUE,TRUE,FALSE,FALSE,FALSE,TRUE,FALSE,TRUE,TRUE,TRUE,
                TRUE,TRUE,TRUE,TRUE,TRUE,FALSE,TRUE,FALSE,FALSE,FALSE,TRUE,FALSE,
                TRUE,TRUE,TRUE,FALSE,TRUE,TRUE,TRUE';

                $dbconn->query("INSERT INTO user (username, password, email, phone, last_login, account_type, account_status, department, name, 
            shift, pin_code, timezone, auto_clock, hourly, pref_filters, perm_full_dashboard, dealer) 
            VALUES ('$username', '$pw', '$email', '$phone', '1999-01-01 00:00:00', 4, 1, '[]', '$name', 1, '$pin-', 'America/New_York', 
            1, 1, 1, 1, 1);");
            } else {
                $dbconn->query("INSERT INTO user (username, password, email, phone, last_login, account_type, account_status, department, name, 
            shift, pin_code, timezone, default_queue, auto_clock, hourly, pref_filters, perm_full_dashboard, dealer) 
            VALUES ('$username', '$pw', '$email', '$phone', '1999-01-01 00:00:00', '$account_type', 1, '$dept_list', '$name', 1, '$pin', 'America/New_York', 
            '$default_queue', 1, 1, 1, 1, 0);");
            }

            $insert_id = $dbconn->insert_id;

            $dbconn->query("INSERT INTO permissions (user_id, impersonate, view_quotes, view_orders, view_operation, view_so, add_room, add_iteration, 
        add_sequence, add_attachment, print_sample, print_coversheet, print_exec_coversheet, print_shop_coversheet, print_sample_label, copy_vin, view_inset_sizing, 
        view_appliance_ws, view_preprod_checklist, view_globals, view_accounting, view_vin, view_so_notes, view_room_notes, view_audit_log, view_brackets, view_tasks, 
        view_workcenter, view_so_list, view_sales_list, view_timecards, view_employees, view_employee_ops, add_feedback, add_so, search, edit_quotes, edit_orders, 
        edit_operation, edit_so, edit_room, edit_appliance_ws, edit_globals, edit_accounting, edit_vin, edit_brackets, update_tasks, edit_employee_ops, login, clock_out, 
        view_all_so, view_attachments, view_rooms, change_order_status, view_dealer_status, submit_quote, add_project) VALUES ($insert_id, " . $perms . ");");

            echo "<h1 style='color:darkgreen;'>Success: User Created</h1>";
        } else {
            echo "<h1 style='color:red;'>Error: Username already exists</h1>";
        }
    }
}
?>

<html>
<body>
<!-- fake fields are a workaround for chrome autofill getting the wrong fields (such as search) -->
<input class="ignoreSaveAlert" type="text" name="ausernameidontcareabout" style="display:none;">
<input class="ignoreSaveAlert" type="password" name="apasswordidontcareabout" style="display:none;">

<form action="?add_usr=true" method="post">
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" />
    </div>

    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" />
    </div>

    <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" />
    </div>

    <div>
        <label for="phone">Phone</label>
        + <input type="text" name="phone" id="phone" maxlength="11" />
    </div>

    <div>
        <label for="account_type">Account Type</label>
        <select name="account_type" id="account_type">
            <option value="1">Super Admin</option>
            <option value="2">Admin</option>
            <option value="4">General</option>
            <option value="5">Shop Employee</option>
        </select>
    </div>

    <div>
        <label for="name">Name</label>
        <input type="text" name="name" id="name" />
    </div>

    <div>
        <label for="pin">PIN</label>
        <input type="password" name="pin" id="pin" maxlength="4" />
    </div>

    <div>
        <label for="default_queue">Default Queue</label>
        <select name="default_queue" id="default_queue">
            <?php
            $queue_qry = $dbconn->query("SELECT DISTINCT bracket FROM operations WHERE bracket != 'Non-Billable' AND bracket != 'Special';");

            while($queue = $queue_qry->fetch_assoc()) {
                echo "<option value='{$queue['bracket']}'>{$queue['bracket']}</option>";
            }
            ?>
        </select>
    </div>

    <div>
        <label for="dealer">Dealer</label>
        <input type="checkbox" name="dealer" id="dealer" value="1" />
    </div>

    <div>
        <input type="submit" />
    </div>
</form>
</body>
</html>