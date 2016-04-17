<?hh

final class UNSAFE_HTML
  implements XHPAlwaysValidChild, XHPUnsafeRenderable {
  private $html;

  public function __construct($html) {
    $this->html = $html;
  }

  public function toHTMLString() {
    return $this->html;
  }
}

abstract class Controller {
  public function UNSAFE_HTML($html) {
    return new UNSAFE_HTML($html);
  }

  protected function getFilters(): array<mixed> {
    return array();
  }

  protected function getActions(): array<string> {
    return array();
  }

  protected function getPages(): array<string> {
    return array();
  }

  abstract public function renderBody(string $page): :xhp;

  public function render(string $title, string $page): :xhp {
    $this->processRequest();
    return
      <x:doctype>
      <html lang="en">
      <head>
        <meta http-equiv="Cache-control" content="no-cache"/>
        <meta http-equiv="Expires" content="-1"/>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>{$title}</title>
        <link rel="icon" type="image/png" href="static/img/favicon.png"/>
        <link rel="stylesheet" href="static/css/fb-ctf.css"/>
      </head>
      {$this->renderBody($page)}
      </html>
    </x:doctype>;
  }

  public function processRequest() {
    $inputMethods = array(
      'POST' => INPUT_POST,
      'GET' => INPUT_GET,
    );
    $method = getSERVER['REQUEST_METHOD'];
    $input_method = $inputMethods[$this->method];

    $action = 'none';
    $page = 'main';

    $parameters = filter_input_array(
      $this->input_method,
      $this->getFilters()[$this->method];
    );
    if ($this->parameters['action']) {
      $this->action = $this->parameters['action'];
    }
    if ($this->parameters['page']) {
      $this->page = $this->parameters['page'];
    }
    if ((!in_array($this->action, $this->getActions()))) {
      $this->action = 'none';
    }
    if ((!(in_array $this->page, $this->getPages()))) {
      $this->page = 'main';
    }
  }
}