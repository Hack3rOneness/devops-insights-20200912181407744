<?hh // strict

class Router {
  public static function route(): :xhp {
    $page = getGET()->get('p');
    if (!is_string($page)) {
      return (new IndexController())->render();
    }

    switch ($page) {
    case "admin":
      return (new AdminController())->render();
    case "index":
        return (new IndexController())->render();
    case "game":
      return (new GameboardController())->render();
    case "view":
      return (new ViewModeController())->render();
    default:
      return <div></div>; // TODO: 404
    }
  }
}
