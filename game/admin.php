<?php

require_once('../common/adminrequests.php');
require_once('../common/teams.php');
require_once('../common/levels.php');
require_once('../common/logos.php');
require_once('../common/sessions.php');
require_once('../common/utils.php');

sess_start();
sess_enforce_admin();

$request = new AdminRequests();
$request->processAdmin();

switch ($request->action) {
  case 'none':
    admin_page();
    break;
  case 'create_team':
    $teams = new Teams();
    $password = hash('sha256', $request->parameters['password']);
    $teams->create_team(
      $request->parameters['name'],
      $password,
      $request->parameters['logo']
    );
    ok_response();
    break;
  case 'update_team':
    $teams = new Teams();
    $password = $request->parameters['password'];
    $password2 = $request->parameters['password2'];
    $new_password = $password;
    if ($password != $password2) {
      $new_password = hash('sha256', $password);
    }
    $teams->update_team(
      $request->parameters['name'],
      $new_password,
      $request->parameters['logo'],
      $request->parameters['team_id']
    );
    ok_response();
    break;
  case 'toggle_admin_team':
    $teams = new Teams();
    $teams->toggle_admin(
      $request->parameters['team_id'],
      $request->parameters['admin']
    );
    ok_response();
    break;
  case 'toggle_status_team':
    $teams = new Teams();
    $teams->toggle_status(
      $request->parameters['team_id'],
      $request->parameters['status']
    );
    ok_response();
    break;
  case 'toggle_visible_team':
    $teams = new Teams();
    $teams->toggle_visible(
      $request->parameters['team_id'],
      $request->parameters['visible']
    );
    ok_response();
    break;
  case 'delete_team':
    $teams = new Teams();
    $teams->delete_team(
      $request->parameters['team_id']
    );
    ok_response();
    break;
  case 'update_session':
    sess_write(
      $request->parameters['cookie'],
      $request->parameters['data']
    );
    ok_response();
    break;
  case 'delete_session':
    sess_destroy(
      $request->parameters['cookie']
    );
    ok_response();
    break;
  default:
    admin_page();
    break;
}

?>