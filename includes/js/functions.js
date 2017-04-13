/**
 * Created by Ben on 3/18/2017.
 */
// This displays a general toast, if no progress bar then set it to false by default
function displayToast(type, message, subject, pb) {
    if(pb !== true) {
        pb = false;
    }

    toastr[type](message, subject);

    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": pb,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
}