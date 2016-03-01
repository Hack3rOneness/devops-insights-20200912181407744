<?hh

require_once('../common/gamerequests.php');
require_once('../common/levels.php');
require_once('../common/teams.php');
require_once('../common/sessions.php');
require_once('../common/utils.php');

sess_start();
sess_enforce_login();

$request = new GameRequests();
$request->processGame();

switch ($request->action) {
  case 'none':
    game_page();
    break;
  case 'answer_level':
    $levels = new Levels();
    // Check if answer is valid
    if ($levels->check_answer(
        $request->parameters['level_id'],
        $request->parameters['answer']
    )) {
      // Give points!
      $levels->score_level(
            $request->parameters['level_id'],
            sess_team()
        );
      // Update teams last score
      $teams = new Teams();
      $teams->last_score(sess_team());

        ok_response();
    } else {
      $levels->log_failed_score(
        $request->parameters['level_id'],
        sess_team(),
        $request->parameters['answer']
      );
        error_response();
    }
    break;
  case 'open_level':
    break;
  default:
    game_page();
    break;
}
