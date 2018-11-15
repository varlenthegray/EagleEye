<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 10/11/2017
 * Time: 11:09 AM
 */

namespace MailHandler;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require SITE_ROOT . '/vendor/autoload.php';


class mail_handler {
  public function sendMessage($to, $from, $subject, $message, $null) {
    $mail = new PHPMailer(true);

    global $dbconn;

    $message = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"), '<br/> ', $message);

    $message_out = <<<HEADER
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
                <td class="header-text">EagleEye ERP</td>
                <td class="logo"><img src="https://3erp.us/assets/images/mini_logo.png" /></td>
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

    $origin_qry = $dbconn->query("SELECT * FROM user WHERE email = '$from'");
    $origin = $origin_qry->fetch_assoc();

    $smsMsg = strip_tags($message);
    $smsSubject = strip_tags($subject);

    sendText($sms['phone'], "$smsSubject
    
    $smsMsg 
     
Reply To {$origin['phone']}");

    try {
      //Server settings
      $mail->isSMTP();                                      // Set mailer to use SMTP
      $mail->Host = 'smtp.office365.com';  // Specify main and backup SMTP servers
      $mail->SMTPAuth = true;                               // Enable SMTP authentication
      $mail->Username = 'ben@smcm.us';                 // SMTP username
      $mail->Password = 'm90gmo4RlUKYtYgl';                           // SMTP password
      $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
      $mail->Port = 587;                                    // TCP port to connect to

      //Recipients
      $mail->setFrom('eagleeye@smcm.us', 'EagleEye ERP');
      $mail->addAddress($to);     // Add a recipient
      $mail->addReplyTo(strip_tags($from)); // adds reply-to

      //Content
      $mail->isHTML(true);                                  // Set email format to HTML
      $mail->Subject = $subject;
      $mail->Body    = $message_out;
      $mail->AltBody = $message_out;

      $mail->send();
      return true;
    } catch (Exception $e) {
      return 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    }


//    return mail($to, $subject, $message_out, $headers);
  }
}