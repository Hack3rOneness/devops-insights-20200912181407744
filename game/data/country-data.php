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

	// All attachments for this level
	$attachments_list = array();
	if ($attachments->has_attachments($level['id'])) {
		foreach ($attachments->all_attachments($level['id']) as $attachment) {
			array_push($attachments_list, $attachment['filename']);
		}
	}

	// All teams that have completed this level
	$completed_by = array();
	foreach ($levels->completed_by($level['id']) as $c) {
		array_push($completed_by, $c['name']);
	}

	// Who is the first owner of this level
	$owner = ($completed_by) ? $completed_by[0] : 'Uncaptured';
	$country_data = (object) array(
		'level_id' => $level['id'],
		'intro' => $level['description'],
		'type'  => $level['type'],
		'points' => (int) $level['points'],
		'bonus' => (int) $level['bonus'],
		'category' => $category['category'],
		'owner' => $owner,
		'completed' => $completed_by,
		'hint' => $hint,
		'hint_cost' => $hint_cost,
		'attachments' => $attachments_list
	);
	$countries_data->{$country['name']} = $country_data;
}

header('Content-Type: application/json');
print json_encode($countries_data, JSON_PRETTY_PRINT);

?>
