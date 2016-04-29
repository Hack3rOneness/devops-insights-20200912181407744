<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

SessionUtils::sessionStart();
SessionUtils::enforceLogin();

class WorldMapController {
  public function render(): :xhp {
    $worldMap = $this->renderWorldMap();
    return
      <svg id="fb-gameboard-map" xmlns="http://www.w3.org/2000/svg" amcharts="http://amcharts.com/ammap" xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1008 651" preserveAspectRatio="xMidYMid meet">
        <defs>
          <amcharts:ammap projection="mercator" leftLongitude="-169.6" topLatitude="83.68" rightLongitude="190.25" bottomLatitude="-55.55"></amcharts:ammap>
        </defs>
        <g class="view-controller">
          {$worldMap}
          <g class="country-hover"></g>
        </g>
      </svg>;
  }

  public function renderWorldMap(): :xhp {
    $svg_countries = <g class="countries"></g>;

    foreach (Country::allMapCountries() as $country) {
      if (Configuration::get('gameboard')->getValue() === '1') {
        $path_class = (($country->getUsed()) && (Country::isActiveLevel($country->getId())))
          ? 'land active'
          : 'land';
        $map_indicator = 'map-indicator ';
        $data_captured = null;
        $country_level = Level::whoUses($country->getId());

        if ($country_level) {
          if (Level::previousScore($country_level->getId(), SessionUtils::sessionTeam(), false)) {
            $map_indicator .= 'captured--you';
            $data_captured = SessionUtils::sessionTeamName();
          } else if (Level::previousScore($country_level->getId(), SessionUtils::sessionTeam(), true)) {
            $map_indicator .= 'captured--opponent';
            $completed_by = Team::completedLevel($country_level->getId());
            $data_captured = '';
            foreach ($completed_by as $c) {
              $data_captured .= ' ' . $c->getName();
            }
          }
        }
      } else {
        $path_class = 'land';
        $map_indicator = 'map-indicator ';
        $data_captured = null;
      }

      $g =
        <g>
          <path id={$country->getIsoCode()} title={$country->getName()} class={$path_class} d={$country->getD()}></path>
          <g transform={$country->getTransform()} class={$map_indicator}>
            <path d="M0,9.1L4.8,0h0.1l4.8,9.1v0L0,9.1L0,9.1z"></path>
          </g>
        </g>;
      if ($data_captured) {
        $g->setAttribute('data-captured', $data_captured);
      }
      $svg_countries->appendChild($g);
    }

    return $svg_countries;
  }
}

$map = new WorldMapController();
echo $map->render();