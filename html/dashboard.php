<?php
require '../includes/header_start.php';
?>

<div class="row">
    <?php if($bouncer->validate('view_quotes') || $bouncer->validate('view_orders')) { ?>

    <div class="col-md-6">
        <div class="col-md-12">
            <?php if($bouncer->validate('view_quotes')) { ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive" style="min-height:42vh;">
                        <table id="quote_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="5%">SO#</th>
                                <th width="40%">Project</th>
                                <th>Sales</th>
                                <th>Sample</th>
                            </tr>
                            </thead>
                            <tbody id="quote_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php } if($bouncer->validate('view_orders')) { ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive" style="min-height:42vh;">
                        <table id="orders_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="5%">SO#</th>
                                <th width="40%">Project</th>
                                <th>Pre-Production</th>
                                <th>Main</th>
                            </tr>
                            </thead>
                            <tbody id="orders_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php } ?>
        </div>
    </div>

    <?php } if($bouncer->validate('view_operation')) { ?>
    <div class="col-md-6">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive" style="min-height:42vh;">
                        <table id="active_ops_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th style="min-width:75px;">&nbsp;</th>
                                <th width="35px">SO#</th>
                                <th width="125px">Room</th>
                                <th width="125px">Department</th>
                                <th width="225px">Operation</th>
                                <th width="90px">Activated Time</th>
                                <th width="50px">Time In</th>
                            </tr>
                            </thead>
                            <tbody id="active_ops_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive" style="min-height:42vh;">
                        <table id="queue_ops_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="23px" class="nowrap">&nbsp;</th>
                                <th width="8px">#</th>
                                <th width="50px">SO#</th>
                                <th width="220px">Room</th>
                                <th width="215px">Operation</th>
                                <th width="80px">Release Date</th>
                                <th width="100px">Operation Time</th>
                                <th width="100px">Weight</th>
                            </tr>
                            </thead>
                            <tbody id="queue_ops_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php } ?>
</div>

<!-- modal -->
<div id="modalStartJob" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalStartJobLabel" aria-hidden="true">
    <!-- for flexibility, I'm going to display this via AJAX data return -->
</div>
<!-- /.modal -->

<!-- modal -->
<div id="modalUpdateJob" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalUpdateJob" aria-hidden="true">
    <!-- same here, displayed via AJAX -->
</div>
<!-- /.modal -->

<script>
    // TODO: Adjust the JS output based on permissions

    var op_queue_list = "<?php
        echo "<select class='ignoreSaveAlert' name='viewing_queue' id='viewing_queue'>";
        echo "<option value='self'>{$_SESSION['shop_user']['name']}</option>";

        $deptartments = json_decode($_SESSION['shop_user']['department']);
        $default = $_SESSION['shop_user']['default_queue'];

        foreach($deptartments as $department) {
            if($department === $default) {
                $selected = 'selected';
            } else {
                $selected = '';
            }

            echo "<option value='$department' $selected>$department</option>";
        }
        echo '</select>';
        ?>";

    <?php if($bouncer->validate('view_quotes')) { ?>
        var quote_table = $("#quote_global_table").DataTable({
            "ajax": "/ondemand/display_actions.php?action=display_quotes",
            "createdRow": function (row, data, dataIndex) {
                $(row).addClass("cursor-hand view_so_info");
            },
            "paging": false,
            scrollY: '31.5vh',
            scrollCollapse: true,
            "dom": '<"#quote_header.dt-custom-header">tipr',
            "order": [[0, "asc"]]
        });

        $("#quote_header").html("<h4>Quotes</h4>");
    <?php } if($bouncer->validate('view_orders')) { ?>
        var order_table = $("#orders_global_table").DataTable({
            "ajax": "/ondemand/display_actions.php?action=display_orders",
            "createdRow": function (row, data, dataIndex) {
                $(row).addClass("cursor-hand view_so_info");
            },
            "paging": false,
            scrollY: '31.5vh',
            scrollCollapse: true,
            "dom": '<"#order_header.dt-custom-header">tipr',
            "order": [[0, "asc"]]
        });

        $("#order_header").html("<h4>Production</h4>");
    <?php } if($bouncer->validate('view_operation')) { ?>

    var active_table = $("#active_ops_global_table").DataTable({
        "ajax": "/ondemand/display_actions.php?action=display_ind_active_jobs",
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
        },
        "paging": false,
        scrollY: '31.5vh',
        scrollCollapse: true,
        "dom": '<"#active_header.dt-custom-header">tipr',
        "columnDefs": [
            {"targets": [0], "orderable": false, className: "nowrap"}
        ],
        "order": [[1, "desc"]]
    });

    var queue_table = $("#queue_ops_global_table").DataTable({
        "ajax": "/ondemand/display_actions.php?action=display_ind_job_queue&queue=" + queue,
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
            $(row).attr('data-weight', data.weight);
        },
        "paging": false,
        scrollY: '31.5vh',
        scrollCollapse: true,
        "dom": '<"#queue_header.dt-custom-header">tipr',
        "columnDefs": [
            {"targets": [0], "orderable": false},
            {"targets": [6, 7], "visible": false},
            {"targets": [7], "searchable": false, "type": "num-html"}
        ],
        "order": [[7, "desc"]],
        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // this displays the numbering of priorities, we already sort based on weight which is an arbitrary number
            var index = iDisplayIndexFull + 1;
            $('td:eq(1)', nRow).html(index);
            return nRow;
        }
    });

    $("#queue_header").html("<h4 class='pull-left'>Operations for " + op_queue_list + "</h4>");
    $("#active_header").html("<h4>Operations (Active) for <?php echo $_SESSION['shop_user']['name']; ?></h4>");

    updateOpQueue();

    <?php } if($_SESSION['userInfo']['account_type'] <= 4) { ?>
        dash_auto_interval = setInterval(function() {
            quote_table.ajax.reload(null, false);
            order_table.ajax.reload(null, false);
        }, 5000);
    <?php } ?>
</script>