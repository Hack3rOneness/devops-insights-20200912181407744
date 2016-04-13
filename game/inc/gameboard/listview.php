<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class ListviewController {
  public function render(): :xhp {
    $listview_div = <div class="listview-container"></div>;
    $listview_table = <table></table>;

    $levels = new Levels();
    $teams = new Teams();

    foreach ($levels->all_levels(1) as $level) {
      $country = Country::get(intval($level['entity_id']));
      $category = $levels->get_category($level['category_id']);
      if ($levels->previous_score($level['id'], sess_team())) {
        $span_status = <span class="fb-status status--yours">Captured</span>;
      } else {
        $span_status = <span class="fb-status status--open">Open</span>;
      }
      $listview_table->appendChild(
        <tr data-country={$country->getName()}>
          <td style="width: 38%;">{$country->getName()} ({$level['title']})</td>
          <td style="width: 10%;">{$level['points']}</td>
          <td style="width: 22%;">{$category['category']}</td>
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
