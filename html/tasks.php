<?php
require '../includes/header_start.php';
?>

<div class="row  hidden-print">
    <div class="col-md-12">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered tablesorter" id="tasks_global_table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>ASSIGNED TO</th>
                            <th>CREATED</th>
                            <th>SHORT DESC</th>
                            <th>PRIORITY</th>
                            <th>ETA</th>
                            <th>% COMPLETED</th>
                            <th>LAST UPDATED</th>
                        </tr>
                        </thead>
                        <tbody id="tasks_information_table">
                        <tr>
                            <td colspan="9">No tasks to display.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task modal -->
<div id="modalTaskInfo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalTaskInfoLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<script>
    var task_table = $("#tasks_global_table").DataTable({
        "ajax": "/ondemand/admin/tasks.php?action=get_task_list",
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand display-task-info");
        },
        "order": [1,'asc'],
        "dom": 'rti',
        "paging": false
    });
</script>