<?php
require '../includes/header_start.php';

if($_REQUEST['action'] === "create") {
    $bracket_name = sanitizeInput($_REQUEST['bracket_name'], $dbconn); // grab the bracket name
    $department = sanitizeInput($_REQUEST['department'], $dbconn); // grab the department
    $bracket_description = sanitizeInput($_REQUEST['id_list'], $dbconn); // grab the id list

    if($bracket_name !== '' && $bracket_description !== '') {
        if($dbconn->query("INSERT INTO bracket_descriptions (description, department, name, created_on) VALUES ('$bracket_description', '$department', '$bracket_name', NOW());")) {
            echo "<S> Inserted successfully!";
        } else {
            dbLogSQLErr($dbconn, true);
        }
    } else {
        echo "<E> No bracket definition or bracket name.";
    }

    die();
}

require '../includes/header_end.php';
?>

<script>
    function buildBracket() {
        var id_list = $("#bracket_builder  .draggable_btn").map(function() {
            return this.id;
        }).get().join("|");

        $.post("create_bracket.php?action=create", $("#bracket_info").serialize() + "&id_list=" + id_list, function(response) {
            console.log(response);
        });
    }
</script>

<!-- Treeview css -->
<link href="/assets/plugins/jstree/style.css" rel="stylesheet" type="text/css"/>

<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <!--<div class="btn-group pull-right m-t-15">
            <button type="button" class="btn btn-custom dropdown-toggle waves-effect waves-light"
                    data-toggle="dropdown" aria-expanded="false">Settings <span class="m-l-5"><i
                        class="fa fa-cog"></i></span></button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#">Action</a>
                <a class="dropdown-item" href="#">Another action</a>
                <a class="dropdown-item" href="#">Something else here</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Separated link</a>
            </div>

        </div>-->
        <h4 class="page-title">Create New Bracket</h4>
    </div>
</div>

<div class="row" id="op_constraint">
    <div class="col-md-6">
        <div class="card-box">
            <div class="row">
                <h2>Available Operations</h2>
            </div>

            <div class="row" id="available_ops">
                <?php
                $query = $dbconn->query("SELECT * FROM operations");
                
                while($result = $query->fetch_assoc()) {
                    echo "<div class='btn btn-block draggable_btn' style='background-color: {$result['color']}' id='{$result['id']}' ondblclick='$(\"#bracket_builder\").append($(\"#{$result['id']}\").clone()).html();$(this).remove();'><i class='{$result['icon']} m-r-5'></i> <span>{$result['op_id']} - {$result['job_title']}</span></div>";
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card-box">
            <div class="row">
                <h2>Bracket Builder</h2>
            </div>

            <div class="row">
                <form id="bracket_info" method="post">
                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="bracket_name">Bracket Name</label>
                            <input class="form-control" type="text" placeholder="Bracket Name" id="bracket_name" name="bracket_name" tabindex="1" autofocus>
                        </fieldset>
                    </div>
                    <div class="col-md-4">
                        <fieldset class="form-group">
                            <label for="department">Department</label>
                            <select name="department" id="department" class="form-control" tabindex="2">
                                <option value="Multi">Multiple</option>
                                <option value="Sales" selected>Sales</option>
                                <option value="Pre-production">Pre-production</option>
                                <option value="Sample Door">Sample Door</option>
                                <option value="Drawer & Doors">Drawer & Doors</option>
                                <option value="Custom">Custom</option>
                                <option value="Box">Box</option>
                            </select>
                        </fieldset>
                    </div>
                </form>

                <div class="col-md-4 text-md-center" style="padding-top: 27px;">
                    <fieldset class="form-group">
                        <button onclick="buildBracket();" class="btn btn-primary waves-effect" id="build_bracket" tabindex="3">Build Bracket</button>
                    </fieldset>
                </div>
            </div>

            <div class="row" id="bracket_builder" style="min-height: 280px;">

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