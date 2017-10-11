<?php
require_once ("header_start.php");
?>

<!--<script src="https://chatwee-api.com/v2/script/595faf5abd616ddd3a8b456f.js"></script>-->

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
                            <optgroup label="Office">
                                <option value="9">Production Administrator</option>
                                <option value="14">Shop Foreman</option>
                                <option value="7">General Manager</option>
                                <option value="1">IT</option>
                                <option value="10">Engineering</option>
                            </optgroup>

                            <optgroup label="Shop">
                                <option value="15">Box</option>
                                <option value="12">Customs</option>
                                <option value="11">Assembly</option>
                                <option value="22">Finishing</option>
                                <option value="11">Shipping</option>
                            </optgroup>
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

<!-- Right Sidebar -->
<div class="side-bar right-bar">
    <div class="nicescroll">
        <ul class="nav nav-tabs text-xs-center">
            <li class="nav-item">
                <a href="#home-2"  class="nav-link active" data-toggle="tab" aria-expanded="false">
                    Activity
                </a>
            </li>
            <li class="nav-item">
                <a href="#messages-2" class="nav-link" data-toggle="tab" aria-expanded="true">
                    Settings
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade in active" id="home-2">
                <div class="timeline-2">
                    <div class="time-item">
                        <div class="item-info">
                            <small class="text-muted">5 minutes ago</small>
                            <p><strong><a href="#" class="text-info">John Doe</a></strong> Uploaded a photo <strong>"DSC000586.jpg"</strong></p>
                        </div>
                    </div>

                    <div class="time-item">
                        <div class="item-info">
                            <small class="text-muted">30 minutes ago</small>
                            <p><a href="" class="text-info">Lorem</a> commented your post.</p>
                            <p><em>"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam laoreet tellus ut tincidunt euismod. "</em></p>
                        </div>
                    </div>

                    <div class="time-item">
                        <div class="item-info">
                            <small class="text-muted">59 minutes ago</small>
                            <p><a href="" class="text-info">Jessi</a> attended a meeting with<a href="#" class="text-success">John Doe</a>.</p>
                            <p><em>"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam laoreet tellus ut tincidunt euismod. "</em></p>
                        </div>
                    </div>

                    <div class="time-item">
                        <div class="item-info">
                            <small class="text-muted">1 hour ago</small>
                            <p><strong><a href="#" class="text-info">John Doe</a></strong>Uploaded 2 new photos</p>
                        </div>
                    </div>

                    <div class="time-item">
                        <div class="item-info">
                            <small class="text-muted">3 hours ago</small>
                            <p><a href="" class="text-info">Lorem</a> commented your post.</p>
                            <p><em>"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam laoreet tellus ut tincidunt euismod. "</em></p>
                        </div>
                    </div>

                    <div class="time-item">
                        <div class="item-info">
                            <small class="text-muted">5 hours ago</small>
                            <p><a href="" class="text-info">Jessi</a> attended a meeting with<a href="#" class="text-success">John Doe</a>.</p>
                            <p><em>"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam laoreet tellus ut tincidunt euismod. "</em></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="messages-2">

                <div class="row m-t-20">
                    <div class="col-xs-8">
                        <h5 class="m-0">Notifications</h5>
                        <p class="text-muted m-b-0"><small>Do you need them?</small></p>
                    </div>
                    <div class="col-xs-4 text-right">
                        <input type="checkbox" checked data-plugin="switchery" data-color="#64b0f2" data-size="small"/>
                    </div>
                </div>

                <div class="row m-t-20">
                    <div class="col-xs-8">
                        <h5 class="m-0">API Access</h5>
                        <p class="m-b-0 text-muted"><small>Enable/Disable access</small></p>
                    </div>
                    <div class="col-xs-4 text-right">
                        <input type="checkbox" checked data-plugin="switchery" data-color="#64b0f2" data-size="small"/>
                    </div>
                </div>

                <div class="row m-t-20">
                    <div class="col-xs-8">
                        <h5 class="m-0">Auto Updates</h5>
                        <p class="m-b-0 text-muted"><small>Keep up to date</small></p>
                    </div>
                    <div class="col-xs-4 text-right">
                        <input type="checkbox" checked data-plugin="switchery" data-color="#64b0f2" data-size="small"/>
                    </div>
                </div>

                <div class="row m-t-20">
                    <div class="col-xs-8">
                        <h5 class="m-0">Online Status</h5>
                        <p class="m-b-0 text-muted"><small>Show your status to all</small></p>
                    </div>
                    <div class="col-xs-4 text-right">
                        <input type="checkbox" checked data-plugin="switchery" data-color="#64b0f2" data-size="small"/>
                    </div>
                </div>

            </div>
        </div>
    </div> <!-- end nicescroll -->
</div>
<!-- /Right-bar -->

</div> <!-- End wrapper -->

<script>
    var resizefunc = [];

    function notifyMe() {
        // Let's check if the browser supports notifications
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notification");
        }

        // Let's check whether notification permissions have already been granted
        else if (Notification.permission === "granted") {
            setTimeout(function() {
                // If it's okay let's create a notification
                var notification = new Notification("Hi there!");
            }, 2000)
        }

        // Otherwise, we need to ask the user for permission
        else if (Notification.permission !== "denied") {
            Notification.requestPermission(function (permission) {
                // If the user accepts, let's create a notification
                if (permission === "granted") {
                    var notification = new Notification("Hi there!");
                }
            });
        }

        // At last, if the user has denied notifications, and you
        // want to be respectful there is no need to bother them any more.
    }

    $("body")
        .on("click", "#feedback-submit", function() {
            var description = tinyMCE.get('feedback-text').getContent();
            var feedback_to = $("#feedback_to").val();

            $.post("/ondemand/admin/tasks.php?action=submit_feedback", {description: description, assignee: feedback_to}, function(data) {
                $("body").append(data);
                $("#feedback-page").modal('hide');
                unsaved = false;

                tinyMCE.get('feedback-text').setContent("");
            });
        })
        .on("click", "#notification_list", function() {
            $.post("/ondemand/alerts.php?action=viewed_alerts");
        });

    $(".modal").draggable({
        handle: ".modal-header"
    });

    <?php
        if($_SESSION['userInfo']['id'] !== '16') {
            $id = $_SESSION['userInfo']['id'];

    ?>

        $.post("/ondemand/alerts.php?action=update_alerts", function(data) {
            $("#notification_list").html(data);
        });

        setInterval(function() {
            $.post("/ondemand/alerts.php?action=update_alerts", function(data) {
                $("#notification_list").html(data);
            });
        }, 10000);

    <?php
        } else {
            $id = $_SESSION['shop_user']['id'];
        }

        if(!empty($id)) {
            $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$id'");
            $usr = $usr_qry->fetch_assoc();

            if(!empty($usr['intro_code'])) {
                echo "var intro = introJs();";
                echo $usr['intro_code'];
                echo "intro.start();";

                $dbconn->query("UPDATE user SET intro_code = NULL WHERE id = '$id'");
            }
        }
    ?>


</script>

<!-- jQuery  -->
<script src="/assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/waves.js"></script>
<script src="/assets/js/jquery.nicescroll.js"></script>
<script src="/assets/plugins/switchery/switchery.min.js"></script>

<!-- Mask -->
<script src="/assets/plugins/jquery.mask.min.js"></script>

<!-- Counter Up  -->
<script src="/assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
<script src="/assets/plugins/counterup/jquery.counterup.min.js"></script>

<!-- KNOB JS -->
<!--[if IE]>
<script type="text/javascript" src="/assets/plugins/jquery-knob/excanvas.js"></script>
<![endif]-->
<script src="/assets/plugins/jquery-knob/jquery.knob.js"></script>
<script type="text/javascript" src="/assets/plugins/multiselect/js/jquery.multi-select.js"></script>
<!-- Peity chart js -->
<script src="/assets/plugins/peity/jquery.peity.min.js"></script>

<!-- App js -->
<script src="/assets/js/jquery.core.js"></script>
<script src="/assets/js/jquery.app.js"></script>

<!-- Calendar -->
<script src="/assets/plugins/fullcalendar/dist/fullcalendar.min.js"></script>

<!-- Tablesaw -->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<!-- Tinysort -->
<script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

<!-- Input Masking -->
<script type="text/javascript" src="/assets/plugins/jquery.mask.min.js"></script>

<!-- Datepicker -->
<script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

<!-- Date/time picker -->
<script src="/assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<!-- Date/Range Picker -->
<script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>

<!-- Xeditable -->
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>

<!-- JScroll -->
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js"></script>

<!-- Unsaved Changes -->
<script src="/assets/js/unsaved_alert.js"></script>

<!-- Jquery filer js -->
<script src="/assets/plugins/jquery.filer/js/jquery.filer.min.js"></script>
