<?php
require '../includes/header_start.php';
?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>

  <!-- JQuery & JQuery UI -->
  <script src="/assets/js/jquery.min.js"></script>
  <script src="/includes/js/jquery-ui.min.js"></script>
  <link href="/includes/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>

  <!-- Global JS functions -->
  <script src="/includes/js/functions.js?v=<?php echo VERSION; ?>"></script>
  <link href="https://fonts.googleapis.com/css?family=Patua+One" rel="stylesheet">

  <!-- Modernizr js -->
  <script src="/assets/js/modernizr.min.js"></script>

  <!-- SocketIO -->
  <script src="/server/node_modules/socket.io-client/dist/socket.io.js"></script>

  <!-- Toastr setup -->
  <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

  <!-- Fancytree -->
  <link rel="stylesheet" type="text/css" href="/assets/plugins/fancytree/skin-win8-n/ui.fancytree.css"/>

  <!-- Datatables -->
  <link href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.0/css/rowReorder.dataTables.min.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.1.3/css/fixedHeader.dataTables.min.css"/>
  <link href="/assets/plugins/datatables/datatables.paginate.fix.css" rel="stylesheet" type="text/css"/>

  <!-- Date Picker -->
  <link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

  <!-- Alert Windows -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">

  <!-- Select2 -->
  <link href="/assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
  <script src="/assets/plugins/select2/js/select2.min.js"></script>

  <!-- DHTMLX -->
  <link rel="stylesheet" href="/assets/css/dhtmlx/dhtmlx.min.css" type="text/css">
  <script src="https://cdn.dhtmlx.com/edge/dhtmlx.js" type="text/javascript"></script>
</head>

<body>

<?php

//outputPHPErrs();

$global_qry = $dbconn->query('SELECT bc.id AS catID, bg.id AS itemID, bc.*, bg.* FROM batch_category bc LEFT JOIN batch_global bg on bc.id = bg.category_id WHERE bc.enabled = TRUE AND bg.enabled = TRUE ORDER BY bc.sort_order, bg.`group`, bg.sort_order ASC');

$prev_cat = null;
$prev_group = null;
?>

<style>
  .global_group {
    border: 1px solid #000;
    border-radius: 4px;
    padding: 5px;
  }
</style>

<?php

echo '<table class="global_group"><tr>';

while($global = $global_qry->fetch_assoc()) {
  // if we're switching to a new category name
  if($global['category_name'] !== $prev_cat) {
    $prev_cat = $global['category_name']; // first tell the system that we're working on the new category
    $prev_group = $global['group']; // tell the system that we're creating the first group (always new)

    // now we're going to create the select dropdown, the first option group AND the first item
    echo "</td></tr><tr></select>
    <td><label for='global_{$global['catID']}'>{$global['category_name']}: </label></td>
    <td><select name='{$global['catID']}' id='global_{$global['catID']}'>
      <option value='' selected disabled>Not Selected</option>
      <optgroup label='{$global['group']}'>
        <option value='{$global['itemID']}'>{$global['name']}</option>";
  } else {
    // now we're going to create the remaining items and option groups
    if($global['group'] !== $prev_group) {
      $prev_group = $global['group'];
      echo "</optgroup><optgroup label='{$global['group']}'>";
    }

    echo "<option value='{$global['itemID']}'>{$global['name']}</option>";
  }
}

echo '</tr></table>'; ?>
</body>

<script>
  $(".global_group").draggable();
</script>

<!-- jQuery  -->
<script src="/assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/waves.js"></script>
<script src="/assets/js/jquery.nicescroll.js"></script>

<!-- custom dropdown -->
<script src="/includes/js/custom_dropdown.min.js?v=<?php echo VERSION; ?>"></script>

<!-- Toastr setup -->
<script src="/assets/plugins/toastr/toastr.min.js"></script>

<!-- Datatables -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/b-1.5.2/b-html5-1.5.2/b-print-1.5.2/fh-3.1.4/rg-1.0.3/sc-1.5.0/sl-1.2.6/datatables.min.js"></script>

<!-- Moment.js for Timekeeping -->
<script src="/assets/plugins/moment/moment.js"></script>

<!-- Alert Windows -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>

<!-- Counter Up  -->
<script src="/assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
<script src="/assets/plugins/counterup/jquery.counterup.min.js"></script>

<!-- App js -->
<script src="/assets/js/jquery.core.js"></script>
<script src="/assets/js/jquery.app.js"></script>

<!-- Tinysort -->
<script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

<!-- JScroll -->
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js"></script>

<!-- Math, fractions and more -->
<script src="/assets/plugins/math.min.js"></script>

<!-- Pricing program -->
<script src="/html/pricing/pricing.js?v=<?php echo VERSION; ?>"></script>

<!-- Fancytree -->
<script src="/assets/plugins/fancytree/jquery.fancytree.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.filter.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.dnd.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.edit.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.gridnav.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.table.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.persist.js"></script>
<script src="/assets/plugins/fancytree/jquery.fancytree.fixed.js"></script>

<!-- MapHilight - for Area Maps on images, dashboard circle display mostly -->
<script src="/assets/plugins/maphilight/jquery.maphilight.min.js"></script>

<!-- Float TableHead -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/2.1.2/jquery.floatThead.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/ui-contextmenu/jquery.ui-contextmenu.min.js"></script>

<!-- Unsaved Changes -->
<script src="/assets/js/unsaved_alert.min.js?v=<?php echo VERSION; ?>"></script>

<!-- Sticky table header -->
<script src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<!-- Association management module -->
<script src="/includes/js/association.min.js"></script>

<script src="/assets/plugins/dhtmlxScheduler/dhtmlxscheduler.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" href="/assets/plugins/dhtmlxScheduler/dhtmlxscheduler_material.css" type="text/css"  title="no title" charset="utf-8">

<script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_limit.js" type="text/javascript" charset="utf-8"></script>
<script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_tooltip.js" type="text/javascript" charset="utf-8"></script>
<script src="/assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_recurring.js" type="text/javascript"></script>

<link rel="stylesheet" href="/html/calendar/ajax/events.php?action=getEventCSS&v=<?php echo VERSION; ?>" type="text/css" charset="utf-8">
</html>