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

  abstract public function renderBody(string $page): :xhp;

  public function render(string $title, string $page): :xhp {
    return
      <x:doctype>
      <html lang="en">
      <head>
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
}