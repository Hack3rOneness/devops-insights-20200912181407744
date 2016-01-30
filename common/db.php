<?php

/*
  DB Object
  Handles all DB Connections & Queries.
  Anything that will use the DB object requires to pull both the db.php
  and the config.php.
*/

class DBObject {
  private $config = null;
  public $dbh = null;
  public $connected = false;

  function __construct($config) {
    $this->config = $config->settings;
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
    }
  }

  public function disconnect() {
    $this->dbh = null;
    $this->connected = false;
  }

  public function query($query, $elements = null) {
    // error_log($query);
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
    }

    $results = array();
    while ($row = $stmt->fetch()) {
      $results[] = $row;
    }
    return $results;
  }
}
