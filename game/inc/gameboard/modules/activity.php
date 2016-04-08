<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ActivityModuleController {
  public function render(): :xhp {
    $control = new Control();
    $levels = new Levels();
    $countries = new Countries();
    $teams = new Teams();

    $my_team = $teams->get_team(sess_team());

    $activity_ul = <ul class="activity-stream"></ul>;

    foreach ($control->all_activity() as $score) {
      if ($score['team_id'] === sess_team()) {
        $class_li = 'your-team';
        $class_span = 'your-name';
      } else {
        $class_li = 'opponent-team';
        $class_span = 'opponent-name';
      }
      $activity_ul->appendChild(
        <li class={$class_li}>
          [ {$score['time']} ] <span class={$class_span}>{$score['team']}</span> captured {$score['country']}
        </li>
      );
    }
    
    return
      <div>
        <header class="module-header">
          <h6>Activity</h6>
        </header>
        <div class="module-content">
          <div class="fb-section-border">
            <div class="module-scrollable">
              {$activity_ul}
            </div>
          </div>
        </div>
      </div>;
  }
}

$activity_generated = new ActivityModuleController();
echo $activity_generated->render();