<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

class ActivityViewModeModuleController {
  public function render(): :xhp {
    $activity_ul = <ul class="activity-stream"></ul>;

    foreach (Control::allActivity() as $score) {
      $activity_ul->appendChild(
        <li class="opponent-team">
          [ {time_ago($score['time'])} ] <span class="opponent-name">{$score['team']}</span> captured {$score['country']}
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

$activity_generated = new ActivityViewModeModuleController();
echo $activity_generated->render();