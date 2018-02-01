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

        <!-- Global JS functions -->
        <script src="/includes/js/functions.js?v=<?php echo VERSION; ?>"></script>
        <link href="https://fonts.googleapis.com/css?family=Patua+One" rel="stylesheet">

        <!-- App CSS -->
        <link href="/assets/css/style.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <!-- Modernizr js -->
        <script src="/assets/js/modernizr.min.js"></script>

        <!-- SocketIO -->
        <script src="/server/node_modules/socket.io-client/dist/socket.io.js"></script>

        <!-- Toastr setup -->
        <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

        <!-- Datatables -->
        <link href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.0/css/rowReorder.dataTables.min.css"/>

        <!-- Date Picker -->
        <link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

        <!-- Alert Windows -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">

        <?php
        $server = explode(".", $_SERVER['HTTP_HOST']);

        if($server[0] === 'dev') {
            echo "<style>body, html, .account-pages, #topnav .topbar-main, .footer {background-color: #750909 !important; }</style>";
        } else {
            echo "<script>$.fn.dataTable.ext.errMode = 'throw';</script>";
        }

        if(stristr($_SERVER["REQUEST_URI"],  'inset_sizing.php')) {
            echo '<link href="/assets/css/inset_sizing.css" rel="stylesheet">';
        }
        ?>

        <link href="/display/css/fin_prod.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
    </head>

    <body>
    <div id="server_failure">
        <h1>Server down for maintenance. Please contact IT.</h1>
    </div>

    <!-- Navigation Bar-->
    <header id="topnav">
        <div class="custom-logo hidden-print">
            <div id="header_container">
                <div id="header_main">EagleEye ERP <div id="header_min">www.3erp.us</div></div>
            </div>

            <div id="slogan">"The all seeing eye in the cloud"</div>
        </div>

        <div id="clock"></div>

        <!-- fake fields are a workaround for chrome autofill getting the wrong fields (such as search) -->
        <input class="ignoreSaveAlert" type="text" name="ausernameidontcareabout" style="display:none;">
        <input class="ignoreSaveAlert" type="password" name="apasswordidontcareabout" style="display:none;">

        <div class="topbar-main hidden-print">
            <div class="container">
                <!-- LOGO -->
                <div class="topbar-left">
                    <a href="/index.php" class="logo">
                        <i class="zmdi zmdi-group-work icon-c-logo"></i>
                        <span><?php echo LOGO_TEXT; ?></span>
                    </a>
                </div>
                <!-- End Logo container-->

                <div class="clearfix"></div>
            </div> <!-- end container -->
        </div>
        <!-- end topbar-main -->

        <div class="navbar-custom">
            <div class="container">
                <div id="navigation">
                    <!-- Navigation Menu-->
                    <?php //require_once("../../includes/nav_menu.php"); ?>
                    <!-- End navigation menu -->
                </div>
            </div>
        </div>

        <div class="js_loading"><i class='fa fa-3x fa-spin fa-spinner'></i></div>
    </header>
    <!-- End Navigation Bar-->

    <div class="wrapper">
        <div class="container">
            <div class="col-md-12" id="main_display">
                <div class="row">
                    <div class="col-md-12">
                        <div id="main_body">
                            <div class="card-box table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>SO #</th>
                                        <th>Customer</th>
                                        <th>Finish</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Dry Time</th>
                                        <th>Comments</th>
                                    </tr>



                                    <tr>
                                        <td>123</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer text-right">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-6 pull-left">
                            <?php echo date("Y"); ?> &copy; <?php echo FOOTER_TEXT; ?>
                        </div>

                        <div class="col-xs-6 pull-right text-md-right"><?php echo "RELEASE DATE " . RELEASE_DATE; ?></div>
                    </div>

                    <div class="global-feedback"></div>
                </div>
            </footer>
            <!-- End Footer -->
        </div> <!-- container -->
    </div> <!-- End wrapper -->

    <script>
        // Connect to the socket to begin transmission of data
        <?php if($server[0] === 'dev') {
            echo "var socket = io.connect('//dev.3erp.us:4000');";
        } else {
            echo "var socket = io.connect('//3erp.us:4100');";
        } ?>

        // -- Socket Handling --
        socket.on("connect", function() {
            $("#server_failure").hide();
            $(".js_loading").hide();
        });

        socket.on("err", function(e) {
            $.alert({
                title: 'An error has occurred!',
                content: e,
                buttons: {
                    cancel: function() {}
                }
            });
        });

        socket.on("catchQueueUpdate", function() {

        });

        socket.on("disconnect", function() {
            $("#server_failure").show();
        });

        socket.on("catchRefresh", function() {
            location.reload();
        });
        // -- End of Socket Handling --

        $(function() {
            $("#clock").html(getLocalTime);

            setInterval(function() {
                $("#clock").html(getLocalTime);
            }, 1000); // clock
        });

        setInterval(function() { // stops the auto-logout
            $.post("/ondemand/session_continue.php");
        }, 450000);
    </script>

    <!-- Global Search loading, required for global search to work -->
    <script src="/ondemand/js/global_search.js?v=<?php echo VERSION; ?>"></script>

    <!-- Adding SO to the system -->
    <script src="/ondemand/js/add_so.js?v=<?php echo VERSION; ?>"></script>

    <!-- jQuery  -->
    <script src="/assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/waves.js"></script>
    <script src="/assets/js/jquery.nicescroll.js"></script>

    <!-- Toastr setup -->
    <script src="/assets/plugins/toastr/toastr.min.js"></script>
    <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

    <!-- Datatables -->
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables/vfs_fonts.js"></script>
    <script src="/assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatables/responsive.bootstrap4.min.js"></script>

    <!-- Moment.js for Timekeeping -->
    <script src="/assets/plugins/moment/moment.js"></script>

    <!-- Alert Windows -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>

    <!-- Mask -->
    <script src="/assets/plugins/jquery.mask.min.js"></script>

    <!-- Counter Up  -->
    <script src="/assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
    <script src="/assets/plugins/counterup/jquery.counterup.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/jquery.core.js"></script>
    <script src="/assets/js/jquery.app.js"></script>

    <!-- Tinysort -->
    <script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

    <!-- Input Masking -->
    <script type="text/javascript" src="/assets/plugins/jquery.mask.min.js"></script>

    <!-- Datepicker -->
    <script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <!-- JScroll -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js"></script>

    <!-- Math, fractions and more -->
    <script src="/assets/plugins/math.min.js"></script>

    <!-- Unsaved Changes -->
    <script src="/assets/js/unsaved_alert.js?v=<?php echo VERSION; ?>"></script>
    </body>
    </html>
<?php
$dbconn->close();
?>