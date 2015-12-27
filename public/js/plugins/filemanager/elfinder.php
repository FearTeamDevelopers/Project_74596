<?php 
session_start();

if(!isset($_SESSION['thc_authUser'])){
    exit;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>elFinder 2.0</title>

        <!-- jQuery and jQuery UI (REQUIRED) -->
        <link rel="stylesheet" href="jquery/ui-themes/smoothness/jquery-ui-1.10.1.custom.css" type="text/css" media="screen" title="no title" charset="utf-8">
        <script src="jquery/jquery-1.9.1.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="jquery/jquery-ui-1.10.1.custom.min.js" type="text/javascript" charset="utf-8"></script>

        <!-- elFinder CSS (REQUIRED) -->
        <link rel="stylesheet" type="text/css" href="css/elfinder.min.css">
        <link rel="stylesheet" type="text/css" href="css/theme.css">

        <!-- elFinder JS (REQUIRED) -->
        <script src="js/elfinder.min.js"></script>

        <!-- elFinder translation (OPTIONAL) -->
        <script src="js/i18n/elfinder.cs.js"></script>

        <!-- elFinder initialization (REQUIRED) -->
        <script type="text/javascript" charset="utf-8">
// Helper function to get parameters from the query string.
            function getUrlParam(paramName) {
                var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i');
                var match = window.location.search.match(reParam);

                return (match && match.length > 1) ? match[1] : '';
            }

            $().ready(function () {
                var funcNum = getUrlParam('CKEditorFuncNum');

                var elf = $('#elfinder').elfinder({
                    url: 'php/connector.minimal.php',
                    getFileCallback: function (file) {
                        window.opener.CKEDITOR.tools.callFunction(funcNum, file.url);
                        window.close();
                    },
                    lang: 'cs',
                    height: 600
                }).elfinder('instance');
            });
        </script>
        
    </head>
    <body>

        <!-- Element where elFinder will be created (REQUIRED) -->
        <div id="elfinder"></div>

    </body>
</html>
