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
      if (idx($params, 'csrf_token') !== sess_csrf_token()) {
        error_log('CSRF Token is invalid');
        error_page();
      }
    }

    switch ($action) {
    case 'none':
      admin_page();
      return ''; // TODO
    case 'create_quiz':
      $bonus = Configuration::get('default_bonus')->getValue();
      $bonus_dec = Configuration::get('default_bonusdec')->getValue();

      Level::createQuiz(
        must_have_string($params, 'title'),
        must_have_string($params, 'question'),
        must_have_string($params, 'answer'),
        intval(must_have_string($params, 'entity_id')),
        intval(must_have_string($params, 'points')),
        intval($bonus),
        intval($bonus_dec),
        must_have_string($params, 'hint'),
        intval(must_have_string($params, 'penalty')),
      );
      return ok_response('Created succesfully', 'admin');
    case 'update_quiz':
      Level::updateQuiz(
        must_have_string($params, 'title'),
        must_have_string($params, 'question'),
        must_have_string($params, 'answer'),
        intval(must_have_string($params, 'entity_id')),
        intval(must_have_string($params, 'points')),
        intval(must_have_string($params, 'bonus')),
        intval(must_have_string($params, 'bonus_dec')),
        must_have_string($params, 'hint'),
        intval(must_have_string($params, 'penalty')),
        intval(must_have_string($params, 'level_id'))
      );
      return ok_response('Updated succesfully', 'admin');
    case 'create_flag':
      $bonus = Configuration::get('default_bonus')->getValue();
      $bonus_dec = Configuration::get('default_bonusdec')->getValue();

      Level::createFlag(
        must_have_string($params, 'title'),
        must_have_string($params, 'description'),
        must_have_string($params, 'flag'),
        intval(must_have_string($params, 'entity_id')),
        intval(must_have_string($params, 'category_id')),
        intval(must_have_string($params, 'points')),
        intval($bonus),
        intval($bonus_dec),
        must_have_string($params, 'hint'),
        intval(must_have_string($params, 'penalty'))
      );
      return ok_response('Created succesfully', 'admin');
    case 'update_flag':
      Level::updateFlag(
        must_have_string($params, 'title'),
        must_have_string($params, 'description'),
        must_have_string($params, 'flag'),
        intval(must_have_string($params, 'entity_id')),
        intval(must_have_string($params, 'category_id')),
        intval(must_have_string($params, 'points')),
        intval(must_have_string($params, 'bonus')),
        intval(must_have_string($params, 'bonus_dec')),
        must_have_string($params, 'hint'),
        intval(must_have_string($params, 'penalty')),
        intval(must_have_string($params, 'level_id'))
      );
      return ok_response('Updated succesfully', 'admin');
    case 'create_base':
      $bonus = Configuration::get('default_bonus')->getValue();
      Level::createBase(
        must_have_string($params, 'title'),
        must_have_string($params, 'description'),
        intval(must_have_string($params, 'entity_id')),
        intval(must_have_string($params, 'category_id')),
        intval(must_have_string($params, 'points')),
        intval($bonus),
        must_have_string($params, 'hint'),
        intval(must_have_string($params, 'penalty'))
      );
      return ok_response('Created succesfully', 'admin');
    case 'update_base':
      Level::updateBase(
        must_have_string($params, 'title'),
        must_have_string($params, 'description'),
        intval(must_have_string($params, 'entity_id')),
        intval(must_have_string($params, 'category_id')),
        intval(must_have_string($params, 'points')),
        intval(must_have_string($params, 'bonus')),
        must_have_string($params, 'hint'),
        intval(must_have_string($params, 'penalty')),
        intval(must_have_string($params, 'level_id'))
      );
      return ok_response('Updated succesfully', 'admin');
    case 'delete_level':
      Level::delete(
        intval(must_have_string($params, 'level_id'))
      );
      return ok_response('Deleted succesfully', 'admin');
    case 'toggle_status_level':
      Level::setStatus(
        intval(must_have_string($params, 'level_id')),
        (bool)intval(must_have_string($params, 'status'))
      );
      return ok_response('Success', 'admin');
    case 'toggle_status_all':
      if (must_have_string($params, 'all_type') === 'team') {
        Team::setStatusAll(
          (bool)intval(must_have_string($params, 'status'))
        );
        return ok_response('Success', 'admin');
      } else {
        Level::setStatusAll(
          (bool)intval(must_have_string($params, 'status')),
          must_have_string($params, 'all_type')
        );
        return ok_response('Success', 'admin');
      }
    case 'create_team':
      $password_hash = Team::generateHash(must_have_string($params, 'password'));
      Team::create(
        must_have_string($params, 'name'),
        $password_hash,
        must_have_string($params, 'logo')
      );
      return ok_response('Created succesfully', 'admin');
    case 'update_team':
      Team::update(
        must_have_string($params, 'name'),
        must_have_string($params, 'logo'),
        intval(must_have_string($params, 'points')),
        intval(must_have_string($params, 'team_id'))
      );
      if (strlen(must_have_string($params, 'password')) > 0) {
        $password_hash = Team::generateHash(must_have_string($params, 'password'));
        Team::updateTeamPassword(
          $password_hash,
          intval(must_have_string($params, 'team_id'))
        );
      }
      return ok_response('Updated succesfully', 'admin');
    case 'toggle_admin_team':
      Team::setAdmin(
        intval(must_have_string($params, 'team_id')),
        (bool)intval(must_have_string($params, 'admin'))
      );
      return ok_response('Success', 'admin');
    case 'toggle_status_team':
      Team::setStatus(
        intval(must_have_string($params, 'team_id')),
        (bool)intval(must_have_string($params, 'status'))
      );
      return ok_response('Success', 'admin');
    case 'toggle_visible_team':
      Team::setVisible(
        intval(must_have_string($params, 'team_id')),
        (bool)intval(must_have_string($params, 'visible'))
      );
      return ok_response('Success', 'admin');
    case 'enable_logo':
      Logo::setEnabled(
        intval(must_have_string($params, 'logo_id')),
        true
      );
      return ok_response('Success', 'admin');
    case 'disable_logo':
      Logo::setEnabled(
        intval(must_have_string($params, 'logo_id')),
        false
      );
      return ok_response('Success', 'admin');
    case 'enable_country':
      Country::setStatus(
        intval(must_have_string($params, 'country_id')),
        true,
      );
      return ok_response('Success', 'admin');
    case 'disable_country':
      Country::setStatus(
        intval(must_have_string($params, 'country_id')),
        false,
      );
      return ok_response('Success', 'admin');
    case 'delete_team':
      Team::delete(
        intval(must_have_string($params, 'team_id'))
      );
      return ok_response('Deleted successfully', 'admin');
    case 'update_session':
      sess_write(
        must_have_string($params, 'cookie'),
        must_have_string($params, 'data')
      );
      return ok_response('Updated successfully', 'admin');
    case 'delete_session':
      sess_destroy(
        must_have_string($params, 'cookie')
      );
      return ok_response('Deleted successfully', 'admin');
    case 'delete_category':
      Category::delete(
        intval(must_have_string($params, 'category_id'))
      );
      return ok_response('Deleted successfully', 'admin');
    case 'create_category':
      Category::create(
        must_have_string($params, 'category')
      );
      return ok_response('Deleted successfully', 'admin');
    case 'create_attachment':
      $result = Attachment::create(
        'attachment_file',
        must_have_string($params, 'filename'),
        intval(must_have_string($params, 'level_id')),
      );
      if ($result) {
        return ok_response('Created successfully', 'admin');
      } else {
        return ''; // TODO
      }
    case 'update_attachment':
      Attachment::update(
        intval(must_have_idx($params, 'attachment_id')),
        intval(must_have_string($params, 'level_id')),
        must_have_string($params, 'filename'),
      );
      return ok_response('Updated successfully', 'admin');
    case 'delete_attachment':
      Attachment::delete(
        intval(must_have_string($params, 'attachment_id')),
      );
      return ok_response('Deleted successfully', 'admin');
    case 'create_link':
      Link::create(
        must_have_string($params, 'link'),
        intval(must_have_string($params, 'level_id')),
      );
      return ok_response('Created successfully', 'admin');
    case 'update_link':
      Link::update(
        must_have_string($params, 'link'),
        intval(must_have_string($params, 'level_id')),
        intval(must_have_string($params, 'link_id')),
      );
      return ok_response('Updated succesfully', 'admin');
    case 'delete_link':
      Link::delete(
        intval(must_have_string($params, 'link_id')),
      );
      return ok_response('Deleted successfully', 'admin');
    case 'change_configuration':
      $field = must_have_string($params, 'field');
      if (Configuration::validField($field)) {
        Configuration::update(
          $field,
          must_have_string($params, 'value')
        );
        return ok_response('Success', 'admin');
      } else {
        return error_response('Invalid configuration', 'admin');
      }
    case 'create_announcement':
      $control = new Control();
      $control->new_announcement(
        must_have_string($params, 'announcement')
      );
      return ok_response('Success', 'admin');
    case 'delete_announcement':
      $control = new Control();
      $control->delete_announcement(
        must_have_string($params, 'announcement_id')
      );
      return ok_response('Success', 'admin');
    case 'create_tokens':
      $control = new Control();
      $control->create_tokens();
      return ok_response('Success', 'admin');
    case 'export_tokens':
      $control = new Control();
      $control->export_tokens();
      return ok_response('Success', 'admin');
    case 'begin_game':
      $control = new Control();
      $control->begin();
      return ok_response('Success', 'admin');
    case 'end_game':
      $control = new Control();
      $control->end();
      return ok_response('Success', 'admin');
    case 'backup_db':
      $control = new Control();
      $control->backup_db();
      return ok_response('Success', 'admin');
    default:
      admin_page();
      return '';
    }

  }
}