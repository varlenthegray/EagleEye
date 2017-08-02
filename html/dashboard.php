<?php
require '../includes/header_start.php';
?>

<div class="row">
    <?php
    if($_SESSION['userInfo']['account_type'] <= 4) {
    ?>
    <div class="col-md-6">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive" style="min-height:38.6vh;">
                        <table id="quote_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="26px">&nbsp;</th>
                                <th>Quote</th>
                            </tr>
                            </thead>
                            <tbody id="quote_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive" style="min-height:38.6vh;">
                        <table id="orders_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th>Order</th>
                            </tr>
                            </thead>
                            <tbody id="orders_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    ?>

    <div class="col-md-6">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive" style="min-height:38.6vh;">
                        <table id="active_ops_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="46px">&nbsp;</th>
                                <th width="50px">SO#</th>
                                <th width="100px">Room</th>
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
                    <div class="card-box table-responsive" style="min-height:38.6vh;">
                        <table id="queue_ops_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="23px">&nbsp;</th>
                                <th width="50px">SO#</th>
                                <th width="100px">Room</th>
                                <th width="225px">Operation</th>
                                <th width="85px">Release Date</th>
                                <th width="85px">Delivery Date</th>
                                <th width="90px">Operation Time</th>
                                <th width="85px">Assignee</th>
                            </tr>
                            </thead>
                            <tbody id="queue_ops_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    function updateQueuedJobs() {
        queue = $('#viewing_queue').val();
        queue_table.ajax.url("/ondemand/shopfloor/dashboard.php?action=display_job_queue&queue=" + queue).load(null,false);
    }

    var op_queue_list = "<?php
        echo "<select name='viewing_queue' id='viewing_queue'>";
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

    <?php
    if($_SESSION['userInfo']['account_type'] <= 4) {
    ?>
    var quote_table = $("#quote_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/dashboard.php?action=display_quotes",
        "createdRow": function (row, data, dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
        },
        "paging": false,
        scrollY: '25.65vh',
        scrollCollapse: true,
        "dom": '<"#quote_header.dt-custom-header">tipr',
        "columnDefs": [{
            "targets": [0],
            "orderable": false
        }],
        "order": [[1, "desc"]]
    });

    var order_table = $("#orders_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/dashboard.php?action=display_orders",
        "createdRow": function (row, data, dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
        },
        "paging": false,
        scrollY: '25.65vh',
        scrollCollapse: true,
        "dom": '<"#order_header.dt-custom-header">tipr'
    });
    <?php
    }
    ?>

    var active_table = $("#active_ops_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/dashboard.php?action=display_active_jobs",
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
        },
        "paging": false,
        scrollY: '25.65vh',
        scrollCollapse: true,
        "dom": '<"#active_header.dt-custom-header">tipr',
        "columnDefs": [{
            "targets": [0],
            "orderable": false
        }],
        "order": [[1, "desc"]]
    });

    var queue_table = $("#queue_ops_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/dashboard.php?action=display_job_queue&queue=" + queue,
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
        },
        "paging": false,
        scrollY: '25.65vh',
        scrollCollapse: true,
        "dom": '<"#queue_header.dt-custom-header">tipr',
        "columnDefs": [{
            "targets": [0],
            "orderable": false
        }],
        "order": [[1, "desc"]]
    });

    $("#queue_header").html("<h4>Operations for " + op_queue_list + "</h4>");
    $("#quote_header").html("<h4>Quotes</h4>");
    $("#order_header").html("<h4>Orders</h4>");
    $("#active_header").html("<h4>Operations (Active) for <?php echo $_SESSION['shop_user']['name']; ?></h4>");

    dash_auto_interval = setInterval(function() {
        <?php
        if($_SESSION['userInfo']['account_type'] <= 4) {
        ?>
        quote_table.ajax.reload(function () {
            /*if(tr.hasClass('shown')) {
                row.child(shownData).show();
                tr.addClass('shown');
            }*/
        }, false);
        order_table.ajax.reload(null, false);
        <?php
        }
        ?>
        active_table.ajax.reload(null,false);
        updateQueuedJobs();
    }, 5000);
</script>