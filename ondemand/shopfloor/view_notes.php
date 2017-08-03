<?php
require '../../includes/header_start.php';

$queue_ID = sanitizeInput($_REQUEST['queueID']);

$op_queue_qry = $dbconn->query("SELECT * FROM op_queue WHERE id = '{$queue_ID}'");
$op_queue = $op_queue_qry->fetch_assoc();

$notes_qry = $dbconn->query("SELECT * FROM op_queue WHERE so_parent = '{$op_queue['so_parent']}' AND room = '{$op_queue['room']}' AND iteration = '{$op_queue['iteration']}' ORDER BY id DESC LIMIT 1,1;");
$notes = $notes_qry->fetch_assoc();
?>

<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title">Notes for <?php echo "{$op_queue['so_parent']}{$op_queue['room']}-{$op_queue['iteration']}"; ?></h4>
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