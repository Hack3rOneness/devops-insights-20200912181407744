<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class LeaderboardDataController extends DataController {
  public function generateData() {
    $teams = new Teams();
    $conf = new Configuration();

    $leaders = $teams->leaderboard();
    $my_team = $teams->get_team(sess_team());
    $my_rank = $teams->my_rank(sess_team());


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

$leaderboardData = new LeaderboardDataController();
$leaderboardData->generateData();