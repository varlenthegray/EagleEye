<?php
if(mail('beach.ben@gmail.com', 'Test Message', 'This is a test message.')) {
  echo 'Successfully sent test message.';
} else {
  echo 'Unable to send test message.';
}