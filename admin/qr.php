<?php
require '../includes/header_start.php';
require '../assets/php/phpqrcode/qrlib.php';

//set it to writable location, a place for temp generated PNG files
$PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

//html PNG location prefix
$PNG_WEB_DIR = 'temp/';

// create the directory if it doesn't exist
if (!file_exists($PNG_TEMP_DIR)) mkdir($PNG_TEMP_DIR);

// the temp QR code
$filename = $PNG_TEMP_DIR . 'tempQR.png';

$md5 = md5("General information goes here.");

QRcode::png('https://3erp.us/qr.php?c=17685', $filename, QR_ECLEVEL_L, 3, 0);
echo '<img src="' . $PNG_WEB_DIR . basename($filename) . '" />';
