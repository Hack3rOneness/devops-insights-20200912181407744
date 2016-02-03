
//
// the javascript for all the buildkit-related
//  functioning, including the page router
//
(function(_BUILDKIT, $, undefined){

    var $loadTarget,
        $body,
        FB_SECTION;


    /* --------------------------------------------
     * --PRIVATE
     * -------------------------------------------- */

    /**
     * set up and build the page, then run the init sequence
     */
    function _buildkitBuildPage(){
        var mainNavPath  = 'inc/components/main-nav.html';

        //
        // load some global components
        //
        if( FB_CTF !== undefined){
            FB_CTF.loadComponent('#fb-main-nav', mainNavPath, _BUILDKIT.enableNavActiveState);
        }


        //
        // ok, everything is ready. lets do this
        //
        _BUILDKIT.init();

    }


    /**
     * build the admin page
     */
    function _buildkitBuildAdmin(){

        var hash         = window.location.hash;

        // set up the default hash
        if( hash === '' ){
            window.location.hash = 'setup-instructions';
        }

        _BUILDKIT.init();

    }




    /* --------------------------------------------
     * --PUBLIC
     * -------------------------------------------- */

    /**
     * demo for the viewmode, since there's no interaction there.
     */
    _BUILDKIT.viewModeDemo = function(){

        $('body').on('gameboard-loaded', function(event) {
            setTimeout(function(){
                FB_CTF.gameboard.captureCountry("Algeria");

                setTimeout(function(){
                    FB_CTF.gameboard.captureCountry("Chile");
                }, 10000);
            }, 4000);

        });

    }



    /**
     * enable the active state on the main nav. This function gets
     *  called since the pages are loaded via ajax.
     */
    _BUILDKIT.enableNavActiveState = function(){

        var hash = window.location.hash.replace('#', '');

        $('.fb-main-nav a').removeClass('active').filter(function(){
            var href = $(this).data('active');

            if(href === undefined || !href.indexOf || hash === ''){
                return false;
            }
            return href.indexOf( hash ) > -1;
        }).addClass('active');
    }


    /**
     * enable the active state on the admin nav. This function gets
     *  called since the pages are loaded via ajax.
     */
    _BUILDKIT.enableAdminActiveState = function(){

        var hash = window.location.hash.replace('#', '');

        $('#fb-admin-nav li').removeClass('active').filter(function(){
            var href = $('a', this).attr('href').replace('#', '');

            if(href === undefined || !href.indexOf || hash === ''){
                return false;
            }
            return href.indexOf( hash ) > -1;
        }).addClass('active');
    }


    /**
     * loads the content in the buildkit
     */
    _BUILDKIT.load = function(file){

        var loadDir     = 'inc',
            loadSection = FB_SECTION,
            fileExt     = '.html',
            loadPath    = loadDir + '/' + loadSection + '/' + file + fileExt;

        //
        // if we're on mobile, show the mobile screen
        //
        if( window.innerWidth < 960 ){
            loadPath = loadDir + '/components/mobile' + fileExt;
            $('body').addClass('mobile-device')
        }

        $loadTarget.load(loadPath, function( response, status, jqxhr ){
            if( status === "error" ){
                console.error("There was a problem loading the content");
                console.log("loadPath: " + loadPath);
                console.log(response);
                console.error("/end error");
            } else {

                if( FB_CTF !== undefined ){
                    //
                    // the following components get placed into the loaded file, so
                    //  we need to load them **after** the requested page fragment
                    //  gets loaded
                    //
                    FB_CTF.loadComponent('.emblem-carousel', 'inc/components/emblem-carousel.html', function(){
                        FB_CTF.slider.init();
                    });
                    FB_CTF.loadComponent('#fb-page-footer', 'inc/components/footer.html', function(){
                        $body.trigger('footer-loaded');
                    });

                    FB_CTF.init();

                    if( FB_SECTION === 'gameboard' || FB_SECTION === 'viewer-mode'){
                        FB_CTF.gameboard.init();

                        if( FB_CTF.gameboard.isViewMode() ){
                            _BUILDKIT.viewModeDemo();
                        }
                    } else if( FB_SECTION === 'admin' ){
                        FB_CTF.admin.init();
                    }

                    $('body').trigger('content-loaded', {page: file});
                }
            }
        });
    };

    /**
     * router for the different buildkit pages
     */
    _BUILDKIT.router = function(hash){

        var fallback    = 'main',
            loadFile    = ! hash || hash == '' ? fallback : hash;

        _BUILDKIT.enableNavActiveState();
        _BUILDKIT.enableAdminActiveState();

        _BUILDKIT.load( loadFile );
    };


    _BUILDKIT.init = function(){
        $(window).on('hashchange', function(event) {
            event.preventDefault();
            var hash = window.location.hash.replace('#', '');
            _BUILDKIT.router(hash);

        }).trigger('hashchange');
    }



    _BUILDKIT.addAlert = function(){
        var alertMarkup = '<li class="loadin"><div class="alert-main">' +
            '       <div class="alert"><span class="opponent-name">DEATH HAXX</span> has offered to help</div>' +
            '       <div class="alert--actionable radio-list">' +
            '           <input type="radio" name="fb--alert--help-2" id="fb--alert--help-2--accept"><label for="fb--alert--help-2--accept" class="click-effect"><span>Accept</span></label>' +
            '           <input type="radio" name="fb--alert--help-2" id="fb--alert--help-2--ignore"><label for="fb--alert--help-2--ignore" class="click-effect"><span>Ignore</span></label>' +
            '       </div>' +
            '   </div>' +
            '   <div class="fb-row-container individual-chat">' +
            '       <div class="chat-box row-fluid">' +
            '           <ul class="chat-list"></ul>' +
            '       </div>' +
            '       <div class="row-fixed">' +
            '           <form class="chat-input">' +
            '               <div class="input-container"><input type="text" placeholder="Type to chat"></div>' +
            '               <button class="fb-cta" type="submit">Submit</button>' +
            '           </form>' +
            '       </div>' +
            '   </div>' +
            '</li>',
            $newAlert = $(alertMarkup);

        $newAlert.appendTo('.alerts');

        setTimeout(function(){
            $newAlert.removeClass('loadin');
        }, 10);

    }



    /**
     * set up stuff on document ready:
     *   - set global variables
     */
    $(document).ready(function() {

        $body = $('body');
        $loadTarget = $('#fb-buildkit');
        FB_SECTION = $body.data('section');

        //
        // route the sequence - if we're on the pages side of things,
        //  load the router and loader so we can navigate the
        //  different pages. Otherwise, if we're on the gameboard,
        //  init all the FB_CTF things and load the gameboard
        //
        if( FB_SECTION === 'pages' ){
            _buildkitBuildPage();
        } else if( FB_SECTION === 'gameboard' || FB_SECTION === 'viewer-mode' ){
            _BUILDKIT.init();
        } else if( FB_SECTION === 'admin'){
            _buildkitBuildAdmin();
        }

    });


})(window._BUILDKIT = window._BUILDKIT || {}, jQuery);
