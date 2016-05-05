var jQuery = require('jquery');
var $ = jQuery;
window.jQuery = jQuery;
require('typed.js'); // TODO: Can we remove this?
var FB_CTF = require('./fb-ctf');
FB_CTF.admin = require('./admin-fb-ctf');


/**
 * add the transitionend event to a global var
 */
(function(window) {
  var transitions = {
    'transition': 'transitionend',
    'WebkitTransition': 'webkitTransitionEnd',
    'MozTransition': 'transitionend',
    'OTransition': 'otransitionend'
  },
      elem = document.createElement('div');

  for (var t in transitions) {
    if (typeof elem.style[t] !== 'undefined') {
      window.transitionEnd = transitions[t];
      break;
    }
  }
})(window);


/**
 * jQuery plugin for adding a class to an element and ensuring that
 *  it is the only sibling with the passed class
 */
!(function($) {
  $.fn.onlySiblingWithClass = function(className) {
    return this.each(function() {
      $(this).addClass(className).siblings('.' + className).removeClass(className);
    });
  };
})(jQuery);

/**
 * Modifications to the typed plugin that use the default text in an
 *  element as the text to be typed, and also adds an option to
 *  "type words" rather than type individual characters (this allows
 *  for a faster typing effect).
 */
!(function($) {
  $.fn.fb_typed = function(passed_options) {
    return this.each(function() {
      var $self = $(this),
          text = $self.html();

      $self.empty().addClass('typing-initialized');

      var options = $.extend({
        strings: [text],
        typeWords: false
      }, passed_options);

      //
      // if the typeWords option is set, then we want to type
      //  fast. So, we have to separate the text by CHUNKS of
      //  characters rather than just characters.
      //
      if (options.typeWords) {
        var lines = text.split('<br>'),
            lineIndex = 0;

        if (lines.length === 0) {
          return;
        }

        /**
         * render a line of text
         */
        function renderLine(chunk) {
          if (lineIndex > lines.length) {
            options.callback();
            return;
          }

          if (!chunk) {
            $self.append('<br>');
            lineIndex++;
            renderLine(lines[lineIndex]);
          } else {
            var chunkArray = chunk.match(/.{1,4}/g),
                chunkIndex = 0;

            var chunkInterval = setInterval(function() {
              if (chunkArray[chunkIndex]) {
                $self.append(chunkArray[chunkIndex]);
                chunkIndex++;
              } else {
                $self.append('<br>');
                lineIndex++;
                clearInterval(chunkInterval);
                renderLine(lines[lineIndex]);
              }
            }, 20);
          }
        }

        renderLine(lines[lineIndex]);


      }
      //
      // if the typedWords option is not enabled, then just use
      //  the typed plugin
      //
      else {
        $(this).typed(options);
      }

    });
  };
})(jQuery);

(function(_BUILDKIT, $, undefined) {
  var FB_CTF = window.FB_CTF;
  var $body,
      FB_SECTION;

  function getURLParameter(name) {
    // eslint-disable-next-line no-sparse-arrays
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
  }

  _BUILDKIT.enableNavActiveState = function() {
    var page = getURLParameter('page');

    $('.fb-main-nav a').removeClass('active').filter(function() {
      var href = $(this).data('active');

      if (href === undefined || !href.indexOf || page === '') {
        return false;
      }
      return href.indexOf(page) > -1;
    }).addClass('active');
  };

  _BUILDKIT.enableAdminActiveState = function() {
    var page = getURLParameter('page');

    $('#fb-admin-nav li').removeClass('active').filter(function() {
      var href = $('a', this).attr('href').replace('#', '');

      if (href === undefined || !href.indexOf || page === '') {
        return false;
      }
      return href.indexOf(page) > -1;
    }).addClass('active');
  };

  $(document).ready(function() {
    $body = $('body');
    FB_SECTION = $body.data('section');

    if (window.innerWidth < 960) {
      window.location = '/index.php?page=mobile';
    }

    FB_CTF.init();

    if (FB_SECTION === 'pages') {
      _BUILDKIT.enableNavActiveState();
    } else if (FB_SECTION === 'gameboard' || FB_SECTION === 'viewer-mode') {
      FB_CTF.gameboard.init();
    } else if (FB_SECTION === 'admin') {
      FB_CTF.admin.init();
      _BUILDKIT.enableAdminActiveState();
    }

    $('body').trigger('content-loaded', {
      page: FB_SECTION
    });
  });

})(window._BUILDKIT = window._BUILDKIT || {}, $);
