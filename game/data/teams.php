<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class TeamDataController extends DataController {
  public function generateData() {
    $teams = new Teams();
    $conf = new Configuration();
    $rank = 1;
    $leaderboard = $teams->leaderboard();
    $teams_data = (object) array();

    // If refresing is disabled, exit
    if ($conf->get('teams') === '0') {
      $this->jsonSend($teams_data);
      exit;
    }
    
    foreach ($leaderboard as $team) {
      $team_data = (object) array(
        'badge' => $team['logo'],
        'team_members' => array(),
        'rank' => $rank,
        'points' => array(
          'base' => (int)$teams->points_by_type($team['id'], 'base'),
          'quiz' => (int)$teams->points_by_type($team['id'], 'quiz'),
          'flag' => (int)$teams->points_by_type($team['id'], 'flag'),
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