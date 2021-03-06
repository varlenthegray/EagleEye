<?php
require_once("language.php"); // require the language file once
require_once("config.php"); // require the config file once
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
    <meta name="author" content="Coderthemes">

    <!-- App Favicon -->
    <link rel="shortcut icon" href="/assets/images/favicon.ico">

    <!-- App title -->
    <title><?php echo TAB_TEXT; ?></title>

    <!-- App CSS -->
    <link href="/assets/css/style.css" rel="stylesheet" type="text/css"/>

    <!-- JQuery Prep -->
    <script src="/assets/js/jquery.min.js"></script>

    <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script src="/assets/js/modernizr.min.js"></script>

    <!-- Toastr setup -->
    <script src="/assets/plugins/toastr/toastr.min.js"></script>
    <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

    <!-- Global Functions JS -->
    <script src="/includes/js/functions.js"></script>

    <?php
        $server = explode(".", $_SERVER['HTTP_HOST']);

        if($server[0] === 'dev-smc') {
            echo "<style>
                    body {
                        background-color: #750909 !important;
                    }
                    
                    .account-pages {
                        background-color: #750909 !important;
                    }
                </style>";
        }
    ?>
</head>


<body>

