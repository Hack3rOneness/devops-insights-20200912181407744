<?hh

require_once('../common/adminrequests.php');
require_once('../common/teams.php');
require_once('../common/levels.php');
require_once('../common/attachments.php');
require_once('../common/links.php');
require_once('../common/logos.php');
require_once('../common/countries.php');
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
  case 'create_quiz':
    $levels = new Levels();
    $levels->create_quiz_level(
      $request->parameters['question'],
      $request->parameters['answer'],
      $request->parameters['entity_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['bonus_dec'],
      $request->parameters['hint'],
      $request->parameters['penalty']
    );
    ok_response();
    break;
  case 'update_quiz':
    $levels = new Levels();
    $levels->update_quiz_level(
      $request->parameters['question'],
      $request->parameters['answer'],
      $request->parameters['entity_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['bonus_dec'],
      $request->parameters['hint'],
      $request->parameters['penalty'],
      $request->parameters['level_id']
    );
    ok_response();
    break;
  case 'create_flag':
    $levels = new Levels();
    $levels->create_flag_level(
      $request->parameters['description'],
      $request->parameters['flag'],
      $request->parameters['entity_id'],
      $request->parameters['category_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['bonus_dec'],
      $request->parameters['hint'],
      $request->parameters['penalty']
    );
    ok_response();
    break;
  case 'update_flag':
    $levels = new Levels();
    $levels->update_flag_level(
      $request->parameters['description'],
      $request->parameters['flag'],
      $request->parameters['entity_id'],
      $request->parameters['category_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['bonus_dec'],
      $request->parameters['hint'],
      $request->parameters['penalty'],
      $request->parameters['level_id']
    );
    ok_response();
    break;
  case 'create_base':
    $levels = new Levels();
    $levels->create_base_level(
      $request->parameters['description'],
      $request->parameters['entity_id'],
      $request->parameters['category_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['hint'],
      $request->parameters['penalty']
    );
    ok_response();
    break;
  case 'update_base':
    $levels = new Levels();
    $levels->update_base_level(
      $request->parameters['description'],
      $request->parameters['entity_id'],
      $request->parameters['category_id'],
      $request->parameters['points'],
      $request->parameters['bonus'],
      $request->parameters['hint'],
      $request->parameters['penalty'],
      $request->parameters['level_id']
    );
    ok_response();
    break;
  case 'delete_level':
    $levels = new Levels();
    $levels->delete_level(
      $request->parameters['level_id']
    );
    ok_response();
    break;
  case 'toggle_status_level':
    $levels = new Levels();
    $levels->toggle_status(
      $request->parameters['level_id'],
      $request->parameters['status']
    );
    ok_response();
    break;
  case 'toggle_status_all':
    if ($request->parameters['all_type'] === 'team') {
      $teams = new Teams();
      $teams->toggle_status_all(
        $request->parameters['status']
      );
      ok_response();
    } else {
      $levels = new Levels();
      $levels->toggle_status_all(
        $request->parameters['status'],
        $request->parameters['all_type']
      );
      ok_response();
    }
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
      $request->parameters['points'],
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
  case 'enable_logo':
    $logos = new Logos();
    $logos->toggle_status(
      $request->parameters['logo_id'],
      1
    );
    ok_response();
    break;
  case 'disable_logo':
    $logos = new Logos();
    $logos->toggle_status(
      $request->parameters['logo_id'],
      0
    );
    ok_response();
    break;
  case 'enable_country':
    $countries = new Countries();
    $countries->toggle_status(
      $request->parameters['country_id'],
      1
    );
    ok_response();
    break;
  case 'disable_country':
    $countries = new Countries();
    $countries->toggle_status(
      $request->parameters['country_id'],
      0
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
  case 'delete_category':
    $levels = new Levels();
    $levels->delete_category(
      $request->parameters['category_id']
    );
    ok_response();
    break;
  case 'create_category':
    $levels = new Levels();
    $levels->create_category(
      $request->parameters['category']
    );
    ok_response();
    break;
  case 'create_attachment':
    $attachments = new Attachments();
    $result = $attachments->create(
      'attachment_file',
      $request->parameters['filename'],
      $request->parameters['level_id']
    );
    if ($result) {
      ok_response();
    }
    break;
  case 'update_attachment':
    $attachments = new Attachments();
    $attachments->update(
      $request->parameters['filename'],
      $request->parameters['level_id']
    );
    ok_response();
    break;
  case 'delete_attachment':
    $attachments = new Attachments();
    $attachments->delete(
      $request->parameters['attachment_id']
    );
    ok_response();
    break;
  case 'create_link':
    $links = new Links();
    $result = $links->create(
      $request->parameters['link'],
      $request->parameters['level_id']
    );
    if ($result) {
      ok_response();
    }
    break;
  case 'update_link':
    $links = new Links();
    $links->update(
      $request->parameters['link'],
      $request->parameters['link_id']
    );
    ok_response();
    break;
  case 'delete_link':
    $links = new Links();
    $links->delete(
      $request->parameters['link_id']
    );
    ok_response();
    break;
  default:
    admin_page();
    break;
}
