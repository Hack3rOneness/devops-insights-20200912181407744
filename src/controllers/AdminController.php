<?hh // strict

sess_start();
sess_enforce_login();
sess_enforce_admin();

class AdminController extends Controller {
  <<__Override>>
  protected function getTitle(): string {
    return 'Facebook CTF | Admin';
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
      'configuration',
      'controls',
      'announcements',
      'quiz',
      'flags',
      'bases',
      'categories',
      'countries',
      'teams',
      'logos',
      'sessions',
      'scoreboard',
      'logs',
    );
  }

  private function generateCountriesSelect(int $selected): :xhp {
    $select = <select name="entity_id" />;

    if ($selected === 0) {
      $select->appendChild(<option value="0" selected={true}>Auto</option>);
    } else {
      $country = Country::get(intval($selected));
      $select->appendChild(<option value={strval($country->getId())} selected={true}>{$country->getName()}</option>);
    }

    foreach (Country::allAvailableCountries() as $country) {
      $select->appendChild(<option value={strval($country->getId())}>{$country->getName()}</option>);
    }

    return $select;
  }

  private function generateLevelCategoriesSelect(int $selected): :xhp {
    $categories = Category::allCategories();
    $select = <select name="category_id" />;

    foreach ($categories as $category) {
      if ($category->getCategory() === 'Quiz') {
        continue;
      }

      if ($category->getId() === $selected) {
        $select->appendChild(<option value={strval($category->getId())} selected={true}>{$category->getCategory()}</option>);
      } else {
        $select->appendChild(<option value={strval($category->getId())}>{$category->getCategory()}</option>);
      }
    }

    return $select;
  }

  private function generateFilterCategoriesSelect(): :xhp {
    $categories = Category::allCategories();
    $select = <select name="category_filter" />;

    $select->appendChild(<option class="filter_option" value="all" selected={true}>All Categories</option>);
    foreach ($categories as $category) {
      if ($category->getCategory() === 'Quiz') {
        continue;
      }
      $select->appendChild(
        <option class="filter_option" value={$category->getCategory()}>
          {$category->getCategory()}
        </option>
      );
    }

    return $select;
  }

  private function registrationTypeSelect(): :xhp {
    $type = Configuration::get('registration_type')->getValue();
    $select = <select name="fb--conf--registration_type"></select>;
    $select->appendChild(<option class="fb--conf--registration_type" value="1" selected={($type === '1')}>Open</option>);
    $select->appendChild(<option class="fb--conf--registration_type" value="2" selected={($type === '2')}>Tokenized</option>);

    return $select;
  }

  private function configurationDurationSelect(): :xhp {
    $duration = intval(Configuration::get('game_duration')->getValue());
    $select = <select name="fb--conf--game_duration"></select>;

    for ($i=1; $i<=24; $i++) {
      $x = 60 * 60 * $i;
      $s = ($i > 1) ? 's' : '';
      $x_str = $i . ' Hour' . $s;
      $select->appendChild(<option class="fb--conf--game_duration" value={(string)$x} selected={($duration === $x)}>{$x_str}</option>);
    }

    return $select;
  }

  public function renderConfigurationTokens(): :xhp {
    $tokens_table = <table></table>;
    $control = new Control();
    foreach($control->all_tokens() as $token) {
      $tokens_table->appendChild(
        <tr>
          <td>{$token['token']}</td>
          <td>{$token['used']}</td>
        </tr>
      );
    }

    return
      <div class="radio-tab-content" data-tab="reg_tokens">
        <div class="admin-sections">
          <section class="admin-box">
            <header class="admin-box-header">
              <h3>Registration Tokens</h3>
            </header>
            <div class="fb-column-container">
              {$tokens_table}
            </div>
            <div class="admin-buttons admin-row">
              <div class="button-right">
                <button class="fb-cta cta--yellow" data-action="create-tokens">Create More Tokens</button>
              </div>
            </div>
          </section>
        </div>
      </div>;
  }

  public function renderConfigurationContent(): :xhp {
    $registration_on = (Configuration::get('registration')->getValue() === '1');
    $registration_off = (Configuration::get('registration')->getValue() === '0');
    $login_on = (Configuration::get('login')->getValue() === '1');
    $login_off = (Configuration::get('login')->getValue() === '0');
    $login_select_on = (Configuration::get('login_select')->getValue() === '1');
    $login_select_off = (Configuration::get('login_select')->getValue() === '0');
    $strong_passwords_on = (Configuration::get('login_strongpasswords')->getValue() === '1');
    $strong_passwords_off = (Configuration::get('login_strongpasswords')->getValue() === '0');
    $registration_names_on = (Configuration::get('registration_names')->getValue() === '1');
    $registration_names_off = (Configuration::get('registration_names')->getValue() === '0');
    $scoring_on = (Configuration::get('scoring')->getValue() === '1');
    $scoring_off = (Configuration::get('scoring')->getValue() === '0');
    $gameboard_on = (Configuration::get('gameboard')->getValue() === '1');
    $gameboard_off = (Configuration::get('gameboard')->getValue() === '0');
    $timer_on = (Configuration::get('timer')->getValue() === '1');
    $timer_off = (Configuration::get('timer')->getValue() === '0');

    if (Configuration::get('start_ts')->getValue() === '0') {
      $start_ts = 'Not started yet';
      $end_ts = 'Not started yet';
    } else {
      $start_ts = date("H:i:s D m/d/Y", Configuration::get('start_ts')->getValue());
      $end_ts = date("H:i:s D m/d/Y", Configuration::get('end_ts')->getValue());
    }

    if (Configuration::get('registration_type')->getValue() === '2') { // Registration is tokenized
      $registration_tokens = $this->renderConfigurationTokens();
      $tabs_conf =
        <div class="radio-tabs">
          <input type="radio" value="reg_conf" name="fb--admin--tabs--conf" id="fb--admin--tabs--conf--conf" checked={true}/>
          <label for="fb--admin--tabs--conf--conf">Configuration</label>
          <input type="radio" value="reg_tokens" name="fb--admin--tabs--conf" id="fb--admin--tabs--conf--tokens"/>
          <label id="fb--admin--tabs--conf--tokens-label" for="fb--admin--tabs--conf--tokens">Tokens</label>
        </div>;
    } else {
      $tabs_conf = <div class="radio-tabs"></div>;
      $registration_tokens = <div></div>;
    }

    return
      <div>
        <header class="admin-page-header">
          <h3>Game Configuration</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$tabs_conf}
        <div class="tab-content-container">
          <div class="radio-tab-content active" data-tab="reg_conf">
            <div class="admin-sections">
              <section class="admin-box">
                <header class="admin-box-header">
                  <h3>Registration</h3>
                  <div class="admin-section-toggle radio-inline">
                    <input type="radio" name="fb--conf--registration" id="fb--conf--registration--on" checked={$registration_on}/>
                    <label for="fb--conf--registration--on">On</label>
                    <input type="radio" name="fb--conf--registration" id="fb--conf--registration--off" checked={$registration_off}/>
                    <label for="fb--conf--registration--off">Off</label>
                  </div>
                </header>
                <div class="fb-column-container">
                  <div class="col col-pad col-1-4">
                    <div class="form-el el--block-label">
                      <label>Player Names</label>
                      <div class="admin-section-toggle radio-inline">
                        <input type="radio" name="fb--conf--registration_names" id="fb--conf--registration_names--on" checked={$registration_names_on}/>
                        <label for="fb--conf--registration_names--on">On</label>
                        <input type="radio" name="fb--conf--registration_names" id="fb--conf--registration_names--off" checked={$registration_names_off}/>
                        <label for="fb--conf--registration_names--off">Off</label>
                      </div>
                    </div>
                  </div>
                  <div class="col col-pad col-2-4">
                    <div class="form-el el--block-label">
                      <label for="">Players Per Team</label>
                      <input type="number" value={Configuration::get('registration_players')->getValue()} name="fb--conf--registration_players" max="12" min="1"/>
                    </div>
                  </div>
                  <div class="col col-pad col-3-4">
                    <div class="form-el el--block-label">
                      <label>Registration Type</label>
                      {$this->registrationTypeSelect()}
                    </div>
                  </div>
                </div>
              </section>
              <section class="admin-box">
                <header class="admin-box-header">
                  <h3>Login</h3>
                  <div class="admin-section-toggle radio-inline">
                    <input type="radio" name="fb--conf--login" id="fb--conf--login--on" checked={$login_on}/>
                    <label for="fb--conf--login--on">On</label>
                    <input type="radio" name="fb--conf--login" id="fb--conf--login--off"checked={$login_off}/>
                    <label for="fb--conf--login--off">Off</label>
                  </div>
                </header>
                <div class="fb-column-container">
                  <div class="col col-pad col-1-2">
                    <div class="form-el el--block-label">
                      <label>Strong Passwords</label>
                      <div class="admin-section-toggle radio-inline">
                        <input type="radio" name="fb--conf--login_strongpasswords" id="fb--conf--login_strongpasswords--on" checked={$strong_passwords_on}/>
                        <label for="fb--conf--login_strongpasswords--on">On</label>
                        <input type="radio" name="fb--conf--login_strongpasswords" id="fb--conf--login_strongpasswords--off" checked={$strong_passwords_off}/>
                        <label for="fb--conf--login_strongpasswords--off">Off</label>
                      </div>
                    </div>
                  </div>
                  <div class="col col-pad col-2-2">
                    <div class="form-el el--block-label">
                      <label>Team Selection</label>
                      <div class="admin-section-toggle radio-inline">
                        <input type="radio" name="fb--conf--login_select" id="fb--conf--login_select--on" checked={$login_select_on}/>
                        <label for="fb--conf--login_select--on">On</label>
                        <input type="radio" name="fb--conf--login_select" id="fb--conf--login_select--off" checked={$login_select_off}/>
                        <label for="fb--conf--login_select--off">Off</label>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
              <section class="admin-box">
                <header class="admin-box-header">
                  <h3>Game</h3>
                </header>
                <div class="fb-column-container">
                  <div class="col col-pad col-1-4">
                    <div class="form-el el--block-label">
                      <label>Scoring</label>
                      <div class="admin-section-toggle radio-inline">
                        <input type="radio" name="fb--conf--scoring" id="fb--conf--scoring--on" checked={$scoring_on}/>
                        <label for="fb--conf--scoring--on">On</label>
                        <input type="radio" name="fb--conf--scoring" id="fb--conf--scoring--off" checked={$scoring_off}/>
                        <label for="fb--conf--scoring--off">Off</label>
                      </div>
                    </div>
                    <div class="form-el el--block-label">
                      <label>Progressive Cycle (s)</label>
                      <input type="number" value={Configuration::get('progressive_cycle')->getValue()} name="fb--conf--progressive_cycle"/>
                    </div>
                  </div>
                  <div class="col col-pad col-2-4">
                    <div class="form-el el--block-label">
                      <label>Refresh Gameboard</label>
                      <div class="admin-section-toggle radio-inline">
                        <input type="radio" name="fb--conf--gameboard" id="fb--conf--gameboard--on" checked={$gameboard_on}/>
                        <label for="fb--conf--gameboard--on">On</label>
                        <input type="radio" name="fb--conf--gameboard" id="fb--conf--gameboard--off"checked={$gameboard_off}/>
                        <label for="fb--conf--gameboard--off">Off</label>
                      </div>
                    </div>
                    <div class="form-el el--block-label">
                      <label>Default Bonus</label>
                      <input type="number" value={Configuration::get('default_bonus')->getValue()} name="fb--conf--default_bonus"/>
                    </div>
                  </div>
                  <div class="col col-pad col-3-4">
                    <div class="form-el el--block-label">
                      <label>Default Bonus Dec</label>
                      <input type="number" value={Configuration::get('default_bonusdec')->getValue()} name="fb--conf--default_bonusdec"/>
                    </div>
                  </div>
                  <div class="col col-pad col-4-4">
                    <div class="form-el el--block-label">

                    </div>
                  </div>
                </div>
              </section>
              <section class="admin-box">
                <header class="admin-box-header">
                  <h3>Timer</h3>
                  <div class="admin-section-toggle radio-inline">
                    <input type="radio" name="fb--conf--timer" id="fb--conf--timer--on" checked={$timer_on}/>
                    <label for="fb--conf--timer--on">On</label>
                    <input type="radio" name="fb--conf--timer" id="fb--conf--timer--off"checked={$timer_off}/>
                    <label for="fb--admin--timer--off">Off</label>
                  </div>
                </header>
                <div class="fb-column-container">
                  <div class="col col-pad col-1-4">
                    <div class="form-el el--block-label el--full-text">
                      <label for="">Server Time</label>
                      <input type="text" value={date("H:i:s D m/d/Y", time())} name="fb--conf--server_time" disabled={true}/>
                    </div>
                  </div>
                  <div class="col col-pad col-2-4">
                    <div class="form-el el--block-label el--full-text">
                      <label for="">Game Duration</label>
                      {$this->configurationDurationSelect()}
                    </div>
                  </div>
                  <div class="col col-pad col-2-4">
                    <div class="form-el el--block-label el--full-text">
                      <label for="">Begin Time</label>
                      <input type="text" value={$start_ts} id="fb--conf--start_ts" disabled={true}/>
                    </div>
                  </div>
                  <div class="col col-pad col-3-4">
                    <div class="form-el el--block-label el--full-text">
                      <label for="">Expected End Time</label>
                      <input type="text" value={$end_ts} id="fb--conf--end_ts" disabled={true}/>
                    </div>
                  </div>
                </div>
              </section>
            </div>
          </div>
          {$registration_tokens}
        </div>
      </div>;
  }

  public function renderAnnouncementsContent(): :xhp {
    $control = new Control();
    $announcements = $control->all_announcements();
    $announcements_div = <div></div>;
    if ($announcements) {
      foreach ($announcements as $announcement) {
        $announcements_div->appendChild(
          <section class="admin-box">
            <form class="announcements_form">
              <input type="hidden" name="announcement_id" value={$announcement['id']}/>
              <header class="countries-management-header">
                <h6>{$announcement['ts']}</h6>
                <a class="highlighted--red" href="#" data-action="delete">DELETE</a>
              </header>
              <div class="fb-column-container">
                <div class="col col-pad">
                  <div class="selected-logo">
                    <span class="logo-name">{$announcement['announcement']}</span>
                  </div>
                </div>
              </div>
            </form>
          </section>
        );
      }
    } else {
      $announcements_div->appendChild(
        <section class="admin-box">
          <div class="fb-column-container">
            <div class="col col-pad">
              <div class="selected-logo-text">
                <span class="logo-name">No Announcements</span>
              </div>
            </div>
          </div>
        </section>
      );
    }
    return
      <div>
        <header class="admin-page-header">
          <h3>Game Controls</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        <div class="admin-sections">
          <section class="admin-box">
            <header class="admin-box-header">
              <h3>Announcements</h3>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-3-4">
                <div class="form-el el--block-label el--full-text">
                  <input type="text" name="new_announcement" placeholder="Write New Announcement here" value=""/>
                </div>
              </div>
              <div class="col col-pad col-1-4">
                <div class="form-el el--block-label el--full-text">
                  <div class="admin-buttons">
                    <button class="fb-cta cta--yellow" data-action="create-announcement">Create</button>
                  </div>
                </div>
              </div>
            </div>
          </section>
          {$announcements_div}
        </div>
      </div>;
  }

  public function renderControlsContent(): :xhp {
    return
      <div>
        <header class="admin-page-header">
          <h3>Game Controls</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        <div class="admin-sections">
          <section class="admin-box">
            <header class="admin-box-header">
              <h3>General Control</h3>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-1-4">
                <div class="form-el el--block-label el--full-text">
                  <div class="admin-buttons">
                    <button class="fb-cta cta--yellow" data-action="backup-db">Back Up Database</button>
                  </div>
                </div>
              </div>
            </div>
          </section>
          <section class="admin-box">
            <header class="admin-box-header">
              <h3>Teams Control</h3>
            </header>
            <div class="fb-column-container">
            </div>
          </section>
          <section class="admin-box">
            <header class="admin-box-header">
              <h3>Levels Control</h3>
            </header>
            <div class="fb-column-container">
            </div>
          </section>
        </div>
      </div>;
  }

  public function renderQuizContent(): :xhp {
    $adminsections =
      <div class="admin-sections">
        <section id="new-element" class="admin-box completely-hidden">
          <form class="level_form quiz_form">
            <input type="hidden" name="level_type" value="quiz"/>
            <header class="admin-box-header">
              <h3>New Quiz Level</h3>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-1-2">
                <div class="form-el el--block-label el--full-text">
                  <label>Title</label>
                  <input name="title" type="text" placeholder="Level title"/>
                </div>
                <div class="form-el el--block-label el--full-text">
                  <label>Question</label>
                  <textarea name="question" placeholder="Quiz question" rows={4} ></textarea>
                </div>
                <div class="form-el el--block-label el--full-text">
                  <label for="">Country</label>
                  {$this->generateCountriesSelect(0)}
                </div>
              </div>
              <div class="col col-pad col-1-2">
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-2-3 el--block-label el--full-text">
                    <label>Answer</label>
                    <input name="answer" type="text"/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Points</label>
                    <input name="points" type="text"/>
                  </div>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-2-3 el--block-label el--full-text">
                    <label>Hint</label>
                    <input name="hint" type="text"/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Hint Penalty</label>
                    <input name="penalty" type="text"/>
                  </div>
                </div>
              </div>
            </div>
          <div class="admin-buttons admin-row">
            <div class="button-right">
              <a href="#" class="admin--edit" data-action="edit">EDIT</a>
              <button class="fb-cta cta--red" data-action="delete">Delete</button>
              <button class="fb-cta cta--yellow" data-action="create">Create</button>
            </div>
          </div>
        </form>
      </section>
      <section id="new-element" class="admin-box">
        <header class="admin-box-header">
          <h3>All Quiz Levels</h3>
          <form class="all_quiz_form">
            <div class="admin-section-toggle radio-inline col">
              <input type="radio" name="fb--levels--all_quiz" id="fb--levels--all_quiz--on"/>
              <label for="fb--levels--all_quiz--on">On</label>
              <input type="radio" name="fb--levels--all_quiz" id="fb--levels--all_quiz--off"/>
              <label for="fb--levels--all_quiz--off">Off</label>
            </div>
          </form>
        </header>
        <header class="admin-box-header">
          <h3>Filter By:</h3>
          <div class="form-el fb-column-container col-gutters">
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
              <select name="status_filter">
                <option class="filter_option" value="all">All Status</option>
                <option class="filter_option" value="Enabled">Enabled</option>
                <option class="filter_option" value="Disabled">Disabled</option>
              </select>
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
          </div>
        </header>
      </section>
    </div>;

    $c = 1;
    foreach (Level::allQuizLevels() as $quiz) {
      error_log($quiz->getTitle());
      $quiz_active_on = ($quiz->getActive());
      $quiz_active_off = (!$quiz->getActive());

      $quiz_status_name = 'fb--levels--level-'.strval($quiz->getId()).'-status';
      $quiz_status_on_id = 'fb--levels--level-'.strval($quiz->getId()).'-status--on';
      $quiz_status_off_id = 'fb--levels--level-'.strval($quiz->getId()).'-status--off';

      $quiz_id = 'quiz_id'.strval($quiz->getId());

      $adminsections->appendChild(
        <section class="admin-box section-locked">
          <form class="level_form quiz_form" name={$quiz_id}>
            <input type="hidden" name="level_type" value="quiz"/>
            <input type="hidden" name="level_id" value={strval($quiz->getId())}/>
            <header class="admin-box-header">
              <h3>Quiz Level {$c}</h3>
              <div class="admin-section-toggle radio-inline">
                <input type="radio" name={$quiz_status_name} id={$quiz_status_on_id} checked={$quiz_active_on}/>
                <label for={$quiz_status_on_id}>On</label>
                <input type="radio" name={$quiz_status_name} id={$quiz_status_off_id} checked={$quiz_active_off}/>
                <label for={$quiz_status_off_id}>Off</label>
              </div>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-1-2">
                <div class="form-el el--block-label el--full-text">
                  <label>Title</label>
                  <input name="title" type="text" value={$quiz->getTitle()} disabled={true}/>
                </div>
                <div class="form-el el--block-label el--full-text">
                  <label>Question</label>
                  <textarea name="question" rows={6} disabled={true}>{$quiz->getDescription()}</textarea>
                </div>
                <div class="form-el el--block-label el--full-text">
                  <label for="">Country</label>
                  {$this->generateCountriesSelect($quiz->getEntityId())}
                </div>
              </div>
              <div class="col col-pad col-1-2">
                <div class="form-el el--block-label el--full-text">
                  <label>Answer</label>
                  <input name="answer" type="text" value={$quiz->getFlag()} disabled={true}/>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Points</label>
                    <input name="points" type="text" value={strval($quiz->getPoints())} disabled={true}/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Bonus</label>
                    <input name="bonus" type="text" value={strval($quiz->getBonus())} disabled={true}/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>-Dec</label>
                    <input name="bonus_dec" type="text" value={strval($quiz->getBonusDec())} disabled={true}/>
                  </div>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-2-3 el--block-label el--full-text">
                    <label>Hint</label>
                    <input name="hint" type="text" value={$quiz->getHint()} disabled={true}/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Hint Penalty</label>
                    <input name="penalty" type="text" value={strval($quiz->getPenalty())} disabled={true}/>
                  </div>
                </div>
              </div>
            </div>
            <div class="admin-buttons admin-row">
              <div class="button-right">
                <a href="#" class="admin--edit" data-action="edit">EDIT</a>
                <button class="fb-cta cta--red" data-action="delete">Delete</button>
                <button class="fb-cta cta--yellow" data-action="save-no-validation">Save</button>
              </div>
            </div>
          </form>
        </section>
      );
      $c++;
    }

    return
      <div>
        <header class="admin-page-header">
          <h3>Quiz Management</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$adminsections}
        <div class="admin-buttons">
          <button class="fb-cta" data-action="add-new">Add Quiz Level</button>
        </div>
      </div>;
  }

  public function renderFlagsContent(): :xhp {
    $adminsections =
      <div class="admin-sections">
        <section id="new-element" class="admin-box completely-hidden">
          <form class="level_form flag_form">
            <input type="hidden" name="level_type" value="flag"/>
            <header class="admin-box-header">
              <h3>New Flag Level</h3>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-1-2">
                <div class="form-el el--block-label el--full-text">
                  <label>Title</label>
                  <input name="title" type="text" placeholder="Level title"/>
                </div>
                <div class="form-el el--block-label el--full-text">
                  <label>Description</label>
                  <textarea name="description" placeholder="Level description" rows={4}></textarea>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-1-2 el--block-label el--full-text">
                    <label for="">Country</label>
                    {$this->generateCountriesSelect(0)}
                  </div>
              <div class="col col-1-2 el--block-label el--full-text">
                <label for="">Category</label>
                {$this->generateLevelCategoriesSelect(0)}
              </div>
            </div>
          </div>
          <div class="col col-pad col-1-2">
            <div class="form-el fb-column-container col-gutters">
              <div class="col col-2-3 el--block-label el--full-text">
                <label>Flag</label>
                <input name="flag" type="text"/>
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Points</label>
                <input name="points" type="text"/>
              </div>
            </div>
            <div class="form-el fb-column-container col-gutters">
              <div class="col col-2-3 el--block-label el--full-text">
                <label>Hint</label>
                <input name="hint" type="text"/>
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Hint Penalty</label>
                <input name="penalty" type="text"/>
              </div>
            </div>
          </div>
        </div>
        <div class="admin-buttons admin-row">
          <div class="button-right">
            <a href="#" class="admin--edit" data-action="edit">EDIT</a>
            <button class="fb-cta cta--red" data-action="delete">Delete</button>
            <button class="fb-cta cta--yellow" data-action="create">Create</button>
          </div>
        </div>
        </form>
      </section>
      <section id="new-element" class="admin-box">
        <header class="admin-box-header">
          <h3>All Flag Levels</h3>
          <form class="all_flag_form">
            <div class="admin-section-toggle radio-inline col">
              <input type="radio" name="fb--levels--all_flag" id="fb--levels--all_flag--on"/>
              <label for="fb--levels--all_flag--on">On</label>
              <input type="radio" name="fb--levels--all_flag" id="fb--levels--all_flag--off"/>
              <label for="fb--levels--all_flag--off">Off</label>
            </div>
          </form>
        </header>
        <header class="admin-box-header">
          <h3>Filter By:</h3>
          <div class="form-el fb-column-container col-gutters">
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
              {$this->generateFilterCategoriesSelect()}
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
              <select name="status_filter">
                <option class="filter_option" value="all">All Status</option>
                <option class="filter_option" value="Enabled">Enabled</option>
                <option class="filter_option" value="Disabled">Disabled</option>
              </select>
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
          </div>
        </header>
      </section>
    </div>;

    $c = 1;
    foreach (Level::allFlagLevels() as $flag) {
      $flag_active_on = ($flag->getActive());
      $flag_active_off = (!$flag->getActive());

      $flag_status_name = 'fb--levels--level-'.strval($flag->getId()).'-status';
      $flag_status_on_id = 'fb--levels--level-'.strval($flag->getId()).'-status--on';
      $flag_status_off_id = 'fb--levels--level-'.strval($flag->getId()).'-status--off';

      $flag_id = 'flag_id'.strval($flag->getId());

      $attachments_div =
        <div class="attachments">
          <div class="new-attachment new-attachment-hidden fb-column-container completely-hidden">
            <div class="col col-pad col-1-3">
              <div class="form-el">
                <form class="attachment_form">
                  <input type="hidden" name="action" value="create_attachment"/>
                  <input type="hidden" name="level_id" value={strval($flag->getId())}/>
                  <div class="col el--block-label el--full-text">
                    <label>New Attachment:</label>
                    <input name="filename" type="text"/>
                    <input name="attachment_file" type="file"/>
                  </div>
                </form>
              </div>
            </div>
            <div class="admin-buttons col col-pad col-1-3">
              <div class="col el--block-label el--full-text">
                <button class="fb-cta cta--red" data-action="delete-new-attachment">X</button>
                <button class="fb-cta cta--yellow" data-action="create-attachment">Create</button>
              </div>
            </div>
          </div>
        </div>;

      if (Attachment::hasAttachments($flag->getId())) {
        $a_c = 1;
        foreach (Attachment::allAttachments($flag->getId()) as $attachment) {
          $attachments_div->appendChild(
            <div class="existing-attachment fb-column-container">
              <div class="col col-pad col-2-3">
                <div class="form-el">
                  <form class="attachment_form">
                    <input type="hidden" name="attachment_id" value={strval($attachment->getId())}/>
                    <div class="col el--block-label el--full-text">
                      <label>Attachment {$a_c}:</label>
                      <input name="filename" type="text" value={$attachment->getFilename()} disabled={true}/>
                      <a href={$attachment->getFilename()} target="_blank">Link</a>
                    </div>
                  </form>
                </div>
              </div>
              <div class="admin-buttons col col-pad col-1-3">
                <div class="col el--block-label el--full-text">
                  <button class="fb-cta cta--red" data-action="delete-attachment">X</button>
                </div>
              </div>
            </div>
          );
          $a_c++;
        }
      }

      $links_div =
        <div class="links">
          <div class="new-link new-link-hidden fb-column-container completely-hidden">
            <div class="col col-pad col-1-3">
              <div class="form-el">
                <form class="link_form">
                  <input type="hidden" name="action" value="create_link"/>
                  <input type="hidden" name="level_id" value={strval($flag->getId())}/>
                  <div class="col el--block-label el--full-text">
                    <label>New Link:</label>
                    <input name="link" type="text"/>
                  </div>
                </form>
              </div>
            </div>
            <div class="admin-buttons col col-pad col-1-3">
              <div class="col el--block-label el--full-text">
                <button class="fb-cta cta--red" data-action="delete-new-link">X</button>
                <button class="fb-cta cta--yellow" data-action="create-link">Create</button>
              </div>
            </div>
          </div>
        </div>;

      if (Link::hasLinks($flag->getId())) {
        $l_c = 1;
        foreach (Link::allLinks($flag->getId()) as $link) {
          $links_div->appendChild(
            <div class="existing-link fb-column-container">
              <div class="col col-pad col-2-3">
                <div class="form-el">
                  <form class="link_form">
                    <input type="hidden" name="link_id" value={strval($link->getId())}/>
                    <div class="col el--block-label el--full-text">
                      <label>Link {$l_c}:</label>
                      <input name="link" type="text" value={$link->getLink()} disabled={true}/>
                      <a href={$link->getLink()} target="_blank">Link</a>
                    </div>
                  </form>
                </div>
              </div>
              <div class="admin-buttons col col-pad col-1-3">
                <div class="col el--block-label el--full-text">
                  <button class="fb-cta cta--red" data-action="delete-link">X</button>
                </div>
              </div>
            </div>
          );
          $l_c++;
        }
      }

      $adminsections->appendChild(
        <section class="admin-box section-locked">
          <form class="level_form flag_form" name={$flag_id}>
            <input type="hidden" name="level_type" value="flag"/>
            <input type="hidden" name="level_id" value={strval($flag->getId())}/>
            <header class="admin-box-header">
              <h3>Flag Level {$c}</h3>
              <div class="admin-section-toggle radio-inline">
                <input type="radio" name={$flag_status_name} id={$flag_status_on_id} checked={$flag_active_on}/>
                <label for={$flag_status_on_id}>On</label>
                <input type="radio" name={$flag_status_name} id={$flag_status_off_id} checked={$flag_active_off}/>
                <label for={$flag_status_off_id}>Off</label>
              </div>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-1-2">
                <div class="form-el el--block-label el--full-text">
                  <label>Title</label>
                  <input name="title" type="text" value={$flag->getTitle()} disabled={true}/>
                </div>
                <div class="form-el el--block-label el--full-text">
                  <label>Description</label>
                  <textarea name="description" rows={6} disabled={true}>{$flag->getDescription()}</textarea>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-1-2 el--block-label el--full-text">
                    <label for="">Country</label>
                    {$this->generateCountriesSelect($flag->getEntityId())}
                  </div>
                  <div class="col col-1-2 el--block-label el--full-text">
                    <label for="">Categories</label>
                    {$this->generateLevelCategoriesSelect($flag->getCategoryId())}
                  </div>
                </div>
              </div>
              <div class="col col-pad col-1-2">
                <div class="form-el fb-column-container col-gutters">
                  <div class="col el--block-label el--full-text">
                    <label>Flag</label>
                    <input name="flag" type="text" value={$flag->getFlag()} disabled={true}/>
                  </div>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Points</label>
                    <input name="points" type="text" value={strval($flag->getPoints())} disabled={true}/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Bonus</label>
                    <input name="bonus" type="text" value={strval($flag->getBonus())} disabled={true}/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>-Dec</label>
                    <input name="bonus_dec" type="text" value={strval($flag->getBonusDec())} disabled={true}/>
                  </div>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-2-3 el--block-label el--full-text">
                    <label>Hint</label>
                    <input name="hint" type="text" value={$flag->getHint()} disabled={true}/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Hint Penalty</label>
                    <input name="penalty" type="text" value={strval($flag->getPenalty())} disabled={true}/>
                  </div>
                </div>
              </div>
            </div>
          </form>
          {$attachments_div}
          {$links_div}
          <div class="admin-buttons admin-row">
            <div class="button-right">
              <a href="#" class="admin--edit" data-action="edit">EDIT</a>
              <button class="fb-cta cta--red" data-action="delete">Delete</button>
              <button class="fb-cta cta--yellow" data-action="save-no-validation">Save</button>
            </div>
            <div class="button-left">
              <button class="fb-cta" data-action="add-attachment">+ Attachment</button>
              <button class="fb-cta" data-action="add-link">+ Link</button>
            </div>
          </div>
        </section>
      );
      $c++;
    }

    return
      <div>
        <header class="admin-page-header">
          <h3>Flags Management</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$adminsections}
        <div class="admin-buttons">
          <button class="fb-cta" data-action="add-new">Add Flag Level</button>
        </div>
      </div>;
  }

  public function renderBasesContent(): :xhp {
    $adminsections =
      <div class="admin-sections">
        <section id="new-element" class="admin-box completely-hidden">
          <form class="level_form base_form">
            <input type="hidden" name="level_type" value="base"/>
            <header class="admin-box-header">
              <h3>New Base Level</h3>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-1-2">
                <div class="form-el el--block-label el--full-text">
                  <label>Title</label>
                  <input name="title" type="text" placeholder="Level title"/>
                </div>
                <div class="form-el el--block-label el--full-text">
                  <label>Description</label>
                  <textarea name="description" placeholder="Level description" rows={4}></textarea>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-1-2 el--block-label el--full-text">
                    <label for="">Country</label>
                    {$this->generateCountriesSelect(0)}
                  </div>
                  <div class="col col-1-2 el--block-label el--full-text">
                    <label for="">Category</label>
                    {$this->generateLevelCategoriesSelect(0)}
                  </div>
                </div>
              </div>
              <div class="col col-pad col-1-2">
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-1-2 el--block-label el--full-text">
                    <label>Points</label>
                    <input name="points" type="text"/>
                  </div>
                  <div class="col col-1-2 el--block-label el--full-text">
                    <label>Bonus</label>
                    <input name="bonus" type="text"/>
                  </div>
                </div>
                <div class="form-el fb-column-container col-gutters">
                  <div class="col col-2-3 el--block-label el--full-text">
                    <label>Hint</label>
                  <input name="hint" type="text"/>
                  </div>
                  <div class="col col-1-3 el--block-label el--full-text">
                    <label>Hint Penalty</label>
                    <input name="penalty" type="text"/>
                  </div>
                </div>
              </div>
            </div>
            <div class="admin-buttons admin-row">
              <div class="button-right">
                <a href="#" class="admin--edit" data-action="edit">EDIT</a>
                <button class="fb-cta cta--red" data-action="delete">Delete</button>
                <button class="fb-cta cta--yellow" data-action="create">Create</button>
              </div>
            </div>
          </form>
        </section>
        <section id="new-element" class="admin-box">
          <header class="admin-box-header">
            <h3>All Base Levels</h3>
            <form class="all_base_form">
              <div class="admin-section-toggle radio-inline col">
                <input type="radio" name="fb--levels--all_base" id="fb--levels--all_base--on"/>
                <label for="fb--levels--all_base--on">On</label>
                <input type="radio" name="fb--levels--all_base" id="fb--levels--all_base--off"/>
                <label for="fb--levels--all_base--off">Off</label>
              </div>
            </form>
          </header>
          <header class="admin-box-header">
            <h3>Filter By:</h3>
            <div class="form-el fb-column-container col-gutters">
              <div class="col col-1-5 el--block-label el--full-text">
              </div>
              <div class="col col-1-5 el--block-label el--full-text">
                {$this->generateFilterCategoriesSelect()}
              </div>
              <div class="col col-1-5 el--block-label el--full-text">
              </div>
              <div class="col col-1-5 el--block-label el--full-text">
                <select name="status_filter">
                  <option class="filter_option" value="all">All Status</option>
                  <option class="filter_option" value="Enabled">Enabled</option>
                  <option class="filter_option" value="Disabled">Disabled</option>
                </select>
              </div>
              <div class="col col-1-5 el--block-label el--full-text">
              </div>
            </div>
          </header>
        </section>
      </div>;

    $c = 1;
    foreach (Level::allBaseLevels() as $base) {
      $base_active_on = ($base->getActive());
      $base_active_off = (!$base->getActive());

      $base_status_name = 'fb--levels--level-'.strval($base->getId()).'-status';
      $base_status_on_id = 'fb--levels--level-'.strval($base->getId()).'-status--on';
      $base_status_off_id = 'fb--levels--level-'.strval($base->getId()).'-status--off';

      $base_id = 'base_id'.strval($base->getId());

      $attachments_div =
        <div class="attachments">
          <div class="new-attachment new-attachment-hidden fb-column-container completely-hidden">
            <div class="col col-pad col-1-3">
              <div class="form-el">
                <form class="attachment_form">
                  <input type="hidden" name="action" value="create_attachment"/>
                  <input type="hidden" name="level_id" value={strval($base->getId())}/>
                  <div class="col el--block-label el--full-text">
                    <label>New Attachment:</label>
                    <input name="filename" type="text"/>
                    <input name="attachment_file" type="file"/>
                  </div>
                </form>
              </div>
            </div>
            <div class="admin-buttons col col-pad col-1-3">
              <div class="col el--block-label el--full-text">
                <button class="fb-cta cta--red" data-action="delete-new-attachment">X</button>
                <button class="fb-cta cta--yellow" data-action="create-attachment">Create</button>
              </div>
            </div>
          </div>
        </div>;

      if (Attachment::hasAttachments($base->getId())) {
        $a_c = 1;
        foreach (Attachment::allAttachments($base->getId()) as $attachment) {
          $attachments_div->appendChild(
            <div class="existing-attachment fb-column-container">
              <div class="col col-pad col-2-3">
                <div class="form-el">
                  <form class="attachment_form">
                    <input type="hidden" name="attachment_id" value={strval($attachment->getId())}/>
                    <div class="col el--block-label el--full-text">
                      <label>Attachment {$a_c}:</label>
                      <input name="filename" type="text" value={$attachment->getFilename()} disabled={true}/>
                      <a href={$attachment->getFilename()} target="_blank">Link</a>
                    </div>
                  </form>
                </div>
              </div>
              <div class="admin-buttons col col-pad col-1-3">
                <div class="col el--block-label el--full-text">
                  <button class="fb-cta cta--red" data-action="delete-attachment">X</button>
                </div>
              </div>
            </div>
          );
        }
        $a_c++;
      }

      $links_div =
        <div class="links">
          <div class="new-link new-link-hidden fb-column-container completely-hidden">
            <div class="col col-pad col-1-3">
              <div class="form-el">
                <form class="link_form">
                  <input type="hidden" name="action" value="create_link"/>
                  <input type="hidden" name="level_id" value={strval($base->getId())}/>
                  <div class="col el--block-label el--full-text">
                    <label>New Link:</label>
                    <input name="link" type="text"/>
                  </div>
                </form>
              </div>
            </div>
            <div class="admin-buttons col col-pad col-1-3">
              <div class="col el--block-label el--full-text">
                <button class="fb-cta cta--red" data-action="delete-new-link">X</button>
                <button class="fb-cta cta--yellow" data-action="create-link">Create</button>
              </div>
            </div>
          </div>
        </div>;

      if (Link::hasLinks($base->getId())) {
        $l_c = 1;
        foreach (Link::allLinks($base->getId()) as $link) {
          $links_div->appendChild(
            <div class="existing-link fb-column-container">
              <div class="col col-pad col-2-3">
                <div class="form-el">
                  <form class="link_form">
                    <input type="hidden" name="link_id" value={strval($link->getId())}/>
                    <div class="col el--block-label el--full-text">
                      <label>Link {$l_c}:</label>
                        <input name="link" type="text" value={$link->getLink()} disabled={true}/>
                        <a href={$link->getLink()} target="_blank">Link</a>
                    </div>
                  </form>
                </div>
              </div>
              <div class="admin-buttons col col-pad col-1-3">
                <div class="col el--block-label el--full-text">
                  <button class="fb-cta cta--red" data-action="delete-link">X</button>
                </div>
              </div>
            </div>
          );
        }
        $l_c++;
      }

      $adminsections->appendChild(
        <section class="admin-box section-locked">
              <form class="level_form base_form" name={$base_id}>
                <input type="hidden" name="level_type" value="base"/>
                <input type="hidden" name="level_id" value={strval($base->getId())}/>
                <header class="admin-box-header">
                  <h3>Base Level {$c}</h3>
                  <div class="admin-section-toggle radio-inline">
                    <input type="radio" name={$base_status_name} id={$base_status_on_id} checked={$base_active_on}/>
                    <label for={$base_status_on_id}>On</label>
                    <input type="radio" name={$base_status_name} id={$base_status_off_id} checked={$base_active_off}/>
                    <label for={$base_status_off_id}>Off</label>
                  </div>
                </header>
                <div class="fb-column-container">
                  <div class="col col-pad col-1-2">
                    <div class="form-el el--block-label el--full-text">
                      <label>Title</label>
                      <input name="title" type="text" value={$base->getTitle()} disabled={true}/>
                    </div>
                    <div class="form-el el--block-label el--full-text">
                      <label>Description</label>
                      <textarea name="description" rows={4} disabled={true}>{$base->getDescription()}</textarea>
                    </div>
                    <div class="form-el fb-column-container col-gutters">
                      <div class="col col-1-2 el--block-label el--full-text">
                        <label for="">Country</label>
                        {$this->generateCountriesSelect($base->getEntityId())}
                      </div>
                      <div class="col col-1-2 el--block-label el--full-text">
                        <label for="">Category</label>
                        {$this->generateLevelCategoriesSelect($base->getCategoryId())}
                      </div>
                    </div>
                  </div>
                  <div class="col col-pad col-1-2">
                    <div class="form-el fb-column-container col-gutters">
                      <div class="col col-1-2 el--block-label el--full-text">
                        <label>Points</label>
                        <input name="points" type="text" value={strval($base->getPoints())} disabled={true}/>
                      </div>
                      <div class="col col-1-2 el--block-label el--full-text">
                        <label>Bonus</label>
                        <input name="bonus" type="text" value={strval($base->getBonus())} disabled={true}/>
                      </div>
                    </div>
                    <div class="form-el fb-column-container col-gutters">
                      <div class="col col-1-2 el--block-label el--full-text">
                        <label>Hint</label>
                        <input name="hint" type="text" value={$base->getHint()} disabled={true}/>
                      </div>
                      <div class="col col-1-2 el--block-label el--full-text">
                        <label>Hint Penalty</label>
                        <input name="penalty" type="text" value={strval($base->getPenalty())} disabled={true}/>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
              {$attachments_div}
              {$links_div}
              <div class="admin-buttons admin-row">
                <div class="button-right">
                  <a href="#" class="admin--edit" data-action="edit">EDIT</a>
                  <button class="fb-cta cta--red" data-action="delete">Delete</button>
                  <button class="fb-cta cta--yellow" data-action="save-no-validation">Save</button>
                </div>
                <div class="button-left">
                  <button class="fb-cta" data-action="add-attachment">+ Attachment</button>
                  <button class="fb-cta" data-action="add-link">+ Link</button>
                </div>
              </div>
            </section>
      );
      $c++;
    }

    return
      <div>
        <header class="admin-page-header">
          <h3>Bases Management</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$adminsections}
        <div class="admin-buttons">
          <button class="fb-cta" data-action="add-new">Add Base Level</button>
        </div>
      </div>;
  }

  public function renderCategoriesContent(): :xhp {
    $adminsections =
      <div class="admin-sections">
      </div>;

    $adminsections->appendChild(
      <section class="admin-box completely-hidden">
        <form class="categories_form">
          <header class="admin-box-header">
            <h3>New Category</h3>
          </header>
          <div class="fb-column-container">
            <div class="col col-pad">
              <div class="form-el el--block-label el--full-text">
                <label class="admin-label" for="">Category: </label>
                <input name="category" type="text" value=""/>
              </div>
            </div>
          </div>
          <div class="admin-buttons admin-row">
            <div class="button-right">
              <a href="#" class="admin--edit" data-action="edit">EDIT</a>
              <button class="fb-cta cta--red" data-action="delete">Delete</button>
              <button class="fb-cta cta--yellow" data-action="create">Create</button>
            </div>
          </div>
        </form>
      </section>
    );

    $categories = Category::allCategories();

    foreach ($categories as $category) {
      if (Category::isUsed($category->getId())) {
        $delete_action = <a></a>;
      } else {
        $delete_action = <a class="highlighted--red" href="#" data-action="delete">DELETE</a>;
      }
      $adminsections->appendChild(
        <section class="admin-box">
          <form class="categories_form">
            <input type="hidden" name="category_id" value={strval($category->getId())}/>
            <header class="countries-management-header">
              <h6>ID{strval($category->getId())}</h6>
              {$delete_action}
            </header>
            <div class="fb-column-container">
              <div class="col col-pad">
                <div class="selected-logo">
                  <label>Category: </label>
                  <span class="logo-name">{$category->getCategory()}</span>
                </div>
              </div>
            </div>
          </form>
        </section>
      );
    }

    return
      <div>
        <header class="admin-page-header">
          <h3>Categories Management</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$adminsections}
        <div class="admin-buttons">
          <button class="fb-cta" data-action="add-new">Add Category</button>
        </div>
      </div>;
  }

  public function renderCountriesContent(): :xhp {
    $adminsections =
      <div class="admin-sections">
      </div>;

    $adminsections->appendChild(
      <section id="new-element" class="admin-box">
        <header class="admin-box-header">
          <h3>Filter By:</h3>
          <div class="form-el fb-column-container col-gutters">
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
              <select name="use_filter">
                <option class="filter_option" value="all">All Countries</option>
                <option class="filter_option" value="Yes">In Use</option>
                <option class="filter_option" value="No">Not Used</option>
              </select>
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
              <select name="country_status_filter">
                <option class="filter_option" value="all">All Status</option>
                <option class="filter_option" value="enabled">Enabled</option>
                <option class="filter_option" value="disabled">Disabled</option>
              </select>
            </div>
            <div class="col col-1-5 el--block-label el--full-text">
            </div>
          </div>
        </header>
      </section>
    );

    foreach (Country::allCountries(false) as $country) {
      $using_country = Level::whoUses($country->getId());
      $current_use = ($using_country) ? 'Yes' : 'No';
      if ($country->getEnabled()) {
        $highlighted_action = 'disable_country';
        $highlighted_color = 'highlighted--red country-enabled';
      } else {
        $highlighted_action = 'enable_country';
        $highlighted_color = 'highlighted--green country-disabled';
      }
      $current_status = strtoupper(explode('_', $highlighted_action)[0]);

      if (!$using_country) {
        $status_action =
          <a class={$highlighted_color} href="#" data-action={str_replace('_', '-', $highlighted_action)}>
            {$current_status}
          </a>;
      } else {
        $status_action = <a class={$highlighted_color}></a>;
      }

      $adminsections->appendChild(
        <section class="admin-box">
          <form class="country_form">
            <input type="hidden" name="country_id" value={strval($country->getId())}/>
            <input type="hidden" name="status_action" value={$highlighted_action}/>
            <header class="countries-management-header">
              <h6>ID{strval($country->getId())}</h6>
              {$status_action}
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-2-3">
                <div class="selected-logo">
                  <label>Country: </label>
                  <span class="logo-name">{$country->getName()}</span>
                </div>
              </div>
              <div class="col col-pad col-1-3">
                <div class="selected-logo">
                  <label>ISO Code: </label>
                  <span class="logo-name">{$country->getIsoCode()}</span>
                </div>
                <div class="selected-logo">
                  <label>In Use: </label>
                  <span class="logo-name country-use">{$current_use}</span>
                </div>
              </div>
            </div>
          </form>
        </section>
      );
    }
    return
      <div>
        <header class="admin-page-header">
          <h3>Countries Management</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$adminsections}
      </div>;
  }

  public function renderTeamsContent(): :xhp {
    $adminsections =
      <div class="admin-sections">
        <section class="admin-box validate-form section-locked completely-hidden">
          <form class="team_form">
            <header class="admin-box-header">
              <h3>New Team</h3>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-1-2">
                <div class="form-el el--block-label el--full-text">
                  <label class="admin-label" for="">Team Name</label>
                  <input name="team_name" type="text" value="" maxlength={20}/>
                </div>
              </div>
              <div class="col col-pad col-1-2">
                <div class="form-el el--block-label el--full-text">
                  <label class="admin-label" for="">Password</label>
                  <input name="password" type="password" value=""/>
                </div>
              </div>
            </div>
            <div class="admin-row el--block-label">
              <label>Team Logo</label>
              <div class="fb-column-container">
                <div class="col col-shrink">
                  <div class="post-avatar has-avatar">
                    <svg class="icon icon--badge">
                      <use href="#icon--badge-"/>

                    </svg>
                  </div>
                </div>
                <div class="col col-grow">
                  <div class="selected-logo">
                    <label>Selected Logo: </label>
                    <span class="logo-name"></span>
                  </div>
                  <a href="#" class="alt-link js-choose-logo">Select Logo ></a>
                </div>
                <div class="col col-shrink admin-buttons">
                  <a href="#" class="admin--edit" data-action="edit">EDIT</a>
                  <button class="fb-cta cta--red" data-action="delete">Delete</button>
                  <button class="fb-cta cta--yellow js-confirm-save" data-action="create">Create</button>
                </div>
              </div>
            </div>
          </form>
        </section>
        <section class="admin-box">
          <header class="admin-box-header">
            <h3>All Teams</h3>
            <form class="all_team_form">
              <div class="admin-section-toggle radio-inline col">
                <input type="radio" name="fb--admin--all_team" id="fb--admin--all_team--on"/>
                <label for="fb--admin--all_team--on">On</label>
                <input type="radio" name="fb--admin--all_team" id="fb--admin--all_team--off"/>
                <label for="fb--admin--all_team--off">Off</label>
              </div>
            </form>
          </header>
        </section>
      </div>;

    $c = 1;
    foreach (Team::allTeams() as $team) {
      $xlink_href = '#icon--badge-'.$team->getLogo();
      $team_protected = $team->getProtected();
      $team_active_on = $team->getActive();
      $team_active_off = !$team->getActive();
      $team_admin_on = $team->getAdmin();
      $team_admin_off = !$team->getAdmin();
      $team_visible_on = $team->getVisible();
      $team_visible_off = !$team->getVisible();

      $team_status_name = 'fb--teams--team-'.strval($team->getId()).'-status';
      $team_status_on_id = 'fb--teams--team-'.strval($team->getId()).'-status--on';
      $team_status_off_id = 'fb--teams--team-'.strval($team->getId()).'-status--off';
      $team_admin_name = 'fb--teams--team-'.strval($team->getId()).'-admin';
      $team_admin_on_id = 'fb--teams--team-'.strval($team->getId()).'-admin--on';
      $team_admin_off_id = 'fb--teams--team-'.strval($team->getId()).'-admin--off';
      $team_visible_name = 'fb--teams--team-'.strval($team->getId()).'-visible';
      $team_visible_on_id = 'fb--teams--team-'.strval($team->getId()).'-visible--on';
      $team_visible_off_id = 'fb--teams--team-'.strval($team->getId()).'-visible--off';

      if ($team_protected) {
        $toggle_status =
          <div class="admin-section-toggle radio-inline">
            <input type="radio" name={$team_status_name} id={$team_status_on_id} checked={$team_active_on}/>
            <label for={$team_status_on_id}>On</label>
          </div>;
        $toggle_admin =
          <div class="admin-section-toggle radio-inline">
            <input type="radio" name={$team_admin_name} id={$team_admin_on_id} checked={$team_admin_on}/>
            <label for={$team_admin_on_id}>On</label>
          </div>;
        $delete_button = <button class="fb-cta cta--red" disabled={true}>Protected</button>;
      } else {
        $toggle_status =
          <div class="admin-section-toggle radio-inline">
            <input type="radio" name={$team_status_name} id={$team_status_on_id} checked={$team_active_on}/>
            <label for={$team_status_on_id}>On</label>
            <input type="radio" name={$team_status_name} id={$team_status_off_id} checked={$team_active_off}/>
            <label for={$team_status_off_id}>Off</label>
          </div>;
        $toggle_admin =
          <div class="admin-section-toggle radio-inline">
            <input type="radio" name={$team_admin_name} id={$team_admin_on_id} checked={$team_admin_on}/>
            <label for={$team_admin_on_id}>On</label>
            <input type="radio" name={$team_admin_name} id={$team_admin_off_id} checked={$team_admin_off}/>
            <label for={$team_admin_off_id}>Off</label>
          </div>;
        $delete_button = <button class="fb-cta cta--red" data-action="delete">Delete</button>;
      }

      $adminsections->appendChild(
        <section class="admin-box validate-form section-locked">
          <form class="team_form" name={strval($team->getId())}>
            <input type="hidden" name="team_id" value={strval($team->getId())}/>
            <header class="admin-box-header">
              <h3>Team {$c}</h3>
              {$toggle_status}
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-1-3">
                <div class="form-el el--block-label el--full-text">
                  <label class="admin-label" for="">Team Name</label>
                  <input name="team_name" type="text" value={$team->getName()} maxlength={20} disabled={true}/>
                </div>
                <div class="form-el el--block-label el--full-text">
                  <label class="admin-label" for="">Score</label>
                  <input name="points" type="text" value={strval($team->getPoints())} disabled={true}/>
                </div>
              </div>
              <div class="col col-pad col-1-3">
                <div class="form-el el--block-label el--full-text">
                  <label class="admin-label" for="">Change Password</label>
                  <input name="password" type="password" disabled={true}/>
                </div>
              </div>
              <div class="col col-pad col-1-3">
                <div class="form-el el--block-label">
                  <label class="admin-label" for="">Admin Level</label>
                  {$toggle_admin}
                </div>
                <div class="form-el el--block-label">
                  <label class="admin-label" for="">Visibility </label>
                  <div class="admin-section-toggle radio-inline">
                    <input type="radio" name={$team_visible_name} id={$team_visible_on_id} checked={$team_visible_on}/>
                    <label for={$team_visible_on_id}>On</label>
                    <input type="radio" name={$team_visible_name} id={$team_visible_off_id} checked={$team_visible_off}/>
                    <label for={$team_visible_off_id}>Off</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="admin-row el--block-label">
              <label>Team Logo</label>
              <div class="fb-column-container">
                <div class="col col-shrink">
                  <div class="post-avatar has-avatar">
                    <svg class="icon icon--badge">
                      <use href={$xlink_href} />

                    </svg>
                  </div>
                </div>
                <div class="col col-grow">
                  <div class="selected-logo">
                    <label>Selected Logo: </label>
                    <span class="logo-name">{$team->getLogo()}</span>
                  </div>
                  <a href="#" class="alt-link js-choose-logo">Select Logo ></a>
                </div>
                <div class="col col-shrink admin-buttons">
                  <a href="#" class="admin--edit" data-action="edit">EDIT</a>
                  {$delete_button}
                  <button class="fb-cta cta--yellow js-confirm-save" data-action="save-no-validation">Save</button>
                </div>
              </div>
            </div>
          </form>
        </section>
      );
      $c++;
    }
    return
      <div>
        <header class="admin-page-header">
          <h3>Team Management</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$adminsections}
        <div class="admin-buttons">
          <button class="fb-cta" data-action="add-new">Add Team</button>
        </div>
      </div>;
  }

  public function renderLogosContent(): :xhp {
    $adminsections =
      <div class="admin-sections">
      </div>;

    foreach (Logo::allLogos() as $logo) {
      $xlink_href = '#icon--badge-'.$logo->getName();
      $using_logo = Team::whoUses($logo->getName());
      $current_use = (count($using_logo) > 0) ? 'Yes' : 'No';
      if ($logo->getEnabled()) {
        $highlighted_action = 'disable_logo';
        $highlighted_color = 'highlighted--red';
      } else {
        $highlighted_action = 'enable_logo';
        $highlighted_color = 'highlighted--green';
      }
      $action_text = strtoupper(explode('_', $highlighted_action)[0]);

      if ($using_logo) {
        $use_select = <select></select>;
        foreach ($using_logo as $t) {
          $use_select->appendChild(<option value="">{$t->getName()}</option>);
        }
      } else {
        $use_select = <select><option value="0">None</option></select>;
      }

      $adminsections->appendChild(
        <section class="admin-box">
          <form class="logo_form">
            <input type="hidden" name="logo_id" value={strval($logo->getId())}/>
            <input type="hidden" name="status_action" value={strtolower($action_text)}/>
            <header class="logo-management-header">
              <h6>ID{strval($logo->getId())}</h6>
              <a class={$highlighted_color} href="#" data-action={str_replace('_', '-', $highlighted_action)}>{$action_text}</a>
            </header>
            <div class="fb-column-container">
              <div class="col col-pad col-shrink">
                <div class="post-avatar has-avatar">
                  <svg class="icon icon--badge">
                    <use href={$xlink_href}></use>

                  </svg>
                </div>
              </div>
              <div class="col col-pad col-grow">
                <div class="selected-logo">
                  <label>Logo Name: </label>
                  <span class="logo-name">{$logo->getName()}</span>
                </div>
                <div class="selected-logo">
                  <label>In use: </label>
                  <span class="logo-name">{$current_use}</span>
                </div>
              </div>
              <div class="col col-pad col-1-3">
                <div class="form-el el--select el--block-label">
                  <label for="">Used By:</label>
                  {$use_select}
                </div>
              </div>
            </div>
          </form>
        </section>
      );
    }

    return
      <div>
        <header class="admin-page-header">
          <h3>Logo Management</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$adminsections}
      </div>;
  }

  public function renderSessionsContent(): :xhp {
    $adminsections =
      <div class="admin-sections">
      </div>;

    $c = 1;
    foreach (sess_all() as $session) {
      $session_id = 'session_'.$session['id'];
      $adminsections->appendChild(
        <section class="admin-box section-locked">
          <form class="session_form" name={$session_id}>
            <input type="hidden" name="session_id" value={$session['id']}/>
            <header class="admin-box-header">
              <span class="session-name">Session {$c}: <span class="highlighted--blue">{$session['last_access_ts']}</span></span>
            </header>
            <div class="fb-column-container">
              <div class="col col-1-2 col-pad">
                <div class="form-el el--block-label el--full-text">
                  <label class="admin-label">Cookie</label>
                  <input name="cookie" type="text" value={$session['cookie']} disabled={true}/>
                </div>
              </div>
              <div class="col col-1-2 col-pad">
                <div class="form-el el--block-label el--full-text">
                  <label class="admin-label">Creation Time:</label>
                  <span class="highlighted"><label class="admin-label">{$session['created_ts']}</label></span>
                </div>
              </div>
            </div>
            <div class="admin-row">
              <div class="form-el el--block-label el--full-text">
                <label class="admin-label">Data</label>
                <input name="data" type="text" value={$session['data']} disabled={true}/>
              </div>
            </div>
            <div class="admin-buttons admin-row">
              <div class="button-right">
                <a href="#" class="admin--edit" data-action="edit">EDIT</a>
                <button class="fb-cta cta--red" data-action="delete">Delete</button>
              </div>
            </div>
          </form>
        </section>
      );
      $c++;
    }
    return
      <div>
        <header class="admin-page-header">
          <h3>Sessions</h3>
          <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
        </header>
        {$adminsections}
      </div>;
  }

  public function renderScoreboardContent(): :xhp {
    return
      <div>
      <header class="admin-page-header">
        <h3>Scoreboard</h3>
        <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
      </header>
      <div class="radio-tabs">
        <input type="radio" value="flags" name="fb--admin--tabs--scoreboard" id="fb--admin--tabs--scoreboard--flags" checked={true}/>
        <label for="fb--admin--tabs--scoreboard--flags">Scoring</label>
        <input type="radio" value="timeline" name="fb--admin--tabs--scoreboard" id="fb--admin--tabs--scoreboard--timeline"/>
        <label for="fb--admin--tabs--scoreboard--timeline">Progressive</label>
      </div>
      <div class="tab-content-container scoreboard-graphic-container">
        <div class="radio-tab-content active" data-tab="flags">
          <div class="admin-sections">
            <section class="admin-box">
              <div class="logo-management-header">
                <h6>Flag 01</h6>
              </div>
              <div class="fb-column-container">
                <div class="col col-shrink col-pad">
                  <div class="post-avatar has-avatar">
                    <svg class="icon icon--badge">
                      <use href="#icon--badge-invader" />

                    </svg>
                  </div>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Team:</dt>
                    <dd>Da Invaderz</dd>
                    <dt>Time:</dt>
                    <dd>02:59:09</dd>
                    <dt>Date:</dt>
                    <dd>2015-08-04</dd>
                    <dt>Total Points:</dt>
                    <dd>345</dd>
                  </dl>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Country:</dt>
                    <dd>Algeria</dd>
                    <dt>Points:</dt>
                    <dd>+35</dd>
                    <dt>Flag Level:</dt>
                    <dd>Expert</dd>
                    <dt>Solution Time:</dt>
                    <dd>00:15:23</dd>
                  </dl>
                </div>
              </div>
            </section>
            <section class="admin-box">
              <div class="logo-management-header">
                <h6>Flag 02</h6>
              </div>
              <div class="fb-column-container">
                <div class="col col-shrink col-pad">
                  <div class="post-avatar has-avatar">
                    <svg class="icon icon--badge">
                      <use href="#icon--badge-car" />

                    </svg>
                  </div>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Team:</dt>
                    <dd>Tank SF</dd>
                    <dt>Time:</dt>
                    <dd>02:59:09</dd>
                    <dt>Date:</dt>
                    <dd>2015-08-04</dd>
                    <dt>Total Points:</dt>
                    <dd>280</dd>
                  </dl>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Country:</dt>
                    <dd>United States of America</dd>
                    <dt>Points:</dt>
                    <dd>+35</dd>
                    <dt>Flag Level:</dt>
                    <dd>Expert</dd>
                    <dt>Solution Time:</dt>
                    <dd>00:15:23</dd>
                  </dl>
                </div>
              </div>
            </section>
          </div>
        </div><!-- data-tab="flags" -->
        <div class="radio-tab-content" data-tab="timeline">
          <div class="scoreboard-graphic">
            <svg class="fb-graphic" data-file="data/scores.php" width="100%" height={180}></svg>
          </div>
          <div class="game-progress fb-progress-bar fb-cf row-fixed">
            <div class="indicator game-progress-indicator">
              <span class="indicator-cell active"></span>
              <span class="indicator-cell active"></span>
              <span class="indicator-cell active"></span>
              <span class="indicator-cell"></span>
              <span class="indicator-cell"></span>
              <span class="indicator-cell"></span>
              <span class="indicator-cell"></span>
              <span class="indicator-cell"></span>
              <span class="indicator-cell"></span>
              <span class="indicator-cell"></span>
            </div>
            <span class="label label--left">[Start]</span>
            <span class="label label--right">[End]</span>
          </div>
          <div class="game-scoreboard fb-row-container">
            <table class="row-fixed">
              <thead>
                <tr>
                  <th style="width: 10%;">filter_</th>
                  <th style="width: 10%;">rank_</th>
                  <th style="width: 40%;">team_name_</th>
                  <th style="width: 10%;">base_pts_</th>
                  <th style="width: 10%;">quiz_pts_</th>
                  <th style="width: 10%;">flag_pts_</th>
                  <th style="width: 10%;">total_</th>
                </tr>
              </thead>
            </table>
            <div class="row-fluid main-data">
              <table class="row-fixed">
                <tbody>
                  <tr>
                    <td></td>
                    <td>02</td>
                    <td>Da Invaderz</td>
                    <td>52</td>
                    <td>54</td>
                    <td>180</td>
                    <td>280</td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>03</td>
                    <td>DEATH HAXX</td>
                    <td>0</td>
                    <td>0</td>
                    <td>262</td>
                    <td>262</td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>04</td>
                    <td>Robocop Squad</td>
                    <td>63</td>
                    <td>65</td>
                    <td>128</td>
                    <td>248</td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>05</td>
                    <td>Tank NYC</td>
                    <td>52</td>
                    <td>54</td>
                    <td>180</td>
                    <td>280</td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>06</td>
                    <td>Rebelz</td>
                    <td>38</td>
                    <td>38</td>
                    <td>144</td>
                    <td>220</td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>07</td>
                    <td>Dominationstation</td>
                    <td>83</td>
                    <td>83</td>
                    <td>50</td>
                    <td>216</td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>08</td>
                    <td>For the Win</td>
                    <td>41</td>
                    <td>59</td>
                    <td>109</td>
                    <td>209</td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>09</td>
                    <td>Monkey Town</td>
                    <td>102</td>
                    <td>0</td>
                    <td>100</td>
                    <td>202</td>
                  </tr>
                  <tr>
                    <td></td>
                    <td>10</td>
                    <td>Heavy Hitterz</td>
                    <td>0</td>
                    <td>60</td>
                    <td>128</td>
                    <td>188</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div><!-- data-tab="timeline" -->
      </div>
    </div>;
  }

  public function renderLogsContent(): :xhp {
    return
      <div>
      <header class="admin-page-header">
        <h3>Logs</h3>
        <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
      </header>
      <div class="radio-tabs">
        <input type="radio" value="scores" name="fb--admin--tabs--logs" id="fb--admin--tabs--logs--scores" checked={true}/>
        <label for="fb--admin--tabs--logs--scores">Scores</label>
        <input type="radio" value="failures" name="fb--admin--tabs--logs" id="fb--admin--tabs--logs--failures"/>
        <label for="fb--admin--tabs--logs--failures">Failures</label>
      </div>
      <div class="tab-content-container">
        <div class="radio-tab-content active" data-tab="scores">
          <div class="admin-sections">
            <section class="admin-box">
              <div class="logo-management-header">
                <h6>Flag 01</h6>
              </div>
              <div class="fb-column-container">
                <div class="col col-shrink col-pad">
                  <div class="post-avatar has-avatar">
                    <svg class="icon icon--mini">
                      <use href="#icon--badge-invader" />

                    </svg>
                  </div>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Team:</dt>
                    <dd>Da Invaderz</dd>
                    <dt>Time:</dt>
                    <dd>02:59:09</dd>
                    <dt>Date:</dt>
                    <dd>2015-08-04</dd>
                    <dt>Total Points:</dt>
                    <dd>345</dd>
                  </dl>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Country:</dt>
                    <dd>Algeria</dd>
                    <dt>Points:</dt>
                    <dd>+35</dd>
                    <dt>Flag Level:</dt>
                    <dd>Expert</dd>
                    <dt>Solution Time:</dt>
                    <dd>00:15:23</dd>
                  </dl>
                </div>
              </div>
            </section>
            <section class="admin-box">
              <div class="logo-management-header">
                <h6>Flag 02</h6>
              </div>
              <div class="fb-column-container">
                <div class="col col-shrink col-pad">
                  <div class="post-avatar has-avatar">
                    <svg class="icon icon--mini">
                      <use href="#icon--badge-car" />

                    </svg>
                  </div>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Team:</dt>
                    <dd>Tank SF</dd>
                    <dt>Time:</dt>
                    <dd>02:59:09</dd>
                    <dt>Date:</dt>
                    <dd>2015-08-04</dd>
                    <dt>Total Points:</dt>
                    <dd>280</dd>
                  </dl>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Country:</dt>
                    <dd>United States of America</dd>
                    <dt>Points:</dt>
                    <dd>+35</dd>
                    <dt>Flag Level:</dt>
                    <dd>Expert</dd>
                    <dt>Solution Time:</dt>
                    <dd>00:15:23</dd>
                  </dl>
                </div>
              </div>
            </section>
          </div>
        </div>
        <div class="radio-tab-content" data-tab="failures">
          <div class="admin-sections">
            <section class="admin-box">
              <div class="logo-management-header">
                <h6>Flag 03</h6>
              </div>
              <div class="fb-column-container">
                <div class="col col-shrink col-pad">
                  <div class="post-avatar has-avatar">
                    <svg class="icon icon--badge">
                      <use href="#icon--badge-invader" />

                    </svg>
                  </div>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Team:</dt>
                    <dd>Da Invaderz</dd>
                    <dt>Time:</dt>
                    <dd>02:59:09</dd>
                    <dt>Date:</dt>
                    <dd>2015-08-04</dd>
                    <dt>Total Points:</dt>
                    <dd>345</dd>
                  </dl>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Country:</dt>
                    <dd>Algeria</dd>
                    <dt>Points:</dt>
                    <dd>+35</dd>
                    <dt>Flag Level:</dt>
                    <dd>Expert</dd>
                    <dt>Solution Time:</dt>
                    <dd>00:15:23</dd>
                  </dl>
                </div>
              </div>
            </section>
            <section class="admin-box">
              <div class="logo-management-header">
                <h6>Flag 04</h6>
              </div>
              <div class="fb-column-container">
                <div class="col col-shrink col-pad">
                  <div class="post-avatar has-avatar">
                    <svg class="icon icon--badge">
                      <use href="#icon--badge-car" />

                    </svg>
                  </div>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Team:</dt>
                    <dd>Tank SF</dd>
                    <dt>Time:</dt>
                    <dd>02:59:09</dd>
                    <dt>Date:</dt>
                    <dd>2015-08-04</dd>
                    <dt>Total Points:</dt>
                    <dd>280</dd>
                  </dl>
                </div>
                <div class="col col-grow col-pad">
                  <dl>
                    <dt>Country:</dt>
                    <dd>United States of America</dd>
                    <dt>Points:</dt>
                    <dd>+35</dd>
                    <dt>Flag Level:</dt>
                    <dd>Expert</dd>
                    <dt>Solution Time:</dt>
                    <dd>00:15:23</dd>
                  </dl>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>;
  }

  public function renderMainContent(): :xhp {
    return
      <h1>ADMIN</h1>;
  }

  public function renderMainNav(): :xhp {
    $game_status = (Configuration::get('game')->getValue() === '1');
    if ($game_status) {
      $game_action =
        <a href="#" class="fb-cta cta--red js-end-game">
          End Game
        </a>;
    } else {
      $game_action =
        <a href="#" class="fb-cta cta--yellow js-begin-game">
          Begin Game
        </a>;
    }
    return
      <div id="fb-admin-nav" class="admin-nav-bar fb-row-container">
        <header class="admin-nav-header row-fixed">
          <h2>Game Admin</h2>
        </header>
        <nav class="admin-nav-links row-fluid">
          <ul>
            <li><a href="/index.php?p=admin&page=configuration">Configuration</a></li>
            <li><a href="/index.php?p=admin&page=controls">Controls</a></li>
            <li><a href="/index.php?p=admin&page=announcements">Announcements</a></li>
            <li><a href="/index.php?p=admin&page=quiz">Levels: Quiz</a></li>
            <li><a href="/index.php?p=admin&page=flags">Levels: Flags</a></li>
            <li><a href="/index.php?p=admin&page=bases">Levels: Bases</a></li>
            <li><a href="/index.php?p=admin&page=categories">Levels: Categories</a></li>
            <li><a href="/index.php?p=admin&page=countries">Levels: Countries</a></li>
            <li><a href="/index.php?p=admin&page=teams">Teams</a></li>
            <li><a href="/index.php?p=admin&page=logos">Teams: Logos</a></li>
            <li><a href="/index.php?p=admin&page=sessions">Teams: Sessions</a></li>
            <li><a href="/index.php?p=admin&page=scoreboard">Scoreboard</a></li>
            <li><a href="/index.php?p=admin&page=logs">Logs</a></li>
          </ul>
          {$game_action}
        </nav>
        <div class="admin-nav--footer row-fixed">
          <a href="/index.php?p=game">Gameboard</a>
          <a href="" class="js-prompt-logout">Logout</a>
          <a></a>
          <fbbranding />
        </div>
      </div>;
  }

  public function renderPage(string $page): :xhp {
    switch ($page) {
      case 'main':
        return $this->renderMainContent();
        break;
      case 'configuration':
        return $this->renderConfigurationContent();
        break;
      case 'controls':
        return $this->renderControlsContent();
        break;
      case 'announcements':
        return $this->renderAnnouncementsContent();
        break;
      case 'quiz':
        return $this->renderQuizContent();
        break;
      case 'flags':
        return $this->renderFlagsContent();
        break;
      case 'bases':
        return $this->renderBasesContent();
        break;
      case 'categories':
        return $this->renderCategoriesContent();
        break;
      case 'countries':
        return $this->renderCountriesContent();
        break;
      case 'teams':
        return $this->renderTeamsContent();
        break;
      case 'logos':
        return $this->renderLogosContent();
        break;
      case 'sessions':
        return $this->renderSessionsContent();
        break;
      case 'scoreboard':
        return $this->renderScoreboardContent();
        break;
      case 'logs':
        return $this->renderLogsContent();
        break;
      default:
        return $this->renderMainContent();
        break;
    }
  }

  <<__Override>>
  public function renderBody(string $page): :xhp {
    return
      <body data-section="admin">
        <input type="hidden" name="csrf_token" value={sess_csrf_token()}/>
        <div style="height: 0; width: 0; position: absolute; visibility: hidden" id="fb-svg-sprite"></div>
        <div class="fb-viewport admin-viewport">
          {$this->renderMainNav()}
          <div id="fb-main-content" class="fb-page fb-admin-main">{$this->renderPage($page)}</div>
        </div>
        <script type="text/javascript" src="static/js/vendor/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="static/js/vendor/d3.min.js"></script>
        <script type="text/javascript" src="static/js/plugins.js"></script>
        <script type="text/javascript" src="static/js/fb-ctf.js"></script>
        <script type="text/javascript" src="static/js/admin-fb-ctf.js"></script>
        <script type="text/javascript" src="static/js/_buildkit.js"></script>
      </body>;
  }
}
