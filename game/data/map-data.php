<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');

sess_start();
sess_enforce_login();

$levels = new Levels();
$countries = new Countries();

$map_data = (object) array();

$my_team = sess_team();
$my_name = sess_teamname();

foreach ($countries->all_enabled_countries(true) as $country) {
  $active = (($country['used'] == 1) && ($countries->is_active_level($country['id'])))
          ? 'active'
          : '';
  $country_level = $countries->who_uses($country['id']);
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
  $map_data->{$country['iso_code']} = $country_data;
}

header('Content-Type: application/json');
print json_encode($map_data, JSON_PRETTY_PRINT);

?>
