<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

sess_start();
sess_enforce_login();

class FilterController {
  public function render(): :xhp {
    $categories_ul = <ul class="radio-list radio-tab-content category-filter-content active"></ul>;
    $categories_ul->appendChild(
      <li>
        <input type="radio" name="fb--module--filter--category" value="All" id="fb--module--filter--category--all" checked={true}/>
        <label for="fb--module--filter--category--all" class="click-effect"><span>All</span></label>
      </li>
    );

    $levels = new Levels();
    $categories = $levels->all_categories();

    foreach ($categories as $category) {
      $cagegory_id = 'fb--module--filter--category--'.strtolower($category['category']);
      $categories_ul->appendChild(
        <li>
          <input type="radio" name="fb--module--filter--category" value={$category['category']} id={$cagegory_id}/>
          <label for={$cagegory_id} class="click-effect"><span>{$category['category']}</span></label>
        </li>
      );
    }

    return
      <div>
        <header class="module-header">
          <h6>Filter</h6>
        </header>
        <div class="module-content">
          <div class="fb-section-border">
            <div class="module-top">
              <div class="radio-tabs">
                <input type="radio" name="fb--module--filter" value="category" id="fb--module--filter--category" checked={true}/>
                <label for="fb--module--filter--category" class="click-effect"><span>Category</span></label>
                <input type="radio" name="fb--module--filter" value="status" id="fb--module--filter--status"/>
                <label for="fb--module--filter--status" class="click-effect"><span>Status</span></label>
              </div>
            </div>
            <div class="module-scrollable">
              {$categories_ul}
              <ul class="radio-list radio-tab-content status-filter-content">
                <li>
                  <input type="radio" name="fb--module--filter--status" value="All" id="fb--module--filter--status--all" checked={true}/>
                  <label for="fb--module--filter--status--all" class="click-effect"><span>All</span></label>
                </li>
                <li>
                  <input type="radio" name="fb--module--filter--status" value="completed" id="fb--module--filter--status--completed"/>
                  <label for="fb--module--filter--status--completed" class="click-effect"><span>Completed</span></label>
                </li>
                <li>
                  <input type="radio" name="fb--module--filter--status" value="remaining" id="fb--module--filter--status--remaining"/>
                  <label for="fb--module--filter--status--remaining" class="click-effect"><span>Remaining</span></label>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>;
  }
}

$filter_generated = new FilterController();
echo $filter_generated->render();