
<header class="module-header">
  <h6>Leaderboard</h6>
</header>
<div class="module-content module-scrollable">
    
  <ul>
<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/teams.php');

$rank = 1;
$teams = new Teams();
foreach ($teams->leaderboard() as $team) {
  $team_name = htmlspecialchars($team['name']);
  echo <<< EOT
    <li class="fb-user-card">
      <div class="user-avatar"><svg class="icon--badge"><use xlink:href="#icon--badge-{$team['logo']}"></use></svg></div>
      <div class="player-info">
        <h6>{$team_name}</h6>
        <span class="player-rank">Rank {$rank}</span>
        <span class="player-score">{$team['points']} pts</span>
      </div>
    </li>
EOT;
  $rank++;
}

?>
  </ul>
</div>