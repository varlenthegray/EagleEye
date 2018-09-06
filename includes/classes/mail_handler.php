<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 10/11/2017
 * Time: 11:09 AM
 */

namespace MailHandler;


class mail_handler {
  public function sendMessage($to, $from, $subject, $message, $null) {
    global $dbconn;

    $headers = "From: EagleEye <dashboard@3erp.us>\r\n";
    $headers .= "Reply-To: ". strip_tags($from) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $message_out = '<!DOCTYPE html><html lang="en">';

    $message_out .= <<<HEADER
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <style>
        @page {margin:5mm 8mm 5mm 8mm;}
        body, html {padding:0;margin:0;font-size:8pt;font-family:'Roboto', sans-serif;}
        a, a:visited {color:#000000;text-decoration:none;font-style:italic;}  
        a:hover {text-decoration:underline;}

        .wrapper {width:100%;margin:0 auto;padding-top:5px;}
        .header-text {font-size:2.5em;font-weight:bold;text-decoration:underline;padding-top:25px;}
        .header-table {border:none;border-collapse:collapse;width:100%;}
        .clearfix {clear:both;}
        .main-window {margin-top:10px;}
        .main-window .header {font-size:1.5em;font-weight:bold;}
        .main-window .information {margin-top:25px;}
        .main-window .signature {padding:25px 0 0 10px;margin:25px 0 0 10px;}
        .footer {margin-top:25px;font-size:.8em;}
    </style>
</head>
HEADER;

    $message_out .= <<<BODY
<body>
<div class="wrapper">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-text">EagleEye Systems</td>
                <td class="logo"><img src="https://3erp.us/assets/images/logo_150.png" /></td>
            </tr>
        </table>

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
        <div class="copyright"><a href="https://3erp.us/">Copyright &copy; 2018 - EagleEye</a></div>
    </div>
</div>
</body>
</html>
BODY;

    // TODO: Fix this so it's based on user ID of the person sent, some people have phones but no email
    $sms_qry = $dbconn->query("SELECT * FROM user WHERE email = '$to'");
    $sms = $sms_qry->fetch_assoc();

    sendText($sms['phone'], "$subject: $message");


    return mail($to, $subject, $message_out, $headers);
  }
}