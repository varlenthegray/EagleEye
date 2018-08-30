<?php
require 'includes/header_start.php';
require 'assets/php/composer/vendor/autoload.php'; // require carbon for date formatting, http://carbon.nesbot.com/

$version = '2.1.02';

use Carbon\Carbon; // prep carbon

if($_SESSION['userInfo']['account_type'] > 4) {
  unset($_SESSION['shop_user'], $_SESSION['shop_active']);
}
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
    <script src="/includes/js/functions.js?v=<?php echo $version; ?>"></script>

    <!-- App CSS -->
    <link href="/assets/css/style.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css" />
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

    <!-- Alert Windows -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">

    <?php
    $server = explode('.', $_SERVER['HTTP_HOST']);

    if($server[0] === 'dev') {
      echo '<style>body, html, .account-pages, #topnav .topbar-main, .footer {background-color: #750909 !important; }</style>';
    } else {
      echo "<script>$.fn.dataTable.ext.errMode = 'throw';</script>";
    }

    if(false !== stripos($_SERVER['REQUEST_URI'], 'inset_sizing.php')) {
      echo '<link href="/assets/css/inset_sizing.css" rel="stylesheet">';
    }
    ?>
  </head>

  <body>
  <!-- Navigation Bar-->
  <header id="topnav">
    <div class="custom-logo hidden-print" style="margin-right:130px;position:reliatve;z-index:2;margin-top:-2px;">
      <img src="../assets/images/logo_new.png" height="135px" />
    </div>

    <!-- fake fields are a workaround for chrome autofill getting the wrong fields (such as search) -->
    <input class="ignoreSaveAlert" type="text" name="ausernameidontcareabout" style="display:none;">
    <input class="ignoreSaveAlert" type="password" name="apasswordidontcareabout" style="display:none;">

    <div class="topbar-main hidden-print">
      <div class="container">
        <!-- LOGO -->
        <div class="topbar-left">
          <a href="/main.php" class="logo">
            <i class="zmdi zmdi-group-work icon-c-logo"></i>
            <span><?php echo LOGO_TEXT . ' - Employee Selection'; ?></span>
          </a>
        </div>
        <!-- End Logo container-->

        <div class="menu-extras">
          <ul class="nav navbar-nav pull-left">
            <li class="nav-item">
              <!-- Mobile menu toggle-->
              <a class="navbar-toggle">
                <div class="lines">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </a>
              <!-- End mobile menu toggle-->
            </li>
          </ul>
        </div> <!-- end menu-extras -->

        <div id="clock" style="z-index:999;color:#000;position:absolute;top:40px;right:-110px;"></div>

        <div class="clearfix"></div>
      </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->

    <div class="navbar-custom">
      <div class="container">
        <div id="navigation">
          <!-- Navigation Menu-->
          <ul class="navigation-menu">
            <?php
            if($_SESSION['userInfo']['account_type'] <= 4) {
              include_once 'includes/nav_menu.php';
            } else {
              echo "<li id='nav_logout'><a href='/login.php?logout=true'><i class='fa fa-sign-out m-r-5'></i><span>" . NAV_LOGOUT . '</span></a></li>';
            }
            ?>
          </ul>
          <!-- End navigation menu  -->
        </div>
      </div>
    </div>

    <div class="js_loading"><i class='fa fa-3x fa-spin fa-spinner'></i></div>
  </header>
  <!-- End Navigation Bar-->

  <div class="wrapper">
    <div class="container">
      <div class="col-md-6">
        <div class="card-box">
          <div class="row" id="default_login_form">
            <div class="col-md-12">
              <table id="individual_login" class="table table-striped table-bordered" width="100%">
                <thead>
                <tr>
                  <th style="min-width:125px;">Employee</th>
                  <th style="min-width:75px;">Last Login</th>
                  <th>Current Operations</th>
                  <th style="min-width:100px;">Time Clocked In</th>
                  <?php
                  if($_SESSION['userInfo']['account_type'] <= 4) {
                    echo "<th width='70px'>Clock Out</th>";
                  }
                  ?>
                </tr>
                </thead>
                <tbody id="room_search_table">
                <?php
                if($_SESSION['userInfo']['account_type'] <= 4) {
                  $qry = $dbconn->query('SELECT * FROM user WHERE account_status = TRUE AND display = TRUE ORDER BY name ASC;');
                } else {
                  $qry = $dbconn->query('SELECT * FROM user WHERE account_status = TRUE AND display = TRUE AND id != 7 AND id != 8 AND id != 1 ORDER BY name ASC;');
                }

                while($result = $qry->fetch_assoc()) {
                  if($result['id'] !== '16') {
                    $last_login_qry = $dbconn->query("SELECT * FROM timecards WHERE employee = {$result['id']} ORDER BY time_in DESC LIMIT 0,1");

                    if($last_login_qry->num_rows > 0) {
                      $last_login = $last_login_qry->fetch_assoc();

                      if($last_login['time_in'] > strtotime('today')) {
                        $time = date(DATE_TIME_ABBRV, $last_login['time_in']);
                      } else {
                        $time = date(DATE_TIME_ABBRV, $last_login['time_in']);
                      }

                      if(empty($last_login['time_out'])) {
                        $time_unix = $last_login['time_in'];

                        if(!empty($time_unix)) {
                          $carbon_time = Carbon::createFromTimestamp($time_unix);
                          $time_in_display = $carbon_time->diffForHumans(null, true);
                        } else {
                          $time_in_display = 'Never logged in';
                        }
                      } else {
                        $time_in_display = 'Not Logged In';
                      }
                    } else {
                      $time = 'Never';
                      $time_unix = null;
                    }

                    $ops_qry = $dbconn->query("SELECT * FROM op_queue LEFT JOIN operations ON op_queue.operation_id = operations.id LEFT JOIN rooms ON op_queue.room_id = rooms.id WHERE active_employees LIKE '%\"{$result['id']}\"%' AND active = TRUE");

                    $final_ops = '';

                    if($ops_qry->num_rows > 0) {
                      while($ops = $ops_qry->fetch_assoc()) {
                        if(!empty($ops['so_parent']) && !empty($ops['room'])) {
                          $so_info = "{$ops['so_parent']}{$ops['room']} - ";
                        } else {
                          $so_info = null;
                        }

                        if($ops['job_title'] === 'Non-Billable') {
                          $subtask = " ({$ops['subtask']})";
                        } else {
                          $subtask = null;
                        }

                        $operation = $so_info . $ops['op_id'] . ': ' . $ops['job_title'] . $subtask;
                        $final_ops .= $operation . ', ';
                      }
                    } else {
                      $final_ops = 'None';
                    }

                    $final_ops = rtrim($final_ops, ', ');

                    $clock_out_btn = null;

                    echo "<tr class='cursor-hand login' data-login-id='{$result['id']}' data-login-name='{$result['name']}'>";
                    echo "<td>{$result['name']}</td>";
                    echo "<td>$time</td>";
                    echo "<td>$final_ops</td>";
                    echo "<td>$time_in_display</td>";

                    if($_SESSION['userInfo']['account_type'] <= 4) {
                      echo "<td><button class='btn btn-primary waves-effect waves-light btn-sm clock_out' data-id='{$result['id']}'>Clock Out</button></td>";
                    }

                    echo '</tr>';
                  }
                }
                ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- modal -->
      <div id="modalLogin" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLoginLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title" id="modalLoginName">Login As XYZ</h4>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12 text-md-center">
                  <h4>Enter PIN Code</h4>

                  <input type="password" autocomplete="off" name="pin" placeholder="PIN" maxlength="4" id="loginPin" class="text-md-center ignoreSaveAlert">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary waves-effect waves-light" id="clock_in">Clock In</button>
            </div>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->

      <!-- Footer -->
      <footer class="footer text-right">
        <div class="container">
          <div class="row">
            <div class="col-xs-6 pull-left">
              <?php echo date('Y'); ?> &copy; <?php echo FOOTER_TEXT; ?>
            </div>

            <div class="col-xs-6 pull-right text-md-right"><?php echo 'RELEASE DATE ' . RELEASE_DATE; ?></div>
          </div>

          <div class="global-feedback"></div>
        </div>
      </footer>
      <!-- End Footer -->
    </div> <!-- container -->
  </div> <!-- End wrapper -->

  <script>
    // Connect to the socket to begin transmission of data
    var socket = io.connect('//dev.3erp.us:4000');

    socket.on("err", function(e) {
      $.alert({
        title: 'An error has occurred!',
        content: e,
        buttons: {
          cancel: function() {}
        }
      });
    });
    // -- End of Socket Handling --

    setInterval(function() {
      $.post("/ondemand/session_continue.php");
    }, 600000);

    $(function() {
      // -- toastr defaults --
      toastr.options = {
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "showDuration": "300",
        "hideDuration": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
      };

      setInterval(function() {
        var time = new Date();
        time = time.toLocaleTimeString();

        $("#clock").html(time);
      }, 1000); // clock

      $("#modalLogin").on("show.bs.modal", function(e) { // when we're triggering the show event
        var userLine = $(e.relatedTarget); // grab the related line and information associated with it
        var modal = $(this); // set the modal to this specific element

        modal.find('.modal-title').text('Hello ' + userLine.data("login-name")); // find and update the text to the login name from the data line

        userID = userLine.data("login-id");

        $("#loginPin").val(""); // clear out any previous entries/attempts
      }).on("shown.bs.modal", function() { // once the modal form is completely shown
        $("#loginPin").focus(); // set the focus (once the modal is fully painted on the canvas)
      });

      $("#loginPin").on("keypress", function(e) { // each time you press a key in the PIN field
        if(e.keyCode === 13) // if hitting the enter key, do login
          $("#clock_in").trigger("click"); // trigger the clockin button actions
      });

      $(".js_loading").hide();
    });

    $("body")
    // -- Employees --
      .on("click", ".login", function() {
        userID = $(this).data("login-id");

        <?php if($_SESSION['userInfo']['account_type'] > 4) { ?>
        // This was tricky, wtf is with the second variable passed to the object? docs do not explain and the demo shows wrong info
        $("#modalLogin").modal("show", $(this));
        <?php } else { ?>
        $.post("/ondemand/account_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
          if (data === 'success') {
            $(".js_loading").show();

            window.location.replace("main.php");
          } else {
            displayToast("error", "Failed to log in, please try again.", "Login Failure");
            $("#modalLogin").modal('hide');
          }
        });
        <?php } ?>
      })
      .on("click", "#clock_in", function() {
        $.post("/ondemand/account_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
          if (data === 'success') {
            $(".js_loading").show();

            window.location.replace("main.php");
          } else {
            displayToast("error", "Failed to log in, please try again.", "Login Failure");
            $("#modalLogin").modal('hide');
          }
        });
      })
      .on("click", ".clock_out", function(e) {
        var id = $(this).data("id");

        e.stopPropagation();

        $.post("/ondemand/account_actions.php?action=clock_out", {user_id: id}, function(data) {
          $("body").append(data);
        });
      })
    // -- End Employees --
    ;
  </script>

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

  <!-- App js -->
  <script src="/assets/js/jquery.core.js"></script>
  <script src="/assets/js/jquery.app.js"></script>

  <!-- Unsaved Changes -->
  <script src="/assets/js/unsaved_alert.js?v=<?php echo $version; ?>"></script>
  </body>
  </html>
<?php
$dbconn->close();
?>