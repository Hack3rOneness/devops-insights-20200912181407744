<?hh

require_once('../vendor/autoload.php');

try {
  echo Router::route();
} catch (RedirectException $e) {
  http_response_code($e->getStatusCode());
  redirect($e->getPath());
}
