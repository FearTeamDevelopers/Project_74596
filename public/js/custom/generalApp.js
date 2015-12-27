jQuery.noConflict();

jQuery(document).ready(function () {

    jQuery(window).load(function () {
        jQuery("#loader, .loader").hide();

//        jQuery.post('/app/system/showprofiler/', function (msg) {
//            jQuery('body').append(msg);
//        });
    });

    /* GLOBAL SCRIPTS */

    jQuery(".datepicker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        firstDay: 1
    });

    jQuery(".datepicker-registration").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "1960:2000",
        dateFormat: "yy-mm-dd",
        firstDay: 1
    });

});