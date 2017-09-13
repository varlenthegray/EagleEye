var unsaved = false;

$(":input").change(function(){ //triggers change in all input fields including text type
    if($(this).attr("id") !== "global_search" && $(this).attr("id") !== "viewing_queue" && $(this).attr("name") !== "ausernameidontcareabout" && $(this).attr("name") !== "apasswordidontcareabout" && $(this).attr('id') !== 'date_range') {
        unsaved = true;
    }
});

$(document).on('change', ':input', function(){ //triggers change in all input fields including text type
    if($(this).attr("id") !== "global_search" && $(this).attr("id") !== "viewing_queue" && $(this).attr("name") !== "ausernameidontcareabout" && $(this).attr("name") !== "apasswordidontcareabout" && $(this).attr('id') !== 'date_range') {
        unsaved = true;
    }
});

function unloadPage(new_location){
    if(unsaved){
        $.confirm({
           buttons: {
                yes: function() {
                    loadPage(new_location);
                    unsaved = false;
                },
                no: function() {}
            }
        });
    } else {
        loadPage(new_location);
    }
}

function checkTransition(funct) {
    if(unsaved){
        $.confirm({
            buttons: {
                yes: function() {
                    funct();
                    unsaved = false;
                },
                no: function() {}
            }
        });
    } else {
        funct();
    }
}