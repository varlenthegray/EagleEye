<?php
require ("../includes/header_start.php");
require("../includes/classes/mail_handler.php");

outputPHPErrs();

$mail = new \MailHandler\mail_handler();

$from = 'ben@smcm.us';
$to = 'ben@smcm.us';


$headers = "From: " . strip_tags($from) . "\r\n";
$headers .= "Reply-To: ". strip_tags($from) . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

$message_out = '<!DOCTYPE html><html lang="en">';

$message_out .= <<<HEADER
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <style>
        @page {margin:5mm 8mm 5mm 8mm;}
        body, html {padding:0;margin:0;font-size:8pt;font-family:'Roboto', sans-serif;}
        a, a:visited {color:#000000;text-decoration:none;font-style:italic;}  
        a:hover {text-decoration:underline;}

        .wrapper {width:70%;margin:0 auto;padding-top:5px;}
        .logo {float:right;}
        .header-text {float:left;font-size:2.5em;font-weight:bold;text-decoration:underline;padding-top:25px;}
        .clearfix {clear:both;}
        .main-window {margin-top:10px;}
        .main-window .header {font-size:1.5em;font-weight:bold;}
        .main-window .information {margin-top:8px;}
        .main-window .signature {padding:25px 0 0 10px;}
        .footer {margin-top:25px;font-size:.8em;}
    </style>
</head>
HEADER;

$message_out .= <<<BODY
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-text">EagleEye Systems</div>

        <div class="logo"><img src="https://3erp.us/assets/images/logo.png" width="150px" /></div>

        <div class="clearfix"></div>
    </div>

    <div class="main-window">
        <div class="header">$subject</div>

        <div class="information">
            $message
        </div>

        <div class="signature">
            Sincerely,<br />
            <br />
            EagleEye
        </div>
    </div>

    <div class="footer">
        <div class="copyright"><a href="https://3erp.us/">Copyright &copy; 2017 - EagleEye</a></div>
    </div>
</div>
</body>
</html>
BODY;

return mail($to, $subject, $message_out, $headers);