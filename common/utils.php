<?hh

function login_page() {
  header('Location: /index.html#login');
}

function error_page() {
  header('Location: /index.html#error');
  die();
}

function registration_page() {
  header('Location: /index.html#registration');
}

function game_page() {
  header('Location: /gameboard.html');
}

function admin_page() {
  header('Location: /admin.html');
}

function start_page() {
  header('Location: /index.html');
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
