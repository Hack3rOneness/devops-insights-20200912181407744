<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ActivityModuleController {
  public function render(): :xhp {
    return
      <div>
        <header class="module-header">
          <h6>Activity</h6>
        </header>
        <div class="module-content">
          <div class="fb-section-border">
            <div class="module-top">
              <div class="radio-tabs">
                <input type="radio" name="fb--module--activity" id="fb--module--activity--your-team" value="your-team"/>
                <label for="fb--module--activity--your-team" class="click-effect"><span>Your Team</span></label>
                <input type="radio" name="fb--module--activity" id="fb--module--activity--everyone" checked={true} value="all"/>
                <label for="fb--module--activity--everyone" class="click-effect"><span>Everyone</span></label>
              </div>
            </div>
            <div class="module-scrollable">
              <ul class="activity-stream">
                <li class="opponent-team">
                  <span class="opponent-name">Team 1</span> captured India
                </li>
                <li class="your-team">
                  <span class="your-name">My Team</span> captured USA from <span class="opponent-name">Other Team</span>
                </li>
                <li class="your-team">
                  <span class="your-name">My Team</span> captured Afghanistan
                </li>
                <li class="opponent-team">
                  <span class="opponent-name">Team 2</span> captured Canada from <span class="your-name">My Team</span>
                </li>
                <li class="opponent-team">
                  <span class="opponent-name">Team 3</span> captured India
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>;
  }
}

$activity_generated = new ActivityModuleController();
echo $activity_generated->render();