<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ScoresDataController extends DataController {
  public function generateData() {
    $teams = new Teams();
    $conf = new Configuration();
    $data = array();
  
    foreach ($teams->leaderboard() as $team) {
      $values = array();
      $i = 1;
      foreach ($teams->progressive($team['name']) as $progress) {
        $score = (object) array(
          'time' => $i,
          'score' => $progress['points']
        );
        array_push($values, $score);
        $i++;
      }
      $color = substr(md5($team['name']), 0, 6);
      $element = (object) array(
        'team' => $team['name'],
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