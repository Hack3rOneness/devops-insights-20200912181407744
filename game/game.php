<?hh

require_once('../vendor/autoload.php');

sess_start();
sess_enforce_login();

class GameboardController extends Controller {

  public function renderMainContent(): :xhp {
    if (sess_admin()) {
      $admin_link = <li><a href="admin.php">Admin</a></li>;
    }
    return
      <div id="fb-gameboard" class="fb-gameboard">
        <div class="gameboard-header">
          <nav class="fb-navigation fb-gameboard-nav">
            <ul class="nav-left">
              <li>
                <a>Navigation</a>
                <ul class="subnav">
                  <li><a href="/viewer-mode.php">View Mode</a></li>
                  <li><a href="#" class="fb-init-tutorial">Tutorial</a></li>
                  {$admin_link}
                  <li><a href="/index.php?page=rules" target="_blank">Rules</a></li>
                  <li><a href="#" class="js-prompt-logout">Logout</a></li>
                </ul>
              </li>
            </ul>
            <div class="branding">
              <a href="game.php">
                <div class="branding-rules">
                  <span class="branding-el">
                    <svg class="icon icon--social-facebook">
                      <use xlink:href="#icon--social-facebook"/>

                    </svg>
                    <span class="has-icon"> Powered By Facebook</span>
                  </span>
                </div>
              </a>
            </div>
            <ul class="nav-right">
              <li>
                <a href="#" class="js-launch-modal" data-modal="scoreboard">Scoreboard</a>
              </li>
            </ul>
          </nav>
          <div class="radio-tabs fb-map-select">
            <input type="radio" name="fb--map-select" id="fb--map-select--you" value="your-team"/>
            <label for="fb--map-select--you" class="click-effect">
              <span class="your-name">
                <svg class="icon icon--team-indicator your-team">
                  <use xlink:href="#icon--team-indicator"/>
                  
                </svg>You</span>
            </label>
            <input type="radio" name="fb--map-select" id="fb--map-select--enemy" value="opponent-team"/>
            <label for="fb--map-select--enemy" class="click-effect">
              <span class="opponent-name">
                <svg class="icon icon--team-indicator opponent-team">
                  <use xlink:href="#icon--team-indicator"/>
                
                </svg>Others</span>
            </label>
            <input type="radio" name="fb--map-select" id="fb--map-select--all" value="all" />
            <label for="fb--map-select--all" class="click-effect"><span>All</span></label>
          </div>
        </div>
        <div class="fb-map"></div>
        <div class="fb-listview"></div>
        <div class="fb-module-container container--column column-left">
          <aside data-name="Leaderboard" data-module="leaderboard"></aside>
          <aside data-name="Announcements" data-module="announcements"></aside>
        </div>
        <div class="fb-module-container container--column column-right">
          <aside data-name="Teams" data-module="teams"></aside>
          <aside data-name="Filter" data-module="filter"></aside>
        </div>
        <div class="fb-module-container container--row">
          <aside data-name="Activity" class="module--outer-left" data-module="activity"></aside>
          <aside data-name="Game Clock" class="module--outer-right" data-module="game-clock"></aside>
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
      <body data-section="gameboard">
        <div class="fb-sprite" id="fb-svg-sprite"></div>
        <div id="fb-main-content" class="fb-page">{$this->renderPage($page)}</div>
        <script type="text/javascript" src="static/js/vendor/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="static/js/vendor/d3.min.js"></script>
        <script type="text/javascript" src="static/js/plugins.js"></script>
        <script type="text/javascript" src="static/js/_buildkit.js"></script>
        <script type="text/javascript" src="static/js/fb-ctf.js"></script>
      </body>;
  }
}

$gameboard = new GameboardController();
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
  'viewmode',
);
$request = new Request($filters, $actions, $pages);
$request->processRequest();
echo $gameboard->render('Facebook CTF | Gameboard', $request->page);