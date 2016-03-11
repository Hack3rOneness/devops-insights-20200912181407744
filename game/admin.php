<?hh

require_once('controller.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');

sess_start();
sess_enforce_login();
sess_enforce_admin();

class AdminController extends Controller {

  public function renderBody(): :xhp {
    return
      <body data-section="admin">
        <div style="height: 0; width: 0; position: absolute; visibility: hidden" id="fb-svg-sprite"></div>
        <div class="fb-viewport admin-viewport">
          <div id="fb-admin-nav" class="admin-nav-bar fb-row-container">
            <header class="admin-nav-header row-fixed">
              <h2>Game Admin</h2>
            </header>
            <nav class="admin-nav-links row-fluid">
              <ul>
                <li><a href="#game-configuration">Game Configuration</a></li>
                <li><a href="#game-controls">Game Controls</a></li>
                <li><a href="#quiz-management">Levels: Quiz</a></li>
                <li><a href="#flags-management">Levels: Flags</a></li>
                <li><a href="#bases-management">Levels: Bases</a></li>
                <li><a href="#categories-management">Levels: Categories</a></li>
                <li><a href="#countries-management">Levels: Countries</a></li>
                <li><a href="#team-management">Teams</a></li>
                <li><a href="#logo-management">Teams: Logos</a></li>
                <li><a href="#sessions">Teams: Sessions</a></li>
                <li><a href="#scoreboard">Scoreboard</a></li>
              </ul>

              <a href="#" class="fb-cta cta--yellow">Begin Game</a>
            </nav>

            <div class="admin-nav--footer row-fixed">
              <a href="/gameboard.php">Gameboard</a>
              <a href="#" class="js-prompt-logout">Logout</a>
              <a></a>

              <span class="branding-el">
                <span class="has-icon">Powered By Facebook</span>
              </span>
            </div>

          </div><!-- /end main navigation -->
          <div id="fb-buildkit" class="fb-page fb-admin-main"></div><!-- #fb-buildkit -->
        </div><!-- .fb-viewport -->
        
        <script type="text/javascript" src="static/js/vendor/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="static/js/vendor/d3.min.js"></script>
        <script type="text/javascript" src="static/js/plugins.js"></script>
        <script type="text/javascript" src="static/js/fb-ctf.js"></script>
        <script type="text/javascript" src="static/js/admin-fb-ctf.js"></script>
        <script type="text/javascript" src="static/js/_buildkit.js"></script>
      </body>;
  }
}

$adminpage = new AdminController();
echo $adminpage->render('Facebook CTF | Admin');