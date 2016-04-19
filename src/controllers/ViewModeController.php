<?hh // strict

class ViewModeController extends Controller {
  <<__Override>>
  protected function getTitle(): string {
    return 'Facebook CTF | View mode';
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
    );
  }

  public function renderMainContent(): :xhp {
    return
      <div id="fb-gameboard" class="fb-gameboard gameboard--viewmode">
        <div class="gameboard-header">
          <nav class="fb-navigation fb-gameboard-nav">
            <div class="branding">
              <a href="/">
                <div class="branding-rules">
                  <fbbranding />
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

  <<__Override>>
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
