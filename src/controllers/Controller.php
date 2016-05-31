<?hh // strict

abstract class Controller {
  abstract protected function getTitle(): string;
  abstract protected function getFilters(): array<string, mixed>;
  abstract protected function getPages(): array<string>;

  abstract protected function genRenderBody(string $page): Awaitable<:xhp>;

  public async function genRender(): Awaitable<:xhp> {
    $page = $this->processRequest();
    $body = await $this->genRenderBody($page);
    return
      <x:doctype>
      <html lang="en">
      <head>
        <meta http-equiv="Cache-control" content="no-cache"/>
        <meta http-equiv="Expires" content="-1"/>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>{$this->getTitle()}</title>
        <link rel="icon" type="image/png" href="static/img/favicon.png"/>
        <link rel="stylesheet" href="static/css/fb-ctf.css"/>
      </head>
        {$body}
      </html>
    </x:doctype>;
  }

  private function processRequest(): string {
    $input_methods = array(
      'POST' => INPUT_POST,
      'GET' => INPUT_GET,
    );
    $method = must_have_string(Utils::getSERVER(), 'REQUEST_METHOD');

    $filter = idx($this->getFilters(), $method);
    if ($filter === null) {
      // Method not supported
      return 'none';
    }

    $input_method = must_have_idx($input_methods, $method);
    $page = 'main';

    $parameters = filter_input_array(
      $input_method,
      $filter,
    );

    $page = idx($parameters, 'page', 'main');
    if (!in_array($page, $this->getPages())) {
      $page = 'main';
    }

    return $page;
  }
}
