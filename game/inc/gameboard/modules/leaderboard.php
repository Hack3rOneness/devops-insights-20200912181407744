<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class LeaderboardController {
  public function render(): :xhp {
    $leaderboard_ul = <ul></ul>;

    $teams = new Teams();
    $leaders = $teams->leaderboard();

    $my_team = $teams->get_team(sess_team());
    $my_rank = $teams->my_rank(sess_team());

    $teams = new Teams();
    $rank = 1;
    $l_max = (sizeof($leaders) > 5) ? 5 : sizeof($leaders);
    for($i = 0; $i<$l_max; $i++) {
      $team = $leaders[$i];
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
        <div class="module-content">
          <div class="fb-section-border">
            <div class="module-top player-info">
              <h5 class="player-name">{$my_team['name']}</h5>
              <span class="player-rank">Rank {$my_rank}</span>
              <br></br>
              <span class="player-score">{$my_team['points']} Pts</span>
            </div>
            <div class="module-scrollable">
              {$leaderboard_ul}
            </div>
          </div>
        </div>
      </div>;
  }
}

$leaderboard_generated = new LeaderboardController();
echo $leaderboard_generated->render();