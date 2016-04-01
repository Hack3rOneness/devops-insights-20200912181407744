<?hh

class Configuration {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  public function progressive_count() {
    $sql = 'SELECT COUNT(DISTINCT(iteration)) AS C FROM ranking_log';
    return $this->db->query($sql)[0]['C'];
  }

  public function create($field, $value) {
    $sql = 'INSERT INTO configuration (field, value) VALUES(?, ?) LIMIT 1';
    $elements = array($field, $value);
    $this->db->query($sql, $elements);
  }

  public function get($field) {
    $sql = 'SELECT value FROM configuration WHERE field = ? LIMIT 1';
    $element = array($field);
    return $this->db->query($sql, $element)[0]['value'];
  }

  // Change configuration field.
  public function change($field, $value) {
    $sql = 'UPDATE configuration SET value = ? WHERE field = ? LIMIT 1';
    $elements = array($value, $field);
    $this->db->query($sql, $elements);
  }

  // Check if field is valid.
  public function valid_field($field) {
    $sql = 'SELECT COUNT(*) FROM configuration WHERE field = ? LIMIT 1';
    $element = array($field);
    return (bool)$this->db->query($sql, $element)[0]['COUNT(*)'];
  }

  // All the configuration.
  public function all_configuration() {
    $sql = 'SELECT * FROM configuration';
    return $this->db->query($sql);
  }
}
