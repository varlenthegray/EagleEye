<?php
require_once ("../includes/header_start.php");
require ("../assets/php/composer/vendor/autoload.php"); // require carbon for date formatting, http://carbon.nesbot.com/

use Carbon\Carbon; // prep carbon

switch($_REQUEST['action']) {
    case 'update_alerts':
        $color = null;

        $alert_qry = $dbconn->query("SELECT * FROM alerts WHERE alert_user = {$_SESSION['userInfo']['id']} ORDER BY time_created DESC LIMIT 0,5");

        if($alert_qry->num_rows === 0) {
            echo <<<HEREDOC
 <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
    <i class="zmdi zmdi-notifications-none noti-icon"></i>
</a>
<div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-lg" aria-labelledby="Preview">
    <!-- item-->
    <div class="dropdown-item noti-title">
        <h5><small><span class="label label-danger pull-xs-right" id="notification_count">0</span>Notifications</small></h5>
    </div>
    
    <a href="javascript:void(0);" class="dropdown-item notify-item">
        <div class="notify-icon bg-success"><i class="mood"></i></div>
        <p class="notify-details">All caught up!<small class="text-muted">Now</small></p>
    </a>

    <!-- All-->
    <a href="javascript:void(0);" class="dropdown-item notify-item notify-all">
        View All
    </a>

</div>
HEREDOC;
        } else {
            $alerts = null;
            $new_alert = false;
            $count = $alert_qry->num_rows;

            $plural = ($count > 1) ? "s" : null;

            while($alert = $alert_qry->fetch_assoc()) {
                $timestamp = Carbon::createFromTimestamp($alert['time_created']);
                $time_out = $timestamp->diffForHumans();

                $alerts .= "<a href='javascript:void(0);' class='dropdown-item notify-item' id='{$alert['id']}'><div class='notify-icon {$alert['color']}'><i class='{$alert['icon']}'></i></div><p class='notify-details'>{$alert['message']}<small class='text-muted'>$time_out</small></p></a>\n\n";

                $new_alert = (empty($alert['time_viewed'])) ? true : false;
            }

            $class_info = ($new_alert === true) ? "zmdi zmdi-notifications-active noti-icon wiggler" : "zmdi zmdi-notifications-none noti-icon";

            echo <<<HEREDOC
<a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
    <i class="$class_info"></i>
</a>
<div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-lg" aria-labelledby="Preview">
    <!-- item-->
    <div class="dropdown-item noti-title">
        <h5><small>Notification$plural</small></h5>
    </div>
    
    $alerts

    <!-- All-->
    <a href="javascript:void(0);" class="dropdown-item notify-item notify-all">
        View All
    </a>
</div>
HEREDOC;
        }

        break;
    case 'viewed_alerts':
        $dbconn->query("UPDATE alerts SET time_viewed = UNIX_TIMESTAMP() WHERE alert_user = {$_SESSION['userInfo']['id']}");

        break;
}