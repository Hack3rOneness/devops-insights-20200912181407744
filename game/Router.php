<?hh // strict

require_once('../vendor/autoload.php');

class Router {
  public static function route(): :xhp {
    $page = getGET()['page'];
    if ($page === null || !is_string($page)) {
      return <div>"Error"</div>; // TODO
    }
    switch ($page) {
    case "admin":
      return new AdminController()->render('', '');
    case "index":
      break;
    case "game":
      break;
    }
    //$request = new Request($filters, $actions, array());
    return <div></div>;
  }
}

/* HH_IGNORE_ERROR[1002] */
echo 'here';
echo Router::route();