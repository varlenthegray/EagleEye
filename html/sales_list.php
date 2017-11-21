<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="col-md-2 no-print" style="position:fixed;top:10vh;overflow:auto;height:87vh;">
                <a class="btn btn-primary btn-block waves-effect waves-light sales_list_print">Print</a><br /><br />

                <table width="100%" style="background-color:#FFFFFF;">
                    <tr>
                        <td><div class="checkbox checkbox-primary"><input id="job_status_lost" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_lost">Lost</label></div></td>
                    </tr>
                    <tr>
                        <td><div class="checkbox checkbox-primary"><input id="job_status_quote" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_quote">Quote</label></div></td>
                    </tr>
                    <tr>
                        <td><div class="checkbox checkbox-primary"><input id="job_status_job" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_job">Job</label></div></td>
                    </tr>
                    <tr>
                        <td><div class="checkbox checkbox-primary"><input id="job_status_completed" class="ignoreSaveAlert" type="checkbox" checked><label for="job_status_completed">Completed</label></div></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <?php
                    $dealer_qry = $dbconn->query("SELECT * FROM dealers ORDER BY dealer_id ASC;");

                    while($dealer = $dealer_qry->fetch_assoc()) {
                        $indent = (strlen($dealer['dealer_id']) > 3) ? "style='margin-left:20px;'" : null;

                        echo "<tr style='border:1px solid #000;'><td style='padding:2px;'><div class='checkbox checkbox-primary' $indent><input class='ignoreSaveAlert hide_dealer' id='hide_dealer_{$dealer['dealer_id']}' data-dealer-id='{$dealer['dealer_id']}' type='checkbox' checked><label for='hide_dealer_{$dealer['dealer_id']}'>{$dealer['dealer_id']}: {$dealer['contact']}</label></div></td></tr>";
                    }
                    ?>
                </table>
            </div>

            <div class="col-md-4 col-md-offset-3">
                <table class="table table-bordered tablesorter" id="so_global_list">
                    <thead>
                    <tr>
                        <th>SO#</th>
                        <th>PROJECT/ROOM</th>
                        <th>CONTACT</th>
                        <th>STATUS</th>
                        <th>DEALER CODE</th>
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
        "columnDefs": [
            {"visible": false, "targets": [2, 4]},
            {"orderable": false, "targets": [0, 1, 3]}
        ],
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand view_so_info ignoreSaveAlert dealer_" + data[4]);

            switch(data[3]) {
                case 'Lost':
                    $(row).addClass("room_lost");
                    break;

                case 'Quote':
                    $(row).addClass("room_quote");
                    break;

                case 'Job':
                    $(row).addClass("room_job");
                    break;

                case 'Completed':
                    $(row).addClass("room_completed");
                    break;

                default:
                    $(row).addClass("highlight_so");
                    break;
            }
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

                    $(rows).eq(i).before('<tr class="sales_list_dealer ignoreSaveAlert"><td colspan="3" style="padding-left:15px">' + group + '</td></tr>');

                    last = group;
                }
            });
        }
    });

    $("body")

    ;
</script>