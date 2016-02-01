<?php

require_once('../common/gamerequests.php');
require_once('../common/levels.php');
require_once('../common/teams.php');
require_once('../common/utils.php');

$request = new GameRequests();
$request->processGame();

switch ($request->action) {
  case 'none':
    game_page();
    break;
  default:
    game_page();
    break;
}


?>
