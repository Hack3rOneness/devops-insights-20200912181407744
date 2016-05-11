<?hh // strict

/* HH_IGNORE_ERROR[1002] */
SessionUtils::sessionStart();
SessionUtils::enforceLogin();

class GameboardController extends Controller {
  <<__Override>>
  protected function getTitle(): string {
    return 'Facebook CTF | Gameboard';
  }

  <<__Override>>
  protected function getFilters(): array<string, mixed> {
    return array(
      'GET' => array(
        'page'        => array(
          'filter'      => FILTER_VALIDATE_REGEXP,
          'options'     => array(
            'regexp'      => '/^[\w-]+$/'
          ),
        ),
      )
    );
  }

  <<__Override>>
  protected function getPages(): array<string> {
    return array(
      'main',
      'viewmode',
    );
  }

  public function renderMainContent(): :xhp {
    if (SessionUtils::sessionAdmin()) {
      $admin_link = <li><a href="index.php?p=admin">Admin</a></li>;
    } else {
      $admin_link = null;
    }
    return
      <div id="fb-gameboard" class="fb-gameboard">
        <div class="gameboard-header">
          <nav class="fb-navigation fb-gameboard-nav">
            <ul class="nav-left">
              <li>
                <a>Navigation</a>
                <ul class="subnav">
                  <!-- <li><a href="/index.php?p=view">View Mode</a></li> -->
                  <li><a href="#" class="fb-init-tutorial">Tutorial</a></li>
                  {$admin_link}
                  <li><a href="/index.php?page=rules" target="_blank">Rules</a></li>
                  <li><a href="#" class="js-prompt-logout">Logout</a></li>
                </ul>
              </li>
            </ul>
            <div class="branding">
              <a href="index.php?p=game">
                <div class="branding-rules">
                  <fbbranding />
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
                  <use href="#icon--team-indicator"/>

                </svg>You</span>
            </label>
            <input type="radio" name="fb--map-select" id="fb--map-select--enemy" value="opponent-team"/>
            <label for="fb--map-select--enemy" class="click-effect">
              <span class="opponent-name">
                <svg class="icon icon--team-indicator opponent-team">
                  <use href="#icon--team-indicator"/>

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

  <<__Override>>
  public async function genRenderBody(string $page): Awaitable<:xhp> {
    return
      <body data-section="gameboard">
        <input type="hidden" name="csrf_token" value={SessionUtils::CSRFToken()}/>
        <div class="fb-sprite" id="fb-svg-sprite"></div>
        <div id="fb-main-content" class="fb-page">{$this->renderPage($page)}</div>
        <script type="text/javascript" src="static/dist/js/app.js"></script>
      </body>;
  }
}
