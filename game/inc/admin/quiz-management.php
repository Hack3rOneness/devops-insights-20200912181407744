<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');

sess_start();
sess_enforce_admin();

function select_countries($selected=0, $used_included = true) {
  $select_html = '<select name="entity_id">';
  $select_html .= '<option value="0">Auto</option>';
  $countries = new Countries();
  $all_countries = ($used_included) 
    ? $countries->all_enabled_countries()
    : $countries->all_available_countries();
  foreach ($all_countries as $country) {
    if ($country['id'] == $selected) {
      $select_html .= '<option value="'.$country['id'].'" selected>'.$country['name'].'</option>';
    } else {
      $select_html .= '<option value="'.$country['id'].'">'.$country['name'].'</option>';
    }
  }
  $select_html .= '</select>';

  return $select_html;
}

$select_available_html = select_countries(0, false);

echo <<< EOT

<header class="admin-page-header">
  <h3>Quiz Management</h3>
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

// Hidden element for creating new quiz level
echo <<< EOT
    <section class="admin-box completely-hidden">
      <form class="level_form quiz_form">
        <input type="hidden" name="level_type" value="quiz">
        <header class="admin-box-header">
          <h3>New Quiz Level</h3>
        </header>

        <div class="fb-column-container">
          <div class="col col-pad col-1-2">
            <div class="form-el el--block-label el--full-text">
              <label>Question</label>
              <textarea name="question" placeholder="Quiz question" rows="4"></textarea>
            </div>

            <div class="form-el el--block-label el--full-text">
              <label for="">Country</label>
              {$select_available_html}
            </div>

            <div class="form-el el--block-label el--full-text">
              <label>Hint</label>
              <input name="hint" type="text">
            </div>
          </div>

          <div class="col col-pad col-1-2">
            <div class="form-el el--block-label el--full-text">
              <label>Answer</label>
              <input name="answer" type="text">
            </div>


            <div class="form-el fb-column-container col-gutters">
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Points</label>
                <input name="points" type="text">
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Bonus</label>
                <input name="bonus" type="text">
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>-Dec</label>
                <input name="bonus_dec" type="text">
              </div>
            </div>

            <div class="form-el fb-column-container col-gutters">
              <div class="col el--block-label el--full-text">
                <label>Hint Penalty</label>
                <input name="penalty" type="text">
              </div>
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

$c = 1;
foreach ($levels->all_quiz_levels() as $quiz) {
  $quiz_question = htmlspecialchars($quiz['description']);
  $quiz_answer = htmlspecialchars($quiz['flag']);
  $quiz_hint = htmlspecialchars($quiz['hint']);
  $quiz_countries_select = select_countries($quiz['entity_id']);
  $quiz_on = ($quiz['active'] == 1)
    ? 'checked'
    : '';
  $quiz_off = ($quiz['active'] == 0)
    ? 'checked'
    : '';

  echo <<< EOT
      <section class="admin-box section-locked">
        <form class="level_form quiz_form" name="quiz_{$quiz['id']}">
        <input type="hidden" name="level_type" value="quiz">
        <input type="hidden" name="level_id" value="{$quiz['id']}">
        <header class="admin-box-header">
          <h3>Quiz Level {$c}</h3>

          <div class="admin-section-toggle radio-inline">
            <input type="radio" name="fb--admin--level-{$quiz['id']}-status" id="fb--admin--level-{$quiz['id']}-status--on" {$quiz_on}>
            <label for="fb--admin--level-{$quiz['id']}-status--on">On</label>

            <input type="radio" name="fb--admin--level-{$quiz['id']}-status" id="fb--admin--level-{$quiz['id']}-status--off" {$quiz_off}>
            <label for="fb--admin--level-{$quiz['id']}-status--off">Off</label>
          </div>
        </header>

        <div class="fb-column-container">
          <div class="col col-pad col-1-2">
            <div class="form-el el--block-label el--full-text">
              <label>Question</label>
              <textarea name="question" rows="4" disabled>{$quiz_question}</textarea>
            </div>

            <div class="form-el el--block-label el--full-text">
              <label for="">Country</label>
              {$quiz_countries_select}
            </div>

            <div class="form-el el--block-label el--full-text">
              <label>Hint</label>
              <input name="hint" type="text" value="{$quiz_hint}" disabled>
            </div>
          </div>

          <div class="col col-pad col-1-2">
            <div class="form-el el--block-label el--full-text">
              <label>Answer</label>
              <input name="answer" type="text" value="{$quiz_answer}" disabled>
            </div>

            <div class="form-el fb-column-container col-gutters">
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Points</label>
                <input name="points" type="text" value="{$quiz['points']}" disabled>
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Bonus</label>
                <input name="bonus" type="text" value="{$quiz['bonus']}" disabled>
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>-Dec</label>
                <input name="bonus_dec" type="text" value="{$quiz['bonus_dec']}" disabled>
              </div>
            </div>

            <div class="form-el fb-column-container col-gutters">
              <div class="col el--block-label el--full-text">
                <label>Hint Penalty</label>
                <input name="penalty" type="text" value="{$quiz['penalty']}" disabled>
              </div>
            </div>
          </div>
        </div>

        <div class="admin-buttons admin-row">
          <div class="button-right">
            <a href="#" class="admin--edit" data-action="edit">EDIT</a>
            <button class="fb-cta cta--red" data-action="delete">Delete</button>
            <button class="fb-cta cta--yellow" data-action="save-no-validation">Save</button>
          </div>
        </div>
      </form>
    </section>
EOT;
  $c++;
}

echo <<< EOT
  </div><!-- .admin-sections -->

  <div class="admin-buttons">
    <button class="fb-cta" data-action="add-new">Add Quiz Level</button>
  </div>
EOT;
?>