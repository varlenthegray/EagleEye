<?php
require '../includes/header_start.php';

if($_REQUEST['action'] === 'list_ops') {
    $id = sanitizeInput($_REQUEST['id']);

    $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$id'");
    $usr = $usr_qry->fetch_assoc();

    $ops_qry = $dbconn->query("SELECT * FROM operations WHERE op_id != '000' AND job_title != 'Bracket Completed' AND responsible_dept != 'N/A' ORDER BY op_id, bracket ASC");

    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "  <th>Op ID</th>";
    echo "  <th>Priority</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody id='operation_list'>";

    $prev_bracket = null;
    $usr_ops = json_decode($usr['ops_available']);

    while($ops = $ops_qry->fetch_assoc()) {
        if($ops['bracket'] !== $prev_bracket) {
            echo "<tr><td colspan='2'><strong>{$ops['bracket']}</strong></td></tr>";
            $prev_bracket = $ops['bracket'];
        }

        if(in_array($ops['id'], $usr_ops)) {
            $style = 'style="background-color:rgba(0,255,0,.8);"';
            $data_enabled = 'true';
            $priority = array_search($ops['id'], $usr_ops) + 1;
            $value = "value='$priority'";
            $tab_index = '0';
            $disabled = null;
        } else {
            $style = null;
            $data_enabled = 'false';
            $priority = null;
            $value = null;
            $tab_index = "-1";
            $disabled = 'disabled';
        }

        echo "<tr class='toggleUserOp cursor-hand' data-enabled='$data_enabled' data-opid='{$ops['id']}' $style>";
        echo "  <td style='padding-left:20px;'>{$ops['op_id']}: {$ops['job_title']} ({$ops['responsible_dept']})</td>";
        echo "  <td><input type='text' style='width:45px;text-align:center;' class='oplist_{$ops['id']}' data-opid='{$ops['id']}' $value tabindex='$tab_index' $disabled /></td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";

    die();
} elseif($_REQUEST['action'] === 'get_user_ops') {
    $id = sanitizeInput($_REQUEST['id']);

    $usr_qry = $dbconn->query("SELECT * FROM user WHERE id = '$id'");
    $usr = $usr_qry->fetch_assoc();

    if(!empty($usr['ops_available'])) {
        echo $usr['ops_available'];
    }

    die();
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card-box">
            <div class="row">
                <div class="col-md-1 sticky save_container" style="display: none;">
                    <h5 class="text-md-center" id="page_title">User Operations</h5>
                    <a class="btn btn-primary btn-block waves-effect waves-light user_op_save" data-id="">Save</a>
                </div>

                <div class="col-md-3">
                    <table class="table table-bordered" style="width:100%;">
                        <thead>
                        <tr>
                            <th>Username</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            $usr_qry = $dbconn->query("SELECT * FROM user WHERE account_status = TRUE ORDER BY name ASC;");

                            while($usr = $usr_qry->fetch_assoc()) {
                                if($usr['id'] !== '16') {
                                    echo "<tr class='cursor-hand assign_ops' id='{$usr['id']}' data-name='{$usr['name']}'>";
                                    echo "  <td>{$usr['name']}</td>";
                                    echo "</tr>";
                                    echo "<tr class='assign_op_window' style='display:none;'>";
                                    echo "</tr>";
                                }
                            }
                        ?>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-3 sticky pl_container" style="max-height:85vh;overflow:auto;">
                    <table class="table table-striped table-bordered" style="width:100%;">
                        <thead>
                        <tr>
                            <th class="text-md-center">Priority</th>
                            <th>Name</th>
                        </tr>
                        </thead>
                        <tbody id="running_priority_list"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function calcEnabled(array) {
        var runningList = [];
        var pl = $(".pl_container")[0];

        $.each(array, function(i, v) {
            $(".oplist_" + v).val(i + 1);

            var optext = $(".oplist_" + v).closest("tr").find("td:first-child").text();

            runningList[i] = "<tr><td class='text-md-center'><input type='text' style='width:45px;text-align:center;' class='oplist_" + v + "' data-opid='" + v + "' value='" + (i + 1) + "' /></td><td>" + optext + "</td></tr>";
        });

        $("#running_priority_list").html(runningList.toString());
        pl.scrollTop = pl.scrollHeight;
    }

    var assignee;
    var enabled = [];

    $("body")
        .on("click", ".assign_ops", function() {
            $(".assign_op_window").html('');

            var userContainer = $(this).next(".assign_op_window");
            assignee = $(this).attr('id');
            var name = $(this).data("name");

            var thisClick = $(this);

            $.post("/html/user_op_mgmt.php?action=list_ops&id=" + assignee, function(data) {
                if(userContainer.is(":hidden")) {
                    userContainer.html("<td>" + data + "</td>");

                    $(".save_container").show(200);
                    $(".user_op_save").attr("data-id", assignee);
                    $("#page_title").html("User Operations:<br />" + name);

                    thisClick.css("background-color", "rgba(63,127,191,.5);");
                } else {
                    $(".save_container").hide(200);
                    $(".user_op_save").attr("data-id", "");

                    thisClick.css("background-color", "transparent");
                }

                $.post("/html/user_op_mgmt.php?action=get_user_ops", {id: assignee}, function(data) {
                    if(data !== '') {
                        enabled = JSON.parse(data);
                    } else {
                        enabled = [];
                    }

                    calcEnabled(enabled);
                });

                userContainer.toggle(200);
            });
        })
        .on("click", ".toggleUserOp", function() {
            var opID = $(this).data('opid');
            var inArray = $.inArray(opID, enabled);

            if($(this).attr('data-enabled') === 'true') {
                $(this).attr('data-enabled', 'false').css("background-color", "transparent");
                $(this).find("input[type='text']").val('').attr('tabindex', '-1').attr("disabled", "disabled");

                enabled.splice($.inArray(opID, enabled), 1);
            } else {
                $(this).attr('data-enabled', 'true').css("background-color", "rgba(0,255,0,.8);");
                $(this).find("input[type='text']").attr('tabindex', '0').removeAttr("disabled");

                if(inArray === -1) {
                    enabled.push(opID);
                } else {
                    enabled.splice($.inArray(opID, enabled), 1);
                }
            }

            calcEnabled(enabled);
        })
        .on("click", "input[type='text']", function() {
            e.preventDefault();
        })
        .on("blur", "input[type='text']", function() {
            if($(this).val() !== '') {
                var thisOp = $(this).data('opid');
                var thisPriority = $(this).val();

                var otherOp = enabled[thisPriority - 1];

                var enabledOtherPos = $.inArray(otherOp, enabled);
                var enabledThisPos = $.inArray(thisOp, enabled);

                // this is for sliding the rest of the priorities down
                enabled.splice(enabledThisPos, 1);
                enabled.splice(enabledOtherPos, 0, thisOp);

                /*
                // this is for swapping the priorities
                enabled[enabledOtherPos] = thisOp;
                enabled[enabledThisPos] = otherOp;
                */

                calcEnabled(enabled);
            }
        })
        .on("click", ".user_op_save", function() {
            var id = $(this).data('id');
            var op_string = JSON.stringify(enabled);

            $.post("/ondemand/account_actions.php?action=save_account_ops&id=" + id, {op_string: op_string}, function(data) {
                $('body').append(data);
            });

            socket.emit("updateQueue");
        })
        .on("focus", "input[type='text']", function() {
            $(this).select();
        })
    ;
</script>