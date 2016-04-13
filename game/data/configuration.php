<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ConfigurationController extends DataController {
  // Refresh rate for teams/leaderboard in milliseconds
  private string $teams_cycle = "5000";
  // Refresh rate for map/announcements in milliseconds
  private string $map_cycle = "5000";
  // Refresh rate for configuration values in milliseconds
  private string $conf_cycle = "10000";
  // Refresh rate for commands in milliseconds
  private string $cmd_cycle = "10000";

  public function generateData() {
    $conf_data = (object) array();

    $control = new Control();

    $conf_data->{'currentTeam'} = sess_teamname();
    $conf_data->{'gameboard'} = Configuration::get('gameboard')->getValue();
    $conf_data->{'refreshTeams'} = $this->teams_cycle;
    $conf_data->{'refreshMap'} = $this->map_cycle;
    $conf_data->{'refreshConf'} = $this->conf_cycle;
    $conf_data->{'refreshCmd'} = $this->cmd_cycle;
    $conf_data->{'progressiveCount'} = $control->progressive_count();

    $this->jsonSend($conf_data);
  }
}

$confController = new ConfigurationController();
$confController->generateData();
