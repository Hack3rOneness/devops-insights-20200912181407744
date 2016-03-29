<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class TeamDataController extends DataController {
  public function generateData() {
    $teams = new Teams();
    $rank = 1;
    $leaderboard = $teams->leaderboard();
    $teams_data = (object) array();
    foreach ($leaderboard as $team) {
      $team_data = (object) array(
        'badge' => $team['logo'],
        'team_members' => array(),
        'rank' => $rank,
        'school_level' => 'collegiate',
        'points' => array(
          'base' => 0,
          'quiz' => 0,
          'flag' => 0,
          'total' => (int)$team['points']
        )
      );
      if ($team['name']) {
        $teams_data->{$team['name']} = $team_data;
      }
      $rank++;
    }

    $this->jsonSend($teams_data);
  }
}

$teamsData = new TeamDataController();
$teamsData->generateData();