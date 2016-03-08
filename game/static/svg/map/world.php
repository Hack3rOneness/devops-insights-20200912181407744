<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');

sess_start();
sess_enforce_login();

echo <<< EOT
<svg id="fb-gameboard-map" xmlns="http://www.w3.org/2000/svg" xmlns:amcharts="http://amcharts.com/ammap" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1008 651" preserveAspectRatio="xMidYMid meet">
	<defs>
		<amcharts:ammap projection="mercator" leftLongitude="-169.6" topLatitude="83.68" rightLongitude="190.25" bottomLatitude="-55.55"></amcharts:ammap>
	</defs>
	<g class="view-controller">
		<g class="countries">
EOT;

$countries = new Countries();
$levels = new Levels();

$my_team = sess_team();
$my_name = htmlspecialchars(sess_teamname());

foreach ($countries->all_map_countries(true) as $country) {
	$active = (($country['used'] == 1) && ($countries->is_active_level($country['id'])))
		? 'active'
		: '';
	$country_level = $countries->who_uses($country['id']);
	if ($country_level) {
		if ($levels->previous_score($country_level['id'], $my_team)) {
			$captured_by = 'captured--you';
			$data_captured = 'data-captured="'.$my_name.'"';
		} else if ($levels->previous_score($country_level['id'], $my_team, true)) {
			$captured_by = 'captured--opponent';
			$completed_by = $levels->completed_by($country_level['id'])[0];
			$data_captured = 'data-captured="' . htmlspecialchars($completed_by['name']) . '"';
		} else {
			$captured_by = '';
			$data_captured = '';
		}
	} else {
		$captured_by = '';
		$data_captured = '';
	}
	echo <<< EOT
			<g {$data_captured}>
				<path id="{$country['iso_code']}" title="{$country['name']}" class="land {$active}" d="{$country['d']}"></path>
				<g transform="{$country['transform']}" class="map-indicator {$captured_by}"><path d="M0,9.1L4.8,0h0.1l4.8,9.1v0L0,9.1L0,9.1z"></path></g>
			</g>
EOT;
}

echo <<< EOT
		</g><!-- countries -->
		<g class="country-hover"></g>
	</g><!-- view-controller -->		
</svg>
EOT;

?>
