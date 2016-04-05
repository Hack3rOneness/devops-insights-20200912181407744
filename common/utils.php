<?hh // strict

function redirect(string $location): void {
  header('Location: '.$location);
}

function login_page(): void {
  redirect('/index.php?page=login');
}

function error_page(): void {
  redirect('/index.php?page=error');
  die();
}

function registration_page(): void {
  redirect('/index.php?page=registration');
}

function game_page(): void {
  redirect('/game.php');
}

function admin_page(): void {
  redirect('/admin.php');
}

function start_page(): void {
  redirect('/index.php');
}

function request_response(string $result, string $msg, string $redirect): void {
  $response_data = array(
    'result' => $result,
    'message' => $msg,
    'redirect' => $redirect,
  );
  echo json_encode($response_data);
}

function hint_response(string $msg, string $result): void {
  $response_data = array(
    'hint' => $msg,
    'result' => $result,
  );
  echo json_encode($response_data);
}

function ok_response(string $msg, string $redirect): void {
  request_response('OK', $msg, $redirect);
}

function error_response(string $msg, string $redirect): void {
  request_response('ERROR', $msg, $redirect);
}

function must_have_idx<Tk, Tv>(
  ?KeyedContainer<Tk, Tv> $arr,
  Tk $idx,
): Tv {
  invariant($arr !== null, 'Container is null');
  $result = idx($arr, $idx);
  invariant($result !== null, sprintf('Index %s not found in container', $idx));
  return $result;
}