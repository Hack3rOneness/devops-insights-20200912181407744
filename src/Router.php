<?hh // strict

class Router {
  public static async function genRoute(): Awaitable<string> {
    $page = idx(Utils::getGET(), 'p');
    if (!is_string($page)) {
      $page = 'index';
    }
    $ajax = Utils::getGET()->get('ajax') === 'true';

    if ($ajax) {
      return await self::genRouteAjax($page);
    } else {
      $response = await self::genRouteNormal($page);
      return strval($response);
    }
  }

  private static async function genRouteAjax(
    string $page,
  ): Awaitable<string> {
    switch ($page) {
    case 'index':
      return await (new IndexAjaxController())->genHandleRequest();
    case 'admin':
      return await (new AdminAjaxController())->genHandleRequest();
    case 'game':
      return await (new GameAjaxController())->genHandleRequest();
    default:
      throw new NotFoundRedirectException();
    }
  }

  private static async function genRouteNormal(string $page): Awaitable<:xhp> {
    switch ($page) {
    case 'admin':
      return await (new AdminController())->genRender();
    case 'index':
      return await (new IndexController())->genRender();
    case 'game':
      return await (new GameboardController())->genRender();
    case 'view':
      return await (new ViewModeController())->genRender();
    case 'logout':
      // TODO: Make a confirmation modal?
      SessionUtils::sessionStart();
      SessionUtils::sessionLogout();
      invariant(false, 'should not reach here');
    default:
      throw new NotFoundRedirectException();
    }
  }
}
