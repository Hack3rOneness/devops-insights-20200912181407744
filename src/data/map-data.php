<?hh // strict

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

/* HH_IGNORE_ERROR[1002] */
SessionUtils::sessionStart();
SessionUtils::enforceLogin();

class MapDataController extends DataController {
  public async function genGenerateData(): Awaitable<void> {
    $map_data = (object) array();

    $my_team_id = SessionUtils::sessionTeam();
    $my_name = SessionUtils::sessionTeamName();

    $enabled_countries = await Country::genAllEnabledCountriesForMap();
    foreach ($enabled_countries as $country) {
      $is_active_level = await Country::genIsActiveLevel($country->getId());
      $active = ($country->getUsed() && $is_active_level)
              ? 'active'
              : '';
      $country_level = await Level::genWhoUses($country->getId());
      if ($country_level) {
        $my_previous_score = await ScoreLog::genPreviousScore($country_level->getId(), $my_team_id, false);
        $other_previous_score = await ScoreLog::genPreviousScore($country_level->getId(), $my_team_id, true);

        // If my team has scored
        if ($my_previous_score) {
          $captured_by = 'you';
          $data_captured = $my_name;
        // If any other team has scored
        } else if ($other_previous_score) {
          $captured_by = 'opponent';
          $completed_by = await Team::genCompletedLevel($country_level->getId());
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
      /* HH_FIXME[1002] */ /* HH_FIXME[2011] */
      $map_data->{$country->getIsoCode()} = $country_data;
    }

    $this->jsonSend($map_data);
  }
}

$map = new MapDataController();
\HH\Asio\join($map->genGenerateData());
