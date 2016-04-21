<?hh

require_once('/var/www/facebook-ctf/vendor/autoload.php');

while (Progressive::getGameStatus()) {
	Progressive::take();
  sleep(Progressive::getCycle());
}