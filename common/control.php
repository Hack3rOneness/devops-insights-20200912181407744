<?hh

class Control {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  public function begin() {
    // Reset all points
    $teams = new Teams();
    $teams->reset_points();

    // Clear scores log
    $this->reset_scores();

    // Clear hints log
    $this->reset_hints();

    // Clear failures log
    $this->reset_failures();

    // Mark game as started
    $conf = new Configuration();
    $conf->change('game', '1');

    // Take timestamp of start
    //$conf->change('start_ts', time());

    // Calculate timestamp of the end
    //$conf->change('end_ts', time());

    // Reset and kick off progressive scoreboard
    $this->reset_progressive();
    $conf->change('ranking', '1');
    $this->progressive_scoreboard();
  }

  public function end() {
    // Mark game as finished
    $conf = new Configuration();
    $conf->change('game', '0');

    // Stop progressive scoreboard
    $conf->change('ranking', '0');
  }

  public function progressive_scoreboard() {
    $conf = new Configuration();
    $take_scoreboard = (bool)$conf->get('ranking');
    $cycle = (int)$conf->get('ranking_cycle');

    while ($take_scoreboard) {
      $this->take_progressive();
      sleep($cycle);
      $take_scoreboard = (bool)$conf->get('ranking');
      $cycle = (int)$conf->get('ranking_cycle');
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
