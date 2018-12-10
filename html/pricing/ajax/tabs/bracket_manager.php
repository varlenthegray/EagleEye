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
        $output['sales_bracket'] = !empty($operations[$room['sales_bracket']]) ? $operations[$room['sales_bracket']] : array('job_title' => 'Unassigned');
        $output['sample_bracket'] = !empty($operations[$room['sample_bracket']]) ? $operations[$room['sample_bracket']] : array('job_title' => 'Unassigned');
        $output['preproduction_bracket'] = !empty($operations[$room['preproduction_bracket']]) ? $operations[$room['preproduction_bracket']] : array('job_title' => 'Unassigned');
        $output['doordrawer_bracket'] = !empty($operations[$room['doordrawer_bracket']]) ? $operations[$room['doordrawer_bracket']] : array('job_title' => 'Unassigned');
        $output['main_bracket'] = !empty($operations[$room['main_bracket']]) ? $operations[$room['main_bracket']] : array('job_title' => 'Unassigned');
        $output['custom_bracket'] = !empty($operations[$room['custom_bracket']]) ? $operations[$room['custom_bracket']] : array('job_title' => 'Unassigned');
        $output['shipping_bracket'] = !empty($operations[$room['shipping_bracket']]) ? $operations[$room['shipping_bracket']] : array('job_title' => 'Unassigned');
        $output['install_bracket'] = !empty($operations[$room['install_bracket']]) ? $operations[$room['install_bracket']] : array('job_title' => 'Unassigned');
        $output['pick_materials_bracket'] = !empty($operations[$room['pick_materials_bracket']]) ? $operations[$room['pick_materials_bracket']] : array('job_title' => 'Unassigned');
        $output['edgebanding_bracket'] = !empty($operations[$room['edgebanding_bracket']]) ? $operations[$room['edgebanding_bracket']] : array('job_title' => 'Unassigned');

        $bstat_sales = getBracketStatus($room['sales_published']);
        $bstat_sample = getBracketStatus($room['sample_published']);
        $bstat_preprod = getBracketStatus($room['preproduction_published']);
        $bstat_main = getBracketStatus($room['main_published']);
        $bstat_custom = getBracketStatus($room['custom_published']);
        $bstat_shipping = getBracketStatus($room['shipping_published']);
        $bstat_install = getBracketStatus($room['install_bracket_published']);
        $bstat_pick = getBracketStatus($room['pick_materials_published']);
        $bstat_eb = getBracketStatus($room['edgebanding_published']);

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
              <tr class="{$bstat_sales['class']}">
                <td>Sales:</td>
                <td>{$output['sales_bracket']['job_title']}</td>
                <td>{$bstat_sales['text']}</td>
              </tr>
              <tr class="{$bstat_sample['class']}">
                <td>Sample:</td>
                <td>{$output['sample_bracket']['job_title']}</td>
                <td>{$bstat_sample['text']}</td>
              </tr>
              <tr class="{$bstat_preprod['class']}">
                <td>Pre-production:</td>
                <td>{$output['preproduction_bracket']['job_title']}</td>
                <td>{$bstat_preprod['text']}</td>
              </tr>
              <tr class="{$bstat_sales['class']}">
                <td>Door/Drawer:</td>
                <td>{$output['doordrawer_bracket']['job_title']}</td>
                <td>{$bstat_sales['text']}</td>
              </tr>
              <tr class="{$bstat_main['class']}">
                <td>Main:</td>
                <td>{$output['main_bracket']['job_title']}</td>
                <td>{$bstat_main['text']}</td>
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
              <tr class="{$bstat_install['class']}">
                <td>Installation:</td>
                <td>{$output['install_bracket']['job_title']}</td>
                <td>{$bstat_install['text']}</td>
              </tr>
              <tr class="{$bstat_pick['class']}">
                <td>Pick/Materials:</td>
                <td>{$output['pick_materials_bracket']['job_title']}</td>
                <td>{$bstat_pick['text']}</td>
              </tr>
              <tr class="{$bstat_eb['class']}">
                <td>Edgebanding:</td>
                <td>{$output['edgebanding_bracket']['job_title']}</td>
                <td>{$bstat_eb['text']}</td>
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