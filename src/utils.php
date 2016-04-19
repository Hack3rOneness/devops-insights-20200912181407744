<?hh // strict

/* HH_IGNORE_ERROR[2001] */
const MUST_MODIFY = /* UNSAFE_EXPR */ "<<must-modify:\xEE\xFF\xFF>";

function getGET(): Map<string, mixed> {
  /* HH_IGNORE_ERROR[2050] */
  return new Map($_GET);
}

function getPOST(): Map<string, mixed> {
  /* HH_IGNORE_ERROR[2050] */
  return new Map($_POST);
}

function getSERVER(): Map<string, mixed> {
  /* HH_IGNORE_ERROR[2050] */
  return new Map($_SERVER);
}

function getFILES(): Map<string, array<string, mixed>> {
  /* HH_IGNORE_ERROR[2050] */
  return new Map($_FILES);
}

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
  redirect('/index.php?p=game');
}

function admin_page(): void {
  redirect('/index.php?p=admin');
}

function start_page(): void {
  redirect('/index.php');
}

function request_response(string $result, string $msg, string $redirect): string {
  $response_data = array(
    'result' => $result,
    'message' => $msg,
    'redirect' => $redirect,
  );
  return json_encode($response_data);
}

function hint_response(string $msg, string $result): string {
  $response_data = array(
    'hint' => $msg,
    'result' => $result,
  );
  return json_encode($response_data);
}

function ok_response(string $msg, string $redirect): string {
  return request_response('OK', $msg, $redirect);
}

function error_response(string $msg, string $redirect): string {
  return request_response('ERROR', $msg, $redirect);
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

function firstx<T>(Traversable<T> $t): T {
  foreach ($t as $v) {
    return $v;
  }
  invariant_violation('Expected non-empty collection');
}