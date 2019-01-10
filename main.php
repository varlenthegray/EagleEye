<?php
require 'includes/header_start.php';

//outputPHPErrs();
?>
  <!DOCTYPE html>
  <html moznomarginboxes mozdisallowselectionprint>
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
    <link href="/assets/css/style.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
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

    <!-- Fancytree -->
    <link rel="stylesheet" type="text/css" href="/assets/plugins/fancytree/skin-win8-n/ui.fancytree.css"/>

    <!-- Datatables -->
    <link href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.0/css/rowReorder.dataTables.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.1.3/css/fixedHeader.dataTables.min.css"/>
    <link href="/assets/plugins/datatables/datatables.paginate.fix.css" rel="stylesheet" type="text/css"/>

    <!-- Date Picker -->
    <link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

    <!-- Alert Windows -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">

    <!-- Select2 -->
    <link href="/assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <script src="/assets/plugins/select2/js/select2.min.js"></script>

    <!-- DHTMLX -->
    <link rel="stylesheet" href="/assets/css/dhtmlx/dhtmlx.min.css" type="text/css">
    <script src="https://cdn.dhtmlx.com/edge/dhtmlx.js" type="text/javascript"></script>

    <?php
    $server = explode('.', $_SERVER['HTTP_HOST']);

    if(false !== stripos($_SERVER['REQUEST_URI'], 'inset_sizing.php')) {
      echo '<link href="/assets/css/inset_sizing.css" rel="stylesheet">';
    }
    ?>
  </head>

  <body>
  <div id="server_failure" style="display:none;"><i class="fa fa-exclamation-triangle"></i> Server Communication Error</div>

  <!-- Navigation Bar-->
  <header id="topnav">
    <div id="clock"></div>

    <!-- fake fields are a workaround for chrome autofill getting the wrong fields (such as search) -->
    <input class="ignoreSaveAlert" type="text" name="ausernameidontcareabout" style="display:none;">
    <input class="ignoreSaveAlert" type="password" name="apasswordidontcareabout" style="display:none;">

    <div class="topbar-main hidden-print">
      <div class="container">
        <!-- LOGO -->
        <div class="topbar-left">
          <a href="/main.php" class="logo">
            <img src="/assets/images/logo.svg" style="max-height:35px;margin-top:-10px;" />
            <span style="margin-left:-15px;"><?php echo LOGO_TEXT; ?></span>
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

            <li class="nav-item notification-list"><a class="nav-link arrow-none waves-light waves-effect" href="/main.php?page=dashboards/index_new" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi zmdi-home noti-icon"></i></a></li>

            <li class="nav-item notification-list"><a class="nav-link arrow-none waves-light waves-effect" href="/main.php?page=mail/cross_page" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi-email noti-icon"></i></a></li>

            <li class="nav-item notification-list"><a class="nav-link arrow-none waves-light waves-effect" href="/main.php?page=calendar/index" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi zmdi-calendar noti-icon"></i></a></li>

            <li class="nav-item dropdown notification-list" id="notification_list">
              <!-- AJAX -->
            </li>
          </ul>
        </div> <!-- end menu-extras -->

        <?php echo $_SERVER['SERVER_NAME'] === 'eagleeye' ? '<div style="position:absolute;left:50%;transform:translateX(-50%);color:#FFF;z-index:1;margin-top:5px;"><h1>DEVELOPMENT</h1></div>' : null; ?>

        <div class="clearfix"></div>
      </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->

    <div class="navbar-custom">
      <div class="container">
        <div id="navigation">
          <!-- Navigation Menu-->
          <?php require_once 'includes/nav_menu.php'; ?>
          <!-- End navigation menu  -->
        </div>
      </div>
    </div>

    <div class="js_loading"><i class='fa fa-3x fa-spin fa-spinner'></i></div>
  </header>
  <!-- End Navigation Bar-->

  <div class="wrapper">
    <div class="container">
      <div class="col-md-12" id="main_display" data-showing="dashboard" data-search="false">
        <div class="row">
          <div class="col-md-12">
            <div id="main_body"></div>
          </div>
        </div>
      </div>

      <div id="search_display" style="display: none;"></div>

      <!-- Global modal -->
      <div id="modalGlobal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalGlobalLabel" aria-hidden="true">
        <!-- Inserted via AJAX -->
      </div>
      <!-- /.modal -->

      <!-- TODO: Change this to global modal (above) -->
      <div id="feedback-page" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="feedbackPageLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title" id="myModalLabel">Feedback</h4>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12">
                  <textarea class="form-control" id="feedback-text" style="width:100%;height:200px;"></textarea>
                </div>
              </div>

              <div class="row" style="margin-top:5px;">
                <div class="col-md-1" style="padding-top:3px;"><label for="feedback_to">Notify: </label></div>

                <div class="col-md-4">
                  <select name="feedback_to" id="feedback_to" class="form-control">
                    <?php
                    $usr_qry = $dbconn->query('SELECT * FROM user WHERE account_status = TRUE AND id != 16 ORDER BY FIELD(id, 9) DESC, name ASC;');

                    while($usr = $usr_qry->fetch_assoc()) {
                      echo "<option value='{$usr['id']}'>{$usr['name']}</option>";
                    }
                    ?>
                  </select>
                </div>

                <div class="col-md-1" style="padding-top:3px;"><label for="feedback_priority">Priority: </label></div>

                <div class="col-md-4">
                  <select name="feedback_priority" id="feedback_priority" class="form-control">
                    <option value="3 - End of Week">End of Week</option>
                    <option value="2 - End of Day">End of Day</option>
                    <option value="1 - Immediate">Immediate</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary waves-effect waves-light" id="feedback-submit">Submit</button>
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
    <?php
    if($server[0] === 'dev') {
      echo "var socket = io.connect('//dev.3erp.us:4000');";
    } elseif($server[0] === 'eagleeye') {
      echo "var socket = io.connect('//localhost:4000');";
    } else {
      echo "var socket = io.connect('//3erp.us:4100');";
    }

    $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '{$_SESSION['userInfo']['id']}'");
    $usr = $usr_qry->fetch_assoc();
    ?>

    var currentPage = '<?php echo $usr['default_dashboard']; ?>';
    var scrollPosition = 0;

    var oplUpdater; // used on html/opl/index.php
    var oplFiltered = false;

    var userID;

    // -- Dashboard --
    <?php
    echo (isset($_SESSION['userInfo']['default_queue']) || !empty($_SESSION['userInfo']['default_queue'])) ? "var queue = '{$_SESSION['userInfo']['default_queue']}';" : "var queue = '{$_SESSION['shop_user']['default_queue']}';";

    $unique_key = hash('md5', microtime() + mt_rand(1,999999999));

    if(!$dbconn->query("UPDATE user SET unique_key = '$unique_key' WHERE id = '{$_SESSION['userInfo']['id']}'")) {
      dbLogSQLErr($dbconn);
    }

    $_SESSION['userInfo']['unique_key'] = $unique_key;

    echo "var unique_key = '$unique_key';";
    echo 'socket.emit("setUK", unique_key);';
    ?>

    var op_id;
    var opFull;
    // -- EO Dashboard --

    // -- Build a VIN --
    var vin_sonum;
    // -- End Build a VIN --

    // -- Socket Handling --
    socket.on("connect", function() {
      $("#server_failure").slideUp(250);
    });

    socket.on("connect_error", function(e) {
      $("#server_failure").slideDown(250);
      
      console.log(e);
    });

    // if there's an unhandled error
    socket.on("err", function(e) {
      $.alert({
        title: 'An error has occurred!',
        content: e,
        buttons: {
          cancel: function() {}
        }
      });
    });

    // if there's a queue update
    socket.on("catchQueueUpdate", function() {
      if(currentPage === 'dashboard' || currentPage === 'eng_report' || currentPage === 'crm/index') {
        globalFunctions.updateOpQueue();
        globalFunctions.updateBreakButton();
      }

      if(currentPage === 'workcenter') {
        jiq_table.ajax.reload(null,false);
        active_table.ajax.reload(null,false);
        completed_table.ajax.reload(null,false);
      }
    });

    // when disconnecting from the socket
    socket.on("disconnect", function() {
      $("#server_failure").slideDown(250);
    });

    // global function to refresh (and interrupt) everyone
    socket.on("catchRefresh", function() {
      location.reload();
    });

    // an update has been done related to the OPL Edit Status, get the latest update from the server
    socket.on("refreshOPLEditStatus", function() {
      socket.emit("getOPLEditingStatus");
    });

    // when requesting the current status of all OPL edits
    socket.on("OPLEditStatusUpdate", function(data) {
      if(data !== null) {
        let cur_usr = '<?php echo $_SESSION['userInfo']['name']; ?>';
        let warningBox = $("#opl_warning");

        if(data.initiator !== cur_usr) {
          $(".opl_action").prop("disabled", true);
          disabled = true;

          let editStarted = new Date(data.timestamp).toLocaleString();

          warningBox.html('<div class="alert alert-warning" role="alert"><strong>Unable to Save!</strong> ' + data.initiator +' is editing this report as of ' + editStarted +'. <?php echo ($bouncer->validate('opl_save_override')) ? '<strong><a href="#" id="OPLForceOverride">Override?</a></strong>' : null; ?>');
        } else {
          $(".opl_action").prop("disabled", false);
          disabled = false;

          warningBox.html('<div class="alert alert-danger" role="alert"><strong>Unsaved Changes!</strong> This table is currently locked for editing by you due to unsaved changes. <strong><a href="#" onclick="$(\'#saveOPL\').trigger(\'click\');">Save</a></strong> or <strong><a href="#" onclick="$(\'#oplRefresh\').trigger(\'click\');">Discard</a></strong> your changes?</div>');
        }
      } else {
        $(".opl_action").prop("disabled", false);
        opl.fancytree('getTree').reload({url: '/html/opl/ajax/actions.php?action=getOPL'});
        disabled = false;

        $("#opl_warning").html('<div class="alert alert-success" role="alert">You are on the most current version of the OPL. <strong><a href="#" id="OPLCheckout">Checkout</a></strong>?');
      }

      opl_history.fancytree('getTree').reload({url: "/html/opl/ajax/actions.php?action=getOPLHistory"});
    });

    socket.on("pullOPLChanges", function(tree) {
      opl.fancytree('getTree').reload(tree);
    });
    // -- End of Socket Handling --

    $(function() {
      <?php
      if(empty($_REQUEST['page'])) {
        echo "globalFunctions.loadPage('{$usr['default_dashboard']}');";
      } else {
        echo "globalFunctions.loadPage('{$_REQUEST['page']}');";
      }

      if($_SESSION['userInfo']['justLoggedIn']) {
        echo "displayToast('success', 'Welcome to your dashboard {$_SESSION['userInfo']['name']}!', 'Successfully Logged In', true);";
        $_SESSION['userInfo']['justLoggedIn'] = FALSE;
      }
      ?>

      jconfirm.defaults = {
        title: "Leaving without saving!",
        content: "You have unsaved changes, do you wish to proceed?",
        type: 'orange',
        typeAnimated: true,
        theme: 'supervan'
      };

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

      // TODO: @globalFunctions.getLocalTime is not used anywhere else other than here - should this be cleaned up?
      $("#clock").html(globalFunctions.getLocalTime);

      setInterval(function() {
        $("#clock").html(globalFunctions.getLocalTime);
      }, 1000); // clock

      $(".modal").draggable({
        handle: ".modal-header"
      });

      $("#modalGlobal").on("hidden.bs.modal", function() {
        $("#modalGlobal").html('');
      });

      $.ui.fancytree.debugLevel = 0;

      main.navTimeCardInit();
      main.employeeClockOutInit();
      main.viewBreakInit();
      main.dashBoardInit();
      main.viewContactsInit();
      main.feedbackSubmitInit();
      main.clickingSoInit();
      main.workCenterInit();
      main.vinInit();
      main.taskPageInit();
      main.roomPageInit();
      main.salesListInit();
      main.notificationInit();
      main.addSoInit();
      main.addProjectInit();
    });

    /** @var tree - the FancyTree to take in and convert into a minified version */
    function getMiniTree(tree) {
      // return the getTree from FancyTree with the toDict (no root node) excluding the following
      return tree.fancytree("getTree").toDict(false, function(node) {
        delete node.expanded; // remove expanded
        delete node.selected; // remove selected
        delete node.partsel; // remove partially selected
      });
    }

    function sendOPLEdit() {
      socket.emit("oplEditing", {initiator: '<?php echo $_SESSION['userInfo']['name']; ?>', timestamp: new Date().getTime()});
    }

      <?php if($bouncer->validate('view_timecards')) { ?>
    
      <?php } ?>

      <?php if($bouncer->validate('view_so')) { ?>
     
      <?php } ?>

      <?php if($bouncer->validate('view_operation')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('view_workcenter')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('edit_vin')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('view_tasks')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('view_audit_log')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('view_sales_list')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('add_feedback')) { ?>

      <?php } ?>



      <?php if($bouncer->validate('clock_out')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('add_so')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('add_project')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('view_contacts')) { ?>

      <?php } ?>

      <?php if($bouncer->validate('view_break')) { ?>

      <?php } ?>

  </script>

  <?php if($bouncer->validate('search')) { ?>
    <!-- Global Search loading, required for global search to work -->
    <script src="/ondemand/js/global_search.min.js?v=<?php echo VERSION; ?>"></script>
  <?php } ?>

  <!-- jQuery  -->
  <script src="/assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
  <script src="/assets/js/bootstrap.min.js"></script>
  <script src="/assets/js/waves.js"></script>
  <script src="/assets/js/jquery.nicescroll.js"></script>
  <script src="/includes/js/main.js"></script>


  <!-- custom dropdown -->
  <script src="/includes/js/custom_dropdown.min.js?v=<?php echo VERSION; ?>"></script>

  <!-- Toastr setup -->
  <script src="/assets/plugins/toastr/toastr.min.js"></script>
  <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

  <!-- Datatables -->
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.2/b-html5-1.5.2/b-print-1.5.2/fh-3.1.4/rg-1.0.3/sc-1.5.0/sl-1.2.6/datatables.min.js"></script>

  <!-- Moment.js for Timekeeping -->
  <script src="/assets/plugins/moment/moment.js"></script>

  <!-- Alert Windows -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>

  <!-- Counter Up  -->
  <script src="/assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
  <script src="/assets/plugins/counterup/jquery.counterup.min.js"></script>

  <!-- App js -->
  <script src="/assets/js/jquery.core.js"></script>
  <script src="/assets/js/jquery.app.js"></script>

  <!-- Tinysort -->
  <script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

  <!-- JScroll -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js"></script>

  <!-- Math, fractions and more -->
  <script src="/assets/plugins/math.min.js"></script>

  <!-- Pricing program -->
  <script src="/html/pricing/pricing.js?v=<?php echo VERSION; ?>"></script>

  <!-- Opl Program -->
  <script src="/includes/js/opl.js"></script>

  <!-- Fancytree -->
  <script src="/assets/plugins/fancytree/jquery.fancytree.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.filter.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.dnd.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.edit.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.gridnav.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.table.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.persist.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.fixed.js"></script>

  <!-- MapHilight - for Area Maps on images, dashboard circle display mostly -->
  <script src="/assets/plugins/maphilight/jquery.maphilight.min.js"></script>

  <!-- Float TableHead -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/2.1.2/jquery.floatThead.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/ui-contextmenu/jquery.ui-contextmenu.min.js"></script>

  <!-- Unsaved Changes -->
  <script src="/assets/js/unsaved_alert.js?v=<?php echo VERSION; ?>"></script>

  <!-- Sticky table header -->
  <script src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

  <!-- Association management module -->
  <script src="/includes/js/association.min.js"></script>

  <script src="/assets/plugins/dhtmlxScheduler/dhtmlxscheduler.js" type="text/javascript" charset="utf-8"></script>
  <link rel="stylesheet" href="/assets/plugins/dhtmlxScheduler/dhtmlxscheduler_material.css" type="text/css"  title="no title" charset="utf-8">

  <script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_limit.js" type="text/javascript" charset="utf-8"></script>
  <script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_tooltip.js" type="text/javascript" charset="utf-8"></script>
  <script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_recurring.js" type="text/javascript"></script>

  <link rel="stylesheet" href="/html/calendar/ajax/events.php?action=getEventCSS&v=<?php echo VERSION; ?>" type="text/css" charset="utf-8">
  </body>
  </html>
<?php
$dbconn->close();
?>