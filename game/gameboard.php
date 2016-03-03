<?hh

require_once('controller.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');

sess_start();
sess_enforce_login();

class GameboardController extends Controller {

  public function renderBody(): :xhp {
    return
      <body data-section="gameboard">
        <div style="height: 0; width: 0; position: absolute; visibility: hidden" id="fb-svg-sprite"></div>
        <div id="fb-buildkit" class="fb-page"></div><!-- #fb-buildkit -->
        
        <script type="text/javascript" src="static/js/vendor/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="static/js/vendor/d3.min.js"></script>
        <script type="text/javascript" src="static/js/plugins.js"></script>
        <script type="text/javascript" src="static/js/_buildkit.js"></script>
        <script type="text/javascript" src="static/js/fb-ctf.js"></script>
      </body>;
  }
}

$gameboard = new GameboardController();
echo $gameboard->render('Facebook CTF | Gameboard');