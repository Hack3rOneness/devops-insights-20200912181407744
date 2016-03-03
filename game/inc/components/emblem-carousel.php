<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/logos.php');


echo <<< EOT
<div class="fb-slider fb-container container--large">
  <ul class="slides">
EOT;

$logos = new Logos();
foreach ($logos->all_enabled_logos() as $logo) {
  $logo_name = htmlspecialchars($logo['name']);

  echo <<< EOT
    <li><svg class="icon--badge"><use xlink:href="#icon--badge-{$logo_name}"/></svg></li>
EOT;
}

echo <<< EOT
  </ul>
</div>
EOT;

?>
