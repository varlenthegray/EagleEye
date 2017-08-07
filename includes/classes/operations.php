<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 8/7/2017
 * Time: 8:24 AM
 */

namespace Operation;

require_once("../header_start.php");

class operations {
    private $room;
    private $new_op;
    private $cur_op;

    // setters, things that may not always be used

    function setNewOp($new_operation) {
        $this->new_op = $new_operation;
    }

    function setCurOp($current_operation) {
        $this->cur_op = $current_operation;
    }

    function setRoom($db_room_results) {
        $this->room = $db_room_results;
    }

    // end setters

    function createNextOp() {
        // grab the entire bracket
        $bracket = json_decode($this->room['individual_bracket_buildout']);

        // find the current operation in the bracket, if provided

    }
}