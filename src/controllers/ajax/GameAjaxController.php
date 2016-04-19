<?hh

sess_start();
sess_enforce_login();

class GameAjaxController extends AjaxController {
  <<__Override>>
  protected function getFilters(): array<string, mixed> {
    return array(
      'POST' => array(
        'level_id'    => FILTER_VALIDATE_INT,
        'answer'      => FILTER_UNSAFE_RAW,
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
      'answer_level',
      'get_hint',
      'open_level',
    );
  }

  <<__Override>>
  protected function handleAction(string $action, array<string, mixed> $params): string {
    if ($action !== 'none') {
      // CSRF check
      if (idx($params, 'csrf_token') !== sess_csrf_token()) {
        error_page();
      }
    }

    switch ($action) {
    case 'none':
      game_page();
      return ''; // TODO
    case 'answer_level':
      if (Configuration::get('scoring')->getValue() === '1') {
        // Check if answer is valid
        if (Level::checkAnswer(
          intval(must_have_string($params, 'level_id')),
          must_have_string($params, 'answer')
        )) {
          // Give points!
          Level::scoreLevel(
            intval(must_have_string($params, 'level_id')),
            intval(sess_team())
          );
          // Update teams last score
          Team::lastScore(intval(sess_team()));
          return ok_response('Success', 'game');
        } else {
          Level::logFailedScore(
            intval(must_have_string($params, 'level_id')),
            intval(sess_team()),
            must_have_string($params, 'answer')
          );
          return error_response('Failed', 'game');
        }
      } else {
        return error_response('Failed', 'game');
      }
    case 'get_hint':
      $requested_hint = Level::getLevelHint(
        intval(must_have_string($params, 'level_id')),
        intval(sess_team())
      );
      if ($requested_hint) {
        return hint_response($requested_hint, 'OK');
      } else {
        return hint_response('', 'ERROR');
      }
    case 'open_level':
      return ok_response('Success', 'admin');
    default:
      game_page();
      return ''; // TODO
    }
  }
}
