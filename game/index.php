<?hh

require_once('controller.php');

class IndexController extends Controller {

  public function renderBody(): :xhp {
    return
      <body data-section="pages">
        <div style="height: 0; width: 0; position: absolute; visibility: hidden" id="fb-svg-sprite"></div>
        <div class="fb-viewport">
          <div id="fb-main-nav"></div><!-- main navigation -->
          <div id="fb-buildkit" class="fb-page"></div><!-- #fb-buildkit -->
        </div><!-- .fb-viewport -->
        
        <script type="text/javascript" src="static/js/vendor/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="static/js/vendor/d3.min.js"></script>
        <script type="text/javascript" src="static/js/plugins.js"></script>
        <script type="text/javascript" src="static/js/_buildkit.js"></script>
        <script type="text/javascript" src="static/js/fb-ctf.js"></script>
        <script type="text/javascript" src="static/js/actions.js"></script>
      </body>;
  }
}

$indexpage = new IndexController();
echo $indexpage->render('Facebook CTF');