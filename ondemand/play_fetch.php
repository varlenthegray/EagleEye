<?php
require '../includes/header_start.php';

switch($_REQUEST['action']) {
    case 'get_dealer_info':
        $dealer = sanitizeInput($_REQUEST['dealer_code']);

        $dealer_qry = $dbconn->query("SELECT * FROM dealers WHERE id = '$dealer'");

        if($dealer_qry->num_rows > 0) {
            $dealer_info = $dealer_qry->fetch_assoc();

            echo json_encode($dealer_info);
        }

        break;
    case 'get_next_iteration':
        $room_id = sanitizeInput($_REQUEST['roomid']);

        $room_qry = $dbconn->query("SELECT * FROM rooms WHERE id = $room_id");

        if($room_qry->num_rows > 0) {
            $room = $room_qry->fetch_assoc();

            if($_REQUEST['output'] === 'sequence') {
                $highest_seq_qry = $dbconn->query("SELECT MAX(iteration) as iteration FROM rooms WHERE so_parent = '{$room['so_parent']}' AND room = '{$room['room']}'");
                $highest_seq = $highest_seq_qry->fetch_assoc();
                $seq_iteration = $highest_seq['iteration'];

                $next_seq = (double)$seq_iteration + 1.00;

                $sequence = explode(".", $next_seq);

                echo $sequence[0] . ".01";
            } else {
                $cur_seq = explode(".", $room['iteration']);

                $highest_it_qry = $dbconn->query("SELECT MAX(iteration) as iteration FROM rooms WHERE so_parent = '{$room['so_parent']}' AND room = '{$room['room']}' AND iteration LIKE '{$cur_seq[0]}%'");
                $highest_it = $highest_it_qry->fetch_assoc();

                $iteration = $highest_it['iteration'];

                echo (double)$iteration + 0.01;
            }
        }

        break;
    case 'get_note':
        $room_id = sanitizeInput($_REQUEST['room_id']);
        $note_type = sanitizeInput($_REQUEST['note_type']);

        $note_qry = $dbconn->query("SELECT * FROM notes WHERE note_type = '$note_type' AND type_id = '$room_id'");

        if($note_qry->num_rows > 0) {
            $note = $note_qry->fetch_assoc();

            echo json_encode($note, true);
        }

        break;
}