<?php
  $options = array(
    'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
    'cost' => 12,
  );
  
  $password_hash = password_hash($argv[1], PASSWORD_BCRYPT, $options);
  
  echo $password_hash;
?>
