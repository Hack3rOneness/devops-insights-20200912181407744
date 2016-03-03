<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facebook CTF</title>
    <link rel="icon" type="image/png" href="static/img/favicon.png">
    <link rel="stylesheet" href="static/css/fb-ctf.css">

</head>
<body data-section="pages">

    <!--
     *
     * SVG icon sprite object
     *
     * gets loaded in via javascript
     *
    -->
    <div style="height: 0; width: 0; position: absolute; visibility: hidden" id="fb-svg-sprite"></div>


    <div class="fb-viewport">
        <!--
         * Main navigation
        -->
        <div id="fb-main-nav"></div><!-- /end main navigation -->

        <!--
         * The main stuff
        -->
        <div id="fb-buildkit" class="fb-page"></div><!-- #fb-buildkit -->

    </div><!-- .fb-viewport -->


    <script type="text/javascript" src="static/js/vendor/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="static/js/plugins.js"></script>
    <script type="text/javascript" src="static/js/fb-ctf.js"></script>
    <script type="text/javascript" src="static/js/actions.js"></script>
    <script type="text/javascript" src="static/js/_buildkit.js"></script>

</body>
</html>
