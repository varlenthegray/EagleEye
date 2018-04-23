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
  </head>

  <body>
  <div id="server_failure">
    <h1>Server down for maintenance. Please contact IT.</h1>
  </div>

  <!-- Navigation Bar-->
  <header id="topnav">
    <div class="custom-logo">
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

            <li class="nav-item notification-list">
              <a class="nav-link arrow-none waves-light waves-effect" href="#" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi-email noti-icon"></i></a>
            </li>

            <li class="nav-item notification-list">
              <a class="nav-link arrow-none waves-light waves-effect dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi zmdi-calendar noti-icon"></i></a>
              <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-lg" aria-labelledby="Preview">
                This is a test.
              </div>
            </li>

            <li class="nav-item notification-list">
              <a class="nav-link arrow-none waves-light waves-effect" href="#" role="button" aria-haspopup="false" aria-expanded="false"><i class="zmdi zmdi zmdi-comments noti-icon"></i></a>
            </li>

            <li class="nav-item dropdown notification-list" id="notification_list">
              <!-- AJAX -->
            </li>
          </ul>
        </div> <!-- end menu-extras -->

        <div class="clearfix"></div>
      </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->

    <div class="navbar-custom">
      <div class="container">
        <div id="navigation">
          <!-- Navigation Menu-->
          <?php require_once("includes/nav_menu.php"); ?>
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

      <div class="row" id="search_display" style="display: none;">
        <div class="col-md-12">
          <div class="card-box">
            <div class="row">
              <div class="col-md-12">
                <button class="btn btn-primary waves-effect waves-light" id="btn_search_to_main"><i class="zmdi zmdi-arrow-left m-r-5"></i> <span>GO BACK</span></button><br /><br />

                <table class="table table-bordered tablesorter" id="search_results_global_table">
                  <thead>
                  <tr>
                    <th colspan="2">SO#</th>
                    <th>PROJECT/CUSTOMER PO</th>
                    <th>PROJECT MANAGER</th>
                    <th>DEALER/CONTRACTOR</th>
                  </tr>
                  </thead>
                  <tbody id="search_results_table">
                  <tr>
                    <td colspan="7" class="text-md-center"><span id="global_search_status"><i class="fa fa-3x fa-spin fa-spinner" style="width: auto;margin-right: 10px;"></i></span></td>
                  </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Customer modal -->
      <div id="modalAddContact" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddContactLabel" aria-hidden="true">
        <!-- Inserted via AJAX -->
      </div>
      <!-- /.modal -->

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

      <!-- View Notes modal -->
      <div id="modalViewNotes" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalViewNotesLabel" aria-hidden="true">
        <!-- Inserted via AJAX -->
      </div>
      <!-- /.modal -->

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
                    $usr_qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE AND id != 16 ORDER BY FIELD(id, 9) DESC, name ASC;");

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
    <?php
    if($server[0] === 'dev') {
      echo "var socket = io.connect('//dev.3erp.us:4000');";
    } else {
      echo "var socket = io.connect('//3erp.us:4100');";
    }

    $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '{$_SESSION['userInfo']['id']}'");
    $usr = $usr_qry->fetch_assoc();
    ?>


    var currentPage = '<?php echo $usr['default_dashboard']; ?>';
    var scrollPosition = 0;

    var indv_dt_interval; // used on functions.js
    var indv_auto_interval; // used on functions.js
    var wc_auto_interval; // used on functions.js
    var dash_auto_interval; // used on functions.js
    var oplUpdater; // used on html/opl/index.php

    var userID;

    // -- Dashboard --
    <?php
    echo (isset($_SESSION['userInfo']['default_queue']) || !empty($_SESSION['userInfo']['default_queue'])) ? "var queue = '{$_SESSION['userInfo']['default_queue']}';" : "var queue = '{$_SESSION['shop_user']['default_queue']}';";

    $unique_key = hash("sha256", microtime() + rand(1,999999999));

    $dbconn->query("UPDATE user SET unique_key = '$unique_key' WHERE id = '{$_SESSION['userInfo']['id']}'");

    $_SESSION['userInfo']['unique_key'] = $unique_key;

    echo "var unique_key = '$unique_key';";
    ?>

    var op_id;
    var opFull;
    // -- EO Dashboard --

    // -- Build a VIN --
    var vin_sonum;
    // -- End Build a VIN --

    // -- Socket Handling --
    socket.on("connect", function() {
      socket.emit("setUK", unique_key);

      $("#server_failure").hide();
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
      if(currentPage === 'dashboard' || currentPage === 'eng_report') {
        updateOpQueue();

        updateBreakButton();
      }

      if(currentPage === 'workcenter') {
        jiq_table.ajax.reload(null,false);
        active_table.ajax.reload(null,false);
        completed_table.ajax.reload(null,false);
      }
    });

    socket.on("disconnect", function() {
      $("#server_failure").show();
    });

    socket.on("catchRefresh", function() {
      location.reload();
    });
    // -- End of Socket Handling --

    $(function() {
      <?php
      if(empty($_REQUEST['page'])) {
        echo "loadPage('{$usr['default_dashboard']}');";
      } else {
        echo "loadPage('{$_REQUEST['page']}');";
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

      $("#clock").html(getLocalTime);

      setInterval(function() {
        $("#clock").html(getLocalTime);
      }, 1000); // clock

      $(".modal").draggable({
        handle: ".modal-header"
      });

      <?php if($_SESSION['userInfo']['id'] !== '16') { ?>
      // TODO: Alerts disabled due to clicking users out of the box
      /*$.post("/ondemand/alerts.php?action=update_alerts", function(data) {
          $("#notification_list").html(data);
      });

      setInterval(function() {
          $.post("/ondemand/alerts.php?action=update_alerts", function(data) {
              $("#notification_list").html(data);
          });
      }, 10000);*/
      <?php }?>
    });

    $("body")
    <?php if($bouncer->validate('view_timecards')) { ?>
    // -- Navigation --
      .on("click", "#nav_timecard", function() {
        var start = Math.round(new Date().getTime()/1000);
        var end = Math.round(new Date().getTime()/1000);

        window.open("/print/timecard.php?start_date=" + start + "&end_date=" + end + "&employee=23", "_blank");
      })
      // -- End Navigation --
      <?php } ?>

      <?php if($bouncer->validate('view_so')) { ?>
      // -- Clicking an SO to view it
      .on("click", ".view_so_info", function(e) {
        e.stopPropagation();

        $("#modalAddContact").modal('hide');

        var id = $(this).attr("id");
        $("#global_search").val(id).trigger("keyup");
      })
      // -- End clicking an SO to view it
      <?php } ?>

      <?php if($bouncer->validate('view_operation')) { ?>
      // -- Dashboard --
      .on("change", "#viewing_queue", function() {
        updateOpQueue();
      })

      .on("click", ".start-operation", function(e) {
        e.stopPropagation();

        opFull = $(this).closest('tr').find('td').eq(4).html();
        op_id = $(this).attr("id");

        if(opFull === 'NB00: Non-Billable' || opFull === 'TF00: On The Fly') {
          $.post("/ondemand/op_actions.php?action=get_start_info", {opID: op_id, op: opFull}, function(data) {
            $("#modalStartJob").html(data);
          }).done(function() {
            $("#modalStartJob").modal();
          }).fail(function() { // if we're receiving a header error
            $("body").append(data); // echo an error and log it
          });
        } else {
          $.post("/ondemand/op_actions.php?action=start_operation", {operation: opFull, id: op_id}, function(data) {
            $('body').append(data);

            socket.emit("updateQueue");

            $.post("/html/view_notes.php", {queueID: op_id}, function(data) {
              $("#modalViewNotes").html(data).modal("show");
            });
          });
        }

        unsaved = false;
      })
      .on("click", "#start_job", function() {
        var other_notes_field = $("#other_notes_field").val(); // non-billable "other" section
        var notes_field = $("#notes_field").val(); // Cabinet Vision task or anything with JUST a notes field

        if($("#other_subtask").is(":checked")) { // if this is a subtask "other" section then we have to verify the notes
          if (other_notes_field.length >= 3) { // and the length of notes is greater than 3
            $.post("/ondemand/op_actions.php?action=start_operation", {id: op_id, operation: opFull, subtask: "Other", notes: other_notes_field}, function (data) {
              $("body").append(data);

              socket.emit("updateQueue");

              $("#modalStartJob").modal('hide');
            });
          } else { // otherwise, the notes is less than 3 and they need to enter notes in
            displayToast("error", "Enter notes in before continuing (more than 3 characters).", "Notes Required");
          }
        } else { // this is an OTF (for now?)
          var subtask = $('input[name=nonBillableTask]:checked').val();
          var otf_so_num = $("#otf_so_num").val();
          var otf_room = $("#otf_room").val();
          var otf_op = $("#otf_operation").val();
          var otf_notes = $("#otf_notes").val();
          var otf_iteration = $("#otf_iteration").val();

          $.post("/ondemand/op_actions.php?action=start_operation", {id: op_id, operation: opFull, subtask: subtask,
            notes: notes_field, otf_so_num: otf_so_num, otf_room: otf_room, otf_op: otf_op, otf_notes: otf_notes, otf_iteration: otf_iteration}, function(data) {
            socket.emit("updateQueue");

            $('body').append(data);

            $("#modalStartJob").modal('hide');
          });
        }

        unsaved = false;
      })
      .on("change", "input[name='nonBillableTask']", function() {
        if($(this).prop("id") === 'other_subtask') {
          $("#other_notes_section").show();
          $("#other_notes_field").focus();
        } else {
          $("#other_notes_section").hide();
        }
      })

      .on("click", ".pause-operation", function(e) {
        e.stopPropagation();

        op_id = $(this).attr("id");

        $.post("/ondemand/op_actions.php?action=get_pause_info", {opID: op_id}, function(data) {
          $("#modalUpdateJob").html(data).modal();
        });
      })
      .on("click", "#pause_op", function() {
        $.post("/ondemand/op_actions.php?action=pause_operation", {opID: op_id, notes: $("#notes").val(), qty: $("#qtyCompleted").val()}, function(data) {
          socket.emit("updateQueue");

          $("body").append(data);
          $("#modalUpdateJob").modal('hide');
        });
      })

      .on("click", ".complete-operation", function(e) {
        e.stopPropagation();

        op_id = $(this).attr("id");

        $.post("/ondemand/op_actions.php?action=get_stop_info", {opID: op_id}, function(data) {
          $("#modalUpdateJob").html(data).modal();
        });

        unsaved = false;
      })
      .on("change", "#rework_reqd", function() {
        if($(this).is(":checked")) {
          $(".rework_reason_group").show();
          $("#complete_op").html("Send Back");
        } else {
          $(".rework_reason_group").hide();
          $("#complete_op").html("Complete");
        }
      })
      .on("click", "#complete_op", function() {
        var notes = $("#notes").val();
        var qty_compl = $("#qtyCompleted").val();
        var rework = $("#rework_reqd").is(":checked");
        var rw_reason = $("#rework_reason").find(":selected").val();

        // formdata required because now we're attaching files
        var formData = new FormData();
        formData.append('opID', op_id);
        formData.append('opnum', opFull);
        formData.append('notes', notes);
        formData.append('qty', qty_compl);
        formData.append('rework_reqd', rework);
        formData.append('rework_reason', rw_reason);
        formData.append('attachment', $("input[type='file']")[0].files[0]);

        $.ajax({
          url: "/ondemand/op_actions.php?action=complete_operation",
          //url: "/admin/test.php",
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(data) {
            $('body').append(data);
            $("#modalUpdateJob").modal('hide');

            socket.emit("updateQueue");
          }
        });



        unsaved = false;
      })

      .on("click", ".op-notes", function(e) {
        e.stopPropagation();

        $.post("/html/view_notes.php", {queueID: $(this).attr("id")}, function(data) {
          $("#modalViewNotes").html(data).modal("show");
        });
      })
      // -- End Dashboard --
      <?php } ?>

      <?php if($bouncer->validate('view_workcenter')) { ?>
      // -- Workcenter --
      .on("click", ".wc-view-queue-so", function() {
        var id = $(this).attr("id");
        $("#global_search").val(id).trigger("keyup");
      })
      // -- End Workcenter --
      <?php } ?>

      <?php if($bouncer->validate('edit_vin')) { ?>
      // -- VIN Page --
      .on("blur", "#so_num", function() {
        vin_sonum = $(this).val();

        $.post("/ondemand/livesearch/build_a_vin.php?search=room&so_num=" + vin_sonum, function(data) {
          $("#room").html(data);
        });
      })
      .on("click blur", "#room", function() {
        $.post("/ondemand/livesearch/build_a_vin.php?search=iteration&so_num=" + vin_sonum + "&room=" + $("#room option:selected").val(), function (data) {
          $("#iteration").html(data);
        });
      })

      .on("click", ".print-sample", function() {
        var room_id = $(this).attr("id");

        calcVin(room_id);

        var formInfo = $("#room_edit_" + room_id).serialize();

        $.post("/ondemand/room_actions.php?action=update_room&" + formInfo + "&roomid=" + room_id, function(data) {
          $('body').append(data);
        }).done(function() {
          setTimeout(function() {
            window.open("/print/sample.php?room_id=" + room_id);
          }, 500);
        });

        unsaved = false;
      })
      .on("change keydown", ".vin_code_calc", function() {
        calcVin(active_room_id);
      })

      .on("change", ".recalcVin", function() {
        calcVin(active_room_id);
      })
      // -- End VIN Page --
      <?php } ?>

      <?php if($bouncer->validate('view_tasks')) { ?>
      // -- Task Page --
      .on("click", ".display-task-info", function() {
        $.post("/ondemand/admin/tasks.php?action=get_task_info", {task_id: $(this).attr("id")}, function(data) {
          $("#modalTaskInfo").html(data);
        }).done(function() {
          $("#modalTaskInfo").modal();
        }).fail(function(data) { // if we're receiving a header error
          $("body").append(data); // echo an error and log it
        });
      })
      .on("click", "#update_task_btn", function() {
        var form_info = $("#task_details").serialize();
        var task_id = $(this).data("taskid");
        var s_text_1 = $("#split-text-1").val();
        var s_text_2 = $("#split-text-2").val();

        $.post("/ondemand/admin/tasks.php?action=update_task", {task_id: task_id, s_text_1: s_text_1, s_text_2: s_text_2, form: form_info}, function(data) {
          $("body").append(data);
          $("#modalTaskInfo").modal('hide');

          unsaved = false;
        });
      })
      .on("click", "#split_task_btn", function() {
        $(".task_hide").toggle(100);
        $("#split_body").toggle(250);

        setTimeout(function() {
          if($("#split_body").is(":visible")) {
            $("#split_task_enabled").val("1");
          } else {
            $("#split_task_enabled").val("0");
          }
        }, 250);
      })
      .on("click", "#create_op_btn", function() {
        var form_info = $("#task_details").serialize();
        var task_id = $(this).data("taskid");

        $.post("/ondemand/admin/tasks.php?action=create_operation&" + form_info, {task_id: task_id}, function(data) {
          $("body").append(data);
          $("#modalTaskInfo").modal('hide');

          unsaved = false;
        });
      })
      // -- End Task Page --
      <?php } ?>

      <?php if($bouncer->validate('view_audit_log')) { ?>
      // -- Room Page --
      .on("change", "#display_log", function() {
        if($(this).is(":checked")) {
          $(".room_note_log").show();
        } else {
          $(".room_note_log").hide();
        }
      })
      // -- End Room Page --
      <?php } ?>

      <?php if($bouncer->validate('view_sales_list')) { ?>
      // -- Sales List Page --
      .on("change", "#job_status_lost", function() {
        if($(this).is(":checked")) {
          $(".room_lost").show();
        } else {
          $(".room_lost").hide();
        }
      })
      .on("change", "#job_status_quote", function() {
        if($(this).is(":checked")) {
          $(".room_quote").show();
        } else {
          $(".room_quote").hide();
        }
      })
      .on("change", "#job_status_job", function() {
        if($(this).is(":checked")) {
          $(".room_job").show();
        } else {
          $(".room_job").hide();
        }
      })
      .on("change", "#job_status_completed", function() {
        if($(this).is(":checked")) {
          $(".room_completed").show();
        } else {
          $(".room_completed").hide();
        }
      })
      .on("change", ".hide_dealer", function() {
        var dealer_id = $(this).data("dealer-id");

        if($(this).is(":checked")) {
          $(".dealer_" + dealer_id).show();
        } else {
          $(".dealer_" + dealer_id).hide();
        }
      })
      .on("click", ".sales_list_visible", function(e) {
        e.stopPropagation();

        var hide = $(this).data("identifier");

        $("." + hide).hide();

        $.post("/ondemand/display_actions.php?action=hide_sales_list_id&id=" + hide);
      })
      .on("click", ".sales_list_hidden", function(e) {
        e.stopPropagation();

        var show = $(this).data("identifier");
        $(this).removeClass('btn-primary-outline sales_list_hidden').addClass('btn-primary sales_list_visible').children('i').removeClass('zmdi-eye').addClass('zmdi-eye-off');

        $.post("/ondemand/display_actions.php?action=show_sales_list_id&id=" + show);
      })
      // -- End Sales List Page --
      <?php } ?>

      <?php if($bouncer->validate('add_feedback')) { ?>
      // -- Feedback --
      .on("click", "#feedback-submit", function() {
        var description = $("#feedback-text").val();
        var feedback_to = $("#feedback_to").val();
        var priority = $("#feedback_priority").val();

        $.post("/ondemand/admin/tasks.php?action=submit_feedback", {description: description, assignee: feedback_to, priority: priority}, function(data) {
          $("body").append(data);
          $("#feedback-page").modal('hide');
          unsaved = false;
          $("#feedback-text").val("");
        });
      })
      // -- End Feedback --
      <?php } ?>

      // -- Notifications --
      .on("click", "#notification_list", function() {
        $.post("/ondemand/alerts.php?action=viewed_alerts");
      })
      // -- End Notifications --

      <?php if($bouncer->validate('clock_out')) { ?>
      // -- Employees --
      .on("click", ".clock_out", function(e) {
        var id = $(this).data("id");

        e.stopPropagation();

        $.post("/ondemand/account_actions.php?action=clock_out", {'clockout_id': id}, function(data) {
          $("body").append(data);
        });
      })
      // -- End Employees --
      <?php } ?>

      <?php if($bouncer->validate('add_so')) { ?>
      .on("click", "#nav_add_so", function() {
        $.post('/html/new_customer.php', function(data) {
          $("#modalAddContact").html(data).modal('show');
        });
      })
      .on("change", "input[name='cu_type']", function() {
        var add_rc = $("#add_retail_customer");
        var add_dist = $("#add_distributor_cc");

        switch($(this).val()) {
          case 'retail':
            add_rc.show();
            add_dist.hide();

            break;
          case 'distribution':
            add_rc.hide();
            add_dist.show();

            break;
          case 'cutting':
            add_rc.hide();
            add_dist.show();

            break;
          default:
            break;
        }
      })
      .on("click", "#submit_new_customer", function() {
        var cuData = $("#add_retail_customer").serialize();

        $.post("/ondemand/so_actions.php?action=add_customer", {so_num: $("#so_num").val(), cu_data: cuData}, function(data) {
          $("body").append(data);

          $("#modalAddContact").modal('hide');
        });

        unsaved = false;
      })
      .on("change", "#secondary_addr_chk", function() {
        $(".secondary_addr_disp").toggle();
      })
      .on("change", "#billing_addr_chk", function() {
        $(".billing_info_disp").toggle();
      })
      .on("change", "#contractor_chk", function() {
        $(".contractor_disp").toggle();
      })
      <?php } ?>

      <?php if($bouncer->validate('add_project')) { ?>
      .on("click", "#nav_add_project", function() {
        $.post('/html/add_project.php?display=dealer', function(data) {
          $("#modalAddContact").html(data).modal('show');
        });
      })
      .on("change", "#contractor_chk", function() {
        $(".contractor_disp").toggle();
      })
      .on("click", "#submit_new_customer", function() {
        var cuData;

        cuData = $("#add_retail_customer").serialize();

        $.post("/ondemand/so_actions.php?action=add_customer&" + cuData, {so_num: $("#so_num").val()}, function(data) {
          $("body").append(data);

          $("#modalAddContact").modal('hide');
        });

        unsaved = false;
      })
      <?php } ?>

      <?php if($bouncer->validate('view_contacts')) { ?>
      .on("click", ".nav_add_contact", function() {
        var defaultType = $(this).attr('data-default');

        $.post('/html/add_contact.php?default=' + defaultType, function(data) {
          $("#modalAddContact").html(data).modal('show');
        });
      })
      .on("click", "#submit_new_contact", function() {
        var contactData = $("#contact_form").serialize();

        $.post('/ondemand/contact_actions.php?action=save_contact&' + contactData, function(data) {
          $("body").append(data);

          $("#modalAddContact").modal('hide');
        });

        unsaved = false;
      })
      .on("change", "#contact_type", function() {
        if($(this).find("option:selected").text() === 'Dealer') {
          $("#dealer_code").show();
        } else {
          $("#dealer_code").hide();
        }
      })
      .on("click", "#update_contact", function() {
        var contactData = $("#contact_form").serialize();

        $.post('/ondemand/contact_actions.php?action=update_contact&' + contactData, function(data) {
          $("body").append(data);

          $("#modalAddContact").modal('hide');
        });

        unsaved = false;
      })
      .on("click", "#delete_contact", function() {
        var contact_name = $(this).attr('data-name');
        var contact_id = $(this).attr('data-contact-id');

        $.confirm({
          title: "Delete contact " + contact_name,
          content: "You are about to <strong>permanently</strong> delete " + contact_name + ". Are you sure you would like to do this?",
          buttons: {
            yes: function() {
              $.post("/ondemand/contact_actions.php?action=delete_contact", {id: contact_id}, function(data) {
                $("body").append(data);
              });
            },
            no: function() {}
          }
        });
      })

      .on("click", ".assign_contact_so", function() {
        var contact_id = $(".add_contact_id :selected").val();

        $.post("/ondemand/contact_actions.php?action=add_contact_project", {contact_id: contact_id, so: active_so_num}, function(data) {
          $("body").append(data);
        });
      })
      .on("click", ".remove_assigned_contact_so", function() {
        var contact_id = $(this).attr('data-id');

        $.post("/ondemand/contact_actions.php?action=remove_contact_project", {contact_id: contact_id, so: active_so_num}, function(data) {
          $("body").append(data);
        });
      })
      .on("click", ".get_customer_info", function(e) {
        $.post("/html/add_contact.php?action=edit", {id: $(this).attr('data-view-id')}, function(data) {
          $("#modalAddContact").html(data).modal('show');
        });

        // stops it from posting to the URL in the browser
        e.preventDefault().stopPropagation();
      })
      <?php } ?>

      <?php if($bouncer->validate('view_break')) { ?>
      .on("click", ".nav_break", function() {
        var thisText = $(this).find('span');

        if($(this).attr('id') === '201') {
          $.post("/ondemand/account_actions.php?action=start_break", {id: 201, operation: 'Break'}, function(data) {
            $('body').append(data);

            socket.emit("updateQueue");

            $.post("/ondemand/account_actions.php?action=get_break_btn", function(data) {
              var result = JSON.parse(data);
              $(".nav_break").attr('id', result.id);

              thisText.html("Stop Break");
            });

            $(this).addClass('btn-success');
          });
        } else {
          $.post("/ondemand/account_actions.php?action=get_break_btn", function(data) {
            var result = JSON.parse(data);

            $(this).removeClass('btn-success');

            $.post("/ondemand/op_actions.php?action=complete_operation", {'opID': result.id, 'opnum': 'NB00'}, function(data) {
              $('body').append(data);

              socket.emit("updateQueue");

              $(".nav_break").attr('id', '201');

              thisText.html("Start Break");
            });
          });
        }
      })
      <?php } ?>

      .on("click", ".post_to_cal", function(e) {
        e.stopPropagation();
      })
    ;

    setInterval(function() { // stops the auto-logout
      $.post("/ondemand/session_continue.php");
    }, 600000);

    updateBreakButton(); // get the break button initial state
  </script>

  <?php if($bouncer->validate('search')) { ?>
    <!-- Global Search loading, required for global search to work -->
    <script src="/ondemand/js/global_search.js?v=<?php echo VERSION; ?>"></script>
  <?php } ?>

  <!-- jQuery  -->
  <script src="/assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
  <script src="/assets/js/bootstrap.min.js"></script>
  <script src="/assets/js/waves.js"></script>
  <script src="/assets/js/jquery.nicescroll.js"></script>

  <!-- custom dropdown -->
  <script src="/includes/js/custom_dropdown.js"></script>

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

  <!-- Fancytree -->
  <script src="/assets/plugins/fancytree/jquery.fancytree.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.filter.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.dnd.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.edit.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.gridnav.js"></script>
  <script src="/assets/plugins/fancytree/jquery.fancytree.table.js"></script>

  <script src="//cdn.jsdelivr.net/npm/ui-contextmenu/jquery.ui-contextmenu.min.js"></script>

  <!-- Unsaved Changes -->
  <script src="/assets/js/unsaved_alert.js?v=<?php echo VERSION; ?>"></script>

  <!-- Sticky table header -->
  <script src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>
  </body>
  </html>
<?php
$dbconn->close();
?>