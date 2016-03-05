<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/teams.php');

function teams_select() {
  $teams = new Teams();
  $html = '';
  foreach ($teams->all_active_teams() as $team) {
    $team_id = $team['id'];
    $team_name = htmlspecialchars($team['name']);
    $html .= "<option value=$team_id>$team_name</option>\n";
  }

  return $html;
}

$teams_select_html = teams_select();
echo <<< EOT
<main role="main" class="fb-main page--login full-height fb-scroll">

    <header class="fb-section-header fb-container">
        <h1 class="fb-glitch" data-text="Team Login">Team Login</h1>
        <p class="inner-container">Please login here. If you have not registered, you may do so by clicking "Sign Up" below. </p>
    </header>

    <div class="fb-login">
        <form class="fb-form">
            <input type="hidden" name="action" value="login_team">
            <fieldset class="form-set fb-container container--small">
                <div class="form-el el--text">
                    <label for="">Team Name</label>
                    <select name="team_id">
                        <option value="0">Select</option>
                        {$teams_select_html}
                    </select>
                </div>
                <div class="form-el el--text">
                    <label for="">Password</label>
                    <input name="password" type="password">
                </div>
            </fieldset>

            <div class="form-el--actions">
                <button id="login_button" class="fb-cta cta--yellow" type="button" onclick="loginTeam()">Login</button>
            </div>

            <div class="form-el--footer">
                <a href="#registration">Sign Up</a>
            </div>

        </form>

    </div><!-- .fb-login -->

</main><!-- .fb-main -->
EOT;
