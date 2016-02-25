<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/attachments.php');

sess_start();
sess_enforce_login();

$levels = new Levels();
$countries = new Countries();
$attachments = new Attachments();

$countries_data = (object) array();
foreach ($levels->all_levels(1) as $level) {
	$country = $countries->get_country($level['entity_id']);
	$category = $levels->get_category($level['category_id']);
	$hint = ($level['hint']) ? 'yes' : 'no';
	$hint_cost = ($level['penalty'] === 0) ? 0 : $level['penalty'];
	$attachments_list = array();
	if ($attachments->has_attachments($level['id'])) {
		foreach ($attachments->all_attachments($level['id']) as $attachment) {
			array_push($attachments_list, $attachment['filename']);
		}
	}
	$country_data = (object) array(
		'intro' => $level['description'],
		'type'  => $level['type'],
		'points' => (int) $level['points'],
		'bonus' => (int) $level['bonus'],
		'category' => $category['category'],
		'owner' => array(),
		'completed' => array(),
		'hint' => $hint,
		'hint_cost' => $hint_cost,
		'attachments' => $attachments_list
	);
	$countries_data->{$country['name']} = $country_data;
}

header('Content-Type: application/json');
print json_encode($countries_data, JSON_PRETTY_PRINT);

?>
