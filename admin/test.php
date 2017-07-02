<?php
require ("../includes/header_start.php");

if($_GET['action'] === 'json') {
    $json_qry = $dbconn->query("SELECT * FROM rooms WHERE id = 11");
    $json = $json_qry->fetch_assoc();

    print_r($json['individual_bracket_buildout']);
    die();
}

require ("../includes/header_end.php");
?>

<div id="output" style="color:#FFF;font-weight:bold;"></div>

<script>
    $.post("/admin/test.php?action=json", function(data) {
        var json = JSON.parse(data);

        console.log(json);
    });
</script>


<?php
require ("../includes/footer_start.php");
require ("../includes/footer_end.php");
?>