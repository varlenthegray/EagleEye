<?php

$email = $_REQUEST['signup-email'];
$phone = $_REQUEST['signup-phone'];

$message = <<<HEREDOC
A new phone consultation has been requested:

Email Address: $email
Phone Number: $phone

With Great Regard,
EagleEye Sales Site
HEREDOC;


mail('ben@smcm.us', 'New Phone Consultation Request', $message);