<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/teams.php');

sess_start();
sess_enforce_login();

$teams = new Teams();
$rank = 1;
$leaderboard = $teams->leaderboard();
$teams_data = (object) array();
foreach ($leaderboard as $team) {
	$team_data = (object) array(
		'badge' => $team['logo'],
		'team_members' => array(),
		'rank' => $rank,
		'school_level' => 'collegiate',
		'points' => array(
			'base' => 0,
			'quiz' => 0,
			'flag' => 0,
			'total' => (int)$team['points']
		)
	);
	$teams_data->{$team['name']} = $team_data;
	$rank++;
}

header('Content-Type: application/json');
print json_encode($teams_data, JSON_PRETTY_PRINT);

?>
