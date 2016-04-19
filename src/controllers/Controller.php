<?hh // strict

abstract class Controller {
  abstract protected function getTitle(): string;
  abstract protected function getFilters(): array<string, mixed>;
  abstract protected function getPages(): array<string>;

  abstract protected function renderBody(string $page): :xhp;

  public function render(): :xhp {
    $page = $this->processRequest();
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
      {$this->renderBody($page)}
      </html>
    </x:doctype>;
  }

  private function processRequest(): string {
    $input_methods = array(
      'POST' => INPUT_POST,
      'GET' => INPUT_GET,
    );
    $method = getSERVER()->get('REQUEST_METHOD');
    invariant(is_string($method), 'REQUEST_METHOD must be a string');

    $filter = idx($this->getFilters(), $method);
    if ($filter === null) {
      // Method not supported
      return ''; // TODO
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