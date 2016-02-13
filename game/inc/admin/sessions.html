<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');

sess_start();
sess_enforce_admin();

echo <<< EOT

<header class="admin-page-header">
    <h3>Sessions</h3>
    <!--
    * @note
    * this will reflect the last saved time inside the
    *  "highlighted" span
    -->
    <span class="admin-section--status">status_<span class="highlighted">2015.10.15</span></span>
</header>


<div class="admin-sections">

EOT;

$c = 1;
foreach (sess_all() as $session) {
  $data = htmlspecialchars($session['data']);
  echo <<< EOT
  <section class="admin-box section-locked">
    <form class="session_form" name="session_{$session['id']}">
      <input type="hidden" name="session_id" value="{$session['id']}">
      <header class="admin-box-header">
        <span class="session-name">Session {$c}: <span class="highlighted--blue">{$session['last_access_ts']}</span></span>

      </header>

      <div class="fb-column-container">
        <div class="col col-1-2 col-pad">
          <div class="form-el el--block-label el--full-text">
            <label class="admin-label">Cookie</label>
            <input name="cookie" type="text" value="{$session['cookie']}" disabled>
          </div>
        </div>
        <div class="col col-1-2 col-pad">
          <div class="form-el el--block-label el--full-text">
            <label class="admin-label">Creation Time:</label>
            <span class="highlighted"><label class="admin-label">{$session['created_ts']}</label></span>
          </div>
        </div>
      </div>

      <div class="admin-row">
        <div class="form-el el--block-label el--full-text">
          <label class="admin-label">Data</label>
          <input name="data" type="text" value="{$data}" disabled>
        </div>
      </div>


      <div class="admin-buttons admin-row">
        <div class="button-right">
          <a href="#" class="admin--edit" data-action="edit">EDIT</a>
          <button class="fb-cta cta--red" data-action="delete">Delete</button>
        </div>
      </div>
    </form>
  </section>
EOT;
  $c++;
}

echo <<< EOT
</div>
EOT;

?>
