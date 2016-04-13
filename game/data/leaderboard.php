<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class LeaderboardDataController extends DataController {
  public function generateData() {
    $leaderboard_data = (object) array();
    
    // If refresing is disabled, exit
    if (Configuration::get('gameboard')->getValue() === '0') {
      $this->jsonSend($leaderboard_data);
      exit;
    }

    $teams = new Teams();
    $leaders = $teams->leaderboard();
    $my_team = $teams->get_team(sess_team());
    $my_rank = $teams->my_rank(sess_team());
    $my_team_data = (object) array(
      'badge' => $my_team['logo'],
      'points' => (int)$my_team['points'],
      'rank' => $my_rank
    );
    $leaderboard_data->{'my_team'} = $my_team_data;

    $teams_data = (object) array();
    $rank = 1;
    $l_max = (sizeof($leaders) > 5) ? 5 : sizeof($leaders);
    for($i = 0; $i<$l_max; $i++) {
      $team = $leaders[$i];
      $team_data = (object) array(
        'badge' => $team['logo'],
        'points' => (int)$team['points'],
        'rank' => $rank
      );
      if ($team['name']) {
        $teams_data->{$team['name']} = $team_data;
      }
      $rank++;
    }
    $leaderboard_data->{'leaderboard'} = $teams_data;

    $this->jsonSend($leaderboard_data);
  }
}

$leaderboardData = new LeaderboardDataController();
$leaderboardData->generateData();