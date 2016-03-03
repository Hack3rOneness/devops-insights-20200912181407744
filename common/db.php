<?hh // strict

require_once('utils.php');
require_once('funs.php');

class DB {
  private string $settings_file = 'settings.ini';
  private ?array<string, string> $config = null;
  private static ?DB $instance = null;
  private ?PDO $dbh = null;

  public static function getInstance(): DB {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct() {
    $this->config = parse_ini_file($this->settings_file);
  }

  private function __clone(): void {}

  public function connect(): void {
    $host = must_have_idx($this->config, 'DB_HOST');
    $port = must_have_idx($this->config, 'DB_PORT');
    $db_name = must_have_idx($this->config, 'DB_NAME');
    $username = must_have_idx($this->config, 'DB_USERNAME');
    $password = must_have_idx($this->config, 'DB_PASSWORD');

    $conn_str = sprintf(
      'mysql:host=%s;port=%s;dbname=%s',
      $host,
      $port,
      $db_name,
    );
    try {
      $this->dbh = new PDO(
        $conn_str,
        $username,
        $password,
      );
    } catch (PDOException $e) {
      error_log("[ db.php ] - Connection error: ".$e->getMessage());
      error_page();
    }
  }

  public function disconnect(): void {
    $this->dbh = null;
  }

  public function isConnected(): bool {
    return $this->dbh !== null;
  }

  public function query(
    string $query,
    ?array<mixed> $elements = null
  ): array<array<string, string>> {
    if (!$this->isConnected()) {
      $this->connect();
    }

    invariant($this->dbh !== null, 'Database handle should not be null');
    $stmt = $this->dbh->prepare($query);
    if ($elements !== null) {
      $i = 1;
      foreach ($elements as &$element) {
        $stmt->bindparam($i, $element);
        $i++;
      }
    }

    try {
      $stmt->execute();
    } catch (PDOException $e) {
      error_log("[ db.php ] - Statement error: " . $stmt->errorInfo());
      error_page();
    }

    $results = array();
    while ($row = $stmt->fetch()) {
      $results[] = $row;
    }
    return $results;
  }
}
