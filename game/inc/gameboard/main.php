<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');

sess_start();
sess_enforce_login();

?>
<div id="fb-gameboard" class="fb-gameboard">

    <div class="gameboard-header">
        <nav class="fb-navigation fb-gameboard-nav">
            <ul class="nav-left">
                <li>
                    <a>Navigation</a>
                    <ul class="subnav">
                        <li><a href="/viewer-mode.php">View Mode</a></li>
                        <li><a href="#" class="fb-init-tutorial">Tutorial</a></li>
                        <?php
                          if (sess_admin()) {
                            echo '<li><a href="admin.php">Admin</a></li>';
                          }
                        ?>
                        <li><a href="#" class="js-prompt-logout">Logout</a></li>
                    </ul>
                </li>
            </ul>

            <div class="branding">
                <a href="gameboard.php">
                    <div class="branding-rules">
                        <span class="branding-el"><svg class="icon icon--social-facebook"><use xlink:href="#icon--social-facebook" /></svg> <span class="has-icon">Powered By Facebook</span></span>
                    </div>
                </a>
            </div>

            <ul class="nav-right">
                <li>
                    <a href="#" class="js-launch-modal" data-modal="scoreboard">Scoreboard</a>
                </li>
            </ul>
        </nav>



        <div class="radio-tabs fb-map-select">
            <input type="radio" name="fb--map-select" id="fb--map-select--you" value="your-team">
            <label for="fb--map-select--you" class="click-effect"><span class="your-name"><svg class="icon icon--team-indicator your-team"><use xlink:href="#icon--team-indicator"></use></svg>You</span></label>

            <input type="radio" name="fb--map-select" id="fb--map-select--enemy" value="opponent-team">
            <label for="fb--map-select--enemy" class="click-effect"><span class="opponent-name"><svg class="icon icon--team-indicator opponent-team"><use xlink:href="#icon--team-indicator"></use></svg>Enemy</span></label>

            <input type="radio" name="fb--map-select" id="fb--map-select--all" value="all" checked="">
            <label for="fb--map-select--all" class="click-effect"><span>All</span></label>
        </div>
    </div><!-- .gameboard-header -->



    <div class="fb-map"></div><!-- .fb-map -->


    <div class="fb-listview"></div><!-- .fb-listmode -->



    <div class="fb-module-container container--column column-left">
        <aside data-name="Leaderboard" data-module="leaderboard"></aside>
        <aside data-name="Activity" data-module="activity"></aside>
    </div>
    <div class="fb-module-container container--column column-right">
        <aside data-name="Teams" data-module="teams"></aside>
        <aside data-name="Filter" data-module="filter"></aside>
    </div>
    <div class="fb-module-container container--row">
        <aside data-name="World Domination" class="module--outer-left" data-module="world-domination"></aside>
        <aside data-name="Game Clock" class="module--outer-right" data-module="game-clock"></aside>
    </div>


</div>
