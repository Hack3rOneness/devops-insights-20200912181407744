<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');

sess_start();
sess_enforce_login();

echo <<< EOT
<header class="module-header">
  <h6>Filter</h6>
</header>
<div class="module-content">
  <div class="fb-section-border">
    <div class="module-top">
      <div class="radio-tabs">
        <input type="radio" name="fb--module--filter" id="fb--module--filter--category" checked="">
        <label for="fb--module--filter--category" class="click-effect"><span>Category</span></label>

        <input type="radio" name="fb--module--filter" id="fb--module--filter--point-value">
        <label for="fb--module--filter--point-value" class="click-effect"><span>Point Value</span></label>
      </div>
    </div>
    <div class="module-scrollable">
      <ul class="radio-list radio-tab-content active">
EOT;

$levels = new Levels();
$categories = $levels->all_categories();

foreach ($categories as $category) {
  $lower_category = strtolower($category['category']);
echo <<< EOT
        <li>
          <input type="radio" name="fb--module--filter--category" value="{$category['category']}" id="fb--module--filter--category--{$lower_category}">
          <label for="fb--module--filter--category--{$lower_category}" class="click-effect"><span>{$category['category']}</span></label>
        </li>
EOT;
}

echo <<< EOT
        <li>
          <input type="radio" name="fb--module--filter--category" value="All" id="fb--module--filter--category--all">
          <label for="fb--module--filter--category--all" class="click-effect">All</span></label>
        </li>
      </ul>
    </div>
    
  </div>
</div>
EOT;

?>