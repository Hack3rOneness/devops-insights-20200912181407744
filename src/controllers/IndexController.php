<?hh // strict

class IndexController extends Controller {
  <<__Override>>
  protected function getTitle(): string {
    return 'Facebook CTF';
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
        'action'      => array(
          'filter'      => FILTER_VALIDATE_REGEXP,
          'options'     => array(
            'regexp'      => '/^[\w-]+$/'
          ),
        )
      )
    );
  }

  <<__Override>>
  protected function getPages(): array<string> {
    return array(
      'main',
      'countdown',
      'rules',
      'registration',
      'login',
      'error',
      'mobile',
      'game',
      'admin'
    );
  }

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

  public async function genRenderCountdownContent(): Awaitable<:xhp> {
    SessionUtils::sessionStart();
    if (SessionUtils::sessionActive()) {
      $play_nav =
        <form class="fb-form inner-container">
          <p>Get ready for the CTF to start and access the gameboard now!</p>
          <div class="form-el--actions">
            <a href="/index.php?p=game" class="fb-cta cta--yellow">Gameboard</a>
          </div>
        </form>;
    } else {
      $registration = await Configuration::gen('registration');
      if ($registration->getValue() === '1') {
        $registration_button = <a style="margin-left: 1em;" href="/index.php?page=registration" class="fb-cta cta--yellow">Register Team</a>;
      } else {
        $registration_button = <a></a>;
      }
      $play_nav =
        <form class="fb-form inner-container">
          <p>Get ready for the CTF to start and register your team now!</p>
          <div class="form-el--actions">
            {$registration_button}
            <a style="margin-left: 1em;" href="/index.php?page=login" class="fb-cta cta--yellow">Login</a>
          </div>
        </form>;
    }
    $next_game = await Configuration::gen('next_game');
    $next_game = $next_game->getValue();
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
              Following actions are prohibited, unless explicitly told otherwise by event Admins.
            </p>
          </header>
          <div class="fb-rules">
            <section>
              <header class="rule-section-header">
                <h3>Rule 1</h3>
                <h6>Cooperation</h6>
              </header>
              <div class="rule-main">
                <p>No cooperation between teams with independent accounts. Sharing of keys or providing revealing hints to other teams is cheating, don’t do it.
                </p>
                <p></p>
              </div>
            </section>
            <section>
              <header class="rule-section-header">
                <h3>Rule 2</h3>
                <h6>Attacking Scoreboard</h6>
              </header>
              <div class="rule-main">
                <p>No attacking the competition infrastructure. If bugs or vulns are found, please alert the competition organizers immediately.
                </p>
                <p></p>
              </div>
            </section>
            <section>
              <header class="rule-section-header">
                <h3>Rule 3</h3>
                <h6>Sabotage</h6>
              </header>
              <div class="rule-main">
                <p>Absolutely no sabotaging of other competing teams, or in any way hindering their independent progress.
                </p>
                <p></p>
              </div>
            </section>
            <section>
              <header class="rule-section-header">
                <h3>Rule 4</h3>
                <h6>Bruteforcing</h6>
              </header>
              <div class="rule-main">
                <p>No brute forcing of challenge flag/ keys against the scoring site.
                </p>
                <p></p>
              </div>
            </section>
            <section>
              <header class="rule-section-header">
                <h3>Rule 5</h3>
                <h6>Denial Of Service</h6>
              </header>
              <div class="rule-main">
                <p>DoSing the CTF platform or any of the challenges is forbidden.
                </p>
                <p></p>
              </div>
            </section>
            <section>
              <header class="rule-section-header">
                <h3>Legal</h3>
                <h6>Disclaimer</h6>
              </header>
              <div class="rule-main">
                <p>By participating in the contest, you agree to release Facebook and its employees, and the hosting organization from any and all liability, claims or actions of any kind whatsoever for injuries, damages or losses to persons and property which may be sustained in connection with the contest. You acknowledge and agree that Facebook et al is not responsible for technical, hardware or software failures, or other errors or problems which may occur in connection with the contest.
                </p>
              </div>
            </section>
            <p>If you have any questions about what is or is not allowed, please ask an organizer.</p>
            <p></p>
            <p>Have fun!</p>
            <p></p>
          </div>
        </main>
      </div>;
  }

  public async function genRenderLogosSelection(): Awaitable<:xhp> {
    return <emblem-carousel />;
  }

  public async function genRenderRegistrationNames(): Awaitable<:xhp> {
    $registration_players = await Configuration::gen('registration_players');
    $players = intval($registration_players->getValue());
    $names_ul = <ul></ul>;

    for ($i=1; $i<=$players; $i++) {
      $name_ = 'registration_name_' . $i;
      $email_ = 'registration_email_' . $i;
      $names_ul->appendChild(
        <li class="fb-column-container">
          <div class="col col-2-4 form-el el--text">
            <label for="">Name</label>
            <input class="registration-name" name={$name_} type="text"/>
          </div>
          <div class="col col-2-4 form-el el--text">
            <label for="">Email</label>
            <input class="registration-email" name={$email_} type="email"/>
          </div>
        </li>
      );
    }

    $registration_type = await Configuration::gen('registration_type');
    if ($registration_type->getValue() === '2') {
      $token_field =
        <div class="form-el el--text">
          <label for="">Token</label>
          <input autocomplete="off" name="token" type="text"/>
        </div>;
    } else {
      $token_field = <div></div>;
    }

    $logos_section = await $this->genRenderLogosSelection();
    return
      <main role="main" class="fb-main page--team-registration full-height fb-scroll">
      <header class="fb-section-header fb-container">
        <h1 class="fb-glitch" data-text="Team Registration">Team Registration</h1>
        <p class="inner-container">
          Register to play Capture The Flag here. Once you have registered, you will be logged in
        </p>
      </header>
      <div class="fb-registration">
        <form class="fb-form">
          <input type="hidden" name="action" value="register_names"/>
          <fieldset class="form-set multiple-registration-list">
            {$names_ul}
          </fieldset>
          <br/><br/>
          <fieldset class="form-set fb-container container--small">
            <div class="form-el el--text">
              <label for="">Team Name</label>
              <input autocomplete="off" name="teamname" type="text" maxlength={20}/>
            </div>
            <div class="form-el el--text">
              <label for="">Password</label>
              <input autocomplete="off" name="password" type="password"/>
            </div>
            {$token_field}
          </fieldset>
          <div class="fb-choose-emblem">
            <h6>Choose an Emblem</h6>
            <div class="emblem-carousel">{$logos_section}</div>
          </div>
          <div class="form-el--actions fb-container container--small">
            <p><button id="register_button" class="fb-cta cta--yellow" type="button">Sign Up</button></p>
          </div>
        </form>
      </div>
    </main>;
  }

  public async function genRenderRegistrationNoNames(): Awaitable<:xhp> {
    $registration_type = await Configuration::gen('registration_type');
    if ($registration_type->getValue() === '2') {
      $token_field =
        <div class="form-el el--text">
          <label for="">Token</label>
          <input autocomplete="off" name="token" type="text"/>
        </div>;
    } else {
      $token_field = <div></div>;
    }

    $logos_section = await $this->genRenderLogosSelection();
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
                <input autocomplete="off" name="teamname" type="text" maxlength={20}/>
              </div>
              <div class="form-el el--text">
                <label for="">Password</label>
                <input autocomplete="off" name="password" type="password"/>
              </div>
              {$token_field}
            </fieldset>
            <div class="fb-choose-emblem">
              <h6>Choose an Emblem</h6>
              <div class="emblem-carousel">{$logos_section}</div>
            </div>
            <div class="form-el--actions fb-container container--small">
              <p><button id="register_button" class="fb-cta cta--yellow" type="button">Sign Up</button></p>
            </div>
          </form>
        </div>
      </main>;
  }

  public async function genRenderRegistrationContent(): Awaitable<:xhp> {
    $registration = await Configuration::gen('registration');
    $registration_names = await Configuration::gen('registration_names');
    if ($registration->getValue() === '1') {
      if ($registration_names->getValue() === '1') {
        return await $this->genRenderRegistrationNames();
      } else {
        return await $this->genRenderRegistrationNoNames();
      }
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

  public async function genRenderLoginContent(): Awaitable<:xhp> {
    $login = await Configuration::gen('login');
    if ($login->getValue() === '1') {
      $login_team = <input autocomplete="off" name="team_name" type="text" maxlength={20}/>;
      $login_select = "off";
      $login_select_config = await Configuration::gen('login_select');
      if ($login_select_config->getValue() === '1') {
        $login_select = "on";
        $login_team = <select name="team_id" />;
        $login_team->appendChild(<option value="0">Select</option>);
        $all_active_teams = await Team::genAllActiveTeams();
        foreach ($all_active_teams as $team) {
          error_log('Getting ' . $team->getName());
          $login_team->appendChild(<option value={strval($team->getId())}>{$team->getName()}</option>);
        }
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
              <input type="hidden" name="login_select" value={$login_select}/>
              <fieldset class="form-set fb-container container--small">
                <div class="form-el el--text">
                  <label for="">Team Name</label>
                  {$login_team}
                </div>
                <div class="form-el el--text">
                  <label for="">Password</label>
                  <input autocomplete="off" name="password" type="password"/>
                </div>
              </fieldset>
              <div class="form-el--actions">
                <button id="login_button" class="fb-cta cta--yellow" type="button">Login</button>
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
        <div class="row-fixed">
          <fbbranding />
        </div>
      </div>;
  }

  public function renderMainNav(): :xhp {
    SessionUtils::sessionStart();
    if (SessionUtils::sessionActive()) {
      $right_nav =
        <ul class="nav-right">
          <li><a href="/index.php?p=logout" data-active="logout">Logout</a></li>
          <li></li>
          <li><a href="/index.php?p=game" data-active="gameboard">Gameboard</a></li>
        </ul>;
    } else {
      $right_nav =
        <ul class="nav-right">
          <li><a href="/index.php?page=registration" data-active="registration">Registration</a></li>
          <li></li>
          <li><a href="/index.php?page=login" data-active="login">Login</a></li>
        </ul>;
    }
    $left_nav =
      <ul class="nav-left">
        <li><a href="/index.php?page=countdown" data-active="countdown">Play CTF</a></li>
        <li></li>
        <li><a href="/index.php?page=rules" data-active="rules">Rules</a></li>
      </ul>;

    return
      <nav class="fb-main-nav fb-navigation">
        {$left_nav}
        <div class="branding">
          <a href="/">
            <div class="branding-rules">
              <fbbranding />
            </div>
          </a>
        </div>
        {$right_nav}
      </nav>;
  }

  public async function genRenderPage(string $page): Awaitable<:xhp> {
    switch ($page) {
    case 'main':
      return $this->renderMainContent();
    case 'error':
      return $this->renderErrorPage();
    case 'mobile':
      return $this->renderMobilePage();
    case 'login':
      return await $this->genRenderLoginContent();
    case 'registration':
      return await $this->genRenderRegistrationContent();
    case 'rules':
      return $this->renderRulesContent();
    case 'countdown':
      return await $this->genRenderCountdownContent();
    case 'game':
      throw new GameRedirectException();
    case 'admin':
      throw new AdminRedirectException();
    default:
      return $this->renderMainContent();
    }
  }

  <<__Override>>
  public async function genRenderBody(string $page): Awaitable<:xhp> {
    $rendered_page = await $this->genRenderPage($page);
    return
      <body data-section="pages">
        <div class="fb-sprite" id="fb-svg-sprite"></div>
        <div class="fb-viewport">
          <div id="fb-main-nav">{$this->renderMainNav()}</div>
          <div id="fb-main-content" class="fb-page">{$rendered_page}</div>
        </div>
        <script type="text/javascript" src="static/dist/js/app.js"></script>
      </body>;
  }
}
