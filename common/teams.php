<?hh

require_once('db.php');

class Teams {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  // Verify if login is valid.
  public function verify_credentials($team_id, $password) {
    $sql = 'SELECT * FROM teams WHERE id = ? AND (active = 1 OR admin = 1) AND password = ? LIMIT 1';
    $elements = array($team_id, $password);
    $team = $this->db->query($sql, $elements);
    if ($team) {
      return $team['0'];
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
  public function create_team($name, $password, $logo) {
    $sql = 'INSERT INTO teams (name, password, logo, created_ts) VALUES (?, ?, ?, NOW())';
    $elements = array($name, $password, $logo);
    $this->db->query($sql, $elements);
    return $this->db->query('SELECT LAST_INSERT_ID() AS id')[0]['id'];
  }

  // Update team.
  public function update_team($name, $password, $logo, $points, $team_id) {
    $sql = 'UPDATE teams SET name = ?, password = ?, logo = ? , points = ? WHERE id = ? LIMIT 1';
    $elements = array($name, $password, $logo, $points, $team_id);
    $this->db->query($sql, $elements);
  }

  // Delete team.
  public function delete_team($team_id) {
    $sql = 'DELETE FROM teams WHERE id = ? LIMIT 1';
    $elements = array($team_id);
    $this->db->query($sql, $elements);
  }

  // Enable or disable teams by passing 1 or 0.
  public function toggle_status($team_id, $status) {
    $sql = 'UPDATE teams SET active = ? WHERE id = ? LIMIT 1';
    $elements = array($status, $team_id);
    $this->db->query($sql, $elements);
  }

  // Sets toggles team admin status.
  public function toggle_admin($team_id, $admin) {
    $sql = 'UPDATE teams SET admin = ? WHERE id = ? LIMIT 1';
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
