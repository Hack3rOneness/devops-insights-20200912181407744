<?hh // strict

class ScoreboardModalController extends ModalController {
  <<__Override>>
  public async function genRender(string $_): Awaitable<:xhp> {
    $scoreboard_tbody = <tbody></tbody>;

    // If refresing is enabled, do the needful
    $gameboard = await Configuration::gen('gameboard');
    if ($gameboard->getValue() === '1') {
      $rank = 1;
      $leaderboard = await MultiTeam::genLeaderboard(false);

      foreach ($leaderboard as $team) {
        $team_id = 'fb-scoreboard--team-'.strval($team->getId());
        list($quiz, $mchoice, $flag, $base) = await \HH\Asio\va(
          MultiTeam::genPointsByType($team->getId(), 'quiz'),
          MultiTeam::genPointsByType($team->getId(), 'mchoice'),
          MultiTeam::genPointsByType($team->getId(), 'flag'),
          MultiTeam::genPointsByType($team->getId(), 'base'),
        );
        $scoreboard_tbody->appendChild(
          <tr>
            <td style="width: 10%;">{$rank}</td>
            <td style="width: 40%;">{$team->getName()}</td>
            <td style="width: 10%;">{strval($quiz)}</td>
            <td style="width: 10%;">{strval($mchoice)}</td>
            <td style="width: 10%;">{strval($flag)}</td>
            <td style="width: 10%;">{strval($base)}</td>
            <td style="width: 10%;">{strval($team->getPoints())}</td>
          </tr>
        );
        $rank++;
      }
    }

    return
      <div class="fb-modal-content fb-row-container">
        <div class="modal-title row-fixed">
          <h4>{tr('scoreboard_')}</h4>
          <a href="#" class="js-close-modal">
            <svg class="icon icon--close">
              <use href="#icon--close" />
            </svg>
          </a>
        </div>
        <div class="game-scoreboard fb-row-container">
          <table class="row-fixed">
            <thead>
              <tr>
                <th style="width: 10%;">{tr('rank_')}</th>
                <th style="width: 40%;">{tr('team_name_')}</th>
                <th style="width: 10%;">{tr('quiz_pts_')}</th>
                <th style="width: 10%;">mchoice_pts_</th>
                <th style="width: 10%;">{tr('flag_pts_')}</th>
                <th style="width: 10%;">{tr('base_pts_')}</th>
                <th style="width: 10%;">{tr('total_pts_')}</th>
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
