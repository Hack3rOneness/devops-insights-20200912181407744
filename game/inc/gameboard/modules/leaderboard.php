<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class LeaderboardModuleController {
  public function render(): :xhp {
    $leaderboard_ul = <ul></ul>;

    $my_team = Team::getTeam(intval(sess_team()));
    $my_rank = Team::myRank(intval(sess_team()));

    // If refresing is enabled, do the needful
    if (Configuration::get('gameboard')->getValue() === '1') {
      $leaders = Team::leaderboard();
      $rank = 1;
      $l_max = (count($leaders) > 5) ? 5 : count($leaders);
      for($i = 0; $i<$l_max; $i++) {
        $team = $leaders[$i];
        $xlink_href = '#icon--badge-'.$team->getLogo();
        $leaderboard_ul->appendChild(
          <li class="fb-user-card">
            <div class="user-avatar">
              <svg class="icon--badge">
                <use xlink:href={$xlink_href}/>

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
    }

    return
      <div>
        <header class="module-header">
          <h6>Leaderboard</h6>
        </header>
        <div class="module-content">
          <div class="fb-section-border">
            <div class="module-top player-info">
              <h5 class="player-name">{$my_team->getName()}</h5>
              <span class="player-rank">Your Rank: {$my_rank}</span>
              <br></br>
              <span class="player-score">Your Score: {strval($my_team->getPoints())} Pts</span>
            </div>
            <div class="module-scrollable leaderboard-info">
              {$leaderboard_ul}
            </div>
          </div>
        </div>
      </div>;
  }
}

$leaderboard_generated = new LeaderboardModuleController();
echo $leaderboard_generated->render();