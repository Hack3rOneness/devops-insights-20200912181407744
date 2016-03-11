<?hh

include('../vendor/autoload.php');

abstract class Controller {

  abstract public function renderBody(): :xhp;

  public function render(string $title): :xhp {
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
      {$this->renderBody()}
      </html>
    </x:doctype>;
  }
}