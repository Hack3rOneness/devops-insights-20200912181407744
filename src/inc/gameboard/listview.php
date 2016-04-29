<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

SessionUtils::sessionStart();
SessionUtils::enforceLogin();

class ListviewController {
  public function render(): :xhp {
    $listview_div = <div class="listview-container"></div>;
    $listview_table = <table></table>;

    foreach (Level::allActiveLevels() as $level) {
      $country = Country::get(intval($level->getId()));
      $category = Category::getSingleCategory($level->getCategoryId());
      if (Level::previousScore($level->getId(), SessionUtils::sessionTeam(), false)) {
        $span_status = <span class="fb-status status--yours">Captured</span>;
      } else {
        $span_status = <span class="fb-status status--open">Open</span>;
      }
      $listview_table->appendChild(
        <tr data-country={$country->getName()}>
          <td style="width: 38%;">{$country->getName()} ({$level->getTitle()})</td>
          <td style="width: 10%;">{strval($level->getPoints())}</td>
          <td style="width: 22%;">{$category->getCategory()}</td>
          <td style="width: 30%;">{$span_status}</td>
        </tr>
      );
    }
    $listview_div->appendChild($listview_table);

    return $listview_div;
  }
}

$listview_generated = new ListviewController();
echo $listview_generated->render();
