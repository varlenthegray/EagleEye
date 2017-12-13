<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered tablesorter" id="so_global_list">
                        <thead>
                        <tr>
                            <th>SO#</th>
                            <th>PROJECT/CUSTOMER</th>
                            <th>PROJECT MANAGER</th>
                            <th>DEALER/CONTRACTOR</th>
                        </tr>
                        </thead>
                        <tbody id="so_global_list_breakdown">
                        <tr>
                            <td colspan="9">No SO's to display.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $("#so_global_list").DataTable({
        "ajax": "/ondemand/so_actions.php?action=get_so_list",
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
        },
        "order": [0,'desc'],
        "dom": 'rti',
        "paging": false
    });
</script>