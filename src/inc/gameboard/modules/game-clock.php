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
    if ($s_each === 0) {
      $current = 0;
    } else {
      $current = intval($current_s/$s_each);
    }
    $indicator = <div class="indicator game-progress-indicator"></div>;
    if (Configuration::get('game')->getValue() === '1') {
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
    } else {
      for ($i=0; $i<10; $i++) {
        $indicator->appendChild(
          <span class="indicator-cell"></span>
        ); 
      }
    }
    return $indicator;
  }

  public function render(): :xhp {
    $timer_conf = Configuration::get('timer')->getValue();
    $start_ts = Configuration::get('start_ts')->getValue();
    $end_ts = Configuration::get('end_ts')->getValue();
    $now = time();
    $init = intval($end_ts) - $now;

    if ($timer_conf === '1') {
      $num_hours = intval(floor($init / 3600));
      $hours_int = ($num_hours >= 0) ? $num_hours : 0;
      $hours = sprintf("%02d", $hours_int);

      $num_minutes = intval(intval($init / 60) % 60);
      $minutes_int = ($num_minutes >= 0) ? $num_minutes : 0;
      $minutes = sprintf("%02d", $minutes_int);

      $num_seconds = intval($init % 60);
      $seconds_int = ($num_seconds >= 0) ? $num_seconds : 0;
      $seconds = sprintf("%02d", $seconds_int);
      
      if ($init > 0) {
        $milli_int = rand(0, 99);
      } else {
        $milli_int = 0;
      }
      $milliseconds = sprintf("%02d", $milli_int);
    } else {
      $hours = '--';
      $minutes = '--';
      $seconds = '--';
      $milliseconds = '--';
    }
    return
      <div>
        <header class="module-header">
          <h6>Game Clock</h6>
        </header>
        <div class="module-content module-scrollable">
          <div class="game-clock fb-numbers">
            <span class="clock-hours">{$hours}</span>:<span class="clock-minutes">{$minutes}</span>:<span class="clock-seconds">{$seconds}</span>:<span class="clock-milliseconds">{$milliseconds}</span>
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
