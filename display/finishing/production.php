<?php
require '../../includes/header_start.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A fully functional ERP designed to manage cabinetry and automation.">
    <meta name="author" content="Stone Mountain Cabinetry & Millwork">

    <!-- App Favicon -->
    <link rel="shortcut icon" href="/assets/images/favicon.ico">

    <!-- App title -->
    <title><?php echo TAB_TEXT; ?></title>

    <!-- JQuery & JQuery UI -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/includes/js/jquery-ui.min.js"></script>
    <link href="/includes/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>

    <link href="https://fonts.googleapis.com/css?family=Patua+One" rel="stylesheet">
    <link href="/display/css/style.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />

    <?php
    $server = explode(".", $_SERVER['HTTP_HOST']);

    if($server[0] === 'dev') {
        echo "<style>body, html, .account-pages, #topnav .topbar-main, .footer {background-color: #750909 !important; }</style>";
    } else {
        echo "<script>$.fn.dataTable.ext.errMode = 'throw';</script>";
    }
    ?>
</head>

<body>
<h1>Work Orders & Service Work (Priority)</h1>

<table class="std-table">
    <tr>
        <th>SO #</th>
        <th>Customer Name</th>
        <th>Finish Code</th>
        <th>Sample Request</th>
        <th>Finishing Start</th>
        <th>Finishing End</th>
        <th>Expected Dry</th>
        <th>Notes</th>
    </tr>
    <tr>
        <td>629</td>
        <td>Evans</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
</table>
</body>
</html>