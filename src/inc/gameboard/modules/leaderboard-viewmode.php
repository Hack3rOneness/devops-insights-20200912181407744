<?hh // strict

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

class LeaderboardModuleViewController {
  public async function genRender(): Awaitable<:xhp> {
    $leaderboard_ul = <ul></ul>;

    $rank = 1;
    $leaderboard = await Team::genLeaderboard();
    foreach ($leaderboard as $team) {
      $xlink_href = '#icon--badge-'.$team->getLogo();
      $leaderboard_ul->appendChild(
        <li class="fb-user-card">
          <div class="user-avatar">
            <svg class="icon--badge">
              <use href={$xlink_href}></use>

            </svg>
          </div>
          <div class="player-info">
            <h6>{$team->getName()}</h6>
            <span class="player-rank">Rank {$rank}</span>
            <br></br>
            <span class="player-score">{strval($team->getPoints())} pts</span>
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
        <div class="module-content module-scrollable leaderboard-viewmode">
          {$leaderboard_ul}
        </div>
      </div>;
  }
}

/* HH_IGNORE_ERROR[1002] */
$leaderboard_generated = new LeaderboardModuleViewController();
echo \HH\Asio\join($leaderboard_generated->genRender());
