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
  'end_game',
  'reset_game'
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
    $levels = new Levels();
    $c = new Configuration();
    $bonus = $c->get('default_bonus');
    $bonus_dec = $c->get('default_bonusdec');
    $levels->create_quiz_level(
      $request->parameters['question'],
      $request->parameters['answer'],
      $request->parameters['entity_id'],
      $request->parameters['points'],
      $bonus,
      $bonus_dec,
      $request->parameters['hint'],
      $request->parameters['penalty']
    );
    ok_response('Created succesfully', 'admin');
    break;
  case 'update_quiz':
    $levels = new Levels();
    $levels->update_quiz_level(
      $request->parameters['question'],
      $request->parameters['answer'],
      $request->parameters['entity_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['bonus_dec'],
      $request->parameters['hint'],
      $request->parameters['penalty'],
      $request->parameters['level_id']
    );
    ok_response('Updated succesfully', 'admin');
    break;
  case 'create_flag':
    $levels = new Levels();
    $c = new Configuration();
    $bonus = $c->get('default_bonus');
    $bonus_dec = $c->get('default_bonusdec');
    $levels->create_flag_level(
      $request->parameters['description'],
      $request->parameters['flag'],
      $request->parameters['entity_id'],
      $request->parameters['category_id'],
      $request->parameters['points'],
      $bonus,
      $bonus_dec,
      $request->parameters['hint'],
      $request->parameters['penalty']
    );
    ok_response('Created succesfully', 'admin');
    break;
  case 'update_flag':
    $levels = new Levels();
    $levels->update_flag_level(
      $request->parameters['description'],
      $request->parameters['flag'],
      $request->parameters['entity_id'],
      $request->parameters['category_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['bonus_dec'],
      $request->parameters['hint'],
      $request->parameters['penalty'],
      $request->parameters['level_id']
    );
    ok_response('Updated succesfully', 'admin');
    break;
  case 'create_base':
    $levels = new Levels();
    $c = new Configuration();
    $bonus = $c->get('default_bonus');
    $levels->create_base_level(
      $request->parameters['description'],
      $request->parameters['entity_id'],
      $request->parameters['category_id'],
      $request->parameters['points'],
      $bonus,
      $request->parameters['hint'],
      $request->parameters['penalty']
    );
    ok_response('Created succesfully', 'admin');
    break;
  case 'update_base':
    $levels = new Levels();
    $levels->update_base_level(
      $request->parameters['description'],
      $request->parameters['entity_id'],
      $request->parameters['category_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['hint'],
      $request->parameters['penalty'],
      $request->parameters['level_id']
    );
    ok_response('Updated succesfully', 'admin');
    break;
  case 'delete_level':
    $levels = new Levels();
    $levels->delete_level(
      $request->parameters['level_id']
    );
    ok_response('Deleted succesfully', 'admin');
    break;
  case 'toggle_status_level':
    $levels = new Levels();
    $levels->toggle_status(
      $request->parameters['level_id'],
      $request->parameters['status']
    );
    ok_response('Success', 'admin');
    break;
  case 'toggle_status_all':
    if ($request->parameters['all_type'] === 'team') {
      $teams = new Teams();
      $teams->toggle_status_all(
        $request->parameters['status']
      );
      ok_response('Success', 'admin');
    } else {
      $levels = new Levels();
      $levels->toggle_status_all(
        $request->parameters['status'],
        $request->parameters['all_type']
      );
      ok_response('Success', 'admin');
    }
    break;
  case 'create_team':
    $teams = new Teams();
    $password_hash = $teams->generate_hash($request->parameters['password']);
    $teams->create_team(
      $request->parameters['name'],
      $password_hash,
      $request->parameters['logo']
    );
    ok_response('Created succesfully', 'admin');
    break;
  case 'update_team':
    $teams = new Teams();
    $teams->update_team(
      $request->parameters['name'],
      $request->parameters['logo'],
      $request->parameters['points'],
      $request->parameters['team_id']
    );
    if (strlen($request->parameters['password']) > 0) {
      $password_hash = $teams->generate_hash($request->parameters['password']);
      $teams->update_team_password(
        $password_hash,
        $request->parameters['team_id']
      );
    }
    ok_response('Updated succesfully', 'admin');
    break;
  case 'toggle_admin_team':
    $teams = new Teams();
    $teams->toggle_admin(
      $request->parameters['team_id'],
      $request->parameters['admin']
    );
    ok_response('Success', 'admin');
    break;
  case 'toggle_status_team':
    $teams = new Teams();
    $teams->toggle_status(
      $request->parameters['team_id'],
      $request->parameters['status']
    );
    ok_response('Success', 'admin');
    break;
  case 'toggle_visible_team':
    $teams = new Teams();
    $teams->toggle_visible(
      $request->parameters['team_id'],
      $request->parameters['visible']
    );
    ok_response('Success', 'admin');
    break;
  case 'enable_logo':
    $logos = new Logos();
    $logos->toggle_status(
      $request->parameters['logo_id'],
      1
    );
    ok_response('Success', 'admin');
    break;
  case 'disable_logo':
    $logos = new Logos();
    $logos->toggle_status(
      $request->parameters['logo_id'],
      0
    );
    ok_response('Success', 'admin');
    break;
  case 'enable_country':
    $countries = new Countries();
    $countries->toggle_status(
      $request->parameters['country_id'],
      1
    );
    ok_response('Success', 'admin');
    break;
  case 'disable_country':
    $countries = new Countries();
    $countries->toggle_status(
      $request->parameters['country_id'],
      0
    );
    ok_response('Success', 'admin');
    break;
  case 'delete_team':
    $teams = new Teams();
    $teams->delete_team(
      $request->parameters['team_id']
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
    $levels = new Levels();
    $levels->delete_category(
      $request->parameters['category_id']
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'create_category':
    $levels = new Levels();
    $levels->create_category(
      $request->parameters['category']
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'create_attachment':
    $attachments = new Attachments();
    $result = $attachments->create(
      'attachment_file',
      $request->parameters['filename'],
      $request->parameters['level_id']
    );
    if ($result) {
      ok_response('Created successfully', 'admin');
    } 
    break;
  case 'update_attachment':
    $attachments = new Attachments();
    $attachments->update(
      $request->parameters['filename'],
      $request->parameters['level_id']
    );
    ok_response('Updated successfully', 'admin');
    break;
  case 'delete_attachment':
    $attachments = new Attachments();
    $attachments->delete(
      $request->parameters['attachment_id']
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'create_link':
    $links = new Links();
    $result = $links->create(
      $request->parameters['link'],
      $request->parameters['level_id']
    );
    if ($result) {
      ok_response('Created successfully', 'admin');
    }
    break;
  case 'update_link':
    $links = new Links();
    $links->update(
      $request->parameters['link'],
      $request->parameters['link_id']
    );
    ok_response('Updated succesfully', 'admin');
    break;
  case 'delete_link':
    $links = new Links();
    $links->delete(
      $request->parameters['link_id']
    );
    ok_response('Deleted successfully', 'admin');
    break;
  case 'change_configuration':
    $conf = new Configuration();
    $field = $request->parameters['field'];
    if ($conf->valid_field($field)) {
      $conf->change(
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
  default:
    admin_page();
    break;
}
