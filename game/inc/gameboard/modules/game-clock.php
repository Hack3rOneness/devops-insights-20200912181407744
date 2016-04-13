<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ClockModuleController {
  private function generateIndicator($start_ts, $end_ts): :xhp {
    return
      <div class="indicator game-progress-indicator">
        <span class="indicator-cell active"></span>
        <span class="indicator-cell active"></span>
        <span class="indicator-cell active current-spot"></span>
        <span class="indicator-cell"></span>
        <span class="indicator-cell"></span>
        <span class="indicator-cell"></span>
        <span class="indicator-cell"></span>
        <span class="indicator-cell"></span>
        <span class="indicator-cell"></span>
        <span class="indicator-cell"></span>
      </div>;
  }

  public function render(): :xhp {
    $timer = Configuration::get('timer')->getValue();
    $start_ts = Configuration::get('start_ts')->getValue();
    $end_ts = Configuration::get('end_ts')->getValue();
    if ($timer === '1') {
      $hours = '';
      $minutes = '';
      $seconds = '';
      $milliseconds = '';
    }
    return
      <div>
        <header class="module-header">
          <h6>Game Clock</h6>
        </header>
        <div class="module-content module-scrollable">
          <div class="game-clock fb-numbers">
            <span class="clock-hours">--</span>:<span class="clock-minutes">--</span>:<span class="clock-seconds">--</span>:<span class="clock-milliseconds">--</span>
          </div>
          <div class="game-progress fb-progress-bar">
            <span class="label label--left">[Start]</span>
            <span class="label label--right">[End]</span>
            {$this->generateIndicator($start_ts, $end_ts)}
          </div>
        </div>
      </div>;
  }
}

$clock_generated = new ClockModuleController();
echo $clock_generated->render();