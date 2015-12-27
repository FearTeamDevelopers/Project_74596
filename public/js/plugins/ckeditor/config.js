/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    config.entities_latin = false;
    config.font_defaultLabel = 'Arial';
    config.fontSize_defaultLabel = '12px';
    config.allowedContent = true;
    config.enterMode = CKEDITOR.ENTER_BR;
    config.format_tags = 'p;h2;h3;h4;h5;h6;pre;address;div';
    config.entities = false;

    config.extraPlugins = 'wordcount,lightbox,youtube';
    
    config.wordcount = {
        showWordCount: false,
        showCharCount: true,
        countHTML: false
    };

    config.youtube_width = '640';
    config.youtube_height = '480';
    config.youtube_related = false;
    config.youtube_older = false;
    config.youtube_privacy = true;

    config.extraAllowedContent = 'a[data-lightbox,data-title,data-lightbox-saved]';

    config.toolbar = [
        {name: 'document', groups: ['mode', 'document', 'doctools'], items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates']},
        {name: 'clipboard', groups: ['clipboard', 'undo'], items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']},
        {name: 'editing', groups: ['find', 'selection', 'spellchecker'], items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']},
        '/',
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup'], items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']},
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'], items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
        '/',
        {name: 'links', items: ['Link', 'Unlink', 'Anchor', 'lightbox']},
        {name: 'insert', items: ['Image',  'Youtube', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak']},
        '/',
        {name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
        {name: 'colors', items: ['TextColor', 'BGColor']},
        {name: 'tools', items: ['Maximize', 'ShowBlocks']}
    ];

// Toolbar groups configuration.
    config.toolbarGroups = [
        {name: 'document', groups: ['mode', 'document', 'doctools']},
        {name: 'clipboard', groups: ['clipboard', 'undo']},
        {name: 'editing', groups: ['find', 'selection', 'spellchecker']},
        '/',
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi']},
        '/',
        {name: 'links'},
        {name: 'insert'},
        '/',
        {name: 'styles'},
        {name: 'colors'},
        {name: 'tools'}
    ];
};
