<?hh

function login_page() {
  header('Location: /index.php#login');
}

function error_page() {
  header('Location: /index.php#error');
  die();
}

function registration_page() {
  header('Location: /index.php#registration');
}

function game_page() {
  header('Location: /gameboard.php');
}

function admin_page() {
  header('Location: /admin.php');
}

function start_page() {
  header('Location: /index.php');
}

function request_response($msg) {
  $response_data = array(
    'result' => $msg
  );
  echo json_encode($response_data);
}

function ok_response() {
  request_response('OK');
}

function error_response() {
  request_response('ERROR');
}
