<?hh

class Teams {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  // Generate salted hash.
  public function generate_hash($password) {
    $options = array(
      'cost' => 12,
    );
    return password_hash($password, PASSWORD_DEFAULT, $options);
  }

  // Checks if hash need refreshing.
  public function regenerate_hash($password_hash) {
    $options = array(
      'cost' => 12,
    );
    return (bool)password_needs_rehash($password_hash, PASSWORD_DEFAULT, $options);
  }

  // Verify if login is valid.
  public function verify_credentials($team_id, $password) {
    $sql = 'SELECT * FROM teams WHERE id = ? AND (active = 1 OR admin = 1) LIMIT 1';
    $element = array($team_id);
    $team = $this->db->query($sql, $element)[0];
    if (password_verify($password, $team['password_hash'])) {
      if ($this->regenerate_hash($team['password_hash'])) {
        $new_hash = $this->generate_hash($password);
        $this->update_team_password($new_hash, $team_id);
      }
      return $team;
    } else {
      return false;
    }
  }

  // Check to see if the team is active.
  public function check_team_status($team_id) {
    $sql = 'SELECT COUNT(*) FROM teams WHERE id = ? AND active = 1 LIMIT 1';
    $elements = array($team_id);
    $is_active = $this->db->query($sql, $elements);
    return (bool)$is_active[0]['COUNT(*)'];
  }

  // Create a team and return the created team id.
  public function create_team($name, $password_hash, $logo) {
    $sql = 'INSERT INTO teams (name, password_hash, logo, created_ts) VALUES (?, ?, ?, NOW())';
    $elements = array($name, $password_hash, $logo);
    $this->db->query($sql, $elements);
    return $this->db->query('SELECT LAST_INSERT_ID() AS id')[0]['id'];
  }

  // Update team.
  public function update_team($name, $logo, $points, $team_id) {
    $sql = 'UPDATE teams SET name = ?, logo = ? , points = ? WHERE id = ? LIMIT 1';
    $elements = array($name, $logo, $points, $team_id);
    $this->db->query($sql, $elements);
  }

  // Update team password.
  public function update_team_password($password_hash, $team_id) {
    $sql = 'UPDATE teams SET password_hash = ? WHERE id = ? LIMIT 1';
    $elements = array($password_hash, $team_id);
    $this->db->query($sql, $elements);
  }

  // Delete team.
  public function delete_team($team_id) {
    $sql = 'DELETE FROM teams WHERE id = ? AND protected = 0 LIMIT 1';
    $elements = array($team_id);
    $this->db->query($sql, $elements);
  }

  // Enable or disable teams by passing 1 or 0.
  public function toggle_status($team_id, $status) {
    $sql = 'UPDATE teams SET active = ? WHERE id = ? LIMIT 1';
    $elements = array($status, $team_id);
    $this->db->query($sql, $elements);
  }

  // Enable or disable all teams by passing 1 or 0.
  public function toggle_status_all($status) {
    $sql = 'UPDATE teams SET active = ? WHERE id > 0 AND protected = 0';
    $element = array($status);
    $this->db->query($sql, $element);
  }

  // Sets toggles team admin status.
  public function toggle_admin($team_id, $admin) {
    $sql = 'UPDATE teams SET admin = ? WHERE id = ? AND protected = 0 LIMIT 1';
    $elements = array($admin, $team_id);
    $this->db->query($sql, $elements);
  }

  // Enable or disable team visibility by passing 1 or 0.
  public function toggle_visible($team_id, $visible) {
    $sql = 'UPDATE teams SET visible = ? WHERE id = ? LIMIT 1';
    $elements = array($visible, $team_id);
    $this->db->query($sql, $elements);
  }

  // Check if a team name is already created.
  public function team_exist($team_name) {
    $sql = 'SELECT COUNT(*) FROM teams WHERE name = ?';
    $element = array($team_name);
    $exist = $this->db->query($sql, $element);
    return (bool)$exist[0]['COUNT(*)'];
  }

  // All active teams.
  public function all_active_teams() {
    $sql = 'SELECT * FROM teams WHERE active = 1 ORDER BY id';
    return $this->db->query($sql);
  }

  // All visible teams.
  public function all_visible_teams() {
    $sql = 'SELECT * FROM teams WHERE visible = 1 AND active = 1 ORDER BY id';
    return $this->db->query($sql);
  }

  // Leaderboard order.
  public function leaderboard() {
    $sql = 'SELECT id, name, logo, points, last_score FROM teams WHERE active = 1 AND visible = 1 ORDER BY points DESC, last_score ASC';
    return $this->db->query($sql);
  }

  // Progressive points.
  public function progressive($team_name) {
    $sql = 'SELECT * FROM ranking_log WHERE team_name = ? GROUP BY iteration ORDER BY points ASC';
    $element = array($team_name);
    return $this->db->query($sql, $element);
  }

  // All teams.
  public function all_teams() {
    $sql = 'SELECT * FROM teams ORDER BY points DESC';
    return $this->db->query($sql);
  }

  // Get a single team.
  public function get_team($team_id) {
    $sql = 'SELECT * FROM teams WHERE id = ? LIMIT 1';
    $elements = array($team_id);
    return $this->db->query($sql, $elements)[0];
  }

  // Get a single team, by name.
  public function get_team_by_name($team_name) {
    $sql = 'SELECT * FROM teams WHERE name = ? LIMIT 1';
    $elements = array($team_name);
    return $this->db->query($sql, $elements)[0];
  }

  // Get points by type.
  public function points_by_type($team_id, $type) {
    $sql = 'SELECT IFNULL(SUM(points), 0) AS points FROM scores_log WHERE type = ? AND team_id = ?';
    $elements = array($type, $team_id);
    return $this->db->query($sql, $elements)[0]['points'];
  }

  // Get healthy status for points.
  public function get_points_health($team_id) {
    $sql = 'SELECT t.points AS points, sum(s.points) AS sum FROM teams AS t, score_log AS s WHERE t.id = ? AND s.team_id = ?';
    $elements = array($team_id, $team_id);
    $team_points = $this->db->query($sql, $elements)['0'];
    if (!$team_points['sum']) {
      $team_points['sum'] = 0;
    }
    return (bool)($team_points['points'] == $team_points['sum']);
  }

  // Update the last_score field.
  public function last_score($team_id) {
    $sql = 'UPDATE teams SET last_score = NOW() WHERE id = ? LIMIT 1';
    $elements = array($team_id);
    $this->db->query($sql, $elements);
  }

  // Set all points to zero for all teams.
  public function reset_points() {
    $sql = 'UPDATE teams SET points = 0 WHERE id > 0';
    $this->db->query($sql);
  }

  // Teams total number.
  public function teams_count() {
    $sql = 'SELECT COUNT(*) AS count FROM teams';
    return $this->db->query($sql)[0];
  }

  // Get rank position for a team
  public function my_rank($team_id) {
    $rank = 1;
    foreach ($this->leaderboard() as $team) {
      if ($team_id == $team['id']) {
        return $rank;
      }
      $rank++;
    }
  }
}
