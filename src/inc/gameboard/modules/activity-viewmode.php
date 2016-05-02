<?hh // strict

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

class ActivityViewModeModuleController {
  public async function genRender(): Awaitable<:xhp> {
    $activity_ul = <ul class="activity-stream"></ul>;

    $all_activity = await Control::genAllActivity();
    foreach ($all_activity as $score) {
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

/* HH_IGNORE_ERROR[1002] */
$activity_generated = new ActivityViewModeModuleController();
echo \HH\Asio\join($activity_generated->genRender());
