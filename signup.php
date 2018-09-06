<?php
// PHPMailer: https://github.com/PHPMailer/PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$email = $_REQUEST['email'];
$phone = $_REQUEST['phone'];

$message = <<<HEREDOC
<p>A new phone consultation has been requested:</p>

<p>Email Address: $email<br />
Phone Number: $phone</p>

<p>With Great Regard,<br />
EagleEye Sales Site</p>
HEREDOC;

$mail = new PHPMailer(true);                              // Passing `true` enables exceptions

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
  $mail->setFrom('eagleeye@smcm.us', 'EagleEye Orders');
  $mail->addAddress('orders@smcm.us', 'SMC Orders');     // Add a recipient

  //Content
  $mail->isHTML(true);                                  // Set email format to HTML
  $mail->Subject = 'New EagleEye Phone Consultation Request';
  $mail->Body    = $message;
  $mail->AltBody = $message;

  $mail->send();
  echo 'Message has been sent';
} catch (Exception $e) {
  echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
}

/*if(mail('ben@smcm.us', 'New EagleEye Phone Consultation Request', $message)) {
  echo 'Mail message sent.';
} else {
  echo 'Mail message not sent.';
}*/