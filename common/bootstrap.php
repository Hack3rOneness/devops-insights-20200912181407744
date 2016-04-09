<?php
// bootstrap.php
require_once "../vendor/autoload.php";

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$isDevMode = true;

// the connection configuration
$dbParams = array(
    'driver'   => 'pdo_mysql',
    'user'     => 'ctf2',
    'password' => 'ctf2',
    'dbname'   => 'facebook-ctf2',
);

$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__), $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);
