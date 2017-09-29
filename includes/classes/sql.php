<?php
/**
 * @author Ben Beach <ben@smcm.us>
 * @copyright 2017 Stone Mountain Cabinetry & Millwork
 *
 * Class to handle SQL default actions
 */

namespace SQL;


class sql {
    public $dbconn;

    public function __construct($type) {
        if($type === 'dev') {
            $servername = "localhost";
            $username = "devsmc";
            $password = "9UnI9Tx721FDBRxiOMmCG3Tv";
            $database = "devsmc";
        } elseif($type === 'live') {
            $servername = "localhost";
            $username = "threeerp";
            $password = "8h294et9hVaLvp0K*s!&";
            $database = "3erp";
        }

        $dbconn = new mysqli($servername, $username, $password, $database);
        $dbconn->set_charset('utf8');
    }
}