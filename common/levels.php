<?hh

require_once('db.php');
require_once('countries.php');

class Levels {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  // Check to see if the level is active.
  public function check_level_status($level_id) {
    $sql = 'SELECT COUNT(*) FROM levels WHERE id = ? AND active = 1 LIMIT 1';
    $element = array($level_id);
    $is_active = $this->db->query($sql, $element);
    return (bool)$is_active[0]['COUNT(*)'];
  }

  // Create a team and return the created level id.
  public function create_level(
    $type,
    $description,
    $entity_id,
    $category_id,
    $points,
    $bonus,
    $bonus_dec,
    $bonus_fix,
    $flag,
    $hint,
    $penalty
  ) {
    $sql = 'INSERT INTO levels '.
      '(type, description, entity_id, category_id, points, bonus, bonus_dec, bonus_fix, flag, hint, penalty, created_ts) '.
      'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW());';
    $elements = array(
      $type,
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus_fix,
      $flag,
      $hint,
      $penalty
    );
    $this->db->query($sql, $elements);
    $level_id = $this->db->query('SELECT LAST_INSERT_ID() AS id')[0]['id'];
    $countries = new Countries();
    $countries->toggle_used($entity_id, 1);

    return $level_id;
  }

  // Create a flag level.
  public function create_flag_level(
    $description,
    $flag,
    $entity_id,
    $category_id,
    $points,
    $bonus,
    $bonus_dec,
    $hint,
    $penalty
  ) {
    return $this->create_level(
      'flag',
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus,
      $flag,
      $hint,
      $penalty
    );
  }

  // Update a flag level.
  public function update_flag_level(
    $description,
    $flag,
    $entity_id,
    $category_id,
    $points,
    $bonus,
    $bonus_dec,
    $hint,
    $penalty,
    $level_id
  ) {
    return $this->update_level(
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus,
      $flag,
      $hint,
      $penalty,
      $level_id
    );
  }

  // Create a quiz level.
  public function create_quiz_level(
    $question,
    $answer,
    $entity_id,
    $points,
    $bonus,
    $bonus_dec,
    $hint,
    $penalty
  ) {
    $sql = 'SELECT id FROM categories WHERE category = "Quiz" LIMIT 1';
    $category_id = $this->db->query($sql)[0]['id'];
    return $this->create_level(
      'quiz',
      $question,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus,
      $answer,
      $hint,
      $penalty
    );
  }

  // Update a quiz level.
  public function update_quiz_level(
    $question,
    $answer,
    $entity_id,
    $points,
    $bonus,
    $bonus_dec,
    $hint,
    $penalty,
    $level_id
  ) {
    $sql = 'SELECT id FROM categories WHERE category = "Quiz" LIMIT 1';
    $category_id = $this->db->query($sql)[0]['id'];
    return $this->update_level(
      $question,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      $bonus_dec,
      $bonus,
      $answer,
      $hint,
      $penalty,
      $level_id
    );
  }

  // Create a base level.
  public function create_base_level(
    $description,
    $entity_id,
    $category_id,
    $points,
    $bonus,
    $hint,
    $penalty
  ) {
    return $this->create_level(
      'base',
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      0,
      $bonus,
      '',
      $hint,
      $penalty
    );
  }

  // Update a base level.
  public function update_base_level(
    $description,
    $entity_id,
    $category_id,
    $points,
    $bonus,
    $hint,
    $penalty,
    $level_id
  ) {
    return $this->update_level(
      $description,
      $entity_id,
      $category_id,
      $points,
      $bonus,
      0,
      $bonus,
      '',
      $hint,
      $penalty,
      $level_id
    );
  }

  // Update level.
  public function update_level(
    $description,
    $entity_id,
    $category_id,
    $points,
    $bonus,
    $bonus_dec,
    $bonus_fix,
    $flag,
    $hint,
    $penalty,
    $level_id
  ) {
    $sql = 'UPDATE levels SET description = ?, entity_id = ?, category_id = ?, points = ?, '.
      'bonus = ?, bonus_dec = ?, bonus_fix = ?, flag = ?, hint = ?, '.
      'penalty = ? WHERE id = ? LIMIT 1';
    $elements = array(
      $description,
      $entity_id,
      $category_id,
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
    // Free country first.
    $level = $this->get_level($level_id);
    $countries = new Countries();
    $countries->toggle_used($level['entity_id'], 0);

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
        : 'SELECT * FROM levels WHERE active = 0'
      : 'SELECT * FROM levels';
    return $this->db->query($sql);
  }

  // All levels by type. Active, inactive or all.
  public function all_type_levels($active, $type) {
    $sql = ($active)
      ? ($active == 1)
        ? 'SELECT * FROM levels WHERE active = 1 AND type = ?'
        : 'SELECT * FROM levels WHERE active = 0 AND type = ?'
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

  // All categories.
  public function all_categories() {
    $sql = 'SELECT * FROM categories';
    return $this->db->query($sql);
  }

  // Delete category.
  public function delete_category($category_id) {
    $sql = 'DELETE FROM categories WHERE id = ? LIMIT 1';
    $elements = array($category_id);
    $this->db->query($sql, $elements);
  }

  // Create category.
  public function create_category($category) {
    $sql = 'INSERT INTO categories (category, created_ts) VALUES (?, NOW())';
    $element = array($category);
    $this->db->query($sql, $element);
    return $this->db->query('SELECT LAST_INSERT_ID() AS id')[0]['id'];
  }

  // Update category.
  public function update_category($category, $category_id) {
    $sql = 'UPDATE categories SET category = ? WHERE id = ? LIMIT 1';
    $elements = array($category, $category_id);
    $this->db->query($sql, $elements);
  }

  // Get category.
  public function get_category($category_id) {
    $sql = 'SELECT * FROM categories WHERE id = ? LIMIT 1';
    $element = array($category_id);
    return $this->db->query($sql, $element)[0];
  }

  // Check if flag is correct.
  public function check_answer($level_id, $answer) {
    $sql = 'SELECT flag FROM levels WHERE active = 1 AND id = ? LIMIT 1';
    $element = array($level_id);
    $flag = $this->db->query($sql, $element)[0]['flag'];

    // TODO: Implement bad answers logging
    return (bool)(strtoupper(trim($flag)) == strtoupper(trim($answer)));
  }

  // Adjust bonus.
  public function adjust_bonus($level_id) {
    $sql = 'UPDATE levels SET bonus = GREATEST(bonus - bonus_dec, 0) WHERE id = ? LIMIT 1';
    $element = array($level_id);
    $this->db->query($sql, $element);
  }

  // Log successful score.
  public function log_valid_score($level_id, $team_id, $points, $type) {
    $sql = 'INSERT INTO scores_log (ts, level_id, team_id, points, type) VALUES (NOW(), ?, ?, ?, ?)';
    $elements = array($level_id, $team_id, $points, $type);
    $this->db->query($sql, $elements);
  }

  // Log hint request hint.
  public function log_get_hint($level_id, $team_id, $penalty) {
    $sql = 'INSERT INTO hints_log (ts, level_id, team_id, penalty) VALUES (NOW(), ?, ?, ?)';
    $elements = array($level_id, $team_id, $penalty);
    $this->db->query($sql, $elements);
  }

  // Log attempt on score.
  public function log_failed_score($level_id, $team_id, $flag) {
    $sql = 'INSERT INTO failures_log (ts, level_id, team_id, flag) VALUES(NOW(), ?, ?, ?)';
    $elements = array($level_id, $team_id, $flag);
    $this->db->query($sql, $elements);
  }

  // Check if there is a previous score.
  public function previous_score($level_id, $team_id, $any_team=false) {
    $sql = ($any_team)
      ? 'SELECT COUNT(*) FROM scores_log WHERE level_id = ? AND team_id != ?'
      : 'SELECT COUNT(*) FROM scores_log WHERE level_id = ? AND team_id = ?';
    $elements = array($level_id, $team_id);
    $have_score = $this->db->query($sql, $elements);
    return (bool)$have_score[0]['COUNT(*)'];
  }

  // Score level.
  public function score_level($level_id, $team_id) {
    // Check if team has already scored this level
    if ($this->previous_score($level_id, $team_id)) {
      return false;
    }

    $level = $this->get_level($level_id);

    // Calculate points to give
    $points = $level['points'] + $level['bonus'];

    // Adjust bonus
    $this->adjust_bonus($level_id);

    // Score!
    $sql = 'UPDATE teams SET points = points + ?, last_score = NOW() WHERE id = ? LIMIT 1';
    $elements = array($points, $team_id);
    $this->db->query($sql, $elements);

    // Log the score...
    $this->log_valid_score($level_id, $team_id, $points, $level['type']);

    // kthxbai
    return true;
  }

  // Check if there is a previous hint.
  public function previous_hint($level_id, $team_id, $any_team=false) {
    $sql = ($any_team)
      ? 'SELECT COUNT(*) FROM hints_log WHERE level_id = ? AND team_id != ?'
      : 'SELECT COUNT(*) FROM hints_log WHERE level_id = ? AND team_id = ?';
    $elements = array($level_id, $team_id);
    $have_hint = $this->db->query($sql, $elements);
    return (bool)$have_hint[0]['COUNT(*)'];
  }

  // Get hint.
  public function get_hint($level_id, $team_id) {
    $level = $this->get_level($level_id);
    $penalty = $level['penalty'];

    // Check if team has already gotten this hint
    if ($this->previous_hint($level_id, $team_id)) {
      $penalty = 0;
    }

    // Make sure team has enough points to pay
    $sql = 'SELECT points FROM teams where id = ? LIMIT 1';
    $element = array($team_id);
    $team_points = $this->db->query($sql, $element)[0]['points'];
    if ($team_points < $penalty) {
      return false;
    }

    // Adjust points
    $sql = 'UPDATE teams SET points = points - ? WHERE id = ? LIMIT 1';
    $elements = array($penalty, $team_id);
    $this->db->query($sql, $elements);

    // Log the hint
    $this->log_get_hint($level_id, $team_id, $penalty);

    // Hint!
    return $level['hint'];
  }

  // All teams that have completed this level
  public function completed_by($level_id) {
    $sql = 'SELECT name FROM teams WHERE id IN (SELECT team_id FROM scores_log WHERE level_id = ? ORDER BY ts) AND visible = 1 AND active = 1';
    $element = array($level_id);
    return $this->db->query($sql, $element);
  }
}