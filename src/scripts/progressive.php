<?hh

require_once('/var/www/fbctf/vendor/autoload.php');

while (Progressive::getGameStatus()) {
  Progressive::take();
  sleep(Progressive::getCycle());
}