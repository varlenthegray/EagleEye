<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="col-md-12">
                <table class="table table-bordered tablesorter" id="tasks_global_table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>NAME</th>
                        <th>SHORT DESC</th>
                        <th>ASSIGNED TO</th>
                        <th>CREATED</th>
                        <th>PRIORITY</th>
                        <th>ETA</th>
                        <th>LAST UPDATED</th>
                        <th>% COMPLETED</th>
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

<script>
    var task_table = $("#tasks_global_table").DataTable({
        "ajax": "/ondemand/admin/tasks.php?action=get_task_list",
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand display-queued-job-info");
        },
        "order": [[0,"desc"]],
        "dom": 'rti',
        "pageLength": 25
    });
</script>