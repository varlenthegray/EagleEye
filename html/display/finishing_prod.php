<?php
require '../../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="row">
                <div class="col-md-1 sticky">
                    <h5 class="text-md-center">Finishing Schedule</h5>
                    <a class="btn btn-secondary btn-block waves-effect waves-light" id="add_row">Add Row</a>
                    <a class="btn btn-primary btn-block waves-effect waves-light edit_room_save">Save</a>
                </div>

                <div class="col-md-6">
                    <form id="finishing_values">
                        <table class="table" id="main_table">
                            <thead>
                            <tr>
                                <th width="10%">SO #</th>
                                <th>Operation</th>
                                <th width="10%">Start</th>
                                <th width="10%">End</th>
                                <th width="10%">Dry Time</th>
                                <th>Comments</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php
                            $full_prod = array();

                            $existing_qry = $dbconn->query("SELECT * FROM disp_fin_prod WHERE visible = TRUE;");

                            if($existing_qry->num_rows > 0) {
                                while($existing = $existing_qry->fetch_assoc()) {
                                    $full_prod[$existing['id']] = $full_prod['op_id'];
                                }
                            }

                            $so_qry = $dbconn->query("SELECT operations.*, rooms.*, rooms.id AS roomID, operations.op_id AS opID FROM operations LEFT JOIN rooms ON rooms.main_bracket = operations.id WHERE responsible_dept = 'Finishing' and rooms.main_published = TRUE ORDER BY so_parent, room ASC;");

                            if($so_qry->num_rows > 0) {
                                while($so = $so_qry->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td><input type='text' name='so[]' placeholder='SO #' class='form-control' value='{$so['so_parent']}{$so['room']}-{$so['iteration']}' /><input type='hidden' name='roomID[]' value='{$so['roomID']}' /></td>";
                                    echo "<td>{$so['opID']}: {$so['job_title']}</td>";
                                    echo "<td><input type='text' name='start[]' placeholder='Start' class='form-control' /></td>";
                                    echo "<td><input type='text' name='end[]' placeholder='End' class='form-control' /></td>";
                                    echo "<td><input type='text' name='dry[]' placeholder='Dry Time' class='form-control' /></td>";
                                    echo "<td><input type='text' name='comments[]' placeholder='Comments' class='form-control' /></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$("body").on("click", "#add_row", function(e) {
    e.stopPropagation();

    var copy = $("#master").html();

    $("#main_table > tbody").append("<tr>" + copy + "</tr>");
});
</script>