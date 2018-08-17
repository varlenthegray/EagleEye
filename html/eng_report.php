<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="col-md-6">
            <div class="card-box room_sort swimlane" data-type="quote">
                <h3 class="sticky">Quote Request <span class='pull-right'><a href="#" id="quote_lock_unlock" data-status="locked"><i class='zmdi zmdi-lock'></i></a></span></h3>

                <?php
                $prev_so = null;
                $exclude_list = '';
                $sort_order = 'r.so_parent, r.room';

                $list_order_qry = $dbconn->query("SELECT * FROM eng_report WHERE user_id = '{$_SESSION['userInfo']['id']}'");

                if($list_order_qry->num_rows === 1) {
                    $list_order = $list_order_qry->fetch_assoc();

                    if(!empty($list_order['quote_sort'])) {
                        $sort_order = '';

                        $list = json_decode($list_order['quote_sort']);

                        foreach($list as $card) {
                            $sort_order .= "$card,";
                        }

                        $sort_order = rtrim($sort_order, ",");

                        $sort_order = "field(r.id, $sort_order)";
                    }

                    if(!empty($list_order['quote_invisible'])) {
                        $exclude_list = json_decode($list_order['quote_invisible']);
                    }
                }

                $quote_qry = $dbconn->query("SELECT so.id AS soID, r.id AS rID, r.*, o.*, so.* FROM rooms r LEFT JOIN operations o ON r.sales_bracket = o.id LEFT JOIN sales_order so ON r.so_parent = so.so_num WHERE o.op_id LIKE 'QT%' AND responsible_dept = 'Engineering' AND sales_published = TRUE ORDER BY {$sort_order} ASC;");

                if($quote_qry->num_rows > 0) {
                    while($quote = $quote_qry->fetch_assoc()) {
                        $hidden_class = in_array($quote['rID'], $exclude_list, true) ? 'quote_card_hidden' : null;

                        $find_loc = array_search($quote['rID'], $exclude_list, true);

                        echo "<div class='card $hidden_class' id='{$quote['rID']}' data-room-id='{$quote['rID']}' data-so-id='{$quote['soID']}' data-type='quote'>";

                        if (empty($hidden_class)) {
                            $eye_off = '-off';
                            $action = 'quote_hide_card';
                        } else {
                            $eye_off = ' text-secondary';
                            $action = 'quote_show_card';
                        }

                        echo "<h4><a href='#' class='view_so_info' id='{$quote['so_parent']}' style='text-decoration:underline;'>{$quote['so_parent']}-{$quote['dealer_code']}_{$quote['room_name']}</a> <div class='pull-right {$action} cursor-hand' style='display:none;'><i class='zmdi zmdi-eye{$eye_off}'></i></div> </h4>";

                        $card_body_hidden = !empty($hidden_class) ? 'hidden-section' : null;

                        echo "<div class='card_body $card_body_hidden'>";

                        $so_note_qry = $dbconn->query("SELECT * FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'so_inquiry' AND type_id = '{$quote['soID']}' ORDER BY timestamp DESC LIMIT 0, 1;");

                        $so_note_count = $dbconn->query("SELECT count(*) FROM notes WHERE note_type = 'so_inquiry' AND type_id = '{$quote['soID']}';");
                        $so_note_count = $so_note_count->fetch_assoc();

                        if($so_note_qry->num_rows > 0) {
                            while($so_note = $so_note_qry->fetch_assoc()) {
                                $name = explode(' ', $so_note['name']);
                                $first_initial = substr($name[0], 0, 1);
                                $last_initial = substr($name[1], 0, 1);

                                $time = date(DATE_DEFAULT, $so_note['timestamp']);

                                echo "<div style='padding-left:15px;'>$time {$first_initial}{$last_initial}: {$so_note['note']}</div>";
                            }
                        }

                        echo "<div style='padding-left:15px;'><h5>{$quote['room']}{$quote['iteration']}</h5></div>";

                        $room_note_qry = $dbconn->query("SELECT * FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'room_note' AND type_id = '{$quote['rID']}' ORDER BY timestamp DESC LIMIT 0, 1;");

                        $room_note_count = $dbconn->query("SELECT count(*) FROM notes WHERE note_type = 'room_note' AND type_id = '{$quote['rID']}'");
                        $room_note_count = $room_note_count->fetch_assoc();

                        $comment_count = $room_note_count['count(*)'] + $so_note_count['count(*)'];

                        if($room_note_qry->num_rows > 0) {
                            while($room_note = $room_note_qry->fetch_assoc()) {
                                $name = explode(' ', $room_note['name']);
                                $first_initial = substr($name[0], 0, 1);
                                $last_initial = substr($name[1], 0, 1);

                                $time = date(DATE_DEFAULT, $room_note['timestamp']);

                                echo "<div style='padding-left:30px;'>$time {$first_initial}{$last_initial}: {$room_note['note']}</div>";
                            }
                        }

                        if($comment_count > 0) {
                            echo "<div style='padding-top:5px;'><i class='zmdi zmdi-comments'></i> $comment_count</div>";
                        }

                        echo '</div></div>';
                    }
                }
                ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-box room_sort swimlane" data-type="fineng">
                <h3 class="sticky">Final Engineering <span class='pull-right'><a href="#" id="fineng_lock_unlock" data-status="locked"><i class='zmdi zmdi-lock'></i></a></span></h3>

                <?php
                $prev_so = null;
                $exclude_list = '';
                $sort_order = 'r.so_parent, r.room';

                $list_order_qry = $dbconn->query("SELECT * FROM eng_report WHERE user_id = '{$_SESSION['userInfo']['id']}'");

                if($list_order_qry->num_rows === 1) {
                    $list_order = $list_order_qry->fetch_assoc();

                    if(!empty($list_order['fineng_sort'])) {
                        $sort_order = '';

                        $list = json_decode($list_order['fineng_sort']);

                        foreach($list as $card) {
                            $sort_order .= "$card,";
                        }

                        $sort_order = rtrim($sort_order, ",");

                        $sort_order = "field(r.id, $sort_order)";
                    }

                    if(!empty($list_order['fineng_invisible'])) {
                        $exclude_list = json_decode($list_order['fineng_invisible']);
                    }
                }

                $quote_qry = $dbconn->query("SELECT so.id AS soID, r.id AS rID, r.*, o.*, so.* FROM rooms r LEFT JOIN operations o ON r.preproduction_bracket = o.id LEFT JOIN sales_order so ON r.so_parent = so.so_num WHERE o.op_id LIKE 'PR%' AND responsible_dept = 'Engineering' AND preproduction_published = TRUE ORDER BY r.so_parent, r.room ASC;");

                if($quote_qry->num_rows > 0) {
                    while($quote = $quote_qry->fetch_assoc()) {
                        $hidden_class = in_array($quote['rID'], $exclude_list, true) ? 'fineng_card_hidden' : null;

                        $find_loc = array_search($quote['rID'], $exclude_list, true);

                        echo "<div class='card $hidden_class' id='{$quote['rID']}' data-room-id='{$quote['rID']}' data-so-id='{$quote['soID']}' data-type='quote'>";

                        if (empty($hidden_class)) {
                            $eye_off = '-off';
                            $action = 'fineng_hide_card';
                        } else {
                            $eye_off = ' text-secondary';
                            $action = 'fineng_show_card';
                        }

                        echo "<h4><a href='#' class='view_so_info' id='{$quote['so_parent']}' style='text-decoration:underline;'>{$quote['so_parent']}-{$quote['dealer_code']}_{$quote['room_name']}</a> <div class='pull-right {$action} cursor-hand' style='display:none;'><i class='zmdi zmdi-eye{$eye_off}'></i></div> </h4>";

                        $card_body_hidden = !empty($hidden_class) ? 'hidden-section' : null;

                        echo "<div class='card_body $card_body_hidden'>";

                        $so_note_qry = $dbconn->query("SELECT * FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'so_inquiry' AND type_id = '{$quote['soID']}' ORDER BY timestamp DESC LIMIT 0, 1;");

                        $so_note_count = $dbconn->query("SELECT count(*) FROM notes WHERE note_type = 'so_inquiry' AND type_id = '{$quote['soID']}';");
                        $so_note_count = $so_note_count->fetch_assoc();

                        if($so_note_qry->num_rows > 0) {
                            while($so_note = $so_note_qry->fetch_assoc()) {
                                $name = explode(' ', $so_note['name']);
                                $first_initial = substr($name[0], 0, 1);
                                $last_initial = substr($name[1], 0, 1);

                                $time = date(DATE_DEFAULT, $so_note['timestamp']);

                                echo "<div style='padding-left:15px;'>$time {$first_initial}{$last_initial}: {$so_note['note']}</div>";
                            }
                        }

                        echo "<div style='padding-left:15px;'><h5>{$quote['room']}{$quote['iteration']}</h5></div>";

                        $room_note_qry = $dbconn->query("SELECT * FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'room_note' AND type_id = '{$quote['rID']}' ORDER BY timestamp DESC LIMIT 0, 1;");

                        $room_note_count = $dbconn->query("SELECT count(*) FROM notes WHERE note_type = 'room_note' AND type_id = '{$quote['rID']}'");
                        $room_note_count = $room_note_count->fetch_assoc();

                        $comment_count = $room_note_count['count(*)'] + $so_note_count['count(*)'];

                        if($room_note_qry->num_rows > 0) {
                            while($room_note = $room_note_qry->fetch_assoc()) {
                                $name = explode(' ', $room_note['name']);
                                $first_initial = substr($name[0], 0, 1);
                                $last_initial = substr($name[1], 0, 1);

                                $time = date(DATE_DEFAULT, $room_note['timestamp']);

                                echo "<div style='padding-left:30px;'>$time {$first_initial}{$last_initial}: {$room_note['note']}</div>";
                            }
                        }

                        if($comment_count > 0) {
                            echo "<div style='padding-top:5px;'><i class='zmdi zmdi-comments'></i> $comment_count</div>";
                        }

                        echo "</div></div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

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
</div>

<!-- View Card modal -->
<div id="modalViewCard" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalViewCardLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

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
    var sortList = $(".room_sort");

    sortList.sortable({
        containment: "parent",
        disabled: true,
        items: ".card",
        stop: function() {
            var list = [];
            var type = $(this).attr('data-type');

            $(this).find(".card").each(function () {
                list.push($(this).attr('id'));
            });

            $.post("/ondemand/display_actions.php?action=update_eng_order", {items: JSON.stringify(list), type: type}, function(data) {
                $("body").append(data);
            });
        }
    });

    $("body")
        .on("click", "#quote_lock_unlock", function() {
            if($(this).attr("data-status") === 'locked') {
                $(this).attr("data-status", "unlocked");
                $(this).children("i").removeClass("zmdi-lock").addClass("zmdi-lock-open");
                $(".quote_hide_card, .quote_show_card").show();
                $(".quote_card_hidden").show();

                $(".room_sort").sortable("option", "disabled", false);
            } else {
                $(this).attr("data-status", "locked");
                $(this).children("i").removeClass("zmdi-lock-open").addClass("zmdi-lock");
                $(".quote_hide_card, .quote_show_card").hide();
                $(".quote_card_hidden").hide();

                $(".room_sort").sortable("option", "disabled", true);
            }
        })
        .on("click", "#fineng_lock_unlock", function() {
            if($(this).attr("data-status") === 'locked') {
                $(this).attr("data-status", "unlocked");
                $(this).children("i").removeClass("zmdi-lock").addClass("zmdi-lock-open");
                $(".fineng_hide_card, .fineng_show_card").show();
                $(".fineng_card_hidden").show();

                $(".room_sort").sortable("option", "disabled", false);
            } else {
                $(this).attr("data-status", "locked");
                $(this).children("i").removeClass("zmdi-lock-open").addClass("zmdi-lock");
                $(".fineng_hide_card, .quote_show_card").hide();
                $(".fineng_card_hidden").hide();

                $(".room_sort").sortable("option", "disabled", true);
            }
        })
        .on("click", ".card", function(e) {
            if($("#quote_lock_unlock").attr("data-status") === 'locked' && $("#fineng_lock_unlock").attr("data-status") === 'locked') {
                e.stopPropagation();

                $.post("/html/modals/view_card.php", {room_id: $(this).data("room-id"), so_id: $(this).data("so-id"), type: $(this).data("type")}, function(data) {
                    $("#modalViewCard").html(data).modal("show");
                });
            }
        })
        .on("click", ".quote_hide_card", function(e) {
            e.stopPropagation();

            var room_id = $(this).parents(".card").attr("data-room-id");

            $.post("/ondemand/display_actions.php?action=hide_eng_card", {room_id: room_id, type: 'quote'}, function(data) {
                $("body").append(data);
            });

            $(this).parents('.card').addClass('quote_card_hidden').find('.card_body').addClass('hidden-section');
            $(this).children('i').removeClass('zmdi-eye-off').addClass('zmdi-eye text-secondary');
            $(this).removeClass('quote_hide_card').addClass('quote_show_card');
        })
        .on("click", ".fineng_hide_card", function(e) {
            e.stopPropagation();

            var room_id = $(this).parents(".card").attr("data-room-id");

            $.post("/ondemand/display_actions.php?action=hide_eng_card", {room_id: room_id, type: 'fineng'}, function(data) {
                $("body").append(data);
            });

            $(this).parents('.card').addClass('fineng_card_hidden').find('.card_body').addClass('hidden-section');
            $(this).children('i').removeClass('zmdi-eye-off').addClass('zmdi-eye text-secondary');
            $(this).removeClass('fineng_hide_card').addClass('fineng_show_card');
        })
        .on("click", ".quote_show_card", function(e) {
            e.stopPropagation();

            var room_id = $(this).parents(".card").attr("data-room-id");

            $.post("/ondemand/display_actions.php?action=show_eng_card", {room_id: room_id, type: 'quote'}, function(data) {
                $("body").append(data);
            });

            $(this).parents('.card').removeClass('quote_card_hidden').find('.card_body').removeClass('hidden-section');
            $(this).children('i').removeClass('zmdi-eye text-secondary').addClass('zmdi-eye-off');
            $(this).removeClass('quote_show_card').addClass('quote_hide_card');
        })
        .on("click", ".fineng_show_card", function(e) {
            e.stopPropagation();

            var room_id = $(this).parents(".card").attr("data-room-id");

            $.post("/ondemand/display_actions.php?action=show_eng_card", {room_id: room_id, type: 'fineng'}, function(data) {
                $("body").append(data);
            });

            $(this).parents('.card').removeClass('fineng_card_hidden').find('.card_body').removeClass('hidden-section');
            $(this).children('i').removeClass('zmdi-eye text-secondary').addClass('zmdi-eye-off');
            $(this).removeClass('fineng_show_card').addClass('fineng_hide_card');
        })
    ;

    $(function() {
        $(".quote_card_hidden, .fineng_card_hidden").hide();
    });

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

    $("#order_header").html("<h4>Orders</h4>");
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