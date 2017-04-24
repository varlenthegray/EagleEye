<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- tablesaw-->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<div class="row" id="default_login_form">
    <div class="col-md-6">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <table class="tablesaw table">
                        <thead>
                        <tr>
                            <th scope="col" data-tablesaw-priority="persist">Employee</th>
                        </tr>
                        </thead>
                        <tbody id="room_search_table">
                        <?php
                        $qry = $dbconn->query("SELECT * FROM user");

                        while($result = $qry->fetch_assoc()) {
                            echo "<tr class='cursor-hand' data-toggle='modal' data-target='#modalLogin' data-login-id='{$result['id']}' data-login-name='{$result['name']}'>";
                            echo "<td>{$result['name']}</td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>

                    <!-- modal -->
                    <div id="modalLogin" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLoginLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h4 class="modal-title" id="modalLoginName">Login As Ben</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 text-md-center">
                                            <h4>Enter PIN Code</h4>

                                            <input type="password" autocomplete="off" name="pin" placeholder="PIN" maxlength="4" id="loginPin" class="text-md-center">
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var userID;

    function doLogin() {
        $.post("/ondemand/shopfloor/login_actions.php?action=login", {id: userID, pin: $("#loginPin").val()}, function(data) {
            if (data === 'success') {
                window.location.href = "/shopfloor/index.php";
            } else {
                displayToast("error", "Failed to log in, please try again.", "Login Failure");
                $("#modalLogin").modal('hide');
            }
        });
    }

    $("#modalLogin").on("show.bs.modal", function(e) { // when we're triggering the show event
        var userLine = $(e.relatedTarget); // grab the related line and information associated with it
        var modal = $(this); // set the modal to this specific element

        modal.find('.modal-title').text('Hello ' + userLine.data("login-name")); // find and update the text to the login name from the data line

        userID = userLine.data("login-id");

        $("#loginPin").val(""); // clear out any previous entries/attempts
    }).on("shown.bs.modal", function() { // once the modal form is completely shown
        $("#loginPin").focus(); // set the focus (once the modal is fully painted on the canvas)
    });

    $("#clock_in").on("click", function() { // if you click the button, do login
        doLogin();
    });

    $("#loginPin").on("keypress", function(e) { // each time you press a key in the PIN field
        if(e.keyCode === 13) // if hitting the enter key, do login
            doLogin();
    });
</script>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>