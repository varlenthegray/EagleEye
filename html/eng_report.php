<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card-box room_sort swimlane">
            <h3>Quote Request <span class='pull-right'><a href="#" id="lock_unlock" data-status="locked"><i class='zmdi zmdi-lock'></i></a></span></h3>

            <?php
            $prev_so = null;
            $exclude_list = '';
            $sort_order = "r.so_parent, r.room";

            $list_order_qry = $dbconn->query("SELECT * FROM eng_report WHERE user_id = '{$_SESSION['userInfo']['id']}'");

            if($list_order_qry->num_rows === 1) {
                $list_order = $list_order_qry->fetch_assoc();

                if(!empty($list_order['order'])) {
                    $sort_order = '';

                    $list = json_decode($list_order['order']);

                    foreach($list as $card) {
                        $sort_order .= "$card,";
                    }

                    $sort_order = rtrim($sort_order, ",");

                    $sort_order = "field(r.id, $sort_order)";
                }

                if(!empty($list_order['hidden'])) {
                    $exclude_list = json_decode($list_order['hidden']);
                }
            }

            $quote_qry = $dbconn->query("SELECT so.id AS soID, r.id AS rID, r.*, o.*, so.* FROM rooms r LEFT JOIN operations o ON r.sales_bracket = o.id LEFT JOIN sales_order so ON r.so_parent = so.so_num WHERE o.op_id LIKE 'QT%' ORDER BY $sort_order ASC;");

            if($quote_qry->num_rows > 0) {
                while($quote = $quote_qry->fetch_assoc()) {
                    $hidden_class = (in_array($quote['rID'], $exclude_list)) ? 'card_hidden' : null;

                    $find_loc = array_search($quote['rID'], $exclude_list);

                    echo "<div class='card $hidden_class' id='{$quote['rID']}' data-room-id='{$quote['rID']}' data-so-id='{$quote['soID']}' data-type='quote'>";

                    if (empty($hidden_class)) {
                        $eye_off = "-off";
                        $action = "hide_card";
                    } else {
                        $eye_off = " text-secondary";
                        $action = "show_card";
                    }

                    echo "<h4><a href='#' class='view_so_info' id='{$quote['so_parent']}' style='text-decoration:underline;'>{$quote['so_parent']}-{$quote['dealer_code']}_{$quote['room_name']}</a> <div class='pull-right {$action} cursor-hand' style='display:none;'><i class='zmdi zmdi-eye{$eye_off}'></i></div> </h4>";

                    $card_body_hidden = (!empty($hidden_class)) ? "hidden-section" : null;

                    echo "<div class='card_body $card_body_hidden'>";

                    $so_note_qry = $dbconn->query("SELECT * FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'so_inquiry' AND type_id = '{$quote['soID']}' ORDER BY timestamp DESC LIMIT 0, 1;");

                    $so_note_count = $dbconn->query("SELECT count(*) FROM notes WHERE note_type = 'so_inquiry' AND type_id = '{$quote['soID']}';");
                    $so_note_count = $so_note_count->fetch_assoc();

                    if($so_note_qry->num_rows > 0) {
                        while($so_note = $so_note_qry->fetch_assoc()) {
                            $name = explode(" ", $so_note['name']);
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
                            $name = explode(" ", $room_note['name']);
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

<!-- View Card modal -->
<div id="modalViewCard" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalViewCardLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
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

            sortList.find(".card").each(function () {
                list.push($(this).attr('id'));
            });

            $.post("/ondemand/display_actions.php?action=update_eng_order", {items: JSON.stringify(list)}, function(data) {
                $("body").append(data);
            });
        }
    });

    $("body")
        .on("click", "#lock_unlock", function() {
            if($(this).attr("data-status") === 'locked') {
                $(this).attr("data-status", "unlocked");
                $(this).children("i").removeClass("zmdi-lock").addClass("zmdi-lock-open");
                $(".hide_card, .show_card").show();
                $(".card_hidden").show();

                $(".room_sort").sortable("option", "disabled", false);
            } else {
                $(this).attr("data-status", "locked");
                $(this).children("i").removeClass("zmdi-lock-open").addClass("zmdi-lock");
                $(".hide_card, .show_card").hide();
                $(".card_hidden").hide();

                $(".room_sort").sortable("option", "disabled", true);
            }
        })
        .on("click", ".card", function(e) {
            if($("#lock_unlock").attr("data-status") === 'locked') {
                e.stopPropagation();

                $.post("/html/modals/view_card.php", {room_id: $(this).data("room-id"), so_id: $(this).data("so-id"), type: $(this).data("type")}, function(data) {
                    $("#modalViewCard").html(data).modal("show");
                });
            }
        })
        .on("click", ".hide_card", function(e) {
            e.stopPropagation();

            var room_id = $(this).parents(".card").attr("data-room-id");

            $.post("/ondemand/display_actions.php?action=hide_eng_card", {room_id: room_id}, function(data) {
                $("body").append(data);
            });

            $(this).parents('.card').addClass('card_hidden').find('.card_body').addClass('hidden-section');
            $(this).children('i').removeClass('zmdi-eye-off').addClass('zmdi-eye text-secondary');
            $(this).removeClass('hide_card').addClass('show_card');
        })
        .on("click", ".show_card", function(e) {
            e.stopPropagation();

            var room_id = $(this).parents(".card").attr("data-room-id");

            $.post("/ondemand/display_actions.php?action=show_eng_card", {room_id: room_id}, function(data) {
                $("body").append(data);
            });

            $(this).parents('.card').removeClass('card_hidden').find('.card_body').removeClass('hidden-section');
            $(this).children('i').removeClass('zmdi-eye text-secondary').addClass('zmdi-eye-off');
            $(this).removeClass('show_card').addClass('hide_card');
        })
    ;

    $(function() {
        $(".card_hidden").hide();
    });
</script>