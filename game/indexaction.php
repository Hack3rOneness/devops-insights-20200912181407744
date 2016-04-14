<?hh

require_once('../vendor/autoload.php');

function register_team($teamname, $password, $token, $logo, $register_names = false, $names, $emails) {
  $control = new Control();

  // Check if registration is enabled
  if (Configuration::get('registration')->getValue() === '0') {
    error_response('Registration failed', 'registration');
    exit;
  }

  // Check if tokenized registration is enabled
  if (Configuration::get('registration_type')->getValue() === '2') {
    // Check provided token
    if (!$control->check_token($token)) {
      error_response('Registration failed', 'registration');
      exit;
    }
  }

  // Check logo
  $final_logo = $logo;
  if (!Logo::checkExists($final_logo)) {
    $final_logo = Logo::randomLogo();
  }

  // Check if team name is not empty or just spaces
  if ((!$teamname) || (trim($teamname) == "")) {
    error_response('Registration failed', 'registration');
    exit;
  }

  // Trim team name to 20 chars, to avoid breaking UI
  $shortname = substr($teamname, 0, 20);

  // Verify that this team name is not created yet
  if (!Team::teamExist($shortname)) {
    $password_hash = Team::generateHash($password);
    $team_id = Team::createTeam($shortname, $password_hash, $final_logo);
    if ($team_id) {
      // Store team players data, if enabled
      if ($register_names) {
        for ($i=0; $i<count($names); $i++) {
          Team::addTeamData($names[$i], $emails[$i], $team_id);
        }
      }
      // If registration is tokenized, use the token
      if (Configuration::get('registration_type')->getValue() === '2') {
        $control->use_token($token, $team_id);
      }
      // Login the team
      login_team($team_id, $password);
    } else {
      error_response('Registration failed', 'registration');
      exit;
    }
  } else {
    error_response('Registration failed', 'registration');
    exit;
  }
}

function login_team($team_id, $password) {
  // Check if login is enabled
  if (Configuration::get('login')->getValue() === '0') {
    error_response('Login failed', 'login');
    exit;
  }
  
  // Verify credentials
  $team = Team::verifyCredentials($team_id, $password);

  if ($team) {
    error_log('3');
    sess_start();
    if (!sess_active()) {
      sess_set('team_id', $team->getId());
      sess_set('name', $team->getName());
      sess_set('csrf_token', base64_encode(openssl_random_pseudo_bytes(16)));
      if ($team->getAdmin()) {
        sess_set('admin', intval($team->getAdmin()));
      }
      sess_set('IP', $_SERVER['REMOTE_ADDR']);
    } 
    if ($team->getAdmin()) {
      $redirect = 'admin';
    } else {
      $redirect = 'game';
    }
    ok_response('Login succesful', $redirect);
  } else {
    error_response('Login failed', 'login');
    exit;
  }
}

$filters = array(
  'POST' => array(
    'team_id'     => FILTER_VALIDATE_INT,
    'teamname'    => FILTER_UNSAFE_RAW,
    'password'    => FILTER_UNSAFE_RAW,
    'logo'        => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    ),
    'token'        => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w]+$/'
      ),
    ),
    'names'       => FILTER_UNSAFE_RAW,
    'emails'      => FILTER_UNSAFE_RAW,
    'action'      => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    )
  )
);
$actions = array(
  'register_team',
  'register_names',
  'login_team',
);
$request = new Request($filters, $actions, array());
$request->processRequest();

switch ($request->action) {
  case 'none':
    start_page();
    break;
  case 'register_team':
    register_team(
      $request->parameters['teamname'],
      $request->parameters['password'],
      $request->parameters['token'],
      $request->parameters['logo'],
      false,
      array(),
      array()
    );
    break;
  case 'register_names':
    $names = json_decode($request->parameters['names']);
    $emails = json_decode($request->parameters['emails']);

    register_team(
      $request->parameters['teamname'],
      $request->parameters['password'],
      $request->parameters['token'],
      $request->parameters['logo'],
      true,
      $names,
      $emails
    );
    break;
  case 'login_team':
    if (Configuration::get('login_select')->getValue() === '1') {
      $team_id = $request->parameters['team_id'];
    } else {
      $team_name = $request->parameters['teamname'];
      if (Team::teamExist($team_name)) {
        $team_id = Team::getTeamByName($team_name)->getId();
      } else {
        error_response('Login failed', 'login');
        exit;
      }
    }

    // If we are here, login!
    login_team(
      $team_id,
      $request->parameters['password']
    );
    break;
  default:
    start_page();
    break;
}
