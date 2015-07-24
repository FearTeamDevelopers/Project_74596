jQuery(document).ready(function(){
    /***** WIDGET LIST HOVER *****/
    jQuery('.widgetlist a').hover(function(){
        jQuery(this).switchClass('default', 'hover');
    },function(){
        jQuery(this).switchClass('hover', 'default');
    });

});