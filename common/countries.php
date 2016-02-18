<?php

require_once('db.php');

class Countries {
  private $db;

  function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->connected) {
      $this->db->connect();
    }
  }

  // Retrieve how many levels are using one country.
  public function who_uses($country_id) {
    $sql = 'SELECT * FROM levels WHERE entity_id = ?';
    $element = array($country_id);
    return $this->db->query($sql, $element);
  }

  // Enable or disable country by passing 1 or 0.
  public function toggle_status($country_id, $status) {
    $sql = 'UPDATE countries SET enabled = ? WHERE id = ? LIMIT 1';
    $elements = array($status, $country_id);
    $this->db->query($sql, $elements);
  }

  // Check if country is enabled.
  public function is_enabled($country_id) {
    $sql = 'SELECT enabled FROM countries WHERE id = ?';
    $element = array($country_id);
    return (bool)($this->db->query($sql, $element) == 1);
  }

  // Mark a country as used by passing 1 or 0.
  public function toggle_used($country_id, $status) {
    $sql = 'UPDATE countries SET used = ? WHERE id = ? LIMIT 1';
    $elements = array($status, $country_id);
    $this->db->query($sql, $elements);
  }

  // Check if country is used.
  public function is_used($country_id) {
    $sql = 'SELECT used FROM countries WHERE id = ?';
    $element = array($country_id);
    return (bool)($this->db->query($sql, $element) == 1);
  }

  // All the countries.
  public function all_countries() {
    $sql = 'SELECT * FROM countries ORDER BY name';
    return $this->db->query($sql);
  }

  // All enabled countries.
  public function all_enabled_countries() {
    $sql = 'SELECT * FROM countries WHERE enabled = 1 ORDER BY name';
    return $this->db->query($sql);
  }

  // All not used and enabled countries.
  public function all_available_countries() {
    $sql = 'SELECT * FROM countries WHERE enabled = 1 AND used = 0 ORDER BY name';
    return $this->db->query($sql);
  }

  // Check if country is in an active level.
  public function is_active_level($country_id) {
    $sql = 'SELECT COUNT(*) FROM levels WHERE entity_id = ? AND active = 1 LIMIT 1';
    $element = array($country_id);
    $is_active = $this->db->query($sql, $element);
    return (bool)$is_active[0]['COUNT(*)'];
  }  
}
