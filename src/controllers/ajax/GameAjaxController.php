<?hh // strict

class GameAjaxController extends AjaxController {
  <<__Override>>
  protected function getFilters(): array<string, mixed> {
    return array(
      'POST' => array(
        'level_id' => FILTER_VALIDATE_INT,
        'answer' => FILTER_UNSAFE_RAW,
        'csrf_token' => FILTER_UNSAFE_RAW,
        'livesync_username' => FILTER_UNSAFE_RAW,
        'livesync_password' => FILTER_UNSAFE_RAW,
        'team_name' => FILTER_UNSAFE_RAW,
        'team_password' => FILTER_UNSAFE_RAW,
        'new_password' => FILTER_UNSAFE_RAW,
        'action' => array(
          'filter' => FILTER_VALIDATE_REGEXP,
          'options' => array('regexp' => '/^[\w-]+$/'),
        ),
        'page' => array(
          'filter' => FILTER_VALIDATE_REGEXP,
          'options' => array('regexp' => '/^[\w-]+$/'),
        ),
      ),
    );
  }

  <<__Override>>
  protected function getActions(): array<string> {
    return array('answer_level', 'get_hint', 'open_level');
  }

  <<__Override>>
  protected async function genHandleAction(
    string $action,
    array<string, mixed> $params,
  ): Awaitable<string> {
    if ($action !== 'none') {
      // CSRF check
      if (idx($params, 'csrf_token') !== SessionUtils::CSRFToken()) {
        return Utils::error_response('CSRF token is invalid', 'game');
      }
    }

    switch ($action) {
      case 'none':
        return Utils::error_response('Invalid action', 'game');
      case 'answer_level':
        $scoring = await Configuration::gen('scoring');
        if ($scoring->getValue() === '1') {
          $level_id = must_have_int($params, 'level_id');
          $answer = must_have_string($params, 'answer');
          list($check_base, $check_status, $check_answer) =
            await \HH\Asio\va(
              Level::genCheckBase($level_id),
              Level::genCheckStatus($level_id),
              Level::genCheckAnswer($level_id, $answer),
            );
          // Check if level is not a base or if level isn't active
          if ($check_base || !$check_status) {
            return Utils::error_response('Failed', 'game');
            // Check if answer is valid
          } else if ($check_answer) {
            // Give points and update last score for team
            $check_answered = await Level::genScoreLevel($level_id, SessionUtils::sessionTeam());
            if (!$check_answered) {
              return Utils::error_response('MChoice Failed', 'game');
            }
            MultiTeam::invalidateMCRecords();
            return Utils::ok_response('Success', 'game');
          } else {
            await FailureLog::genLogFailedScore(
              $level_id,
              SessionUtils::sessionTeam(),
              $answer,
            );
            $level = await Level::gen($level_id);
            $type = $level->getType();
            if ($type === "mchoice") {
              await \HH\Asio\va(
                Level::genScoreLevel($level_id, SessionUtils::sessionTeam(), false)
              );
              return Utils::error_response('MChoice Failed', 'game');
            }
            return Utils::error_response('Failed', 'game');
          }
        } else {
          return Utils::error_response('Failed', 'game');
        }
      case 'get_hint':
        $requested_hint = await Level::genLevelHint(
          must_have_int($params, 'level_id'),
          SessionUtils::sessionTeam(),
        );
        if ($requested_hint !== null) {
          MultiTeam::invalidateMCRecords('ALL_TEAMS'); // Invalidate Memcached MultiTeam data.
          MultiTeam::invalidateMCRecords('POINTS_BY_TYPE'); // Invalidate Memcached MultiTeam data.
          MultiTeam::invalidateMCRecords('LEADERBOARD'); // Invalidate Memcached MultiTeam data.
          return Utils::hint_response($requested_hint, 'OK');
        } else {
          return Utils::hint_response('', 'ERROR');
        }
      case 'open_level':
        return Utils::ok_response('Success', 'admin');
      case 'set_team_name':
        $updated_team_name = await Team::genSetTeamName(
          SessionUtils::sessionTeam(),
          must_have_string($params, 'team_name'),
        );
        if ($updated_team_name === true) {
          return Utils::ok_response('Success', 'game');
        } else {
          return Utils::error_response('Failed', 'game');
        }
      case 'set_team_password':
        $team_id = SessionUtils::sessionTeam();
        $new_pw = must_have_string($params, 'new_password');
        $verify = await Team::genVerifyCredentials(
          $team_id,
          must_have_string($params, 'team_password')
        );
        $strong_pw = await Configuration::gen('login_strongpasswords');
        $pw_pass = true;
        
        if ($strong_pw->getValue() !== '0') {
          $password_type = await Configuration::genCurrentPasswordType();
          if (!preg_match(strval($password_type->getValue()), $new_pw)) {
            $pw_pass = false;
          }
        }

        if ($verify !== null && $new_pw !== '' && $pw_pass) {
          $password_hash =
            Team::generateHash($new_pw);
          await Team::genUpdateTeamPassword($password_hash, $team_id);
          return Utils::ok_response('Success', 'game');
        } elseif ($verify !== null && $new_pw === '') {
          return Utils::error_response('PW Error', 'game');
        } elseif ($verify !== null && !$pw_pass) {
          return Utils::error_response('Password too simple', 'game');
        } else {
          return Utils::error_response('Failed', 'game');
        }
      case 'set_livesync_password':
        $livesync_password_update = await Team::genSetLiveSyncPassword(
          SessionUtils::sessionTeam(),
          "fbctf",
          must_have_string($params, 'livesync_username'),
          must_have_string($params, 'livesync_password'),
        );
        if ($livesync_password_update === true) {
          return Utils::ok_response('Success', 'game');
        } else {
          return Utils::error_response('Failed', 'game');
        }
      default:
        return Utils::error_response('Invalid action', 'game');
    }
  }
}
