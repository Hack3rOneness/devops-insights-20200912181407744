<?hh

class Logos {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  // Check to see if the team is active.
  public function check_exists($logo) {
    $all_logos = $this->all_enabled_logos();
    foreach ($all_logos as $l) {
      if ($logo == $l['name']) {
        return true;
      }
    }
    return false;
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
    return $this->db->query($sql)[0]['name'];
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
    $sql = 'SELECT * FROM logos WHERE enabled = 1 AND protected = 0';
    return $this->db->query($sql);
  }
}
