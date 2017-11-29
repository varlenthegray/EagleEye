<?php
require 'includes/header_start.php';
require 'includes/header_end.php';
?>

<div class="col-md-12" id="main_display">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-box">
                        <div class="col-md-12">
                            <table class="table table-bordered tablesorter" id="tasks_global_table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ASSIGNED TO</th>
                                    <th>CREATED</th>
                                    <th>SHORT DESC</th>
                                    <th>PRIORITY</th>
                                    <th>ETA</th>
                                    <th>% COMPLETED</th>
                                    <th>LAST UPDATED</th>
                                </tr>
                                </thead>
                                <tbody id="tasks_information_table">
                                <tr>
                                    <td colspan="9">No tasks to display.</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task modal -->
            <div id="modalTaskInfo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalTaskInfoLabel" aria-hidden="true">
                <!-- Inserted via AJAX -->
            </div>
            <!-- /.modal -->
        </div>
    </div>
</div>

<!-- Add Customer modal -->
<div id="modalAddCustomer" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAddCustomerLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<!-- Add Customer modal -->
<div id="modalViewNotes" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalViewNotesLabel" aria-hidden="true">
    <!-- Inserted via AJAX -->
</div>
<!-- /.modal -->

<!-- Global Search loading, required for global search to work -->
<script src="/ondemand/js/global_search.js?random=<?php echo rand(0,1500); ?>"></script>

<!-- Adding SO to the system -->
<script src="/ondemand/js/add_so.js"></script>

<script>
    $("#tasks_global_table").DataTable({
        "ajax": "/ondemand/admin/tasks.php?action=get_task_list",
        "createdRow": function(row,data,dataIndex) {
            $(row).addClass("cursor-hand display-task-info");
        },
        "order": [1,'asc'],
        "dom": 'rti',
        "pageLength": 25
    });

    $(".js_loading").show();

    $(function() {
        $(".js_loading").hide();
    });

    $("body")
    // -- Navigation --
        .on("click", ".display-task-info", function() {
            $.post("/ondemand/admin/tasks.php?action=get_task_info", {task_id: $(this).attr("id")}, function(data) {
                $("#modalTaskInfo").html(data);
            }).done(function() {
                $("#modalTaskInfo").modal();
            }).fail(function(data) { // if we're receiving a header error
                $("body").append(data); // echo an error and log it
            });
        })
        .on("click", "#update_task_btn", function() {
            var form_info = $("#task_details").serialize();
            var task_id = $(this).data("taskid");
            var s_text_1 = $("#split-text-1").val();
            var s_text_2 = $("#split-text-2").val();

            $.post("/ondemand/admin/tasks.php?action=update_task&" + form_info, {task_id: task_id, s_text_1: s_text_1, s_text_2: s_text_2}, function(data) {
                $("body").append(data);
                $("#modalTaskInfo").modal('hide');

                unsaved = false;
            });
        })
        .on("click", "#split_task_btn", function() {
            $(".task_hide").toggle(100);
            $("#split_body").toggle(250);

            setTimeout(function() {
                if($("#split_body").is(":visible")) {
                    $("#split_task_enabled").val("1");
                } else {
                    $("#split_task_enabled").val("0");
                }
            }, 250);
        })
        .on("click", "#create_op_btn", function() {
            var form_info = $("#task_details").serialize();
            var task_id = $(this).data("taskid");

            $.post("/ondemand/admin/tasks.php?action=create_operation&" + form_info, {task_id: task_id}, function(data) {
                $("body").append(data);
                $("#modalTaskInfo").modal('hide');

                unsaved = false;
            });
        })
    ;

    setInterval(function() {
        var time = new Date();
        time = time.toLocaleTimeString();

        $("#clock").html(time);
    }, 1000);
</script>

<?php
require 'includes/footer_start.php';
require 'includes/footer_end.php';
?>