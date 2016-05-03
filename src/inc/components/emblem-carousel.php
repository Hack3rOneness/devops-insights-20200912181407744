<?hh // strict

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

class LogosController {
  public async function genRender(): Awaitable<:xhp> {
    $logos_div = <div class="fb-slider fb-container container--large"></div>;
    $logos_ul = <ul class="slides"></ul>;

    $logos = await Logo::genAllEnabledLogos();
    foreach ($logos as $logo) {
      $xlink_href = '#icon--badge-'.$logo->getName();
      $logos_ul->appendChild(
        <li>
          <svg class="icon--badge">
            <use href={$xlink_href}></use>
          </svg>
        </li>
      );
    }

    $logos_div->appendChild($logos_ul);
    return $logos_div;
  }
}

/* HH_IGNORE_ERROR[1002] */
$logos_generated = new LogosController();
echo \HH\Asio\join($logos_generated->genRender());
