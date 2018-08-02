/*jshint strict: false*/

$("body")
  .on("click", ".add_room_trigger", function(e) {
    e.stopPropagation();

    active_so_num = $(this).attr('data-sonum');

    $.post("/html/search/modal/add_room.php?so_num=" + active_so_num, function(data) {
      $("#modalAddRoom").html(data).modal("show");
    });
  })
  .on("click", "#modalAddRoomCreate", function() {
    let room_data = $("#modalAddRoomData").serialize();

    $.post("/html/search/ajax/room_actions.php?action=add_new_room", {data: room_data}, function(data) {
      $("body").append(data);
    });
  })
;