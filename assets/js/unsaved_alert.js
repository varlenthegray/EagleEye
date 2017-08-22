var unsaved = false;

$(":input").change(function(){ //triggers change in all input fields including text type
    if($(this).attr("id") !== "global_search") {
        unsaved = true;
    }
});

$(document).on('change', ':input', function(){ //triggers change in all input fields including text type
    if($(this).attr("id") !== "global_search") {
        unsaved = true;
    }
});

function unloadPage(new_location){
    if(unsaved){
        $.confirm({
           buttons: {
                confirm: function() {
                    loadPage(new_location);
                    unsaved = false;
                },
                cancel: function() {}
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
                confirm: function() {
                    funct();
                    unsaved = false;
                },
                cancel: function() {}
            }
        });
    } else {
        funct();
    }
}