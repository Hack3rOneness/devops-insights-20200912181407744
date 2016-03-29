<?hh

include($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');
include($_SERVER['DOCUMENT_ROOT'] . '/components.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/teams.php');

class LeaderboardViewController {
  public function render(): :xhp {
    $leaderboard_ul = <ul></ul>;

    $rank = 1;
    $teams = new Teams();
    foreach ($teams->leaderboard() as $team) {
      $xlink_href = '#icon--badge-'.$team['logo'];
      $leaderboard_ul->appendChild(
        <li class="fb-user-card">
          <div class="user-avatar">
            <svg class="icon--badge">
              <use xlink:href={$xlink_href}></use>

            </svg>
          </div>
          <div class="player-info">
            <h6>{$team['name']}</h6>
            <span class="player-rank">Rank {$rank}</span>
            <br></br>
            <span class="player-score">{$team['points']} pts</span>
          </div>
        </li>
      );
      $rank++;
    }

    return
      <div>
        <header class="module-header">
          <h6>Leaderboard</h6>
        </header>
        <div class="module-content module-scrollable">
          {$leaderboard_ul}
        </div>
      </div>;
  }
}

$leaderboard_generated = new LeaderboardViewController();
echo $leaderboard_generated->render();