var unsaved = false;

$(":input").change(function(){ //triggers change in all input fields including text type
    if(!$(this).hasClass("ignoreSaveAlert")) {
        unsaved = true;
    }
});

$(document).on('change', ':input', function(){ //triggers change in all input fields including text type
    if(!$(this).hasClass("ignoreSaveAlert")) {
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