<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ScoresDataController extends DataController {
  public function generateData() {
    $data = array();

    foreach (Team::leaderboard() as $team) {
      $values = array();
      $i = 1;
      foreach (Progressive::progressiveScoreboard($team->getName()) as $progress) {
        $score = (object) array(
          'time' => $i,
          'score' => $progress->getPoints()
        );
        array_push($values, $score);
        $i++;
      }
      $color = substr(md5($team->getName()), 0, 6);
      $element = (object) array(
        'team' => $team->getName(),
        'color' => '#'.$color,
        'values' => $values
      );
      array_push($data, $element);
    }

    $this->jsonSend($data);
  }
}

$scoresData = new ScoresDataController();
$scoresData->generateData();