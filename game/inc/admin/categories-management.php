<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');

sess_start();
sess_enforce_admin();

echo <<< EOT

<header class="admin-page-header">
  <h3>Categories Management</h3>
  <!--
    * @note
    * this will reflect the last saved time inside the
    *  "highlighted" span
-->
<span class="admin-section--status">status_<span class="highlighted">2015.10.15</span></span>
</header>

<div class="admin-sections">

EOT;

$levels = new Levels();
$categories = $levels->all_categories();

// Hidden element for creating new categories
echo <<< EOT
  <section class="admin-box completely-hidden">
    <form class="categories_form">
      <header class="admin-box-header">
        <h3>New Category</h3>
      </header>
      <div class="fb-column-container">
        <div class="col col-pad">
          <div class="form-el el--block-label el--full-text">
            <label class="admin-label" for="">Category: </label>
            <input name="category" type="text" value="">
          </div>
        </div>
      </div>

      <div class="admin-buttons admin-row">
        <div class="button-right">
          <a href="#" class="admin--edit" data-action="edit">EDIT</a>
          <button class="fb-cta cta--red" data-action="delete">Delete</button>
          <button class="fb-cta cta--yellow" data-action="create">Create</button>
        </div>
      </div>
      
    </form>
  </section>
EOT;

foreach ($categories as $category) {

  echo <<< EOT
  <section class="admin-box">
  <form class="categories_form">
    <input type="hidden" name="category_id" value="{$category['id']}">
    <header class="countries-management-header">
      <h6>ID{$category['id']}</h6>
      <a class="highlighted--red" href="#" data-action="delete">DELETE</a>
    </header>

    <div class="fb-column-container">

      <div class="col col-pad">
        <div class="selected-logo">
          <label>Category: </label>
          <span class="logo-name">{$category['category']}</span>
        </div>
      </div>

    </div>
    </form>
  </section>
EOT;
}

echo <<< EOT
</div><!-- .admin-sections -->

<div class="admin-buttons">
  <button class="fb-cta" data-action="add-new">Add Category</button>
</div>
EOT;
?>