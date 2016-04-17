<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class TeamDataController extends DataController {
  public function generateData() {
    $rank = 1;
    $leaderboard = Team::leaderboard();
    $teams_data = (object) array();

    // If refresing is disabled, exit
    if (Configuration::get('gameboard')->getValue() === '0') {
      $this->jsonSend($teams_data);
      exit;
    }

    foreach ($leaderboard as $team) {
      $team_data = (object) array(
        'badge' => $team->getLogo(),
        'team_members' => array(),
        'rank' => $rank,
        'points' => array(
          'base' => Team::pointsByType($team->getId(), 'base'),
          'quiz' => Team::pointsByType($team->getId(), 'quiz'),
          'flag' => Team::pointsByType($team->getId(), 'flag'),
          'total' => $team->getPoints()
        )
      );
      if ($team->getName()) {
        /* HH_FIXME[1002] */
        $teams_data->{$team->getName()} = $team_data;
      }
      $rank++;
    }

    $this->jsonSend($teams_data);
  }
}

$teamsData = new TeamDataController();
$teamsData->generateData();