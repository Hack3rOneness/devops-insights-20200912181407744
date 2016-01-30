<?php

require_once('config.php');
require_once('db.php');

class Levels {
  private static $db;

  function __construct($db) {
    $this->db = $db;
    if (!$this->db->connected) {
      $this->db->connect();
    }
  }

  // Check to see if the level is active.
  public function check_level_status($level_id) {
    $sql = 'SELECT COUNT(*) FROM levels WHERE id = ? AND active = 1 LIMIT 1';
    $elements = array($level_id);
    $is_active = $this->db->query($sql, $elements);
    return (bool)$is_active[0]['COUNT(*)'];
  }

  // Create a team and return the created level id.
  public function create_level(
    $type,
    $description,
    $entity_id,
    $points,
    $bonus,
    $bonus_dec,
    $bonus_fix,
    $flag,
    $hint,
    $penalty
  ) {
    $sql = 'INSERT INTO levels '.
      '(type, description, entity_id, points, bonus, bonus_dec, bonus_fix, flag, penalty, created_ts) '.
      'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW());';
    $elements = array(
      $type,
      $description,
      $entity_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus_fix,
      $flag,
      $hint,
      $penalty
    );
    $this->db->query($sql, $elements);
    return $this->db->query('SELECT LAST_INSERT_ID() AS id')[0]['id'];
  }

  // Update level.
  public function update_level(
    $description,
    $entity_id,
    $points,
    $bonus,
    $bonus_dec,
    $bonus_fix,
    $flag,
    $hint,
    $penalty,
    $level_id
  ) {
    $sql = 'UPDATE levels SET description = ?, entity_id = ?, points = ?, '.
      'bonus = ?, bonus_dec = ?, bonus_fix = ?, flag = ?, hint = ?, '.
      'penalty = ? WHERE id = ? LIMIT 1';
    $elements = array(
      $description,
      $entity_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus_fix,
      $flag,
      $hint,
      $penalty,
      $level_id
    );
    $this->db->query($sql, $elements);
  }

  // Delete level.
  public function delete_level($level_id) {
    $sql = 'DELETE FROM levels WHERE id = ? LIMIT 1';
    $elements = array($level_id);
    $this->db->query($sql, $elements);
  }

  // Enable or disable level by passing 1 or 0.
  public function toggle_status($level_id, $active) {
    $sql = 'UPDATE levels SET active = ? WHERE id = ? LIMIT 1';
    $elements = array($active, $level_id);
    $this->db->query($sql, $elements);
  }

  // All levels. Active, inactive or all.
  public function all_levels($active=null) {
    $sql = ($active)
      ? ($active == 1)
        ? 'SELECT * FROM levels WHERE active = 1'
        : 'SELECT * FROM levels WHERE active = 0';
      : 'SELECT * FROM levels';
    return $this->db->query($sql);
  }

  // All levels by type. Active, inactive or all.
  public function all_type_levels($active=null, $type) {
    $sql = ($active)
      ? ($active == 1)
        ? 'SELECT * FROM levels WHERE active = 1 AND type = ?'
        : 'SELECT * FROM levels WHERE active = 0 AND type = ?';
      : 'SELECT * FROM levels WHERE type = ?';
    $element = array($type);
    return $this->db->query($sql, $element);
  }

  // All quiz levels. Active, inactive or all.
  public function all_quiz_levels($active=null) {
    return $this->all_type_levels($active, 'quiz');
  }

  // All base levels. Active, inactive or all.
  public function all_base_levels($active=null) {
    return $this->all_type_levels($active, 'base');
  }

  // All flag levels. Active, inactive or all.
  public function all_flag_levels($active=null) {
    return $this->all_type_levels($active, 'flag');
  }

  // Get a single level.
  public function get_level($level_id) {
    $sql = 'SELECT * FROM levels WHERE id = ? LIMIT 1';
    $elements = array($level_id);
    return $this->db->query($sql, $elements)[0];
  }
}
