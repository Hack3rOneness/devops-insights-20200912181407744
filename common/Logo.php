<?hh // strict

class Logo extends Model {
  private function __construct(
    private int $id, 
    private int $used,
    private int $enabled, 
    private int $protected, 
    private string $name,
    private string $logo
  ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getLogo(): string {
    return $this->logo;
  }

  public function getUsed(): bool {
    return $this->used === 1;
  }

  public function getEnabled(): bool {
    return $this->enabled === 1;
  }

  public function getProtected(): bool {
    return $this->protected === 1;
  }

  // Check to see if the logo exists.
  public static function checkExists(string $logo): bool {
    $all_logos = self::allEnabledLogos();
    foreach ($all_logos as $l) {
      if ($logo === $l->getName()) {
        return true;
      }
    }
    return false;
  }

  // Enable or disable logo by passing 1 or 0.
  public static function setEnabled(int $logo_id, bool $enabled): void {
    $db = self::getDb();

    $sql = 'UPDATE logos SET enabled = ? WHERE id = ? LIMIT 1';
    $elements = array($enabled, $logo_id);
    $db->query($sql, $elements);
  }

  // Retrieve a random logo from the table.
  public static function randomLogo(): string {
    $db = self::getDb();

    $sql = 'SELECT name FROM logos WHERE enabled = 1 ORDER BY RAND() LIMIT 1';
    $results = $db->query($sql);
    invariant(count($results) === 1, 'Expected exactly one result');

    return firstx($results)['name'];
  }

  // All the logos.
  public static function allLogos(): array<Logo> {
    $db = self::getDb();

    $sql = 'SELECT * FROM logos';
    $results = $db->query($sql);

    $logos = array();
    foreach ($results as $row) {
      $logos[] = self::logoFromRow($row);
    }

    return $logos;
  }

  // All the enabled logos.
  public static function allEnabledLogos(): array<Logo> {
    $db = self::getDb();

    $sql = 'SELECT * FROM logos WHERE enabled = 1 AND protected = 0';
    $results = $db->query($sql);

    $logos = array();
    foreach ($results as $row) {
      $logos[] = self::logoFromRow($row);
    }

    return $logos;
  }

  private static function logoFromRow(array<string, string> $row): Logo {
    return new Logo(
      intval(must_have_idx($row, 'id')),
      intval(must_have_idx($row, 'used')),
      intval(must_have_idx($row, 'enabled')),
      intval(must_have_idx($row, 'protected')),
      must_have_idx($row, 'name'),
      must_have_idx($row, 'logo'),
    );
  }
}
