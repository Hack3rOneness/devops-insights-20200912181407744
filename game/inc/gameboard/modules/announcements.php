<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class AnnouncementsModuleController {
  public function render(): :xhp {
    return
      <div>
        <header class="module-header">
          <h6>Announcements</h6>
        </header>
        <div class="module-content">
          <div class="fb-section-border">
            <div class="module-top">
            </div>
            <div class="module-scrollable">
              <ul class="activity-stream">
                <li><span class="announcement-highlight">Level</span> has been opened</li>
                <li><span class="announcement-highlight">Level</span> has been closed</li>
              </ul>
            </div>
          </div>
        </div>
      </div>;
  }
}

$announcements_generated = new AnnouncementsModuleController();
echo $announcements_generated->render();