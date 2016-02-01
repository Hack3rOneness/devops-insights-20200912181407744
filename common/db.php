<?php

require_once('utils.php');

class DB {
  private $settings_file = 'settings.ini';
  private $config = null;
  public $dbh = null;
  public $connected = false;

  function __construct() {
    $this->config = parse_ini_file($this->settings_file);
  }

  function __destruct() {
    $this->connected = false;
    $this->dbh = null;
  }

  public function connect() {
    try {
      $conn_str = 'mysql:host='.$this->config['DB_HOST'].';'.
        'port='.$this->config['DB_PORT'].';'.
        'dbname='.$this->config['DB_NAME'];
      $this->dbh = new PDO(
        $conn_str,
        $this->config['DB_USERNAME'],
        $this->config['DB_PASSWORD']
      );
      $this->connected = true;

    } catch (PDOException $e) {
      error_log("[ db.php ] - Connection error: ".$e->getMessage());
      error_page();
    }
  }

  public function disconnect() {
    $this->dbh = null;
    $this->connected = false;
  }

  public function query($query, $elements = null) {
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
      error_page();
    }

    $results = array();
    while ($row = $stmt->fetch()) {
      $results[] = $row;
    }
    return $results;
  }
}
