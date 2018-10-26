<?php
require '../includes/header_start.php';
?>
<style>
  .tmp_main {
    clear: both;
    border: none;
  }

  div {
    padding: 2px 5px;
    text-align: center;
    border: 1px solid #000;
  }

  @media print {
    div {
      page-break-inside: avoid;
    }
  }
</style>

<?php
$door_qry = $dbconn->query("SELECT * FROM vin_schema WHERE segment = 'door_design'");
$i = 1;

echo '<div class="tmp_main">';

while($door = $door_qry->fetch_assoc()) {

  echo "<div style='float:left;'><h1>{$door['value']}</h1>";
  echo "<img src='/assets/images/vin/{$door['image']}' style='max-height:320px;' /></div>";

  if($i >= 4) {
    echo '</div>';
    echo '<div class="tmp_main">';
    $i = 0;
  }

  $i++;
}
?>