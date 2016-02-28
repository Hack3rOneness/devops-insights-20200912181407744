<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');

sess_start();
sess_enforce_login();

$conf_data = (object) array();

$conf_data->{'currentTeam'} = sess_teamname();
$conf_data->{'refreshTeams'} = 5000;
$conf_data->{'refreshCountries'} = 5000;
$conf_data->{'refreshConf'} = 30000;
$conf_data->{'refreshCmd'} = 30000;


header('Content-Type: application/json');
print json_encode($conf_data, JSON_PRETTY_PRINT);

?>
