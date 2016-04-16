<?hh

require_once('../vendor/autoload.php');

sess_start();
sess_enforce_admin();

$filters = array(
  'POST' => array(
    'level_id'    => FILTER_VALIDATE_INT,
    'level_type'  => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[a-z]{4}$/'
      ),
    ),
    'team_id'     => FILTER_VALIDATE_INT,
    'session_id'  => FILTER_VALIDATE_INT,
    'cookie'      => FILTER_SANITIZE_STRING,
    'data'        => FILTER_UNSAFE_RAW,
    'name'        => FILTER_UNSAFE_RAW,
    'password'    => FILTER_UNSAFE_RAW,
    'admin'       => FILTER_VALIDATE_INT,
    'status'      => FILTER_VALIDATE_INT,
    'visible'     => FILTER_VALIDATE_INT,
    'all_type'    => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[a-z]{4}$/'
      ),
    ),
    'logo_id'     => FILTER_VALIDATE_INT,
    'logo'        => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    ),
    'entity_id'   => FILTER_VALIDATE_INT,
    'attachment_id' => FILTER_VALIDATE_INT,
    'filename'    => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w\-\.]+$/'
       ),
    ),
    'attachment_file' => FILTER_UNSAFE_RAW,
    'link_id'     => FILTER_VALIDATE_INT,
    'link'        => FILTER_VALIDATE_URL,
    'category_id' => FILTER_VALIDATE_INT,
    'category'    => FILTER_SANITIZE_STRING,
    'country_id'  => FILTER_VALIDATE_INT,
    'title'       => FILTER_UNSAFE_RAW,
    'description' => FILTER_UNSAFE_RAW,
    'question'    => FILTER_UNSAFE_RAW,
    'flag'        => FILTER_UNSAFE_RAW,
    'answer'      => FILTER_UNSAFE_RAW,
    'hint'        => FILTER_UNSAFE_RAW,
    'points'      => FILTER_VALIDATE_INT,
    'bonus'       => FILTER_VALIDATE_INT,
    'bonus_dec'   => FILTER_VALIDATE_INT,
    'penalty'     => FILTER_VALIDATE_INT,
    'active'      => FILTER_VALIDATE_INT,
    'field'       => FILTER_UNSAFE_RAW,
    'value'       => FILTER_UNSAFE_RAW,
    'announcement'=> FILTER_UNSAFE_RAW,
    'announcement_id' => FILTER_VALIDATE_INT,
    'csrf_token'  => FILTER_UNSAFE_RAW,
    'action'      => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    ),
    'page'      => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    )
  )
);
$actions = array(
  'create_team',
  'create_quiz',
  'update_quiz',
  'create_flag',
  'update_flag',
  'create_base',
  'update_base',
  'update_team',
  'delete_team',
  'delete_level',
  'delete_all',
  'update_session',
  'delete_session',
  'toggle_status_level',
  'toggle_status_all',
  'toggle_status_team',
  'toggle_admin_team',
  'toggle_visible_team',
  'enable_country',
  'disable_country',
  'create_category',
  'delete_category',
  'enable_logo',
  'disable_logo',
  'create_attachment',
  'update_attachment',
  'delete_attachment',
  'create_link',
  'update_link',
  'delete_link',
  'begin_game',
  'change_configuration',
  'create_announcement',
  'delete_announcement',
  'create_tokens',
  'end_game',
  'reset_game',
  'backup_db'
);
$request = new Request($filters, $actions, array());
$request->processRequest();

if ($request->action !== 'none') {
  // CSRF check
  if ($request->parameters['csrf_token'] !== sess_csrf_token()) {
    error_log('CSRF Token is invalid');
    error_page();
  }
}

switch ($request->action) {
  case 'none':
    admin_page();
    break;
  case 'create_quiz':
    $bonus = Configuration::get('default_bonus')->getValue();
    $bonus_dec = Configuration::get('default_bonusdec')->getValue();
    Level::createQuiz(
      $request->parameters['title'],
      $request->parameters['question'],
      $request->parameters['answer'],
      intval($request->parameters['entity_id']),
      intval($request->parameters['points']),
      intval($bonus),
      intval($bonus_dec),
      $request->parameters['hint'],
      intval($request->parameters['penalty'])
    );
    ok_response('Created succesfully', 'admin');
    break;
  case 'update_quiz':
    Level::updateQuiz(
      $request->parameters['title'],
      $request->parameters['question'],
      $request->parameters['answer'],
      intval($request->parameters['entity_id']),
      intval($request->parameters['points']),
      intval($request->parameters['bonus']),
      intval($request->parameters['bonus_dec']),
      $request->parameters['hint'],
      intval($request->parameters['penalty']),
      intval($request->parameters['level_id'])
    );
    ok_response('Updated succesfully', 'admin');
    break;
  case 'create_flag':
    $bonus = Configuration::get('default_bonus')->getValue();
    $bonus_dec = Configuration::get('default_bonusdec')->getValue();
    Level::createFlag(
      $request->parameters['title'],
      $request->parameters['description'],
      $request->parameters['flag'],
      intval($request->parameters['entity_id']),
      intval($request->parameters['category_id']),
      intval($request->parameters['points']),
      intval($bonus),
      intval($bonus_dec),
      $request->parameters['hint'],
      intval($request->parameters['penalty'])
    );
    ok_response('Created succesfully', 'admin');
    break;
  case 'update_flag':
    Level::updateFlag(
      $request->parameters['title'],
      $request->parameters['description'],
      $request->parameters['flag'],
      intval($request->parameters['entity_id']),
      intval($request->parameters['category_id']),
      intval($request->parameters['points']),
      intval($request->parameters['bonus']),
      intval($request->parameters['bonus_dec']),
      $request->parameters['hint'],
      intval($request->parameters['penalty']),
      intval($request->parameters['level_id'])
    );
    ok_response('Updated succesfully', 'admin');
    break;
  case 'create_base':
    $bonus = Configuration::get('default_bonus')->getValue();
    Level::createBase(
      $request->parameters['title'],
      $request->parameters['description'],
      intval($request->parameters['entity_id']),
      intval($request->parameters['category_id']),
      intval($request->parameters['points']),
      intval($bonus),
      $request->parameters['hint'],
      intval($request->parameters['penalty'])
    );
    ok_response('Created succesfully', 'admin');
    break;
  case 'update_base':
    Level::updateBase(
      $request->parameters['title'],
      $request->parameters['description'],
      intval($request->parameters['entity_id']),
      intval($request->parameters['category_id']),
      intval($request->parameters['points']),
      intval($request->parameters['bonus']),
      $request->parameters['hint'],
      intval($request->parameters['penalty']),
      intval($request->parameters['level_id'])
    );
    ok_response('Updated succesfully', 'admin');
    break;
  case 'delete_level':
    Level::delete(
      intval($request->parameters['level_id'])
    );
    ok_response('Deleted succesfully', 'admin');
    break;
  case 'toggle_status_level':
    Level::setStatus(
      intval($request->parameters['level_id']),
      (bool)intval($request->parameters['status'])
    );
    ok_response('Success', 'admin');
    break;
  case 'toggle_status_all':
    if ($request->parameters['all_type'] === 'team') {
      Team::setStatusAll(
        (bool)intval($request->parameters['status'])
      );
      ok_response('Success', 'admin');
    } else {
      Level::setStatusAll(
        (bool)intval($request->parameters['status']),
        $request->parameters['all_type']
      );
      ok_response('Success', 'admin');
    }
    break;
  case 'create_team':
    $password_hash = Team::generateHash($request->parameters['password']);
    Team::create(
      $request->parameters['name'],
      $password_hash,
      $request->parameters['logo']
    );
    ok_response('Created succesfully', 'admin');
    break;
  case 'update_team':
    Team::update(
      $request->parameters['name'],
      $request->parameters['logo'],
      intval($request->parameters['points']),
      intval($request->parameters['team_id'])
    );
    if (strlen($request->parameters['password']) > 0) {
      $password_hash = Team::generateHash($request->parameters['password']);
      Team::updateTeamPassword(
        $password_hash,
        intval($request->parameters['team_id'])
      );
    }
    ok_response('Updated succesfully', 'admin');
    break;
  case 'toggle_admin_team':
    Team::setAdmin(
      intval($request->parameters['team_id']),
      (bool)intval($request->parameters['admin'])
    );
    ok_response('Success', 'admin');
    break;
  case 'toggle_status_team':
    Team::setStatus(
      intval($request->parameters['team_id']),
      (bool)intval($request->parameters['status'])
    );
    ok_response('Success', 'admin');
    break;
  case 'toggle_visible_team':
    Team::setVisible(
      intval($request->parameters['team_id']),
      (bool)intval($request->parameters['visible'])
    );
    ok_response('Success', 'admin');
    break;
  case 'enable_logo':
    Logo::setEnabled(
      intval($request->parameters['logo_id']),
      true
    );
    ok_response('Success', 'admin');
    break;
  case 'disable_logo':
    Logo::setEnabled(
      intval($request->parameters['logo_id']),
      false
    );
    ok_response('Success', 'admin');
    break;
  case 'enable_country':
    Country::setStatus(
      $request->parameters['country_id'],
      true,
    );
    ok_response('Success', 'admin');
    break;
  case 'disable_country':
    Country::setStatus(
      $request->parameters['country_id'],
      false,
    );
    ok_response('Success', 'admin');
    break;
  case 'delete_team':
    Team::delete(
      intval($request->parameters['team_id'])
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'update_session':
    sess_write(
      $request->parameters['cookie'],
      $request->parameters['data']
    );
    ok_response('Updated successfully', 'admin');
    break;
  case 'delete_session':
    sess_destroy(
      $request->parameters['cookie']
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'delete_category':
    Category::delete(
      intval($request->parameters['category_id'])
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'create_category':
    Category::create(
      $request->parameters['category']
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'create_attachment':
    $result = Attachment::create(
      'attachment_file',
      $request->parameters['filename'],
      intval($request->parameters['level_id']),
    );
    if ($result) {
      ok_response('Created successfully', 'admin');
    }
    break;
  case 'update_attachment':
    Attachment::update(
      $request->parameters['filename'],
      intval($request->parameters['level_id']),
    );
    ok_response('Updated successfully', 'admin');
    break;
  case 'delete_attachment':
    Attachment::delete(
      intval($request->parameters['attachment_id']),
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'create_link':
    Link::create(
      $request->parameters['link'],
      intval($request->parameters['level_id']),
    );
    ok_response('Created successfully', 'admin');
    break;
  case 'update_link':
    Link::update(
      $request->parameters['link'],
      intval($request->parameters['link_id']),
    );
    ok_response('Updated succesfully', 'admin');
    break;
  case 'delete_link':
    Link::delete(
      intval($request->parameters['link_id']),
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'change_configuration':
    $field = $request->parameters['field'];
    if (Configuration::validField($field)) {
      Configuration::update(
        $field,
        $request->parameters['value']
      );
      ok_response('Success', 'admin');
    } else {
      error_response('Invalid configuration', 'admin');
    }
    break;
  case 'create_announcement':
    $control = new Control();
    $control->new_announcement(
      $request->parameters['announcement']
    );
    ok_response('Success', 'admin');
    break;
  case 'delete_announcement':
    $control = new Control();
    $control->delete_announcement(
      $request->parameters['announcement_id']
    );
    ok_response('Success', 'admin');
    break;
  case 'create_tokens':
    $control = new Control();
    $control->create_tokens();
    ok_response('Success', 'admin');
    break;
  case 'export_tokens':
    $control = new Control();
    $control->export_tokens();
    ok_response('Success', 'admin');
    break;
  case 'begin_game':
    $control = new Control();
    $control->begin();
    ok_response('Success', 'admin');
    break;
  case 'end_game':
    $control = new Control();
    $control->end();
    ok_response('Success', 'admin');
    break;
  case 'backup_db':
    $control = new Control();
    $control->backup_db();
    ok_response('Success', 'admin');
    break;
  default:
    admin_page();
    break;
}
