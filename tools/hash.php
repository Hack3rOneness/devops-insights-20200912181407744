<?hh

$options = array(
  'Cost' => 12,
);

$password_hash = password_hash($argv[1], PASSWORD_DEFAULT, $options);

echo $password_hash;