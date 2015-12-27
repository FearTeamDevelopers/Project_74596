jQuery.noConflict();

jQuery(document).ready(function () {
    jQuery('a.ajaxLoadTemplate').click(function (event) {
        event.preventDefault();
        
        var template = jQuery('select[name=template] option:selected').val();
        var lang = jQuery('select[name=lang] option:selected').val();
        var url = jQuery(this).attr('href')+template+'/'+lang;

        jQuery.post(url, function (message) {
            if (message == 'notfound') {
                jQuery('#dialog p').text('Error while loading template');
                jQuery('#dialog').dialog();
            } else {
                var template = jQuery.parseJSON(message);

                CKEDITOR.instances['ckeditor'].setData(template.text);
                jQuery('input[name=subject]').val(template.subject);
            }
        });
    });

    jQuery('.nosubmit').submit(function (event) {
        event.preventDefault();
        return false;
    });

    jQuery('#clear-text').click(function (event) {
        event.preventDefault();
        CKEDITOR.instances['ckeditor'].setData('');
    });
    jQuery('#clear-texten').click(function (event) {
        event.preventDefault();
        CKEDITOR.instances['ckeditor2'].setData('');
    });

    jQuery('#text-new-paragraph').click(function (event) {
        event.preventDefault();
        CKEDITOR.instances['ckeditor'].insertText('<br class="clear-all" />');
    });
    jQuery('#texten-new-paragraph').click(function (event) {
        event.preventDefault();
        CKEDITOR.instances['ckeditor2'].insertText('<br class="clear-all" />');
    });

    jQuery('#text-link-to-gallery, #texten-link-to-gallery').click(function (event) {
        event.preventDefault();
        var type = jQuery(this).attr('id');

        jQuery('#insert-dialog p').html('Nahrávám ...');
        jQuery('#insert-dialog p').load('/admin/gallery/inserttocontent/');

        jQuery('#insert-dialog').dialog({
            title: 'Vložit odkaz',
            width: 600,
            modal: true,
            buttons: {
                'Insert': function () {
                    var src = jQuery('#content').val();
                    var target = jQuery('#link-target').val();
                    var name = jQuery('#link-name').val();
                    var tag = "<a href=\"http://www.hastrman.cz/galerie/r/" + src + "\" target=" + target + ">" + name + "</a>";

                    if (type.substr(0, 6) == 'texten') {
                        CKEDITOR.instances['ckeditor2'].insertText(tag);
                    } else {
                        CKEDITOR.instances['ckeditor'].insertText(tag);
                    }

                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                },
                Close: function () {
                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                }
            }
        });
        return false;
    });

    jQuery('#text-link-to-action, #texten-link-to-action').click(function (event) {
        event.preventDefault();
        var type = jQuery(this).attr('id');

        jQuery('#insert-dialog p').html('Nahrávám ...');
        jQuery('#insert-dialog p').load('/admin/action/inserttocontent/');

        jQuery('#insert-dialog').dialog({
            title: 'Vložit odkaz',
            width: 600,
            modal: true,
            buttons: {
                'Insert': function () {
                    var src = jQuery('#content').val();
                    var target = jQuery('#link-target').val();
                    var name = jQuery('#link-name').val();
                    var tag = "<a href=\"http://www.hastrman.cz/akce/r/" + src + "\" target=" + target + ">" + name + "</a>";

                    if (type.substr(0, 6) == 'texten') {
                        CKEDITOR.instances['ckeditor2'].insertText(tag);
                    } else {
                        CKEDITOR.instances['ckeditor'].insertText(tag);
                    }

                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                },
                Close: function () {
                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                }
            }
        });
        return false;
    });

    jQuery('#text-link-to-report, #texten-link-to-report').click(function (event) {
        event.preventDefault();
        var type = jQuery(this).attr('id');

        jQuery('#insert-dialog p').html('Nahrávám ...');
        jQuery('#insert-dialog p').load('/admin/report/inserttocontent/');

        jQuery('#insert-dialog').dialog({
            title: 'Vložit odkaz',
            width: 600,
            modal: true,
            buttons: {
                'Insert': function () {
                    var src = jQuery('#content').val();
                    var target = jQuery('#link-target').val();
                    var name = jQuery('#link-name').val();
                    var tag = "<a href=\"http://www.hastrman.cz/reportaze/r/" + src + "\" target=" + target + ">" + name + "</a>";

                    if (type.substr(0, 6) == 'texten') {
                        CKEDITOR.instances['ckeditor2'].insertText(tag);
                    } else {
                        CKEDITOR.instances['ckeditor'].insertText(tag);
                    }

                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                },
                Close: function () {
                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                }
            }
        });
        return false;
    });

    jQuery('#text-link-to-news, #texten-link-to-news').click(function (event) {
        event.preventDefault();
        var type = jQuery(this).attr('id');

        jQuery('#insert-dialog p').html('Nahrávám ...');
        jQuery('#insert-dialog p').load('/admin/news/inserttocontent/');

        jQuery('#insert-dialog').dialog({
            title: 'Vložit odkaz',
            width: 600,
            modal: true,
            buttons: {
                'Insert': function () {
                    var src = jQuery('#content').val();
                    var target = jQuery('#link-target').val();
                    var name = jQuery('#link-name').val();
                    var tag = "<a href=\"http://www.hastrman.cz/novinky/r/" + src + "\" target=" + target + ">" + name + "</a>";

                    if (type.substr(0, 6) == 'texten') {
                        CKEDITOR.instances['ckeditor2'].insertText(tag);
                    } else {
                        CKEDITOR.instances['ckeditor'].insertText(tag);
                    }

                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                },
                Close: function () {
                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                }
            }
        });
        return false;
    });

    jQuery('#text-link-to-content, #texten-link-to-content').click(function (event) {
        event.preventDefault();
        var type = jQuery(this).attr('id');

        jQuery('#insert-dialog p').html('Nahrávám ...');
        jQuery('#insert-dialog p').load('/admin/content/inserttocontent/');

        jQuery('#insert-dialog').dialog({
            title: 'Vložit odkaz',
            width: 600,
            modal: true,
            buttons: {
                'Insert': function () {
                    var src = jQuery('#content').val();
                    var target = jQuery('#link-target').val();
                    var name = jQuery('#link-name').val();
                    var tag = "<a href=\"http://www.hastrman.cz/page/" + src + "\" target=" + target + ">" + name + "</a>";

                    if (type.substr(0, 6) == 'texten') {
                        CKEDITOR.instances['ckeditor2'].insertText(tag);
                    } else {
                        CKEDITOR.instances['ckeditor'].insertText(tag);
                    }

                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                },
                Close: function () {
                    jQuery('#insert-dialog p').html('');
                    jQuery(this).dialog('close');
                }
            }
        });
        return false;
    });
    
    jQuery('select[name=type]').change(function () {
        var type = jQuery(this).children('option:selected').val();

        if (type == 1) {
            if (jQuery('#singleRecipient').hasClass('nodisplay')) {
                jQuery('#singleRecipient').removeClass('nodisplay');
                jQuery('#groupRecipient').addClass('nodisplay');
                jQuery('#actionRecipient').addClass('nodisplay');
            }
        } else if (type == 2) {
            if (jQuery('#groupRecipient').hasClass('nodisplay')) {
                jQuery('#groupRecipient').removeClass('nodisplay');
                jQuery('#singleRecipient').addClass('nodisplay');
                jQuery('#actionRecipient').addClass('nodisplay');
            }
        } else if (type == 3) {
            if (jQuery('#actionRecipient').hasClass('nodisplay')) {
                jQuery('#actionRecipient').removeClass('nodisplay');
                jQuery('#singleRecipient').addClass('nodisplay');
                jQuery('#groupRecipient').addClass('nodisplay');
            }
        } else {
            jQuery('#singleRecipient').addClass('nodisplay');
            jQuery('#groupRecipient').addClass('nodisplay');
            jQuery('#actionRecipient').addClass('nodisplay');
        }
    });
});