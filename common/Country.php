<?hh // strict

class Country extends Model {
  private function __construct(
    private int $id,
    private string $iso_code,
    private string $name,
    private int $used,
    private int $enabled,
    private string $d,
    private string $transform,
  ) {
  }

  public function getId(): int {
    return $this->id;
  }

  public function getIsoCode(): string {
    return $this->iso_code;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getUsed(): bool {
    return $this->used === 1;
  }

  public function getEnabled(): bool {
    return $this->enabled === 1;
  }

  public function getD(): string {
    return $this->d;
  }

  public function getTransform(): string {
    return $this->transform;
  }

  // Make sure all the countries used field is good
  public static function usedAdjust(): void {
    $db = self::getDb();

    $sql1 = 'UPDATE countries SET used = 1 WHERE id IN (SELECT entity_id FROM levels)';
    $sql2 = 'UPDATE countries SET used = 0 WHERE id NOT IN (SELECT entity_id FROM levels)';
    $db->query($sql1);
    $db->query($sql2);
  }

  // TODO: Convert to hack strict when we have a Level object
  // Retrieve how many levels are using one country
  public static function whoUses(int $country_id): mixed {
    $db = self::getDb();

    $sql = 'SELECT * FROM levels WHERE entity_id = ? AND active = 1 LIMIT 1';
    $element = array($country_id);
    $who_uses = $db->query($sql, $element);
    if ($who_uses) {
      return $who_uses[0];
    }
    return $who_uses;
  }

  // Enable or disable a country
  public static function setStatus(int $country_id, bool $status): void {
    $db = self::getDb();

    $sql = 'UPDATE countries SET enabled = ? WHERE id = ?';
    $elements = array($status ? 1 : 0, $country_id);
    $db->query($sql, $elements);
  }

  // Check if a country is enabled
  public static function isEnabled(int $country_id): bool {
    $db = self::getDb();

    $sql = 'SELECT enabled FROM countries WHERE id = ?';
    $element = array($country_id);
    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return intval(firstx($results)) === 1;
  }

  // Set the used flag for a country
  public static function setUsed(int $country_id, bool $status): void {
    $db = self::getDb();

    $sql = 'UPDATE countries SET used = ? WHERE id = ? LIMIT 1';
    $elements = array($status ? 1 : 0, $country_id);
    $db->query($sql, $elements);
  }

  // Check if a country is used
  public static function isUsed(int $country_id): bool {
    $db = self::getDb();

    $sql = 'SELECT used FROM countries WHERE id = ?';
    $element = array($country_id);
    return (bool)($db->query($sql, $element) == 1);
  }

  // Get all countries
  public static function allCountries(bool $map): array<Country> {
    $db = self::getDb();

    if ($map) {
      $sql = 'SELECT * FROM countries ORDER BY CHAR_LENGTH(d)';
    } else {
      $sql = 'SELECT * FROM countries ORDER BY name';
    }
    $results = $db->query($sql);

    $countries = array();
    foreach ($results as $row) {
      $countries[] = self::countryFromRow($row);
    }

    return $countries;
  }

  // All enabled countries. The weird sorting is because SVG lack of z-index
  // and things looking like shit in the map. See issue #20.
  public static function allEnabledCountries(bool $map): array<Country> {
    $db = self::getDb();

    if ($map) {
      $sql = 'SELECT * FROM countries WHERE enabled = 1 ORDER BY CHAR_LENGTH(d)';
    } else {
      $sql = 'SELECT * FROM countries WHERE enabled = 1 ORDER BY name';
    }
    $results = $db->query($sql);

    $countries = array();
    foreach ($results as $row) {
      $countries[] = self::countryFromRow($row);
    }

    return $countries;
  }

  // All enabled and unused countries
  public static function allAvailableCountries(): array<Country> {
    $db = self::getDb();

    $sql = 'SELECT * FROM countries WHERE enabled = 1 AND used = 0 ORDER BY name';
    $results = $db->query($sql);

    $countries = array();
    foreach ($results as $row) {
      $countries[] = self::countryFromRow($row);
    }

    return $countries;
  }

  // Check if country is in an active level
  public static function isActiveLevel(int $country_id): bool {
    $db = self::getDb();

    $sql = 'SELECT COUNT(*) FROM levels WHERE entity_id = ? AND active = 1';
    $element = array($country_id);
    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return intval(firstx($results)['COUNT(*)']) > 0;
  }

  // Get a country by id
  public static function get(int $country_id): Country {
    $db = self::getDb();

    $sql = 'SELECT * FROM countries WHERE id = ? LIMIT 1';
    $element = array($country_id);
    $results = $db->query($sql, $element);
    invariant(count($results) === 1, 'Expected exactly one result');

    return self::countryFromRow(firstx($results));
  }

  private static function countryFromRow(array<string, string> $row): Country {
    return new Country(
      intval(must_have_idx($row, 'id')),
      must_have_idx($row, 'iso_code'),
      must_have_idx($row, 'name'),
      intval(must_have_idx($row, 'used')),
      intval(must_have_idx($row, 'enabled')),
      must_have_idx($row, 'd'),
      must_have_idx($row, 'transform'),
    );
  }

  // Get a random enabled, unused country ID
  public static function randomAvailableCountryId(): int {
    $db = self::getDb();

    $sql = 'SELECT id FROM countries WHERE enabled = 1 AND used = 0 ORDER BY RAND() LIMIT 1';
    $results = $db->query($sql);
    invariant(count($results) === 1, 'Expected exactly one result');

    return intval(firstx($results)['id']);
  }
}
