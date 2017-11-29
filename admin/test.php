<?php
require ("../includes/header_start.php");
require("../includes/classes/mail_handler.php");
require ("../includes/classes/queue.php");

//outputPHPErrs();

$queue = new \Queue\queue();

$id = 8855;
$notes = "Test Close";
$rw_reqd = false;
$rw_reason = null;
$opnum = 150;

$queue->stopOp($id, $notes, $rw_reqd, $rw_reason, $opnum);