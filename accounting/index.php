<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>

<!-- tablesaw -->
<link href="/assets/plugins/tablesaw/dist/tablesaw.css" rel="stylesheet" type="text/css"/>

<!-- daterange -->
<link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="hidden-print">Timecard Reporting</h4>

                    <div class="col-md-1 hidden-print">
                        <fieldset class="form-group">
                            <label for="employee">Employee</label>
                            <select name="employee" id="employee" class="form-control">
                                <?php
                                $emp_qry = $dbconn->query("SELECT * FROM user");

                                if($emp_qry->num_rows > 0) {
                                    while($employee = $emp_qry->fetch_assoc()) {
                                        echo "<option value='{$employee['id']}'>{$employee['name']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </fieldset>
                    </div>

                    <div class="col-md-4 hidden-print">
                        <div class="form-group">
                            <label for="date_range">Date Range</label>
                            <input class="form-control input-daterange-datepicker" type="text" name="date_range" id="date_range">
                        </div>
                    </div>

                    <div class="col-md-1 hidden-print">
                        <button type="button" class="btn btn-primary waves-effect waves-light w-xs" id="search" name="search" style="margin-top: 20px;">Search</button>
                    </div>

                    <div class="col-md-12" id="timecard_audit"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- date range picker -->
<script src="/assets/plugins/moment/moment.js"></script>
<script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>

<!--Tablesaw-->
<script src="/assets/plugins/tablesaw/dist/tablesaw.js"></script>
<script src="/assets/plugins/tablesaw/dist/tablesaw-init.js"></script>

<script>
    $("#date_range").daterangepicker();

    $("#search").on("click", function() {
        var employeeID = $("#employee").val();
        var daterange = $("#date_range").val();

        $.post("/ondemand/accounting/timecard.php?action=report", {employee: employeeID, dates: daterange}, function(data) {
            $("#timecard_audit").html(data);
        });
    });
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>