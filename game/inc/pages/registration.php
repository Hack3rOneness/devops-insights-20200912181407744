<main role="main" class="fb-main page--registration full-height fb-scroll">

    <header class="fb-section-header fb-container">
        <h1 class="fb-glitch" data-text="Team Registration">Team Registration</h1>
        <p class="inner-container">Register to play Capture The Flag here. Once you have registered, you will be logged in.</p>
    </header>

    <div class="fb-registration">
        <form class="fb-form">
            <input type="hidden" name="action" value="register_team">
            <fieldset class="form-set fb-container container--small">
                <div class="form-el el--text">
                    <label for="">Team Name</label>
                    <input name="teamname" type="text" size="20">
                </div>
                <div class="form-el el--text">
                    <label for="">Password</label>
                    <input name="password" type="password">
                </div>
            </fieldset>

            <div class="fb-choose-emblem">
                <h6>Choose an Emblem</h6>

                <div class="emblem-carousel"></div><!-- .emblem-carousel -->
            </div>

            <div class="form-el--actions fb-container container--small">
                <p><button id="register_button" class="fb-cta cta--yellow" type="button" onclick="registerTeam()">Sign Up</button></p>
            </div>

        </form>

    </div><!-- .fb-registration -->


</main><!-- .fb-main -->
