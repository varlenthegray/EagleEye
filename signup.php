<?php

$email = $_REQUEST['email'];
$phone = $_REQUEST['phone'];

$message = <<<HEREDOC
A new phone consultation has been requested:

Email Address: $email
Phone Number: $phone

With Great Regard,
EagleEye Sales Site
HEREDOC;


mail('it@smcm.us', 'New Phone Consultation Request', $message);