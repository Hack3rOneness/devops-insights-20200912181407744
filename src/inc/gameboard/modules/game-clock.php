<?hh // strict

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ClockModuleController {
  private function generateIndicator(string $start_ts, string $end_ts): :xhp {
    $seconds = intval($end_ts) - intval($start_ts);
    $s_each = intval($seconds/10);
    $now = time();
    $current_s = intval($now) - intval($start_ts);
    $current = intval($current_s/$s_each);
    $indicator = <div class="indicator game-progress-indicator"></div>;
    for ($i=0; $i<10; $i++) {
      $indicator_classes = 'indicator-cell ';
      if ($current >= $i) {
        $indicator_classes .= 'active ';
      }
      $indicator->appendChild(
        <span class={$indicator_classes}></span>
      );
    }
    return $indicator;
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