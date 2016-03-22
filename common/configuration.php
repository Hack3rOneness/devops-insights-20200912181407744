<?hh

require_once('db.php');

class Configuration {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
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

  // All the configuration.
  public function all_configuration() {
    $sql = 'SELECT * FROM configuration';
    return $this->db->query($sql);
  }
}
