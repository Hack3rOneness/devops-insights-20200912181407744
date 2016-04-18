<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ScoreboardController {
  public function render(): :xhp {
    $scoreboard_tbody = <tbody></tbody>;

    // If refresing is enabled, do the needful
    if (Configuration::get('gameboard')->getValue() === '1') {
      $rank = 1;
      $leaderboard = Team::leaderboard();

      foreach ($leaderboard as $team) {
        $team_id = 'fb-scoreboard--team-'.strval($team->getId());
        $color = '#' . substr(md5($team->getName()), 0, 6) . ';';
        $style = 'color: '.$color.'; background:' .$color. ';';
        $scoreboard_tbody->appendChild(
          <tr>
            <td style="width: 10%;" class="el--radio">
              <input type="checkbox" name="fb-scoreboard-filter" id={$team_id} value={$team->getName()} checked={true}/>
              <label class="click-effect" for={$team_id}><span style={$style}>FU</span></label>
            </td>
            <td style="width: 10%;">{$rank}</td>
            <td style="width: 40%;">{$team->getName()}</td>
            <td style="width: 10%;">{strval(Team::pointsByType($team->getId(), 'quiz'))}</td>
            <td style="width: 10%;">{strval(Team::pointsByType($team->getId(), 'flag'))}</td>
            <td style="width: 10%;">{strval(Team::pointsByType($team->getId(), 'base'))}</td>
            <td style="width: 10%;">{strval($team->getPoints())}</td>
          </tr>
        );
        $rank++;
      }
    }

    return
      <div class="fb-modal-content fb-row-container">
        <div class="modal-title row-fixed">
          <h4>scoreboard_</h4>
          <a href="#" class="js-close-modal">
            <svg class="icon icon--close">
              <use href="#icon--close"/>

            </svg>
          </a>
        </div>
        <div class="scoreboard-graphic scoreboard-graphic-container">
          <svg class="fb-graphic" data-file="data/scores.php" width="820" height={220}></svg>
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
                <th style="width: 40%;">team_name_</th>
                <th style="width: 10%;">quiz_pts_</th>
                <th style="width: 10%;">flag_pts_</th>
                <th style="width: 10%;">base_pts_</th>
                <th style="width: 10%;">total_pts_</th>
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