<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class MapDataController extends DataController {
  public function generateData() {
    $levels = new Levels();

    $map_data = (object) array();

    $my_team = sess_team();
    $my_name = sess_teamname();

    foreach (Country::allEnabledCountries(true) as $country) {
      $active = ($country->getUsed() && Country::isActiveLevel($country->getId()))
              ? 'active'
              : '';
      $country_level = Country::whoUses($country->getId());
      if ($country_level) {
        if ($levels->previous_score($country_level['id'], $my_team)) {
          $captured_by = 'you';
          $data_captured = $my_name;
        } else if ($levels->previous_score($country_level['id'], $my_team, true)) {
          $captured_by = 'opponent';
          $completed_by = $levels->completed_by($country_level['id'])[0];
          $data_captured = $completed_by['name'];
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
      $map_data->{$country->getIsoCode()} = $country_data;
    }

    $this->jsonSend($map_data);
  }
}

$map = new MapDataController();
$map->generateData();