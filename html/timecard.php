<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="hidden-print">Timecard Reporting</h4>

                        <div class="col-md-1 hidden-print">
                            <fieldset class="form-group">
                                <label for="employee">Employee</label>
                                <select name="employee" id="employee" class="form-control">
                                    <?php
                                    $emp_qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE");

                                    if($emp_qry->num_rows > 0) {
                                        echo "<optgroup label='Active'>";

                                        while($employee = $emp_qry->fetch_assoc()) {
                                            echo "<option value='{$employee['id']}'>{$employee['name']}</option>";
                                        }

                                        echo "</optgroup>";
                                    }

                                    $emp_qry = $dbconn->query("SELECT * FROM user WHERE account_status = FALSE");

                                    if($emp_qry->num_rows > 0) {
                                        echo "<optgroup label='In-active'>";

                                        while($employee = $emp_qry->fetch_assoc()) {
                                            echo "<option value='{$employee['id']}'>{$employee['name']}</option>";
                                        }

                                        echo "</optgroup>";
                                    }
                                    ?>
                                </select>
                            </fieldset>
                        </div>

                        <div class="col-md-4 hidden-print">
                            <div class="form-group">
                                <label for="date_range">Date Range</label>
                                <input class="form-control input-daterange-datepicker ignoreSaveAlert" type="text" name="date_range" id="date_range">
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
</div>

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