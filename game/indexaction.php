<?hh

require_once('../vendor/autoload.php');

function register_team($teamname, $password, $logo) {
  $teams = new Teams();
  $logos = new Logos();
  $conf = new Configuration();
  $final_logo = $logo;
  if (!$logos->check_exists($final_logo)) {
    $final_logo = $logos->random_logo();
  }

  // Check if team name is not empty or just spaces
  if ((!$teamname) || (trim($teamname) == "")) {
    error_response('Registration failed', 'registration');
    exit;
  }

  // Trim team name to 20 chars, to avoid breaking UI
  $shortname = substr($teamname, 0, 20);

  // Verify that this team name is not created yet
  if (!$teams->team_exist($shortname)) {
    $password_hash = $teams->generate_hash($password);
    $team_id = $teams->create_team($shortname, $password_hash, $final_logo);
      if ($team_id) {
        if ($conf->get('registration_login') === '1') {
          login_team($team_id, $password);
        } else {
          ok_response('Registration succesful', 'login');
        }
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
  $teams = new Teams();
  $team = $teams->verify_credentials($team_id, $password);
  if ($team) {
    sess_start();
    if (!sess_active()) {
      sess_set('team_id', $team['id']);
      sess_set('name', $team['name']);
      sess_set('csrf_token', base64_encode(openssl_random_pseudo_bytes(16)));
      if ($team['admin'] == 1) {
        sess_set('admin', $team['admin']);
      }
      sess_set('IP', $_SERVER['REMOTE_ADDR']);
    }
    ok_response('Login succesful', 'game');
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
      $request->parameters['logo']
    );
    break;
  case 'login_team':
    login_team(
      $request->parameters['team_id'],
      $request->parameters['password']
    );
    break;
  default:
    start_page();
    break;
}
