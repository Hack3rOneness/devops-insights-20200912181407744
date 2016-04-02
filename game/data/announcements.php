<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class AnnouncementsDataController extends DataController {
  public function generateData() {
    $teams = new Teams();
    $control = new Control();
    $data = array();
  
    foreach ($control->all_announcements() as $announcement) {
      array_push($data, $announcement['announcement']);
    }

    $this->jsonSend($data);
  }
}

$announcementsData = new AnnouncementsDataController();
$announcementsData->generateData();