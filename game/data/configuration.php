<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ConfigurationController extends DataController {
  public function generateData() {
    $conf_data = (object) array();

    $c = new Configuration();

    $conf_data->{'currentTeam'} = sess_teamname();
    $conf_data->{'teams'} = $c->get('teams');
    $conf_data->{'refreshTeams'} = $c->get('teams_cycle');
    $conf_data->{'map'} = $c->get('map');
    $conf_data->{'refreshMap'} = $c->get('map_cycle');
    $conf_data->{'conf'} = $c->get('conf');
    $conf_data->{'refreshConf'} = $c->get('conf_cycle');
    $conf_data->{'cmd'} = $c->get('cmd');
    $conf_data->{'refreshCmd'} = $c->get('cmd_cycle');
    $conf_data->{'progressiveCount'} = $c->progressive_count();

    $this->jsonSend($conf_data);
  }
}

$conf = new ConfigurationController();
$conf->generateData();
