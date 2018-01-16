<?php
require '../includes/header_start.php';

$queue_ID = sanitizeInput($_REQUEST['queueID']);

// TODO: Wtf? Why was this query commented out?
// FIXME: Query needs to reference notes table, pull out everything for that operational room.

$op_queue_qry = $dbconn->query("SELECT * FROM op_queue LEFT JOIN rooms ON op_queue.room_id = rooms.id LEFT JOIN operations ON op_queue.operation_id = operations.id WHERE op_queue.id = '{$queue_ID}'");
$op_queue = $op_queue_qry->fetch_assoc();

$notes_qry = $dbconn->query("SELECT * FROM op_queue WHERE room_id = {$op_queue['room_id']} ORDER BY id DESC LIMIT 1,1;");
$notes = $notes_qry->fetch_assoc();

if($op_queue['job_title'] !== 'Honey Do') {
    $title = "{$op_queue['so_parent']}{$op_queue['room']}-{$op_queue['iteration']}";
} else {
    $title = "Honey Do ID: {$op_queue['room_id']}";
}
?>

<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title">Notes for <?php echo $title; ?></h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <?php
                        echo (!empty($notes['notes'])) ? $notes['notes'] : "No notes logged.";
                    ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Read & Confirmed</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->