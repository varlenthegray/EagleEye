<?php
require ("../includes/header_start.php");

outputPHPErrs();

$target_dir = SITE_ROOT . "/attachments/";
$target_ext = end(explode(".", $_FILES['attachment']['name']));
$target_file = $target_dir . "test." . $target_ext;
$uploadOK = true;
$upload_err = '';
$fileType = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);

if($fileType !== 'pdf') {
    $uploadOK = false;
    $upload_err .= "Incorrect Filetype. PDF, JPG, PNG, or JPEG only. Received $fileType.";
}

if(file_exists($target_file)) {
    $uploadOK = false;
    $upload_err .= "File already exists on the server.";
}

if(move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
    echo displayToast("success", "Uploaded file successfully.", "File Uploaded");
} else {
    echo displayToast("error", "Unable to upload file. $upload_err", "File Error");
}
/*
$excluded_ops = '89,';

$rooms_qry = $dbconn->query("SELECT * FROM rooms WHERE individual_bracket_buildout LIKE '%$excluded_ops%'");

if($rooms_qry->num_rows > 0) {
    while($rooms = $rooms_qry->fetch_assoc()) {
        $first_slice = explode($excluded_ops, $rooms['individual_bracket_buildout']);

        //$middle_slice = "110,111,112,113,114,115,19";

        $final_slice = $first_slice[0] . $first_slice[1];

        $dbconn->query("UPDATE rooms SET individual_bracket_buildout = '$final_slice' WHERE id = '{$rooms['id']}'");

        echo "{$rooms['id']} has been updated to $final_slice.<br />";
    }
}

echo "<h4>Update completed.</h4>";*/
