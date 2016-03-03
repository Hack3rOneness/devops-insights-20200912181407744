<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/logos.php');

sess_start();
sess_enforce_admin();

echo <<< EOT

<header class="admin-page-header">
  <h3>Logo Management</h3>
    <!--
    * @note
    * this will reflect the last saved time inside the
    *  "highlighted" span
  -->
  <span class="admin-section--status">status_<span class="highlighted">2015.10.15</span></span>
</header>

<div class="admin-sections">

EOT;

$logos = new Logos();
foreach ($logos->all_logos() as $logo) {
  $logo_name = htmlspecialchars($logo['name']);
  $using_logo = $logos->who_uses($logo['name']);
  $current_use = ($using_logo) ? 'Yes' : 'No';
  if ($logo['enabled'] == 1) {
    $highlighted_action = 'disable';
    $highlighted_color = 'red';
  } else {
    $highlighted_action = 'enable';
    $highlighted_color = 'green';
  }
  $action_text = strtoupper($highlighted_action);

  echo <<< EOT
  <section class="admin-box">
  <form class="logo_form">
    <input type="hidden" name="logo_id" value="{$logo['id']}">
    <input type="hidden" name="status_action" value="{$highlighted_action}">
    <header class="logo-management-header">
      <h6>ID{$logo['id']}</h6>
      <a class="highlighted--{$highlighted_color}" href="#" data-action="{$highlighted_action}-logo">{$action_text}</a>
    </header>

    <div class="fb-column-container">
      <div class="col col-pad col-shrink">
        <div class="post-avatar has-avatar"><svg class="icon icon--badge"><use xlink:href="#icon--badge-{$logo_name}" /></use></svg></div>
      </div>

      <div class="col col-pad col-grow">
        <div class="selected-logo">
          <label>Logo Name: </label>
          <span class="logo-name">{$logo_name}</span>
        </div>
        <div class="selected-logo">
          <label>In use: </label>
          <span class="logo-name">{$current_use}</span>
        </div>
      </div>

      <div class="col col-pad col-1-3">
        <div class="form-el el--select el--block-label">
          <label for="">Used By:</label>
          <select>
EOT;
    if ($using_logo) {
      foreach ($using_logo as $t) {
        $t_name = htmlspecialchars($t['name']);
      echo <<< EOT
            <option value="">{$t_name}</option>
EOT;
      }
    } else {
      echo <<< EOT
            <option value="">None</option>
EOT;
    }
    echo <<< EOT
          </select>
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