<?php
require '../../../../includes/header_start.php';

//outputPHPErrs();

$so_num = sanitizeInput($_REQUEST['so_num']);

$vin_schema = getVINSchema();

$operations = []; // operation information

// get all operations
$op_qry = $dbconn->query('SELECT * FROM operations');

while($op = $op_qry->fetch_assoc()) {
  $operations[$op['id']] = $op;
}

$so_qry = $dbconn->query("SELECT * FROM sales_order so LEFT JOIN dealers d ON so.dealer_code = d.dealer_id WHERE so_num = '$so_num'");
$so = $so_qry->fetch_assoc();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-4 bracket_info">
      <?php
      $room_qry = $dbconn->query("SELECT * FROM rooms WHERE so_parent = '$so_num' ORDER BY room, iteration ASC");

      function getBracketStatus($bracket_pub) {
        $out['class'] = (bool)$bracket_pub ? 'col-green' : null;
        $out['text'] = (bool)$bracket_pub ? 'Published' : 'Not Published';

        return $out;
      }

      $prev_room = null;
      $prev_sequence = null;

      while($room = $room_qry->fetch_assoc()) {
        $output['sales_marketing_bracket'] = !empty($operations[$room['sales_marketing_bracket']]) ? $operations[$room['sales_marketing_bracket']] : array('job_title' => 'Unassigned');
        $output['shop_bracket'] = !empty($operations[$room['shop_bracket']]) ? $operations[$room['shop_bracket']] : array('job_title' => 'Unassigned');
        $output['preproduction_bracket'] = !empty($operations[$room['preproduction_bracket']]) ? $operations[$room['preproduction_bracket']] : array('job_title' => 'Unassigned');
        $output['press_bracket'] = !empty($operations[$room['press_bracket']]) ? $operations[$room['press_bracket']] : array('job_title' => 'Unassigned');
        $output['paint_bracket'] = !empty($operations[$room['paint_bracket']]) ? $operations[$room['paint_bracket']] : array('job_title' => 'Unassigned');
        $output['custom_bracket'] = !empty($operations[$room['custom_bracket']]) ? $operations[$room['custom_bracket']] : array('job_title' => 'Unassigned');
        $output['shipping_bracket'] = !empty($operations[$room['shipping_bracket']]) ? $operations[$room['shipping_bracket']] : array('job_title' => 'Unassigned');
        $output['assembly_bracket'] = !empty($operations[$room['assembly_bracket']]) ? $operations[$room['assembly_bracket']] : array('job_title' => 'Unassigned');
        $output['welding_bracket'] = !empty($operations[$room['welding_bracket']]) ? $operations[$room['welding_bracket']] : array('job_title' => 'Unassigned');

        $bstat_sales_marketing = getBracketStatus($room['sales_marketing_published']);
        $bstat_shop = getBracketStatus($room['shop_published']);
        $bstat_preprod = getBracketStatus($room['preproduction_published']);
        $bstat_paint = getBracketStatus($room['paint_published']);
        $bstat_custom = getBracketStatus($room['custom_published']);
        $bstat_shipping = getBracketStatus($room['shipping_published']);
        $bstat_assembly = getBracketStatus($room['assembly_published']);
        $bstat_welding = getBracketStatus($room['welding_published']);

        $seq_it = explode('.', $room['iteration']);

        if($prev_room !== $room['room']) {
          $prev_room = $room['room'];
          $prev_sequence = $seq_it[0];

          $room_header = "{$room['room']}{$room['iteration']}: {$room['room_name']}";
        } else {
          if($prev_sequence !== $seq_it[0]) {
            $prev_sequence = $seq_it[0];

            $room_header = "&nbsp;&nbsp;&nbsp;{$room['iteration']}: {$room['room_name']}";
          } else {
            $room_header = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.{$seq_it[1]}: {$room['room_name']}";
          }
        }

        echo /** @lang HTML */
        <<<HEREDOC
        <div class="room_bracket">
          <div class="sticky bracket_header cursor-hand">
          <button class="btn waves-effect btn_secondary disabled" id="show_attachments_room_{$room['id']}"><i class="zmdi zmdi-attachment-alt"></i></button> 
          <button class="btn btn-primary-outline waves-effect add_iteration" data-roomid="{$room['id']}" data-sonum="{$room['so_parent']}" data-addto="sequence" data-iteration="{$room['iteration']}" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add additional sequence" style="font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;visibility:visible;"> S +1</button> 
          <button class="btn btn-primary-outline waves-effect add_iteration" data-roomid="{$room['id']}" data-sonum="{$room['so_parent']}" data-addto="iteration" data-iteration="{$room['iteration']}" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add additional iteration" style="font-size:10px;width:30px;height:22px;margin-top:1px;padding:0;visibility:visible;"> I +.01</button></td>
            $room_header
          </div>
          
          <table class="bracket_details">
            <colgroup>
              <col width="150px">
              <col width="200px">
              <col width="*">
            </colgroup>
            <thead>
              <tr>
                <th>Bracket</th>
                <th>Operation</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr class="{$bstat_sales_marketing['class']}">
                <td>Sales/Marketing:</td>
                <td>{$output['sales_marketing_bracket']['job_title']}</td>
                <td>{$bstat_sales_marketing['text']}</td>
              </tr>
              <tr class="{$bstat_shop['class']}">
                <td>Shop:</td>
                <td>{$output['shop_bracket']['job_title']}</td>
                <td>{$bstat_shop['text']}</td>
              </tr>
              <tr class="{$bstat_preprod['class']}">
                <td>Pre-production:</td>
                <td>{$output['preproduction_bracket']['job_title']}</td>
                <td>{$bstat_preprod['text']}</td>
              </tr>
              <tr class="{$bstat_sales_marketing['class']}">
                <td>Press:</td>
                <td>{$output['press_bracket']['job_title']}</td>
                <td>{$bstat_sales_marketing['text']}</td>
              </tr>
              <tr class="{$bstat_paint['class']}">
                <td>Paint:</td>
                <td>{$output['paint_bracket']['job_title']}</td>
                <td>{$bstat_paint['text']}</td>
              </tr>
              <tr class="{$bstat_custom['class']}">
                <td>Custom:</td>
                <td>{$output['custom_bracket']['job_title']}</td>
                <td>{$bstat_custom['text']}</td>
              </tr>
              <tr class="{$bstat_shipping['class']}">
                <td>Shipping:</td>
                <td>{$output['shipping_bracket']['job_title']}</td>
                <td>{$bstat_shipping['text']}</td>
              </tr>
              <tr class="{$bstat_assembly['class']}">
                <td>Assembly:</td>
                <td>{$output['assembly_bracket']['job_title']}</td>
                <td>{$bstat_assembly['text']}</td>
              </tr>
              <tr class="{$bstat_welding['class']}">
                <td>Welding:</td>
                <td>{$output['welding_bracket']['job_title']}</td>
                <td>{$bstat_welding['text']}</td>
              </tr>
            </tbody>
          </table>
        </div>
HEREDOC;
      }
      ?>
    </div>
  </div>
</div>

<script>
  crmBatch.bracketMgr.init();
</script>