<?hh // strict

include($_SERVER['DOCUMENT_ROOT'] . '/language/language.php');

class :fbbranding extends :x:element {
  category %flow;

  protected string $tagName = 'fbbranding';

  protected function render(): XHPRoot {
    tr_start();
    return
      <span class="branding-el">
        <svg class="icon icon--social-facebook">
          <use href="#icon--social-facebook" />
        </svg>
        <span class="has-icon"> {tr('Powered By Facebook')}</span>
      </span>;
  }
}
