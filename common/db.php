<?php

require_once('config.db');

class DB {
  private $config = null;
  public $dbh = null;
  public $connected = false;

  function __construct($config) {
    $this->config = $config->settings;
  }

  function __destruct() {
    $this->connected = false;
    $this->dbh = null;
  }

  public function connect() {
    define('DB_HOST',     $this->config['DB_HOST']);
    define('DB_PORT',     $this->config['DB_PORT']);
    define('DB_NAME',     $this->config['DB_NAME']);
    define('DB_USERNAME', $this->config['DB_USERNAME']);
    define('DB_PASSWORD', $this->config['DB_PASSWORD']);
    try {
      $conn_str = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME;
      $this->dbh = new PDO($conn_str, DB_USERNAME, DB_PASSWORD);
      $this->connected = true;

    } catch (PDOException $e) {
      error_log("[ db.php ] - Connection error: ".$e->getMessage());
      header('Location: /error.html');
      die();
    }
  }

  public function disconnect() {
    $this->dbh = null;
    $this->connected = false;
  }

  public static function query($query, $elements = null) {
    if (!$this->connected) {
      $this->connect();
    }
    $stmt = $this->dbh->prepare($query);
    if ($elements !== null) {
      $i = 1;
      foreach ($elements as &$element) {
        $stmt->bindparam($i, $element);
        $i++;
      }
    }

    try {
      $stmt->execute();
    } catch (PDOException $e) {
      error_log("[ db.php ] - Statement error: " . $stmt->errorInfo());
      header('Location: /error.html');
      die();
    }

    $results = array();
    while ($row = $stmt->fetch()) {
      $results[] = $row;
    }
    return $results;
  }
}
