<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class MapDataController extends DataController {
  public function generateData() {
    $map_data = (object) array();

    $my_team_id = intval(sess_team());
    $my_name = sess_teamname();

    foreach (Country::allEnabledCountries(true) as $country) {
      $active = ($country->getUsed() && Country::isActiveLevel($country->getId()))
              ? 'active'
              : '';
      $country_level = Level::whoUses($country->getId());
      if ($country_level) {
        // If my team has scored
        if (Level::previousScore($country_level->getId(), $my_team_id, false)) {
          $captured_by = 'you';
          $data_captured = $my_name;
        // If any other team has scored
        } else if (Level::previousScore($country_level->getId(), $my_team_id, true)) {
          $captured_by = 'opponent';
          $completed_by = Team::completedLevel($country_level->getId());
          $data_captured = '';
          foreach ($completed_by as $c) {
            $data_captured .= ' ' . $c->getName();
          }
        } else {
          $captured_by = 'no';
          $data_captured = 'no';
        }
      } else {
        $captured_by = 'no';
        $data_captured = 'no';
      }
      $country_data = (object) array(
        'status' => $active,
        'captured' => $captured_by,
        'datacaptured' => $data_captured
      );
      /* HH_FIXME[1002] */
      $map_data->{$country->getIsoCode()} = $country_data;
    }

    $this->jsonSend($map_data);
  }
}

$map = new MapDataController();
$map->generateData();