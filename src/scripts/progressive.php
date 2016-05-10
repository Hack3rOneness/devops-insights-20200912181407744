<?hh

require_once('/var/www/fbctf/vendor/autoload.php');

while (\HH\Asio\join(Progressive::genGameStatus())) {
  \HH\Asio\join(Progressive::genTake());
  sleep(\HH\Asio\join(Progressive::genCycle()));
}