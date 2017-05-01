<?php
require '../includes/header_start.php';

if($_REQUEST['action'] === "create") {
    $bracket_name = sanitizeInput($_REQUEST['bracket_name'], $dbconn); // grab the bracket name
    $department = sanitizeInput($_REQUEST['department'], $dbconn); // grab the department
    $bracket_description = json_decode($_POST['id_list']); // grab the id list

    $final_bracket = array();

    $final_bracket['Sales'] = array();
    $final_bracket['Pre-Production'] = array();
    $final_bracket['Sample'] = array();
    $final_bracket['Drawer & Doors'] = array();
    $final_bracket['Custom'] = array();
    $final_bracket['Box'] = array();


    foreach($bracket_description as $op_id) {
        $qry = $dbconn->query("SELECT * FROM operations WHERE id = '$op_id'");
        $result = $qry->fetch_assoc();

        array_push($final_bracket[$result['department']], $result['id']);
    }

    $final_bracket = array_values($final_bracket); // convert to simple array instead of associative
    $final_bracket = json_encode($final_bracket); // convert it to JSON to store

    if(!empty($bracket_name) && !empty($bracket_description)) {
        if($dbconn->query("INSERT INTO brackets (description, bracket_name, created_on) VALUES ('$final_bracket', '$bracket_name', UNIX_TIMESTAMP())")) {
            echo displayToast("success", "Successfully created bracket.", "Bracket Created");
        } else {
            dbLogSQLErr($dbconn, true);
        }
    } else {
        echo displayToast("error", "Missing bracket name or bracket data.", "Insert Information");
    }

    die();
}

require '../includes/header_end.php';
?>

<script>
    function buildBracket() {
        var id_list = $("#bracket_builder .draggable_btn").map(function() {
            return this.id;
        }).get();

        id_list = JSON.stringify(id_list);

        $.post("bracket_builder.php?action=create", $("#bracket_info").serialize() + "&id_list=" + id_list, function(data) {
            $("body").append(data);
        });
    }
</script>

<div class="row" id="op_constraint">
    <div class="col-md-6">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h2>Available Operations</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12" id="available_ops">
                    <?php
                    $query = $dbconn->query("SELECT * FROM operations");

                    while($result = $query->fetch_assoc()) {
                        echo "<div class='btn btn-block draggable_btn' style='background-color: {$result['color']}' id='{$result['id']}' ondblclick='$(\"#bracket_builder\").append($(\"#{$result['id']}\").clone()).html();$(this).remove();'><i class='{$result['icon']} m-r-5'></i> <span>{$result['op_id']} - {$result['job_title']}</span></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h2>Bracket Builder</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <form id="bracket_info" method="post">
                        <div class="col-md-4">
                            <fieldset class="form-group">
                                <label for="bracket_name">Bracket Name</label>
                                <input class="form-control" type="text" placeholder="Bracket Name" id="bracket_name" name="bracket_name" tabindex="1" autofocus>
                            </fieldset>
                        </div>
                    </form>

                    <div class="col-md-4" style="padding-top: 27px;">
                        <fieldset class="form-group">
                            <button onclick="buildBracket();" class="btn btn-primary waves-effect" id="build_bracket" tabindex="3">Build Bracket</button>
                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12" id="bracket_builder" style="min-height: 280px;">
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        $("#available_ops").sortable({
            connectWith: "#bracket_builder",
            scrollSpeed: 10
        });

        $("#bracket_builder").sortable({
            connectWith: "#available_ops",
            scrollSpeed: 10
        });
    });
</script>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>