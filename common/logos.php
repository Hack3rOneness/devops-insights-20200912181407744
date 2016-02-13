<?php

require_once('db.php');

class Logos {
  private $db;

  function __construct() {
    $this->db = DB::getInstance();
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

  // Enable or disable logo by passing 1 or 0.
  public function toggle_status($logo_id, $active) {
    $sql = 'UPDATE logos SET enabled = ? WHERE id = ? LIMIT 1';
    $elements = array($active, $logo_id);
    $this->db->query($sql, $elements);
  }

  // Retrieve a random logo from the table.
  public function random_logo() {
    $sql = 'SELECT name FROM logos WHERE enabled = 1 ORDER BY RAND() LIMIT 1';
    $this->db->query($sql)[0]['name'];
  }

  // Retrieve how many teams are using one logo.
  public function who_uses($logo) {
    $sql = 'SELECT * FROM teams WHERE logo = ?';
    $element = array($logo);
    return $this->db->query($sql, $element);
  }

  // All the logos.
  public function all_logos() {
    $sql = 'SELECT * FROM logos';
    return $this->db->query($sql);
  }

  // All the enabled logos.
  public function all_enabled_logos() {
    $sql = 'SELECT * FROM logos WHERE enabled = 1';
    return $this->db->query($sql);
  }
}
