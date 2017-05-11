<?php
$server = explode(".", $_SERVER['HTTP_HOST']);

if($server[0] === 'dev-smc') {
    echo "<style>body {background-color: #B913EB !important;}</style>";
}