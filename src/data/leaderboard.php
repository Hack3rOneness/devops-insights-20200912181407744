<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

SessionUtils::sessionStart();
SessionUtils::enforceLogin();

class LeaderboardDataController extends DataController {
  public function generateData() {
    $leaderboard_data = (object) array();

    // If refresing is disabled, exit
    if (Configuration::get('gameboard')->getValue() === '0') {
      $this->jsonSend($leaderboard_data);
      exit;
    }

    $leaders = Team::leaderboard();
    $my_team = Team::getTeam(SessionUtils::sessionTeam());
    $my_rank = Team::myRank(SessionUtils::sessionTeam());
    $my_team_data = (object) array(
      'badge' => $my_team->getLogo(),
      'points' => $my_team->getPoints(),
      'rank' => $my_rank
    );
    /* HH_FIXME[1002] */
    $leaderboard_data->{'my_team'} = $my_team_data;

    $teams_data = (object) array();
    $rank = 1;
    $l_max = (count($leaders) > 5) ? 5 : count($leaders);
    for($i = 0; $i<$l_max; $i++) {
      $team = $leaders[$i];
      $team_data = (object) array(
        'badge' => $team->getLogo(),
        'points' => $team->getPoints(),
        'rank' => $rank
      );
      if ($team->getName()) {
        $teams_data->{$team->getName()} = $team_data;
      }
      $rank++;
    }
    $leaderboard_data->{'leaderboard'} = $teams_data;

    $this->jsonSend($leaderboard_data);
  }
}

$leaderboardData = new LeaderboardDataController();
$leaderboardData->generateData();