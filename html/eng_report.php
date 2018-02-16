<?php
require '../includes/header_start.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card-box room_sort swimlane">
            <h3>Quote Request <span class='pull-right'><a href="#" id="lock_unlock" data-status="locked"><i class='zmdi zmdi-lock'></i></a></span></h3>

            <?php
            $prev_so = null;

            $quote_qry = $dbconn->query("SELECT so.id AS soID, r.id AS rID, r.*, o.*, so.* FROM rooms r LEFT JOIN operations o ON r.sales_bracket = o.id LEFT JOIN sales_order so ON r.so_parent = so.so_num WHERE o.op_id LIKE 'QT%' ORDER BY r.so_parent, r.room ASC;");

            if($quote_qry->num_rows > 0) {
                while($quote = $quote_qry->fetch_assoc()) {
                    echo "<div class='card' data-room-id='{$quote['rID']}'>";

                    echo "<h4><a href='#' class='view_so_info' id='{$quote['so_parent']}' style='text-decoration:underline;'>{$quote['so_parent']}-{$quote['dealer_code']}_{$quote['room_name']}</a></h4>";

                    $so_note_qry = $dbconn->query("SELECT * FROM notes n LEFT JOIN user u ON n.user = u.id WHERE note_type = 'so_inquiry' AND type_id = '{$quote['soID']}' ORDER BY timestamp DESC LIMIT 0, 1;");

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

                    if($room_note_qry->num_rows > 0) {
                        while($room_note = $room_note_qry->fetch_assoc()) {
                            $name = explode(" ", $room_note['name']);
                            $first_initial = substr($name[0], 0, 1);
                            $last_initial = substr($name[1], 0, 1);

                            $time = date(DATE_DEFAULT, $room_note['timestamp']);

                            echo "<div style='padding-left:30px;'>$time {$first_initial}{$last_initial}: {$room_note['note']}</div>";
                            echo "<div style='padding-top:5px;'><i class='zmdi zmdi-comments'></i> 2</div>";
                        }
                    }

                    echo "</div>";
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
    $(".room_sort").sortable({
        containment: "parent",
        disabled: true
    });

    $("body")
        .on("click", "#lock_unlock", function() {
            if($(this).attr("data-status") === 'locked') {
                $(this).attr("data-status", "unlocked");
                $(this).children("i").removeClass("zmdi-lock").addClass("zmdi-lock-open");

                $(".room_sort").sortable("option", "disabled", false);
            } else {
                $(this).attr("data-status", "locked");
                $(this).children("i").removeClass("zmdi-lock-open").addClass("zmdi-lock");

                $(".room_sort").sortable("option", "disabled", true);
            }
        })
        .on("click", ".card", function(e) {
            if($("#lock_unlock").attr("data-status") === 'locked') {
                e.stopPropagation();

                $.post("/html/modals/view_card.php", {room_id: $(this).data("room-id")}, function(data) {
                    $("#modalViewCard").html(data).modal("show");
                });
            }
        })
    ;
</script>