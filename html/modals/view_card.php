<?php
require '../../includes/header_start.php';

$room_id = $_REQUEST['room_id'];

$quote_qry = $dbconn->query("SELECT so.id AS soID, r.id AS rID, r.*, o.*, so.* FROM rooms r LEFT JOIN operations o ON r.sales_bracket = o.id LEFT JOIN sales_order so ON r.so_parent = so.so_num WHERE r.id = '$room_id';");
$quote = $quote_qry->fetch_assoc();
?>

<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title"><?php echo "{$quote['so_parent']}-{$quote['dealer_code']}_{$quote['room_name']}"; ?></h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <?php

                    ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">Close</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->