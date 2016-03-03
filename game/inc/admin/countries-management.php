<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');

sess_start();
sess_enforce_admin();

echo <<< EOT

<header class="admin-page-header">
  <h3>Countries Management</h3>
  <!--
    * @note
    * this will reflect the last saved time inside the
    *  "highlighted" span
-->
<span class="admin-section--status">status_<span class="highlighted">2015.10.15</span></span>
</header>

<div class="admin-sections">

EOT;

$countries = new Countries();
foreach ($countries->all_countries() as $country) {
  $using_country = $countries->who_uses($country['id']);
  $current_use = ($using_country) ? 'Yes' : 'No';
  if ($country['enabled'] == 1) {
    $highlighted_action = 'disable';
    $highlighted_color = 'red';
  } else {
    $highlighted_action = 'enable';
    $highlighted_color = 'green';
  }
  $current_status = strtoupper($highlighted_action);

  echo <<< EOT
  <section class="admin-box">
  <form class="country_form">
    <input type="hidden" name="country_id" value="{$country['id']}">
    <input type="hidden" name="status_action" value="{$highlighted_action}">
    <header class="countries-management-header">
      <h6>ID{$country['id']}</h6>
      <a class="highlighted--{$highlighted_color}" href="#" data-action="{$highlighted_action}-country">{$current_status}</a>
    </header>

    <div class="fb-column-container">

      <div class="col col-pad col-2-3">
        <div class="selected-logo">
          <label>Country: </label>
          <span class="logo-name">{$country['name']}</span>
        </div>
      </div>

      <div class="col col-pad col-1-3">
        <div class="selected-logo">
          <label>ISO Code: </label>
          <span class="logo-name">{$country['iso_code']}</span>
        </div>
        <div class="selected-logo">
          <label>In Use: </label>
          <span class="logo-name">{$current_use}</span>
        </div>
      </div>

    </div>
    </form>
  </section>
EOT;
}

echo <<< EOT
</div><!-- .admin-sections -->
EOT;
?>