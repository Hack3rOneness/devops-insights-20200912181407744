<?hh // strict

include($_SERVER['DOCUMENT_ROOT'] . '/language/language.php');

abstract class ModalController {
  public abstract function genRender(string $modal): Awaitable<:xhp>;
}
