<?hh

sess_start();
sess_enforce_admin();

class AdminAjaxController extends AjaxController {
  <<__Override>>
  protected function getFilters(): array<string, mixed> {
    return array(
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
  }

  <<__Override>>
  protected function getActions(): array<string> {
    return array(
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
  }

  <<__Override>>
  protected function handleAction(string $action, array<string, mixed> $params): string {
    if ($action !== 'none') {
      // CSRF check
      if ($params['csrf_token'] !== sess_csrf_token()) {
        error_log('CSRF Token is invalid');
        error_page();
      }
    }

    switch ($action) {
    case 'none':
      admin_page();
      break;
    case 'create_quiz':
      $bonus = Configuration::get('default_bonus')->getValue();
      $bonus_dec = Configuration::get('default_bonusdec')->getValue();
      Level::createQuiz(
        $params['title'],
        $params['question'],
        $params['answer'],
        intval($params['entity_id']),
        intval($params['points']),
        intval($bonus),
        intval($bonus_dec),
        $params['hint'],
        intval($params['penalty'])
      );
      ok_response('Created succesfully', 'admin');
      break;
    case 'update_quiz':
      Level::updateQuiz(
        $params['title'],
        $params['question'],
        $params['answer'],
        intval($params['entity_id']),
        intval($params['points']),
        intval($params['bonus']),
        intval($params['bonus_dec']),
        $params['hint'],
        intval($params['penalty']),
        intval($params['level_id'])
      );
      ok_response('Updated succesfully', 'admin');
      break;
    case 'create_flag':
      $bonus = Configuration::get('default_bonus')->getValue();
      $bonus_dec = Configuration::get('default_bonusdec')->getValue();
      Level::createFlag(
        $params['title'],
        $params['description'],
        $params['flag'],
        intval($params['entity_id']),
        intval($params['category_id']),
        intval($params['points']),
        intval($bonus),
        intval($bonus_dec),
        $params['hint'],
        intval($params['penalty'])
      );
      ok_response('Created succesfully', 'admin');
      break;
    case 'update_flag':
      Level::updateFlag(
        $params['title'],
        $params['description'],
        $params['flag'],
        intval($params['entity_id']),
        intval($params['category_id']),
        intval($params['points']),
        intval($params['bonus']),
        intval($params['bonus_dec']),
        $params['hint'],
        intval($params['penalty']),
        intval($params['level_id'])
      );
      ok_response('Updated succesfully', 'admin');
      break;
    case 'create_base':
      $bonus = Configuration::get('default_bonus')->getValue();
      Level::createBase(
        $params['title'],
        $params['description'],
        intval($params['entity_id']),
        intval($params['category_id']),
        intval($params['points']),
        intval($bonus),
        $params['hint'],
        intval($params['penalty'])
      );
      ok_response('Created succesfully', 'admin');
      break;
    case 'update_base':
      Level::updateBase(
        $params['title'],
        $params['description'],
        intval($params['entity_id']),
        intval($params['category_id']),
        intval($params['points']),
        intval($params['bonus']),
        $params['hint'],
        intval($params['penalty']),
        intval($params['level_id'])
      );
      ok_response('Updated succesfully', 'admin');
      break;
    case 'delete_level':
      Level::delete(
        intval($params['level_id'])
      );
      ok_response('Deleted succesfully', 'admin');
      break;
    case 'toggle_status_level':
      Level::setStatus(
        intval($params['level_id']),
        (bool)intval($params['status'])
      );
      ok_response('Success', 'admin');
      break;
    case 'toggle_status_all':
      if ($params['all_type'] === 'team') {
        Team::setStatusAll(
          (bool)intval($params['status'])
        );
        ok_response('Success', 'admin');
      } else {
        Level::setStatusAll(
          (bool)intval($params['status']),
          $params['all_type']
        );
        ok_response('Success', 'admin');
      }
      break;
    case 'create_team':
      $password_hash = Team::generateHash($params['password']);
      Team::create(
        $params['name'],
        $password_hash,
        $params['logo']
      );
      ok_response('Created succesfully', 'admin');
      break;
    case 'update_team':
      Team::update(
        $params['name'],
        $params['logo'],
        intval($params['points']),
        intval($params['team_id'])
      );
      if (strlen($params['password']) > 0) {
        $password_hash = Team::generateHash($params['password']);
        Team::updateTeamPassword(
          $password_hash,
          intval($params['team_id'])
        );
      }
      ok_response('Updated succesfully', 'admin');
      break;
    case 'toggle_admin_team':
      Team::setAdmin(
        intval($params['team_id']),
        (bool)intval($params['admin'])
      );
      ok_response('Success', 'admin');
      break;
    case 'toggle_status_team':
      Team::setStatus(
        intval($params['team_id']),
        (bool)intval($params['status'])
      );
      ok_response('Success', 'admin');
      break;
    case 'toggle_visible_team':
      Team::setVisible(
        intval($params['team_id']),
        (bool)intval($params['visible'])
      );
      ok_response('Success', 'admin');
      break;
    case 'enable_logo':
      Logo::setEnabled(
        intval($params['logo_id']),
        true
      );
      ok_response('Success', 'admin');
      break;
    case 'disable_logo':
      Logo::setEnabled(
        intval($params['logo_id']),
        false
      );
      ok_response('Success', 'admin');
      break;
    case 'enable_country':
      Country::setStatus(
        $params['country_id'],
        true,
      );
      ok_response('Success', 'admin');
      break;
    case 'disable_country':
      Country::setStatus(
        $params['country_id'],
        false,
      );
      ok_response('Success', 'admin');
      break;
    case 'delete_team':
      Team::delete(
        intval($params['team_id'])
      );
      ok_response('Deleted successfully', 'admin');
      break;
    case 'update_session':
      sess_write(
        $params['cookie'],
        $params['data']
      );
      ok_response('Updated successfully', 'admin');
      break;
    case 'delete_session':
      sess_destroy(
        $params['cookie']
      );
      ok_response('Deleted successfully', 'admin');
      break;
    case 'delete_category':
      Category::delete(
        intval($params['category_id'])
      );
      ok_response('Deleted successfully', 'admin');
      break;
    case 'create_category':
      Category::create(
        $params['category']
      );
      ok_response('Deleted successfully', 'admin');
      break;
    case 'create_attachment':
      $result = Attachment::create(
        'attachment_file',
        $params['filename'],
        intval($params['level_id']),
      );
      if ($result) {
        ok_response('Created successfully', 'admin');
      }
      break;
    case 'update_attachment':
      Attachment::update(
        $params['filename'],
        intval($params['level_id']),
      );
      ok_response('Updated successfully', 'admin');
      break;
    case 'delete_attachment':
      Attachment::delete(
        intval($params['attachment_id']),
      );
      ok_response('Deleted successfully', 'admin');
      break;
    case 'create_link':
      Link::create(
        $params['link'],
        intval($params['level_id']),
      );
      ok_response('Created successfully', 'admin');
      break;
    case 'update_link':
      Link::update(
        $params['link'],
        intval($params['level_id']),
        intval($params['link_id']),
      );
      ok_response('Updated succesfully', 'admin');
      break;
    case 'delete_link':
      Link::delete(
        intval($params['link_id']),
      );
      ok_response('Deleted successfully', 'admin');
      break;
    case 'change_configuration':
      $field = $params['field'];
      if (Configuration::validField($field)) {
        Configuration::update(
          $field,
          $params['value']
        );
        ok_response('Success', 'admin');
      } else {
        error_response('Invalid configuration', 'admin');
      }
      break;
    case 'create_announcement':
      $control = new Control();
      $control->new_announcement(
        $params['announcement']
      );
      ok_response('Success', 'admin');
      break;
    case 'delete_announcement':
      $control = new Control();
      $control->delete_announcement(
        $params['announcement_id']
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

  }
}