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
                    <div class="card-box table-responsive" style="min-height:42vh;">
                        <table id="quote_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="40%">Quote</th>
                                <th>Sales</th>
                                <th>Sample</th>
                                <th>SO#</th>
                            </tr>
                            </thead>
                            <tbody id="quote_table"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card-box table-responsive" style="min-height:42vh;">
                        <table id="orders_global_table" class="table table-striped table-bordered" width="100%">
                            <thead>
                            <tr>
                                <th width="40%">Order</th>
                                <th>Pre-Production</th>
                                <th>Main</th>
                                <th>SO#</th>
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
                                <th width="140px">Room</th>
                                <th width="215px">Operation</th>
                                <th width="80px">Release Date</th>
                                <th width="100px">Operation Time</th>
                                <th width="80px">Assignee</th>
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
        scrollY: '31.5vh',
        scrollCollapse: true,
        "dom": '<"#quote_header.dt-custom-header">tipr',
        "columnDefs": [
            {"targets": [0],"orderable": false},
            {"targets": [3], "visible": true}
        ],
        "order": [[3, "asc"]]
    });

    var order_table = $("#orders_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/dashboard.php?action=display_orders",
        "createdRow": function (row, data, dataIndex) {
            $(row).addClass("cursor-hand view_so_info");
        },
        "paging": false,
        scrollY: '31.5vh',
        scrollCollapse: true,
        "dom": '<"#order_header.dt-custom-header">tipr',
        "columnDefs": [
            {"targets": [0],"orderable": false},
            {"targets": [3], "visible": true}
        ],
        "order": [[3, "asc"]]
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
        scrollY: '31.5vh',
        scrollCollapse: true,
        "dom": '<"#active_header.dt-custom-header">tipr',
        "columnDefs": [
            {"targets": [0], "orderable": false, className: "nowrap"}
        ],
        "order": [[1, "desc"]]
    });

    var queue_table = $("#queue_ops_global_table").DataTable({
        "ajax": "/ondemand/shopfloor/dashboard.php?action=display_job_queue&queue=" + queue,
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
            {"targets": [8], "visible": false, "searchable": false, "type": "num-html"}
        ],
        "order": [[8, "desc"]],
        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // this displays the numbering of priorities, we already sort based on weight which is an arbitrary number
            var index = iDisplayIndexFull + 1;
            $('td:eq(1)', nRow).html(index);
            return nRow;
        }
//        ,rowReorder: {
//            enable: false,
//            selector: "tr",
//            dataSrc: 8
//        }
    });

    $("#queue_header").html("<h4 class='pull-left'>Operations for " + op_queue_list + "</h4><!--<span class='pull-right'><button class='btn btn-dark waves-effect waves-light' id='#edit_priorities'> <i class='fa fa-pencil-square m-r-5'></i> <span>Edit Priorities</span> </button></span>-->");
    $("#quote_header").html("<h4>Quotes</h4>");
    $("#order_header").html("<h4>Orders</h4>");
    $("#active_header").html("<h4>Operations (Active) for <?php echo $_SESSION['shop_user']['name']; ?></h4>");

    dash_auto_interval = setInterval(function() {
        <?php
        if($_SESSION['userInfo']['account_type'] <= 4) {
        ?>
        quote_table.ajax.reload(null, false);
        order_table.ajax.reload(null, false);
        <?php
        }
        ?>
        active_table.ajax.reload(null,false);
        updateQueuedJobs();
    }, 5000);
</script>