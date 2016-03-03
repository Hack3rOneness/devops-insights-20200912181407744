<?php

require_once('db.php');

class Links {
  private $db;

  function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->connected) {
      $this->db->connect();
    }
  }

  // 

  // Create link for a given level.
  public function create($link, $level_id) {
    $sql = 'INSERT INTO links (link, level_id, created_ts) VALUES (?, ?, NOW())';
    $elements = array($link, $level_id);
    $this->db->query($sql, $elements);
    return true;
  }

  // Modify existing link.
  public function update($link, $level_id, $link_id) {
    $sql = 'UPDATE links SET link = ?, level_id = ? WHERE id = ? LIMIT 1';
    $elements = array($link, $level_id, $link_id);
    $this->db->query($sql, $elements);
  }

  // Delete existing link.
  public function delete($link_id) {
    $sql = 'DELETE FROM links WHERE id = ? LIMIT 1';
    $element = array($link_id);
    $this->db->query($sql, $element);
  }

  // Get all links for a given level.
  public function all_links($level_id) {
    $sql = 'SELECT * FROM links WHERE level_id = ?';
    $element = array($level_id);
    return $this->db->query($sql, $element);
  }

  // Get a single link.
  public function get_link($link_id) {
    $sql = 'SELECT * FROM links WHERE id = ? LIMIT 1';
    $element = array($link_id);
    return $this->db->query($sql, $element);
  }

  // Check if a level has links.
  public function has_links($level_id) {
    $sql = 'SELECT COUNT(*) FROM links WHERE level_id = ?';
    $element = array($level_id);
    return (bool)$this->db->query($sql, $element)[0]['COUNT(*)'];
  }
}
