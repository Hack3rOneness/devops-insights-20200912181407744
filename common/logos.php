<?php

require_once('db.php');

class Logos {
  private $db;

  function __construct() {
    $db = new DB();
    $this->db = $db;
    if (!$this->db->connected) {
      $this->db->connect();
    }
  }

  // Check to see if the team is active.
  public function check_exists($logo) {
    $sql = 'SELECT COUNT(*) FROM logos WHERE name = ? AND enabled = 1 LIMIT 1';
    $elements = array($logo);
    $exists = $this->db->query($sql, $elements);
    return (bool)$exists[0]['COUNT(*)'];
  }

  // Retrieve a random logo from the table.
  public function random_logo() {
    $sql = 'SELECT name FROM logos WHERE enabled = 1 ORDER BY RAND() LIMIT 1';
    $this->db->query($sql)[0]['name'];
  }
}
