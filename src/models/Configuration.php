<?hh // strict

class Configuration extends Model {
  private function __construct(private int $id, private string $field, private string $value, private string $description) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getField(): string {
    return $this->field;
  }

  public function getValue(): string {
    return $this->value;
  }

  public function getDescription(): string {
    return $this->description;
  }

  // Create configuration entry.
  public static function create(string $field, string $value, string $description): void {
    $db = self::getDb();
    $sql = 'INSERT INTO configuration (field, value, description) VALUES(?, ?, ?) LIMIT 1';
    $elements = array($field, $value, $description);
    $db->query($sql, $elements);
  }

  // Get configuration entry.
  public static function get(string $field): Configuration {
    $db = self::getDb();
    $sql = 'SELECT * FROM configuration WHERE field = ? LIMIT 1';
    $element = array($field);

    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return self::configurationFromRow(firstx($results));
  }

  // Change configuration field.
  public static function update(string $field, string $value): void {
    $db = self::getDb();
    $sql = 'UPDATE configuration SET value = ? WHERE field = ? LIMIT 1';
    $elements = array($value, $field);
    $db->query($sql, $elements);
  }

  // Check if field is valid.
  public static function validField(string $field): bool {
    $db = self::getDb();
    $sql = 'SELECT COUNT(*) FROM configuration WHERE field = ?';
    $element = array($field);

    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return intval(firstx($results)['COUNT(*)']) > 0;
  }

  // All the configuration.
  public static function allConfiguration(): array<Configuration> {
    $db = self::getDb();
    $sql = 'SELECT * FROM configuration';
    $results = $db->query($sql);

    $configuration = array();
    foreach ($results as $row) {
      $configuration[] = self::configurationFromRow($row);
    }

    return $configuration;
  }

  private static function configurationFromRow(array<string, string> $row): Configuration {
    return new Configuration(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'field'),
      must_have_idx($row, 'value'),
      must_have_idx($row, 'description'),
    );
  }
}
