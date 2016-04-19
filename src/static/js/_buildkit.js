(function(_BUILDKIT, $, undefined){

  var $loadTarget,
      $body,
      FB_SECTION;

  function getURLParameter(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
  }

  _BUILDKIT.enableNavActiveState = function(){
    var page = getURLParameter('page');

    $('.fb-main-nav a').removeClass('active').filter(function(){
      var href = $(this).data('active');

      if(href === undefined || !href.indexOf || page === ''){
        return false;
      }
      return href.indexOf( page ) > -1;
    }).addClass('active');
  }

  _BUILDKIT.enableAdminActiveState = function(){
    var page = getURLParameter('page');

    $('#fb-admin-nav li').removeClass('active').filter(function(){
      var href = $('a', this).attr('href').replace('#', '');

      if(href === undefined || !href.indexOf || page === ''){
        return false;
      }
      return href.indexOf( page ) > -1;
    }).addClass('active');
  }

  $(document).ready(function() {
    $body = $('body');
    $loadTarget = $('#fb-main-content');
    FB_SECTION = $body.data('section');

    if( window.innerWidth < 960 ){
      window.location = '/index.php?page=mobile';
    }

    FB_CTF.init();

    if( FB_SECTION === 'pages' ){
      _BUILDKIT.enableNavActiveState();
    } else if( FB_SECTION === 'gameboard' || FB_SECTION === 'viewer-mode' ){
      FB_CTF.gameboard.init();
    } else if( FB_SECTION === 'admin'){
      FB_CTF.admin.init();
      _BUILDKIT.enableAdminActiveState();
    }

    $('body').trigger('content-loaded', {page: FB_SECTION});
  });

})(window._BUILDKIT = window._BUILDKIT || {}, jQuery);
