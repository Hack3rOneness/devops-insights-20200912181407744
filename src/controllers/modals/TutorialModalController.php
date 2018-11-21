<?hh // strict

class TutorialModalController extends ModalController {
  private function getStep(
    string $step,
  ): (string, string, string, ?:xhp, :xhp) {
    switch ($step) {
      case 'tool-bars':
        $content =
          <div class="main-text">
            <p>
              {tr(
                'Tool bars are located on all edges of the gameboard. Tap a category to expand and close each tool bar.',
              )}
            </p>
          </div>;
        return tuple($step, tr('Tool_Bars'), 'game-clock', null, $content);
      case 'game-clock':
        $content =
          <div class="main-text">
            <p>
              {tr(
                'Tap the "Game Clock" to keep track of time during gameplay. Don’t let time get the best of you.',
              )}
            </p>
          </div>;
        return tuple($step, tr('Game_Clock'), 'captures', null, $content);
      case 'captures':
        $header =
          <div class="header-graphic">
            <svg class="icon--country-australia--captured">
              <use href="#icon--country-australia--captured"></use>
            </svg>
          </div>;
        $content =
          <div class="main-text">
            <p>
              {tr('Countries marked with an ')}
              <svg class="icon--team-indicator your-team">
                <use href="#icon--team-indicator"></use>
              </svg>
              {tr('are captured by you.')}
            </p>
            <p>
              {tr('Countries marked with an ')}
              <svg class="icon--team-indicator opponent-team">
                <use href="#icon--team-indicator"></use>
              </svg>{tr(' are owned by others.')}
            </p>
          </div>;
        return tuple($step, tr('Captures'), 'navigation', $header, $content);
      case 'navigation':
        $content =
          <div class="main-text">
            <p>
              {tr(
                'Click "Navigation" to access main navigation links like Rules of Play, Account Settings, and to Logout.',
              )}
            </p>
          </div>;
        return tuple($step, tr('Navigation'), 'scoreboard', null, $content);
      case 'scoreboard':
        $content =
          <div class="main-text">
            <p>
              {tr(
                'Track your competition by clicking "scoreboard" to access real-time game statistics.',
              )}
            </p>
          </div>;
        return tuple($step, tr('Scoreboard'), 'game-on', null, $content);
      case 'game-on':
        $content =
          <div class="main-text">
            <p>{tr('Have fun, be the best and conquer the world.')}</p>
          </div>;
        return tuple($step, tr('Game_On'), '', null, $content);
      default:
        invariant(false, 'invalid tutorial name');
    }
  }

  <<__Override>>
  public async function genRender(string $step): Awaitable<:xhp> {
    list($step, $name, $next_step, $header, $content) = $this->getStep($step);

    return
      <div class="fb-modal-content fb-tutorial" data-tutorial-step={$step}>
        {$header}
        <div class="modal-title">
          <h4>{tr('tutorial_')}<span class="highlighted">{$name}</span></h4>
        </div>
        <div class="tutorial-content">
          {$content}
          <div class="tutorial-navigation fb-column-container">
            <ul class="tutorial-progress"></ul>
            <div class="tutorial-actionable">
              <a
                href="#"
                class="fb-cta cta--yellow"
                data-next-tutorial={$next_step}>
                {tr('Next')}
              </a>
            </div>
          </div>
        </div>
        <div class="tutorial-skip">
          <a href="#" class="fb-cta js-close-tutorial">
            {tr('Skip to play')}
          </a>
        </div>
      </div>;
  }
}
