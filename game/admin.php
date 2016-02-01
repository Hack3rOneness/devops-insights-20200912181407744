<?php

require_once('../common/adminrequests.php');
require_once('../common/teams.php');
require_once('../common/levels.php');
require_once('../common/utils.php');

$request = new AdminRequests();
$request->processAdmin();

switch ($request->action) {
  case 'none':
    admin_page();
    break;
  default:
    admin_page();
    break;
}


?>
