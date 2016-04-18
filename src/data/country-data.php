<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class CountryDataController extends DataController {
  public function generateData() {
    $my_team = Team::getTeam(intval(sess_team()));

    $countries_data = (object) array();

    // If gameboard refresing is disabled, exit
    if (Configuration::get('gameboard')->getValue() === '0') {
      $this->jsonSend($countries_data);
      exit;
    }

    foreach (Level::allActiveLevels() as $level) {
      $country = Country::get(intval($level->getEntityId()));
      if (!$country) {
        continue;
      }

      $category = Category::getSingleCategory($level->getCategoryId());
      if (count($level->getHint()) > 0) {
        // There is hint, can this team afford it?
        if ($level->getPenalty() > $my_team->getPoints()) { // Not enough points
          $hint_cost = -2;
          $hint = 'no';
        } else {
          // Has this team requested this hint or scored this level before?
          if (
            (Level::previousHint($level->getId(), $my_team->getId(), false))
              ||
            (Level::previousScore($level->getId(), $my_team->getId(), false))
          ) {
            $hint_cost = 0;
          } else {
            $hint_cost = $level->getPenalty();
          }
          $hint = ($hint_cost === 0) ? $level->getHint() : 'yes';
        }
      } else { // No hints
        $hint_cost = -1;
        $hint = 'no';
      }

      // All attachments for this level
      $attachments_list = array();
      if (Attachment::hasAttachments($level->getId())) {
        foreach (Attachment::allAttachments($level->getId()) as $attachment) {
          array_push($attachments_list, $attachment->getFilename());
        }
      }

      // All links for this level
      $links_list = array();
      if (Link::hasLinks($level->getId())) {
        foreach (Link::allLinks($level->getId()) as $link) {
          array_push($links_list, $link->getLink());
        }
      }

      // All teams that have completed this level
      $completed_by = array();
      foreach (Team::completedLevel($level->getId()) as $c) {
        array_push($completed_by, $c->getName());
      }

      // Who is the first owner of this level
      $owner = ($completed_by) ? $completed_by[0] : 'Uncaptured';
      $country_data = (object) array(
        'level_id'    => $level->getId(),
        'title'       => $level->getTitle(),
        'intro'       => $level->getDescription(),
        'type'        => $level->getType(),
        'points'      => $level->getPoints(),
        'bonus'       => $level->getBonus(),
        'category'    => $category->getCategory(),
        'owner'       => $owner,
        'completed'   => $completed_by,
        'hint'        => $hint,
        'hint_cost'   => $hint_cost,
        'attachments' => $attachments_list,
        'links'       => $links_list
      );
      /* HH_FIXME[1002] */
      $countries_data->{$country->getName()} = $country_data;
    }

    $this->jsonSend($countries_data);
  }
}

$countryData = new CountryDataController();
$countryData->generateData();