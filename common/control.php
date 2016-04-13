<?hh

class Control {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  public function all_tokens() {
    $sql = 'SELECT * FROM registration_tokens';
    return $this->db->query($sql);
  }

  public function all_available_tokens() {
    $sql = 'SELECT * FROM registration_tokens WHERE used = 0';
    return $this->db->query($sql);
  }

  public function check_token($token) {
    $sql = 'SELECT COUNT(*) FROM registration_tokens WHERE used = 0 AND token = ?';
    $element = array($token);
    return (bool)$this->db->query($sql, $element)[0]['COUNT(*)'];
  }

  public function use_token($token, $team_id) {
    $sql = 'UPDATE registration_tokens SET used = 1, team_id = ?, use_ts = NOW() WHERE token = ? LIMIT 1';
    $elements = array($team_id, $token);
    $this->db->query($sql, $elements);
  }

  public function delete_token($token) {
    $sql = 'DELETE from registration_tokens WHERE token = ? LIMIT 1';
    $element = array($token);
    $this->db->query($sql, $element);
  }

  public function create_tokens() {
    $crypto_strong = True;
    $tokens = array();
    $query = array();
    $token_len = 15;
    $token_number = 50;
    $arr_remove = array("=","+", "/");
    for ($i = 0; $i < $token_number; $i++) {
      $token = str_replace($arr_remove, "",
        base64_encode(
          openssl_random_pseudo_bytes(
            $token_len,
            $crypto_strong
          )
        )
      );
      $sql = 'INSERT INTO registration_tokens (token, created_ts) VALUES(?, NOW())';
      $element = array($token);
      $this->db->query($sql, $element);
    }
  }

  public function export_tokens() {
    $sql = 'SELECT * FROM registration_tokens WHERE used = 0';
    $tokens = $this->db->query($sql);
  }

  public function begin() {
    // Reset all points
    $teams = new Teams();
    $teams->reset_points();

    $conf = new Configuration();

    // Clear scores log
    $this->reset_scores();

    // Clear hints log
    $this->reset_hints();

    // Clear failures log
    $this->reset_failures();

    // Mark game as started
    $conf->change('game', '1');

    // Enable scoring
    $conf->change('scoring', '1');

    // Take timestamp of start
    $start_ts = time();
    $conf->change('start_ts', $start_ts);

    // Calculate timestamp of the end
    $duration = $conf->get('game_duration');
    $end_ts = $start_ts + $duration;
    $conf->change('end_ts', $end_ts);

    // Kick off timer
    $conf->change('timer', '1');

    // Reset and kick off progressive scoreboard
    $this->reset_progressive();
    $this->progressive_scoreboard();
  }

  public function end() {
    // Mark game as finished and it stops progressive scoreboard
    $conf = new Configuration();
    $conf->change('game', '0');

    // Disable scoring
    $conf->change('scoring', '0');

    // Put timestampts to zero
    $conf->change('start_ts', '0');
    $conf->change('end_ts', '0');

    // Stop timer
    $conf->change('timer', '0');
  }

  public function new_announcement($announcement) {
    $sql = 'INSERT INTO announcements_log (ts, announcement) (SELECT NOW(), ?) LIMIT 1';
    $element = array($announcement);
    $this->db->query($sql, $element);
  }

  public function delete_announcement($announcement_id) {
    $sql = 'DELETE FROM announcements_log WHERE id = ? LIMIT 1';
    $element = array($announcement_id);
    $this->db->query($sql, $element);
  }

  public function all_announcements() {
    $sql = 'SELECT * FROM announcements_log ORDER BY ts DESC';
    return $this->db->query($sql);
  }

  public function all_activity() {
    $sql = 'SELECT DATE_FORMAT(scores_log.ts, "%H:%i:%S") AS time, teams.name AS team, countries.name AS country, scores_log.team_id AS team_id FROM scores_log, levels, teams, countries WHERE scores_log.level_id = levels.id AND levels.entity_id = countries.id AND scores_log.team_id = teams.id ORDER BY time ASC';
    return $this->db->query($sql);
  }

  public function progressive_scoreboard() {
    $conf = new Configuration();
    $take_scoreboard = (bool)$conf->get('game');
    $cycle = (int)$conf->get('progressive_cycle');

    while ($take_scoreboard) {
      $this->take_progressive();
      sleep($cycle);
      $take_scoreboard = (bool)$conf->get('game');
      $cycle = (int)$conf->get('progressive_cycle');
    }
  }

  public function take_progressive() {
    $sql = 'INSERT INTO ranking_log (ts, team_name, points, iteration) (SELECT NOW(), name, points, (SELECT IFNULL(MAX(iteration)+1, 1) FROM ranking_log) FROM teams)';
    $this->db->query($sql);
  }

  public function reset_progressive() {
    $sql = 'DELETE FROM ranking_log WHERE id > 0';
    $this->db->query($sql);
  }

  public function reset_scores() {
    $sql = 'DELETE FROM scores_log WHERE id > 0';
    $this->db->query($sql);
  }

  public function reset_hints() {
    $sql = 'DELETE FROM hints_log WHERE id > 0';
    $this->db->query($sql);
  }

  public function reset_failures() {
    $sql = 'DELETE FROM failures_log WHERE id > 0';
    $this->db->query($sql);
  }
}
