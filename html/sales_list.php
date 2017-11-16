<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card-box">
            <div class="col-md-12">
                <table class="table table-bordered tablesorter" id="so_global_list">
                    <thead style="position:sticky;top:18vh;">
                    <tr>
                        <th>SO#</th>
                        <th>PROJECT/ROOM</th>
                        <th>CONTACT</th>
                        <th>STATUS</th>
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

<script>
    $("#so_global_list").DataTable({
        "ajax": "/ondemand/so_actions.php?action=get_sales_list",
        "columnDefs": [{"visible": false, "targets": 2}],
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
        },
        "order": [2,'asc'],
        "dom": 'rti',
        "paging": false,
        "drawCallback": function(settings) {
            var api = this.api();
            var rows = api.rows({page: 'current'}).nodes();
            var last = null;

            api.column(2, {page: 'current'}).data().each(function (group, i) {
                if (last !== group) {
                    $(rows).eq(i).before('<tr class="sales_list_dealer"><td colspan="4" style="padding-left:15px">' + group + '</td></tr>');

                    last = group;
                }
            });
        }
    });
</script>