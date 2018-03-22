<?php
require 'includes/header_start.php';

//outputPHPErrs();
?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ERP Clockout Page">
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

    <!-- App CSS -->
    <link href="/assets/css/style.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <!-- Modernizr js -->
    <script src="/assets/js/modernizr.min.js"></script>

    <!-- Global JS functions -->
    <script src="/includes/js/functions.js?v=<?php echo VERSION; ?>"></script>

    <!-- Toastr setup -->
    <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

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
  </head>

  <body>
  <!-- Navigation Bar-->
  <header id="topnav" style="min-height:0">
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
          <a href="/out.php" class="logo">
            <i class="zmdi zmdi-group-work icon-c-logo"></i>
            <span><?php echo LOGO_TEXT; ?></span>
          </a>
        </div>
        <!-- End Logo container-->

        <div class="clearfix"></div>
      </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->
  </header>
  <!-- End Navigation Bar-->

  <div class="wrapper">
    <div class="container">
      <div class="col-md-12" id="main_display" data-showing="dashboard" data-search="false">
        <div class="row">
          <div class="col-md-3 col-md-offset-4">
            <div id="main_body">
              <div class="card-box">
                <h5>Clock Out</h5>

                <input type="password" id="pin" placeholder="PIN" class="form-control-lg" style="width:100%;" maxlength="4">
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

  <!-- Toastr setup -->
  <script src="/assets/plugins/toastr/toastr.min.js"></script>
  <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

  <script>
    $("body").on("keyup", "#pin", function() {
      var id = $(this).val();

      if(id.length === 4) {
        $.post("/ondemand/account_actions.php?action=pin_out", {pin: id}, function(data) {
          $("body").append(data);
          $("#pin").val('');
        });
      }
    });
  </script>

  </body>
  </html>
<?php
$dbconn->close();
?>