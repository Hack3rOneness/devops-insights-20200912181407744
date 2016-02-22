<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');

sess_start();
sess_enforce_login();

$levels = new Levels();
$countries = new Countries();

$countries_data = (object) array();
foreach ($levels->all_levels(1) as $level) {
	$country = $countries->get_country($level['entity_id']);
	$category = $levels->get_category($level['category_id']);
	$hint = ($level['hint']) ? 'yes' : 'no';
	$country_data = (object) array(
		'points' => (int) $level['points'],
		'bonus' => (int) $level['bonus'],
		'category' => $category['category'],
		'owner' => array(),
		'completed' => array(),
		'intro' => $level['description'],
		'hint' => $hint,
	);
	$countries_data->{$country['name']} = $country_data;
}

header('Content-Type: application/json');
print json_encode($countries_data, JSON_PRETTY_PRINT);

?>
