<?hh

require_once('../vendor/autoload.php');

class IndexController extends Controller {

  public function renderMainContent(): :xhp {
    return
      <div class="fb-row-container full-height fb-scroll">
        <main role="main" class="fb-main page--landing row-fluid no-shrink center-vertically fb-img-glitch">
          <div class="fb-container fb-centered-main">
            <h1 class="fb-glitch" data-text="Conquer the World">Conquer the world</h1>
            <p class="typed-text">Welcome to the Facebook Capture the Flag Competition. By clicking "Play," you will be entered into the official CTF challenge. Good luck in your conquest.</p>

            <div class="fb-actionable">
              <a href="/index.php?page=countdown" class="fb-cta cta--yellow">Play</a>
            </div>
          </div>
        </main>
      </div>;
  }

  public function renderCountdownContent(): :xhp {
    sess_start();
    if (sess_active()) {
      $play_nav =
        <form class="fb-form inner-container">
          <p>Get ready for the CTF to start and access the gameboard now!</p>
          <div class="form-el--actions">
            <a href="/game.php" class="fb-cta cta--yellow">Gameboard</a>
          </div>
        </form>;
    } else {
      $play_nav =
        <form class="fb-form inner-container">
          <p>Get ready for the CTF to start and register your team now!</p>
          <div class="form-el--actions">
            <a href="/index.php?page=registration" class="fb-cta cta--yellow">Register Team</a>
          </div>
        </form>;
    }
    $c = new Configuration();
    $next_game = $c->get('next_game');
    if ($next_game === "0") {
      $next_game_text = "Soon";
    } else {
      $next_game_text = $next_game;
    }
    return
      <div class="fb-row-container full-height fb-scroll">
        <main role="main" class="fb-main page--game-status row-fluid no-shrink center-vertically fb-img-glitch">
          <div class="fb-container fb-centered-main">
            <h3 class="title-lead">Upcoming Game</h3>
            <h1 class="fb-glitch" data-text={$next_game_text}>{$next_game_text}</h1>
            <ul class="upcoming-game-countdown">
              <li><span class="count-number">--</span>_days</li>
              <li><span class="count-number">--</span>_hours</li>
              <li><span class="count-number">--</span>_minutes</li>
              <li><span class="count-number">--</span>_seconds</li>
            </ul>
            {$play_nav}
          </div>
        </main>
      </div>;
  }

  public function renderRulesContent(): :xhp {
    return
      <div class="fb-column-container full-height">
        <main role="main" class="fb-main page--rules fb-scroll">
          <header class="fb-section-header fb-container">
            <h1 class="fb-glitch" data-text="Official CTF Rules">Official CTF Rules</h1>
            <p class="inner-container typed-text">
              Lorem ipsum dolor sit amet, nec quem fugit ea. Novum decore scriptorem appellantur, partem iriure lobortis sed.
            </p>
          </header>
          <div class="fb-rules">
            <section>
              <header class="rule-section-header">
                <h3>Rule Topic 1</h3>
                <h6>LOREM IPSUM DOLOR SIT</h6>
              </header>
              <div class="rule-main">
                <p>Lorem ipsum dolor sit amet, nec quem fugit ea. Novum scriptorem appellantur, partem iriure lobortis sed. Sed tractatos sapientem deterruisset ex, ut vim ancillae verterem id est novum dolor partem iriure lobortis perfecto iriure lobortis. Sed tractatos sapientem deterruisset ex, ut vim ancillae verterem id est novum dolor partem iriure lobortis perfecto iriure lobortis.
                </p>
                <p>Lorem ipsum dolor sit amet, nec quem fugit ea. Novum scriptorem appellantur, partem iriure lobortis sed. Sed tractatos sapientem deterruisset ex, ut vim ancillae verterem id est novum dolor partem iriure lobortis perfecto iriure lobortis. Sed tractatos sapientem deterruisset ex, ut vim ancillae verterem id est novum dolor partem iriure lobortis perfecto iriure lobortis.
                </p>
                <p></p>
                <ul>
                  <li>Lorem ipsum</li>
                  <li>Ipsum dolor sit</li>
                  <li>Cum decore ceirois</li>
                  <li>Dolor sit</li>
                  <li>lorem ipsum</li>
                </ul>
              </div>
            </section>
          </div>
        </main>
        <aside class="fb-secondary fb-row-container">
          <div class="row-fixed">
            <div class="fb-search">
              <input type="search" placeholder="Type to search"/>
            </div>
          </div>
          <div class="blog--top-posts row-fluid fb-row-container">
            <header class="row-fixed aside-section-header">
              <h5>Table of Contents</h5>
            </header>
            <ul class="rules--table-of-contents row-fluid">
              <li><a href="#">Rule Topic 1</a></li>
              <li><a href="#">Rule Topic 2</a></li>
              <li><a href="#">Rule Topic 3</a></li>
              <li><a href="#">Rule Topic 4</a></li>
              <li><a href="#">Rule Topic 5</a></li>
              <li><a href="#">Rule Topic 6</a></li>
            </ul>
          </div>
        </aside>
      </div>;
  }

  public function renderLogosSelection(): :xhp {
    $logos = new Logos();
    $logos_list = <ul class="slides" />;
    foreach ($logos->all_enabled_logos() as $logo) {
      $xlink_href = '#icon--badge-'.$logo['name'];
      $logos_list->appendChild(<li><svg class="icon--badge"><use xlink:href={$xlink_href}></use></svg></li>);
    }
    return
      <div class="fb-slider fb-container container--large">
        {$logos_list}
      </div>;
  }

  public function renderRegistrationContent(): :xhp {
    $conf = new Configuration();
    if ($conf->get('registration') === '1') {
      return
        <main role="main" class="fb-main page--registration full-height fb-scroll">
          <header class="fb-section-header fb-container">
            <h1 class="fb-glitch" data-text="Team Registration">Team Registration</h1>
            <p class="inner-container">Register to play Capture The Flag here. Once you have registered, you will be logged in.</p>
          </header>
          <div class="fb-registration">
            <form class="fb-form">
              <input type="hidden" name="action" value="register_team"/>
              <fieldset class="form-set fb-container container--small">
                <div class="form-el el--text">
                  <label for="">Team Name</label>
                  <input name="teamname" type="text" size={20} />
                </div>
                <div class="form-el el--text">
                  <label for="">Password</label>
                  <input name="password" type="password"/>
                </div>
              </fieldset>
              <div class="fb-choose-emblem">
                <h6>Choose an Emblem</h6>
                <div class="emblem-carousel">{$this->renderLogosSelection()}</div>
              </div>
              <div class="form-el--actions fb-container container--small">
                <p><button id="register_button" class="fb-cta cta--yellow" type="button" onclick="registerTeam()">Sign Up</button></p>
              </div>
            </form>
          </div>
        </main>;
      } else {
        return
          <div class="fb-row-container full-height fb-scroll">
            <main role="main" class="fb-main page--game-status row-fluid no-shrink center-vertically fb-img-glitch">
              <div class="fb-container fb-centered-main">
                <h3 class="title-lead">Team Registration</h3>
                <h1 class="fb-glitch" data-text="Not Available">Not Available</h1>
                <form class="fb-form inner-container">
                  <p>Team Registration will be open soon, stay tuned!</p>
                  <div class="form-el--actions">
                    <a href="/index.php?page=registration" class="fb-cta cta--yellow">Try Again</a>
                  </div>
                </form>
              </div>
            </main>
          </div>;
      }
  }

  public function renderLoginContent(): :xhp {
    $conf = new Configuration();
    if ($conf->get('login') === '1') {
      $teams = new Teams();
      $select = <select name="team_id" />;
      $select->appendChild(<option value="0">Select</option>);
      foreach ($teams->all_active_teams() as $team) {
        $select->appendChild(<option value={$team['id']}>{$team['name']}</option>);
      }
      return
        <main role="main" class="fb-main page--login full-height fb-scroll">
          <header class="fb-section-header fb-container">
            <h1 class="fb-glitch" data-text="Team Login">Team Login</h1>
            <p class="inner-container">Please login here. If you have not registered, you may do so by clicking "Sign Up" below. </p>
          </header>
          <div class="fb-login">
            <form class="fb-form">
              <input type="hidden" name="action" value="login_team"/>
              <fieldset class="form-set fb-container container--small">
                <div class="form-el el--text">
                  <label for="">Team Name</label>
                  {$select}
                </div>
                <div class="form-el el--text">
                  <label for="">Password</label>
                  <input name="password" type="password"/>
                </div>
              </fieldset>
              <div class="form-el--actions">
                <button id="login_button" class="fb-cta cta--yellow" type="button" onclick="loginTeam()">Login</button>
              </div>
              <div class="form-el--footer">
                <a href="/index.php?page=registration">Sign Up</a>
              </div>
            </form>
          </div>
        </main>;
      } else {
        return
          <div class="fb-row-container full-height fb-scroll">
            <main role="main" class="fb-main page--game-status row-fluid no-shrink center-vertically fb-img-glitch">
              <div class="fb-container fb-centered-main">
                <h3 class="title-lead">Team Login</h3>
                <h1 class="fb-glitch" data-text="Not Available">Not Available</h1>
                <form class="fb-form inner-container">
                  <p>Team Login will be open soon, stay tuned!</p>
                  <div class="form-el--actions">
                    <a href="/index.php?page=login" class="fb-cta cta--yellow">Try Again</a>
                  </div>
                </form>
              </div>
            </main>
          </div>;
      }
  }

  public function renderErrorPage(): :xhp {
    return
      <main role="main" class="fb-main page--login full-height fb-scroll">
        <header class="fb-section-header fb-container">
          <h1 class="fb-glitch" data-text="ERROR">ERROR</h1>
        </header>
        <div class="fb-actionable">
          <h1>¯\_(ツ)_/¯</h1>
          <a href="/index.php" class="fb-cta cta--yellow">Start Over</a>
        </div>
      </main>;
  }

  public function renderMobilePage(): :xhp {
    return
      <div class="fb-row-container full-height page--mobile">
        <main role="main" class="fb-main row-fluid center-vertically fb-img-glitch">
          <div class="fb-container fb-centered-main">
            <h1 class="fb-glitch" data-text="Window is too small">Window is too small</h1>
            <p>For the best CTF experience, please make window size bigger.</p>
            <p>Thank you.</p>
          </div>
        </main>
        <div class="row-fixed branding-el">
          <svg class="icon icon--social-facebook">
            <use xlink:href="#icon--social-facebook"/>

          </svg>
          <span class="has-icon"> Powered By Facebook</span>
        </div>
      </div>;
  }

  public function renderMainNav(): :xhp {
    sess_start();
    if (sess_active()) {
      $session_nav =
        <ul class="nav-right">
          <li></li>
          <li><a href="/game.php" data-active="gameboard">Gameboard</a></li>
          <li></li>
        </ul>;
    } else {
      $session_nav =
        <ul class="nav-right">
          <li><a href="/index.php?page=registration" data-active="registration">Registration</a></li>
          <li></li>
          <li><a href="/index.php?page=login" data-active="login">Login</a></li>
        </ul>;
    }
    return
      <nav class="fb-main-nav fb-navigation">
        <ul class="nav-left">
          <li><a href="/index.php?page=countdown" data-active="countdown">Play CTF</a></li>
          <li></li>
          <li><a href="/index.php?page=rules" data-active="rules">Rules</a></li>
        </ul>
        <div class="branding">
          <a href="/">
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
        {$session_nav}
      </nav>;
  }

  public function renderPage(string $page): :xhp {
    switch ($page) {
      case 'main':
      return $this->renderMainContent();
      break;
    case 'error':
      return $this->renderErrorPage();
      break;
    case 'mobile':
      return $this->renderMobilePage();
      break;
    case 'login':
      return $this->renderLoginContent();
      break;
    case 'registration':
      return $this->renderRegistrationContent();
      break;
    case 'rules':
      return $this->renderRulesContent();
      break;
    case 'countdown':
      return $this->renderCountdownContent();
      break;
    default:
      return $this->renderMainContent();
      break;
    }
  }

  public function renderBody(string $page): :xhp {
    return
      <body data-section="pages">
        <div class="fb-sprite" id="fb-svg-sprite"></div>
        <div class="fb-viewport">
          <div id="fb-main-nav">{$this->renderMainNav()}</div>
          <div id="fb-main-content" class="fb-page">{$this->renderPage($page)}</div>
        </div>
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
  'countdown',
  'rules',
  'registration',
  'login',
  'error',
  'mobile',
);

$request = new Request($filters, $actions, $pages);
$request->processRequest();

echo $indexpage->render('Facebook CTF', $request->page);