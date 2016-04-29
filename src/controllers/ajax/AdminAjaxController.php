<?hh

SessionUtils::sessionStart();
SessionUtils::enforceLogin();

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
        'link'        => FILTER_UNSAFE_RAW,
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
      if (idx($params, 'csrf_token') !== SessionUtils::CSRFToken()) {
        return Utils::error_response('CSRF token is invalid', 'admin');
      }
    }

    switch ($action) {
    case 'none':
      return Utils::error_response('Invalid action', 'admin');
    case 'create_quiz':
      $bonus = Configuration::get('default_bonus')->getValue();
      $bonus_dec = Configuration::get('default_bonusdec')->getValue();
      Level::createQuiz(
        must_have_string($params, 'title'),
        must_have_string($params, 'question'),
        must_have_string($params, 'answer'),
        must_have_int($params, 'entity_id'),
        must_have_int($params, 'points'),
        intval($bonus),
        intval($bonus_dec),
        must_have_string($params, 'hint'),
        intval(must_have_idx($params, 'penalty')),
      );
      return Utils::ok_response('Created succesfully', 'admin');
    case 'update_quiz':
      Level::updateQuiz(
        must_have_string($params, 'title'),
        must_have_string($params, 'question'),
        must_have_string($params, 'answer'),
        must_have_int($params, 'entity_id'),
        must_have_int($params, 'points'),
        must_have_int($params, 'bonus'),
        must_have_int($params, 'bonus_dec'),
        must_have_string($params, 'hint'),
        intval(must_have_idx($params, 'penalty')),
        must_have_int($params, 'level_id'),
      );
      return Utils::ok_response('Updated succesfully', 'admin');
    case 'create_flag':
      $bonus = Configuration::get('default_bonus')->getValue();
      $bonus_dec = Configuration::get('default_bonusdec')->getValue();

      Level::createFlag(
        must_have_string($params, 'title'),
        must_have_string($params, 'description'),
        must_have_string($params, 'flag'),
        must_have_int($params, 'entity_id'),
        must_have_int($params, 'category_id'),
        must_have_int($params, 'points'),
        intval($bonus),
        intval($bonus_dec),
        must_have_string($params, 'hint'),
        intval(must_have_idx($params, 'penalty')),
      );
      return Utils::ok_response('Created succesfully', 'admin');
    case 'update_flag':
      Level::updateFlag(
        must_have_string($params, 'title'),
        must_have_string($params, 'description'),
        must_have_string($params, 'flag'),
        must_have_int($params, 'entity_id'),
        must_have_int($params, 'category_id'),
        must_have_int($params, 'points'),
        must_have_int($params, 'bonus'),
        must_have_int($params, 'bonus_dec'),
        must_have_string($params, 'hint'),
        intval(must_have_idx($params, 'penalty')),
        must_have_int($params, 'level_id'),
      );
      return Utils::ok_response('Updated succesfully', 'admin');
    case 'create_base':
      $bonus = Configuration::get('default_bonus')->getValue();
      Level::createBase(
        must_have_string($params, 'title'),
        must_have_string($params, 'description'),
        must_have_int($params, 'entity_id'),
        must_have_int($params, 'category_id'),
        must_have_int($params, 'points'),
        must_have_int($params, 'bonus'),
        must_have_string($params, 'hint'),
        intval(must_have_idx($params, 'penalty')),
      );
      return Utils::ok_response('Created succesfully', 'admin');
    case 'update_base':
      Level::updateBase(
        must_have_string($params, 'title'),
        must_have_string($params, 'description'),
        must_have_int($params, 'entity_id'),
        must_have_int($params, 'category_id'),
        must_have_int($params, 'points'),
        must_have_int($params, 'bonus'),
        must_have_string($params, 'hint'),
        intval(must_have_idx($params, 'penalty')),
        must_have_int($params, 'level_id'),
      );
      return Utils::ok_response('Updated succesfully', 'admin');
    case 'delete_level':
      Level::delete(
        must_have_int($params, 'level_id'),
      );
      return Utils::ok_response('Deleted succesfully', 'admin');
    case 'toggle_status_level':
      Level::setStatus(
        must_have_int($params, 'level_id'),
        must_have_int($params, 'status') === 1,
      );
      return Utils::ok_response('Success', 'admin');
    case 'toggle_status_all':
      if (must_have_string($params, 'all_type') === 'team') {
        Team::setStatusAll(
          must_have_int($params, 'status') === 1
        );
        return Utils::ok_response('Success', 'admin');
      } else {
        Level::setStatusAll(
          must_have_int($params, 'status') === 1,
          must_have_string($params, 'all_type'),
        );
        return Utils::ok_response('Success', 'admin');
      }
    case 'create_team':
      $password_hash = Team::generateHash(must_have_string($params, 'password'));
      Team::create(
        must_have_string($params, 'name'),
        $password_hash,
        must_have_string($params, 'logo'),
      );
      return Utils::ok_response('Created succesfully', 'admin');
    case 'update_team':
      Team::update(
        must_have_string($params, 'name'),
        must_have_string($params, 'logo'),
        must_have_int($params, 'points'),
        must_have_int($params, 'team_id'),
      );
      if (strlen(must_have_string($params, 'password')) > 0) {
        $password_hash = Team::generateHash(must_have_string($params, 'password'));
        Team::updateTeamPassword(
          $password_hash,
          must_have_int($params, 'team_id'),
        );
      }
      return Utils::ok_response('Updated succesfully', 'admin');
    case 'toggle_admin_team':
      Team::setAdmin(
        must_have_int($params, 'team_id'),
        must_have_int($params, 'admin') === 1,
      );
      return Utils::ok_response('Success', 'admin');
    case 'toggle_status_team':
      Team::setStatus(
        must_have_int($params, 'team_id'),
        must_have_int($params, 'status') === 1,
      );
      return Utils::ok_response('Success', 'admin');
    case 'toggle_visible_team':
      Team::setVisible(
        must_have_int($params, 'team_id'),
        must_have_int($params, 'visible') === 1,
      );
      return Utils::ok_response('Success', 'admin');
    case 'enable_logo':
      Logo::setEnabled(
        must_have_int($params, 'logo_id'),
        true,
      );
      return Utils::ok_response('Success', 'admin');
    case 'disable_logo':
      Logo::setEnabled(
        must_have_int($params, 'logo_id'),
        false,
      );
      return Utils::ok_response('Success', 'admin');
    case 'enable_country':
      Country::setStatus(
        must_have_int($params, 'country_id'),
        true,
      );
      return Utils::ok_response('Success', 'admin');
    case 'disable_country':
      Country::setStatus(
        must_have_int($params, 'country_id'),
        false,
      );
      return Utils::ok_response('Success', 'admin');
    case 'delete_team':
      Team::delete(
        must_have_int($params, 'team_id'),
      );
      return Utils::ok_response('Deleted successfully', 'admin');
    case 'update_session':
      Session::update(
        must_have_string($params, 'cookie'),
        must_have_string($params, 'data'),
      );
      return Utils::ok_response('Updated successfully', 'admin');
    case 'delete_session':
      Session::delete(
        must_have_string($params, 'cookie'),
      );
      return Utils::ok_response('Deleted successfully', 'admin');
    case 'delete_category':
      Category::delete(
        must_have_int($params, 'category_id'),
      );
      return Utils::ok_response('Deleted successfully', 'admin');
    case 'create_category':
      Category::create(
        must_have_string($params, 'category'),
      );
      return Utils::ok_response('Deleted successfully', 'admin');
    case 'create_attachment':
      $result = Attachment::create(
        'attachment_file',
        must_have_string($params, 'filename'),
        must_have_int($params, 'level_id'),
      );
      if ($result) {
        return Utils::ok_response('Created successfully', 'admin');
      } else {
        return ''; // TODO
      }
    case 'update_attachment':
      Attachment::update(
        must_have_int($params, 'attachment_id'),
        must_have_int($params, 'level_id'),
        must_have_string($params, 'filename'),
      );
      return Utils::ok_response('Updated successfully', 'admin');
    case 'delete_attachment':
      Attachment::delete(
        must_have_int($params, 'attachment_id'),
      );
      return Utils::ok_response('Deleted successfully', 'admin');
    case 'create_link':
      Link::create(
        must_have_string($params, 'link'),
        must_have_int($params, 'level_id'),
      );
      return Utils::ok_response('Created successfully', 'admin');
    case 'update_link':
      Link::update(
        must_have_string($params, 'link'),
        must_have_int($params, 'level_id'),
        must_have_int($params, 'link_id'),
      );
      return Utils::ok_response('Updated succesfully', 'admin');
    case 'delete_link':
      Link::delete(
        must_have_int($params, 'link_id'),
      );
      return Utils::ok_response('Deleted successfully', 'admin');
    case 'change_configuration':
      $field = must_have_string($params, 'field');
      if (Configuration::validField($field)) {
        Configuration::update(
          $field,
          must_have_string($params, 'value'),
        );
        return Utils::ok_response('Success', 'admin');
      } else {
        return Utils::error_response('Invalid configuration', 'admin');
      }
    case 'create_announcement':
      Announcement::create(
        must_have_string($params, 'announcement'),
      );
      return Utils::ok_response('Success', 'admin');
    case 'delete_announcement':
      Announcement::delete(
        must_have_int($params, 'announcement_id'),
      );
      return Utils::ok_response('Success', 'admin');
    case 'create_tokens':
      Token::create();
      return Utils::ok_response('Success', 'admin');
    case 'export_tokens':
      Token::export();
      return Utils::ok_response('Success', 'admin');
    case 'begin_game':
      Control::begin();
      return Utils::ok_response('Success', 'admin');
    case 'end_game':
      Control::end();
      return Utils::ok_response('Success', 'admin');
    case 'backup_db':
      Control::backupDb();
      return Utils::ok_response('Success', 'admin');
    default:
      return Utils::error_response('Invalid action', 'admin');
    }
  }
}