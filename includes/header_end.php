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

    <!-- Toastr setup -->
    <script src="/assets/plugins/toastr/toastr.min.js"></script>
    <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

    <!-- Global JS functions -->
    <script src="/includes/js/functions.js"></script>

    <!-- Switchery css -->
    <link href="/assets/plugins/switchery/switchery.min.css" rel="stylesheet" />

    <!-- App CSS -->
    <link href="/assets/css/style.css?date=112720171556" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <!-- Modernizr js -->
    <script src="/assets/js/modernizr.min.js"></script>

    <!-- Full Calendar-->
    <link href="/assets/plugins/fullcalendar/dist/fullcalendar.min.css" rel="stylesheet" />

    <!-- SocketIO -->
    <script src="/server/node_modules/socket.io-client/dist/socket.io.js"></script>

    <!-- datatables -->
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables/dataTables.buttons.min.js"></script>
    <script src="/assets/plugins/datatables/buttons.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables/jszip.min.js"></script>
    <script src="/assets/plugins/datatables/pdfmake.min.js"></script>
    <script src="/assets/plugins/datatables/vfs_fonts.js"></script>
    <script src="/assets/plugins/datatables/buttons.html5.min.js"></script>
    <script src="/assets/plugins/datatables/buttons.print.min.js"></script>
    <script src="/assets/plugins/datatables/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.2.0/js/dataTables.rowReorder.min.js"></script>

    <!-- Responsive examples -->
    <script src="/assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatables/responsive.bootstrap4.min.js"></script>

    <!-- DataTables -->
    <link href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.0/css/rowReorder.dataTables.min.css"/>

    <!-- Moment.js for Timekeeping -->
    <script src="/assets/plugins/moment/moment.js"></script>

    <!-- Intro (for tutorials) -->
    <script src="/assets/plugins/intro/intro.min.js"></script>
    <link href="/assets/plugins/intro/introjs.min.css" rel="stylesheet" type="text/css"/>

    <!-- Tablesaw-->
    <link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

    <!-- Date Picker -->
    <link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

    <!-- daterange -->
    <link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- Jquery filer css -->
    <link href="/assets/plugins/jquery.filer/css/jquery.filer.css" rel="stylesheet" />
    <link href="/assets/plugins/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css" rel="stylesheet" />

    <!-- Alert Windows -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>

    <script>
        var userTZ = '<?php echo $_SESSION['userInfo']['timezone']; ?>';

        setInterval(function() {
            $.ajax({
                cache: false,
                type: "POST",
                url: "/ondemand/session_continue.php"
            })
        }, 600000);

        jconfirm.defaults = {
            title: "Leaving without saving!",
            content: "You have unsaved changes, do you wish to proceed?",
            type: 'orange',
            typeAnimated: true,
            theme: 'supervan'
        };

        //var socket = io.connect({secure: true});
    </script>

    <?php
        $server = explode(".", $_SERVER['HTTP_HOST']);

        if($server[0] === 'dev') {
            echo "<style>
                        body {
                            background-color: #750909 !important;
                        }
                        
                        .account-pages {
                            background-color: #750909 !important;
                        }
                        
                        #topnav .topbar-main {
                            background-color: #750909 !important;
                        }
                        
                        .footer {
                            background-color: #750909 !important;
                        }
                    </style>";
        } else {
            echo "<script>$.fn.dataTable.ext.errMode = 'throw';</script>";
        }

        if(!empty($error_display)) {
            echo $error_display;
        }

        if(stristr($_SERVER["REQUEST_URI"],  'inset_sizing.php')) {
            echo '<link href="/assets/css/inset_sizing.css" rel="stylesheet">';
            echo '<script src="/assets/plugins/math.min.js"></script>';
        }
    ?>
</head>

<body>
    <?php require 'topbar.php'; ?>