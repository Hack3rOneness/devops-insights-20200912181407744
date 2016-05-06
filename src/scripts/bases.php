<?hh

require_once('/var/www/fbctf/vendor/autoload.php');

while ((Configuration::get('game')->getValue() === '1')) {
  // Get all active base levels
  $bases_endpoints = array();
  foreach (Level::allActiveBases() as $base) {
    $endpoint = array(
      'id' => $base->getId(),
      'url' => Level::getBaseIP($base->getId())
    );
    array_push($bases_endpoints, $endpoint);
  }

  // Retrieve current owners
  foreach (Level::getBasesResponses($bases_endpoints) as $response) {
    if ($response['response']) {
      $code = 0;
      $json_r = json_decode($response['response'])[0];
      $teamname = $json_r->team;
      // Give points to the team if exists
      if (Team::teamExist($teamname)) {
        $team = Team::getTeamByName($teamname);
        Level::scoreBase($response['id'], $team->getId());
        //echo "Points\n";
      }
      //echo "Base(".strval($response['id']).") taken by ".$teamname."\n";
    } else {
      $code = -1;
      //echo "Base(".strval($response['id']).") is DOWN\n";
    }
    Level::logBaseEntry($response['id'], $code, strval($response['response']));
  }
  // Wait until next iteration
  sleep(intval(Configuration::get('bases_cycle')->getValue()));
}