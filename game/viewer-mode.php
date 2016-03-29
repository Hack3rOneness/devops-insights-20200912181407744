<?hh //strict

require_once('../vendor/autoload.php');

class ViewModeController extends Controller {

  public function renderMainContent(): :xhp {
    return
      <div id="fb-gameboard" class="fb-gameboard gameboard--viewmode">
        <div class="gameboard-header">
          <nav class="fb-navigation fb-gameboard-nav">
            <div class="branding">
              <a href="/">
                <div class="branding-rules">
                  <span class="branding-el">
                    <svg class="icon icon--social-facebook">
                      <use xlink:href="#icon--social-facebook">

                      </use>
                    </svg>
                    <span class="has-icon"> Powered By Facebook</span>
                  </span>
                </div>
              </a>
            </div>
          </nav>
        </div>
        <div class="fb-map"></div>
        <div class="fb-module-container container--row">
          <aside data-name="Leaderboard" class="module--outer-left active" data-module="leaderboard-viewmode"></aside>
          <aside data-name="Under Attack" class="module--inner-right active" data-module="under-attack"></aside>
          <aside data-name="Game Clock" class="module--outer-right active" data-module="game-clock"></aside>
        </div>
      </div>;
  }

  public function renderPage(string $page): :xhp {
    switch ($page) {
      case 'main':
      return $this->renderMainContent();
      break;
    default:
      return $this->renderMainContent();
      break;
    }
  }

  public function renderBody(string $page): :xhp {
    return
      <body data-section="viewer-mode">
        <div class="fb-sprite" id="fb-svg-sprite"></div>
        <div id="fb-main-content" class="fb-page">
          {$this->renderPage($page)}
        </div>
        <script type="text/javascript" src="static/js/vendor/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="static/js/vendor/d3.min.js"></script>
        <script type="text/javascript" src="static/js/plugins.js"></script>
        <script type="text/javascript" src="static/js/_buildkit.js"></script>
        <script type="text/javascript" src="static/js/fb-ctf.js"></script>
      </body>;
  }
}

$viewmode = new ViewModeController();
$filters = array(
  'GET' => array(
    'page'        => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    ),
    'action'      => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    )
  )
);
$actions = array('none');
$pages = array(
  'main',
);
$request = new Request($filters, $actions, $pages);
$request->processRequest();
echo $viewmode->render('Facebook CTF | Gameboard', $request->page);