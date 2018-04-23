<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 2/15/2018
 * Time: 2:16 PM
 */

namespace Bouncer;


class bouncer {
    function __construct() {
        global $dbconn;

        if(!empty($_SESSION['userInfo'])) {
            $perm_qry = $dbconn->query("SELECT pg.* FROM user u LEFT JOIN permission_groups pg on u.permission_id = pg.id WHERE u.id = {$_SESSION['userInfo']['id']}");
            $perm = $perm_qry->fetch_assoc();

            $_SESSION['permissions'] = $perm;
        }
    }

    function validate($access) {
        if((bool)$_SESSION['permissions'][$access] === false) {
            return false;
        } else {
            return true;
        }
    }
}