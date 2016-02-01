<?php

require_once('../common/indexrequests.php');
require_once('../common/teams.php');
require_once('../common/logos.php');
require_once('../common/utils.php');

function register_team($teamname, $password, $logo) {
  $teams = new Teams();
  $logos = new Logos();
  $final_logo = $logo;
  if (!$logos->check_exists($final_logo)) {
    $final_logo = $logos->random_logo();
  }
  $hash = hash('sha256', $password);
  $team_id = $teams->create_team($teamname, $hash, $final_logo);

  // TODO: Login the newly created team
  if ($team_id) {
    ok_response();
  } else {
    error_response();
  }
}

function login_team($teamname, $password) {
}

$request = new IndexRequests();
$request->processIndex();

switch ($request->action) {
  case 'none':
    start_page();
    break;
  case 'register_team':
    register_team(
      $request->parameters['teamname'],
      $request->parameters['password'],
      $request->parameters['logo']
    );
    break;
  case 'login_team':
    login_team();
    break;
  default:
    start_page();
    break;
}


?>
