<?php
$source = $_FILES['file']['tmp_name'];
move_uploaded_file( $source, 'aaa.png' );
?>