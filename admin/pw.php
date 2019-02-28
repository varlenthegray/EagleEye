<?php
$ri = [];

// https://github.com/first20hours/google-10000-english/blob/master/google-10000-english-usa-no-swears-medium.txt
$file = 'google-10000-english-usa-no-swears-medium.txt';

// Convert the text fle into array and get text of each line in each array index
$file_arr = file($file);

// Total number of lines in file
$num_lines = count($file_arr);

// Getting the last array index number
$last_arr_index = $num_lines - 1;

// Random index number
for($i = 0; $i < 3; $i++) {
  $ri[] = mt_rand(0, $last_arr_index);
}

// random ending number
$rand_num = mt_rand(0, 9);

// separator
$separator = '-';

// random text from a line. The line will be a random number within the indexes of the array
foreach($ri AS $rand) {
  $rand_text .=  $file_arr[$rand] . $separator;
}

// parsing the end result string
$rand_text = str_replace(array("\n","\r", PHP_EOL), '', $rand_text) . $rand_num;

$db_pw = password_hash($rand_text, PASSWORD_DEFAULT);

$pin = substr(str_shuffle('0123456789'), 0, 4);

// outputting the final result
echo "[PIN: $pin] [PW: $rand_text] = $db_pw";