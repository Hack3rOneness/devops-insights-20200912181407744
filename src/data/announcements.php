<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class AnnouncementsDataController extends DataController {
  public function generateData() {
    $data = array();

    foreach (Announcement::allAnnouncements() as $announcement) {
      array_push($data, $announcement->getAnnouncement());
    }

    $this->jsonSend($data);
  }
}

$announcementsData = new AnnouncementsDataController();
$announcementsData->generateData();