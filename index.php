<?php
require 'includes/header_start.php';
require 'includes/header_end.php';
?>

<script>
    <?php
        if((bool)$_REQUEST['login'])
            echo "displayToast('success', 'Welcome to your dashboard {$_SESSION['userInfo']['name']}!', 'Successfully Logged In', true);";
    ?>
</script>

<div class="col-md-12">
    <div class="row">
        <div class="col-md-12">
            <div id="main_body"></div>
        </div>
    </div>
</div>

<script>
    $("#main_body").load("/html/individual_op.php", function() {
        $(".js_loading").hide();
    });

    $("body")
        .on("click", "#nav_dashboard", function() {
            $(".js_loading").show();
            $("#main_body").load("/html/individual_op.php", function() {
                $(".js_loading").hide();
            });
        })
        .on("click", "#nav_pricing", function() {
            $(".js_loading").show();
            $("#main_body").load("/html/pricing.php", function() {
                $(".js_loading").hide();
            });
        })
        .on("click", "#nav_workcenter", function() {
            $(".js_loading").show();
            $("#main_body").load("/html/workcenter.php", function() {
                $(".js_loading").hide();
            });
        })
        .on("click", "#nav_timecard", function() {
            $(".js_loading").show();
            $("#main_body").load("/html/timecard.php", function() {
                $(".js_loading").hide();
            });
        })
        .on("click", "#nav_job-management", function() {
            $(".js_loading").show();
            $("#main_body").load("/html/job_management.php", function() {
                $(".js_loading").hide();
            });
        })
        .on("keyup", "#global_search", function() {
            var searchTab = $("#search-results-tab");
            var input = $("#global_search");
            var searchDefaults = '<tr><td colspan="7" class="text-md-center"><i class="fa fa-3x fa-spin fa-spinner" style="width: auto;margin-right: 10px;"></i></td></tr>';
            var searchEmpty = '<tr><td colspan="7">No results found.</td></tr>';

            if(input.val().length > 0) {
                searchTab.show().tab("show");

                clearTimeout(timer);

                timer = setTimeout(function () {
                    if (input.val().length >= 1) {
                        $.post("/ondemand/livesearch/search_results.php?search=general", {find: input.val()}, function (data) {
                            $("#search_results_table").html(data);
                            $("#search_results_global_table").trigger("update");

                            if (data !== '') {
                                $('[data-toggle="tooltip"]').tooltip(); // enable tooltips

                                // setup field masks
                                $(".mask-zip").mask('00000-0000');
                                $(".mask-phone").mask('(000) 000-0000');

                                // setup date picker
                                $(".delivery_date").datepicker({
                                    autoclose: true,
                                    todayHighlight: true
                                }).mask('00/00/0000');
                            } else {
                                $("#search_results_table").html(searchEmpty);
                            }
                        });
                    } else {
                        if ($("#mainTab").find(".active").text() === 'Search Results...') {
                            $("#production-tab").tab("show");
                        }

                        searchTab.hide();
                        $("#search_results_table").html(searchDefaults);
                    }
                }, 400);
            } else {
                if ($("#mainTab").find(".active").text() === 'Search Results...') {
                    $("#production-tab").tab("show");
                }

                searchTab.hide();
                $("#search_results_table").html(searchDefaults);
            }
        })
    ;
</script>

<?php
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>