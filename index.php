<?php
require 'includes/header_start.php';
require 'includes/header_end.php';
?>

<script>
    <?php
        if($_SESSION['userInfo']['justLoggedIn']) {
            echo "displayToast('success', 'Welcome to your dashboard {$_SESSION['userInfo']['name']}!', 'Successfully Logged In', true);";
            $_SESSION['userInfo']['justLoggedIn'] = FALSE;
        }
    ?>
</script>

<div class="col-md-12" id="main_display">
    <div class="row">
        <div class="col-md-12">
            <div id="main_body"></div>
        </div>
    </div>
</div>

<div class="row" id="search_display" style="display: none;">
    <div class="col-md-12">
        <div class="card-box">
            <div class="col-md-12">
                <button class="btn btn-info waves-effect waves-light" id="btn_search_to_main"><i class="zmdi zmdi-arrow-left m-r-5"></i> <span>Return</span></button><br /><br />

                <table class="table table-bordered tablesorter" id="search_results_global_table">
                    <thead>
                    <tr>
                        <th colspan="2">SO#</th>
                        <th>PROJECT/CUSTOMER PO</th>
                        <th>SALESPERSON</th>
                        <th>DEALER/CONTRACTOR</th>
                        <th>ACCOUNT TYPE</th>
                        <th>PROJECT MANAGER/CONTACT</th>
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

<!-- Add Customer modal -->
<div id="modalAddCustomer" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddCustomerLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<!-- Global Search loading, required for global search to work -->
<script src="/ondemand/js/global_search.js"></script>

<!-- Adding SO to the system -->
<script src="/ondemand/js/add_so.js"></script>

<script>
    var indv_dt_interval;
    var indv_auto_interval;
    var wc_auto_interval;

    var individual_op_scripts = false;
    var workcenter_scripts = false;

    function clearIntervals() {
        clearInterval(indv_dt_interval);
        clearInterval(indv_auto_interval);
        clearInterval(wc_auto_interval);
    }

    function backFromSearch() {
        $("#search_display").fadeOut(200);
        $("#global_search").val("");

        setTimeout(function() {
            $("#main_display").fadeIn(200);
        }, 200);
    }

    $("#main_body").load("/html/individual_op.php", function() {
        $(".js_loading").hide();
    });

    $("body")
        .on("click", "#nav_dashboard", function() {
            $(".js_loading").show();
            $("#main_body").load("/html/individual_op.php", function() {
                $(".js_loading").hide();
            });

            backFromSearch();
        })
        .on("click", "#nav_pricing", function() {
            clearIntervals();

            $(".js_loading").show();
            $("#main_body").load("/html/pricing.php", function() {
                $(".js_loading").hide();
            });

            backFromSearch();
        })
        .on("click", "#nav_workcenter", function() {
            clearIntervals();

            $(".js_loading").show();
            $("#main_body").load("/html/workcenter.php", function() {
                $(".js_loading").hide();
            });

            backFromSearch();
        })
        .on("click", "#nav_timecard", function() {
            clearIntervals();

            $(".js_loading").show();
            $("#main_body").load("/html/timecard.php", function() {
                $(".js_loading").hide();
            });

            backFromSearch();
        })
        .on("click", "#nav_job-management", function() {
            clearIntervals();

            $(".js_loading").show();
            $("#main_body").load("/html/job_management.php", function() {
                $(".js_loading").hide();
            });

            backFromSearch();
        })
        .on("click", "#nav_employees", function() {
            clearIntervals();

            $(".js_loading").show();
            $("#main_body").load("/html/employees.php", function() {
                $(".js_loading").hide();
            });

            backFromSearch();
        })
        .on("click", "#nav_tasks", function() {
            clearIntervals();

            $(".js_loading").show();
            $("#main_body").load("/html/tasks.php", function() {
                $(".js_loading").hide();
            });

            backFromSearch();
        })
        .on("click", "#nav_vin", function() {
            clearIntervals();

            $(".js_loading").show();
            $("#main_body").load("/html/build_a_vin.php", function() {
                $(".js_loading").hide();
            });

            backFromSearch();
        })
    ;
</script>

<?php
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>