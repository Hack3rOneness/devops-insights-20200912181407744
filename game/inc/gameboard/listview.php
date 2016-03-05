<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/teams.php');

sess_start();
sess_enforce_login();

echo <<< EOT
<div class="listview-container">
  <table>
EOT;

$levels = new Levels();
$countries = new Countries();
$teams = new Teams();

$my_team = sess_team();
$my_name = htmlspecialchars(sess_teamname());

foreach ($levels->all_levels(1) as $level) {
  $country = $countries->get_country($level['entity_id']);
  $category = $levels->get_category($level['category_id']);
  if ($levels->previous_score($country_level['id'], $my_team)) {
    $span_status = '<span class="fb-status status--yours">Captured</span>';
  } else {
    $span_status = '<span class="fb-status status--open">Open</span>';
  }
  echo <<< EOT
    <tr data-country="{$country['name']}" class="">
      <td width="38%">{$country['name']}</td>
      <td width="10%">{$level['points']}</td>
      <td width="22%">{$category['category']}</td>
      <td width="30%">{$span_status}</td>
    </tr>
EOT;
}

echo <<< EOT
  </table>
</div>
EOT;

?>