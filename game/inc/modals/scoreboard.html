
<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/teams.php');

sess_start();
sess_enforce_login();

echo <<< EOT

<div class="fb-modal-content fb-row-container">
  <div class="modal-title row-fixed">
    <h4>scoreboard_</h4>
    <a href="#" class="js-close-modal"><svg class="icon icon--close"><use xlink:href="#icon--close"/></svg></a>
  </div>

  <div class="scorboard-graphic scoreboard-graphic-container">
    <svg class="fb-graphic" data-file="data/scores.json" width="820" height="220"></svg>
  </div>

  <div class="game-progress fb-progress-bar fb-cf row-fixed">
    <div class="indicator game-progress-indicator">
      <span class="indicator-cell active"></span>
      <span class="indicator-cell active"></span>
      <span class="indicator-cell active"></span>
      <span class="indicator-cell"></span>
      <span class="indicator-cell"></span>
      <span class="indicator-cell"></span>
      <span class="indicator-cell"></span>
      <span class="indicator-cell"></span>
      <span class="indicator-cell"></span>
      <span class="indicator-cell"></span>
    </div>
    <span class="label label--left">[Start]</span>
    <span class="label label--right">[End]</span>
  </div>

  <div class="game-scoreboard fb-row-container">

    <table class="row-fixed">
      <thead>
        <tr>
          <th width="10%">filter_</th>
          <th width="10%">rank_</th>
          <th width="50%">team_name_</th>
          <th width="30%">total_pts_</th>
        </tr>
      </thead>
    </table>
    <div class="row-fluid main-data">
      <table class="row-fixed">
        <tbody>
EOT;

$teams = new Teams();
$rank = 1;
$leaderboard = $teams->leaderboard();

foreach ($leaderboard as $team) {
  $team_name = htmlspecialchars($team['name']);
  echo <<< EOT
          <tr>
            <td width="10%" class="el--radio"><input type="checkbox" name="fb-scoreboard-filter" id="fb-scoreboard--team-{$team['id']}" value="{$team_name}" checked="checked"><label class="click-effect" for="fb-scoreboard--team-{$team['id']}"><span></span></label></td>
            <td width="10%">{$rank}</td>
            <td width="50%">{$team_name}</td>
            <td width="30%">{$team['points']}</td>
          </tr>
EOT;
  $rank++;
}

echo <<< EOT
        </tbody>
      </table>
    </div>
    
  </div>

</div>
EOT
?>