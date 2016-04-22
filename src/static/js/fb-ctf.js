//
// FB CTF javascript
//
(function(FB_CTF, $, undefined){
  var $body;

  // colors
  var COLOR_LIGHT_BLUE = "#cff8fa",
  COLOR_TEAL_BLUE  = "#5cf0f6",
  COLOR_MAIN_BLUE  = "#13242b";

  // checks
  var ua     = navigator.userAgent.toLowerCase(),
  is_firefox = ua.indexOf('firefox') > -1,
  is_ie      = ua.indexOf('msie') > -1;

  FB_CTF.debug = true;
  FB_CTF.data = Object();

  /* --------------------------------------------
   * --util
   * -------------------------------------------- */

  /**
   * get the given parameter value
   */
  function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');

      if (sParameterName[0] === sParam) {
        return sParameterName[1] === undefined ? true : sParameterName[1];
      }
    }
  };

  /* --------------------------------------------
   * --modules
   * -------------------------------------------- */

  /**
   * --gameboard
   *
   * handles all the loading of the modules and the map, as well as
   *  all map-related event listeners and animations
   */
  FB_CTF.gameboard = (function(){

    var GAMEBOARD_LOADED = false,
        LOADING_CLASS    = 'loading',
        LIST_VIEW        = false,
        VIEW_ONLY        = false,
        CURRENT_ZOOM     = 1,
        $gameboard,
        $listview,
        $mapSvg,
        $map,
        $countryHover;


    /**
     * enable click and drag capabilities on the map
     */
    var enableClickAndDrag = (function(){
      if( typeof d3 === 'undefined'){
        return;
      }

      var $window = $(window);

      var zoom = d3.behavior.zoom().scaleExtent([1, 10]).on("zoom", zoomed);

      var svgMap,
          container;

      function init(){
        svgMap = d3.select('#fb-gameboard-map').call(zoom);
        container = svgMap.select(".view-controller").call(zoom);

        $window.on('keyup', function(event){
          var key     = event.which,
              ww      = $window.width(),
              wh      = $window.height(),
              zoomin  = is_firefox ? 61 : 187,
              zoomout = is_firefox ? 173 : 189;

          // the plus (zoom in) or the minus
          if( key === zoomin || key === zoomout){
            var multiplier = key === zoomin ? 1 : -1;

            svgMap.call(zoom.event); // https://github.com/mbostock/d3/issues/2387

            // Record the coordinates (in data space) of the center (in screen space).
            var center0      = [ 504 , 325],
                translate0   = zoom.translate(),
                coordinates0 = coordinates(center0),
                newScale     = zoom.scale() * Math.pow(2, multiplier);

            if(newScale > 10){
              zoom.scale( 10 );
            } else if(newScale < 1){
              zoom.scale( 1 );
            } else {
              zoom.scale( newScale );
            }

            // Translate back to the center.
            var center1 = point(coordinates0);
            zoom.translate([translate0[0] + center0[0] - center1[0], translate0[1] + center0[1] - center1[1]]);

            svgMap.transition().duration(400).call(zoom.event);
          }
        });
      }


      /**
       * zoom and pan to the given coordinates
       *
       * @param latFocus (number) // default = 0
       *   - the x coordinate of the indicator
       *
       * @param lngFocus (number) // default = 0
       *   - the y coordinate of the indicator
       *
       * @param newScale (number) // default = 1
       *   - the zoom level
       */
      function zoomToPoint(latFocus, lngFocus, newScale){
        svgMap.call(zoom.event); // https://github.com/mbostock/d3/issues/2387

        // default parameters
        if( latFocus === undefined )
          latFocus = 0;

        if( lngFocus === undefined )
          lngFocus = 0;

        if( newScale === undefined )
          newScale = 1;

        var latModifier = -1 * (1 + (latFocus - 620) / 504);
        var lngModifier = VIEW_ONLY ? -1 * (1 + (lngFocus - 400) / 400) : -1 * (1 + (lngFocus - 325) / 325);

        zoom.scale( newScale );
        zoom.translate([latFocus * latModifier, lngFocus * lngModifier]);

        svgMap.transition().duration(400).call(zoom.event);
      }


      /**
       * function to call when the svg is zoomed
       */
      function zoomed( scale ){
        var zoomScale      = scale ? scale : d3.event.scale,
            xyOffset       = zoomScale,
            translateVal   = scale ? "0,0" : d3.event.translate,
            indicatorRatio = 1 / zoomScale,
            transformRatio = 5.6 - (5.6 * indicatorRatio),
            panX           = translateVal[0],
            panY           = translateVal[1];

        FB_CTF.modal.closeHoverPopup();

        container.attr("style", "transform: translate(" + panX + "px," + panY + "px) scale(" + zoomScale + ")");

        $('.countries .map-indicator path', $mapSvg).css({
          'transform': 'translate(' + transformRatio + 'px,' + transformRatio + 'px) scale(' + indicatorRatio + ')'
        });

        $('.countries .land', $mapSvg).each(function(){
          var $self    = $(this),
              modifier = $self.attr('class').indexOf('active') > -1 ? 1 : 1.5;

          $(this).css({
            'stroke-width': (indicatorRatio * modifier)
          });
        });
      };

      /**
       * get svg coordinates
       */
      function coordinates(point) {
        var scale = zoom.scale(), translate = zoom.translate();
        return [(point[0] - translate[0]) / scale, (point[1] - translate[1]) / scale];
      }

      /**
       * get a point
       */
      function point(coordinates) {
        var scale = zoom.scale(), translate = zoom.translate();
        return [coordinates[0] * scale + translate[0], coordinates[1] * scale + translate[1]];
      }

      /**
       * get the zoom
       */
      function getZoom(){
        return zoom.scale();
      }

      return {
        init        : init,
        zoom        : zoom,
        getZoom     : getZoom,
        zoomToPoint : zoomToPoint,
        zoomed      : zoomed
      };
    })(); // enableClickAndDrag


    //
    // PRIVATE
    //

    /**
     * build the gameboard, and display the loading screen while
     *  it's being built
     */
    function build(){
      var countryDataLoaded = getCountryData();

      //
      // add the modules and the map
      //
      var modulesLoaded  = loadModules(),
          mapLoaded,
          confDataLoaded = loadConfData(),
          listViewLoaded = loadListView(),
          teamDataLoaded = loadTeamData(),
          loadingLoaded  = loadIn();

      if (VIEW_ONLY) {
        mapLoaded = loadMapView();
      } else {
        mapLoaded = loadMap();
      }

      $.when( mapLoaded, countryDataLoaded).done(function(){
        renderCountryData();
      });

      // do stuff when the map and modules are loaded
      $.when(modulesLoaded, mapLoaded, confDataLoaded, listViewLoaded, teamDataLoaded, loadingLoaded).done(function(){
        console.log("modules, map, conf data, list view, team data, and loading screen are loaded");

        // trigger an event for the gameboard loaded, so
        //  external things know that everything has been
        //  loaded (like the buildkit js).
        $('body').trigger('gameboard-loaded');

        // check off that the gameboard is loaded (or in the
        //  process of being loaded)
        GAMEBOARD_LOADED = true;

        //
        // set up the listeners
        //
        if (!VIEW_ONLY) {
          gameEventListeners();
        }

        //
        // initialize the tutorial, if the query string is present
        //
        if( getUrlParameter('tutorial') === 'false' || VIEW_ONLY || FB_CTF.debug ){
          loadOut();
        } else {
          initTutorial();
        }

        // Kick off all the timers to keep data refreshed, not on view mode
        if (!VIEW_ONLY) {

          // Load initial configuration
          loadConfData();

          // Load initial command line
          FB_CTF.command_line.loadCommandsData();
          FB_CTF.command_line.init();

          // Load initial filters
          loadFilterModule();

          // Load initial teams related modules and data
          loadTeamData();
          var loaded = loadTeamsModule();
          $.when(loaded).done(function(){
            activateTeams();
          });
          loadLeaderboardModule();

          // Load initial activity
          loadActivityModule();

          // Configuration reloader
          setInterval( function() {
            loadConfData();
          }, FB_CTF.data.CONF.refreshConf);

          // Countries
          setInterval( function() {
            if (FB_CTF.data.CONF.gameboard === '1') {
              // Map
              getCountryData();
              refreshMapData();
              // Announcements
              loadAnnouncementsModule();
              // Filter
              //loadFilterModule();
              // Activity
              loadActivityModule();
            } else {
              clearMapData();
              clearAnnouncements();
              clearActivity();
            }
          }, FB_CTF.data.CONF.refreshMap);

          // Teams
          setInterval( function() {
            if (FB_CTF.data.CONF.gameboard === '1') {
              // Teams
              loadTeamData();
              loadTeamsModule();
              loadLeaderboardModule();
            } else {
              clearTeams();
              clearLeaderboard();
            }
          }, FB_CTF.data.CONF.refreshMap);

          // Commands
          setInterval( function() {
            FB_CTF.command_line.loadCommandsData();
          }, FB_CTF.data.CONF.refreshCmd);
        }
      });
    }

    /* --------------------------------------------
     * --setup
     * -------------------------------------------- */


    function clearTeams(){
      var $teamgrid = $('aside[data-module="teams"] .grid-list');
      $('li', $teamgrid).remove();
    }

    function clearLeaderboard(){
      var $leaderboard = $('aside[data-module="leaderboard"] .leaderboard-info');
      $('li', $leaderboard).remove();
    }

    function clearAnnouncements(){
      var $announcements = $('aside[data-module="announcements"] .announcements-list');
      $('li', $announcements).remove();
    }

    function clearActivity(){
      var $announcements = $('aside[data-module="activity"] .activity-stream');
      $('li', $announcements).remove();
    }

    /**
     * get the owner of the given country, and return the markup
     *  for rendering somewhere
     *
     * @param capturedBy (string)
     *   - the capturing team
     */
    function getCapturedByMarkup( capturedBy ){
      if( capturedBy === undefined ){
        return "Uncaptured";
      }

      var capturedClass = (capturedBy === FB_CTF.data.CONF.currentTeam) ? 'your-name' : 'opponent-name';
        var span = $('<span/>').attr('class', capturedClass).text(capturedBy);
        return span;
      }

      /**
       * automatically scroll through the content on the sidebar
       *  modules. This happens in the "view only" mode
       */
      function autoScrollModules(){
        var $modules = $('aside[data-module="under-attack"], aside[data-module="leaderboard-viewmode"]');

        $modules.each(function(){
          var $scrollable    = $('.module-scrollable', this),
              scrollHeight   = $scrollable.children('ul').height() - $scrollable.height(),
              scrollTime     = scrollHeight / .05,
              scrollInterval;

          $scrollable.on('mouseover', function(event) {
            event.preventDefault();
            clearInterval( scrollInterval );
          });
          $scrollable.on('mouseout', startScroll);

          /**
           * start the scrolling interval
           */
          function startScroll(){
            scrollInterval = setInterval( function(){
              var st = $scrollable.scrollTop(),
              scrollLeft = $('ul', $scrollable).height() - $scrollable.height();

              if( st >= scrollLeft ){
                $scrollable.scrollTop(0);
              } else {
                $scrollable.scrollTop( st + 1 );
              }
            }, 20);
          }

          startScroll();
        });
      }

      /**
       * the event listeners for the game
       */
      function gameEventListeners(){
        var $svgCountries = $('.countries > g', $mapSvg);

        /* --------------------------------------------
         * --modules
         * -------------------------------------------- */

        //
        // open the module
        //
        $( 'aside[data-module]' ).on('click', '.module-header', function(event) {
          event.preventDefault();
          $(this).closest('aside').toggleClass('active');

          // Remember status of module
          if ($(this).closest('aside').hasClass('active')) {
            setWidgetStatus($(this).text(), 'open');
          } else {
            setWidgetStatus($(this).text(), 'close');
          }

          $body.trigger('module-changestate');
        })

        //
        // check to see if a module has changed its state - it it
        //  has, check the listview to rearrange it
        //
        $body.on('module-changestate', function(){
          var rightModules = false,
              bottomModules = false,
              margin = '25%',
              $listviewContainer = $('.listview-container', $listview);

          $( 'aside[data-module].active' ).each(function(){
            var $container = $(this).closest('.fb-module-container');

            if ($container.hasClass('column-right')){
              rightModules = true;

              if($container.width() > 350){
                margin = '390px';
              }
            } else if ($container.hasClass('container--row')){
              bottomModules = true;
            }
          });

          if(rightModules){
            $listviewContainer.css('right', margin);
          } else {
            $listviewContainer.css('right', '10px');
          }
          if(bottomModules){
            $listviewContainer.css('bottom', '34vh');
          } else {
            $listviewContainer.css('bottom', '100px');
          }
        });

        /* --------------------------------------------
         * --inputs
         * -------------------------------------------- */

        //
        // filter the map based on category
        //
        $('input[name="fb--module--filter--category"]').on('change', function(event) {
          event.preventDefault();
          var category = $(this).val();

          $svgCountries.each(function(){
            var countryGroup = d3.select(this);

            countryGroup.classed("inactive", false);
            countryGroup.classed("highlighted", false);

            if( category !== "All" ){
              if( countryGroup.attr('data-category') === category){
                countryGroup.classed('highlighted', true);
              } else {
                countryGroup.classed('inactive', true);
              }
            }
          });
        });

        //
        // filter the map based on status
        //
        $('input[name="fb--module--filter--status"]').on('change', function(event) {
          event.preventDefault();
          var status = $(this).val();

          $svgCountries.each(function(){
            var countryGroup = d3.select(this);

            countryGroup.classed("inactive", false);
            countryGroup.classed("highlighted", false);

            if( status !== "All" ){
              if( countryGroup.attr('data-status') === status){
                countryGroup.classed('highlighted', true);
              } else {
                countryGroup.classed('inactive', true);
              }
            }
          });
        });

        //
        // filter the filters
        //
        $('input[name="fb--module--filter"]').on('change', function(event) {
          event.preventDefault();
          var filter_type = $(this).val();
          if (filter_type === 'category') {
            $('.status-filter-content').removeClass('active');
            $('.category-filter-content').addClass('active');
          }
          if (filter_type === 'status') {
            $('.category-filter-content').removeClass('active');
            $('.status-filter-content').addClass('active');
          }
        });

        //
        // filter the map based on captured
        //
        $('input[name="fb--map-select"]').on('change', function(event) {
          event.preventDefault();
          var select = $(this).val();

          $svgCountries.each(function(){
            var countryGroup = d3.select(this),
            captureTeam  = countryGroup.attr('data-captured');

            countryGroup.classed("inactive", false);
            countryGroup.classed("highlighted", false);

            if( select !== "all" ){
              if( ( select === "your-team" && captureTeam === FB_CTF.data.CONF.currentTeam ) ||
                ( select === "opponent-team" && captureTeam && captureTeam !== FB_CTF.data.CONF.currentTeam ) ){
                countryGroup.classed('highlighted', true);
            } else {
              countryGroup.classed('inactive', true);
            }
          }
        });

        $('tr', $listview).each(function(){
          var $tr = $(this),
              $self = $tr.removeClass('inactive highlighted'),
              captureTeam = $self.data('captured');

          if (select !== "all") {
            if(
              (select === "your-team" && captureTeam === FB_CTF.data.CONF.currentTeam) ||
              (select === "opponent-team" && captureTeam && captureTeam !== FB_CTF.data.CONF.currentTeam) ||
              (select === "give-help" && $('.status--give-help', $tr).length > 0) ||
              (select === "need-help" && $('.status--incoming-help', $tr).length > 0)
            ){
              $self.addClass('highlighted');
            } else {
              $self.addClass('inactive');
            }
          }
        });
      });

      /* --------------------------------------------
       * --country interaction
       * -------------------------------------------- */

      //
      // on country click, open the "capture country" modal
      //
      $map.on('click', '.country-hover g', function( event ){
        event.preventDefault();

        var country = $('[class~="land"]', this).attr('title');

        CURRENT_ZOOM = enableClickAndDrag.getZoom();
        captureCountry(country);
      });

      //
      // hover on a country
      //
      $map.hoverIntent({
        over     : countryHover,
        out      : function(){},
        selector : '.countries > g',
      });

      $countryHover.on('mouseleave', function(event) {
        event.preventDefault();
        FB_CTF.modal.closeHoverPopup();
        $countryHover.empty();
      });
    } // function gameEventListeners()

    /* --------------------------------------------
     * --svg interactions
     * -------------------------------------------- */

    /**
     * when a country is clicked on:
     *   - add the crosshairs interaction
     *   - launch the "capture_country" modal
     *
     * @param country (string)
     *   - the country that is being captured
     *
     * @param capturingTeam (string)
     *   - an optional parameter for the team that is attempting
     *      to capture the given country
     */
    function captureCountry(country, capturingTeam) {
      var $selectCountry    = $('.countries .land[title="' + country + '"]', $mapSvg),
          capturedBy        = getCapturedByMarkup( $selectCountry.closest('g').data('captured') ),
          showAnimation     = !( is_ie || LIST_VIEW ),
          animationDuration = !showAnimation ? 0 : 600;

      // make sure there's a country node
      if($selectCountry.length === 0){
        console.error( country + ' is not a valid country' );
        return;
      }

      if( $countryHover.has('g').length === 0 ){
        var $hoveredCountry = $( '.countries .land[title="' + country + '"]', $mapSvg ).closest('g').clone();
        $countryHover.data( $hoveredCountry.data() );
        $countryHover.empty().append( $hoveredCountry );
      }

      // close the hover popup
      FB_CTF.modal.closeHoverPopup();

      // if there's no data, don't continue
      if (! FB_CTF.data.COUNTRIES) {
        return;
      }

      // if the country is not active, don't continue
      if (! FB_CTF.data.COUNTRIES[country]) {
        return;
      }

      // engage the crosshairs animation
      if( showAnimation ){
        engageCrosshairs();
      }

      if( VIEW_ONLY ){
        setTimeout(function(){
          captureViewOnly( country, capturedBy, capturingTeam );
        }, animationDuration);
      } else {
        setTimeout( function(){
          launchCaptureModal( country, capturedBy );
        }, animationDuration);
      }
    } // function countryClick();

    /**
     * the animation that plays when the user is in view-only mode
     *
     * @param country (string)
     *   - the country being captured
     *
     * @param capturedBy (string)
     *   - the user or team who has captured this country
     *
     * @param capturingTeam (string)
     *   - an optional parameter for the team that is attempting
     *      to capture the given country
     */
    function captureViewOnly( country, capturedBy, capturingTeam ){
      if( capturingTeam === undefined ){
        capturingTeam = FB_CTF.data.CONF.currentTeam;
      }

      FB_CTF.modal.viewmodePopup( function(){
        var $container = $('#fb-country-popup'),
            positionX  = $('.longitude-focus').position().left + 60,
            positionY  = $('.latitude-focus').position().top - $container.height() - 60,
            points     = FB_CTF.data.COUNTRIES && FB_CTF.data.COUNTRIES[country] ? FB_CTF.data.COUNTRIES[country].points : 0;

        $('.capturing-team-name', $container).text(capturingTeam);
        $('.points-value', $container).text('+ ' + points + ' Pts');
        $('.country-owner', $container).text(capturedBy);
        $('.country-name', $container).text(country);

        $container.css({
          left : positionX + 'px',
          top  : positionY + 'px'
        });

        setTimeout(function(){
          removeCaptured();
          FB_CTF.modal.closeHoverPopup();

          enableClickAndDrag.zoomToPoint();
        }, 5000);
      });
    }

      /**
       * remove all the captured/hovered states from the map
       *
       * @param event (object)
       *   - if this is called from an event listener, it comes with
       *      an event object
       */
      function removeCaptured(event){
        if(event)
          event.preventDefault();

        console.log(CURRENT_ZOOM);

        if(CURRENT_ZOOM < 1.1){
          enableClickAndDrag.zoomToPoint();
        }

        $('[class~="country-clicked"]', $mapSvg).fadeOut(function(){
          $(this).remove();
        });
        $countryHover.empty();
      }

      /**
       * launch the "capture country" modal
       *
       * @param country (string)
       *   - the country that is being captured
       *
       * @param capturedBy (string)
       *   - the user or team who has captured this country
       */
      function launchCaptureModal( country, capturedBy ){
        var data = FB_CTF.data.COUNTRIES[country];

        FB_CTF.modal.loadPopup('country-capture', function(){
          var $container = $('.fb-modal-content'),
              level_id   = data ? data.level_id : 0,
              title      = data ? data.title : '',
              intro      = data ? data.intro : '',
              hint       = data ? data.hint : '',
              hint_cost  = data ? data.hint_cost : -1,
              points     = data ? data.points : '',
              category   = data ? data.category : '',
              type       = data ? data.type : '',
              completed  = data ? data.completed : '',
              owner      = data ? data.owner : '',
              attachments= data ? data.attachments : '',
              links      = data ? data.links : '';

          $('.country-name', $container).text(country);
          $('.country-title', $container).text(title);
          $('input[name=level_id]', $container).attr('value',level_id);
          $('.capture-text', $container).text(intro);
          if( attachments instanceof Array){
            $.each(attachments, function(){
              var f = this.substr(this.lastIndexOf('/') +1);
              var attachment = $('<a/>').attr('target', '_blank').attr('href', this).text('[ ' + f + ' ]');
              $('.capture-links', $container).append(attachment);
              $('.capture-links', $container).append($('<br/>'));
            });
          }
          if( links instanceof Array){
            var link_c = 1;
            $.each(links, function(){
              var link;
              if (this.startsWith('http')) {
                link = $('<a/>').attr('target', '_blank').attr('href', this).text('[ Link ' + link_c + ' ]');
              } else {
                var ip = this.split(':')[0];
                var port = this.split(':')[1];
                link = $('<input/>').attr('type', 'text').attr('disabled', true).attr('value', 'nc ' + ip + ' ' + port);
              }
              $('.capture-links', $container).append(link);
              $('.capture-links', $container).append($('<br/>'));
              link_c++;
            });
          }
          $('.points-number', $container).text(points);
          $('.country-type', $container).text(type);
          $('.country-category', $container).text(category);
          $('.country-owner', $container).text(owner);

          if( completed instanceof Array){
            $.each(completed, function(){
              var li = $('<li/>').text(this);
              $('.completed-list', $container).append(li);
            });
          }

          //
          // event listeners
          //
          if (hint_cost == -2) {
            $('.js-trigger-hint span', $container).text('Need more points');
            $('.capture-hint div', $container).text('Need more points');
          } else if (hint_cost == -1) {
            $('.js-trigger-hint span', $container).text('No Hint');
            $('.capture-hint div', $container).text('No Hint');
          } else {
            if (hint_cost == 0) {
              $('.js-trigger-hint span', $container).text('Free Hint');
              $(this).onlySiblingWithClass('active').closest('.fb-modal-content').addClass('hint-enabled');
              $('.capture-hint div', $container).text(hint);
            } else {
              $('.js-trigger-hint', $container).attr('data-hover', '-' + hint_cost + ' PTS');
            }

            $('.js-trigger-hint', $container).on('click', function(event) {
              event.preventDefault();

		          $(this).onlySiblingWithClass('active').closest('.fb-modal-content').addClass('hint-enabled');
              var hint_level = $('input[name=level_id]', $container)[0].value;
              var csrf_token = $('input[name=csrf_token]')[0].value;
              var hint_data = {
                action: 'get_hint',
                level_id: hint_level,
                csrf_token: csrf_token
              };

              $.post(
                'index.php?p=game&ajax=true',
                hint_data
              ).fail(function() {
              // TODO: Make this a modal
                console.log('ERROR');
              }).done(function(data) {
                var responseData = JSON.parse(data);
                if (responseData.result === 'OK') {
                  console.log('OK');
		              console.log('Hint: ' + responseData.hint);
                  $('.capture-hint div', $container).text(responseData.hint);
                } else {
                  console.log('Failed');
                  $('.js-trigger-hint span', $container).text('ERROR');
                }
              });
            });
          }
          $('.js-trigger-score', $container).on('click', function(event) {
            event.preventDefault();

            var score_level = $('input[name=level_id]', $container)[0].value;
            var score_answer = $('input[name=answer]', $container)[0].value;
            var csrf_token = $('input[name=csrf_token]')[0].value;
            var score_data = {
              action: 'answer_level',
              level_id: score_level,
              answer: score_answer,
              csrf_token: csrf_token
            };

            $.post(
              'index.php?p=game&ajax=true',
              score_data
            ).fail(function() {
              // TODO: Make this a modal
              console.log('ERROR');
            }).done(function(data) {
              var responseData = JSON.parse(data);
              if (responseData.result === 'OK') {
                console.log('OK');
                $('input[name=answer]', $container).css("background-color","#1f7a1f");
                $('.js-trigger-score', $container).text('YES!');
                setTimeout(function(){
                  $('.js-close-modal', $container).click();
                }, 2000);
              } else {
              // TODO: Make this a modal
                console.log('Failed');
                $('input[name=answer]', $container).css("background-color","#800000");
                $('.js-trigger-score', $container).text('NOPE :(');
                setTimeout(function(){
                  $('.js-trigger-score', $container).text('SUBMIT');
                  $('input[name=answer]')[0].value = '';
                  $('input[name=answer]', $container).css("background-color","");
                }, 2000);
              }
            });
          });
          $($container).on('keypress', function(e) {
            if (e.keyCode == 13) {
              e.preventDefault();
              $('.js-trigger-score').click();
            }
          });
          $('.js-close-modal', $container).on('click', removeCaptured);
        });
      } // function launchCaptureModal();

      /**
       * on hover of a country in the svg
       */
      function countryHover(event) {
        if(event) {
          event.preventDefault();
        }

        var $self  = $(this),
            country    = $('[class~="land"]', $self).attr('title'),
            capturedBy = getCapturedByMarkup( $self.data('captured') ),
            mouse_x    = event.pageX,
            mouse_y    = event.pageY;

        if (! FB_CTF.data.COUNTRIES) {
          return;
        }

        var data = FB_CTF.data.COUNTRIES[country];

        if (data) {
          FB_CTF.modal.countryHoverPopup(function(){
            var $container = $('#fb-country-popup').css({
              left : mouse_x + 'px',
              top  : mouse_y + 'px'
            }),
                points     = data ? data.points : '',
                category   = data ? data.category : '',
                title      = data ? data.title : '',
                type       = data ? data.type : '';

            $('.country-name', $container).text(country);
            $('.country-title', $container).text(title);
            $('.points-number', $container).text(points);
            $('.country-type', $container).text(type);
            $('.country-category', $container).text(category);
          });
        } else {
          FB_CTF.modal.countryInactiveHoverPopup(function(){
            var $container = $('#fb-country-popup').css({
              left : mouse_x + 'px',
              top  : mouse_y + 'px'
            });

            $('.country-name', $container).text(country);
          });
        }
        //
        // add the country path to the hover group so we can
        //  see the outline
        //
        var clone = $self.clone();
            $countryHover.data( $self.data() ).empty().append(clone);

      } // function countryHover();


      /**
       * the crosshairs interstitial animation that takes place
       *  before the "capture_country" modal appears
       */
      function engageCrosshairs(){
        var svgMap           = d3.select('#fb-gameboard-map'),
            hoveredCountry   = svgMap.select('.country-hover .land'),
            indicator        = svgMap.select('.country-hover .map-indicator');

        //
        // make sure the indicator exists. The double [0] checks
        //  the d3 syntax.
        //
        if( ! indicator[0][0] ){
          return;
        }

        var indicatorStyle   = indicator.attr('transform'),
            // the view controller
            viewController   = svgMap.select('.view-controller'),
            country_clicked  = viewController.append('g').attr('class', 'country-clicked'),
            $country_clicked = $('[class~="country-clicked"]', $mapSvg),

            // setting the explicit height and width of the indictator
            //  seem to be the most effecttive way to position the
            //  crosshair stuff. The sizes are based on the size of the
            //  indicator when the svg is it's natural size
            indicatorWidth   = indicatorStyle.indexOf('scale(') > -1 ? 5.82 : 9.7,
            indicatorHeight  = indicatorStyle.indexOf('scale(') > -1 ? 5.46 : 9.1,

            translateString  = indicatorStyle.replace(' scale(0.6)', '').substring( indicatorStyle.lastIndexOf("translate(") + 10, indicatorStyle.lastIndexOf(")") ),
            translateArray   = translateString.replace( new RegExp('px','g'), '').replace(' ', ',').split(","),

            latFocus         = parseFloat(translateArray[0]) + (indicatorWidth / 2),
            lngFocus         = parseFloat(translateArray[1]) + (indicatorHeight / 2) + 2,
            mapHeight        = $mapSvg.height(),
            mapWidth         = $mapSvg.width(),
            zoom             = enableClickAndDrag.getZoom();

            zoom = zoom < 2 ? 2 : zoom;

            var zoomRatio        = 1/zoom,
            focusAdjusted    = 10 * zoomRatio,
            latLngAdjusted   = 1 / (5.6 * zoomRatio),
            xRatio           = (1 / zoomRatio) / 10;

            enableClickAndDrag.zoomToPoint( latFocus, lngFocus, 2 );

            // add a slightly transparent background to overlay
            //  the rest of the map
        country_clicked.append('rect')
          .attr('x', 0)
          .attr('y', 0)
          .attr('width', mapWidth)
          .attr('height', mapHeight)
          .attr('fill', '#13242b')
          .attr('style', 'opacity:.6');

        // add the hovered country path
        $country_clicked.append(hoveredCountry.node());

        // add the crosshairs
        var crosshairs_xy_translate = 'translate(' + (latFocus + xRatio - 100) + 'px,' + (lngFocus - xRatio - 100) + 'px) scale(' + zoomRatio + ')';
        var crosshairs_xy = country_clicked.append('g')
          .attr('class', 'crosshairs')
          .attr('style', 'transform:' + crosshairs_xy_translate + '; -webkit-transform:' + crosshairs_xy_translate + ';-moz-transform:' + crosshairs_xy_translate + ';');

        var crosshairs = crosshairs_xy.append('g').attr('class', 'crosshairs-rotate');
        // x
        var latLines_translate = 'translate(' + xRatio + 'px,' + (lngFocus - xRatio - 100) + 'px)';
        var latLines = country_clicked.append('g')
          .attr('class', 'latitude-focus')
          .attr('stroke', COLOR_TEAL_BLUE)
          .attr('stroke-width', zoomRatio)
          .attr('style', 'transform:' + latLines_translate + ';-webkit-transform:' + latLines_translate + ';-moz-transform:' + latLines_translate + ';');
        latLines.append('path').attr('d', "M0,0L" + (latFocus - focusAdjusted) + ",0" );
        latLines.append('path').attr('d', "M" + (latFocus + focusAdjusted) + ",0L" + mapWidth + ",0" );
        // y
        var lngLines_translate = 'translate(' + (latFocus + xRatio - 100) + 'px,-' + xRatio + 'px)';
        var lngLines = country_clicked.append('g')
          .attr('class', 'longitude-focus')
          .attr('stroke', COLOR_TEAL_BLUE)
          .attr('stroke-width', zoomRatio)
          .attr('style', 'transform:' + lngLines_translate + ';-webkit-transform:' + lngLines_translate + ';-moz-transform:' + lngLines_translate + ';');
        lngLines.append('path').attr('d', "M0,0L0," + (lngFocus - focusAdjusted) );
        lngLines.append('path').attr('d', "M0," + (lngFocus + focusAdjusted) + "L0," + mapHeight );
        // circles
        crosshairs.append('circle')
          .attr('cx', 0).attr('cy', 0).attr('r', 30)
          .attr('fill', 'none')
          .attr('stroke-dasharray', '31.4 15.7')
          .attr('style', 'transform:rotate(-75deg);-webkit-transform:rotate(-75deg);-moz-transform:rotate(-75deg)')
          .attr('stroke', COLOR_TEAL_BLUE);
        crosshairs.append('circle')
          .attr('cx', 0).attr('cy', 0).attr('r', 35)
          .attr('fill', 'none')
          .attr('stroke-dasharray', '9.158333333 45.79167')
          .attr('style', 'transform:rotate(-53deg);-webkit-transform:rotate(-53deg);-moz-transform:rotate(-53deg)')
          .attr('stroke-width', 2)
          .attr('stroke', COLOR_TEAL_BLUE);

        setTimeout(function(){
          var latLines_active = 'translate(' + xRatio + 'px,' + (lngFocus - xRatio) + 'px)';
              latLines.attr('style', 'transform:' + latLines_active + ';-webkit-transform:' + latLines_active + ';-moz-transform:' + latLines_active + ';');

          var latLines_active = 'translate(' + (latFocus + xRatio) + 'px,-' + xRatio + 'px)';
              lngLines.attr('style', 'transform:' + latLines_active + ';-webkit-transform:' + latLines_active + ';-moz-transform:' + latLines_active + ';');

          var crosshairs_xy_active = 'translate(' + (latFocus + xRatio) + 'px,' + (lngFocus - xRatio) + 'px) scale(' + zoomRatio + ')';
              crosshairs_xy.attr('style', 'transform:' + crosshairs_xy_active + ';-webkit-transform:' + crosshairs_xy_active + ';-moz-transform:' + crosshairs_xy_active + ';');
        }, 10);

        // add the indicator
        $country_clicked.append(indicator.node());
      }

      /* --------------------------------------------
       * --loading
       * -------------------------------------------- */


      /**
       * the gameboard is loading. load the loading screen, and
       *  ensure the loading animation completes before we resolve
       *  this deferred.
       *
       * @return Deferred
       *   - indicate that this jqxhr request is all done
       */
      function loadIn(){
        var df            = $.Deferred(),
            $loadModal    = $('#gameboard-loading'),
            loadPath      = 'inc/gameboard/loading.php';

        if($loadModal.length === 0){
          $loadModal = $('<div id="gameboard-loading" class="fb-loading" />').appendTo( $gameboard );
        }

        FB_CTF.loadComponent($loadModal, loadPath, function() {
          $gameboard.addClass( LOADING_CLASS);

          var $loadingCells = $('.gameboard-loading .indicator-cell'),
              $currCell     = $loadingCells.eq(0),
              loadInterval;

          loadInterval = setInterval(function(){
            if ($currCell.length === 0) {
              clearInterval(loadInterval);
            }
            $currCell.addClass('active');
            $currCell = $currCell.next();
          }, 450 );

          if (!FB_CTF.debug) {
            $('.boot-sequence').fb_typed({
                typeSpeed  : 1,
                humanize   : false,
                showCursor : false,
                typeWords  : true,
                callback   : function() {
                  setTimeout(function(){
                    df.resolve();
                  }, 400);
                }
            });
          } else {
            df.resolve();
          }
        });

        return df;
      }

      /**
       * the gameboard is done loading. Hide the loading screen
       */
      function loadOut() {
        $gameboard.removeClass(LOADING_CLASS);
      }

      /**
       * load up all the modules
       *
       * @return Promise
       *   - when all the modules are loaded, return a promise
       */
      function loadModules(){
        var $modules      = $('aside[data-module]', $gameboard),
            df            = $.Deferred(),
            deferredArray = [];

        $modules.each(function(){
          var $self      = $(this),
              module     = $self.data('module'),
              modulePath = 'inc/gameboard/modules/' + module + '.php';

          var get = $.get( modulePath, function( data, status, jqxhr){
              $self.html(data);
            }).error(function(){
              console.error("There was a problem retrieving the module.");
              console.log(modulePath);
              console.error("/error");
            });

          deferredArray.push(get);
        });

        $.when.apply($, deferredArray).done(function(){
          if (VIEW_ONLY) {
            autoScrollModules();
          }
          df.resolve();
        });

        return df;
      }

      /**
       * load the svg map for gameboard. This inserts the svg into the
       * page and then sets up some jquery object variables for use
       * elsewhere in this module.
       */
      function loadMap(){
        var mapPath = 'static/svg/map/world.php';

        return $.get( mapPath, function(data, status, jqxhr){
          $map = $('.fb-map');
          $map.html(data);
          $mapSvg = $('#fb-gameboard-map');
          $countryHover = $('[class~="country-hover"]', $mapSvg);
          enableClickAndDrag.init();
        }, 'html').error(function(){
          console.error("There was a problem loading the svg map");
          console.error("/error");
        });
      }

      /**
       * load the svg map for view-mode. This inserts the svg into the
       * page and then sets up some jquery object variables for use
       * elsewhere in this module.
       */
      function loadMapView(){
        var mapPath = 'static/svg/map/world-view.php';

        return $.get( mapPath, function(data, status, jqxhr){
          console.log("map loaded");
          $map = $('.fb-map');
          $map.html(data);
          $mapSvg = $('#fb-gameboard-map');
          $countryHover = $('[class~="country-hover"]', $mapSvg);
          enableClickAndDrag.init();
        }, 'html').error(function(){
          console.error("There was a problem loading the svg map");
          console.error("/error");
        });
      }

      /**
       * load the list view for the game
       */
      function loadListView(){
        var listViewPath = 'inc/gameboard/listview.php';

        return $.get(listViewPath, function(data, status, jqxhr) {
          $listview = $('.fb-listview');
          $listview.html(data);
          listviewEventListeners($listview);
        }, 'html').error(function(){
          console.error("There was a problem loading the List View");
          console.error("/error");
        });
      }

      /**
       * load module generic
       */
      function loadModuleGeneric(loadPath, targetSelector){
        $.get(loadPath, function( data, status, jqxhr){
          var $target = $(targetSelector);
          $target.html(data);
          var df = $.Deferred();
          return df.resolve();
        }).error(function(){
          console.error("There was a problem retrieving the module.");
          console.log(loadPath);
          console.error("/error");
        });
      }

      /**
       * load the teams module
       */
      function loadTeamsModule(){
        var teamsModulePath = 'inc/gameboard/modules/teams.php';
        var teamsTargetSelector = 'aside[data-module="teams"]';

        return loadModuleGeneric(teamsModulePath, teamsTargetSelector);
      }

      /**
       * load the leaderboard module
       */
      function loadLeaderboardModule(){
        var leaderboardModulePath = 'inc/gameboard/modules/leaderboard.php';
        var leaderboardSelector = 'aside[data-module="leaderboard"]';

        return loadModuleGeneric(leaderboardModulePath, leaderboardSelector);
      }

      /**
       * load the team data
       */
      function loadTeamData(){
        var loadPath = 'data/teams.php';

        return $.get(loadPath, function(data, status, jqxhr) {
          FB_CTF.data.TEAMS = data;
          var df = $.Deferred();
          return df.resolve(FB_CTF.data.TEAMS);
        }, 'json').error( function( jqhxr, status, error){
          console.error("There was a problem retrieving the team data.");
          console.log(loadPath);
          console.log(status);
          console.log(error);
          console.error("/error");
        });
      }

      /**
       * load the announcements module
       */
      function loadAnnouncementsModule(){
        var announcementsModulePath = 'inc/gameboard/modules/announcements.php';
        var announcementsTargetSelector = 'aside[data-module="announcements"]';

        return loadModuleGeneric(announcementsModulePath, announcementsTargetSelector);
      }

      /**
       * load the filter module
       */
      function loadFilterModule(){
        var filterModulePath = 'inc/gameboard/modules/filter.php';
        var filterTargetSelector = 'aside[data-module="filter"]';

        return loadModuleGeneric(filterModulePath, filterTargetSelector);
      }

      /**
       * load the activity module
       */
      function loadActivityModule(){
        var activityModulePath = 'inc/gameboard/modules/activity.php';
        var activityTargetSelector = 'aside[data-module="activity"]';

        return loadModuleGeneric(activityModulePath, activityTargetSelector);
      }

      /**
       * load the configuration data
       */
      function loadConfData(){
        var loadPath = 'data/configuration.php';

        return $.get( loadPath, function(data, status, jqxhr){
          FB_CTF.data.CONF = data;
          var df = $.Deferred();
          return df.resolve(FB_CTF.data.CONF);
        }, 'json').error( function( jqhxr, status, error){
          console.error("There was a problem retrieving the conf data.");
          console.log(loadPath);
          console.log(status);
          console.log(error);
          console.error("/error");
        });
      }

      /**
       * refresh the map data
       */
      function refreshMapData(){
        var loadPath = 'data/map-data.php';

        return $.get(loadPath, function(data, status, jqxhr) {
          $.each(data, function(key, value){
            // First we clear all
            $('#' + key)[0].classList.remove('active');
            $('#' + key)[0].parentNode.removeAttribute('data-captured');
            $('#' + key)[0].parentNode.children[1].classList.remove("captured--you");
            $('#' + key)[0].parentNode.children[1].classList.remove("captured--opponent");

            // Active country
            if (value.status === 'active') {
              if (!$('#' + key).hasClass('active')) {
                $('#' + key)[0].classList.add('active');
              }
            } /*else { // Inactive country
              $('#' + key)[0].classList.remove('active');
              $('#' + key)[0].parentNode.removeAttribute('data-captured');
              $('#' + key)[0].parentNode.children[1].classList.remove("captured--you");
              $('#' + key)[0].parentNode.children[1].classList.remove("captured--opponent");
            }*/
            if (value.captured == 'you') {
              //$('#' + key)[0].parentNode.children[1].classList.remove("captured--opponent");
              $('#' + key)[0].parentNode.children[1].classList.add("captured--you");
              //$('#' + key)[0].parentNode.removeAttribute('data-captured');
              $('#' + key)[0].parentNode.setAttribute('data-captured', value.datacaptured);
            } else if (value.captured == 'opponent') {
              //$('#' + key)[0].parentNode.children[1].classList.remove("captured--you");
              $('#' + key)[0].parentNode.children[1].classList.add("captured--opponent");
              //$('#' + key)[0].parentNode.removeAttribute('data-captured');
              $('#' + key)[0].parentNode.setAttribute('data-captured', value.datacaptured);
            }
          });
        }, 'json').error( function( jqhxr, status, error){
          console.error("There was a problem retrieving the map data.");
          console.log(loadPath);
          console.log(status);
          console.log(error);
          console.error("/error");
        });
      }

      /**
       * clear the map data
       */
      function clearMapData(){
        var loadPath = 'data/map-data.php';

        return $.get( loadPath, function(data, status, jqxhr){
          $.each(data, function(key, value){
            $('#' + key)[0].classList.remove('active');
            $('#' + key)[0].parentNode.removeAttribute('data-captured');
            $('#' + key)[0].parentNode.children[1].classList.remove("captured--you");
            $('#' + key)[0].parentNode.children[1].classList.remove("captured--opponent");
          });
        }, 'json').error( function( jqhxr, status, error){
          console.error("There was a problem retrieving the map data.");
          console.log(loadPath);
          console.log(status);
          console.log(error);
          console.error("/error");
        });
      }



      /**
       * get the game data, which is stored as json
       *
       * @return Deferred
       *   - indicate that this jqxhr request is all done
       */
      function getCountryData(){
        var loadPath = 'data/country-data.php';

        return $.get( loadPath, function(data, status, jqxhr){
          FB_CTF.data.COUNTRIES = data;
          var df = $.Deferred();
          return df.resolve(FB_CTF.data.COUNTRIES);
        }, 'json').error(function(jqxhr, status, error){
          console.error("There was a problem retrieving the game data.");
          console.log(loadPath);
          console.log(status);
          console.log(error);
          console.error("/error");
        });
      }

      /**
       * since a lot of the data is in an external file, go through
       *  that data and markup the svg so we can use in
       */
      function renderCountryData(){
        if (!FB_CTF.data.COUNTRIES) {
          return;
        }

        $('.countries .land', $mapSvg).each(function(){
          var $countryPath = $(this),
              $group       = $countryPath.closest('g'),
              country      = $countryPath.attr('title'),
              data         = FB_CTF.data.COUNTRIES[country];

          if (data) {
            // add the category
            $group.attr('data-category', data.category);
            // add the status
            var completed_list = data.completed;
            var data_status = (completed_list.indexOf(FB_CTF.data.CONF.currentTeam) >= 0) ? 'completed' : 'remaining';
            $group.attr('data-status', data_status);
          }
        });
      }

      /* --------------------------------------------
       * --list view
       * -------------------------------------------- */

      /**
       * toggle the list view on/off
       */
      function toggleListView( enabled ){
        var activeClass = 'listview-enabled',
            toggle      = enabled === undefined ? ! LIST_VIEW : enabled ? true : false;

            // the containers (for moving the modules around)
            $containerLeft  = $('.fb-module-container.container--column.column-left'),
            $containerRight = $('.fb-module-container.container--column.column-right'),
            $containerRow   = $('.fb-module-container.container--row'),

            // the modules
            $module_activity    = $('aside[data-module="activity"]'),
            $module_leaderboard = $('aside[data-module="leaderboard"]'),
            $module_domination  = $('aside[data-module="world-domination"]');

        if( toggle ){
          $gameboard.addClass( activeClass );
          LIST_VIEW = true;
          $module_activity.prependTo( $containerRow ).addClass('module--outer-left');
          $module_domination.appendTo( $containerLeft );
          $module_leaderboard.prependTo( $containerRight );
        } else {
          $gameboard.removeClass( activeClass );
          LIST_VIEW = false;
          $module_leaderboard.appendTo( $containerLeft );
          $module_activity.appendTo( $containerLeft ).removeClass('module--outer-left');
          $module_domination.prependTo( $containerRow );
        }
      }

      /**
       * the event listeners for the list view mode of the gameboard
       *
       * @param $listview (jquery object)
       *   - the listview
       */
      function listviewEventListeners( $listview ){
        //
        // click on the row, engage the "capture_country"
        //  modal, except for in a couple situations
        //
        $('tr', $listview).on('click', function(event) {
          event.preventDefault();
          var $tr     = $(this).closest('tr'),
              country = $tr.data('country');

        //
        // try to capture the country if:
        //   - the country is not using help
        //   - the country is NOT captured
        //
          if (! $tr.hasClass('help-enabled') && $tr.data('captured') === undefined) {
            captureCountry( country );
          }
        });

        //
        // the incoming help status
        //
        $('tr .status--incoming-help', $listview).on('click', function(event) {
          event.preventDefault();
          event.stopPropagation();
          var country = $(this).closest('tr').data('country');

          FB_CTF.modal.loadPopup('country-help', function(){
            $('#fb-modal .add-new-help-chat').data('country', country);
            $('#fb-modal .country-name').text(country);
          });
        });

        //
        // offer to give help to someone who needs it
        //
        $('tr .status--give-help', $listview).on('click', function(event) {
          event.preventDefault();
          event.stopPropagation();
          var country = $(this).closest('tr').data('country');
          FB_CTF.modal.loadPopup('country-help-opponent', function(){
            $('#fb-modal .add-new-help-chat').data('country', country);
          });
        });

        //
        // click on the timer to launch the individual chat
        //
        $('tr .status--timer', $listview).on('click', function(event) {
          event.preventDefault();
          event.stopPropagation();
          var country = $(this).closest('tr').data('country');
          $('.alerts li[data-help="' + country + '"] .js-expand-individual-chat').trigger('click');
        });
      }

      /* --------------------------------------------
       * --tutorial
       * -------------------------------------------- */

      /**
       * init the tutorial modals
       */
      function initTutorial(event){
        if(event) {
          event.preventDefault();
        }

        var firstTutorial = 'tool-bars',
            tutorialSteps = 8,
            currStepIndex = 1;

        FB_CTF.modal.load('tutorial--' + firstTutorial, function(){
          // we're done loading stuff, so remove the laoding class
          loadOut();
          buildTutorial();
          // enable the "skip tutorial" button
          $('#fb-main-content').on('click', '.fb-tutorial .js-close-tutorial', closeTutorial);
          // enable the "next tutorial" button
          $('#fb-main-content').on('click', 'a[data-next-tutorial]', function(event) {
            event.preventDefault();
            var next = $(this).data('nextTutorial');

            if(next){
              var loadPath = 'inc/modals/tutorial--' + next + '.php';
              currStepIndex++;
              FB_CTF.loadComponent('#fb-modal', loadPath, buildTutorial);
            } else {
              closeTutorial();
            }
          });
        });

        /**
         * things to do after the tutorial step gets loaded
         */
        function buildTutorial(){
          var $tutorial    = $('.fb-tutorial'),
              $progressBar = $('.tutorial-progress', $tutorial),
              currStep     = $tutorial.data('tutorialStep');

          // build the tutorial progress bar
          for (var i = 0; i < tutorialSteps; i++) {
            var markup = i < currStepIndex ? '<li class="step-filled" />' : '<li />';
            $progressBar.append( markup );
          }
          $body.removeClass(function(){
            var stepName = $(this).data('tutorial');
            return 'tutorial-step--' + stepName;
          }).data('tutorial', currStep).addClass('tutorial-active tutorial-step--' + currStep);
        }
      }

      /**
       * close the tutorial
       *
       * @param event (object)
       *   - if this function is called from an event listener,
       *      prevent the default action
       */
      function closeTutorial(event){
        if(event) {
          event.preventDefault();
        }

        $body.removeAttr('class').removeData('tutorial');
        FB_CTF.modal.close();
      }

      /* --------------------------------------------
       * --init
       * -------------------------------------------- */

      /**
       * init the gameboard
       */
      function init(){
        // init the jquery object variables
        $gameboard = $('#fb-gameboard');

        VIEW_ONLY = $body.data('section') === 'viewer-mode';

        if (GAMEBOARD_LOADED === false) {
          build();
        }
      }

      /**
       * utility function to check if the game is currently in
       *  view-only mode
       */
      function isViewMode(){
        return VIEW_ONLY;
      }

      return{
        init               : init,
        data               : getCountryData,
        captureCountry     : captureCountry,
        initTutorial       : initTutorial,
        toggleListView     : toggleListView,
        // clos the tutorial
        closeTutorial      : closeTutorial,
        // enable the zoomable stuff from console
        enableClickAndDrag : enableClickAndDrag,
        // check to see if we're in view mode (for external use)
        isViewMode         : isViewMode
      };
    })(); // gameboard

    /**
     * --modal
     */
    FB_CTF.modal = (function(){
      var LOAD_EXT        = '.php',
      ACTIVE_CLASS    = 'visible',
      POPUP_CLASSES   = 'fb-modal-wrapper modal--popup',
      DEFAULT_CLASSES = 'fb-modal-wrapper modal--default',
      MODAL_DIR       = 'inc/modals/',
      $modalContainer,
      $modal,
      $countryHover;

      /**
       * initialize the modal, including grabbing the modal div and
       *  setting up event listeners
       */
      function init(){
        $modal = $('#fb-modal');
        $modalContainer = $('#fb-main-content');
        $countryHover = $('#fb-country-popup');

        //
        // trigger the launch of a modal
        //
        $body.on('click', '.js-launch-modal', function(event) {
          event.preventDefault();
          var modal = $(this).data('modal'),
              cb;

          //
          // if we're launching the login modal, add the active
          //  class to the nav item
          //
          if(modal === 'login'){
            $('.fb-main-nav a').removeClass('active');
            $(this).addClass('active');
          }
          load(modal, cb);
        });

        //
        // close the modal
        //
        $body.on('click', '.js-close-modal', close);
      }

      /**
       * close the modal
       *
       * @param event (object)
       *   - if this function call comes from an event listener,
       *      prevent the default action
       */
      function close( event ){
        if(event) {
          event.preventDefault();
        }

        $('div[id^="fb-modal"]').removeClass( ACTIVE_CLASS );

        //
        // @NOTICE
        // this is here to re-enable the active state on the nav
        //  in case it was altered when the modal was launched.
        //
        if (_BUILDKIT !== undefined) {
          _BUILDKIT.enableNavActiveState();
        }
      }

      /**
       * close the poup
       *
       * @param event (object)
       *   - if this function call comes from an event listener,
       *      prevent the default action
       */
       function closeHoverPopup( event ){
        if(event) {
          event.preventDefault();
        }

        $countryHover.removeClass(ACTIVE_CLASS);
      }

      /**
       * call FB_CTF.loadComponent for the modal content
       *
       * @param $modal (jquery object)
       *   - the modal jquery object to load the content into
       *
       * @param loadPath (string)
       *   - the path to the modal file
       *
       * @param cb (function)
       *   - the callback
       */
      function openAndLoad( $modal, loadPath, cb){
        FB_CTF.loadComponent( $modal, loadPath, function(){
          if( typeof cb === 'function' ){
            cb();
          }
          $modal.addClass( ACTIVE_CLASS );
        });
      }

      /* --------------------------------------------
       * --the modal rendering
       * -------------------------------------------- */

      /**
       * there are two types of modals - default and popup. The
       *  default modal takes up a full page, while the poup modal
       *  creates a popup box for content. Both of these wrapper
       *  functions take the same parameters.
       *
       * @param modalName (string)
       *   - the filename of the modal content you're looking to
       *      load up, without the trailing .html
       *
       * @param cb (function)
       *   - a callback function for after the modal content loads
       */
      function loadPopup(modalName, cb){
        _load(modalName, POPUP_CLASSES, MODAL_DIR, cb);
      }
      function load(modalName, cb){
        _load(modalName, DEFAULT_CLASSES, MODAL_DIR, cb);
      }

      /**
       * load and create
       *
       * @param modalName (string)
       *   - the filename of the modal content you're looking to
       *      load up
       *
       * @param modalClasses (string)
       *   - the classes for the modal
       *
       * @param loadDir (string)
       *   - the location in the filesystem where we're looking for
       *      the file
       *
       * @param cb (function)
       *   - a callback function for after the modal content loads
       *
       */
      function _load(modalName, modalClasses, loadDir, cb){
        var loadPath = loadDir + modalName + LOAD_EXT;
        closeHoverPopup();
        modalClasses += ' modal--' + modalName;

        if( $modal.length === 0 ){
          $modal = $('<div id="fb-modal" class="' + modalClasses + '" />').appendTo( $modalContainer );
        } else {
          $modal.removeAttr('class').addClass(modalClasses);
        }

        openAndLoad($modal, loadPath, cb);
      }

      /**
       * create a persistent modal. This is used to build a modal
       *  that is very specific, and should be loaded as quickly
       *  as possible after initially loaded, like the command line
       *  modal.
       *
       * @param modalName (string)
       *   - the name of the module being loaded
       *
       * @param cb (function)
       *   - callback funtion for after the persistent modal is loaded
       */
      function loadPersistent(modalName, cb){
        var loadPath = MODAL_DIR + modalName + LOAD_EXT,
            modalId  = 'fb-modal-persistent--' + modalName,
            $modal   = $(modalId);

        if ($modal.length === 0) {
          $modal = $('<div id="' + modalId + '" class="' + POPUP_CLASSES + '" />').appendTo( $modalContainer );
        }
        FB_CTF.loadComponent($modal, loadPath, cb);
      }

      /**
       * open the persistent modal
       *
       * @param modalName (string)
       *   - the name of the modal to open
       */
      function openPersistent(modalName){
        var modalId = '#fb-modal-persistent--' + modalName;
        $(modalId).addClass( ACTIVE_CLASS );
      }

      /**
       * a specific function for rendering the country hover popup
       *
       * @param cb (function)
       *   - a callback to render the country data in the popup
       */
      function countryHoverPopup(cb){
        var loadPath = 'inc/modals/country-popup.php';
        if ($countryHover.length === 0) {
          $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--hover fb-section-border" />').appendTo( $modalContainer );
        }
        openAndLoad($countryHover, loadPath, cb);
      }

      /**
       * a specific function for rendering a inactive country hover popup
       *
       * @param cb (function)
       *   - a callback to render the country data in the popup
       */
      function countryInactiveHoverPopup(cb){
        var loadPath = 'inc/modals/country-inactive-popup.php';
        if ($countryHover.length === 0) {
          $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--hover fb-section-border" />').appendTo( $modalContainer );
        }
        openAndLoad($countryHover, loadPath, cb);
      }

      /**
       * the code for the popup in the view-only mode
       *
       * @param cb (function)
       *   - a callback to render the country data in the popup
       */
      function viewmodePopup( cb ){
        var loadPath = 'inc/modals/country-popup--viewmode.php';
        if ($countryHover.length === 0) {
          $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--view-only" />').appendTo( $modalContainer );
        }

        openAndLoad($countryHover, loadPath, cb);
      }

      return {
        init              : init,
        // loads the basic modal
        load              : load,
        // loads a persistent modal
        loadPersistent    : loadPersistent,
        // open a persistent modal
        openPersistent    : openPersistent,
        // load a popup modal
        loadPopup         : loadPopup,
        // load and show the popup modal for a country hover
        countryHoverPopup : countryHoverPopup,
        // load and show the popup modal for an inactive country hover
        countryInactiveHoverPopup : countryInactiveHoverPopup,
        // load and show the view only country info
        viewmodePopup     : viewmodePopup,
        // close the popup modal for a country hover
        closeHoverPopup   : closeHoverPopup,
        // close the regular modal
        close             : close
      };
    })(); // modal

    /**
     * --slider
     */
    FB_CTF.slider = (function(){
      var selector = '.fb-slider';

      /**
       * init the slider
       *
       * @param cb (function)
       *   - an optional callback function to run after
       *      the slider loads
       */
      function init( cb ){
        var itemWidth = $(selector).closest('#fb-modal').length > 0 ? 90 : 120 ;

        $(selector).flexslider({
          namespace  : "fb-slider-",
          animation  : "slide",
          selector   : ".slides > li",
          slideshow  : false,
          minItems   : 2,
          itemWidth  : itemWidth,
          maxItems   : 7,
          move       : 1,
          controlNav : false,
          start      : function(){
            if( typeof cb === 'function' ){
              cb();
            }
          }
        });
      }

      return {
        init : init
      };
    })(); // slider

    /**
     * --graphics
     * build graphics (like the scorecard) using d3
     */
    FB_CTF.graphics = (function(){

      /**
       * set up event listeners
       */
      function init(){
        $('.fb-graphic').each(function(){
          var $graphic = $(this),
              datafile = $graphic.data('file');

          if ($graphic.hasClass('initialized')) {
            return;
          }
          $graphic.addClass('initialized');
          build(this, datafile);
        });
        //
        // scoreboard filter
        //
        $('input[name="fb-scoreboard-filter"]').on('change', function(event) {
          event.preventDefault();
          var team = $(this).val(),
              $teamLine = $('.scoreboard-graphic-container .team-score-line[data-team="' + team + '"]');
          if(this.checked){
            $teamLine.show();
          } else {
            $teamLine.hide();
          }
        });
      }

      /**
       * build the graph
       *
       * @param svgEl (object)
       *   - the svg element to attach the graphic to
       *
       * @param datafile (string)
       *   - the file to load, which contains the data
       */
      function build( svgEl, datafile ){
        if (datafile === undefined) {
          return;
        }
        var $container = $(svgEl).closest('.scoreboard-graphic-container');

        $.get( datafile , function(data, status, jqxhr){
          var scores = data;
          var maxScore = 0;
          $.each(scores, function(){
            $.each(this.values, function(){
              if (parseInt(this.score) > parseInt(maxScore)) {
                maxScore = parseInt(this.score);
              }
            });
          });
          var maxYaxis = parseInt(maxScore) + 30;

          var graphic  = d3.select( svgEl ),
              MARGIN   = {left: 60, right: 20, bottom:40},
              WIDTH    = $container.length > 0 ? $container.width() - MARGIN.left - MARGIN.right : 820 - MARGIN.left - MARGIN.right,
              HEIGHT   = 220 - MARGIN.bottom,

              X_START  = 1,
              X_LENGTH = FB_CTF.data.CONF.progressiveCount,
              xRange   = d3.scale.linear().range([0, WIDTH]).domain([X_START, X_LENGTH]),
              /*yRange   = d3.scale.linear().range([HEIGHT, 0]).domain([d3.min(minMaxArray, function (d) {
                return d.score;
              }), d3.max(minMaxArray, function (d) {
                return d.score + 30;
              }) ]),*/
              yRange   = d3.scale.linear().range([HEIGHT, 0]).domain([0, maxYaxis]),

              xAxis = d3.svg.axis().tickFormat("").scale(xRange).ticks(X_LENGTH);

              yAxis = d3.svg.axis().scale(yRange).ticks(6).orient("left");

          graphic.append("svg:g").attr("class", "x axis").attr("transform", "translate(" + MARGIN.left + "," + HEIGHT + ")").call(xAxis)
            .selectAll('line').attr('transform', 'translate(0, -6)');

          // Add the text label for the X axis
          graphic.append("text")
            .attr("transform", "rotate(0)")
            .attr("y", HEIGHT + MARGIN.right)
            .attr("x", (WIDTH + MARGIN.left / 2))
            .attr("dy", "1em")
            .attr("stroke", "#fff")
            .style("text-anchor", "middle")
            .text("Time");

          graphic.append("svg:g").attr("class", "y axis").attr("transform", "translate(" + MARGIN.left + ",0)").call(yAxis)
            .selectAll('line').attr('transform', 'translate(6,0)');

          // Add the text label for the Y axis
          graphic.append("text")
            .attr("transform", "rotate(-90)")
            .attr("y", 0)
            .attr("x",0 - (HEIGHT / 2))
            .attr("dy", "1em")
            .attr("stroke", "#fff")
            .style("text-anchor", "middle")
            .text("Score");

          var lineFunc = d3.svg.line().x(function(d) {
            return xRange(d.time) + MARGIN.left;
          }).y(function(d) {
            return yRange(d.score);
          }).interpolate('linear');

          graphic.append('svg:rect')
            .attr('width', WIDTH)
            .attr('height', HEIGHT)
            .attr('x', MARGIN.left)
            .attr('fill', '#142e35');

          var graphLine = graphic.append('svg:g').attr('class', 'mouseline').attr('opacity', "0");

          graphLine.append('svg:path')
            .attr('stroke', COLOR_LIGHT_BLUE)
            .attr('stroke-width', 2)
            .attr('d', "M0,0L0," + HEIGHT);

          graphLine.append('circle')
            .attr('cx', 0)
            .attr('cy', 5)
            .attr('r', 5)
            .attr('stroke', COLOR_LIGHT_BLUE)
            .attr('fill', 'black')
            .attr('stroke-width', 2);

          scores.forEach(function(d, i){
            graphic.append('svg:path')
              .attr('d', lineFunc(d.values))
              .attr('class', 'team-score-line')
              .attr('stroke', d.color)
              .attr('data-team', d.team)
              .attr('stroke-width', 2)
              .attr('fill', 'none');
          });

          graphic.append('svg:rect')
            .attr('width', WIDTH)
            .attr('height', HEIGHT)
            .attr('x', MARGIN.left)
            .attr('fill', 'none')
            .attr('pointer-events', 'all')
            .on('mouseout', function(){
              d3.select(".mouseline").attr("opacity", "0");
            })
            .on('mouseover', function(){
              d3.select(".mouseline").attr("opacity", "1");
            })
            .on('mousemove', function() {
              var xCoor = d3.mouse(this)[0];
              d3.select('.mouseline').attr('transform', 'translate(' + xCoor + ',0)');
            });
        }, 'json').error(function(jqxhr, status, error){
          console.error("There was a problem retrieving the game scores.");
          console.log(status);
          console.log(error);
          console.error("/error");
        });
      }
      return {
        init  : init,
        build : build
      };
    })(); // graphics

    /**
     * --command line
     */
    FB_CTF.command_line = (function(){
      var loadPath  = 'data/command-line.php',
          modalName = 'command-line',
          $cmdPromptList,
          $cmdResultsList;

      /**
       * load the commands data
       */
      function loadCommandsData(){
        var loadPath = 'data/command-line.php';

        return $.get(loadPath, function(data, status, jqxhr) {
          FB_CTF.data.COMMAND = data;
          var df = $.Deferred();
          return df.resolve(FB_CTF.data.COMMAND);
        }, 'json').error( function( jqhxr, status, error){
          console.error("There was a problem retrieving the commands data.");
          console.log(loadPath);
          console.log(status);
          console.log(error);
          console.error("/error");
        });
      }

      /**
       * event listeners for the command line
       *  - the keyup on the window to trigger the command line
       *  - the keyup on the input prompt
       */
      function eventListeners(){
        var $promptInput        = $('#command-prompt--input'),
            $filterResultsInput = $('#command-prompt--filter-results'),
            // since the modal is fading in, we need to delay the
            //  focus so that it actually focuses when the modal
            //  appears
            animDelay           = 400,
            numCommands         = $('li', $cmdPromptList).length;
        //
        // get the command line up
        //
        $(window).on('keyup', function(event) {
          var key = event.which;
          // if the forward slash has been pressed
          if( key === 191){
            // No command line if typing happens in a input text
            if (event.target instanceof HTMLInputElement) {
              return false;
            }
            event.preventDefault();

            FB_CTF.modal.close();
            FB_CTF.modal.closeHoverPopup();
            FB_CTF.modal.openPersistent( modalName );
            if( $('li', $cmdPromptList).length % 2 === 0){
              $cmdPromptList.addClass('offset');
            }
            setTimeout(function(){
              $promptInput.focus();
            }, animDelay);
          }
          // esc closes the command prompt
          else if (key === 27){
            clearCommandPrompt();
            FB_CTF.gameboard.closeTutorial();
            $('.js-close-modal').trigger('click');
          }
        }); // window.on('keyup')

        //
        // type in the command line prompt
        //
        $promptInput.on('keyup', function(event) {
          event.preventDefault();

          var $self         = $(this),
              key           = event.which,
              cmd           = $self.val(),
              $autocomplete = $self.siblings('.autocomplete');

          // if the "enter" key has been pressed
          if (key === 13) {
            var autocompleteCmd = $autocomplete.text(),
                selectedCmd     = FB_CTF.data.COMMAND.commands[cmd];

            if (selectedCmd === undefined) {
              selectedCmd = FB_CTF.data.COMMAND.commands[autocompleteCmd];
              if (selectedCmd) {
                $promptInput.val(autocompleteCmd);
              }
            }
            if (selectedCmd) {
              chooseCommand(selectedCmd);
              $promptInput.trigger('blur');
              $filterResultsInput.trigger('focus');
            } else {
              $cmdResultsList.append('<li>Invalid command</li>');
            }
          }
          // up arrow goes up in results box
          else if (key === 38){
            var $active = $cmdPromptList.find('li.selected');

            if($active.prevAll(':not(.hidden)').length > 0){
              $active = $active.removeClass('selected').prevAll(':not(.hidden)').eq(0).addClass('selected');
            }

            $promptInput.val( $active.text() );
            $autocomplete.empty();
            checkSelectedVisible();
          }
          // down arrow goes down in results box
          else if(key === 40 ){
            var $active = $cmdPromptList.find('li.selected');

            if($active.nextAll(':not(.hidden)').length > 0){
              $active = $active.removeClass('selected').nextAll(':not(.hidden)').eq(0).addClass('selected');
            }

            if( $active.length === 0 ){
              $active = $cmdPromptList.find('li:not(.hidden)').eq(0).addClass('selected');
            }

            $promptInput.val( $active.text() );
            $autocomplete.empty();
            checkSelectedVisible();
          }
          // the user is actually typing
          else {
            $('li', $cmdPromptList).removeClass('hidden selected').filter(function(){
              var text = $(this).text();
              return text.indexOf(cmd) !== 0;
            }).addClass('hidden');

            if( cmd !== "" ){
              var first = $cmdPromptList.find('li:not(.hidden)').eq(0).text();
              $autocomplete.text(first);
            } else {
              $autocomplete.empty();
            }
            $cmdResultsList.empty();
          }
        }); // $promptInput.on('keyup')

        //
        // filter the results from the selected command
        //
        $filterResultsInput.on('keyup', function( event ){
          event.preventDefault();
          var $self         = $(this),
              key           = event.which,
              search        = $self.val(),
              $autocomplete = $self.siblings('.autocomplete'),
              $selected     = $cmdResultsList.find('li.selected');

          if( key === 13 ){
            $('body').trigger('command-option-selected', {
              selected : $selected.text()
            });
          }
          // up arrow goes up in results box
          else if (key === 38){
            var $active = $cmdResultsList.find('li.selected');

            if($active.prevAll(':not(.hidden)').length > 0){
              $active = $active.removeClass('selected').prevAll(':not(.hidden)').eq(0).addClass('selected');
            }

            $filterResultsInput.val( $active.text() );
            $autocomplete.empty();
            checkSelectedVisible();
          }
          // down arrow goes down in results box
          else if(key === 40 ){
            var $active = $cmdResultsList.find('li.selected');

            if($active.nextAll(':not(.hidden)').length > 0){
              $active = $active.removeClass('selected').nextAll(':not(.hidden)').eq(0).addClass('selected');
            }

            if( $active.length === 0 ){
              $active = $cmdResultsList.find('li:not(.hidden)').eq(0).addClass('selected');
            }

            $filterResultsInput.val( $active.text() );
            $autocomplete.empty();
            checkSelectedVisible();
          }
          // else if the use is actually typing
          else {
            var $selected = $cmdResultsList.find('li').removeClass('hidden selected').filter(function(){
              var val = $(this).text().toLowerCase();
              return val.indexOf(search.toLowerCase()) !== 0;
            }).addClass('hidden');

            if($selected.hasClass('hidden')){
              $selected.removeClass('selected');

              if( $selected.nextAll(':not(.hidden)').length > 0){
                $selected.nextAll(':not(.hidden)').eq(0).addClass('selected');
              } else if( $selected.prevAll(':not(.hidden)').length > 0){
                $selected.prevAll(':not(.hidden)').eq(0).addClass('selected');
              }
            }

            if( search !== "" ){
              var first = $cmdResultsList.find('li:not(.hidden)').eq(0).text();

              if( search.charAt(0) === search.charAt(0).toLowerCase() ){
                first = first.toLowerCase();
              }

              $autocomplete.text(first);
            } else {
              $autocomplete.empty();
            }

            if($selected.length === 0){
              $cmdResultsList.find('li').eq(0).addClass('selected');
            }
          }
        });
      } // event listeners

      /**
       * clear all the command prompt stuff
       */
      function clearCommandPrompt() {
        var $promptInput        = $('#command-prompt--input'),
            $filterResultsInput = $('#command-prompt--filter-results');
        $('.fb-command-line .autocomplete').empty();
        $promptInput.val('');
        $filterResultsInput.val('');
        $cmdResultsList.empty();
        $cmdPromptList.find('li').removeClass('hidden selected');
      }

      /**
       * clear all the commands
       */
      function clearCommands() {
        var $commandlist = $('.command-list');
        $('li', $commandlist).remove();

        var $commandresults = $('.row-fluid');
        $('li', $commandresults).remove();
      }

      /**
       * check to see if the selected option is visible in
       *  the container
       */
      function checkSelectedVisible() {
        var $selected = $cmdResultsList.find('li.selected');

        if ($selected.length === 0) {
          return;
        }

        var resultsListScroll = $cmdResultsList.scrollTop(),
            resultListHeight  = $cmdResultsList.height(),
            selectedPos       = $selected.position();

        if (selectedPos.top > resultListHeight) {
          $cmdResultsList.animate({
            scrollTop : (resultsListScroll + selectedPos.top) + 'px'
          })
        } else if (selectedPos.top < 0) {
          $cmdResultsList.animate({
            scrollTop : (resultsListScroll + selectedPos.top) + 'px'
          })
        }
      }

      /**
       * a command has been chosen. Now do stuff, depending on the
       *  command and the data in the command
       *
       * @param cmdData (object)
       *   - an object with data for the selected command
       */
      function chooseCommand( cmdData ){
        var results     = cmdData.results,
            cmdFunction = cmdData.function;

        if (results) {
          if (typeof results === "string") {
            var list = FB_CTF.data.COMMAND.results_library[results];

            if (list) {
              $.each( list, function(index, listItem){
                var li = $('<li/>').text(listItem);
                $cmdResultsList.append(li);
                //$cmdResultsList.append('<li>' + listItem + '</li>');
              });
            }
          } else {
            $.each(results, function(index, listItem){
              var li = $('<li/>').text(listItem);
              $cmdResultsList.append(li);
              //$cmdResultsList.append('<li>' + listItem + '</li>');
            });
          }
          $cmdResultsList.find('li:first-child').addClass('selected');
        }

        if (cmdFunction) {
          var funcName = cmdFunction.name,
              param    = cmdFunction.param;

          switch (funcName) {
            case 'change-radio':
              cmd_changeRadio(param);
              break;
            case 'show-team':
              cmd_showTeam();
              break;
            case 'capture-country':
              cmd_captureCountry();
              break;
            case 'close-module':
              cmd_closeModule();
              break;
            case 'open-module':
              cmd_openModule();
              break;
            case 'open-listview':
              cmd_toggleListView();
              break;
            default:
              console.error("That command's associated function is undefined.");
              break;
          }
        }
      }

      /* --------------------------------------------
       * --cmd functions
       *
       * functions for the commands
       * -------------------------------------------- */

      /**
       * change a radio button selection
       *
       * @param inputName (string)
       *   - the input name we're looking to change
       */
      function cmd_changeRadio( inputName ){
        $('body').on('command-option-selected', function(event, data){
          if(inputName === 'fb--module--filter--category'){
            $('aside[data-module="filter"]').addClass('active');
            $body.trigger('module-changestate');
          }

          $('input[name="' + inputName + '"]').prop('checked', false);
          $('input[name="' + inputName + '"][value="' + data.selected + '"]').trigger('change').prop('checked', true);

          FB_CTF.modal.close();
          clearCommandPrompt();

          $('body').off('command-option-selected');
        });
      }

      /**
       * capture a country
       */
      function cmd_captureCountry(){
        $('body').on('command-option-selected', function(event, data){
          var country = data.selected;
          FB_CTF.modal.close();
          clearCommandPrompt();

          FB_CTF.gameboard.captureCountry(country);

          $('body').off('command-option-selected');
        });
      }

      /**
       * show team's info
       */
      function cmd_showTeam(){
        $('body').on('command-option-selected', function(event, data){
          var team = data.selected;
          FB_CTF.modal.close();
          clearCommandPrompt();

          var teamData = FB_CTF.data.TEAMS[team];

          if( teamData === undefined){
            console.error("Invalid team name in markup");
            return;
          }

          FB_CTF.modal.loadPopup( 'team', function(){
            var $modal       = $('#fb-modal'),
                rank         = teamData.rank + "",
                $teamMembers = $('.team-members', $modal);

            // team name
            $('.team-name', $modal).text(team);
            // team badge
            $('.icon--badge use', $modal).attr('xlink:href', "#icon--badge-" + teamData.badge);
            // team members
            $.each( teamData.team_members, function(){
              $teamMembers.append('<li>' + this + '</li>');
            });
            // rank
            if( rank.length === 1 ){
              rank = "0" + rank;
            }
            $('.points-number', $modal).text( rank );
            // team points
            $('.points--base', $modal).text( teamData.points.base);
            $('.points--quiz', $modal).text( teamData.points.quiz);
            $('.points--flag', $modal).text( teamData.points.flag);
            $('.points--total', $modal).text( teamData.points.total);
          });
          $('body').off('command-option-selected');
        });
      }


      /**
       * close a module
       */
      function cmd_closeModule(){
        $('body').on('command-option-selected', function(event, data){
          var module = data.selected;
          if( module === "All" ){
            $('aside').removeClass('active');
            setAllWidgetStatus('close');
          } else {
            $('aside[data-name="' + module + '"]').removeClass('active');
            setWidgetStatus(module, 'close');
          }
          $body.trigger('module-changestate');

          FB_CTF.modal.close();
          clearCommandPrompt();

          $('body').off('command-option-selected');
        });
      }

      /**
       * open a module
       */
      function cmd_openModule(){
        $('body').on('command-option-selected', function(event, data){
          var module = data.selected;

          if( module === "All" ){
            $('aside').addClass('active');
            setAllWidgetStatus('open');
          } else {
            $('aside[data-name="' + module + '"]').addClass('active');
            setWidgetStatus(module, 'open');
          }
          $body.trigger('module-changestate');

          FB_CTF.modal.close();
          clearCommandPrompt();

          $('body').off('command-option-selected');
        });
      }

      /**
       * toggle the list view
       */
      function cmd_toggleListView(){
        $('body').on('command-option-selected', function(event, data){
          var enable = data.selected === "On" ? true : false;

          FB_CTF.gameboard.toggleListView( enable );

          FB_CTF.modal.close();
          clearCommandPrompt();

          $('body').off('command-option-selected');
        });
      }

      /* --------------------------------------------
       * --init
       * -------------------------------------------- */

      /**
       * init the command line functionality
       */
      function init(){
        FB_CTF.modal.loadPersistent(modalName, function(){
          $.get(loadPath , function(data, status, jqxhr){
            $cmdPromptList = $('.fb-command-line .command-list ul');
            $cmdResultsList = $('.fb-command-line .command-results ul');

            FB_CTF.data.COMMAND = data;

            if (FB_CTF.data.COMMAND && FB_CTF.data.COMMAND.commands) {
              $.each(FB_CTF.data.COMMAND.commands, function(command, cmdData){
                $cmdPromptList.append('<li>' + command + '</li>');
              });
              eventListeners();
            }
          }, 'json').error(function(){
            console.error("There was a problem retrieving the commands.");
            console.error("/error");
          });
        });
      }

      return {
        init: init,
        loadCommandsData: loadCommandsData,
        clearCommands: clearCommands
      };
    })(); // command line

    /* --------------------------------------------
     * --public
     * -------------------------------------------- */
    FB_CTF.init = function(){
      //
      // set up global variables
      //
      $body = $('body');

      //
      // load the svg sprite. This is in the FB_CTF namespace
      //  rather than the buildkit as this is the recommended
      //  method of loading the sprite through a purely front-end
      //  solution. This can be removed if the sprite is included
      //  via some server-side solution.
      //
      FB_CTF.loadComponent('#fb-svg-sprite', 'static/svg/icons/build/icons.svg');

      // load the modal
      FB_CTF.modal.init();

      //
      // any modules that does stuff based on loaded content (for
      //  example, modals or svg grahics) should get fired when the
      //  "content-loaded" event gets fired. The modules that load
      //  content should trigger this event when the content is
      //  done loading
      //
      $body.on('content-loaded', function(event) {
        // load the sliders
        FB_CTF.slider.init();

        // load the grpahics
        FB_CTF.graphics.init();
      }).trigger('content-loaded');

      /* --------------------------------------------
       * --more generic stuff
       * -------------------------------------------- */

      //
      // computer typed text effect
      //
      $(".typed-text").fb_typed({
        typeSpeed: 2,
        showCursor: false,
      });

      //
      // dropkick - for select form elements
      //
      $('select').dropkick();

      /* --------------------------------------------
       * --global event listeners
       * -------------------------------------------- */

      //
      // radio tabs
      //
      $('.radio-tabs').each(function(){
        var $tabs       = $(this),
            $tabContent = $tabs.next('.tab-content-container');

        if ($tabContent.length > 0) {
          $('input[type="radio"]', $tabs).on('change', function(event) {
            event.preventDefault();
            var tab = this.value;
            console.log(tab);

            $('.radio-tab-content[data-tab="' + tab + '"]').onlySiblingWithClass('active');
          });
        }
      })

      //
      // trending list filtering
      //
      var filteredCategories = [];
      $('.trending-list input[type="checkbox"]').on('change', function(event) {
        event.preventDefault();
        var $posts    = $('.post-list--main .fb-post:not(.pinned-post)').removeClass('hidden'),
            isChecked = this.checked,
            category  = $(this).val();

        if ( isChecked) {
          filteredCategories.push(category);
        } else {
          var index = filteredCategories.indexOf(category);
          filteredCategories.splice(index, 1);
        }

        if(filteredCategories.length === 0){
          return;
        }

        $posts.filter(function(){
          var categories = $(this).data('categories'),
              hasCat     = true;

          $.each( filteredCategories, function(i, cat){
            if ( categories.indexOf(cat) > -1) {
              hasCat = false;
              return;
            }
          });
          return hasCat;
        }).addClass('hidden');
      });

      //
      // init the tutorial
      //
      $('.fb-init-tutorial').on('click', function(event) {
        event.preventDefault();
        FB_CTF.gameboard.initTutorial();
      });

      //
      // click events
      //
      $body.on('click', '.click-effect', function(){
        var $self = $(this).addClass('clicked');

        $self.find('span').on('animationend', function(){
          $self.removeClass('clicked');
          $self.off('animationend');
        });
      });

      //
      // prompt logout
      //
      $('.js-prompt-logout').on('click', function(event) {
        event.preventDefault();
        FB_CTF.modal.loadPopup('action-logout');
      });

      //
      // read more posts
      //
      $('.post-readmore').on('click', function(event) {
        event.preventDefault();
        var $self    = $(this),
            $content = $self.closest('.post-content').toggleClass('show-full');

        if ( $content.hasClass('show-full')) {
          $self.text('Close Post');
        } else {
          $self.text('Read More');
        }
      });

      //
      // rules table of contents
      //
      $('.rules--table-of-contents li a').on('click', function(event) {
        event.preventDefault();

        var $rules         = $('.fb-rules section'),
            index          = $(this).parent().index(),
            offset         = $('.page--rules .fb-section-header').innerHeight(),
            $selectedRule  = $rules.eq(index),
            rulesScrollTop = $selectedRule.position().top + offset;

        $('.page--rules').animate({
          scrollTop: rulesScrollTop + 'px'
        });
      });

      //
      // choose a logo
      //
      $body.on('click', '.emblem-carousel .slides li', function(event) {
        event.preventDefault();
        $(this).onlySiblingWithClass('active');
      });
    }

    /**
     * load a component into a target on the site
     *
     * @param target (string)
     *   - selector to select where to load the content
     *
     * @param component (string)
     *   - the name of the component to load
     *
     * @param cb (function)
     *   - callback function for when the load is successful
     */
    FB_CTF.loadComponent = function(target, component, cb){
      var $target = typeof target === 'object' ? target : $(target);

      $target.load( component, function(response, status, jqxhr){
        if(status === "error"){
          console.error("There was a problem loading the component:");
          console.log("target: " + target);
          console.log("component: " + component);
          console.error("/end error");
        } else {
          //
          // fire the "content-loaded" event to initialize any
          //  dynamic content that is in the loaded content
          //
          $('body').trigger('content-loaded', {component: component});

          if( typeof cb === 'function'){
            cb();
          }
        }
      });
    }

    /**
     * set up stuff on document ready
     */
     $(document).ready(function() {
        //
        // check to make sure that we're not using the buildkit, and
        //  then initialize the Capture the Flag scripts. This
        //  prevents the init() function from running multiple times
        //  if we are using the buildkit.
        //
        if( typeof _BUILDKIT === 'undefined' ){
          FB_CTF.init();
        }
      });

   })(window.FB_CTF = window.FB_CTF || {}, jQuery);

function activateTeams() {
  var $teamgrid = $('aside[data-module="teams"]');
  $teamgrid.on('click', 'a', function(event) {
    event.preventDefault();
    var team = String($(this).data('team'));

    if( team === undefined || team === ""){
      team = "Facebook CTF";
    }
    var teamData = FB_CTF.data.TEAMS[team];
    if (teamData === undefined) {
      console.error("Invalid team name in markup");
      return;
    }
    FB_CTF.modal.loadPopup( 'team', function(){
        var $modal       = $('#fb-modal'),
            rank         = teamData.rank + "",
            $teamMembers = $('.team-members', $modal);
        // team name
        $('.team-name', $modal).text(team);
        // team badge
        $('.icon--badge use', $modal).attr('xlink:href', "#icon--badge-" + teamData.badge);
        // team members
        $.each( teamData.team_members, function(){
          $teamMembers.append('<li>' + this + '</li>');
        });
        // rank
        if( rank.length === 1 ){
          rank = "0" + rank;
        }
        $('.points-number', $modal).text( rank );
        // team points
        $('.points--base', $modal).text( teamData.points.base);
        $('.points--quiz', $modal).text( teamData.points.quiz);
        $('.points--flag', $modal).text( teamData.points.flag);
        $('.points--total', $modal).text( teamData.points.total);
      });

  });
}
