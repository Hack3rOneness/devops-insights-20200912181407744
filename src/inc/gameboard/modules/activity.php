<?hh // strict

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

/* HH_IGNORE_ERROR[1002] */
SessionUtils::sessionStart();
SessionUtils::enforceLogin();

class ActivityModuleController {
  public async function genRender(): Awaitable<:xhp> {
    $activity_ul = <ul class="activity-stream"></ul>;

    $all_activity = await Control::genAllActivity();
    foreach ($all_activity as $score) {
      if (intval($score['team_id']) === SessionUtils::sessionTeam()) {
        $class_li = 'your-team';
        $class_span = 'your-name';
      } else {
        $class_li = 'opponent-team';
        $class_span = 'opponent-name';
      }
      $activity_ul->appendChild(
        <li class={$class_li}>
          [ {time_ago($score['time'])} ] <span class={$class_span}>{$score['team']}</span> captured {$score['country']}
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
echo \HH\Asio\join($activity_generated->genRender());
