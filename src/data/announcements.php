<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

SessionUtils::sessionStart();
SessionUtils::enforceLogin();

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