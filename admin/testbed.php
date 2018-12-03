<?php
require '../includes/header_start.php';

//outputPHPErrs();

$vin_schema = getVINSchema();

print_r($vin_schema);