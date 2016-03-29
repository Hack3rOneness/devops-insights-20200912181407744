<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ScoreboardController {
  public function render(): :xhp {
    $scoreboard_tbody = <tbody></tbody>;

    $teams = new Teams();
    $rank = 1;
    $leaderboard = $teams->leaderboard();

    foreach ($leaderboard as $team) {
      $team_id = 'fb-scoreboard--team-'.$team['id'];
      $scoreboard_tbody->appendChild(
        <tr>
          <td style="width: 10%;" class="el--radio">
            <input type="checkbox" name="fb-scoreboard-filter" id={$team_id} value={$team['name']} checked={true}/>
            <label class="click-effect" for={$team_id}><span></span></label>
          </td>
          <td style="width: 10%;">{$rank}</td>
          <td style="width: 50%;">{$team['name']}</td>
          <td style="width: 30%;">{$team['points']}</td>
        </tr>
      );
      $rank++;
    }

    return
      <div class="fb-modal-content fb-row-container">
        <div class="modal-title row-fixed">
          <h4>scoreboard_</h4>
          <a href="#" class="js-close-modal">
            <svg class="icon icon--close">
              <use xlink:href="#icon--close"/>

            </svg>
          </a>
        </div>
        <div class="scorboard-graphic scoreboard-graphic-container">
          <svg class="fb-graphic" data-file="data/scores.json" width={820} height={220}></svg>
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
                <th style="width: 10%;">filter_</th>
                <th style="width: 10%;">rank_</th>
                <th style="width: 50%;">team_name_</th>
                <th style="width: 30%;">total_pts_</th>
              </tr>
            </thead>
          </table>
          <div class="row-fluid main-data">
            <table class="row-fixed">
              {$scoreboard_tbody}
            </table>
          </div>
        </div>
      </div>;
  }
}

$scoreboard_generated = new ScoreboardController();
echo $scoreboard_generated->render();