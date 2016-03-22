<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/configuration.php');

sess_start();
sess_enforce_login();

class ConfigurationController {
	private function jsonSend($data) {
		header('Content-Type: application/json');
		print json_encode($data, JSON_PRETTY_PRINT);
	}

	public function generateConfiguration() {
		$conf_data = (object) array();

		$c = new Configuration();

		$conf_data->{'currentTeam'} = sess_teamname();
		$conf_data->{'refreshTeams'} = $c->get('teams_cycle');
		$conf_data->{'refreshMap'} = $c->get('map_cycle');
		$conf_data->{'refreshConf'} = $c->get('conf_cycle');
		$conf_data->{'refreshCmd'} = $c->get('cmd_cycle');

		$this->jsonSend($conf_data);
	}
}

$conf = new ConfigurationController();
$conf->generateConfiguration();
